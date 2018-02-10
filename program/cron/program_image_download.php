<?php
	/**
	 * Created by PhpStorm.
	 * User: mding
	 * Date: 2017/5/15
	 * Time: 18:33
	 */
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$objProgram = new Program();
	$imgPath = "/app/site/ezconnexion.com/web/img/logo_program/";

	$i = 0;
	while (true) {
		$sql = "select a.`ID`,a.`name`,a.LogoUrl from program a inner join program_intell b on a.`ID`=b.`ProgramId` where a.LogoUrl is not null and b.LogoName is null and a.`StatusInAff` = 'Active' and a.`Partnership` = 'Active'limit $i,1000";
		$data = $objProgram->objMysql->getRows($sql);
		
		if(empty($data))
			break;
		
		$i += 1000;
		foreach ($data as $datum)
		{
			$img_type = exif_imagetype($datum['LogoUrl']);
			if($img_type !== false)
			{
				$suffix = image_type_to_extension($img_type);
				$img_name = mt_rand(100,999).$datum['ID'].mt_rand(100,999).$suffix;
				if(save_image($datum['LogoUrl'],$imgPath,$img_name))
				{
					$sql = "update program_intell set LogoName='{$img_name}' where ProgramId='{$datum['ID']}'";
					$objProgram->objMysql->query($sql);
				}
			}
		}
	}
	//更新到store表
//	$sql = "SELECT COUNT(1) as c FROM store WHERE StoreAffSupport = 'YES'  AND (LogoName = '' OR  LogoName IS NULL )";
//	$res = $objProgram->objMysql->getRows($sql);
//	if(!empty($res[0]['c'])){
//		$pagesize = 50;
//		$page_total = ceil($res[0]['c']/$pagesize);
//		for($i=0;$i<$page_total;$i++){
//			$pz=$i*$pagesize;
//			$sql = "select ID from store where StoreAffSupport = 'YES'  AND (LogoName = '' OR  LogoName IS NULL ) limit $pz,$pagesize";
//			$res = $objProgram->objMysql->getRows($sql);
//			if(!empty($res)){
//				foreach($res as $k){
//					$sql = "select a.StoreId,a.ProgramId,b.LogoName from r_store_program as a left JOIN program AS b ON a.`ProgramId` = b.`ID` where b.`LogoName` !='' AND b.`LogoName` IS NOT NULL AND  a.StoreId =".$k['ID'];
//					$res1 = $objProgram->objMysql->getRows($sql);
//					if(!empty($res1)){
//						$img = '';
//						foreach($res1 as $v1 ){
//							$img.=$v1['LogoName'].',';
//						}
//						$img = rtrim($img,',');
//						$sql = "update store set logoname='$img' where id=".$res1[0]['StoreId'];
//						$objProgram->objMysql->query($sql);
//					}else{
//						continue;
//					}
//				}
//			}
//
//		}
//	}
