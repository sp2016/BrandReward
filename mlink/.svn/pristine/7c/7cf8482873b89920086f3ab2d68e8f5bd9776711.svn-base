<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';
define('ACCOUNT_ID','192821');

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

download_payments();
//echo 2;exit();
download_invoice();
echo 1;exit();

function download_payments(){
    global $file_temp,$_db;
    $payments_url = "http://www.webgains.com/publisher/".ACCOUNT_ID."/account/payment/history";
    _curl($payments_url);
    
    $content = file_get_contents($file_temp);
    preg_match('/<tbody>.*?<\/tbody>/is',$content,$mTbody);
    if(empty($mTbody) || !isset($mTbody[0])){
        echoMsg('get payments history fail','get payments','fail');exit();
    }
    preg_match_all('/<tr>.*?<\/tr>/is',$mTbody[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])) {
        echoMsg('get payments tr fail','get payments','fail');exit();
    }

    $payments_arr = array();
    foreach($mTr[0] as $k=>$tr){
        preg_match_all('/<td>.*?<\/td>/is',$tr,$mTd);
        if(empty($mTd) || !isset($mTd[0])) {
            echoMsg('get payments td fail','get payments','fail');exit();
        }
        $date = strip_tags(trim($mTd[0][0]));
        $amount_str = strip_tags(trim($mTd[0][1]));
        preg_match('/(.*?)(\d.*)/i',$amount_str,$mAmount);
        preg_match('/payment=(\d+)"/is',trim($mTd[0][4]),$mpid);
        $currency = 'USD';
        switch(trim($mAmount[1])){
            case '&pound;':
                $currency = 'GBP';
            break;
            case '&euro;':
                $currency = 'EUR';
            break;
            case '$':
                $currency = 'USD';
            break;
            case 'AU$':
                $currency = 'AUD';
            break;
        }
        $amount = str_replace(',','',trim($mAmount[2]));
        $paymentsid = $mpid[1];
        list($d,$m,$y) = explode('/',$date);
        $date_new = '20'.$y.'-'.$m.'-'.$d;
        $payments_arr[] = array('NetworkID'=>AFFID,'IDinNetwork'=>$paymentsid,'Remit_date'=>date('Y-m-d',strtotime($date_new)),'Remit_currency'=>$currency,'Remit_amount'=>$amount);
    }
    
    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function download_invoice(){
    global $_db;
    $sql = "SELECT * FROM payments_network_remit WHERE NetworkID = ".AFFID." AND GetInvoice = 'no'";
    $rows = $_db->getRows($sql);

    foreach($rows as $k=>$payment){
        _download_invoice($payment);
    }
}

function _download_invoice($payment){
    global $file_temp;
    $url = "http://www.webgains.com/publisher/".ACCOUNT_ID."/report/index/view/type/transaction/for/transaction-earning?payment=".$payment['IDinNetwork'];
    _curl($url);

    $url = "http://www.webgains.com/affiliates/report.html?raw=changeformat&format=csv&ref=ae&key=31636cc48a7cb972cea3f93b749b6d6f&period=&scale=itemised";
    _curl($url);

    $invoice_tmp = $file_temp;
    $invoice_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payment['ID'].'_'.date('ymd',strtotime($payment['Remit_date'])).'.dat';
    file_put_contents($invoice_file,'');
    $sum = 0;
    $count = 0;
    if (($handle = fopen($invoice_tmp, "r")) !== FALSE) {
        $line = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $line++;
            if($line == 1){
                if(
                    strstr($data[0], 'Program')
                    && strstr($data[1], 'Program-programID')
                    && strstr($data[2], 'Date & Time')
                    && strstr($data[3], 'Web Site')
                    && strstr($data[4], 'Web Site-campaignID')
                    && strstr($data[5], 'Sale-currency')
                    && strstr($data[6], 'Sale')
                    && strstr($data[7], 'Commission-currency')
                    && strstr($data[8], 'Commission')
                    && strstr($data[9], 'Click reference')
                    && strstr($data[10], 'Clickthru Time')
                    && strstr($data[11], 'Product ID')
                    && strstr($data[12], 'Status')
                    && strstr($data[13], 'Status-status info')
                ){
                    continue;
                }else{
                    echo "invoice csv title wrong";
                }
            }
            if(empty($data[2]) || !preg_match('/\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}/',$data[2]))
                continue;

            preg_match('/(\d{2})\/(\d{2})\/(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$data[2],$m);
            $c_date = '20'.$m[3].'-'.$m[2].'-'.$m[1].' '.$m[4].':'.$m[5].':'.$m[6];
            $sid = $data[9];
            $sales = $data[6];
            $comm = $data[8];
            $curr = $data[7];
            
            $sum+=$comm;
            $count++;
            $tmp = join("\t",array($c_date,$sid,$sales,$comm,$sid,$curr));
            file_put_contents($invoice_file,$tmp."\n",FILE_APPEND);
        }
        fclose($handle);
    }

    print_r("sum = ".$sum." , count = ".$count.", file = ".$invoice_file."\n");
}

function login(){
    $LoginUrl = 'https://www.webgains.com/loginform.html?action=login';

    $postArr['user_type'] = 'affiliateuser';
    $postArr['username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['password'] = AFFILIATE_PASS;#AFFILIATE_PASS;
    $postArr['screenwidth'] = '';
    $postArr['screenheight'] = '';
    $postArr['colourdepth'] = '';

    $loginHtml = _curl($LoginUrl,$postArr,1);

    $keywords = ACCOUNT_ID;
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
