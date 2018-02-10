<?php
global $_cf,$_db,$_req;
include_once(INCLUDE_ROOT.'/lib/PHPExcel.php');
selfcheck();

echo "begin\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

$sql = "select a.`ID`,a.`Site`,b.`Alias`,b.`PublisherId`,a.`PaidDate` from payments as a left join publisher_account as b on a.`Site` = b.`ApiKey` WHERE a.InvoiceFile = ''";
$rows = $_db->getRows($sql);

$root_data_dir = '/app/site/admin.brandreward.com/web/data/payments';

foreach($rows as $k=>$v){
    $date = $v['PaidDate'];
    $alias = $v['Alias'];
    $objD = new Datetime($date);
    $Year = $objD->format('Y');
    $Month = $objD->format('M');
    $ymd = $objD->format('Ymd');
    $file_name = '/'.$ymd.'/'.$v['PublisherId'].'/'.$Month.$Year.'_'.str_replace(' ','_',trim($alias)).'.xlsx';

    $res_dir = dir_check($root_data_dir,$file_name);
    if($res_dir){
        $file_full_name = $root_data_dir.'/'.ltrim($file_name,'/');
        download_invoice($v['ID'],$file_full_name);
        if(is_file($file_full_name)){
            $sql = "UPDATE payments SET InvoiceFile = '".addslashes($file_name)."' WHERE ID = ".$v['ID'];
            $_db->query($sql);
            print_r("doing\t|\t".$v['PaidDate']."\t|\t".$v['Site']."\t|\t".$v['Alias']."\t|\t".$file_full_name."\n");
        }else{
            print_r("doing\t|\t".$v['PaidDate']."\t|\t".$v['Site']."\t|\t".$v['Alias']."\t|\tFailed\n");
        }
    }
}
echo "end\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

function selfcheck(){
    global $_req;
    $cmd = "ps aux | grep '\-act=".$_req['act']."' | wc -l";
    //echo $cmd."\n";
    exec($cmd, $res);
    //print_r($res);exit();
    if($res[0] > 3){
        echo "stop\t|\tprocess is doing\n";exit();
    }else{
        return true;
    }
}

function dir_check($root_data_dir,$file_name){
    $dirname = dirname($file_name);
    $dir_path_arr = explode('/',trim($dirname,'/'));

    foreach($dir_path_arr as $k=>$v){
        $num = $k + 1;
        $tmp_path_arr = array_slice($dir_path_arr,0,$num);
        $dir = $root_data_dir.'/'.join('/',$tmp_path_arr);
        if(!is_dir($dir)){
            mkdir($dir,0755);
        }
    }

    if(is_dir($root_data_dir.'/'.ltrim($dirname,'/')))
        return true;
    else
        return false;
}


