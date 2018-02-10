<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

if(!isset($_SESSION['storeActiveList'])){
    header("Location:advertiser_index.php");
    exit();
}
$whiteaccountidsession = $_SESSION['whiteListAccount']['ID'];
$storeidsession = $_SESSION['storeActiveList']['active'];
$storename = $_SESSION['storeList'][$storeidsession]['name'];
$objTpl->assign('whiteaccountidsession', $whiteaccountidsession);
$objTpl->assign('storeidsession', $storeidsession);
$objTpl->assign('storename', $storename);

$whiteList = new WhiteList;

$action = $_REQUEST['action'];
/* if($action == 'ss'){
	$keywords = $_REQUEST['keywords'];
	if($keywords){
		$sql = "select name from store where name like '%$keywords%' limit 20";
		$tmp_arr = $db->getRows($sql, 'name');
		
		echo implode('|', array_keys($tmp_arr));
	}	
	exit;
}elseif($action == 'ad_ad'){
	$keywords = $_REQUEST['name'];
	if($keywords){
		$sql = "select id from store where name = '$keywords'";
		$storeid = $db->getFirstRowColumn($sql);
		
		if($storeid){
			$sql = "insert ignore into store_by_advertiser (storeid, advertiserid) value('$storeid', '$whiteaccountidsession')";
			$db->query($sql);
			
			$sql = "select a.storeid from store_by_advertiser a where a.advertiserid = '$whiteaccountidsession' and a.storeid = '$storeid'";
			$store_arr = $db->getFirstRow($sql, 'storeid');
			
			if(count($store_arr)){
				echo 'success';				
			}
			
		}else{
			echo "Can't find advertiser.";
		}
	}	
	exit;	
} */

//获取store对应的advertise的信息
$sql = "select a.* from white_list_store a where a.WhiteAccountId = '$whiteaccountidsession' and a.StoreId = '$storeidsession'";
$store_arr = $db->getFirstRow($sql);
$objTpl->assign('store_arr', $store_arr);

//store信息 
$sql = "select * from store where id = '{$store_arr['StoreId']}'";
$store_org_arr = $db->getFirstRow($sql);
if(strlen($store_org_arr['LogoName']) && strpos($store_org_arr['LogoName'], ',') !== false){
	$store_org_arr['LogoName'] = substr($store_org_arr['LogoName'], 0, strpos($store_org_arr['LogoName'], ','));
}
if($store_org_arr['LogoName'] == ''){
    $store_org_arr['LogoName'] = 'brandreward.png';
}
$objTpl->assign('store_org_arr', $store_org_arr);

