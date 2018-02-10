<?php
class MerchantExt extends LibFactory{

    function __construct(){
        $this->objMysql = $GLOBALS['db'];
        $this->time_zone = array(
            "de" => "Europe/Berlin",
            "ca" => "America/Toronto",
            "uk" => "Europe/London",
            "ie" => "Europe/London",
            "nz" => "Pacific/Auckland",
            "us" => "America/Los_Angeles",
            "fr" => "Europe/Paris",
            "in" => "Indian/Antananarivo",
            "au" => "Australia/Sydney");
    }

    function TranslateTime($date,$time_from = "us",$time_to = "us"){
        $time_zone_arr = $this->time_zone;
        if(!isset($time_zone_arr[$time_from])) mydie("No such site");
        $datetime = new DateTime($date,new DateTimeZone($time_zone_arr[$time_from]));
        $datetime->setTimezone(new DateTimeZone($time_zone_arr[$time_to]));
        return $datetime;
    }
    function delfavorite($cid,$uid){
        $sql = "delete from publisher_favorites WHERE uid=$uid AND cid=$cid";
        $res = $this->query($sql);
        if($res == 1){
            return 1;
        }else{
            return 2;
        }
    }
    function GetsSearch($type,$uid){
        //$_SERVER['PHP_AUTH_USER']
        $sql = "select svalue,ID from publisher_search WHERE type='$type' and uname ='$uid' Order by svalue asc";
        return $this->getRows($sql);
    }
    function GetTimeZone($site){
        $time_zone_arr = $this->time_zone;
        if(isset($time_zone_arr[$site]) && $time_zone_arr[$site])
            return $time_zone_arr[$site];
        else{
            return $time_zone_arr['us'];
        }
    }
    function addfavorite($id,$uid,$aname,$type){
        if($type == 0){
            $insert_d['cid'] = $id;
            $insert_d['uid'] = $uid;
            $insert_d['aname'] = $aname;
            $insert_d['addtime'] = date('Y-m-d H:i:s');
            $this->table('publisher_favorites')->insert($insert_d);
            return 1;
        }else{
            $sql = "delete from publisher_favorites WHERE uid=$uid AND cid=$id";
            $this->query($sql);
            return 2;
        }
    }
    function checkfavorite($uid){
        $sql = "select cid from publisher_favorites where uid=$uid";
        return $this->getRows($sql);
    }
    function GetFavorites($opts,$page = 1,$pagesize = 20,$uid){
        $return = '';
        $where = '';
        $group = '';

		if (isset($opts['categories']) && !empty($opts['categories'])){
			$category = explode(',',$opts['categories']);
			foreach ($category as $k => $v) {
				if(!is_numeric($v))
					unset($category[$k]);
			}
			$categoryId = implode(',',$category);
		}else{
			$sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
			$res = $this->getRow($sql);
			$categoryId = trim($res['CategoryId'],',');
		}
		$categoryArr = explode(',',trim($categoryId,','));
		if(!empty($categoryArr))
		{
			$where .= " AND(";
			foreach($categoryArr as $cateid)
			{
				$where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
			}
			$where = rtrim($where,'OR').")";
		}

        if(isset($opts['country']) && !empty($opts['country'])){
            //if(isset($this->time_zone[$opts['country']])){
            $where .= " AND (b.`ShippingCountry` like '%".addslashes($opts['country'])."%' OR b.`ShippingCountry` = '' OR b.`ShippingCountry`  is null)";
            //}
        }

        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $where .= " AND f.`Name` LIKE '%".addslashes($opts['keyword'])."%'";

        }
        if(isset($opts['domain']) && !empty($opts['domain'])){
            $opts['domain'] = preg_replace('/\s/','',$opts['domain']);
            $sql = "select ID from store WHERE Domains LIKE '%".addslashes($opts['domain'])."%'";//$_SERVER['PHP_AUTH_USER'];
            $row = $this->getRow($sql);
            if(empty($row)){
                $where .= ' AND 1=1';
            }else{
                $id = $row['ID'];
                $where .= " AND d.`StoreId` = $id";
            }
        }
        if (isset($opts['group']) && !empty($opts['group'])){
            $group = 'GROUP BY a.'.$opts['group'].' '.$opts['sc'].'';
        }

        if(isset($opts['start']) && !empty($opts['start'])){
            $where .= " AND a.`StartDate` >= '".addslashes(date('Y-m-d H:i:s',strtotime($opts['start'])))."'";
        }

        if(isset($opts['end']) && !empty($opts['end'])){
            $where .= " AND a.`EndDate` <= '".addslashes(date('Y-m-d H:i:s',strtotime($opts['end'])))."'";
        }

