<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
mysql_query("SET NAMES 'latin1'");
$Publisher = new Publisher();
$Account = new Account();
$statis = new Statis();
if(isset($_POST['check']) && !empty($_POST['check'])){
    $info = $Publisher->getchangelog($_POST,1,1);
    $oldinfo = json_decode($info[0]['oldinfo'],true);
    $detail = json_decode($info[0]['newinfo'],true);
    $arr = array('old'=>$oldinfo,'new'=>$detail);
    echo json_encode_no_zh($arr);
    die;
}
if(isset($_GET['id']) && !empty($_GET['id'])){

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
//            'm' => 'Adbars & Toolbars',
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
//            'm' => 'Adbars & Toolbars',
            'n' => 'File sharing platform',
            'o' => 'Video sharing platform',
            'p' => 'Other',
        )
    );
    //展示信息入口
    isset($_GET['p']) ? $p = $_GET['p'] : $p = 1;
    $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : 20;
    $info = $Publisher->getchangelog($_GET,$p,$pagesize);
    $objTpl->assign('info',$info['info']);
    $page_html = get_page_html($info);
    $objTpl->assign("pageHtml",$page_html);
    $countryOption = getDictionary('country');
    $catArr = $statis->getCategory();
    $objTpl->assign('countryOption',$countryOption);
    $objTpl->assign('categoryiesOfContent', $categoryiesOfContent);
    $objTpl->assign("title","Change log");
    $objTpl->assign("name",$_GET['name']);
    $objTpl->assign("id",$_GET['id']);
    $objTpl->assign('catArr', $catArr);

}
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('pid', $_GET['id']);
$objTpl->display('b_publisher_changelog.html');