/* if($action == 'editInfo'){
    $rs = getStoreInfo($store_arr,$store_org_arr);
    echo json_encode([
        'code'=>1,
        'result'=>$rs
    ]);
    exit;
}else if($action == 'cancelInfo'){
    $rs = getStoreInfo($store_arr,$store_org_arr);
    echo json_encode([
        'code'=>1,
        'result'=>$rs
    ]);
    exit;
}else if($action == 'updateInfo'){
    $where = array();
    if(isset($_POST['Name'])){
        $where[] = "`Name`='".addslashes($_POST['Name'])."'";
    }
    if(isset($_POST['AdvertiserEmail'])){
        $where[] = "`AdvertiserEmail`='".addslashes($_POST['AdvertiserEmail'])."'";
    }
    if(isset($_POST['Desc'])){
        $where[] = "`Desc`='".addslashes($_POST['Desc'])."'";
    }
    if(isset($_POST['AdvertiserEmailType'])){
        $where[] = "`AdvertiserEmailType`='".addslashes($_POST['AdvertiserEmailType'])."'";
    }
    if(isset($_POST['PPCPolicy'])){
        $where[] = "`PPCPolicy`='".addslashes($_POST['PPCPolicy'])."'";
    }
    $where = implode($where, ",");
    if($where!=''){
        $sql = "update store_by_advertiser set $where where `Storeid`={$store_arr['StoreId']} and `AdvertiserId`={$whiteaccountidsession} ";
        $db->query($sql);
        $sql = "select a.* from store_by_advertiser a where a.advertiserid = '$whiteaccountidsession' and a.StoreId = {$store_arr['StoreId']}";
        $store_arr = $db->getFirstRow($sql);
        $rs = getStoreInfo($store_arr,$store_org_arr);
        echo json_encode([
            'code'=>1,
            'result'=>$rs
        ]);
        exit;
    }
    echo json_encode([
        'code'=>0,
        'msg'=>'update error'
    ]);
    exit;
}else if($action == 'updateChoose'){
    if(isset($_POST['type']) && isset($_POST['param'])){
        $where = "";
        switch ($_POST['type']){
            case 'category':
                $where .= "`CategoryId`='".addslashes($_POST['param'])."'";
                break;
            case 'preference':
                $where .= "`SupportType`='".addslashes($_POST['param'])."'";
                break;
            case 'country':
                $where .= "`SupportCountry`='".addslashes($_POST['param'])."'";
                break;
            default:
                echo json_encode([
                    'code'=>0,
                    'msg'=>'param error'
                ]);
                exit;
                break;
        }
        $sql = "update store_by_advertiser set $where where `Storeid`={$store_arr['StoreId']} and `AdvertiserId`={$whiteaccountidsession} ";
        $db->query($sql);
        echo json_encode([
            'code'=>1
        ]);
        exit;
    }
}else if($action == 'uploadLogo'){
    if(isset($_FILES) && !empty($_FILES)){
        if(!is_dir('img/adv_logo/')){
            mkdir('img/adv_logo/',0777);
        }
        if(move_uploaded_file($_FILES['files']['tmp_name'][0],'img/adv_logo/'.$_FILES['files']['name'][0])){
            $where = "`LogoAdr`='".addslashes($_FILES['files']['name'][0])."'";
            $sql = "update store_by_advertiser set $where where `Storeid`={$store_arr['StoreId']} and `AdvertiserId`={$whiteaccountidsession} ";
            $db->query($sql);
            echo json_encode([
                'code'=>1
            ]);
            exit;
        }
    }
    echo json_encode([
        'code'=>0,
        'msg'=>'upload error'
    ]);
    exit;
}

function getStoreInfo($store_arr,$store_org_arr){
    if($store_arr['LogoAdr']){
        $rs['LogoName'] = $store_arr['LogoAdr'];
    }else if($store_org_arr['LogoName']){
        $rs['LogoName'] = $store_org_arr['LogoName'];
    }else {
        $rs['LogoName'] = 'brandreward.png';
    }
    if($store_arr['Name']){
        $rs['Name'] = $store_arr['Name'];
    }else if($store_org_arr['NameOptimized']){
        $rs['Name'] = $store_org_arr['NameOptimized'];
    }else if($store_org_arr['Name']){
        $rs['Name'] = $store_org_arr['Name'];
    }else {
        $rs['Name'] = '';
    }
    $rs['AdvertiserEmail'] = $store_arr['AdvertiserEmail']!=null?$store_arr['AdvertiserEmail']:'';
    $rs['AdvertiserEmailType'] = $store_arr['AdvertiserEmailType']!='Unknown'?$store_arr['AdvertiserEmailType']:'';
    $rs['Desc'] = $store_arr['Desc']!=null?$store_arr['Desc']:'';
    $rs['PPCPolicy'] = $store_arr['PPCPolicy']!=null?$store_arr['PPCPolicy']:'';;
    $rs['Domains'] = $store_org_arr['Domains'];
    return $rs;
} */