        if(isset($_SESSION['u']['ID'])){
            $checksql = "select sitetype from publisher_detail where PublisherId='{$_SESSION['u']['ID']}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $where.=" AND f.SupportCoupon = 'YES'";
                }
            }
        }

        $sql = "SELECT
                  COUNT(DISTINCT(a.ID)) AS c
                FROM
                  content_feed a
                  INNER JOIN program_intell b
                    ON a.programid = b.programid
                  INNER JOIN r_domain_program c
                    ON b.`programid` = c.`pid`
                  INNER JOIN r_store_domain d
                    ON c.`did` = d.`DomainId`
                  INNER JOIN store f
					ON d.`StoreId` = f.id
				  INNER JOIN publisher_favorites g
					ON a.`ID` = g.cid
                WHERE b.`IsActive`='active' AND a.`status`='active' AND c.status= 'active' AND g.uid=$uid $where $group";
        $count = $this->objMysql->getFirstRow($sql);
        $return['page_now'] = $page;
        $return['page_total'] = ceil($count['c']/$pagesize);

        $sql = "SELECT
                  distinct a.ID, a.*, f.name as Advertiser_Name
                FROM
                  content_feed a
                  INNER JOIN program_intell b
                    ON a.programid = b.programid
                  INNER JOIN r_domain_program c
                    ON b.`programid` = c.`pid`
                  INNER JOIN r_store_domain d
                    ON c.`did` = d.`DomainId`
                  INNER JOIN store f
					ON d.`StoreId` = f.id
				  INNER JOIN publisher_favorites g
					ON a.`ID` = g.cid
                WHERE b.`IsActive`='active' AND a.`status`='active' AND c.status= 'active' AND g.uid=$uid $where $group LIMIT ".($page-1)*$pagesize.",$pagesize";

        $content = $this->objMysql->getRows($sql);
        $return['content'] = $content;
        return $return;
    }
    function GetContent($opts,$page = 1,$pagesize = 20,$uid){
        $sql_names_set = 'SET NAMES latin1';
        $this->query($sql_names_set);

        $return = '';
        $where = '';
        $group = '';
        $stime = addslashes(date('Y-m-d',time()));
        // only show publisher their category adversiter
        if (isset($opts['categories']) && !empty($opts['categories'])){
            $category = explode(',',$opts['categories']);
            foreach ($category as $k => $v) {
                if(!is_numeric($v))
					unset($category[$k]);
            }
			$categoryId = implode(',',$category);
        }else{
			$sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
			$res = $this->getRow($sql);
			$categoryId = trim($res['CategoryId'],',');
		}
		$categoryArr = explode(',',trim($categoryId,','));
		if(!empty($categoryArr))
		{
			$where .= " AND(";
			foreach($categoryArr as $cateid)
			{
				$where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
			}
			$where = rtrim($where,'OR').")";
		}

        if (isset($opts['chk']) && $opts['chk'] == 1){
            $sql = "select sid from publisher_collect where uid=$uid";
            $res = $this->getRows($sql);
            if(!empty($res)){
                $num = '';
                foreach($res as $k){
                    $num.=$k['sid'].',';
                }
                $num = rtrim($num,',');
                $where.= " AND f.`ID` IN ($num)";
            }
        }
        if (isset($opts['type']) && !empty($opts['type'])){
                $where.= " AND a.Type='".$opts['type']."'";
        }
        if (isset($opts['language']) && !empty($opts['language'])){
                $where.= " AND a.language='".addslashes($opts['language'])."'";
        }
        if(isset($opts['country']) && !empty($opts['country'])){
			$where .= " AND (b.`ShippingCountry` like '%".addslashes($opts['country'])."%' OR b.`ShippingCountry` = '' OR b.`ShippingCountry`  is null)";
        }

        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $opts['keyword'] = preg_replace('/\s/','',$opts['keyword']);
            //进入用户搜索记录�?
            $sql = "select svalue from publisher_search WHERE type='Advertiser' and svalue = '".$opts['keyword']."' and uname =$uid ";//$_SERVER['PHP_AUTH_USER'];
            $res = $this->getRows($sql);
            if(empty($res)){
                $insert_d['type'] ='Advertiser';
                $insert_d['svalue'] = $opts['keyword'];
                $insert_d['uname'] =$uid;
                $this->table('publisher_search')->insert($insert_d);
            }
            $where .= " AND f.`Name` LIKE '%".addslashes($opts['keyword'])."%'";
        }
        if(isset($opts['domain']) && !empty($opts['domain'])){
            $opts['domain'] = preg_replace('/\s/','',$opts['domain']);
            $sql = "select svalue from publisher_search WHERE type='Domain' and svalue = '".$opts['domain']."' and uname =$uid";//$_SERVER['PHP_AUTH_USER'];
            $res = $this->getRows($sql);
            if(empty($res)){
                $insert_d['type'] ='Domain';
                $insert_d['svalue'] = $opts['domain'];
                $insert_d['uname'] =$uid;
                $this->table('publisher_search')->insert($insert_d);
            }
            $sql = "select ID from store WHERE Domains LIKE '%".addslashes($opts['domain'])."%'";//$_SERVER['PHP_AUTH_USER'];
            $row = $this->getRow($sql);
            if(!empty($row))
			{
                $id = $row['ID'];
                $where .= " AND d.`StoreId` = $id";
            }
        }
        if (isset($opts['group']) && !empty($opts['group'])){
                $group = 'ORDER BY a.'.$opts['group'].' '.$opts['sc'].'';
        }

        if(isset($opts['start']) && !empty($opts['start'])){
            $where .= " AND a.`StartDate` < '".addslashes(date('Y-m-d H:i:s',strtotime($opts['start'])))."'";
        }else{
            $where .= " AND a.`StartDate` < '$stime'";
        }

        if(isset($opts['end']) && !empty($opts['end'])){
            $where .= " AND a.`EndDate` <= '".addslashes(date('Y-m-d H:i:s',strtotime($opts['end'])))."'";
        }
        $where .= " AND (a.`EndDate` > '".addslashes(date('Y-m-d H:i:s'))."' OR a.`EndDate` = '0000-00-00 00:00:00')";
        
        if(isset($_SESSION['u']['ID'])){
	    	$checksql = "select sitetype from publisher_detail where PublisherId='{$_SESSION['u']['ID']}'";
	        $checkrow = $this->getRow($checksql);
	        $checkarr = explode('+',$checkrow['sitetype']);
	        foreach($checkarr as $k){
	            if($k == '1_e' || $k == '2_e'){
	                $where.=" AND f.SupportCoupon = 'YES'";
	            }
	        }
        }

        $sql = "SELECT
                  COUNT(DISTINCT(a.ID)) AS c
                FROM
                  content_feed a 
                  INNER JOIN program_intell b 
                    ON a.programid = b.programid
                  INNER JOIN r_domain_program c 
                    ON b.`programid` = c.`pid` 
                  INNER JOIN r_store_domain d 
                    ON c.`did` = d.`DomainId` 
                  INNER JOIN store f
					ON d.`StoreId` = f.id
                WHERE b.`IsActive`='active' AND a.`status`='active' AND c.status= 'active' $where $group";
        $count = $this->objMysql->getFirstRow($sql);
        $return['page_now'] = $page;
        $return['page_total'] = ceil($count['c']/$pagesize);

        $sql = "SELECT
                  distinct a.ID, a.*, f.name as Advertiser_Name
                FROM
                  content_feed a 
                  INNER JOIN program_intell b 
                    ON a.programid = b.programid
                  INNER JOIN r_domain_program c 
                    ON b.`programid` = c.`pid` 
                  INNER JOIN r_store_domain d 
                    ON c.`did` = d.`DomainId` 
                  INNER JOIN store f
					ON d.`StoreId` = f.id
                WHERE b.`IsActive`='active' AND a.`status`='active' AND c.status= 'active' $where $group LIMIT ".($page-1)*$pagesize.",$pagesize";
        $content = $this->objMysql->getRows($sql);
        $sql1= "select svalue from publisher_search where uname = $uid AND type ='Advertiser'";
        $sql2= "select svalue from publisher_search where uname = $uid AND type ='Domain'";
        $adv = $this->getRows($sql1);
        $dom = $this->getRows($sql2);
        $return['content'] = $content;
        $return['adv'] = $adv;
        $return['dom'] = $dom;
        if(!empty($opts['group'])){
            $return['group'] = $opts['group'];
            $return['sc'] = $opts['sc'];
        }
        return $return;
    }
    
    
    
    function GetContentNew($opts,$page = 1,$pagesize = 20,$uid,$export=false,$searchCount=false,$activity='normal'){
        $sql_names_set = 'SET NAMES BINARY';
        $this->query($sql_names_set);
        $return = '';
        $promotionZeroWhere = $where = '';
        $categoryArr = array();
        // only show publisher their category adversiter, pass the rules when the publisher search advertiser name
        if( (isset($opts['keyword']) && !empty($opts['keyword'])) ){// ||  (isset($opts['domain']) && !empty($opts['domain']))
            $categoryArr = array();
        }else{
            $sql = "SELECT Country FROM publisher WHERE ID = ".$_SESSION['u']['ID'];
            $res = $this->getRow($sql);
             
            if($res['Country'] == Constant::COUNTRY_ID_GERMANY || $res['Country'] == Constant::COUNTRY_ID_FRANCE){
                
            }else{
                $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
                $res = $this->getRow($sql);
                $categoryId = trim($res['CategoryId'],',');
                $categoryArr = explode(',',trim($categoryId,','));
            }
        }

        if (isset($opts['categories']) && !empty($opts['categories'])){
            $category_search = explode(',',$opts['categories']);
            foreach ($category_search as $k => $v) {
                if(!is_numeric($v))
                    unset($category_search[$k]);
            }

            if(!empty($categoryArr))
                $categoryArr = array_intersect($categoryArr,$category_search);
            else
                $categoryArr = $category_search;
        }
        
        if(!empty($categoryArr))
        {
            $where .= " AND(";
            foreach($categoryArr as $cateid)
            {
                $where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
            }
            $where = rtrim($where,'OR').")";
        }elseif((!isset($opts['keyword']) || empty($opts['keyword']))){// && (!isset($opts['domain']) || empty($opts['domain'])) 
            $where .= ' AND 1=1';
        }
    
        if (isset($opts['chk']) && $opts['chk'] == 1){
            $sql = "select sid from publisher_collect where uid=$uid";
            $res = $this->getRows($sql);
            if(!empty($res)){
                $num = '';
                foreach($res as $k){
                    $num.=$k['sid'].',';
                }
                $num = rtrim($num,',');
                $where.= " AND f.`ID` IN ($num)";
            }else {
                $where.= " AND 1=0";
            }
        }
        if (isset($opts['type']) && !empty($opts['type'])){
            $where.= " AND a.Type='".$opts['type']."'";
        }
        if (isset($opts['language']) && !empty($opts['language'])){
            $where.= " AND a.language='".addslashes($opts['language'])."'";
        }
        if(isset($opts['country']) && !empty($opts['country'])){
            if(strtolower($opts['country']) == 'uk' || strtolower($opts['country']) == 'gb'){
                $where .= " AND ( FIND_IN_SET('UK',a.country) OR FIND_IN_SET('GB',a.country) )";
            }else {
                $where .= " AND FIND_IN_SET('".strtolower(addslashes($opts['country']))."',a.country)";
            }
        }
    
        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $opts['keyword'] = trim($opts['keyword']);
            //进入用户搜索记录�?
            /* $sql = "select svalue from publisher_search WHERE type='Advertiser' and svalue = '".$opts['keyword']."' and uname =$uid ";//$_SERVER['PHP_AUTH_USER'];
            $res = $this->getRows($sql);
            if(empty($res)){
                $insert_d['type'] ='Advertiser';
                $insert_d['svalue'] = $opts['keyword'];
                $insert_d['uname'] =$uid;
                $this->table('publisher_search')->insert($insert_d);
            } */
            /* if(isset($opts['keyword_type']) && $opts['keyword_type']=='Equal'){
                $where .= " AND (f.`Name` = '".addslashes($opts['keyword'])."' OR f.`NameOptimized` = '".addslashes($opts['keyword'])."')";
            }else { */
                $where .= " AND (f.`Name` LIKE '%".addslashes($opts['keyword'])."%' OR f.`NameOptimized` LIKE '%".addslashes($opts['keyword'])."%')";
//             }
        }

        if(isset($opts['keywords']) && !empty($opts['keywords'])){
            $opts['keywords'] = trim($opts['keywords']);
            $where .= " AND (a.`Title` LIKE '%".addslashes($opts['keywords'])."%' OR a.`Desc` LIKE '%" . addslashes($opts['keywords'])."%')";
        }
        
        if($activity == 'valentine'){
            $where .= " AND ( a.`Title` LIKE '%valentine%' OR a.`Desc` LIKE '%valentine%') ";
        }

        /* if(isset($opts['domain']) && !empty($opts['domain'])){
            $opts['domain'] = preg_replace('/\s/','',$opts['domain']);
            $sql = "select svalue from publisher_search WHERE type='Domain' and svalue = '".$opts['domain']."' and uname =$uid";//$_SERVER['PHP_AUTH_USER'];
            $res = $this->getRows($sql);
            if(empty($res)){
                $insert_d['type'] ='Domain';
                $insert_d['svalue'] = $opts['domain'];
                $insert_d['uname'] =$uid;
                $this->table('publisher_search')->insert($insert_d);
            }
            $where .= " AND f.`Domains` LIKE '%".addslashes($opts['domain'])."%'";
        } */
        if (isset($opts['group']) && !empty($opts['group'])){
            $orderBy = 'ORDER BY a.'.$opts['group'].' '.$opts['sc'].'';
        }else {
            $orderBy = ' ORDER BY a.AddTime DESC,a.ID DESC ';
        }
    
        if (isset($opts['source']) && !empty($opts['source'])){
            $where.= " AND a.source='".addslashes($opts['source'])."'";
        }
        if (isset($opts['sid']) && !empty($opts['sid'])){
            $where.= " AND a.StoreId=".$opts['sid'];
        }
        if(isset($opts['filter']) && !empty($opts['filter']) && $opts['filter']!=''){
            switch ($opts['filter']){
                case 'current':
                    $where .= " AND ( a.`StartDate` <= '".date('Y-m-d H:i:s')."' AND (a.`EndDate` >= '".date('Y-m-d H:i:s')."' OR a.`EndDate` = '0000-00-00 00:00:00') ) ";
                    break;
                case 'upcoming':
                    $where .= " AND ( a.`StartDate` > '".date('Y-m-d H:i:s')."' AND (a.`EndDate` > '".date('Y-m-d H:i:s')."' OR a.`EndDate` = '0000-00-00 00:00:00') ) ";
                    break;
                /* case 'expired':
                    $where .= " AND ( a.`StartDate` < '".date('Y-m-d H:i:s')."' AND a.`EndDate` < '".date('Y-m-d H:i:s')."' AND a.`EndDate` != '0000-00-00 00:00:00' ) ";
                    break; */
            }
        }
        
        if(isset($opts['start']) && !empty($opts['start'])){
            $where .= " AND a.`StartDate` >= '".date('Y-m-d',strtotime($opts['start']))."'";
        }
