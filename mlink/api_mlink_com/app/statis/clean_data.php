<?php
global $_cf,$_db,$_req;
#because of data from mega
#some click's affid = 0 will change to affid = 9999
#the old data should be delete or there will be many copy data.

echo 'clean data start : '.date('Y-m-d H:i:s')."\n";

$where_str = '';

if( !isset($_req['datemonth']) ){
	$date = date('Y-m-d',strtotime("-3 day"));
	$where_str = 'WHERE createddate >= "'.$date.'"';
}else{
	$d = new DateTime($_req['datemonth']);
	$date_from  = $d->format('Y-m-01');
	$date_to = $d->modify('+1 month')->format('Y-m-01');

	$where_str = 'WHERE createddate >= "'.$date_from.'" AND createddate < "'.$date_to.'"';
}

echo 'doing data in : '.$where_str."\n";

$sql = 'DELETE FROM statis_affiliate '.$where_str;
echo $sql."\n";
$_db->query($sql);
$sql = 'DELETE FROM statis_domain '.$where_str;
echo $sql."\n";
$_db->query($sql);
$sql = 'DELETE FROM statis_program '.$where_str;
echo $sql."\n";
$_db->query($sql);

echo 'clean data end : '.date('Y-m-d H:i:s')."\n";

?>
