<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';
define('ACCOUNT_ID','340252');

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

#download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();

function download_payments(){
    global $file_temp,$_db;
    $url = 'http://afuk.affiliate.affiliatefuture.co.uk/finance/payments.aspx';
    $curl_data = array();
    _curl($url,$curl_data);

    $pageHtml = file_get_contents($file_temp);
    preg_match('/<table class="aftable payments".*?<\/table>/is',$pageHtml,$mPage);
    if(empty($mPage) || !isset($mPage[0])){
       echoMsg('can not get table html','crawl payments','fail');exit();
    }

    preg_match_all('/<tr style="background-color:White;">.*?<\/tr>/is',$mPage[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])){
       echoMsg('can not get tr html','crawl payments','fail');exit();
    }
    $payments_arr = array();
    foreach($mTr[0] as $tr){
        preg_match_all('/<td align="left">(.*?)<\/td>/is',$tr,$mTd);

        if(empty($mTd) || !isset($mTd[0])){
            echoMsg('can not get td html','crawl payments','fail');exit();
        }
        $IDinNetwork = trim(strip_tags($mTd[1][0]));
        $date = trim(strip_tags($mTd[1][1]));
        list($d,$m,$y) = explode('/',$date);
        $Remit_date = join('-',array('20'.$y,$m,$d));
        preg_match('/([\d|,|\.]+)/is',strip_tags($mTd[1][3]),$mAmount);
        $Remit_amount = str_replace(',','',$mAmount[1]);
        $Remit_currency = 'GBP';

        $payments_arr[] = array(
            'NetworkID'=>AFFID,
            'IDinNetwork'=>$IDinNetwork,
            'Remit_date'=>$Remit_date,
            'Remit_amount'=>$Remit_amount,
            'Remit_currency'=>$Remit_currency,
        );
    }
    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function download_invoice(){
    global $file_temp,$_db;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);
    $key = _array_column($rows,'IDinNetwork');
    $payments_arr = array_combine($key,$rows);

    $url = 'http://afuk.affiliate.affiliatefuture.co.uk/finance/payments.aspx';
    $curl_data = array();
    _curl($url,$curl_data);

    $pageHtml = file_get_contents($file_temp);
    
    preg_match('/id="__VIEWSTATE" value="(.*?)"/is',$pageHtml,$mVIEWSTATE);
    if(empty($mVIEWSTATE) || !isset($mVIEWSTATE[0])){

    }
    $__VIEWSTATE = $mVIEWSTATE[1];
    preg_match('/id="__VIEWSTATEGENERATOR" value="(.*?)"/is',$pageHtml,$mVIEWSTATEGENERATOR);
    if(empty($mVIEWSTATEGENERATOR) || !isset($mVIEWSTATEGENERATOR[0])){

    }
    $__VIEWSTATEGENERATOR = $mVIEWSTATEGENERATOR[1];
    
    preg_match('/<table class="aftable payments".*?<\/table>/is',$pageHtml,$mPage);
    if(empty($mPage) || !isset($mPage[0])){
       echoMsg('can not get table html','crawl payments','fail');exit();
    }       
            
    preg_match_all('/<tr style="background-color:White;">.*?<\/tr>/is',$mPage[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])){
       echoMsg('can not get tr html','crawl payments','fail');exit();
    }
    foreach($mTr[0] as $tr){
        preg_match_all('/<td align="left">(.*?)<\/td>/is',$tr,$mTd);
    
        if(empty($mTd) || !isset($mTd[0])){
            echoMsg('can not get td html','crawl payments','fail');exit();
        } 
        $IDinNetwork = trim(strip_tags($mTd[1][0]));
        preg_match('/dg1_ctl(\d+)_lbl_ReceiptNumber/is',$mTd[1][0],$mPos);
        $payments_arr[$IDinNetwork]['pos'] = $mPos[1];
    }
    foreach($payments_arr as $payments){
        _download_invoice($payments,$__VIEWSTATE,$__VIEWSTATEGENERATOR);
    }

}

