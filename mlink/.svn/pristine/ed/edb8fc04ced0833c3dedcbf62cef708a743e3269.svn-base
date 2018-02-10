<?php
#sync clicks info from mega to mlink
#get if clicks has aff-relationship in m
global $_cf,$_req,$_db;

if(isset($_req['startdate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['startdate']) ){
	$startDate = $_req['startdate'];
}else{
	$startDate = date('Y-m-d',strtotime('-60 day'));
}

$endDate = date('Y-m-d',strtotime('-1 day'));
if(isset($_req['enddate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['enddate']) && $_req['enddate'] <= $endDate){
	$endDate = $_req['enddate'];
}

echo 'Start '.date('Y-m-d H:i:s').': create clicks-date-file from:'.$startDate.' to:'.$endDate.'...'."\n";


$date_range = get_date_arr($startDate,$endDate);
$file = array();
$api_url = 'http://api.i.meikaiinfotech.com/?act=mlink.get_transaction&date=[datetime]';
$content = '';

$colums_in = "Source,SourceID,Af,AffId,Created,CreatedDate,Updated,UpdatedDate,Sales,Commission,IdInAff,ProgramName,SID,OrderId,TradeId,Site,PublishTracking,domainId,programId,TradeKey";

foreach($date_range as $date){
	echo "doing file data in ".$date.'......'."\n";
	$url = str_replace('[datetime]', $date, $api_url);

	$http_data = array();
	$http_data['file_cook'] = DATA_ROOT.'curl.cookie';
	$http_data['file_temp'] = DATA_ROOT.'mktradelog/transaction_'.str_replace('-','',$date).'.dat';
        _http($url,$http_data);
	
	$sql = 'DELETE FROM rpt_transaction_unique_inner WHERE CreatedDate = "'.$date.'" AND Source = "mk"';
	$_db->query($sql);

	$sql = "load data infile  '".$http_data['file_temp']."' REPLACE into table rpt_transaction_unique_inner fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' (".$colums_in.")";
	$_db->query($sql);
}

echo 'End '.date('Y-m-d H:i:s').': create transaction-date-file from:'.$startDate.' to:'.$endDate.'...'."\n";
exit();


function get_date_arr($startDate,$endDate,$format='Y-m-d'){
	$startDate = date('Y-m-d',strtotime($startDate));
	$endDate = date('Y-m-d',strtotime($endDate));

	if($startDate > $endDate)
		return array();

	$d = new DateTime($startDate);

	$return_d = array();

	while($d->format('Y-m-d') <= $endDate){

		$return_d[] = $d->format($format);

		$d->modify('+1 day');
    }
    return $return_d;
}


function _http($url,$data=array(),$return=false){
    $file_cook = isset($data['file_cook'])?$data['file_cook']:DATA_ROOT.'curl.cookie';
    $file_temp = isset($data['file_temp'])?$data['file_temp']:DATA_ROOT.'curl.tmp';

    $file = isset($data['tmp_file'])?$data['tmp_file']:$file_temp;

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
    curl_setopt($ch, CURLOPT_TIMEOUT        , 300);

    if(isset($data['postdata']) && !empty($data['postdata'])){
        $post_query = http_build_query($data['postdata']);
        curl_setopt($ch, CURLOPT_POST , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $post_query);
        print_r("curl_post :".$post_query."\n");
    }

    if(isset($data['headers']) && !empty($data['headers'])){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $data['headers']); 
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
