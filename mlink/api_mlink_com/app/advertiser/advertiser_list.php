<?php
global $_cf,$_req,$_db,$_user;
$opt = array();
$outformat = isset($_req['outformat'])?$_req['outformat']:'txt';
$page = isset($_req['page'])?intval($_req['page']):1;
$pagesize = isset($_req['pagesize'])?intval($_req['pagesize']):100;
if(isset($_req['domain']) && !empty($_req['domain'])){
    $opt['domain'] = getHostUrl($_req['domain']);
}
if(isset($_req['country']) && !empty($_req['country'])){
    $opt['country'] = $_req['country'];
}
if(isset($_req['category']) && !empty($_req['category'])){
    $opt['categories'] = $_req['category'];
}
if(isset($_req['recommend']) && !empty($_req['recommend'])){
    $opt['recommend'] = $_req['recommend'];
}
if(isset($_req['favor']) && $_req['favor'] == 1){
    $opt['chk'] = 1;
}
if(isset($_req['key']) && !empty($_req['key'])){
    $opt['key'] = $_req['key'];
}
$opt['outformat'] = $outformat;

$content = getAdvertiserList($opt,$page,$pagesize,$_user['PublisherId'],$_user['ID']);

if(isset($_req['xo']) && $_req['xo'] == 'ox'){
    echo '<pre>';print_r($content);
}else{
    arr_out_format($content,$outformat);
}

exit();