function _download_invoice($payments,$__VIEWSTATE,$__VIEWSTATEGENERATOR){
    global $file_temp;
    $url = "http://afuk.affiliate.affiliatefuture.co.uk/finance/payments.aspx";
    $postArr = array();
    $postArr['__EVENTTARGET'] = '';
    $postArr['__EVENTARGUMENT'] = '';
    $postArr['__VIEWSTATE'] = $__VIEWSTATE;
    $postArr['__VIEWSTATEGENERATOR'] = $__VIEWSTATEGENERATOR;
    $postArr['dg1$ctl'.$payments['pos'].'$imgCSV.x'] = '20';
    $postArr['dg1$ctl'.$payments['pos'].'$imgCSV.y'] = '20';
    $curl_data = array('post'=>$postArr);
    _curl($url,$curl_data);

    $csv = array();
    $count = 0;
    $sum = 0;
    $data_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    file_put_contents($data_file,'');

    if(file_exists($file_temp)){


        if (($handle = fopen($file_temp, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;
                if($line == 1){

                    if(
                        strstr($data[0], "transactionID")
                        && strstr($data[1], "originalTransactionDate")
                        && strstr($data[2], "transactionDate")
                        && strstr($data[3], "paymentDate")
                        && strstr($data[4], "PaymentID")
                        && strstr($data[5], "merchantName")
                        && strstr($data[6], "TransactionType")
                        && strstr($data[7], "OrderID")
                        && strstr($data[8], "OrderValue")
                        && strstr($data[9], "CommissionAmount")
                        && strstr($data[10], "PaymentAmount")
                        && strstr($data[11], "clickReference")
                        && strstr($data[12], "referrer")
                        && strstr($data[13], "UntrackedAffiliateEnquiryID")
                        && strstr($data[14], "UntrackedCustomerRef")
                        && strstr($data[15], "UntrackedUserID")
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 2');
                        fclose($handle);
                        exit();
                    }
                }

                $datetime = trim($data[2]);
                if(empty($datetime)){
                    continue;
                }
                
                list($date,$time) = explode(' ',$datetime);
                list($d,$m,$y) = explode('/',$date);
                $date_new = join('-',array($y,$m,$d)).' '.$time;
                $tradeid = trim($data[0]);
                $sales = str_replace(',','',trim($data[8]));
                $comm = str_replace(',','',trim($data[9]));
                $curr = $payments['Remit_currency'];
                $sid = trim($data[11]);
                $tmp = array(
                    'date'=>$date_new,
                    'tradeid'=>$tradeid,
                    'sales'=>$sales,
                    'commission'=>$comm,
                    'sid'=>$sid,
                    'currency'=>$curr,
                    );
                file_put_contents($data_file,join("\t",$tmp)."\n",FILE_APPEND);
                $count++; 
                $sum+=$comm;
            }

            fclose($handle);
        }

    }
    print_r("doing , ID = ".$payments['ID'].", paiddate= ".$payments['Remit_date'].",paysum = ".$payments['Remit_amount'].", count= ".$count." ,sum = ".$sum.", file = ".$data_file." \n");
}

function login(){
    $LoginUrl = 'http://afuk.affiliate.affiliatefuture.co.uk/login.aspx';
    $loginPage = _curl($LoginUrl,array(),1);
    preg_match('/id="__VIEWSTATE" value="(.*?)"/is',$loginPage,$mVIEWSTATE);
    if(empty($mVIEWSTATE) || !isset($mVIEWSTATE[0])){
    
    }
    $__VIEWSTATE = $mVIEWSTATE[1];
    preg_match('/id="__VIEWSTATEGENERATOR" value="(.*?)"/is',$loginPage,$mVIEWSTATEGENERATOR);
    if(empty($mVIEWSTATEGENERATOR) || !isset($mVIEWSTATEGENERATOR[0])){
    
    }
    $__VIEWSTATEGENERATOR = $mVIEWSTATEGENERATOR[1];
     
    $postArr['txtUsername'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['txtPassword'] = AFFILIATE_PASS;#AFFILIATE_PASS;
    $postArr['__VIEWSTATE'] = $__VIEWSTATE;
    $postArr['__VIEWSTATEGENERATOR'] = $__VIEWSTATEGENERATOR;
    $postArr['btnLogin'] = "LOGIN";
    $curl_data = array();
    $curl_data['post'] = $postArr;

    $loginHtml = _curl($LoginUrl,$curl_data,1);

    $keywords = ACCOUNT_ID;
    if(strpos($loginHtml,$keywords)){
        return true;
    }else{
        return false;
    }
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
    curl_setopt($ch, CURLOPT_USERAGENT      , 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36');
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
