<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();

function download_payments(){
    global $file_temp,$_db;
    $url = 'https://www.tagadmin.asia/affiliate_invoice.html';
    _curl($url);

    $pageHtml = file_get_contents($file_temp);
    preg_match('/<form action="affiliate_invoice.html".*?<\/form>/is',$pageHtml,$mPage);
    if(empty($mPage) || !isset($mPage[0])){
       echoMsg('can not get table html','crawl payments','fail');exit();
    }

    preg_match_all('/(<tr   >|<tr   class="even"  >).*?<\/tr>/is',$mPage[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])){
       echoMsg('can not get tr html','crawl payments','fail');exit();
    }

    $payments_arr = array();
    foreach($mTr[0] as $tr){
        preg_match_all('/<td>(.*?)<\/td>/is',$tr,$mTd);
        if(empty($mTd) || !isset($mTd[0])){
            echoMsg('can not get td html','crawl payments','fail');exit();
        }
        preg_match('/iId=(.*?)\'/is',$mTd[1][0],$mID);
        if(empty($mID) || !isset($mID[0])){
            echoMsg('can not get ID','crawl payments','fail');exit();
        }
        $IDinNetwork = $mID[1];
        $date = $mTd[1][1];
        list($d,$m,$y) = explode('/',$date);
        $Remit_date = join('-',array($y,$m,$d));
        preg_match('/([\d|,|\.]+)/is',$mTd[1][5],$mAmount);
        $Remit_amount = $mAmount[1];
        $Remit_currency = 'USD';

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
    global $_db;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        _download_invoice($payments);
    }
}

function _download_invoice($payments){
    global $file_temp;
    $url = "https://www.tagadmin.asia/affiliate_invoice_details_export.html?iId=".$payments['IDinNetwork']."&exportType=csv";
    _curl($url);

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
                        strstr($data[0], "Transaction ID")
                        && strstr($data[1], "Merchant")
                        && strstr($data[2], "Program")
                        && strstr($data[3], "Source")
                        && strstr($data[4], "Sub ID")
                        && strstr($data[5], "Order Date")
                        && strstr($data[6], "Order Value (USD)")
                        && strstr($data[7], "Commission (USD)")
                        && strstr($data[8], "Approval Date")
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 2');
                        fclose($handle);
                        exit();
                    }
                }

                $date = trim($data[8]);
                if(empty($date)){
                    continue;
                }
                list($d,$m,$y) = explode('/',$date);
                $date_new = join('-',array($y,$m,$d));
                $tradeid = trim($data[0]);
                $sales = str_replace(',','',trim($data[6]));
                $comm = str_replace(',','',trim($data[7]));
                $curr = 'USD';
                $sid = trim($data[4]);
                $tmp = array(
                    'date'=>$date_new,
                    'orderid'=>$tradeid,
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
    $LoginUrl = 'https://www.tagadmin.asia/login.html';

    $postArr['username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['password'] = AFFILIATE_PASS;#AFFILIATE_PASS;

    $loginHtml = _curl($LoginUrl,$postArr,1);

    $keywords = 'logout';
    if(strpos($loginHtml,$keywords)){
        return true;
    }else{
        return false;
    }
}

function _curl($url,$post=array(),$return=false,$tmp_file_name=false){
    global $file_cook,$file_temp;

    $file = $tmp_file_name?$tmp_file_name:$file_temp;

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

    if(!empty($post)){
        $post_tmp = array();
        foreach($post as $k=>$v){
            $post_tmp[] = $k.'='.urlencode($v);
        }
        $post_query = join('&',$post_tmp);
        curl_setopt($ch, CURLOPT_POST , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $post_query);
        print_r("curl_post :".$post_query."\n");
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