function getAdvertiserList($search,$page = 1,$page_size = 20,$uid,$uaid){
    global $_db;
    $where_str_store = '';
    
    $checksql = 'select ID,SiteTypeNew from publisher_account pa where pa.`ApiKey` = "'.addslashes($search['key']).'" AND pa.`Status` = "Active"';
    $checkrow = $_db->getFirstRow($checksql);
    $checkarr = explode('+',$checkrow['SiteTypeNew']);
    $where_str_store.=" AND a.SupportType != 'None'";
    $siteType = 'content';
    foreach($checkarr as $k){
        if($k == '1_e' || $k == '2_e'){
            $siteType = 'coupon';
        }
    }
    
//     $site = ' site IN(';
//     $checksql = "select sitetype from publisher_detail where PublisherId=".intval($uid);
//     $checkrow = $_db->getFirstRow($checksql);
//     $checkarr = explode('+',$checkrow['sitetype']);
//     $where_str_store.=" AND a.SupportType != 'None'";
//     $siteType = 'content';
//     foreach($checkarr as $k){
//         if($k == '1_e' || $k == '2_e'){
//             $siteType = 'coupon';
//         }
//     }
//     $sitesql = 'select ApiKey from publisher_account where publisherId='.intval($uid);
//     $siterow = $_db->getRows($sitesql);
//     foreach($siterow as $k){
//         $site.= "'".$k['ApiKey']."',";
//     }
//     $site = rtrim($site,',').')';

    $sql = "SELECT * FROM block_relationship WHERE ((AccountId = ".intval($uaid)." AND AccountType = 'AccountId') OR (AccountId = ".intval($uid)." AND AccountType = 'PublisherId')) AND `Status` = 'Active'";
    $rows_block = $_db->getRows($sql);
    
    if($siteType == 'coupon'){
        $where_str_store.=" AND a.SupportType != 'Content' ";
        if(!empty($rows_block)){
            $where_str_store.=" AND doa.SupportType = 'Promotion' ";
        }
    }else{
        $where_str_store.=" AND a.SupportType != 'Promotion' ";
        if(!empty($rows_block)){
            $where_str_store.=" AND doa.SupportType = 'Content' ";
        }
    }
    if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
        $where_str_store .= ' AND (a.NameOptimized LIKE "'.addslashes(trim($search['store_keywords'])).'%" OR a.Name LIKE "'.addslashes(trim($search['store_keywords'])).'%")';
    }
    if(isset($search['domain']) && !empty($search['domain'])){
//         $search['domain'] = preg_replace('/\s/','',$search['domain']);
//         $where_str_store .= ' AND (a.Domains LIKE "%'.addslashes(trim($search['domain'])).'%")';
        $where_str_store .= ' AND FIND_IN_SET("'.addslashes($search['domain']).'",a.Domains) ';
    }
    if (isset($search['country']) && !empty($search['country']))
        $where_str_store.=' AND FIND_IN_SET("'.addslashes($search['country']).'",a.CountryCode) ';
    if(isset($search['collect']) && !empty($search['collect'])){
        $where_str_store.=' AND a.ID IN(select sid from publisher_collect where uid = '.intval($uid).')';
    }

    if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
        $categoryArr = array();
    }else{
        //该publisher属于德国或法国，category不加限制
        $sql = "SELECT Country FROM publisher WHERE ID = ".$uid;
        $rs = $_db->getFirstRow($sql);
        if($rs['Country'] == 7 || $rs['Country'] == 82){
            $categoryArr = array();
        }else {
            $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$uid;
            $res = $_db->getFirstRow($sql);
            $categoryId = trim($res['CategoryId'],", \t\n\r\0\x0B");
            $categoryArr = explode(',',trim($categoryId,','));
        }
    }

    if(isset($search['categories']) && !empty($search['categories'])){
        $category_search = explode(',',trim($search['categories'],", \t\n\r\0\x0B"));
        if(!empty($categoryArr))
            $categoryArr = array_intersect($categoryArr,$category_search);
        else
            $categoryArr = $category_search;
    }
    
    if(!empty($categoryArr))
    {
        $where_str_store .= " AND (";
        foreach($categoryArr as $cateid)
        {
            $where_str_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
        }
        $where_str_store = rtrim($where_str_store,'OR')." )";
    }elseif(!isset($search['store_keywords']) || empty($search['store_keywords'])){
        if(isset($rs['Country']) && ($rs['Country'] == 7 || $rs['Country'] == 82)){
        
        }else {
            $where_str_store .= ' AND 0=1';
        }
    }
    if(isset($search['recommend']) && !empty($search['recommend'])){
        $where_str = '';
        if (isset($search['country']) && !empty($search['country']))
            $where_str = ' WHERE country = "'.addslashes($search['country']).'"';
        $sql = "SELECT * FROM store_recommend_by_am".$where_str;
        $store_recommend = array();
        $rows = $_db->getRows($sql);
        foreach($rows as $k=>$v){
            $store_recommend[$v['storeid']] = $v['storeid'];
        }

        $store_ids = array_keys($store_recommend);
        if(!empty($store_ids)){
            $where_str_store .= ' AND a.ID IN ('.join(',',$store_ids).')';
        }else{
            $where_str_store .= ' AND 0=1';
        }
    }

    if (isset($search['chk']) && $search['chk'] == 1){
        $sql = "select sid from publisher_collect where uid=".$uid;
        $res = $_db->getRows($sql);
        if(!empty($res)){
            $arr = array();
            foreach($res as $k){
                $arr[]=$k['sid'];
            }
            
            $where_str_store .= ' AND a.ID IN ('.join(',',$arr).')';
        }else{
            $where_str_store .= ' AND 0=1';
        }
    }

    $domain_list_where = "";

    if(!empty($rows_block)){
        foreach($rows_block as $k=>$v){
            switch($v['ObjType']){
                case 'Affiliate':
                    $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
                    $domain_list_where .= " AND c.AffId != '".$v['ObjId']."'";
                    break;
                case 'Program':
                    $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
                    $domain_list_where .= " AND c.ProgramId != '".$v['ObjId']."'";
                    break;
                case 'Store':
                    $where_str_store .= " AND a.ID != ".$v['ObjId'];
                    $domain_list_where .= " AND b.`StoreId` != '".$v['ObjId']."'";
                    break;
            }
        }

        if($siteType == 'coupon'){
//             $where_str_store .= " AND a.SupportType != 'Mixed' ";
            $domain_list_where .= " AND a.SupportType != 'Content'";
        }
    }

    $return_d = array();
    if(!empty($rows_block)){
        $sql = "SELECT count(distinct(a.ID)) as c FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS b ON doa.did = b.domainid LEFT JOIN store AS a ON b.storeid = a.id left join program_intell as c on doa.`PID` = c.`ProgramId` WHERE a.`StoreAffSupport` = 'YES'".$where_str_store.$domain_list_where;
        $row = $_db->getFirstRowColumn($sql);
        $count = intval($row);
        $sql = "SELECT a.ID, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS `Name`, a.LogoName AS Image, a.CategoryId,a.LogoStatus FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS b ON doa.did = b.domainid LEFT JOIN store AS a ON b.storeid = a.id left join program_intell as c on doa.`PID` = c.`ProgramId` WHERE a.`StoreAffSupport` = 'YES' $where_str_store $domain_list_where GROUP BY StoreId ORDER BY `Name` ASC,a.ID LIMIT ". ($page - 1) * $page_size . ',' . $page_size;
//         $sql = "SELECT doa.site,doa.`Key`,doa.`ID` AS doaid,b.StoreId,a.ID, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS `Name`, a.LogoName AS Image, a.CategoryId,a.LogoStatus FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS b ON doa.did = b.domainid LEFT JOIN store AS a ON b.storeid = a.id left join program_intell as c on doa.`PID` = c.`ProgramId` WHERE a.`StoreAffSupport` = 'YES' $where_str_store $domain_list_where GROUP BY StoreId ORDER BY `Name` ASC,a.ID LIMIT ". ($page - 1) * $page_size . ',' . $page_size;
        $rows = $_db->getRows($sql);
    }else {
        $sql = "SELECT COUNT(*) as c FROM store a WHERE a.`StoreAffSupport` = 'YES'".$where_str_store;
        $row = $_db->getFirstRowColumn($sql);
        $count = intval($row);
        $sql = "SELECT a.ID, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS `Name`, a.LogoName as Image, a.CategoryId,a.LogoStatus FROM store a WHERE a.`StoreAffSupport` = 'YES' $where_str_store ORDER BY `Name` ASC,a.ID LIMIT ". ($page - 1) * $page_size . ',' . $page_size;
        $rows = $_db->getRows($sql);
    }
    if(!empty($rows)){
//         if(!empty($rows_block)){
//             $sids = array();
//             $tmp_country_domain = array();
//             foreach($rows as $k=>$v){
//                 $sids[] = $v['ID'];
//                 $country = $v['site']=='uk'?'gb':$v['site'];
//                 $tmp_country_domain[$v['StoreId']]['site'][] = $country;
//                 $tmp_country_domain[$v['StoreId']]['Key'][] = $v['Key'];
//             }
//         }else {
            $sids = array();
            foreach($rows as $k=>$v){
                $sids[] = $v['ID'];
            }
            $sql = "SELECT a.site,a.Key,MIN(a.DefaultOrder),a.`ID`,b.StoreId FROM domain_outgoing_all AS a LEFT JOIN r_store_domain AS b ON a.`DID` = b.`DomainId` left join program_intell as c on a.`PID` = c.`ProgramId` WHERE b.`StoreId` IN (".join(',',$sids).") ".$domain_list_where." GROUP BY a.site,a.Key";
            $rows_country_domain = $_db->getRows($sql);
            $tmp_country_domain = array();
            foreach($rows_country_domain as $k=>$v){
                $country = $v['site']=='uk'?'gb':$v['site'];
                $tmp_country_domain[$v['StoreId']]['site'][] = $country;
                $tmp_country_domain[$v['StoreId']]['Key'][] = $v['Key'];
            }
//         }

        $sql = 'SELECT ID,Name FROM category_std';
        $rows_category = $_db->getRows($sql);
        $tmp_category_map = array();
        foreach($rows_category as $k=>$v){
            $tmp_category_map[$v['ID']] = $v['Name'];
        }
        
        foreach($rows as $k=>$v){
            if(isset($tmp_country_domain[$v['ID']]['site'])){
                $country_list = array_unique($tmp_country_domain[$v['ID']]['site']);
                $domain_list = array_unique($tmp_country_domain[$v['ID']]['Key']);

                //$rows[$k]['Countries'] = '"'.join('","',$country_list).'"';
                //$rows[$k]['Domains'] = '"'.join('","',$domain_list).'"';
                $rows[$k]['Countries'] = array_values($country_list);
                $rows[$k]['Domains'] = array_values($domain_list);
            }
            $CategoryId = $v['CategoryId'];
            $categoryid_arr = explode(',',$CategoryId);
            $categorystr_arr = array();
            foreach($categoryid_arr as $cid){
                if(isset($tmp_category_map[$cid]))
                    $categorystr_arr[] = $tmp_category_map[$cid];
            }
            //$rows[$k]['Category'] = '"'.join('","',$categorystr_arr).'"';
            $rows[$k]['Category'] = array_values($categorystr_arr);
            unset($rows[$k]['CategoryId']);
            $rows[$k]['Name'] = ucfirst($v['Name']);
            

            if(!empty($v['Image'])){
                $image_arr = explode(',',$v['Image']);
                if($v['LogoStatus'] == '2'){
                    $rows[$k]['Image'] = 'http://www.brandreward.com/img/logo_program/'.$image_arr[0];
                }else {
                    $rows[$k]['Image'] = 'http://www.brandreward.com/img/adv_logo/'.$image_arr[0];
                }
            }
            
            if (isset($search['country']) && $search['country']){
                $rows[$k]['Countries'] = $search['country'];
            }
           
            if($search['outformat'] == 'txt'){
                $rows[$k]['Countries'] = '"'.join('","',$country_list).'"';
                $rows[$k]['Domains'] = '"'.join('","',$domain_list).'"';
                $rows[$k]['Category'] = '"'.join('","',$categorystr_arr).'"';
            }else if($search['outformat'] == 'csv'){
                $rows[$k]['Countries'] = join(',',$country_list);
                $rows[$k]['Domains'] = join(',',$domain_list);
                $rows[$k]['Category'] = join(',',$categorystr_arr);
            }
            unset($rows[$k]['LogoStatus']);
        }
    }

    $return_d['response']['PageTotal'] = ceil($count / $page_size);
    $return_d['response']['PageNow'] = $page;
    $return_d['response']['Num'] = $count;
    $return_d['response']['NumReturn'] = count($rows);

    $return_d['data'] = $rows;

    return $return_d;
}

//获取url的domain
function getHostUrl($url){
    $host = '';
    $url = trim($url);
    if(!empty($url)){
        if(!preg_match("/^https?:\\/\\//i",$url)){
            $url = "http://".$url;
        }
        $purl = parse_url($url);
        if($purl){
            $host = $purl['host'];
            if(preg_match("/^www./",$host)){
                $host = substr($host, 4);
            }
        }
    }
    return $host;
}

?>
