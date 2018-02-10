<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");

	$objProgram = new Program();
	$date = date('Y-m-d H:i:s');
	$flag = true;
	$i = 0;
	$count_update = 0;
	do{
		$sql = "SELECT a.id AS StoreId,GROUP_CONCAT(d.categoryid) AS CateId,a.CategoryId,a.`Name` FROM store a INNER JOIN r_store_domain b ON a.id = b.storeid INNER JOIN r_domain_program c ON c.did = b.domainid INNER JOIN program_intell d ON c.pid = d.programid WHERE a.CategoryHumanCtrl = 'NO' AND d.`CategoryId` IS NOT NULL AND d.`CategoryId` != '' AND d.`IsActive`='Active' GROUP BY a.`ID` ORDER BY a.`ID` LIMIT $i, 1000";
		$allCate = $objProgram->objMysql->getRows($sql);
		if(empty($allCate))
			$flag = false;
		else
		{
			$not_important = array('Gewinnspiel','Gewinnen','Gewinner','Gewinn','Gratis','Meinungsstudie','Geschenkkarte','Gutschein','Gutscheine','Voucher');
			foreach($allCate as $item)
			{
//				echo "\t{$item['StoreId']} Start",PHP_EOL;
				$cateId = array_unique(explode(',',$item['CateId']));
				asort($cateId);
				$cateId = implode(',',$cateId);
				$flag = true;
				foreach ($not_important as $v) {
					if (stristr($item['Name'], $v) !== false) {
						$flag = false;
						break;
					}
				}
				if($flag)
				{
					if($cateId != $item['CategoryId'])
					{
						$sql = "insert into store_change_log (StoreId,`Name`,FieldName,FieldValueOld,FieldValueNew,LastUpdateTime) values ('{$item['StoreId']}','{$item['Name']}','CategoryID','{$item['CategoryId']}','{$cateId}','{$date}')";
						$objProgram->objMysql->query($sql);
						
						if(!empty($cateId))
						{
							$sql = "UPDATE store SET CategoryId='{$cateId}',CategoryUpdateTime='{$date}' WHERE ID='{$item['StoreId']}'";
							$objProgram->objMysql->query($sql);
							$count_update ++;
						}
					}
				}
				else
				{
					$sql = "UPDATE store SET CategoryId='41',CategoryUpdateTime='{$date}' WHERE ID='{$item['StoreId']}'";
					$objProgram->objMysql->query($sql);
					$count_update ++;
				}
			}
		}
		$i += 1000;
	}while($flag);
	
	$sql = "select StoreId from store_change_log where LastUpdateTime='{$date}' and FieldValueNew=''";
	$no_cate_store = $objProgram->objMysql->getRows($sql,'StoreId');
	$cateId = implode(',',array_keys(array_unique($no_cate_store)));
	if(!empty($cateId))
	{
		$mail_body = "Store turn to no category，ids are:'{$cateId}";
		AlertEmail::SendAlert('Store turn to no category，ids are:'.$cateId,'mcskyding@meikaitech.com,stanguan@meikaitech.com');
	}
	echo "\tSet store category successfully. update count= '{$count_update}',End @ $date\n\r";
	
?>