<?php
#TradeId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('ACCOUNT_ID','387850');

//download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();


function download_payments(){
    global $_db;
    $url_temp = "https://services.daisycon.com/publishers/{ACCOUNT_ID}/financial/payments?status=paid&page=1&per_page=1000";
    $url = str_replace('{ACCOUNT_ID}',ACCOUNT_ID,$url_temp);
    
    $curl_data = array();
    $curl_data['header'][] = 'Authorization: Basic ' . base64_encode( AFFILIATE_USER.':'.AFFILIATE_PASS );
    $api_data = _curl($url,$curl_data,true);
    $data = json_decode($api_data,true);

    $payments_arr = array();
    foreach($data as $k=>$v){
        $payments_arr[] = array(
            'NetworkID'=>AFFID,
            'IDinNetwork'=>trim($v['invoice_number']),
            'Remit_date'=>trim($v['date']),
            'Remit_currency'=>'EUR',
            'Remit_amount'=>str_replace(',','',trim($v['amount'])),
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

    $td = $payments['Remit_date'];
    $objD = new DateTime($td);
    $fd = $objD->modify('-8 month')->format('Y-m-d');
    $page = 1;
    $page_size = 500;
    $url_temp = "https://services.daisycon.com/publishers/{ACCOUNT_ID}/transactions?page={page}&per_page={page_size}&start={fd}&end={td}&invoice_reference_number={payments_id}&order_direction=asc";
    $invoice_file = PATH_DATA."/".AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    file_put_contents($invoice_file,'');
    $sum = 0;
    $count = 0;

    do{
        $param = array(
            '{ACCOUNT_ID}'=>ACCOUNT_ID,
            '{td}'=>$td,
            '{fd}'=>$fd,
            '{payments_id}'=>$payments['IDinNetwork'],
            '{page}'=>$page,
            '{page_size}'=>$page_size,
        );
        $url = str_replace(array_keys($param),array_values($param),$url_temp);
        $curl_data = array();
        $curl_data['header'][] = 'Authorization: Basic ' . base64_encode( AFFILIATE_USER.':'.AFFILIATE_PASS );
        
        $api_data = _curl($url,$curl_data,true);
        $data = json_decode($api_data,true);

        if(!empty($data)){
            foreach($data as $program){
                foreach($program['parts'] as $v){
                    $sid = $v['subid']?$v['subid']:'';
                    $sid = $sid?$sid:$v['subid_2'];
                    $sid = $sid?$sid:$v['subid_3'];
                    $tmp = array(
                        'date'=>trim($v['date']),
                        'keyid'=>trim($v['id']),
                        'sales'=>str_replace(',','',trim($v['revenue'])),
                        'commission'=>str_replace(',','',trim($v['commission'])),
                        'sid'=>trim($sid),
                        'currency'=>'EUR',
                    );
                    $count++;
                    $sum+=$tmp['commission'];
                    file_put_contents($invoice_file,join("\t",$tmp)."\n",FILE_APPEND);
                }
            }
        }
        $page++;
    }while(!empty($data));
    print_r("doing , ID = ".$payments['ID'].", paiddate= ".$payments['Remit_date'].",paysum = ".$payments['Remit_amount'].", count= ".$count." ,sum = ".$sum.", file = ".$invoice_file." \n");
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
