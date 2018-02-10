<?php
#TradeId
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
    global $_db;
    $url = 'https://login.tradedoubler.com/pan/reportSelection/Payment';
    $paymentsHtml = _curl($url,array(),1); 
    
    preg_match('/<select name="payment_id".*?<\/select>/is',$paymentsHtml,$mSELECT);
    if(empty($mSELECT) || !isset($mSELECT[0])){
        echoMsg('get payments list fail','crawl payments','fail');exit();
    }

    preg_match_all('/<option value="(.*?)" >(\d{2}\/\d{2}\/\d{2}) \/ .*?<\/OPTION>/is',$mSELECT[0],$mOPTION);
    if(empty($mOPTION) || !isset($mOPTION[1]) || !isset($mOPTION[2])){
        echoMsg('get payments list fail - 2','crawl payments','fail');exit();
    }

    $payments_arr = array();
    foreach($mOPTION[1] as $k=>$v){
        $date_old = $mOPTION[2][$k];
        list($d,$m,$y) = explode('/',$date_old);
        $date_new = '20'.trim($y).'-'.trim($m).'-'.trim($d);
        $payments_arr[] = array('IDinNetwork'=>trim($v),'Remit_date'=>$date_new,'NetworkID'=>AFFID);
    }

    foreach($payments_arr as $k=>$v){
        $url = 'https://login.tradedoubler.com/pan/reportSelection/Payment?cache=invalidate&payment_id='.$v['IDinNetwork'];
        $paymentsDetailHtml = _curl($url,array(),1);
        preg_match_all('/class="report2summaryCell".*?>(.*?)<\/td>/i',$paymentsDetailHtml,$mSUMMARY);
        if(empty($mSUMMARY) || !isset($mSUMMARY[1])){
            echoMsg('get payments amount fail','crawl payments','fail');exit();
        }
        $cc = count($mSUMMARY[1]);
        $payments_arr[$k]['Remit_currency'] = trim($mSUMMARY[1][$cc-2]);
        $payments_arr[$k]['Remit_amount'] = str_replace(',','',trim($mSUMMARY[1][$cc-1]));
    }
    $sql = getBatchUpdateSql($payments_arr,'payments_network_remit','NetworkID,IDinNetwork');
    $_db->query($sql);
}  

function download_invoice(){
    global $_db;
    $sql = "SELECT * FROM payments_network_remit WHERE NetworkID = 5 AND GetInvoice = 'no'";
    $rows = $_db->getRows($sql);

    foreach($rows as $k=>$payment){
        $url = 'https://login.tradedoubler.com/pan/aReport3.action?reportName=aAffiliatePaymentBreakdownReport&paymentId='.$payment['IDinNetwork'].'&viewType=1&format=XML';
        $invoice_tmp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.'.$payment['IDinNetwork'].'.invoice.xml';
        _curl($url,array(),false,$invoice_tmp);
        $invoice_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payment['ID'].'_'.date('ymd',strtotime($payment['Remit_date'])).'.dat';
        file_put_contents($invoice_file,'');
        $xml = simplexml_load_file($invoice_tmp);
        $tmp = json_encode($xml);
        $xmlArr = json_decode($tmp,true);
        //print_r($xmlArr['matrix']['rows']);exit();
        //print_r(array_keys($xmlArr['matrix']['rows']));exit();
        foreach($xmlArr['matrix']['rows']['row'] as $k=>$v){
            $c_date = $v['timeOfEvent'];
            preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/',$c_date,$m);
            if(empty($m))
                continue;
            $c_date = $m[1];

            $sid = '';
            if(!empty($v['epi1']))
                $sid = $v['epi1'];
            elseif(!empty($v['epi2']))
                $sid = $v['epi2'];

            $sales = $v['orderValue'];
            $comm = $v['commissionPaid'];
            $curr = $v['currency'];
            if($v['eventName'] == 'Lead')
                $keyid = $v['leadNr'];
            else
                $keyid = $v['orderNr'];
            
            $tmp = join("\t",array($c_date,$keyid,$sales,$comm,$sid,$curr));
            file_put_contents($invoice_file,$tmp."\n",FILE_APPEND);
        }
    }
}

function login(){
    $LoginUrl = 'https://login.tradedoubler.com/pan/login';
    $postArr['j_username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['j_password'] = AFFILIATE_PASS;#AFFILIATE_PASS;

    $loginHtml = _curl($LoginUrl,$postArr,1);
    $keywords = 'Account balance';
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
