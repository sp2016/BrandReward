<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';
define('ACCOUNT_ID','157460');

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

$conf = array('setCompanyID'=>4,'setCustomerSiteIDs'=>261296,'NetworkID'=>65);
/*
$config = array(
'AT'=>array('setCompanyID'=>18,'setCustomerSiteIDs'=>264723,'NetworkID'=>425),
'BE'=>array('setCompanyID'=>11,'setCustomerSiteIDs'=>265244,'NetworkID'=>2029),
'FR'=>array('setCompanyID'=>10,'setCustomerSiteIDs'=>265014,'NetworkID'=>427),
'DE'=>array('setCompanyID'=>4,'setCustomerSiteIDs'=>261296,'NetworkID'=>65),
#'IN'=>array('setCompanyID'=>36,'setCustomerSiteIDs'=>264723,'NetworkID'=>52),
'IT'=>array('setCompanyID'=>8,'setCustomerSiteIDs'=>294350,'NetworkID'=>2026),
'NL'=>array('setCompanyID'=>3,'setCustomerSiteIDs'=>278842,'NetworkID'=>2027),
'RU'=>array('setCompanyID'=>19,'setCustomerSiteIDs'=>265438,'NetworkID'=>2028),
'CH'=>array('setCompanyID'=>20,'setCustomerSiteIDs'=>297750,'NetworkID'=>426),
#'UA'=>array('setCompanyID'=>34,'setCustomerSiteIDs'=>265245,'NetworkID'=>52),
'GB'=>array('setCompanyID'=>5,'setCustomerSiteIDs'=>283029,'NetworkID'=>52)
);
*/

download_payments($conf);
download_invoice();


function download_payments($conf){
    global $file_temp,$_db;
    $objD = new DateTime();
    list($ty,$tm,$td) = explode('-',$objD->format('Y-n-j'));
    $objD->modify('-1 year');
    list($fy,$fm,$fd) = explode('-',$objD->format('Y-n-j'));
    $url = "https://affiliate.tradetracker.com/financial/invoice?desc=1&outputType=1&c=&r=0&setCompanyID=".$conf['setCompanyID']."&setCustomerSiteIDs=".$conf['setCustomerSiteIDs']."&p[t]=-1&p[fd]=".$fd."&p[fm]=".$fm."&p[fy]=".$fy."&p[td]=".$td."&p[tm]=".$tm."&p[ty]=".$ty."&submit_period_p=Apply&offset=0&xmlhttp=1&rand=198f172";
    _curl($url);

    $tableHtml = file_get_contents($file_temp);
    preg_match('/&lt;tbody&gt;.*?&lt;\/tbody&gt;/is',$tableHtml,$mTbody);
    if(empty($mTbody) || !isset($mTbody[0])){
       return; 
    }

    preg_match_all('/&lt;tr&gt;.*?&lt;\/tr&gt;/is',$mTbody[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])){
        return;
    }

    $payments_arr = array();
    foreach($mTr[0] as $k=>$tr){
        preg_match_all('/&lt;td.*?&lt;\/td&gt;/is',$tr,$mTd);
        if(empty($mTd) || !isset($mTd[0]))
            return;

        preg_match('/value=&quot;(\d+)&quot;/i',$mTd[0][0],$mID);
        $IDinNetwork = $mID[1];

        preg_match('/\d{2}\/\d{2}\/\d{4}/i',$mTd[0][2],$mDate);
        $date = $mDate[0];
        list($d,$m,$y) = explode('/',$date);
        $date_new = join('-',array($y,$m,$d));

        preg_match('/((\d|\.|,)+)&lt;/is',$mTd[0][3],$mCurr);
        $amount = str_replace(',','',$mCurr[1]);
        
        $currency = "USD";
        if(preg_match('/£/is',$mTd[0][3])){
            $currency = 'GBP';
        }elseif(preg_match('/€/is',$mTd[0][3])){
            $currency = 'EUR';
        }elseif(preg_match('/р/is',$mTd[0][3])){
            $currency = 'RUB';
        }elseif(preg_match('/CHF/is',$mTd[0][3])){
            $currency = 'CHF';
        }

        $payments_arr[] = array(
            'NetworkID'=>$conf['NetworkID'],
            'IDinNetwork'=>$IDinNetwork,
            'Remit_date'=>$date_new,
            'Remit_amount'=>$amount,
            'Remit_currency'=>$currency,
        );
    }
    if(empty($payments_arr))
        return;

    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function _download_payments($conf){
    global $file_temp,$_db;
    $objD = new DateTime();
    list($ty,$tm,$td) = explode('-',$objD->format('Y-n-j'));
    $objD->modify('-1 year');
    list($fy,$fm,$fd) = explode('-',$objD->format('Y-n-j'));
    $url = "https://affiliate.tradetracker.com/financial/invoice?desc=1&outputType=2&c=&r=0&p[t]=-1&p[fd]=".$fd."&p[fm]=".$fm."&p[fy]=".$fy."&p[td]=".$td."&p[tm]=".$tm."&p[ty]=".$ty."&setCompanyID=".$conf['setCompanyID']."&setCustomerSiteIDs=".$conf['setCustomerSiteIDs']."&submit_period_p=Apply&offset=0";
    _curl($url);

    $xml = simplexml_load_file($file_temp);
    $tmp = json_encode($xml);
    $xmlArr = json_decode($tmp,true);
print_r();exit();
    if(empty($xmlArr) || !isset($xmlArr['record']) || empty($xmlArr['record'])){
        return;
    }
    if(!isset($xmlArr['record'][0])){
        $tmp = $xmlArr['record'];
        unset($xmlArr['record']);

        $xmlArr['record'] = array($tmp);
    }

    $payments_arr = array();
    foreach($xmlArr['record'] as $k=>$v){
        list($d,$m,$y) = explode('/',$v['Invoice_Date']); 
        $date_new = join('-',array($y,$m,$d));
        preg_match('/(\d|\.|,)+/is',$v['Payment_Total'],$m);
        $amount = str_replace(',','',$m[0]);

        $currency = "USD";
        if(preg_match('/£/is',$v['Payment_Total'])){
            $currency = 'GBP';
        }elseif(preg_match('/€/is',$v['Payment_Total'])){
            $currency = 'EUR';
        }elseif(preg_match('/р/is',$v['Payment_Total'])){
            $currency = 'RUB';
        }elseif(preg_match('/CHF/is',$v['Payment_Total'])){
            $currency = 'CHF';
        }
        $payments_arr[] = array(
            'NetworkID'=>AFFID,
            'IDinNetwork'=>$v['Invoice_Number'],
            'Remit_date'=>$date_new,
            'Remit_amount'=>$amount,
            'Remit_currency'=>$currency,
        );
    }

    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}


