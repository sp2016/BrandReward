<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('ACCOUNT_ID','47119');

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();


function download_payments(){
    global $_db,$file_temp;
    $url = "https://affiliate.paidonresults.com/cgi-bin/invoice-status.pl";
    _curl($url);
 
    $content = file_get_contents($file_temp);
    preg_match('/<form name="tableForm1".*?<\/form>/is',$content,$mTable);
    if(empty($mTable) || !isset($mTable[0])){
        echoMsg('can not get table html','crawl payments','fail');exit();
    }
    preg_match_all('/<tr id=".*?<\/tr>/',$mTable[0],$mTr);
    if(empty($mTr) || !isset($mTr[0])){
        echoMsg('can not get table tr html','crawl payments','fail');exit();
    }
    $payments_arr = array();
    array_pop($mTr[0]);

    foreach($mTr[0] as $tr){
        preg_match_all('/<td.*?<\/td>/is',$tr,$mTd);
        if(empty($mTd) || !isset($mTd[0])){
            echoMsg('can not get table td html','crawl payments','fail');exit();
        }
        
        $date = strip_tags($mTd[0][0]);
        list($d,$m,$y) = explode('/',$date);
        $map_month = array('Jan'=>'01','Feb'=>'02','Mar'=>'03','Apr'=>'04','May'=>'05','Jun'=>'06','Jul'=>'07','Aug'=>'08','Sep'=>'09','Oct'=>10,'Nov'=>'11','Dec'=>12);
        $month = $map_month[$m];
        $dateNew = join('-',array($y,$month,$d));
        $amount = str_replace(array('&pound;',','),'',trim(strip_tags($mTd[0][6])));
        $currency = 'GBP';
        $idinnetwork = strip_tags($mTd[0][2]);
        
        $payments_arr[] = array(
            'NetworkID'=>AFFID,
            'IDinNetwork'=>$idinnetwork,
            'Remit_date'=>$dateNew,
            'Remit_amount'=>$amount,
            'Remit_currency'=>$currency,
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
    global $file_temp,$file_cook;

    $url = "https://affiliate.paidonresults.com/cgi-bin/payment-breakdown.pl?invoice=".$payments['IDinNetwork']."&show_tab=transactions";
    $pagehtml = _curl($url,array(),1);
    preg_match('/name="table_data" value="(.*?)"/is',$pagehtml,$mPage);
    $table_data = $mPage[1];

    preg_match('/name="scriptURL" value="(.*?)"/is',$pagehtml,$mPage);
    $scriptURL = $mPage[1];

    $url = "https://affiliate.paidonresults.com/cgi-bin/export-table.pl";
    $post = array();
    $post['table_data'] = $table_data;
    $post['scriptURL'] = $scriptURL;

    $curl_data = array();
    $curl_data['post'] = $post;
    _curl($url,$curl_data);
    
    $data_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    file_put_contents($data_file,'');
    $sum = 0;
    $count = 0;
    $fdw = fopen($data_file, 'w+');

    if (($handle = fopen($file_temp, "r")) !== FALSE) {
        $line = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $line++;

            if(empty($data) || !isset($data[1]) || empty($data[1])){
                continue;
            }
            if($line == 1){
                if(strstr($data[0], 'Order ID')
                    && strstr($data[1], 'Merchant Name')
                    && strstr($data[2], 'Affiliate ID')
                    && strstr($data[3], 'Commission')
                    && strstr($data[4], 'Order Value')
                    && strstr($data[5], 'Status')
                    && strstr($data[6], 'Country')
                    && strstr($data[7], 'Click Time')
                    && strstr($data[8], 'Order Date')
                    && strstr($data[9], 'Last Updated')
                    && strstr($data[10], 'IP Address')
                    && strstr($data[11], 'HTTP Referal')
                    && strstr($data[12], 'Custom Tracking ID')
                    && strstr($data[13], 'Creative')
                    && strstr($data[14], 'Order Notes')
                ){
                    continue;
                }else{
                    print_r('error:title format wrong 1'."\n");
                    fclose($handle);
                    return;
                }
            }
            preg_match('/(\d{2}) (\w{3}) (\d{4}) - (.*)/',$data[8],$m);
            if(empty($m))
                continue;

            $map_month = array('Jan'=>'01','Feb'=>'02','Mar'=>'03','Apr'=>'04','May'=>'05','Jun'=>'06','Jul'=>'07','Aug'=>'08','Sep'=>'09','Oct'=>10,'Nov'=>'11','Dec'=>12);
            $month = $map_month[$m[2]];
            $dateNew = $m[3].'-'.$month.'-'.$m[1].' '.$m[4];
            $sales = str_replace(array(',','£'),'',trim($data[4]));
            $sales = substr($sales,1);
            $commission = str_replace(array(',','£'),'',trim($data[3]));
            $commission = substr($commission,1);

            $tmp = array(
                'date'=>$dateNew,
                'trade_id'=>trim($data[0]),
                'sales'=>$sales,
                'commission'=>$commission,
                'sid'=>trim($data[12]),
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

function login(){
    $LoginUrl = 'https://www.paidonresults.com/login/';
    $post = array();
    $post['username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $post['password'] = AFFILIATE_PASS;#AFFILIATE_PASS;
    $curl_data = array();
    $curl_data['post'] = $post;
    $curl_data['header'][] = 'Referer: https://www.paidonresults.com/login/';

    $loginHtml = _curl($LoginUrl,$curl_data,1);
    $keywords = 'home.pl';
    if(!strpos($loginHtml,$keywords)){
        return false;
    }
    
    $url = "https://affiliate.paidonresults.com/cgi-bin/home.pl";
    $homepage = _curl($url,array(),1);

    $keywords = ACCOUNT_ID;
    if(strpos($homepage,$keywords)){
        return true;
    }else{
        return false;
    }
    
    print_r($homepage);exit(); 
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