/* if(!count($store_arr)){
    $action = 'addstore';
} */
/* if($action == 'edit'){	
	$objTpl->assign('action', 'edit');
	//exit;
}elseif($action == 'addstore'){	
	$objTpl->assign('action', 'addstore');
}elseif($action == 'request_advertiser'){//request_advertiser	
	$storeid = intval($_REQUEST['sid']);
	$publisherid = intval($_REQUEST['pid']);
	if(in_array($type, array('y','n')) && $storeid == $store_arr['StoreId'] && $publisherid){
		$status = ($type == 'y') ? 'Active' : 'Inactive';
		$sql = "insert ignore into store_whitelisting(storeid, publisherid, addtime, status)
				value ('$storeid', '$publisherid', 'Pending', '".date('Y-m-d H:i:s')."')";
		$db->query($sql);
		echo 'success';
	}	
	exit;
}else */if($action == 'adp'){//do add feedback		
	$Question = trim($_REQUEST['qn']);
	$type = trim(addslashes($_REQUEST['type']));
	$StartDate = trim($_REQUEST['StartDate']);
	$Duration = trim($_REQUEST['Duration']);
	/*select * from proposal where userid = '$whiteaccountidsession' and usertype = 'Advertiser' */
	if($Question){		
		$sql = "insert into proposal(WhiteAccountId,StoreId, UserType, Content, addtime, `status`, Title, StartDate, Duration)
				value('$whiteaccountidsession','$storeidsession', 'Advertiser', '".addslashes($Question)."', '".date('Y-m-d H:i:s')."', 'Pending', '$type', '$StartDate', '$Duration')";
		$db->query($sql);
		echo 'success';
	}	
	exit;
	
}elseif($action == 'df'){//delete feed
	$feeid = intval($_REQUEST['fid']);	
	
	if($feeid){		
		$sql = "update content_feed_new set status = 'InActive', lastupdatetime = '".date('Y-m-d H:i:s')."' where id = $feeid";
		$db->query($sql);
		echo 'success';
	}	
	exit;
	
}/* elseif($action == 'afd'){//do add feed
	$code = trim($_REQUEST['code']);
	$startDate = trim($_REQUEST['startDate']);
	$endDate = trim($_REQUEST['endDate']);
	$title = trim($_REQUEST['title']);
	$desc = trim($_REQUEST['desc']);
	$affurl = trim($_REQUEST['url']);
	$affurl = trim($_REQUEST['url']);
	
	if($title && $affurl){	
		$sql = "select max(SimpleId) from content_feed_new where source = 'whitelisting'";
		$max_linkid = $db->getFirstRowColumn($sql);
		if(!$max_linkid) $max_linkid = 0;
		$max_linkid ++;
			
		$type = ($code == '') ? 'Promotion' : 'Coupon';
		$sql = "insert ignore into content_feed_new(storeid, SimpleId, couponcode, title, `desc`, affurl, startdate, enddate, addtime, `status`, `type`, `adduser`, source)
				value('{$store_arr['StoreId']}', '$max_linkid', '".addslashes($code)."', '".addslashes($title)."', '".addslashes($desc)."', '".addslashes($affurl)."',
						 '".addslashes($startDate)."', '".addslashes($endDate)."', '".date('Y-m-d H:i:s')."', 'Active', '".addslashes($type)."', '$whiteaccountidsession', 'whitelisting')";
		$db->query($sql);
		echo 'success';
	}	
	exit;
} */elseif($action == 'rw'){//request whitelisting
	$type = $_REQUEST['type'];
	$storeid = intval($_REQUEST['sid']);
	$publisherid = intval($_REQUEST['pid']);
	if(in_array($type, array('y','n')) && $storeid == $store_arr['StoreId'] && $publisherid){
		$status = ($type == 'y') ? 'Active' : 'Inactive';
		$sql = "update store_whitelisting set status = '$status', lastupdatetime = '".date('Y-m-d H:i:s')."' where storeid = $storeid and publisherid = $publisherid";
		$db->query($sql);
		echo 'success';
	}	
	exit;
}elseif($action == 's_prefer'){//edit support type		
	$support_type = trim($_REQUEST['con']);
	$sql = "update store_by_advertiser set SupportType = '$support_type' where storeid = {$store_arr['StoreId']} and advertiserid = $whiteaccountidsession";
	$db->query($sql);
	echo 'success';
	exit;
}/* elseif($action == 'rb'){//request blacklisting
    if(isset($_GET['type']) && isset($_GET['sid']) && isset($_GET['pid'])){
        $rs = $whiteList->handleBlockList($_GET);
        echo $rs;
    }else {
        $rs = array(
            "code" => 2,
            "msg" => "param error"
        );
        echo $rs;
    }
    exit;
    
	$type = $_REQUEST['type'];
	$storeid = intval($_REQUEST['sid']);
	$publisherid = intval($_REQUEST['pid']);
	if(in_array($type, array('y','n')) && $storeid == $store_arr['StoreId'] && $publisherid){
		$status = ($type == 'y') ? 'Active' : 'Inactive';
		if($status == 'Active'){
			$sql = "REPLACE into store_blacklisting (StoreId, PublisherId, Status, Addtime) values ('$storeid', '$publisherid', 'Active', '".date('Y-m-d H:i:s')."')";
		}else{
			$sql = "update store_blacklisting set status = '$status', lastupdatetime = '".date('Y-m-d H:i:s')."' where storeid = $storeid and publisherid = $publisherid";
		}
		$db->query($sql);
		echo 'success';
	}	
	exit;
} */else{
    $objTpl->assign('action', 'show');
    
    //get category
    $category_id = $store_arr['CategoryId'] ? $store_arr['CategoryId'] : $store_org_arr['CategoryId'];
    //category的显示文本
    $category_str = '';
    //category id数组
    $category_idList = array();
    if($category_id){
        $sql = "select id,name from category_std where id in ($category_id)";
        $category_arr = $db->getRows($sql,'name');
        foreach ($category_arr as $arr){
            $category_str .= $arr['name'].", ";
            $category_idList[$arr['id']] = $arr['id'];
        }
        $category_str = rtrim($category_str,', ');
    }
    $objTpl->assign('category_str', $category_str);
    $category_idList = implode($category_idList, ",");
    $objTpl->assign('category_idList', $category_idList);
    
	//get domain from store
	$sql = "select DomainId,dd.Domain from r_store_domain rsd left join domain dd on dd.ID = rsd.DomainId where rsd.storeid = '{$store_arr['StoreId']}'";
	$domain_arr = $db->getRows($sql, 'DomainId');
	$domain_text = '';
	foreach ($domain_arr as $temp){
	    $domain_text .= $temp['Domain'].",";
	}
	$objTpl->assign('domain_text', trim($domain_text,","));
	$objTpl->assign('domain_arr', $domain_arr);
	
	//支持的country有哪些
	if(count($domain_arr)){
		//get commission rate
		$sql = "select pi.CommissionUsed, pi.CommissionValue, pi.CommissionType, pi.CommissionCurrency, pi.affid, pi.programid, pi.shippingcountry from program_intell pi inner join r_domain_program rdp on pi.programid = rdp.pid where pi.isactive = 'active'
				and rdp.status = 'active' and rdp.did in (".implode(',', array_keys($domain_arr)).")";
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
					if($cc == 'uk'){
					    $cc = 'gb';
					}
					$country_arr[strtoupper($cc)] = strtoupper($cc);
				}
			}
		}
		if(strlen($store_arr['SupportCountry']) || $store_arr['SupportCountry']===''){
			$country_arr = array();
			foreach(explode(',', $store_arr['SupportCountry']) as $cc){
				if(strlen($cc) == 2){
				    if($cc == 'uk'){
					    $cc = 'gb';
					}
					$country_arr[strtoupper($cc)] = strtoupper($cc);
				}
			}
		}
		if(count($country_arr)){
			$sql = "SELECT countryname, UPPER(countrycode) as countrycode FROM country_codes WHERE countrycode IN ('".implode("','", $country_arr)."')";
			$tmp_arr = $db->getRows($sql, 'countrycode');		
			foreach($tmp_arr as $k => $v){
				if(isset($country_arr[$k])){
					$country_arr[$k] = $v['countryname'];
				}
			}		
		}
		$commission = implode(',', $commission_arr);
		$objTpl->assign('commission', $commission);
		//Support Country
		$objTpl->assign('country', implode(', ', $country_arr));
		$objTpl->assign('country_idList', implode(',', array_keys($country_arr)));
		
	}
	
	$categoryiesOfContent = array(
        '1' => array(
            'a' => 'E-commerce',
            'b' => 'Price Comparison',
            'c' => 'Loyalty Websites (Cashback, Incentive, Rewards, Points, etc.)',
            'd' => 'Cause-Related Marketing',
            'e' => 'Coupon, Rebate, Deal, Discount Websites',
            'f' => 'Content and niche market websites',
            'g' => 'Product Review Site',
            'h' => 'Blogs (Typically with an RSS feed)',
            'i' => 'E-mail Marketing',
            'j' => 'Registration or co-registration',
            'k' => 'Shopping Directories',
            'l' => 'Gaming',
//             'm' => 'Adbars & Toolbars',
            'n' => 'Virtual currency',
            'o' => 'File sharing platform',
            'p' => 'Video sharing platform',
            'q' => 'Other',
        ),
        '2.MOBILE APP' => array(
            'a' => 'E-commerce',
            'b' => 'Price Comparison',
            'c' => 'Loyalty Websites (Cashback, Incentive, Rewards, Points, etc.)',
            'd' => 'Cause-Related Marketing',
            'e' => 'Coupon, Rebate, Deal, Discount Websites',
            'f' => 'Content and niche market websites',
            'g' => 'Product Review Site',
            'h' => 'Blogs (Typically with an RSS feed)',
            'i' => 'E-mail Marketing',
            'j' => 'Registration or co-registration',
            'k' => 'Shopping Directories',
            'l' => 'Gaming',
//             'm' => 'Adbars & Toolbars',
            'n' => 'File sharing platform',
            'o' => 'Video sharing platform',
            'p' => 'Other',
        )
    );
	//所有的国家信息
	$sql = "SELECT id, `CountryName`,`CountryCode` FROM `country_codes` WHERE `CountryStatus` = 'On'";
	$dictionary = $db->getRows($sql, 'id');
	$countryList = array();
	foreach ($dictionary as $val){
	    $countryList[$val["CountryCode"]] = $val["CountryName"];
	}
	$objTpl->assign('countryList', $countryList);
	
