<?php
	include_once('conf_ini.php');
	include_once(INCLUDE_ROOT.'init_back.php');
	//mysql_query("SET NAMES UTF8");
	
	if(SID != 'TEST'){
		if($_SESSION['u']['Career'] != 'advertiser_white'){
		    header("Location:b_merchants.php");
		    exit();
		}
		check_user_login();
		
		$advertiserid = $_SESSION['u']['ID'];
	}else{
		$advertiserid = 287;
	}
	
	/*if(isset($_POST['img']) && !empty($_POST['img'])){
		print_r($_FILES);
		//move_uploaded_file($_FILES['id_photos']['tmp_name'],'img/'.$_FILES['id_photos']['name']);
	}*/
	if(isset($_POST['post']) && !empty($_POST['post'])){
		$val = $_POST['val'];
		$sid = $_POST['id'];
		$arr = json_decode($val);
		$key = '';
		$vals  = "";
		foreach($arr as $k=>$v){
			$key.= '`'.$k.'`="'.addslashes($v).'",';
		}
		$key  = rtrim($key,',');
		$sql = "update store_by_advertiser set $key where Storeid=$sid";
		$db->query($sql);
		$res =  mysql_affected_rows();
		if($res >= 1){
			echo 1;
		}else{
			echo 0;
		}
		die;
	}
	$sql = 'SELECT CountryName,CountryCode FROM country_codes order by CountryCode ASC ';
	$arr = $db->getRows($sql);
	foreach($arr as $v){
		$countryArr[$v['CountryName']] = $v['CountryCode'];
	}
	
	$category = array();
	$sql = "SELECT * from category_std ORDER BY `Name` ASC;";
	$rs = $db->getRows($sql);
	foreach($rs as $item)
	{
		$category[$item['ID']] = $item['Name'];
	}
	$objTpl->assign('category', $category);	
	$objTpl->assign('countryArr', $countryArr);
	
	
	$sql = "select a.storeid, a.* from store_by_advertiser a where a.advertiserid = '$advertiserid'";
	$store_arr = $db->getRows($sql);
	$objTpl->assign('info', $store_arr);
	//print_r($store_arr);
	
	$provide_arr = array('Promotion', 'Banner', 'Seasonal spotlight', 'Newsletter', 'Exclusive Offers', 'Youtube', 'Instagram', 'Twitter', 'Facebook');//, 'Pinterest', 'WeChat', 'Yelp'
	$objTpl->assign('provide_arr', $provide_arr);	
	$provide_id = $store_arr[0]['SupportWay'];
	$provide_id = array_flip(explode(',', $provide_id));
	//print_r($provide_id);
	$objTpl->assign('provide_id', $provide_id);	
	
	$Preference = array('E-commerce', 'Price Comparison', 'Loyalty Websites', 'Cause-Related Marketing', 'Promotion Websites', 'Content and niche market websites', 'Product Review Site', 'Blogs', 'E-mail Marketing', 'Registration or co-registration', 'Shopping Directories', 'Gaming', 'Virtual currency', 'File sharing platform', 'Video sharing platform');
	$objTpl->assign('Preference', $Preference);	
	$SupportType = $store_arr[0]['SupportType'];
	$SupportType = array_flip(explode(',', $SupportType));
	//print_r($SupportType);
	$objTpl->assign('SupportType', $SupportType);	
	
	//get store 
	$sql = "select * from store where id = '{$store_arr[0]['storeid']}'";
	$store_org_arr = $db->getFirstRow($sql, 'storeid');
	$objTpl->assign('store_org_arr', $store_org_arr);
	
	$category_id = $store_arr[0]['CategoryId'] ? $store_arr[0]['CategoryId'] : $store_org_arr['CategoryId'];
	$category_id = array_flip(explode(',', $category_id));
	
	//print_r($category_id);
	//get domain from store
	$sql = "select domainid from r_store_domain where storeid = '{$store_arr[0]['storeid']}'";
	$domain_arr = $db->getRows($sql, 'domainid');
	if(count($domain_arr)){
		//get shipping country
		$sql = "select a.shippingcountry from program_intell a inner join r_domain_program b on a.programid = b.pid where a.isactive = 'active'
				and b.status = 'active' and b.did in (".implode(',', array_keys($domain_arr)).")";
		$tmp_arr = $db->getRows($sql);
		$commission_arr = $country_arr = array();
		foreach($tmp_arr as $v){
			if($v['CommissionType'] == 'Percent'){
				$commission_arr['programid'] = $v['CommissionUsed'].'%';
			}else{
				$commission_arr['programid'] = $v['CommissionCurrency'].' '.$v['CommissionUsed'];
			}
			foreach(explode(',', $v['shippingcountry']) as $cc){
				if(strlen($cc) == 2){
					if($cc == 'uk') $cc = 'gb';
					$country_arr[strtoupper($cc)] = strtoupper($cc);
				}
			}
		}
	}
	
	if(strlen($store_arr[0]['SupportCountry'])){
		$country_arr = array();
		foreach(explode(',', $store_arr[0]['SupportCountry']) as $cc){
			if(strlen($cc) == 2){
				if($cc == 'uk') $cc = 'gb';
				$country_arr[strtoupper($cc)] = strtoupper($cc);
			}
		}
	}
	
	
			
	$sys_header['css'][] = BASE_URL.'/css/jquery.filer.css';
	$sys_header['css'][] = BASE_URL.'/css/jquery.filer-dragdropbox-theme.css';
	$sys_header['js'][] = BASE_URL.'/js/jquery.filer.min.js';
	
	
	$objTpl->assign('country_arr', $country_arr);	
	$objTpl->assign('category_id', $category_id);	
	$objTpl->assign('id', $store_arr[0]['storeid']);	
	$objTpl->assign('sys_header', $sys_header);
    $objTpl->display('b_updat.html');
?>