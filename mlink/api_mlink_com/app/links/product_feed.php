<?php
global $_cf,$_req,$_db,$_user;

$opt = array();
$outformat = isset($_req['outformat'])?$_req['outformat']:'txt';
$page = isset($_req['page'])?intval($_req['page']):1;
$pagesize = isset($_req['pagesize'])?intval($_req['pagesize']):100;
if(isset($_req['favor']) && $_req['favor'] == 1){
    $opt['favor'] = 1;
}
if(isset($_req['country']) && !empty($_req['country'])){
    $opt['country'] = $_req['country'];
}
if(isset($_req['category']) && !empty($_req['category'])){
    $opt['categories'] = $_req['category'];
}
if(isset($_req['language']) && !empty($_req['language'])){
    $opt['language'] = $_req['language'];
}
if(isset($_req['key']) && !empty($_req['key'])){
    $opt['key'] = $_req['key'];
}
/* if(isset($_req['lastupdated']) && !empty($_req['lastupdated'])){
    $opt['lastupdated'] = $_req['lastupdated'];
} */
$content = GetProduct($opt,$page,$pagesize,$_user);
if(isset($_req['xo']) && $_req['xo'] == 'ox'){
    echo '<pre>';print_r($content);
}else{
    arr_out_format($content,$outformat);
}

exit();

