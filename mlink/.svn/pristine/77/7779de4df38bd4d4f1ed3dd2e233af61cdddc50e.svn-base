<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT . 'init.php');
$countryOption = getDictionary('country');
$account = new Account();
if(isset($_GET['signkey']) && !empty($_GET['signkey'])){
    $key = trim($_GET['signkey']);
    $res = $account->checksignkey($key);
    if($res !='error'){
        $objTpl->assign('signkey',$res);
        $objTpl->assign('type', 2);
    }
}else {
    $objTpl->assign('signkey', '');
    $objTpl->assign('type', 1);
}
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
$category = array();
$sql = "SELECT * from category_std ORDER BY `Name` ASC;";
$rs = $db->getRows($sql);
foreach($rs as $item)
{
    $category[$item['ID']] = $item['Name'];
}
$sys_header['js'][] = BASE_URL.'/js/layer.js';
$sys_header['css'][] = BASE_URL.'/css/reveal.css';
$objTpl->assign('countryOption', $countryOption);
$objTpl->assign('category', $category);
$objTpl->assign('categoryiesOfContent', $categoryiesOfContent);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title', 'Sign Up');
$objTpl->display('signup.html');
?>
