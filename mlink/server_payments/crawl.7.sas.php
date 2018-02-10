<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('AFF_ID', '1273061');
define('AFF_API_TOKEN', 'fseaGaeoRUGCc3Tb');//1&h2SRW7qwUya
define('AFF_API_SECRET_KEY', 'RIv8sm9p7RSnuz7aBGd6xk0q7EZulz8z');
define('AFF_API_VERSION', '2.1');
define('AFF_API_ACTION', 'activity');
define('AFF_API_URI', 'https://shareasale.com/x.cfm?action={ACTION}&affiliateId={AFFID}&token={TOKEN}&dateStart={FD}&dateEnd={TD}&version={VERSION}&paidDate={PD}');

//download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();


function download_payments(){
    global $_db;
    $objD = new DateTime();
    $payment_arr = array();
    for($i=0;$i<12;$i++){
        $payment_date = $objD->modify('-1 month')->format('Y-m-20');
        $tmp = array('NetworkID'=>AFFID,'Remit_date'=>$payment_date,'Remit_currency'=>'USD','IDinNetwork'=>$objD->format('Ym'));
        $tmp['Remit_amount'] = _download_payments($payment_date);
        $payment_arr[] = $tmp;
    }
    $sql = getBatchInsertSql($payment_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function _download_payments($date){
    global $file_temp;
    $action = 'paymentSummary';
    $timestamp = gmdate(DATE_RFC1123);
    $sig = AFF_API_TOKEN . ':' . $timestamp . ':' . $action . ':' . AFF_API_SECRET_KEY;
    $sig = hash("sha256", $sig);

    $api_url_payments = 'https://api.shareasale.com/x.cfm?action='.$action.'&affiliateId={AFFID}&token={TOKEN}&paymentDate={PAYMENTDATE}&version={VERSION}';
    $headers = array("x-ShareASale-Date: {$timestamp}", "x-ShareASale-Authentication: {$sig}");
    $url = str_replace(array('{PAYMENTDATE}', '{TOKEN}', '{VERSION}', '{AFFID}'), array(date('m/d/Y', strtotime($date)), AFF_API_TOKEN, AFF_API_VERSION, AFF_ID ), $api_url_payments);

    _curl($url,array('header'=>$headers));

    $rh = fopen($file_temp,'r');
    $k = 0;
    $comm = 0;
    while (!feof($rh)) {
        $k++;
        $line =  fgets($rh);
        if(empty($line))
            continue;
        $data = explode('|',$line);
        if($k == 1){
            if(trim($data[0]) != 'Merchant Id' ||
               trim($data[1]) != 'Merchant' || 
               trim($data[2]) != 'Website' || 
               trim($data[3]) != 'Number of Transactions' || 
               trim($data[4]) != 'Net Sales' || 
               trim($data[5]) != 'Commissions' 
               ){
                   echoMsg('payments summary field changed','crawl payments','fail');exit();
               }
               continue;
        }

        $comm = bcadd($comm,str_replace(',','',trim($data[5])),4);
    }
    $comm = round($comm,2);
    fclose($rh);
    return $comm;
}

function download_invoice(){
    global $_db;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        $objD = new DateTime($payments['Remit_date']);
        $start_3 = $objD->modify('-90 day')->format('Y-m-d');
        $end_3 = $payments['Remit_date'];
        
        $end_6 = $objD->modify('-1 day')->format('Y-m-d');
        $start_6 = $objD->modify('-90 day')->format('Y-m-d');

        $data_file = PATH_DATA.'/'.AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
        file_put_contents($data_file,'');
        _download_invoice($payments,$start_3,$end_3,$data_file);
        _download_invoice($payments,$start_6,$end_6,$data_file);
    }

}

function _download_invoice($payments,$start_date,$end_date,$data_file){
    global $file_temp,$file_cook;
    $paiddate = $payments['Remit_date'];
    if(file_exists($file_temp))
        unlink($file_temp);

    $timestamp = gmdate(DATE_RFC1123);
    $sig = AFF_API_TOKEN . ':' . $timestamp . ':' . AFF_API_ACTION . ':' . AFF_API_SECRET_KEY;
    $sig = hash("sha256", $sig);
    $headers = array("x-ShareASale-Date: {$timestamp}", "x-ShareASale-Authentication: {$sig}");
    $url = str_replace(array('{FD}', '{TD}', '{ACTION}', '{TOKEN}', '{VERSION}', '{AFFID}','{PD}'), array(date('m/d/Y', strtotime($start_date)), date('m/d/Y', strtotime($end_date)), AFF_API_ACTION, AFF_API_TOKEN, AFF_API_VERSION, AFF_ID, date('m/d/Y', strtotime($paiddate))), AFF_API_URI);

    _curl($url,array('header'=>$headers,'file'=>$file_temp));

    $line = 0;
    $count = 0;
    $sum = 0;
    $fp = fopen($file_temp, 'r');
    while (!feof($fp)) {
        $lr = fgetcsv($fp, 0, '|', '"');
        $line++;
        if($line == 1){
            if(strstr($lr[0], 'Trans ID') 
                && strstr($lr[1], 'User ID')
                && strstr($lr[2], 'Merchant ID')
                && strstr($lr[3], 'Trans Date')
                && strstr($lr[4], 'Trans Amount')
                && strstr($lr[5], 'Commission')
                && strstr($lr[6], 'Comment')
                && strstr($lr[7], 'Voided')
                && strstr($lr[8], 'Pending Date')
                && strstr($lr[9], 'Locked')
                && strstr($lr[10], 'Aff Comment')
                && strstr($lr[11], 'Banner Page')
                && strstr($lr[12], 'Reversal Date')
                && strstr($lr[13], 'Click Date')
                && strstr($lr[14], 'Click Time')
                && strstr($lr[15], 'Banner Id')
                && strstr($lr[16], 'SKU List')
                && strstr($lr[17], 'Quantity List')
                && strstr($lr[18], 'Lock Date')
                && strstr($lr[19], 'Paid Date')
                && strstr($lr[20], 'Merchant Organization')
                && strstr($lr[21], 'Merchant Website')
                && strstr($lr[22], 'Trans Type')
            ){
                continue;
            }else{
                print_r('error:title format wrong 1');
                fclose($fp);
                exit();
            }
        }

        if(strstr($lr[0], 'Trans ID') 
            && strstr($lr[1], 'User ID')
            && strstr($lr[2], 'Merchant ID')
            && strstr($lr[3], 'Trans Date')
            && strstr($lr[4], 'Trans Amount')
            && strstr($lr[5], 'Commission')
            && strstr($lr[6], 'Comment')
            && strstr($lr[7], 'Voided')
            && strstr($lr[8], 'Pending Date')
            && strstr($lr[9], 'Locked')
            && strstr($lr[10], 'Aff Comment')
            && strstr($lr[11], 'Banner Page')
            && strstr($lr[12], 'Reversal Date')
            && strstr($lr[13], 'Click Date')
            && strstr($lr[14], 'Click Time')
            && strstr($lr[15], 'Banner Id')
            && strstr($lr[16], 'SKU List')
            && strstr($lr[17], 'Quantity List')
            && strstr($lr[18], 'Lock Date')
            && strstr($lr[19], 'Paid Date')
            && strstr($lr[20], 'Merchant Organization')
            && strstr($lr[21], 'Merchant Website')
            && strstr($lr[22], 'Trans Type')
        ){
            continue;
        }


        if(!isset($lr[4]) || empty($lr[0]))
            continue;

        $sale = $lr[4];
        $commission = $lr[7]?0:$lr[5];
        $tmp = array(
            'date'=>date('Y-m-d H:i:s', strtotime(trim($lr[3]))),
            'transid'=>trim($lr[0]),
            'sales'=>$sale,
            'commission'=>$commission,
            'sid'=>trim($lr[10]),
            'currency'=>$payments['Remit_currency'],
            );

        $count++;
        $sum = bcadd($sum,$commission,2);
        
        file_put_contents($data_file , join("\t",$tmp)."\n",FILE_APPEND);
        
    }
    fclose($fp);
    print_r("doing , paiddate= ".$paiddate.", count= ".$count." ,sum = ".$sum." \n");
    print_r("file ,".$data_file." \n");
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
