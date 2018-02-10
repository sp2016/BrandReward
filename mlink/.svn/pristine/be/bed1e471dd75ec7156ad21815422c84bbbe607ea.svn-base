<?php

class Payment extends LibFactory
{
    function getHistoryPayment(){
        
        $site = array();
        $siteText = '';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                $site[] = $temp['ApiKey'];
            }
        }
        $siteText = implode('","', $site);
        
        $sql = 'SELECT p.`ID`,p.`PaidDate`,p.Amount,p.Currency,p.Paymenttype,p.PaymentDetail,p.Status,p.InvoiceFile, pa.`Domain` 
             FROM payments p LEFT JOIN publisher_account pa ON pa.`ApiKey` = p.`Site` where p.`Site` in ("'.$siteText.'") and p.`Status` = "succ" ORDER BY PaidDate desc';
        $rs = $this->getRows($sql);
        foreach ($rs as $key=>$value){
            $rs[$key]['Amount'] = number_format($value['Amount'],2);
            if($value['Paymenttype'] == 'bank'){
                $rs[$key]['Paymenttype'] = 'wire transfer';
            }
            if($value['Status'] == 'succ'){
                $rs[$key]['Status'] = 'success';
            }
            if($value['InvoiceFile']!='' && file_exists("/app/site/admin.brandreward.com/web/data/payments{$value['InvoiceFile']}")){
                $rs[$key]['FileExist'] = '1';
            }else {
                $rs[$key]['FileExist'] = '0';
            }
        }
        return $rs;
    }
    
    function downloadInvoice($id){
        $sql = "SELECT * FROM payments where ID = '".intval($id)."' ";
        $rs = $this->getRow($sql);
        if(!empty($rs) && $rs['InvoiceFile']!=''){
            if($_SESSION['u']['ID'] == $rs['PublisherId']){
                $filename = "/app/site/admin.brandreward.com/web/data/payments{$rs['InvoiceFile']}";
                //检查文件是否存在
                if (!file_exists($filename)) {
                    header('HTTP/1.1 404 Not Found');
                    echo "Error: 404 Not Found.(server file path error)";
                } else {
                    /* //输入文件标签
                    ob_end_clean();
                    Header ( "Content-type: application/octet-stream" );
                    Header ( "Accept-Ranges: bytes" );
                    Header ( "Accept-Length: " . filesize ($filename) );
                    Header ( "Content-Disposition: attachment; filename=".basename($filename));
                    //输出文件内容
                    //读取文件内容并直接输出到浏览器
                    echo file_get_contents($filename);
                    exit; */
                    
                    //打开文件
                    $file  =  fopen($filename, "rb");
                    ob_end_clean();
//                    Header("Content-Type: application/vnd.ms-excel; charset=utf8");
                    Header( "Content-type:  application/octet-stream ");
                    Header( "Accept-Ranges:  bytes ");
                    header('Content-Transfer-Encoding: binary');
                    Header( "Content-Disposition:  attachment;  filename=".basename($filename));
                    $contents = "";
                    while (!feof($file)) {
                        $contents .= fread($file, 8192);
                    }
                    echo $contents;
                    fclose($file);
                    exit;
                }
            }else {
                header('HTTP/1.1 404 Not Found');
                echo "Error: 404 Not Found.(server file path error)";
            }
        }else {
            header('HTTP/1.1 404 Not Found');
            echo "Error: 404 Not Found.(server file path error)";
        }
        return $rs;
    }
    
}