function GetProduct($opts,$page = 1,$pagesize = 20,$_user){
    global $_db;

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

    $export=false;
    $sql_names_set = 'SET NAMES BINARY';
    $_db->query($sql_names_set);

    $return = '';
    $where = '';
    $orderBy = '';
    // only show publisher their category adversiter, pass the rules when the publisher search advertiser name
//     if( (isset($opts['keyword']) && !empty($opts['keyword'])) ||  (isset($opts['domain']) && !empty($opts['domain'])) ){
//         $categoryArr = array();
//     }else{
            //该publisher属于德国或法国，category不加限制
            $sql = "SELECT Country FROM publisher WHERE ID = ".$_user['PublisherId'];
            $rs = $_db->getFirstRow($sql);
            if($rs['Country'] == 7 || $rs['Country'] == 82){
                $categoryArr = array();
            }else {
                $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_user['PublisherId'];
                $res = $_db->getFirstRow($sql);
                $categoryId = trim($res['CategoryId'],", \t\n\r\0\x0B");
                $categoryArr = explode(',',trim($categoryId,','));
            }
//     }

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
    }elseif((!isset($opts['keyword']) || empty($opts['keyword'])) && (!isset($opts['domain']) || empty($opts['domain'])) ){
        if($rs['Country'] == 7 || $rs['Country'] == 82){
        	$where .= ' AND 1=1';
    	}else{
    		$where .= ' AND 0=1';
    	}
    }

    if (isset($opts['favor']) && $opts['favor'] == 1){
        $sql = "select sid from publisher_collect where uid=".$_user['PublisherId'];
        $res = $_db->getRows($sql);
        if(!empty($res)){
            $num = '';
            foreach($res as $k){
                $num.=$k['sid'].',';
            }
            $num = rtrim($num,',');
            $where.= " AND f.`ID` IN ($num)";
        }
    }
    if (isset($opts['language']) && !empty($opts['language'])){
        $where.= " AND a.language='".addslashes($opts['language'])."'";
        if($opts['language'] == 'fr'){
            $where.= " AND (key_money>0 or key_from>0 or key_percent>0 or key_free_download>0 or key_free_gift>0)";
        }
    }
    if(isset($opts['country']) && !empty($opts['country'])){
        $where .= " AND FIND_IN_SET('".strtolower(addslashes($opts['country']))."',a.country)";
    }
    /* if(isset($opts['lastupdated']) && !empty($opts['lastupdated'])){
        $lastupdated = date("Y-m-d",strtotime($opts['lastupdated']));
        $where .= " AND a.LastUpdateTime >= '".$lastupdated."' ";
    } */

    if(isset($_user['PublisherId'])){
        $checksql = 'select ID,SiteTypeNew from publisher_account pa where pa.`ApiKey` = "'.addslashes($opts['key']).'" AND pa.`Status` = "Active"';
        $checkrow = $_db->getFirstRow($checksql);
        $checkarr = explode('+',$checkrow['SiteTypeNew']);
        $where.=" AND pi.SupportType != 'None'";
        $siteType = 'content';
        foreach($checkarr as $k){
            if($k == '1_e' || $k == '2_e'){
                $siteType = 'coupon';
                break;
            }
        }
        
        $where.=" AND pi.SupportType != 'None'";
        if($siteType == 'coupon'){
            $where.=" AND pi.SupportType != 'Content' ";
        }else{
            $where.=" AND pi.SupportType != 'Promotion' ";
        }

        $sql = "select * from block_relationship where ((AccountId = ".intval($_user['ID'])." AND AccountType = 'AccountId') OR (AccountId = ".intval($_user['PublisherId'])." AND AccountType = 'PublisherId')) AND `Status` = 'Active'";
        $rows_block = $_db->getRows($sql);
        $block_affids = array();
        $block_pids = array();
        $block_sids = array();
        foreach($rows_block as $k=>$v){
            if($v['ObjType'] == 'Affiliate')
                $block_affids[] = $v['ObjId'];
            if($v['ObjType'] == 'Program')
                $block_pids[] = $v['ObjId'];
            if($v['ObjType'] == 'Store')
                $block_sids[] = $v['ObjId'];
        }
        if(!empty($block_affids))
            $where .= " AND ( pi.AffId NOT IN (".join(',',$block_affids).") OR pi.AffId is NULL ) ";
        if(!empty($block_pids))
            $where .= " AND a.ProgramId NOT IN (".join(',',$block_pids).")";
        if(!empty($block_sids))
            $where .= " AND a.StoreId NOT IN (".join(',',$block_sids).")";
    }
    
    if(!$export){
        $sql = "SELECT
        COUNT(a.ID) AS c
        FROM
        product_feed a
        LEFT JOIN store f
        ON a.`StoreId` = f.id
        LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
        WHERE a.`status`='active' $where  ";
        //echo $sql;exit;
        $count = $_db->getFirstRow($sql);
        $return['response']['PageTotal'] = ceil($count['c']/$pagesize);
        $return['response']['PageNow'] = $page;
        $return['response']['Num'] = $count['c'];
    }
    
    $sql = "SELECT
    a.ProductName ,a.ProductDesc ,a.ProductImage ,a.ProductPrice ,a.ProductCurrencySymbol ,a.ProductLocalImage ,a.EncodeId ,
    IF(f.NameOptimized='' or f.NameOptimized is null,f.Name,f.NameOptimized) AS Advertiser_Name,f.LogoName as Logo,f.CategoryId 
    FROM
    product_feed a
    LEFT JOIN store f
    ON a.`StoreId` = f.id
    LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
    WHERE  a.`status`='active' $where  $orderBy LIMIT ".($page-1)*$pagesize.",$pagesize";
    $content = $_db->getRows($sql);

    /* if(!empty($content)){
        $storeids = array();
        foreach($content as $k=>$v){
            $storeids[] = $v['StoreId'];
        }
        $sql = "SET NAMES UTF8";
        $_db->query($sql);
        $sql = "SELECT ID as StoreId, IF(NameOptimized='' or NameOptimized is null,Name,NameOptimized) AS Advertiser_Name,LogoName as Logo,CategoryId FROM store WHERE ID in (".join(',',$storeids).")";
        $rows_store = $_db->getRows($sql);
        $sql = "SET NAMES latin1";
        $_db->query($sql);

        $tmp_store = array();
        foreach($rows_store as $k=>$v){
            $tmp_store[$v['StoreId']] = $v;
        }

        foreach($content as $k=>$v){
            $content[$k] = array_merge($v,$tmp_store[$v['StoreId']]);
        }
    } */

    $sql = "SELECT ID,`Name` FROM category_std ORDER BY `Name` ASC";
    $rows_cate = $_db->getRows($sql);
    $map_category = array();
    foreach($rows_cate as $k=>$v){
        $map_category[$v['ID']] = $v['Name'];
    }

    $data = array();
    foreach($content as $k=>$v){

        $category_str = '';
        if(!empty($v['CategoryId'])){
            $category_arr = array();
            $cids = explode(',',$v['CategoryId']);
            foreach($cids as $c){
                if($c == '100' || empty($c))
                   continue;
                if(isset($opts['language']) && $opts['language']=='fr'){
                    if(isset($map_category[$c]) && isset($category_fr[$map_category[$c]])){
                        $category_arr[] = $category_fr[$map_category[$c]];
                    }
                }else{
                    $category_arr[] = $map_category[$c];
                }
            }
            $category_arr = array_unique($category_arr);
            $category_str = join(',',$category_arr);
        }

        if(isset($category_store_set[$v['Advertiser_Name']])){
             $category_str = $category_store_set[$v['Advertiser_Name']];
        }

        $logo = '';
        if(!empty($v['Logo'])){
            $image_arr = explode(',',$v['Logo']);
            $logo = 'http://www.brandreward.com/img/adv_logo/'.$image_arr[0];
        }
        
        if($v['ProductCurrencySymbol'] != ''){
            $v['ProductPrice'] = $v['ProductCurrencySymbol'].round($v['ProductPrice'],2);
        }else {
            $v['ProductPrice'] = $v['ProductCurrency'].round($v['ProductPrice'],2);
        }
        
        if(preg_match('@/app/site/ezconnexion.com/web/img/(.*?)$@',$v['ProductLocalImage'],$matche)){
            $v['ProductImage'] = "https://www.brandreward.com/img/".$matche[1];
        }

        $data[] = array(
            'Advertiser' => $v['Advertiser_Name'],
            'Logo' => $logo,
            'Category' => $category_str,
            'Title' => $v['ProductName'],
            'Description' => str_replace(array("\r\n", "\r", "\n"), " ", $v['ProductDesc']),
            'Price' => $v['ProductPrice'],
            'Image' => $v['ProductImage'],
            'ShippingCountry'=> empty($v['Country'])?'global':$v['Country'],
            'LinkUrl' => 'http://r.brandreward.com/?key='.$_user['ApiKey'].'&linkid='.urlencode($v['EncodeId']),
        );
    }

    $return['data'] = $data;
    $return['response']['NumReturn'] = count($content);
    
    $sql_names_set = 'SET NAMES latin1';
    $_db->query($sql_names_set);
    return $return;
}