function download_invoice($id,$file_name){
    global $_db;
    if(is_file($file_name))
        return true;

    $sql = "SELECT a.*,b.`Alias`,c.PayPal,c.Email FROM payments AS a LEFT JOIN publisher_account AS b ON a.`Site` = b.`ApiKey` LEFT JOIN publisher AS c ON a.`PublisherId` = c.`ID` WHERE a.`ID` = ".intval($id);
    $row_payments = $_db->getFirstRow($sql);
    
    $PaidDate = $row_payments['PaidDate'];
    $site = $row_payments['Site'];

    $sql = "SELECT a.*,b.Visited,b.PublishTracking,b.SID FROM payments_pending_invoice AS a LEFT JOIN rpt_transaction_unique AS b ON a.BRID = b.BRID WHERE a.PaidDate = '".$PaidDate."' AND a.site = '".$site."' ORDER BY a.CreatedDate DESC";
    $rows = $_db->getRows($sql);
    if(empty($rows))
        return array();

    $data = array();
    $total_transactions = count($rows);
    $total_earnings = 0;
    $total_refunds = 0;
    $total_net_earnings = 0;

    $domainids = array();
    $sids = array();
    $site_alias = $row_payments['Alias'];
    $publisherid = $row_payments['PublisherId'];

    #Created    Advertiser      Transaction ID  Earnings        SID     Status  Site    ClickPage
    foreach($rows as $k=>$v){
        $tmp = array();
        $tmp['Created'] = $v['Visited'];
        $tmp['Advertiser'] = '';
        $tmp['Transaction ID'] = $v['BRID'];
        $tmp['Earnings'] = number_format($v['Commission'],2,'.','');
        $tmp['SID'] = $v['PublishTracking'];
        $tmp['sessionid'] = $v['SID'];
        $tmp['domainId'] = $v['domainId'];
        $tmp['Status'] = "CONFIRMED";
        $tmp['ClickPage'] = '';

        if($tmp['Earnings'] > 0){
            $total_earnings += $tmp['Earnings'];
        }

        if($tmp['Earnings'] < 0){
            $total_refunds += $tmp['Earnings'];
        }

        $data[] = $tmp;
        $domainids[] = $v['domainId'];
        $sids[] = $v['SID'];
    }
    $total_net_earnings = $total_earnings + $total_refunds;
    $domainids = array_unique($domainids);
    $sids = array_unique($sids);


    $sql = "SELECT MIN(CreatedDate) as start_date,MAX(CreatedDate) as end_date FROM payments_pending_invoice WHERE PaidDate = '".$PaidDate."' AND site = '".$site."'";
    $row_date = $_db->getFirstRow($sql);
    $start_date = $row_date['start_date'];
    $end_date = $row_date['end_date'];
    $sql = "SELECT sessionid,pageUrl FROM bd_out_tracking WHERE createddate >= '".$start_date."' AND createddate <= '".$end_date."' AND sessionid IN ('".join("','",$sids)."')";
    $rows_sids = $_db->getRows($sql);

    $map_sid_pageurl = array();
    foreach($rows_sids as $k=>$v){
        $map_sid_pageurl[$v['sessionid']]  =  $v['pageUrl'];
    }

    $sql = "SELECT a.*,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) AS storeName FROM r_store_domain AS a LEFT JOIN store AS b ON a.`StoreId` = b.`ID` WHERE a.`DomainId` IN (".join(',',$domainids).")";
    $rows_domainids = $_db->getRows($sql);

    $map_domainid_store = array();
    foreach($rows_domainids as $k=>$v){
        $map_domainid_store[$v['DomainId']] = $v['storeName'];
    }

    foreach($data as $k=>$v){
        if(isset($map_sid_pageurl[$v['sessionid']])){
            $data[$k]['ClickPage'] = $map_sid_pageurl[$v['sessionid']];
        }
        if(isset($map_domainid_store[$v['domainId']])){
            $data[$k]['Advertiser'] = $map_domainid_store[$v['domainId']];
        }

        unset($data[$k]['domainId']);
        unset($data[$k]['sessionid']);
    }

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle('Invoice');
    $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');

    $objPHPExcel->getActiveSheet()->setCellValue('A1', date('M j, Y',strtotime($start_date)).' - '.date('M j, Y',strtotime($end_date)));
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objPHPExcel->getActiveSheet()->setCellValue('A2','Total Affiliate Transactions');
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('B2',$total_transactions);
    
    $objPHPExcel->getActiveSheet()->setCellValue('A4','Total Earnings');
    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('B4',$total_earnings);

    $objPHPExcel->getActiveSheet()->setCellValue('A5','Refunds & Cancellations');
    $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('B5',$total_refunds);
    $BStyle = array(
        'borders' => array(
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_DOUBLE
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('A5:B5')->applyFromArray($BStyle);

    $objPHPExcel->getActiveSheet()->setCellValue('A6','Net Earnings');
    $objPHPExcel->getActiveSheet()->getStyle('A6')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('B6',$total_net_earnings);

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(28);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(24);
    $objPHPExcel->getActiveSheet()->getStyle('B4:B6')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_RED);

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->setTitle('Transactions Report');
    $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Reporting Period: '.date('M j, Y',strtotime($start_date)).' - '.date('M j, Y',strtotime($end_date)));
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('A2','Created');
    $objPHPExcel->getActiveSheet()->setCellValue('B2','Advertiser');
    $objPHPExcel->getActiveSheet()->setCellValue('C2','Transaction ID');
    $objPHPExcel->getActiveSheet()->setCellValue('D2','Earnings');
    $objPHPExcel->getActiveSheet()->setCellValue('E2','SID');
    $objPHPExcel->getActiveSheet()->setCellValue('F2','Status');
    $objPHPExcel->getActiveSheet()->setCellValue('G2','ClickPage');

    $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
    $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(27);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);

    $key_st = $key = 3;
    foreach($data as $k=>$v){
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$key,$v['Created']);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$key,$v['Advertiser']);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$key,$v['Transaction ID']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$key,$v['Earnings']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$key,$v['SID']);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$key,$v['Status']);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$key,$v['ClickPage']);
        $key++;
    }
    $key_end = $key;

    $objPHPExcel->getActiveSheet()->getStyle('D'.$key_st.':D'.$key_end)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_RED);
    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save($file_name);
}
?>