// 	$sql = "SELECT distinct(PublisherId) FROM block_relationship br WHERE `Status` = 'Active' AND ObjType = 'Store' AND ObjId = 88229";
	
	$sql = "SELECT pp.ID publisherId,pa.ID publisherAccountId,pp.`Name`, pa.Domain,pa.siteTypeNew, pp.`Status`, SUM(orders) as traffic, SUM(sales) as sales FROM `publisher_data` pd LEFT JOIN r_store_domain rsd ON rsd.DomainId = pd.objId 
	     LEFT JOIN publisher_account pa ON pa.`ApiKey` = pd.`site`  LEFT JOIN publisher pp ON pp.ID = pa.PublisherId
           WHERE pd.`objType` = 'domain' AND pp.status = 'Active' AND rsd.StoreId = '{$store_arr['StoreId']}' AND pd.site <> '' GROUP BY pd.site HAVING traffic > 0 OR sales > 0 ORDER BY pp.`Name`";
	$publisherTraffics = $db->getRows($sql);
	foreach ($publisherTraffics as $k=>$temp){
	    $publisherTraffics[$k]["iscoupon"] = "Content";
	    if((stripos($temp["siteTypeNew"], '1_e') !== false) || (stripos($temp["siteTypeNew"], '2_e') !== false)){
	        $publisherTraffics[$k]["iscoupon"] = "Promotion";
	    }
	    $publisherTraffics[$k]['Name'] = $temp['Domain'];
	    /* $publisherTraffics[$k]['traffic'] = number_format($temp['traffic'],0,".",",");
	    $publisherTraffics[$k]['sales'] = number_format($temp['sales'],2,".",","); */
	}
	$objTpl->assign('publisherTraffics', $publisherTraffics);
	
	//反馈提议
	$sql = "select id, WhiteAccountId,StoreId, usertype, title, content, startdate, enddate,Duration,Status from proposal where WhiteAccountId = '$whiteaccountidsession' and StoreId = '$storeidsession' and usertype = 'Advertiser' ";
	$proposal_arr = $db->getRows($sql);
	$objTpl->assign('proposal_arr', $proposal_arr);
	
	//get Promotion by adver
	$sql = "select id, programid, couponcode, title, `desc`, affurl, startdate, enddate, addtime, `status`, `type`, `adduser`, ImgAdr from content_feed_new where storeid = '{$store_arr['StoreId']}' and Status = 'active' order by id desc";
	$promotion_arr = $db->getRows($sql);
	$objTpl->assign('promotion_arr', $promotion_arr);
	
	$provide_arr = array('Banner', 'Seasonal spotlight', 'Newsletter', 'Exclusive Offers', 'Youtube', 'Instagram', 'Twitter', 'Facebook');//, 'Pinterest', 'WeChat', 'Yelp'
	$objTpl->assign('provide_arr', $provide_arr);
}

