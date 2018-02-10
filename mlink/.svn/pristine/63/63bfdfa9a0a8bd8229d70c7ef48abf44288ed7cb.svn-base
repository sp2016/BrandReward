<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';


download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();


function download_payments(){
    global $_db;
    $payments_arr = array(
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201707','Remit_date'=>'2017-08-01','Remit_amount'=>'1197.76','Remit_currency'=>'USD'),
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201708','Remit_date'=>'2017-09-01','Remit_amount'=>'1346.71','Remit_currency'=>'USD'),
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201709','Remit_date'=>'2017-10-01','Remit_amount'=>'1015.56','Remit_currency'=>'USD'),
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201710','Remit_date'=>'2017-11-01','Remit_amount'=>'1420.24','Remit_currency'=>'USD'),
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201711','Remit_date'=>'2017-12-01','Remit_amount'=>'1574.72','Remit_currency'=>'USD'),
        array('NetworkID'=>AFFID,'IDinNetwork'=>'201712','Remit_date'=>'2018-01-01','Remit_amount'=>'1979.13','Remit_currency'=>'USD'),
    );
    
    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function download_invoice(){
    global $_db;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        _download_invoice($payments);
    }

}

function _download_invoice($payments){
    global $file_temp,$file_cook;
    $file_temp = PATH_TMP.'/glopss_'.$payments['IDinNetwork'].'.csv';
    $count = 0;
    $sum = 0;
    $data_file = PATH_DATA.'/'.AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    $fdw = fopen($data_file, 'w+');

    if (($handle = fopen($file_temp, "r")) !== FALSE) {
        $line = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $line++;
        
            if(empty($data) || !isset($data[1]) || empty($data[1])){
                continue;
            }
            if($line == 1){
                if(strstr($data[0], 'Transaction ID')
                    && strstr($data[1], 'Merchant')
                    && strstr($data[2], 'Affiliate info')
                    && strstr($data[3], 'Date')
                    && strstr($data[4], 'MVD')
                    && strstr($data[5], 'Sale Amount $')
                    && strstr($data[6], 'Payout $')
                    && strstr($data[7], 'Resrvation ID')
                ){
                    continue;
                }else{
                    print_r('error:title format wrong 1'."\n");
                    fclose($handle);
                    return;
                }
            }

            $tmp = array(
                'date'=>trim($data[3]),
                'transaction_id'=>trim($data[0]),
                'sales'=>str_replace(',','',trim($data[5])),
                'commission'=>str_replace(',','',trim($data[6])),
                'sid'=>trim($data[2]),
                'currency'=>$payments['Remit_currency'],
                );
            $count++;
            $sum+=$tmp['commission'];
            fwrite($fdw , join("\t",$tmp)."\n");
        }
    } 
    fclose($handle);
    fclose($fdw);
    
    print_r("doing , ID = ".$payments['ID'].", paiddate= ".$payments['Remit_date'].",paysum = ".$payments['Remit_amount'].", count= ".$count." ,sum = ".$sum.", file = ".$data_file." \n");
}


function get_date_range_arr($startDate,$endDate,$range,$format='Y-m-d'){
    $startDate = date('Y-m-d',strtotime($startDate));
    $endDate = date('Y-m-d',strtotime($endDate));
    $d = new DateTime($startDate);

    $return_d = array();

    while($d->format('Y-m-d') <= $endDate){

        $start_dt = $d->format($format);
        $d->modify('+'.$range);
        if($d->format('Y-m-d') > $endDate){
            $end_dt = date($format,strtotime($endDate));
        }else{
            $end_dt = $d->format($format);
        }
        $d->modify('+1 day');

        $return_d[] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt);
    }

    return $return_d;
}


function _curl($url,$data=array(),$return=false){
    global $file_cook,$file_temp;

    $file = isset($data['file'])?$data['file']:$file_temp;
    
    $fw = fopen($file, 'w+');

    print_r("curl :".$url."\n");
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER         , false);
    curl_setopt($ch, CURLOPT_NOBODY         , false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    curl_setopt($ch, CURLOPT_COOKIEJAR      , $file_cook);
    curl_setopt($ch, CURLOPT_COOKIEFILE     , $file_cook);
    curl_setopt($ch, CURLOPT_USERAGENT      , 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');
    curl_setopt($ch, CURLOPT_FILE           , $fw);
    curl_setopt($ch, CURLOPT_REFERER        , $url);

    if(isset($data['post']) && !empty($data['post'])){
        $post = $data['post'];
        $post_tmp = array();
        foreach($post as $k=>$v){
            $post_tmp[] = $k.'='.urlencode($v);
        }
        $post_query = join('&',$post_tmp);
        curl_setopt($ch, CURLOPT_POST , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $post_query);
        print_r("curl_post :".$post_query."\n");
    }

    if(isset($data['header']) && !empty($data['header'])){
        $header = $data['header'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    $rs = curl_exec($ch);
    curl_close($ch);
    fclose($fw);

    if($return){
        return file_get_contents($file);
    }else{
        return $return;
    }
}

?>
