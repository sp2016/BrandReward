<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
isset($_GET['type']) ? $type = $_GET['type'] : $type = 1;
$categoryiesOfContent = array(
    '1.WEB-BASED (DESKTOP/MOBILE)' => array(
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
//         'm' => 'Adbars & Toolbars',
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
//         'm' => 'Adbars & Toolbars',
        'n' => 'File sharing platform',
        'o' => 'Video sharing platform',
        'p' => 'Other',
    )
);
$user_profile = $objAccount->get_completioninfo($USERINFO['ID']);
//再次申请状态
if(isset($_GET['check']) && !empty($_GET['check'])){
    $objTpl->assign('checktype', 1);
}
if($type == 2){
    $checkarray = array();
    foreach($user_profile['base'] as $k=>$v){
        if($k == 'ZipCode' ||$k == 'Name' || $k == 'Email' || $k == 'Company' || $k == 'Domain' || $k == 'Phone' || $k == 'CompanyAddr' || $k == 'Country' ){//|| $k == 'PayPal' 
            $checkarray['info'][$k] = $v;
        }else{
            $checkarray['detail'][$k] = $v;
        }

    }

    $objTpl->assign('checkarray', json_encode($checkarray));
}
if($type == 3){
    $account = new Account();
    $res = $account->getupinfo($USERINFO['ID']);
    $objTpl->assign('info',$res['info']);
}
$is_form = isset($_POST['is_form']) ? $_POST['is_form'] : 0;
$countryOption = getDictionary('country');
if(isset($_GET['infoull'])){
    $objTpl->assign('infonull', $_GET['infoull']);
}
$category = array();
$sql = "SELECT * from category_std ORDER BY `Name` ASC;";
$rs = $db->getRows($sql);
foreach($rs as $item)
{
    $category[$item['ID']] = $item['Name'];
}
$objTpl->assign('category', $category);
$objTpl->assign('type', $type);
$objTpl->assign('user_profile', $user_profile);
$objTpl->assign('countryOption', $countryOption);
$objTpl->assign('categoryiesOfContent', $categoryiesOfContent);
$objTpl->assign('is_form', $is_form);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_completion.html');
?>