//Publisher Preference
$Preference = array('E-commerce', 'Price Comparison', 'Loyalty Websites', 'Cause-Related Marketing', 'Promotion Websites', 'Content and niche market websites', 'Product Review Site', 'Blogs', 'E-mail Marketing', 'Registration or co-registration', 'Shopping Directories', 'Gaming', 'Virtual currency', 'File sharing platform', 'Video sharing platform');
$objTpl->assign('preference', $Preference);
$SupportType = explode(',', $store_arr['SupportType']);
$objTpl->assign('supportType', $SupportType);
//category list
$category = array();
$sql = "SELECT * from category_std ORDER BY `Name` ASC;";
$rs = $db->getRows($sql);
foreach($rs as $item)
{
    $category[$item['ID']] = $item['Name'];
}
$objTpl->assign('category', $category);

$objTpl->assign('advertiserid', $whiteaccountidsession);
$sys_header['css'][] = BASE_URL.'/css/front.css';
// $objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_userinfo', $USERINFO);

$sys_header['css'][] = BASE_URL.'/css/jquery.filer.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.filer-dragdropbox-theme.css';
$sys_header['js'][] = BASE_URL.'/js/jquery.filer.min.js';

$sys_header['css'][] = BASE_URL.'/css/bootstrap-datetimepicker.min.css';
$sys_header['js'][] = BASE_URL.'/js/bootstrap-datetimepicker.js';

$sys_header['css'][] = BASE_URL.'/css/dataTables.bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/jquery.dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.bootstrap.min.js';
// $sys_header['js'][] = BASE_URL.'/js/jquery.ellipsis.js';

$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';

$objTpl->assign('storeList',$_SESSION['storeList']);
$objTpl->assign('storeActiveList',$_SESSION['storeActiveList']);

$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_white_listing.html');

?>
