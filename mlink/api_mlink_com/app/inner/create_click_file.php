<?php
#2015-04-21 开始讲第一个站点的出站迁移至BDG
global $_cf,$_req,$_db;

$cover = isset($_req['c'])?1:0;

if(isset($_req['startdate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['startdate']) ){
	$startDate = $_req['startdate'];
}else{
	$startDate = date('Y-m-d',strtotime('-4 day'));
}

$endDate = date('Y-m-d',strtotime('-1 day'));
if(isset($_req['enddate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['enddate']) && $_req['enddate'] <= $endDate){
	$endDate = $_req['enddate'];
}

echo 'Start '.date('Y-m-d H:i:s').': create clicks-date-file from:'.$startDate.' to:'.$endDate.'...'."\n";


$date_range = get_date_arr($startDate,$endDate);
$file = array();
foreach($date_range as $k=>$v){
	$file[$v] = DATA_ROOT.'inner_clicks/clicks_'.date('Ymd',strtotime($v)).'.dat';
}


foreach($file as $d=>$f){
	if(file_exists($f)){
		if($cover){
			unlink($f);
		}else{
			continue;
		}
	}

	echo "get date($d)\t";
	$time_aa = time();

	$br_file = $f;
	$bdg_file = $br_file.'.ps';
	
	$sql = "SELECT o.publishTracking,o.AffId,o.created,o.programId,p.IdInAff,p.Name FROM bd_out_tracking AS o LEFT JOIN program AS p ON o.programId = p.`ID` WHERE  o.createddate = '".$d."' AND o.affid > 0 and o.affid != 9999 into outfile '".$br_file."' fields terminated by '\t' lines terminated by '\n'";
	$_db->query($sql);

	$sql = "SELECT publishTracking,AffId,created,programId,programIdInAff,programName FROM (SELECT min(o.id),o.publishTracking,oi.AffId,o.created,oi.programId,oi.programIdInAff,oi.programName FROM bd_out_tracking AS o LEFT JOIN bd_out_tracking_inner AS oi ON o.`sessionId` = oi.publishTracking WHERE  o.createddate = '".$d."' AND (o.affid = 9999 or o.affid = 0) GROUP BY o.id) as c into outfile '".$bdg_file."' fields terminated by '\t' lines terminated by '\n'";
	$_db->query($sql);

	$content_bdg = file_get_contents($bdg_file);
	file_put_contents($br_file, $content_bdg, FILE_APPEND);

	unlink($bdg_file);

	$time_bb = time();
	echo "DUMP:" . $file[$d]. " \t(". ($time_bb - $time_aa) .")s\n";
}


echo 'End '.date('Y-m-d H:i:s').': create clicks-date-file from:'.$startDate.' to:'.$endDate.'...'."\n";
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
?>
