<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
$merchant = new MerchantExt();
$objOutlog = new Outlog;
if(isset($_POST['query']) && !empty($_POST['query'])){
    $val = addslashes(trim($_POST['query']));
    $sql = "select id,IF(NameOptimized='' OR NameOptimized IS NULL,`Name`,NameOptimized) AS name from store where StoreAffSupport = 'YES' AND (`Name` like '%$val%' OR NameOptimized like '%$val%') ORDER BY NameOptimized,`Name`";
    $res = $merchant->objMysql->getRows($sql,'id');
    if(!empty($res)){
        $id = '(';
        foreach($res as $k){
            $id.='"'.$k['id'].'",';
        }
        $id = rtrim($id,',').')';
        $sql = "select a.StoreId,a.ProgramId,b.Name as pname,c.Name as aname from r_store_program a INNER JOIN program b on a.ProgramId = b.ID INNER JOIN wf_aff c ON b.AffId = c.ID where a.StoreId IN $id";
        $res2 = $db->getRows($sql);
        $dataarr = array();
        foreach($res2 as $k=>$v){
            $dataarr[$k]['name'] = $res[$v['StoreId']]['name'].' |---| '.$v['pname'].' |---| '.$v['aname'];
            $dataarr[$k]['id'] = $v['StoreId'].','.$v['ProgramId'];
        }
        echo json_encode($dataarr);
    }else{
        echo json_encode(array());
    }
    die;
}
if(isset($_POST['subtype'])){
    $sql_names_set = 'SET NAMES latin1';
    $db->query($sql_names_set);
    $Tools = new Tools();
    $idarr = explode(',',$_POST['spid']);
    $storeId = $idarr[0];
    $programid = $idarr[1];
    $user = 1;
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $countryarr = $_POST['country'];
    $title = addslashes($_POST['name']);
    $desc = addslashes($_POST['desc']);

    $productPrice = isset($_POST['productprice']) ? $_POST['productprice'] : 0;
    $productcurrency = isset($_POST['productcurrency']) ? $_POST['productcurrency'] : 0;

    $OriginalUrl = addslashes($_POST['product_url']);
    $currentTime = date("Y-m-d H:i:s");
    $language  = $_POST['language'];
    $simpleId = $Tools->random(8);
    $EncodeId = $Tools->getEncodeId();
    if(empty($countryarr)){
        $sql = "select ShippingCountry from program_intell where ProgramId=$programid";
        $countryres = $merchant->getRow($sql);
        if(!empty($countryres)){
            $country = $countryres['ShippingCountry'];
        }else{
            $country = '';
        }
    }else{
        $country = '';
        foreach($countryarr as $k){
            $country.=strtolower($k).',';
        }
        $country = rtrim($country,',');
    }
    if($_POST['subtype'] == 1){
        $id = $_POST['id'];
        $sql = "UPDATE product_feed SET `StoreId`=$storeId,`ProductPrice`='$productPrice',`ProductCurrency`='$productcurrency',`country`='$country',`ProductName`='$title',`ProductDesc`='$desc',`ProductUrl`='$OriginalUrl',`ProductStartDate`='$startDate',`ProductEndDate`='$endDate',`language`='$language' WHERE `ID`=$id";
        if($db->query($sql)){
            $data = array(
                'flag' => 1,
                'msg' => 'Success'
            );
            echo json_encode($data);
        } else {
            $data = array(
                'flag' => 2,
                'msg' => 'Insert Error!'
            );
            echo json_encode($data);
        }
    }else{
        $sql = "INSERT INTO product_feed (`ProductPrice`,`ProductCurrency`,`country`,`StoreId`,`ProgramId`,`ProductName`,`ProductDesc`,`ProductUrl`,`ProductStartDate`,`ProductEndDate`,`AddTime`,`Status`,`EncodeId`,`language`,`source`)
                VALUES ($productPrice,'$productcurrency','$country','$storeId','$programid','$title','$desc','$OriginalUrl','$startDate','$endDate','$currentTime','Active','$EncodeId','$language','manual')";
        if($db->query($sql)){
            $data = array(
                'flag' => 1,
                'msg' => 'Success'
            );
            echo json_encode($data);
        } else {
            $data = array(
                'flag' => 2,
                'msg' => 'Insert Error!'
            );
            echo json_encode($data);
        }
    }
    die;
}

//country
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';
$affname = $objOutlog->get_affname();
$affname[] = array('ID'=>-1,'Name'=>'Other');
$objTpl->assign('affname', $affname);


//表单修改功能
$updateTag = isset($_POST['updatep']) ? $_POST['updatep'] : false;
if (!empty($updateTag)) {

    $id = isset($_POST['pid']) ? $_POST['pid'] : 0;
    empty($id) && exit(0);
    $filter = array(
        'pid' => $id,
        'download' => 1
    );
    $data = $merchant->getProductFeed($filter,0,20);

    $sql = "SELECT * FROM product_feed a WHERE ID = " . $id;
    $product = $merchant->getRow($sql);
    $pid = isset($product['ProgramId']) ? $product['ProgramId'] : 0;
    $sid = isset($product['StoreId']) ? $product['StoreId'] : 0;
    empty($pid) && exit(0);
    $sql = "SELECT `Name`,`ID` AS `pid` FROM program WHERE ID =".$pid;
    $pname = $merchant->getRow($sql);
    $cdata = $merchant->getCommissionRate($sid,$pid);
    $store = isset($cdata['store']) ? $cdata['store'] : '--';
    $affiliate = isset($cdata['affiliate']) ? $cdata['affiliate'] : '--';
    $data['data'][0]['advertiser'] = $store.' |---| '.$pname['Name'].' |---| '.$affiliate;
    $data['data'][0]['spid'] = $sid.','.$pid;
    echo json_encode($data['data'][0]);
    die;
}