function get_spell_title($keys,$language='en'){
    $keywords = array();
    $brand_exist = 0;
    if(empty($keywords) && $keys['key_money'] > 0){
        if($language == 'en'){
            $keywords[] = get_currency_flag($keys['key_money_currency']).$keys['key_money'].' OFF';
        }elseif($language == 'fr'){
            $ex_words = empty($keys['key_code'])?'':'Code Promo ';
            $brandname = $brand_exist?'':$ex_words.$keys['Advertiser_Name'].' : ';
            $keywords[] = $brandname.get_currency_flag($keys['key_money_currency']).$keys['key_money'].' de réduction';
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_from'] > 0){
        if($language == 'en'){
            $keywords[] = 'FROM '.get_currency_flag($keys['key_from_currency']).$keys['key_money'];
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':$keys['Advertiser_Name'].' : ';
            $keywords[] = $brandname.'à partir de '.get_currency_flag($keys['key_from_currency']).$keys['key_from'];
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_percent'] > 0){
        if($language == 'en'){
            $keywords[] = $keys['key_percent'].'% OFF';
        }elseif($language == 'fr'){
            $ex_words = empty($keys['key_code'])?'':'Code Promo ';
            $brandname = $brand_exist?'':$ex_words.$keys['Advertiser_Name'].' : ';
            $keywords[] = $brandname.ceil($keys['key_percent']).'% de réduction';
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_free_trial'] > 0){
        if($language == 'en'){
            $keywords[] = 'FREE TRIAL';
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':' '.$keys['Advertiser_Name'];
            $keywords[] = 'Offre Gratuite'.$brandname;
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_free_download'] > 0){
        if($language == 'en'){
            $keywords[] = 'FREE DOWNLOAD';
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':' '.$keys['Advertiser_Name'];
            $keywords[] = 'Téléchargement Gratuit'.$brandname;
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_free_gift'] > 0){
        if($language == 'en'){
            $keywords[] = 'FREE GIFT';
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':$keys['Advertiser_Name'].' ';
            $keywords[] = $brandname.'recevez un cadeaux gratuit';
            $brand_exist = 1;
        }
    }
    if(empty($keywords) && $keys['key_free_sample'] > 0){
        if($language == 'en'){
            $keywords[] = 'FREE SAMPLE';
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':$keys['Advertiser_Name'].' ';
            $keywords[] = $brandname.'recevez un cadeaux gratuit';
            $brand_exist = 1;
        }
    }
/*
    if($keys['key_free_shipping'] > 0){
        if($language == 'en'){
            $keywords[] = 'FREE SHIPPING';
        }elseif($language == 'fr'){
            $brandname = $brand_exist?'':' '.$keys['Advertiser_Name'];
            $keywords[] = 'Livraison Gratuite'.$brandname;
            $brand_exist = 1;
        }
    }
*/
    if(empty($keywords)){
        $keywords[] = 'other';
    }
    
    return join(' + ',$keywords);

}

function get_currency_flag($cur){
    if($cur == 'dollar')
        return '$';
    if($cur == 'EUR' || $cur == 'euro')
        return '€';
    if($cur == 'GBP')
        return '£';
    else
        return $cur;
}
?>