function download_invoice(){
    global $_db,$conf;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.$conf['NetworkID'].' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        _download_invoice($payments);
    }
}

function _download_invoice($payments){
    global $file_temp,$conf;
    $objD = new DateTime();
    list($ty,$tm,$td) = explode('-',$objD->format('Y-n-j'));
    $objD->modify('-1 year');
    list($fy,$fm,$fd) = explode('-',$objD->format('Y-n-j'));

    $url = "https://affiliate.tradetracker.com/financial/invoiceSpecification/ID/".$payments['IDinNetwork']."?returnURL=financial/invoice?p[t]=-1&setCompanyID=".$conf['setCompanyID']."&setCustomerSiteIDs=".$conf['setCustomerSiteIDs']."&desc=1&offset=0&outputType=1&rand=5658efa&desc=&offset=0&outputType=2";
    _curl($url);

    $invoice_tmp = $file_temp;
    $invoice_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    file_put_contents($invoice_file,'');
    $xml = simplexml_load_file($invoice_tmp);
    $tmp = json_encode($xml);
    $xmlArr = json_decode($tmp,true);

    $count = 0;
    $sum = 0;

    foreach($xmlArr['record'] as $k=>$v){
        preg_match('/(\d{2})\/(\d{2})\/(\d{4}), (\d{2}):(\d{2})/',$v['Registration_date'],$m);
        if(empty($m))
            continue;

        $c_date = $m[3].'-'.$m[2].'-'.$m[1].' '.$m[4].':'.$m[5].':00';
        $keyid = $v['Transaction_ID'];
        $sales = 0;
        $comm = str_replace(array('€','$','£','р','CHF'),'',$v['Commission']);
        $sid = !empty($v['Reference'])?$v['Reference']:'';
        $curr = $payments['Remit_currency'];

        $tmp_str = join("\t",array($c_date,$keyid,$sales,$comm,$sid,$curr))."\n";
        file_put_contents($invoice_file,$tmp_str,FILE_APPEND);

        $count++;
        $sum+=$comm;
    }
    echo "count = ".$count.", sum = ".$sum.", payment = ".$payments['Remit_amount'].", file = ".$invoice_file."\n";
}

function login(){
    global $file_temp;
    $url = 'https://affiliate.tradetracker.com/user/login';
    $content = file_get_contents($url);
    preg_match('/name="__FORM" value="([^"]*)"/is',$content,$m);
    $__FORM = isset($m[1])?$m[1]:'';

    $url = 'https://affiliate.tradetracker.com/user/login';
    $posts = array();
    $posts['username'] = AFFILIATE_USER;
    $posts['password'] = AFFILIATE_PASS;
    $posts['rememberMe'] = '0';
    $posts['redirectURL'] = '';
    $posts['submitLogin'] = 'Log in';
    $posts['__FORM'] = $__FORM;

    $loginHtml = _curl($url,$posts,1);
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
