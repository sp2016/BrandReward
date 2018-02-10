<?php
global $_cf,$_req,$_db;

$res = array();

$sql = 'SELECT COUNT(*) as c FROM c_content_feed WHERE IsActive="YES"';
$row = $_db->getRows($sql);
$all = $row[0]['c'];

$res['total'] = $all;
$res['page_size'] = 500;
$res['page'] = isset($_req['page'])?intval($_req['page']):1;
$res['page'] = ($res['page'] < 1)?1:$res['page'];

$sql = 'SELECT ID,Title,Code,`Type`,Url,Advertiser_Name,ImgIsDownload,ImgFile as Images,StartTime,ExpireTime,TimeZone,CreateTime as Created,UpdateTime as Updated FROM c_content_feed WHERE IsActive="YES" LIMIT '.($res['page']-1)*$res['page_size'].','.$res['page_size'];
$rows = $_db->getRows($sql);

foreach($rows as $k=>$v){
	$rows[$k]['Url'] = 'http://r.brandreward.com?key='.$_req['key'].'&url='.urlencode($v['Url']);
	$rows[$k]['Images'] = json_decode($v['Images'],true);
	if(!empty($rows[$k]['Images']) && $rows[$k]['ImgIsDownload'] =='YES'){
		foreach($rows[$k]['Images'] as $a=>$b){
			$rows[$k]['Images'][$a] = 'http://api.brandreward.com/data/linksIMG'.$b;
		}
	}else{
		$rows[$k]['Images'] = '';
	}
	unset($rows[$k]['ImgIsDownload']);
}

$res['num'] = count($rows);
$res['data'] = $rows;

if(isset($_req['xo']) && $_req['xo'] == 'ox'){
	echo '<pre>';print_r($res);
}
else{
	echo json_encode($res);
}

exit();
?>
