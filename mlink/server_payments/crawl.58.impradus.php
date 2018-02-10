<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('TOKEN', "IRYrCKQmWhbn245060XeRFfN3HQ8QboRi1");
define('ACCSID', "vN3jiEFiYDrJ6rV7GSFcDk9dcwfgGyKE");
define("REST_SERVER", "https://{AuthToken}:{AccountSid}@api.impactradius.com");
define("ACCOUNT_ID",'245060');

//download_payments();
download_invoice();
echo 'done';exit();

function download_payments(){
    global $file_temp,$_db;
    $url = "https://".TOKEN.":".ACCSID."@api.impactradius.com/Mediapartners/".TOKEN."/Reports/mp_paystub_history";
    _curl($url);

    $xml = simplexml_load_file($file_temp);
    $tmp = json_encode($xml);
    $xmlArr = json_decode($tmp,true);

    $payments_arr = array();
    foreach($xmlArr['Records']['Record'] as $k=>$v){

        $date = $v['Date'];
        preg_match('/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(.{3}).*/is',$date,$m);
        $date_new = date('Y-m-d H:i:s',strtotime($m[1])-intval($m[2])*3600);
        
        $payments_arr[] = array(
            'NetworkID'=>AFFID,
            'IDinNetwork'=>$v['Paystub_Id'],
            'Remit_date'=>$date_new,
            'Remit_currency'=>'USD',
            'Remit_amount'=>str_replace(',','',trim($v['Amount'])),
        );
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
    $url = REST_SERVER."/Mediapartners/{AuthToken}/Reports/action_listing_withdrawal?Paystub_Id={Paystub_Id}";
    $param_data = array(
        '{AuthToken}'=>TOKEN,
        '{AccountSid}'=>ACCSID,
        '{Paystub_Id}'=>$payment['IDinNetwork'],
    );
    $url = str_replace(array_keys($param_data),array_values($param_data), $url);
    _curl($url);
    $xml = simplexml_load_file($file_temp);
    $tmp = json_encode($xml);
    $xmlArr = json_decode($tmp,true);
    
    $count = 0;
    $sum = 0;
    $file_invoice = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payment['ID'].'_'.date('ymd',strtotime($payment['Remit_date'])).'.dat';
    file_put_contents($file_invoice,'');
    foreach($xmlArr['Records']['Record'] as $k=>$v){
        $sid = '';
        if(!empty($v['PubSubId1'])){
            $sid = trim($v['PubSubId1']);
        }elseif(!empty($v['PubSubId2'])){
            $sid = trim($v['PubSubId2']);
        }elseif(!empty($v['PubSubId3'])){
            $sid = trim($v['PubSubId3']);
        }
         
        $tmp_line_arr = array(
            date('Y-m-d H:i:s', strtotime(trim($v['ActionDate']))),
            trim($v['ActionId']),
            str_replace(',','',trim($v['SaleAmount'])),
            str_replace(',','',trim($v['Earnings'])),
            $sid,
            'USD',
        );
        $count++;
        $sum += str_replace(',','',trim($v['Earnings']));
        file_put_contents($file_invoice,join("\t",$tmp_line_arr)."\n",FILE_APPEND);
    }
    echo "count = ".$count.", sum = ",$sum.", file = ".$file_invoice."\n";
}

function login(){
    $LoginUrl = 'https://member.impactradius.com/secure/login.user?';

    $postArr['j_username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['j_password'] = AFFILIATE_PASS;#AFFILIATE_PASS;

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
