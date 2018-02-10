<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

check_user_login();

include_once('auth_ini.php');

$objAccount = new Account;

//查询申请修改状态
$check = $objAccount->checkuptype($USERINFO['ID']);
if($check == 1){
    $objTpl->assign('checktype',1);
}else{
    $objTpl->assign('checktype',0);
}
//撤销申请
if(isset($_POST['remove']) && !empty($_POST['remove'])){
    $res = $objAccount->removeapply($USERINFO['ID']);
    echo $res;
    return $res;
    die;
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
$countryOption = getDictionary('country');
$sitetypeOption = getDictionary('sitetype');
$info = $objAccount->get_completioninfo($USERINFO['ID']);
$paymentAccount = $objAccount->getPaymentAccount($USERINFO['ID']);
//显示银行所在的国家
if(isset($countryOption[$paymentAccount['AccountCountry']])){
    $paymentAccount['AccountCountry'] = $countryOption[$paymentAccount['AccountCountry']];
}else {
    $paymentAccount['AccountCountry'] = '';
}

$DevKnowledge = $info['base']['DevKnowledge'];
if($DevKnowledge == 'turn on a computer'){
    $objTpl->assign('DevKnowledge', 'I can turn on a computer');
}else if($DevKnowledge == 'wordpress templates'){
    $objTpl->assign('DevKnowledge', 'I prefer wordpress templates');
}else if($DevKnowledge == 'get by with coding'){
    $objTpl->assign('DevKnowledge', 'I can get by with coding');
}else{
    $objTpl->assign('DevKnowledge', 'I’m a developing wizard');
}
$ProfitModel = $info['base']['ProfitModel'];
if($p = preg_replace('/\+/',' , ',$ProfitModel)){
    $objTpl->assign('ProfitModel',$p);
}else{
    $objTpl->assign('ProfitModel',$ProfitModel);
}
$TypeOfContent = $info['base']['TypeOfContent'];
if($t = preg_replace('/\+/',' , ',$TypeOfContent)){
    $objTpl->assign('TypeOfContent',$t);
}else{
    $objTpl->assign('TypeOfContent',$TypeOfContent);
}
$WaysOfTraffic = $info['base']['WaysOfTraffic'];
if($w = preg_replace('/\+/',' , ',$WaysOfTraffic)){
    $objTpl->assign('WaysOfTraffic',$w);
}else{
    $objTpl->assign('WaysOfTraffic',$WaysOfTraffic);
}
$StaffNumber = $info['base']['StaffNumber'];
if($StaffNumber == '1'){
    $objTpl->assign('StaffNumber','Only Me (1)');
}else{
    $objTpl->assign('StaffNumber',$StaffNumber);
}
$ContentProduction = $info['base']['ContentProduction'];
if($ContentProduction == 'user generated'){
    $objTpl->assign('ContentProduction','User Generated Content');
}else{
    $objTpl->assign('ContentProduction',$ContentProduction);
}
$category = getMineCategory($USERINFO['ID']);
// $country = getMineCountry($info['base']['GeoBreakdown']);

/* if(!empty($info['base']['SiteType'])){
    $sitetext = '';
    $sitetype = $info['base']['SiteType'];
        if(preg_match('/\+/',$sitetype)){
            $sitarr = explode('+',$sitetype);
            $sitarr = array_unique($sitarr);
			$sitarr = array_filter($sitarr);
            foreach($sitarr as $k){
                if(preg_match('/\_/',$sitetype)){
                    $v = explode('_', $k);
                    if ($v[0] == 1) {
                        //  echo $v[1];
                        if (array_key_exists($v[1], $categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'])) {
                            $sitetext .= 'WEB: '.$categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'][$v[1]] . '; ';
                        }
                    }else if($v[0] != 1 && $v[0] != 2){
                        $sitetext .= 'Other: '.$v[0].'; ';
                    }
                    else {
                        $sitetext .= 'MOBILE: '.$categoryiesOfContent['2.MOBILE APP'][$v[1]].'; ';
                    }
                }else{
                    break;
                }
            }
        }else{
                if(preg_match('/\_/',$sitetype)){
                    $v = explode('_', $sitetype);
                    if ($v[0] == 1) {
                        if (array_key_exists($v[1], $categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'])) {
                            $sitetext .= 'WEB: '.$categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'][$v[1]] . '; ';
                        }
                    }
                    else {
                        $sitetext .= 'MOBILE: '.$categoryiesOfContent['2.MOBILE APP'][$v[1]].'; ';
                    }
                }else{
                    $sitetext .= 'Other: '.$sitetype.'; ';
                }

        }
}else{
    $sitetext = ' ';
} */

$refer_info = $objAccount->get_refer_info($USERINFO['ID']);

foreach ($info['site'] as &$val){
    $GeoBreakdownList = explode('+',$val['GeoBreakdown']);
    $GeoBreakdownText = '';
    foreach ($GeoBreakdownList as $temp){
        if(isset($countryOption[$temp])){
            $GeoBreakdownText .= ",".$countryOption[$temp];
        }
    }
    $val['GeoBreakdownText'] = trim($GeoBreakdownText,",");
    
    $SiteTypeNewList = explode('+',$val['SiteTypeNew']);
    $SiteTypeNewText = '';
    foreach ($SiteTypeNewList as $temp){
        if(preg_match('/\_/',$temp)){
            $v = explode('_', $temp);
            if ($v[0] == 1) {
                if (array_key_exists($v[1], $categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'])) {
                    $SiteTypeNewText .= 'WEB: '.$categoryiesOfContent['1.WEB-BASED (DESKTOP/MOBILE)'][$v[1]] . '; ';
                }
            }else if($v[0] == 2){
                if (array_key_exists($v[1], $categoryiesOfContent['2.MOBILE APP'])) {
                    $SiteTypeNewText .= 'MOBILE: '.$categoryiesOfContent['2.MOBILE APP'][$v[1]].'; ';
                }
            }else {
                $SiteTypeNewText .= 'Other: '.$v[0].'; ';
            }
        }else {
            if($temp!=null && $temp!=''){
                $SiteTypeNewText .= 'Other: '.$temp.'; ';
            }
        }
    }
    $val['SiteTypeNewText'] = trim($SiteTypeNewText,",");
    
}

//查询子账户
$subAccountList = $objAccount->searchSubAccount($USERINFO['ID']);
$objTpl->assign('refer_info', $refer_info);
$objTpl->assign('user_profile', $info);
$objTpl->assign('paymentAccount', $paymentAccount);
$objTpl->assign('category', $category);
// $objTpl->assign('country', $country);
// $objTpl->assign('sitetype',rtrim($sitetext,' , ') );
$objTpl->assign('sitetypeOption', $sitetypeOption);
$objTpl->assign('countryOption', $countryOption);
$objTpl->assign('categoryiesOfContent', $categoryiesOfContent);
$objTpl->assign('subAccountList', $subAccountList);
$objTpl->assign('subAccountCount', count($subAccountList));
$objTpl->assign('Career', $_SESSION['u']['Career']);
$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_account.html');
?>