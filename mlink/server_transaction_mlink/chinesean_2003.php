<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('API_UNAME', AFFILIATE_USER);
define('API_PASS', AFFILIATE_PASS);
//https://www.chinesean.com/api/event_detail.do?publisher=publisher_name&password=password&start=YYYYMMDD&end=YYYYMMDD&program_id=program_id
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
	$end_dt   = date('Y-m-d', strtotime(END_TIME));
	$begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
	$end_dt   = date('Y-m-d');
	$begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';

if (file_exists($file_temp))
	unlink($file_temp);

$fw = fopen($file_temp, 'w');

$url = "https://www.chinesean.com/api/event_detail.do?publisher=".API_UNAME."&password=".API_PASS."&start=".date('Ymd', strtotime($begin_dt))."&end=".date('Ymd', strtotime($end_dt));

echo "req => {$url} \n";
$ch = curl_init($url);
$curl_opts = array(
		
		CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_FILE => $fw,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
);
curl_setopt_array($ch, $curl_opts);
$rs = curl_exec($ch);
curl_close($ch);
fclose($fw);

$rs = file_get_contents($file_temp);

$rs = simplexml_load_string($rs);

$re = json_decode(json_encode($rs), true);

//print_r($rs);
//print_r($re);

foreach ($re['event'] as $v)
{	print_r($v['status']);exit;
	$TransactionTime = date('Y-m-d H:i:s', strtotime($v['datetime']));
	$Sid = !empty($v['member_id'])?$v['member_id']:'';
	$programname = $v['program_name']['en_us'];
	$TransactionId = $v['transaction_id'];
	$oldcommission = $v['commission'];
	$tradestatus = $v['status'];
	$Curency = $v['currency'];
	$referrer = $v['reference_id'];
	$ProgramId = $v['program_id'];
	$oldsales = $v['order_value'];
	
	$tdate = date("Y-m-d",strtotime($TransactionTime));
	$cur_exr = cur_exchange($Curency, 'USD',$tdate);
	$sales = $oldsales>0?round($oldsales * $cur_exr, 4):0;
	$Commission = $oldcommission>0?round($oldcommission * $cur_exr, 4):0;
        $cancelreason = '';
	
	$replace_array = array(
			'{createtime}'      => trim($TransactionTime),
			'{updatetime}'      => trim($TransactionTime),
			'{sales}'           => $sales,
			'{commission}'      => $Commission,
			'{idinaff}'         => $ProgramId,
			'{programname}'     => trim($programname),
			'{sid}'             => trim($Sid),
			'{orderid}'         => trim($TransactionId),
			'{clicktime}'       => trim($TransactionTime),
			'{tradeid}'         => trim($TransactionId),
			'{tradestatus}'     => trim($tradestatus),
			'{oldcur}'          => $Curency,
			'{oldsales}'        => $oldsales,
			'{oldcommission}'   => $oldcommission,
			'{tradetype}'       => '',
			'{referrer}'        => $referrer,
                        '{cancelreason}'    => $cancelreason,
	);
	$_day = date("Y-m-d", strtotime($v['datetime']));
	$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $_day) . '.upd';
	if (!isset($fws[$rev_file])) {
		$fws[$rev_file] = fopen($rev_file, 'w');
	}
	
	fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}


foreach ($fws as $file => $f) {
	fclose($f);
}



?>
