<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('ACCOUNT_ID',274181);

$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}


$currency_map = array(
	'GBP'=>array('page_id'=>1,'download_tmp'=>'/affiliate/'.ACCOUNT_ID.'/report/transactions/export/network/awin/dateRange/lifetime/transRegions/GB/searchQueryField/payment_id/searchQuery/'),
	'EUR'=>array('page_id'=>2,'download_tmp'=>'/affiliate/'.ACCOUNT_ID.'/report/transactions/export/network/awin/dateRange/lifetime/transRegions/EUR/searchQueryField/payment_id/searchQuery/'),
	'USD'=>array('page_id'=>3,'download_tmp'=>'/affiliate/'.ACCOUNT_ID.'/report/transactions/export/network/awin/dateRange/lifetime/transRegions/SG,US/searchQueryField/payment_id/searchQuery/'),
	'CAD'=>array('page_id'=>4,'download_tmp'=>'/affiliate/'.ACCOUNT_ID.'/report/transactions/export/network/awin/dateRange/lifetime/transRegions/CA/searchQueryField/payment_id/searchQuery/'),
);

//download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();

function download_invoice(){
    global $_db;

    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        _download_invoice($payments);
    }
}


function _download_invoice($payments){
    global $currency_map,$file_temp;
    $url = 'https://ui.awin.com'.$currency_map[$payments['Remit_currency']]['download_tmp'].$payments['IDinNetwork'];
    _curl($url);

    $csv = array();
    $count = 0;
    $sum = 0;
    $data_file = PATH_DATA.'/'.AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    $fdw = fopen($data_file, 'w+');

    if (($handle = fopen($file_temp, "r")) !== FALSE) {
        $line = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $line++;


            if($line == 1){
                if(strstr($data[0], 'id')
                    && strstr($data[1], 'advertiser_id')
                    && strstr($data[2], 'sale_amount')
                    && strstr($data[3], 'commission')
                    && strstr($data[4], 'date')
                    && strstr($data[5], 'commission_status')
                    && strstr($data[6], 'validation_date')
                    && strstr($data[7], 'click_ref')
                    && strstr($data[8], 'type')
                    && strstr($data[9], 'site_name')
                    && strstr($data[10], 'URL')
                    && strstr($data[11], 'decline_reason')
                    && strstr($data[12], 'clickThroughTime')
                    && strstr($data[13], 'voucher_code_used')
                    && strstr($data[14], 'lapse_time')
                    && strstr($data[15], 'amended')
                    && strstr($data[16], 'amend_reason')
                    && strstr($data[17], 'old_sale_amount')
                    && strstr($data[18], 'old_commission')
                    && strstr($data[19], 'different_currency')
                    && strstr($data[20], 'click_device')
                    && strstr($data[21], 'transaction_device')
                    && strstr($data[22], 'publisher_url')
                    && strstr($data[23], 'transaction_parts')
                    && strstr($data[24], 'customer_country')
                    && strstr($data[25], 'custom_parameters')
                    && strstr($data[26], 'paid_to_publisher')
                    && strstr($data[27], 'payment_status')
                    && strstr($data[28], 'payment_id')
                    && strstr($data[29], 'transaction_query_id')
                    && strstr($data[30], 'click_ref2')
                    && strstr($data[31], 'click_ref3')
                    && strstr($data[32], 'click_ref4')
                    && strstr($data[33], 'click_ref5')
                    && strstr($data[34], 'click_ref6')
                    && strstr($data[35], 'voucher_code')
                    && strstr($data[36], 'commission_sharing_publisher_id')
                    && strstr($data[37], 'commission_sharing_publisher')
                ){
                    continue;
                }else{
                    print_r('error:title format wrong 1'."\n");
                    fclose($handle);
                    return;
                }
            }

            if(empty($data) || !isset($data[1]) || empty($data[1]))
                continue;

            $tmp = array(
                'date'=>trim($data[4]),
                'transaction_id'=>$data[0],
                'sales'=>str_replace(',','',trim($data[2])),
                'commission'=>str_replace(',','',trim($data[3])),
                'sid'=>trim($data[7]),
                'currency'=>$payments['Remit_currency'],
                );
            $count++;
            $sum+=$tmp['commission'];
            fwrite($fdw , join("\t",$tmp)."\n");
        }
        fclose($handle);
    }


    fclose($fdw);
    print_r("doing , payment_id= ".$payments['IDinNetwork'].", count= ".$count." ,sum = ".$sum." \n");
}

function download_payments(){
    global $currency_map,$_db;

    $payments_arr = array();
    foreach($currency_map as $k=>$v){
        $url = "https://ui.awin.com/awin/affiliate/".ACCOUNT_ID."/payments/history?paymentRegion=".$v['page_id'];
        $paymentsHtml =  _curl($url,array(),1);
        preg_match('/<div id="paymentHistory".*?<\/tbody>/is',$paymentsHtml,$mTable);
        if(empty($mTable) || !isset($mTable[0])){
            echoMsg('crawl payments html fail','crawl payments','fail');exit();
        }

        preg_match_all('/<tr class=.*?<\/tr>/is',$mTable[0],$mTr);
        if(empty($mTr) || !isset($mTr[0])){
            echoMsg('crawl payments tr fail','crawl payments','fail');exit();
        }

        foreach($mTr[0] as $tr){
            preg_match_all('/<td.*?<\/td>/is',$tr,$mTd);
            if(empty($mTd) || !isset($mTd[0])){
                echoMsg('crawl payments td fail','crawl payments','fail');exit();
            }

            $date = strip_tags($mTd[0][0]);
            $currency = $k;
            $amount = str_replace(',','',trim(strip_tags($mTd[0][3])));
            preg_match('/'.str_replace('/','\/',$v['download_tmp']).'(\d+)"/is',$tr,$mDOWNURL);
            if(empty($mDOWNURL) || !isset($mDOWNURL[1])){
                echoMsg('crawl payments id fail','crawl payments','fail');exit();
            }
            $paymentsid = trim($mDOWNURL[1]);
            $payments_arr[] = array(
                'NetworkID'=>AFFID,
                'IDinNetwork'=>$paymentsid,
                'Remit_date'=>date('Y-m-d',strtotime($date)),
                'Remit_currency'=>$currency,
                'Remit_amount'=>$amount,
            );
        }
    }
    $sql = getBatchInsertSql($payments_arr,'payments_network_remit',false,true);
    $_db->query($sql);
}

function login(){
    $LoginUrl = 'https://ui.awin.com/login';

    $postArr['email'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['password'] = AFFILIATE_PASS;#AFFILIATE_PASS;
    $postArr['Login'] = '';

    $loginHtml = _curl($LoginUrl,$postArr,1);

    $keywords = '274181';
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