//删除Product Feed
if(isset($_POST['delete_content']))
{
    $currentTime = date("Y-m-d H:i:s");
    $sql = "UPDATE product_feed SET `Status`= 'InActive' WHERE ID = '{$_POST['id']}'";
    if($db->query($sql)){
        echo 1;
    } else {
        echo 0;
    }
    die;
} elseif(isset($_POST['act']) && $_POST['act']== 'tip_program_aff'){
    if (isset($_POST['keywords'])) {

        $re_arr = array(
            'flag'=>0,
            'data'=>array(),
            'msg'=>'',
        );
        $_POST['keywords'] = trim($_POST['keywords']);
        if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {

            $where_str = '( a.Name LIKE "%' . addslashes($_POST['keywords']) . '%" OR a.IdInAff LIKE "' . addslashes($_POST['keywords']) . '%")';//and优先级强于or，所有前面的or要加括号
            $sql = "select a.id,a.name,b.name as aff from program a left join wf_aff b on a.affid = b.id where $where_str";
            $rows = $db->getRows($sql);
            if ($rows) {
                $tmp = array();

                foreach ($rows as $v) {
                    $tmp['name'] = $v['name'].'('.$v['aff'].')';
                    $tmp['id'] = $v['id'];
                    $re_arr['data'][] = $tmp;
                }
                $re_arr['flag'] = 1;

            } else {
                $re_arr['msg']="there is no such Program in table 'program'";
            }

        }
    }

    echo json_encode($re_arr);
    exit();


}

if(isset($_POST['utype']) && !empty($_POST['utype'])){
    $uid =  $_POST['uid'];
    $sql = "select ApiKey,Domain,Name from publisher_account where publisherid=".$uid;
    $res = $db->getRows($sql);
    if(!empty($res)){
        $val = array();
        $val['data'] = $res;
        echo json_encode($val);
    }else{
        return 0;
    }
    die;
}
//category
$statis = new Statis();
$category = $statis->getCategory();
//Product:表单功能
$table = isset($_POST['table']) ? $_POST['table'] : '';
if(!empty($table)) {
    $offset = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['length']) ? $_POST['length'] : 0;
    $affiliate = '';
    $search['order'] = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 0;
    $number = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : -1;
    $search['oname'] = isset($_POST['columns'][$number]['data']) ? $_POST['columns'][$number]['data'] : '';
    $search['id'] = isset($_POST['id']) ? $_POST['id'] : 0;
    $data = json_decode($_POST['data'], true);
    for ($i = 0; $i < count($data); $i++) {
        $search[$data[$i]['name']] = $data[$i]['value'];
        if ($data[$i]['name'] == 'affiliate') {
            $affiliate .= $data[$i]['value'] . ',';
        }
    }
    $search['download'] = 0;
    $search['aff'] = rtrim($affiliate,',');
    $productTotal = $merchant->getProductFeed($search,$offset,$limit);
    $productData = $productTotal['data'];
    if(!empty($productData)){
        foreach ($productData as &$v)
        {
            $v['AddTime'] = date('Y-m-d',strtotime($v['AddTime']));
            $v['orders'] = !empty($v['orders']) ? $v['orders'] : '/';
            $v['sales'] = !empty($v['sales']) ? $v['sales'] : '/';
            $v['commission'] = !empty($v['commission']) ? $v['commission'] : '/';
            $commissionData = $merchant->getCommissionRate($v['sid'],$v['pid']);
            $v['commissionRate'] = isset($commissionData['commission']) ? $commissionData['commission'] : '/';
            $v['ProductUrl'] = isset($v['ProductUrl']) ? urldecode($v['ProductUrl']) : '';
            !empty($v['ProductLocalImage']) && $v['ProductLocalImage'] = str_replace('/app/site/ezconnexion.com/web/', '', $v['ProductLocalImage']);
        }
    }
    $productTotal['data'] = $productData;
    $productTotal['recordsFiltered'] = $productTotal['count'];
    unset($productTotal['count']);
    echo json_encode($productTotal);
    die;
}



$sql = "select IF(`Name` = '' OR `Name` IS NULL, Email, `Name`) AS `Name`,`ID` from publisher WHERE Status='Active' order by `Name` ASC ";
$pubres = $db->getRows($sql);
$sel_cate = array();
if(isset($_GET['categories'])){
    $sel_cate = explode(',',$_GET['categories']);
}
$objTpl->assign('pubres', $pubres);
$objTpl->assign('sel_cate', $sel_cate);
$objTpl->assign("title","Promotions");
$objTpl->assign('search', $_GET);
$objTpl->assign('category', $category);
$objTpl->assign('countryArr', $countryArr);
$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['js'][] = BASE_URL.'/js/jquery.filer.min.js';
$sys_header['js'][] = BASE_URL.'/js/bootstrap-typeahead.js';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.filer.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.filer-dragdropbox-theme.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_product_feed.html');