//         else{
//             $where .= " AND a.`StartDate` <= '".date('Y-m-d H:i:s')."'";
//         }
        if(isset($opts['end']) && !empty($opts['end'])){
            $where .= " AND a.`EndDate` <= '".date('Y-m-d 23:59:59',strtotime($opts['end']))."'";
        }
        $where .= " AND (a.`EndDate` >= '".date('Y-m-d H:i:s')."' OR a.`EndDate` = '0000-00-00 00:00:00')";
        $where .= " AND (a.`StartDate` <= '".date('Y-m-d H:i:s')."' OR a.`StartDate` = '0000-00-00 00:00:00')";
    
        if(isset($_SESSION['u']['ID'])){
            
            $siteType = 'content';
            if(isset($_SESSION['pubAccActiveList']['active'])){
                foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                    $checkarr = explode('+',$temp['SiteTypeNew']);
                    foreach($checkarr as $k){
                        if($k == '1_e' || $k == '2_e'){
                            $siteType = 'coupon';
                            break;
                        }
                    }
                }
            }else {
                $checksql = "select sitetype from publisher_detail where PublisherId='{$_SESSION['u']['ID']}'";
                $checkrow = $this->getRow($checksql);
                $checkarr = explode('+',$checkrow['sitetype']);
                foreach($checkarr as $k){
                    if($k == '1_e' || $k == '2_e'){
                        $siteType = 'coupon';
                        break;
                    }
                }
            }
            
            $where.=" AND pi.SupportType != 'None'";
            $promotionZeroWhere.=" AND pi.SupportType != 'None'";
            if($siteType == 'coupon'){
                $where.=" AND pi.SupportType != 'Content' ";
                $promotionZeroWhere.=" AND pi.SupportType != 'Content' ";
            }else{
                $where.=" AND pi.SupportType != 'Promotion' ";
                $promotionZeroWhere.=" AND pi.SupportType != 'Promotion' ";
            }
            
            $sql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
            $rows_block = $this->getRows($sql);
            $block_affids = array();
            $block_pids = array();
            $block_sids = array();
            foreach($rows_block as $k=>$v){
                if($v['ObjType'] == 'Affiliate'){
                    if($v['AccountType'] == "PublisherId"){
                        $block_affids[] = $v['ObjId'];
                    }else {
                        if(isset($_SESSION['pubAccActiveList']['active'])){
                            if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                $block_affids[] = $v['ObjId'];
                            }
                        }else {
                            $block_affids[] = $v['ObjId'];
                        }
                    }
//                     $block_affids[] = $v['ObjId'];
                }
                if($v['ObjType'] == 'Program'){
                    if($v['AccountType'] == "PublisherId"){
                        $block_pids[] = $v['ObjId'];
                    }else {
                        if(isset($_SESSION['pubAccActiveList']['active'])){
                            if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                $block_pids[] = $v['ObjId'];
                            }
                        }else {
                            $block_pids[] = $v['ObjId'];
                        }
                    }
//                     $block_pids[] = $v['ObjId'];
                }
                if($v['ObjType'] == 'Store'){
                    if($v['AccountType'] == "PublisherId"){
                        $block_sids[] = $v['ObjId'];
                    }else {
                        if(isset($_SESSION['pubAccActiveList']['active'])){
                            if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                $block_sids[] = $v['ObjId'];
                            }
                        }else {
                            $block_sids[] = $v['ObjId'];
                        }
                    }
//                     $block_sids[] = $v['ObjId'];
                }
            }
            if(!empty($block_affids))
                $where .= " AND ( pi.AffId NOT IN (".join(',',$block_affids).") OR pi.AffId is NULL ) ";
            if(!empty($block_pids))
                $where .= " AND a.ProgramId NOT IN (".join(',',$block_pids).")";
            if(!empty($block_sids))
                $where .= " AND a.StoreId NOT IN (".join(',',$block_sids).")";
        }
        
        //查询各个store的数量
        if($searchCount){
            if(isset($opts['storeIds']) && $opts['storeIds']!=""){
                $where.=" AND a.StoreId in (".$opts['storeIds'].") ";
            }
            $sql = "SELECT a.StoreId,count(a.StoreId) StoreIdCount from content_feed_new a LEFT JOIN store f ON a.`StoreId` = f.id LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
                where a.`status`='active' $where GROUP BY a.StoreId";
            $return = $this->objMysql->getRows($sql);
        }else {
            if(!$export){
                $sql = "SELECT
                COUNT(DISTINCT(a.ID)) AS c
                FROM
                content_feed_new a
                LEFT JOIN store f
                ON a.`StoreId` = f.id
                LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId`
                WHERE a.`status`='active' $where  ";
                $count = $this->objMysql->getFirstRow($sql);
                $return['page_now'] = $page/$pagesize+1;
                $return['page_total'] = ceil($count['c']/$pagesize);
                $return['total'] = ceil($count['c']);
            }
            
            $sql = "SELECT
            distinct a.ID, a.*,IF(f.NameOptimized='' or f.NameOptimized is null,f.Name,f.NameOptimized) AS Advertiser_Name,pi.CommissionValue,pi.`CommissionType` as CommissionType,pi.`CommissionUsed` as CommissionUsed,pi.`CommissionCurrency` as CommissionCurrency,pi.PPC 
            FROM
            content_feed_new a
            LEFT JOIN store f 
            ON a.`StoreId` = f.id
            LEFT JOIN program_intell pi 
            ON a.`ProgramId` = pi.`ProgramId`
            WHERE  a.`status`='active' $where  $orderBy LIMIT $page,$pagesize";
            $content = $this->objMysql->getRows($sql);
            
            
            //以下代码是programId为0时做处理的
            //查询ProgramId为0的promotions
            $programZeroList = array();
            foreach ($content as $key=>$val){
                if($val['ProgramId'] == '0'){
                    $programZeroList[$val['ID']] = $val['StoreId'];
                }else {
                    $commissionRangeArr = array();
                    if($val['CommissionValue'] != '' && $val['CommissionValue'] != null){
                        $commissionArr = explode("|", $val['CommissionValue'])[0];
                        $commissionValText = trim($commissionArr,"[]");
                        $commissionValArr = explode(",", $commissionValText);
                        foreach ($commissionValArr as $temp){
                            preg_match("/\d+(\.\d+)?/", $temp,$number);
                            $commissionRangeArr[$number[0]] = $temp;
                        }
                    }else {
                        if($val['CommissionUsed'] == '0'){
                            $commissionRangeArr[0] = 'other';
                        }else if($val['CommissionType'] == 'Value'){
                            if($val['CommissionCurrency'] != ''){
                                $commissionRangeArr[0] = $val['CommissionCurrency'].$val['CommissionUsed'];
                            }else{
                                $commissionRangeArr[0] = "USD".$val['CommissionUsed'];
                            }
                        }else{
                            $commissionRangeArr[0] = $val['CommissionUsed'].'%';
                        }
                    }
                    ksort($commissionRangeArr);
                    if(count($commissionRangeArr)<=1){
                        $content[$key]['CommissionRange'] = current($commissionRangeArr);
                    }else {
                        $content[$key]['CommissionRange'] = current($commissionRangeArr).'~'.end($commissionRangeArr);
                    }
                }
            }
            //若有ProgramId为0的promotions
            if(!empty($programZeroList)){
                /* //找到该publisher的群体大多来自哪个国家
                if(isset($_SESSION['pubAccActiveList']['active'])){
                    $countryList = explode('+', current($_SESSION['pubAccActiveList']['data'])['GeoBreakdown']);
                }else {
                    $sql = 'SELECT GeoBreakdown FROM publisher_detail where Publisherid = '.$uid;
                    $geoBreakdown = $this->getRow($sql);
                    $countryList = explode('+', $geoBreakdown['GeoBreakdown']);
                }
                $userCountry = '';
                if(isset($countryList[0]) && $countryList[0]!=''){
                    $sql = 'SELECT CountryCode from country_codes where id = '.$countryList[0];
                    $rs = $this->getRow($sql);
                    if(isset($rs['CountryCode'])){
                        $userCountry = $rs['CountryCode'];
                    }
                } */
                //通过storeId找出所有的program信息中的commission值
                $programZeroText = implode(',', $programZeroList);
                $sql = 'SELECT rsp.`StoreId`,rsp.`ProgramId`,rsp.`Outbound`,pi.`CommissionType`,pi.`CommissionUsed`,pi.`CommissionCurrency`,pi.`CommissionValue` from r_store_program rsp 
                     LEFT JOIN program_intell pi on pi.`ProgramId` = rsp.`ProgramId` WHERE rsp.`Outbound` != "" and rsp.`StoreId` in ('.$programZeroText.')'.$promotionZeroWhere;
                $rs =  $this->objMysql->getRows($sql);
                $commissionRangeArr = array();
                foreach ($rs as $val){
                    if($val['CommissionValue'] != '' && $val['CommissionValue'] != null){
                        $commissionArr = explode("|", $val['CommissionValue'])[0];
                        $commissionValText = trim($commissionArr,"[]");
                        $commissionValArr = explode(",", $commissionValText);
                        foreach ($commissionValArr as $temp){
                            preg_match("/\d+(\.\d+)?/", $temp,$number);
                            $unit = preg_replace("/[0-9. ]/",'', $temp);
                            $commissionRangeArr[$val['StoreId']]['range'][$unit][number_format($number[0],3)] = $temp;
                        }
                    }else {
                        if($val['CommissionUsed'] == '0'){
                            $commissionRangeArr[$val['StoreId']]['value'] = 'other';
                        }else if($val['CommissionType'] == 'Value'){
                            if($val['CommissionCurrency'] != ''){
                                $commissionRangeArr[$val['StoreId']]['value'] = $val['CommissionCurrency'].$val['CommissionUsed'];
                            }else{
                                $commissionRangeArr[$val['StoreId']]['value'] = "USD".$val['CommissionUsed'];
                            }
                        }else{
                            $commissionRangeArr[$val['StoreId']]['value'] = $val['CommissionUsed'].'%';
                        }
                    }
                }
                foreach ($content as $key=>$val){
                    if($val['ProgramId'] == 0 && isset($commissionRangeArr[$val['StoreId']])){
                        if(isset($commissionRangeArr[$val['StoreId']]['range'])){
                            $val['CommissionRange'] = '';
                            foreach ($commissionRangeArr[$val['StoreId']]['range'] as $tempK=>$tempV){
                                ksort($tempV);
                                if(count($tempV)<=1){
                                    $val['CommissionRange'] .= ','.current($tempV);
                                }else {
                                    $val['CommissionRange'] .= ','.current($tempV).'~'.end($tempV);
                                }
                            }
                            $content[$key]['CommissionRange'] = trim($val['CommissionRange'],',');
                        }else {
                            $content[$key]['CommissionRange'] = $commissionRangeArr[$val['StoreId']]['value'];
                        }
                    }
                }
                /* //循环取数据
                foreach($rs as $key=>$val){
                    //若没有Outbound则退出当前循环
                    if(empty($val['Outbound']) || $val['Outbound'] == ''){
                        unset($rs[$key]);
                        continue;
                    }
                    if (strstr($val['Outbound'], ',')) {
                        $value = explode(',', $val['Outbound']);
                    }else{
                        $value = array($val['Outbound']);
                    }
                    $valueTemp = [];
                    foreach($value as $k=>$v){
                        if(strstr($v,'|')){
                            $valueList = explode('|',$v);
                        }else{
                            $valueList = explode('-',$v);
                        }
                        $valueTemp[] = strtoupper($valueList[1]);
                    }
                    //将Outbound中的所有国家都取出来，拼成字符串
                    $valueText = implode(',', $valueTemp);
                    //若上面的字符串中包含了publisher的用户群体所在国家
                    if($userCountry!='' && strstr($valueText, $userCountry)){
                        //若storeId在数组中已存在的话
                        if(isset($result[$val['StoreId']])){
                            //当存在过了受众国家时
                            if(isset($result[$val['StoreId']]['userCountry'])){
                                //且commission类型一致时，用大的值替换小的(默认按第一种进入的类型来显示commission，否则百分比和数值没法作比较)
                                if($result[$val['StoreId']]['CommissionType'] == $val['CommissionType'] && $val['CommissionUsed']>$result[$val['StoreId']]['CommissionUsed']){
                                    $result[$val['StoreId']]['CommissionUsed'] = $val['CommissionUsed'];
                                }
                            }else {
                                //没有时则初始化加入commission
                                $result[$val['StoreId']]['CommissionType'] = $val['CommissionType'];
                                $result[$val['StoreId']]['CommissionUsed'] = $val['CommissionUsed'];
                                $result[$val['StoreId']]['CommissionCurrency'] = $val['CommissionCurrency'];
                                $result[$val['StoreId']]['userCountry'] = $userCountry;
                            }
                        }else {
                            //没有时初始化加入commission
                            $result[$val['StoreId']]['CommissionType'] = $val['CommissionType'];
                            $result[$val['StoreId']]['CommissionUsed'] = $val['CommissionUsed'];
                            $result[$val['StoreId']]['CommissionCurrency'] = $val['CommissionCurrency'];
                            $result[$val['StoreId']]['userCountry'] = $userCountry;
                        }
                    }else {
                        //若storeId在数组中已存在的话
                        if(isset($result[$val['StoreId']])){
                            //只有当不存在受众国家且commission类型一致时，用大的值替换小的
                            if(!isset($result[$val['StoreId']]['userCountry']) && $result[$val['StoreId']]['CommissionType'] == $val['CommissionType'] && $val['CommissionUsed']>$result[$val['StoreId']]['CommissionUsed']){
                                $result[$val['StoreId']]['CommissionUsed'] = $val['CommissionUsed'];
                            }
                        }else {
                            //没有时初始化加入commission(不包含国家)
                            $result[$val['StoreId']]['CommissionType'] = $val['CommissionType'];
                            $result[$val['StoreId']]['CommissionUsed'] = $val['CommissionUsed'];
                            $result[$val['StoreId']]['CommissionCurrency'] = $val['CommissionCurrency'];
                        }
                    }
                } */
            }
            /* if(!empty($result)){
                foreach ($content as &$val){
                    if(isset($result[$val['StoreId']])){
                        $val['CommissionType'] = $result[$val['StoreId']]['CommissionType'];
                        $val['CommissionUsed'] = $result[$val['StoreId']]['CommissionUsed'];
                        $val['CommissionCurrency'] = $result[$val['StoreId']]['CommissionCurrency'];
                    }
                }
            } */
            /* $sql1= "select svalue from publisher_search where uname = $uid AND type ='Advertiser' order by ID desc limit 10";
            $sql2= "select svalue from publisher_search where uname = $uid AND type ='Domain' order by ID desc limit 10";
            $adv = $this->getRows($sql1);
            $dom = $this->getRows($sql2); */
            $return['content'] = $content;
            /* $return['adv'] = $adv;
            $return['dom'] = $dom; */
            if(!empty($opts['group'])){
                $return['group'] = $opts['group'];
                $return['sc'] = $opts['sc'];
            }
        }
        $sql_names_set = 'SET NAMES latin1';
        $this->query($sql_names_set);
        return $return;
    }

	function TopCilckPromotions($id)
	{
		$sql = "select CategoryId,AdvancedCategoryId from publisher_detail where PublisherId = $id";
		$res = $this->getRow($sql);
		$categoryId = trim(trim($res['CategoryId']."," . $res['AdvancedCategoryId'],','),',');
		$where = '';
		$categoryArr = explode(',',trim($categoryId,','));
		if(!empty($categoryArr))
		{
			$where .= " AND(";
			foreach($categoryArr as $cateid)
			{
				$where .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
			}
			$where = rtrim($where,'OR').")";
		}
		$sql = "SELECT
                  b.`ID` AS StoreId,b.`Name` AS StoreName,a.revenues AS Revenue,a.clicks,e.`PID`,f.`Title`,f.`Desc`,f.`CouponCode`,f.`StartDate`,f.`EndDate`,f.type,f.AffUrl
                FROM
                  (SELECT
                    storeId,
                    SUM(revenues) AS revenues,
                    SUM(clicks) AS clicks
                  FROM
                    statis_domain_br
                  GROUP BY StoreId) a
                INNER JOIN store b
                  ON a.storeId = b.`ID`
                INNER JOIN r_store_domain c
                  ON c.`StoreId` = b.`ID`
                INNER JOIN domain_outgoing_default_other d
                  ON d.`DID` = c.DomainId
                INNER JOIN r_domain_program e
                  ON c.`DomainId`=e.`DID`
                INNER JOIN content_feed_new f
                  ON f.`ProgramId` = e.`PID`
            WHERE
              (f.StartDate < '2017-01-05 00:00:00' OR f.StartDate = '0000-00-00 00:00:00')
              AND
              (f.EndDate > '2017-01-05 00:00:00' OR f.EndDate= '0000-00-00 00:00:00') ".
              $where."
            GROUP BY b.`ID`
            ORDER BY a.clicks DESC
            LIMIT 15";
		$result = $this->getRows($sql);
		return $result;
	}

    function MineCategoryList($id){
        $sql = "select pd.CategoryId,pd.AdvancedCategoryId,p.Country from publisher_detail pd left join publisher p on p.id=pd.PublisherId where pd.PublisherId = $id";
        $res = $this->getRow($sql);
        if($_SESSION['u']['Level'] == 'TIER1')
		{
			$categoryId = trim(trim($res['CategoryId'],',')."," . trim($res['AdvancedCategoryId'],','),',');
		}
		else
		{
			$categoryId = trim($res['CategoryId'],',');
		}
		if($res['Country'] == Constant::COUNTRY_ID_GERMANY || $res['Country'] == Constant::COUNTRY_ID_FRANCE){
		    $where = "";
		}else {
		    $where = " WHERE `ID` IN(".$categoryId.")";
		}
        $category = array();
        $sql = "SELECT * FROM category_std " . $where . " ORDER BY `Name` ASC";
        $arr = $this->objMysql->getRows($sql);
        foreach($arr as $item)
        {
            $category[$item['ID']] = $item['Name'];
        }
        return $category;
    }
	function RecommendAdverTiser($id)
	{
		$sql = "select CategoryId,AdvancedCategoryId from publisher_detail where PublisherId = $id";
		$res = $this->getRow($sql);
		if($_SESSION['u']['Level'] == 'TIER1')
		{
			$categoryId = trim($res['CategoryId']."," . $res['AdvancedCategoryId'],',');
		}
		else
		{
			$categoryId = trim($res['CategoryId'],',');
		}
		$categoryArr = explode(',',trim($categoryId,','));
		$where = ' 1=1 ';
		if(!empty($categoryArr))
		{
			$where .= " AND(";
			foreach($categoryArr as $cateid)
			{
				$where .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
			}
			$where = rtrim($where,'OR').")";
		}
		$limit = 3;
		if($_SESSION['u']['Level'] == 'TIER1')
		{
			$limit = 15;
		}

		$sql = "SELECT b.`ID` AS StoreId,b.`Name` AS StoreName,a.revenues AS Revenue FROM (SELECT storeId,SUM(revenues) AS revenues FROM statis_domain_br GROUP BY StoreId) a INNER JOIN store b ON a.storeId = b.`ID`
  					INNER JOIN r_store_domain c ON c.`StoreId` = b.`ID` INNER JOIN domain_outgoing_default_other f ON f.`DID` = c.DomainId 	WHERE ".$where." GROUP BY b.`ID` ORDER BY a.revenues DESC LIMIT $limit";
		$result = $this->getRows($sql);
        foreach ($result as &$v){
            $v['StoreName'] = ucwords($v['StoreName']);
            unset($v);
        }
		return $result;
	}
    function GetContentCsvFileNew($opts,$apikey){
        set_time_limit(600);
        $info = $this->GetContentNew($opts,1,1,$_SESSION['u']['ID']);
        $count = $info['page_total'];
        $page = 0;
        $pagesize = 1000;
        $page_total = ceil($count/$pagesize);
        header('Pragma:public');
        header('Expires:0');
        header("Content-type:text/csv");
        //header("Content-type:  application/octet-stream;");
        header('Content-Transfer-Encoding: binary');
        header("Content-Disposition: attachment; filename= ContentFeed.csv");
        print(chr(0xEF).chr(0xBB).chr(0xBF)); //add utf8 bom in csv file
        //csv文件头部
        $dataHead = array('LinkID','Advertiser','AdvertiserID','Title','CouponCode','Description','StartDate','EndDate','LinkUrl','PPCLinkUrl');
        echo implode(',',$dataHead)."\n";
        do{

            $data = $this->GetContentNew($opts,$page*1000,1000,$_SESSION['u']['ID'],true);
            $content = $data['content'];
            foreach ($content as $k => &$v){
                $ppclink = '';
                if($v['PPC'] == 3){
                    $ppclink= GO_URL.'/?key='.$apikey.'&linkid='.urlencode($v['EncodeId'])."&ppc=1";
                }
                $data = array(
                    $v['ID'],
                    $v['Advertiser_Name'],
                    $v['StoreId'],
                    str_replace('"','""',$v['Title']),
                    $v['CouponCode'],
                    str_replace('"','""',$v['Desc']),
                    ($v['StartDate']!='0000-00-00 00:00:00')?$v['StartDate']:'N/A',
                    ($v['EndDate']!='0000-00-00 00:00:00')?$v['EndDate']:'N/A',
                    GO_URL.'/?key='.$apikey.'&linkid='.urlencode($v['EncodeId']),
                    $ppclink
                );
                echo '"'.implode('","',$data).'"'."\n";
            }
            $page++;
        }while($page < $page_total);
        exit();
    }
    
    //下载所有的ppc状态为restricted的advertiser
    function GetAdvertiserRestricted($param,$uid){
        require_once 'tools/PHPExcel.php';
        require_once 'tools/PHPExcel/IOFactory.php';
        
        $blockSql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($blockSql);
        
        $where = '';
        $siteType = 'content';
        $doaType = '';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                if((stripos($temp["SiteTypeNew"], '1_e') !== false) || (stripos($temp["SiteTypeNew"], '2_e') !== false)){
                    $siteType = 'coupon';
                    if($doaType == 'content' || $doaType == 'all'){
                        $doaType = 'all';
                    }else {
                        $doaType = 'coupon';
                    }
                }else {
                    if($doaType == 'coupon' || $doaType == 'all'){
                        $doaType = 'all';
                    }else {
                        $doaType = 'content';
                    }
                }
            }
        }else {
            $sql = "select sitetype from publisher_detail where PublisherId='{$uid}'";
            $row = $this->getRow($sql);
            $arr = explode('+',$row['sitetype']);
            foreach($arr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                    break;
                }
            }
        }
        $where.=" AND s.SupportType != 'None'";
        if($siteType == 'coupon'){
            if(!empty($rows_block)){
//                 $where.=" AND doa.SupportType != 'Content' ";
            }else {
                $where.=" AND s.SupportType != 'Content' ";
            }
        }else{
            if(!empty($rows_block)){
//                 $where.=" AND doa.SupportType != 'Content' ";
            }else {
                $where.=" AND s.SupportType != 'Promotion' ";
            }
        }
        
        if($doaType == 'content'){
            if(!empty($rows_block)){
                $where.=" AND doa.SupportType = 'Content' ";
            }
        }else if($doaType == 'coupon'){
            if(!empty($rows_block)){
                $where.=" AND doa.SupportType = 'Promotion' ";
            }
        }
        
        //该publisher属于德国或法国，category不加限制
        $sql = "SELECT Country FROM publisher WHERE ID = ".$uid;
        $rs = $this->getRow($sql);
        if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
            $categoryArr = array();
        }else {
            $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$uid;
            $res = $this->getRow($sql);
            $categoryId = trim($res['CategoryId'],", \t\n\r\0\x0B");
            $categoryArr = explode(',',trim($categoryId,','));
        }
        if(!empty($categoryArr))
        {
            $where .= " AND (";
            foreach($categoryArr as $cateid)
            {
                $where .= " FIND_IN_SET('$cateid',s.CategoryId) OR";
            }
            $where = rtrim($where,'OR')." )";
        }else{
            if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
        
            }else {
                $where .= ' AND 0=1';
            }
        }
        
        if(!empty($rows_block)){
            foreach($rows_block as $k=>$v){
                switch($v['ObjType']){
                    case 'Affiliate':
                        if($v['AccountType'] == "PublisherId"){
                            $where .= " AND s.Affids != '".$v['ObjId']."'";
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where .= " AND s.Affids != '".$v['ObjId']."'";
                                }
                            }else {
                                $where .= " AND s.Affids != '".$v['ObjId']."'";
                            }
                        }
                        break;
                    case 'Program':
                        if($v['AccountType'] == "PublisherId"){
                            $where .= " AND s.Programids != '".$v['ObjId']."'";
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where .= " AND s.Programids != '".$v['ObjId']."'";
                                }
                            }else {
                                $where .= " AND s.Programids != '".$v['ObjId']."'";
                            }
                        }
                        break;
                    case 'Store':
                        if($v['AccountType'] == "PublisherId"){
                            $where .= " AND s.ID != ".$v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where .= " AND s.ID != ".$v['ObjId'];
                                }
                            }else {
                                $where .= " AND s.ID != ".$v['ObjId'];
                            }
                        }
                        break;
                }
            }
        }
        
        
        //4是restrict 3是allowed，若无type则默认为4
        if(isset($param['type']) && $param['type'] == 'allowed'){
            /* $where = '';
            $siteType = 'content';
            if(isset($_SESSION['pubAccActiveList']['active'])){
                foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                    $arr = explode('+',$temp['SiteTypeNew']);
                    foreach($arr as $k){
                        if($k == '1_e' || $k == '2_e'){
                            $siteType = 'coupon';
                            break;
                        }
                    }
                }
            }else {
                $sql = "select sitetype from publisher_detail where PublisherId='{$uid}'";
                $row = $this->getRow($sql);
                $arr = explode('+',$row['sitetype']);
                foreach($arr as $k){
                    if($k == '1_e' || $k == '2_e'){
                        $siteType = 'coupon';
                        break;
                    }
                }
            }
            $where.=" AND s.SupportType != 'None'";
            if($siteType == 'coupon'){
                $where.=" AND s.SupportType != 'Content' ";
            }else{
                $where.=" AND s.SupportType != 'Promotion' ";
            } */
            
            //该publisher属于德国或法国，category不加限制
            /* $sql = "SELECT Country FROM publisher WHERE ID = ".$uid;
            $rs = $this->getRow($sql);
            if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
                $categoryArr = array();
            }else {
                $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$uid;
                $res = $this->getRow($sql);
                $categoryId = trim($res['CategoryId'],", \t\n\r\0\x0B");
                $categoryArr = explode(',',trim($categoryId,','));
            }
            if (isset($param['country']) && $param['country']!==''){
                $country = strtolower($param['country']);
                if($country == 'uk' || $country == 'gb'){
                    $where .= " AND ( FIND_IN_SET('UK',s.CountryCode) OR FIND_IN_SET('GB',s.CountryCode) )";
                }else {
                    $where.=' AND FIND_IN_SET("'.addslashes($country).'",s.CountryCode) ';
                }
            }
            if(!empty($categoryArr))
            {
                $where .= " AND (";
                foreach($categoryArr as $cateid)
                {
                    $where .= " FIND_IN_SET('$cateid',s.CategoryId) OR";
                }
                $where = rtrim($where,'OR')." )";
            }else{
                if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
                    
                }else {
                    $where .= ' AND 0=1';
                }
            } */
            if (isset($param['country']) && $param['country']!==''){
                $country = strtolower($param['country']);
                if($country == 'uk' || $country == 'gb'){
                    $where .= " AND ( FIND_IN_SET('UK',s.CountryCode) OR FIND_IN_SET('GB',s.CountryCode) )";
                }else {
                    $where.=' AND FIND_IN_SET("'.addslashes($country).'",s.CountryCode) ';
                }
            }
            
            if(!empty($rows_block)){
                $sql = "SELECT DISTINCT(rsd.`StoreId`),`Name`,NameOptimized,IFNULL(s.clicks,0) AS clicks FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid LEFT JOIN store AS s ON rsd.storeid = s.id WHERE s.StoreAffSupport = 'YES' AND s.PPCStatus = 'PPCAllowed' ".$where." ORDER BY s.`clicks` DESC";
            }else {
                $sql = "SELECT `Name`,NameOptimized,IFNULL(s.clicks,0) as clicks FROM store s WHERE s.StoreAffSupport = 'YES' AND s.PPCStatus = 'PPCAllowed' ".$where." ORDER BY s.`clicks` DESC";
            }
            $result = $this->getRows($sql);
            
            $objPHPExcel = new PHPExcel();
            // Create a first sheet, representing sales data
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Terms & Conditions');
            $objPHPExcel->getActiveSheet()->setCellValue('A2', '1. ALLOWED - TM+ bidding (i.e. - advertiser + coupon, advertiser name + discount, etc.)');
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. NOT ALLOWED - TM bidding (i.e. - advertiser, advertiser website, advertiser product/service, etc.)');
            $objPHPExcel->getActiveSheet()->setCellValue('A4', '3. NOT ALLOWED - linking directly to any page of the advertiser\'s website(s)');
            $objPHPExcel->getActiveSheet()->setCellValue('A5', '4. NOT ALLOWED - you may not use the advertiser\'s name or product/service name in the site URL');
            // Rename sheet
//             $objPHPExcel->getActiveSheet()->setTitle('Notice');
            
            // Create a new worksheet, after the default sheet
//             $objPHPExcel->createSheet();
            // Add some data to the second sheet, resembling some different data types
//             $objPHPExcel->setActiveSheetIndex(1);
            $i = 8;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Advertiser Name');
            foreach ($result as $k => $v){
                $i++;
                if($v['NameOptimized']!=''){
                    $data = $v['NameOptimized'];
                }else {
                    $data = $v['Name'];
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $data);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->getColor()->setRGB(PHPExcel_Style_Color::COLOR_WHITE);
            $objPHPExcel->getActiveSheet()->getStyle('A8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A8')->getFill()->getStartColor()->setRGB('000000');
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setRGB(PHPExcel_Style_Color::COLOR_WHITE);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setRGB('000000');
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            // Rename 2nd sheet
            $objPHPExcel->getActiveSheet()->setTitle('Advertiser Name List');
//             $objPHPExcel->setActiveSheetIndex(0);
        }else {
            //暂时屏蔽掉
            exit;
            if(!empty($rows_block)){
                $sql = "SELECT DISTINCT(rsd.`StoreId`),`Name`,NameOptimized,IFNULL(s.clicks,0) AS clicks FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid LEFT JOIN store AS s ON rsd.storeid = s.id WHERE s.StoreAffSupport = 'YES' AND s.PPCStatus <> 'PPCAllowed' ".$where." ORDER BY s.`clicks` DESC";
            }else {
                $sql = "SELECT `Name`,NameOptimized,IFNULL(s.clicks,0) as clicks FROM store s WHERE s.StoreAffSupport = 'YES' AND s.PPCStatus <> 'PPCAllowed' ".$where." ORDER BY s.`clicks` DESC";
            }
            $result = $this->getRows($sql);
            
            $objPHPExcel = new PHPExcel();
            // Create a first sheet, representing sales data
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Terms & Conditions');
            $objPHPExcel->getActiveSheet()->setCellValue('A2', '1. NOT ALLOWED - TM+ bidding (i.e. - advertiser + coupon, advertiser name + discount, etc.)');
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. NOT ALLOWED - linking directly to any page of the advertiser\'s website(s)');
            $objPHPExcel->getActiveSheet()->setCellValue('A4', '3. NOT ALLOWED - you may not use the advertiser\'s name or product/service name in the site URL');
            $objPHPExcel->getActiveSheet()->setCellValue('A5', '4. REQUIRED - NEGATIVE MATCH the advertiser\'s name, product/service name and URL');
            $objPHPExcel->getActiveSheet()->setCellValue('A6', '5. We take advertiser bidding as a serious violation and have the right to suspend your account and reverse all commissions for the advertiser and/or your account');
            
            $i = 9;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Advertiser Name');
            foreach ($result as $k => $v){
                $i++;
                if($v['NameOptimized']!=''){
                    $data = $v['NameOptimized'];
                }else {
                    $data = $v['Name'];
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $data);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A9')->getFont()->getColor()->setRGB(PHPExcel_Style_Color::COLOR_WHITE);
            $objPHPExcel->getActiveSheet()->getStyle('A9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A9')->getFill()->getStartColor()->setRGB('000000');
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setRGB(PHPExcel_Style_Color::COLOR_WHITE);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setRGB('000000');
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            // Rename 2nd sheet
            $objPHPExcel->getActiveSheet()->setTitle('Advertiser Name List');
        }
        
        header('Content-Type: application/vnd.ms-excel;charset=UTF-8');
        header('Content-Disposition: attachment;filename="Advertiser.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
    
    //下载黑五的content feed
    function GetValentineContentFeed(){
        require_once 'tools/PHPExcel.php';
        require_once 'tools/PHPExcel/IOFactory.php';
    
        $category_fr = array(
            'Accomodation & Rentals & Vacation'=>'Tourisme',
            'Air Travel'=>'Tourisme',
            'Arts & Entertainment'=>'Loisire & Culture',
            'Automotive'=>'Auto & Moto',
            'Banking & Financial Services'=>'Services & Proximité',
            'Beauty & Hair'=>'Beauté & Bien-être',
            'Betting & Gaming'=>'Loisirs & Culture',
            'Business Solutions'=>'Services & Proximité',
            'Cameras & Photo'=>'High Tech',
            'Cell Phones & Mobile'=>'High Tech',
            'Computer & Software'=>'High Tech',
            'Consumer Electronics'=>'High Tech',
            'Education'=>'Loisirs & Culture',
            'Family & Children'=>'Enfants',
            'Food & Spirits & Tobacco'=>'Alimentation & boisson',
            'Green (All Verticals)'=>'Alimentation & boisson',
            'Groceries & Flowers & Gifts'=>'Alimentation & boisson',
            'Health & Pharmacy'=>'Beauté & Bien-être',
            'Home & Garden'=>'Maison & Jardin',
            'Insurance & Legal'=>'Services & Proximité',
            'Jewelry & Watches'=>'Mode & Accessoires',
            'Loan & Other Financial Services'=>'Services & Proximité',
            'Men\'s Accessories'=>'Mode & Accessoires',
            'Men\'s Fashion'=>'Mode & Accessoires',
            'Motorcycle'=>'Auto & Moto',
            'Music & TV'=>'Loisirs & Culture',
            'News, Books & Magazines'=>'Loisirs & Culture',
            'Online Services'=>'High Tech',
            'Other'=>'Autres',
            'Outdoor Activities'=>'Sport',
            'Pets'=>'Services & Proximité',
            'Real Estate'=>'Services & Proximité',
            'Self-Help & Dating'=>'Services & Proximité',
            'Shopping Malls'=>'Alimentation & boisson',
            'Sports & Fitness'=>'Sport',
            'Toys & Hobbies'=>'Enfants',
            'Women\'s Accessories'=>'Mode & Accessoires',
            'Women\'s Fashion'=>'Mode & Accessoires',
        );
        $category_store_set = array(
            'Aliexpress'=>'High Tech, & Mode & Accessoires',
            'Banggood'=>'High Tech',
            'MAGIX'=>'High Tech',
            '365Tickets'=>'Tourisme',
            'AllSaints'=>'Mode & Accessoires',
            'Baleària'=>'Tourisme',
            'Barcelo.com FR'=>'Tourisme',
            'Gearbest'=>'High Tech',
            'KoreanMall'=>'Mode & Accessoires',
            'Les maux de dos'=>'Beauté & Bien-être',
            'Lilysilk'=>'Maison & Jardin',
            'Look Fantastic'=>'Beauté & Bien-être',
            'Mama Shelter'=>'Tourisme, Restaurant',
            'metalmonde'=>'Mode & Accessoires',
            'Milanoo'=>'Mode & Accessoires',
            'Nastydress'=>'Mode & Accessoires',
            'Newchic'=>'Mode & Accessoires',
            'RoseGal'=>'Mode & Accessoires',
            'SammyDress'=>'Mode & Accessoires',
            'Spartoo'=>'Mode & Accessoires',
            'SportsShoes'=>'Mode & Accessoires, Sport',
            'Tidebuy'=>'Mode & Accessoires',
            'TomTop'=>'Mode & Accessoires',
            'Webdistrib.com'=>'High Tech',
            'Yoins'=>'Mode & Accessoires',
            'ZAFUL'=>'Mode & Accessoires',
            'zzcostumes'=>'Mode & Accessoires',
        );
        $sql = "SELECT ID,`Name` FROM category_std ORDER BY `Name` ASC";
        $rows_cate = $this->getRows($sql);
        $map_category = array();
        foreach($rows_cate as $k=>$v){
            $map_category[$v['ID']] = $v['Name'];
        }
        
        $siteType = 'content';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                $checkarr = explode('+',$temp['SiteTypeNew']);
                foreach($checkarr as $k){
                    if($k == '1_e' || $k == '2_e'){
                        $siteType = 'coupon';
                        break;
                    }
                }
            }
        }
        $where = "";
        if($siteType == 'coupon'){
            $where.=" AND `pi`.SupportType != 'Content' ";
        }else{
            $where.=" AND `pi`.SupportType != 'Promotion' ";
        }
        
        $sqlCountry = "SELECT Country FROM publisher WHERE ID = ".$_SESSION['u']['ID'];
        $res = $this->getRow($sqlCountry);
        if($res['Country'] == Constant::COUNTRY_ID_GERMANY || $res['Country'] == Constant::COUNTRY_ID_FRANCE){
        
        }else{
            $sqlCategory = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
            $res = $this->getRow($sqlCategory);
            $categoryId = trim($res['CategoryId'],',');
            $categoryArr = explode(',',trim($categoryId,','));
        }
        if(!empty($categoryArr))
        {
            $where .= " AND(";
            foreach($categoryArr as $cateid){
                $where .= " FIND_IN_SET('$cateid',s.CategoryId) OR";
            }
            $where = rtrim($where,'OR').")";
        }else {
            $where .= " AND 1=0";
        }
        
        $blocksql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($blocksql);
        $block_affids = array();
        $block_pids = array();
        $block_sids = array();
        foreach($rows_block as $k=>$v){
            if($v['ObjType'] == 'Affiliate'){
                if($v['AccountType'] == "PublisherId"){
                    $block_affids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_affids[] = $v['ObjId'];
                        }
                    }else {
                        $block_affids[] = $v['ObjId'];
                    }
                }
            }
            if($v['ObjType'] == 'Program'){
                if($v['AccountType'] == "PublisherId"){
                    $block_pids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_pids[] = $v['ObjId'];
                        }
                    }else {
                        $block_pids[] = $v['ObjId'];
                    }
                }
            }
            if($v['ObjType'] == 'Store'){
                if($v['AccountType'] == "PublisherId"){
                    $block_sids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_sids[] = $v['ObjId'];
                        }
                    }else {
                        $block_sids[] = $v['ObjId'];
                    }
                }
            }
        }
        if(!empty($block_affids)){
            $where .= " AND ( pi.AffId NOT IN (".join(',',$block_affids).") OR pi.AffId is NULL ) ";
        }
        if(!empty($block_pids)){
            $where .= " AND cfn.ProgramId NOT IN (".join(',',$block_pids).")";
        }
        if(!empty($block_sids)){
            $where .= " AND cfn.StoreId NOT IN (".join(',',$block_sids).")";
        }
        
        $where .= " AND (cfn.`EndDate` >= '".date('Y-m-d H:i:s')."' OR cfn.`EndDate` = '0000-00-00 00:00:00')";
        $where .= " AND (cfn.`StartDate` <= '".date('Y-m-d H:i:s')."' OR cfn.`StartDate` = '0000-00-00 00:00:00')";
        
        $sql = "SELECT s.ID, IF( NameOptimized = '' OR NameOptimized IS NULL, NAME, NameOptimized ) AS Advertiser_Name, s.LogoName AS Logo, s.CategoryId,EncodeId,StoreId,Title,CouponCode,StartDate,EndDate,country,cfn.Desc
         FROM content_feed_new cfn LEFT JOIN store s ON s.`ID` = cfn.`StoreId` LEFT JOIN program_intell `pi` ON `pi`.ProgramId = cfn.`ProgramId` WHERE s.`StoreAffSupport` = 'YES' AND cfn.`Status`='Active' AND ( `Title` LIKE '%valentine%' OR `Desc` LIKE '%valentine%' ) $where order by Advertiser_Name ";
        $activeStore= $this->getRows($sql);
        $objPHPExcel = new PHPExcel();
        // Create a first sheet, representing sales data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Advertiser');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Logo');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Category');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Title');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'CouponCode');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Description');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'StartDate');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'EndDate');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'LinkUrl');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'ShippingCountry');
        $i = 2;
        foreach ($activeStore as $k => $v){
            $logo = '';
            if(!empty($v['Logo'])){
                $image_arr = explode(',',$v['Logo']);
                $logo = 'http://www.brandreward.com/img/adv_logo/'.$image_arr[0];
            }
            
            $category_str = '';
            $category_id = '';
            if(!empty($v['CategoryId'])){
                $category_arr = array();
                $category_arr_id = array();
                $cids = explode(',',$v['CategoryId']);
                foreach($cids as $c){
                    if($c == '100' || empty($c))
                        continue;
                    if(isset($opts['language'])){
                        if(isset($map_category[$c]) && isset($category_fr[$map_category[$c]])){
                            $category_arr[] = $category_fr[$map_category[$c]];
                            $category_arr_id[] = $c;
                        }
                    }else{
                        $category_arr[] = $map_category[$c];
                        $category_arr_id[] = $c;
                    }
                }
                $category_arr = array_unique($category_arr);
                $category_arr_id = array_unique($category_arr_id);
                $category_str = join(',',$category_arr);
                $category_id = join(',',$category_arr_id);
            }
            
            if(isset($category_store_set[$v['Advertiser_Name']])){
                $category_str = $category_store_set[$v['Advertiser_Name']];
                $category_id = '';
            }
            
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Advertiser_Name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $logo);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $category_str);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $v['Title']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $v['CouponCode']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, str_replace(array("\r\n", "\r", "\n"), " ", $v['Desc']));
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $v['StartDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $v['EndDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'http://r.brandreward.com/?key='.reset($_SESSION['pubAccActiveList']['data'])['ApiKey'].'&linkid='.urlencode($v['EncodeId']));
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i, empty($v['country'])?'global':$v['country']);
            $i++;
        }
        // Rename 2nd sheet
        $objPHPExcel->getActiveSheet()->setTitle('Content Feed List');
        
        header('Content-Type: application/vnd.ms-excel;charset=UTF-8');
        header('Content-Disposition: attachment;filename="Valentine.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }


    function GetProductFeed($opts,$page = 1,$pagesize = 20){
        $sql_names_set = 'SET NAMES binary';
        $this->query($sql_names_set);
    
        $return = '';
        $where = '';
        $group = '';
        $stime = addslashes(date('Y-m-d',time()));
        
        $sql = "SELECT Country FROM publisher WHERE ID = ".$_SESSION['u']['ID'];
        $res = $this->getRow($sql);
        $categoryArr = array();
        if($res['Country'] == Constant::COUNTRY_ID_GERMANY || $res['Country'] == Constant::COUNTRY_ID_FRANCE){
            
        }else{
            $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
            $res = $this->getRow($sql);
            $categoryId = trim($res['CategoryId'],',');
            $categoryArr = explode(',',trim($categoryId,','));
        }
        if (isset($opts['categories']) && !empty($opts['categories'])){
            $category_search = explode(',',$opts['categories']);
            foreach ($category_search as $k => $v) {
                if(!is_numeric($v))
                    unset($category_search[$k]);
            }
        
            if(!empty($categoryArr)){
                $categoryArr = array_intersect($categoryArr,$category_search);
            }else{
                $categoryArr = $category_search;
            }
        }
        if(!empty($categoryArr)){
            $where .= " AND(";
            foreach($categoryArr as $cateid)
            {
                $where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
            }
            $where = rtrim($where,'OR').")";
        }
        
        $siteType = 'content';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                $checkarr = explode('+',$temp['SiteTypeNew']);
                foreach($checkarr as $k){
                    if($k == '1_e' || $k == '2_e'){
                        $siteType = 'coupon';
                        break;
                    }
                }
            }
        }else {
            $checksql = "select sitetype from publisher_detail where PublisherId='{$_SESSION['u']['ID']}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                    break;
                }
            }
        }
        $where.=" AND pi.SupportType != 'None'";
        if($siteType == 'coupon'){
            $where.=" AND pi.SupportType != 'Content' ";
        }else{
            $where.=" AND pi.SupportType != 'Promotion' ";
        }
        
        $sql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($sql);
        $block_affids = array();
        $block_pids = array();
        $block_sids = array();
        foreach($rows_block as $k=>$v){
            if($v['ObjType'] == 'Affiliate'){
                if($v['AccountType'] == "PublisherId"){
                    $block_affids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_affids[] = $v['ObjId'];
                        }
                    }else {
                        $block_affids[] = $v['ObjId'];
                    }
                }
            }
            if($v['ObjType'] == 'Program'){
                if($v['AccountType'] == "PublisherId"){
                    $block_pids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_pids[] = $v['ObjId'];
                        }
                    }else {
                        $block_pids[] = $v['ObjId'];
                    }
                }
            }
            if($v['ObjType'] == 'Store'){
                if($v['AccountType'] == "PublisherId"){
                    $block_sids[] = $v['ObjId'];
                }else {
                    if(isset($_SESSION['pubAccActiveList']['active'])){
                        if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                            $block_sids[] = $v['ObjId'];
                        }
                    }else {
                        $block_sids[] = $v['ObjId'];
                    }
                }
            }
        }
        if(!empty($block_affids)){
            $where .= " AND ( pi.AffId NOT IN (".join(',',$block_affids).") OR pi.AffId is NULL ) ";
        }
        if(!empty($block_pids)){
            $where .= " AND a.ProgramId NOT IN (".join(',',$block_pids).")";
        }
        if(!empty($block_sids)){
            $where .= " AND a.StoreId NOT IN (".join(',',$block_sids).")";
        }
        
        if (isset($opts['language']) && !empty($opts['language'])){
            $where.= " AND a.language='".addslashes($opts['language'])."'";
        }
        
        if(isset($opts['country']) && !empty($opts['country'])){
            if(strtolower($opts['country']) == 'uk' || strtolower($opts['country']) == 'gb'){
                $where .= " AND ( FIND_IN_SET('UK',a.country) OR FIND_IN_SET('GB',a.country) )";
            }else {
                $where .= " AND FIND_IN_SET('".strtolower(addslashes($opts['country']))."',a.country)";
            }
        }
    
        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $opts['keyword'] = trim($opts['keyword']);
            $where .= " AND (a.`ProductName` LIKE '%".addslashes($opts['keyword'])."%' OR a.`ProductDesc` LIKE '%" . addslashes($opts['keyword'])."%' OR f.`Name` LIKE '%".addslashes($opts['keyword'])."%' OR f.`NameOptimized` LIKE '%".addslashes($opts['keyword'])."%')";
        }
        
        if (isset($opts['chk']) && $opts['chk'] == 1){
            $collectSql = "select sid from publisher_collect where uid='".$_SESSION['u']['ID']."'";
            $collectResult = $this->getRows($collectSql);
            if(!empty($collectResult)){
                $num = '';
                foreach($collectResult as $k){
                    $num.=$k['sid'].',';
                }
                $num = rtrim($num,',');
                $where.= " AND a.`StoreId` IN ($num)";
            }else {
                $where.= " AND 1=0";
            }
        }
        
        /* if(isset($opts['collect']) && !empty($opts['collect'])){
            $collectSql = "select count(1) as cc from publisher_collect where uid = '".$_SESSION['u']['ID']."'";
            $collectResult = $this->objMysql->getRow($collectSql);
            var_dump($collectResult);exit;
            $where.=' AND a.`StoreId` IN (select sid from publisher_collect where uid = '.$_SESSION['u']['ID'].')';
        } */
        
        $where .= " AND ProductLocalImage != '' ";
        $where .= " AND ProductCurrency != '' ";
    
        $sql = "SELECT
        COUNT(DISTINCT(a.ID)) AS c
        FROM
        product_feed a
        LEFT JOIN store f ON a.`StoreId` = f.id 
        LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
        WHERE  a.`status`='active' $where";
        $count = $this->objMysql->getFirstRow($sql);
        $return['page_now'] = $page/$pagesize+1;
        $return['page_total'] = ceil($count['c']/$pagesize);
    
    
    
        $sql = "SELECT
        a.id,a.ProductName,a.ProductDesc,a.ProductImage,a.ProductLocalImage,a.ProductPrice,a.ProductCurrency,a.ProductCurrencySymbol,a.LastUpdateTime,a.EncodeId,f.id as sid,IF(f.NameOptimized='' or f.NameOptimized is null,f.Name,f.NameOptimized) as storeName
        FROM
        product_feed a
        LEFT JOIN store f  ON a.`StoreId` = f.id 
        LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
        WHERE a.`status`='active' $where ORDER BY a.AddTime desc LIMIT $page,$pagesize";
        //echo $sql.'<br/>';
        $content = $this->objMysql->getRows($sql);
    
        /* foreach ($content as $key=>$value){
    
            $sql = "select b.name from program a left join wf_aff b on a.affid = b.id where a.id = {$value['ProgramId']}  limit 1";
            $aff = $this->objMysql->getFirstRow($sql);
            $content[$key]['affName'] = $aff['name'];
        } */
        $return['content'] = $content;
    
        $sql_names_set = 'SET NAMES latin1';
        $this->query($sql_names_set);
         
        return $return;
    }
}