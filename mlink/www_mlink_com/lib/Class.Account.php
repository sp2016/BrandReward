<?php

class Account extends LibFactory
{
//     function checksignkey($key){
//         $sql = "select ID,`key` from publisher_signup where `key` ='$key'";
//         $res = $this->getRow($sql);
//         if(!empty($res['key'])){
//             return $res['ID'];
//         }else{
//             return 'error';
//         }
//     }
//     function edit_publish_account($data)
//     {
//         if (!isset($data['ID']) || empty($data['ID'])) {
//             return 1;
//         }
//         $update_d = array();
//         $update_d['Domain'] = isset($data['data']['pub_site']) && !empty($data['data']['pub_site']) ? $data['data']['pub_site'] : '';
//         $update_d['Domain'] = trim($update_d['Domain']);
//         $update_d['Email'] = isset($data['data']['pub_email']) && !empty($data['data']['pub_email']) ? $data['data']['pub_email'] : '';
//         $update_d['Email'] = trim($update_d['Email']);
//         $update_d['Phone'] = isset($data['data']['pub_phone']) && !empty($data['data']['pub_phone']) ? $data['data']['pub_phone'] : '';
//         $update_d['Phone'] = trim($update_d['Phone']);
//         $update_d['Company'] = isset($data['data']['pub_company']) && !empty($data['data']['pub_company']) ? $data['data']['pub_company'] : '';
//         $update_d['Company'] = trim($update_d['Company']);
//         $update_d['CompanyAddr'] = isset($data['data']['pub_companyaddr']) && !empty($data['data']['pub_companyaddr']) ? $data['data']['pub_companyaddr'] : '';
//         $update_d['CompanyAddr'] = trim($update_d['CompanyAddr']);
//         $update_d['Country'] = isset($data['data']['pub_country']) && !empty($data['data']['pub_country']) ? $data['data']['pub_country'] : '';
//         $update_d['Country'] = intval($update_d['Country']);
//         $update_d['Name'] = isset($data['data']['pubname']) && !empty($data['data']['pubname']) ? $data['data']['pubname'] : '';
//         $update_d['Name'] = trim($update_d['Name']);
//         /* $update_d['PayPal'] = isset($data['data']['pub_paypal']) && !empty($data['data']['pub_paypal']) ? $data['data']['pub_paypal'] : '';
//         $update_d['PayPal'] = trim($update_d['PayPal']); */
//         $update_d['ZipCode'] = isset($data['data']['zipcode']) && !empty($data['data']['zipcode']) ? $data['data']['zipcode'] : '';
//         $update_d['ZipCode'] = trim($update_d['ZipCode']);
//         $res = $this->table('publisher')->where('ID = ' . intval($data['ID']))->update($update_d);
//         if (!$res) {
//             return 2;
//         }else{
//             if($data['updateid'] == 1){
//                 $id = $data['ID'];
//                 $CategoryId = isset($data['CategoryId']) && !empty($data['CategoryId']) ? $data['CategoryId'] : '';
// //                 $GeoBreakdown = isset($data['GeoBreakdown']) && !empty($data['GeoBreakdown']) ? $data['GeoBreakdown'] : '';
// //                 $SiteType = isset($data['SiteType']) && !empty($data['SiteType']) ? $data['SiteType'] : '';
//                 $StaffNumber = isset($data['StaffNumber']) && !empty($data['StaffNumber']) ? $data['StaffNumber'] : '';
//                 $DevKnowledge = isset($data['DevKnowledge']) && !empty($data['DevKnowledge']) ? $data['DevKnowledge'] : '';
//                 $ContentProduction = isset($data['ContentProduction']) && !empty($data['ContentProduction']) ? $data['ContentProduction'] : '';
//                 $WaysOfTraffic = isset($data['WaysOfTraffic']) && !empty($data['WaysOfTraffic']) ? $data['WaysOfTraffic'] : '';
//                 $TypeOfContent= isset($data['TypeOfContent']) && !empty($data['TypeOfContent']) ? $data['TypeOfContent'] : '';
//                 $CurrentNetwork = isset($data['CurrentNetwork']) && !empty($data['CurrentNetwork']) ? $data['CurrentNetwork'] : '';
//                 $ProfitModel = isset($data['ProfitModel']) && !empty($data['ProfitModel']) ? $data['ProfitModel'] : '';
//                 $sql = 'INSERT INTO publisher_detail (PublisherId,CategoryId,StaffNumber,DevKnowledge,ContentProduction,WaysOfTraffic,TypeOfContent,CurrentNetwork,ProfitModel) VALUES ("'.$id.'","'.$CategoryId.'","'.$StaffNumber.'","'.$DevKnowledge.'","'.$ContentProduction.'","'.$WaysOfTraffic.'","'.$TypeOfContent.'","'.$CurrentNetwork.'","'.$ProfitModel.'")';
//                 $res = $this->query($sql);
//                 if ($res == 1) {
//                     return 11;
//                 }else{
//                     return 0;
//                 }
//             }else{
//                 $update_new = array();
//                 $update_new['CategoryId'] = isset($data['CategoryId']) && !empty($data['CategoryId']) ? $data['CategoryId'] : '';
//                 $update_new['CategoryId'] = trim($update_new['CategoryId']);
//                 /* $update_new['GeoBreakdown'] = isset($data['GeoBreakdown']) && !empty($data['GeoBreakdown']) ? $data['GeoBreakdown'] : '';
//                 $update_new['GeoBreakdown'] = trim($update_new['GeoBreakdown']);
//                 $update_new['SiteType'] = isset($data['SiteType']) && !empty($data['SiteType']) ? $data['SiteType'] : '';
//                 $update_new['SiteType'] = trim($update_new['SiteType']); */
//                 $update_new['WaysOfTraffic'] = isset($data['WaysOfTraffic']) && !empty($data['WaysOfTraffic']) ? $data['WaysOfTraffic'] : '';
//                 $update_new['WaysOfTraffic'] = trim($update_new['WaysOfTraffic']);
//                 $update_new['StaffNumber'] = isset($data['StaffNumber']) && !empty($data['StaffNumber']) ? $data['StaffNumber'] : '';
//                 $update_new['StaffNumber'] = trim($update_new['StaffNumber']);
//                 $update_new['ContentProduction'] = isset($data['ContentProduction']) && !empty($data['ContentProduction']) ? $data['ContentProduction'] : '';
//                 $update_new['ContentProduction'] = trim($update_new['ContentProduction']);
//                 $update_new['CurrentNetwork'] = isset($data['CurrentNetwork']) && !empty($data['CurrentNetwork']) ? $data['CurrentNetwork'] : '';
//                 $update_new['CurrentNetwork'] = trim($update_new['CurrentNetwork']);
//                 $update_new['ProfitModel'] = isset($data['ProfitModel']) && !empty($data['ProfitModel']) ? $data['ProfitModel'] : '';
//                 $update_new['ProfitModel'] = trim($update_new['ProfitModel']);
//                 $res = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['ID']))->update($update_new);
//                 if($res == 1){
//                     return 11;
//                 }else{
//                     return 0;
//                 }
//             }
//         }
//     }

    function edit_publish_account($data)
    {
        if (!isset($data['ID']) || empty($data['ID'])) {
            return 1;
        }
        $update_d = array();
        $update_d['Domain'] = isset($data['data']['pub_site']) && !empty($data['data']['pub_site']) ? $data['data']['pub_site'] : '';
        $update_d['Domain'] = trim($update_d['Domain']);
        $update_d['Email'] = isset($data['data']['pub_email']) && !empty($data['data']['pub_email']) ? $data['data']['pub_email'] : '';
        $update_d['Email'] = trim($update_d['Email']);
        $update_d['Phone'] = isset($data['data']['pub_phone']) && !empty($data['data']['pub_phone']) ? $data['data']['pub_phone'] : '';
        $update_d['Phone'] = trim($update_d['Phone']);
        $update_d['Company'] = isset($data['data']['pub_company']) && !empty($data['data']['pub_company']) ? $data['data']['pub_company'] : '';
        $update_d['Company'] = trim($update_d['Company']);
        $update_d['CompanyAddr'] = isset($data['data']['pub_companyaddr']) && !empty($data['data']['pub_companyaddr']) ? $data['data']['pub_companyaddr'] : '';
        $update_d['CompanyAddr'] = trim($update_d['CompanyAddr']);
        $update_d['Country'] = isset($data['data']['pub_country']) && !empty($data['data']['pub_country']) ? $data['data']['pub_country'] : '';
        $update_d['Country'] = intval($update_d['Country']);
        $update_d['Name'] = isset($data['data']['pubname']) && !empty($data['data']['pubname']) ? $data['data']['pubname'] : '';
        $update_d['Name'] = trim($update_d['Name']);
        /* $update_d['PayPal'] = isset($data['data']['pub_paypal']) && !empty($data['data']['pub_paypal']) ? $data['data']['pub_paypal'] : '';
         $update_d['PayPal'] = trim($update_d['PayPal']); */
        $update_d['ZipCode'] = isset($data['data']['zipcode']) && !empty($data['data']['zipcode']) ? $data['data']['zipcode'] : '';
        $update_d['ZipCode'] = trim($update_d['ZipCode']);
        $res = $this->table('publisher')->where('ID = ' . intval($data['ID']))->update($update_d);
        if (!$res) {
            return 2;
        }else{
            if($data['updateid'] == 1){
                $id = $data['ID'];
                $CategoryId = isset($data['CategoryId']) && !empty($data['CategoryId']) ? $data['CategoryId'] : '';
                //                 $GeoBreakdown = isset($data['GeoBreakdown']) && !empty($data['GeoBreakdown']) ? $data['GeoBreakdown'] : '';
                //                 $SiteType = isset($data['SiteType']) && !empty($data['SiteType']) ? $data['SiteType'] : '';
                $StaffNumber = isset($data['StaffNumber']) && !empty($data['StaffNumber']) ? $data['StaffNumber'] : '';
                $DevKnowledge = isset($data['DevKnowledge']) && !empty($data['DevKnowledge']) ? $data['DevKnowledge'] : '';
                $ContentProduction = isset($data['ContentProduction']) && !empty($data['ContentProduction']) ? $data['ContentProduction'] : '';
                $WaysOfTraffic = isset($data['WaysOfTraffic']) && !empty($data['WaysOfTraffic']) ? $data['WaysOfTraffic'] : '';
                $TypeOfContent= isset($data['TypeOfContent']) && !empty($data['TypeOfContent']) ? $data['TypeOfContent'] : '';
                $CurrentNetwork = isset($data['CurrentNetwork']) && !empty($data['CurrentNetwork']) ? $data['CurrentNetwork'] : '';
                $ProfitModel = isset($data['ProfitModel']) && !empty($data['ProfitModel']) ? $data['ProfitModel'] : '';
                $sql = 'INSERT INTO publisher_detail (PublisherId,CategoryId,StaffNumber,DevKnowledge,ContentProduction,WaysOfTraffic,TypeOfContent,CurrentNetwork,ProfitModel) VALUES ("'.$id.'","'.$CategoryId.'","'.$StaffNumber.'","'.$DevKnowledge.'","'.$ContentProduction.'","'.$WaysOfTraffic.'","'.$TypeOfContent.'","'.$CurrentNetwork.'","'.$ProfitModel.'")';
                $res = $this->query($sql);
                if ($res == 1) {
                    return 11;
                }else{
                    return 0;
                }
            }else{
                $update_new = array();
                $update_new['CategoryId'] = isset($data['CategoryId']) && !empty($data['CategoryId']) ? $data['CategoryId'] : '';
                $update_new['CategoryId'] = trim($update_new['CategoryId']);
                /* $update_new['GeoBreakdown'] = isset($data['GeoBreakdown']) && !empty($data['GeoBreakdown']) ? $data['GeoBreakdown'] : '';
                 $update_new['GeoBreakdown'] = trim($update_new['GeoBreakdown']);
                 $update_new['SiteType'] = isset($data['SiteType']) && !empty($data['SiteType']) ? $data['SiteType'] : '';
                $update_new['SiteType'] = trim($update_new['SiteType']); */
                /* $update_new['WaysOfTraffic'] = isset($data['WaysOfTraffic']) && !empty($data['WaysOfTraffic']) ? $data['WaysOfTraffic'] : '';
                 $update_new['WaysOfTraffic'] = trim($update_new['WaysOfTraffic']); */
                $update_new['StaffNumber'] = isset($data['StaffNumber']) && !empty($data['StaffNumber']) ? $data['StaffNumber'] : '';
                $update_new['StaffNumber'] = trim($update_new['StaffNumber']);
                $update_new['DevKnowledge'] = isset($data['DevKnowledge']) && !empty($data['DevKnowledge']) ? $data['DevKnowledge'] : '';
                $update_new['DevKnowledge'] = trim($update_new['DevKnowledge']);
                $update_new['ContentProduction'] = isset($data['ContentProduction']) && !empty($data['ContentProduction']) ? $data['ContentProduction'] : '';
                $update_new['ContentProduction'] = trim($update_new['ContentProduction']);
                $update_new['WaysOfTraffic'] = isset($data['WaysOfTraffic']) && !empty($data['WaysOfTraffic']) ? $data['WaysOfTraffic'] : '';
                $update_new['WaysOfTraffic'] = trim($update_new['WaysOfTraffic']);
                $update_new['TypeOfContent'] = isset($data['TypeOfContent']) && !empty($data['TypeOfContent']) ? $data['TypeOfContent'] : '';
                $update_new['TypeOfContent'] = trim($update_new['TypeOfContent']);
                $update_new['CurrentNetwork'] = isset($data['CurrentNetwork']) && !empty($data['CurrentNetwork']) ? $data['CurrentNetwork'] : '';
                $update_new['CurrentNetwork'] = trim($update_new['CurrentNetwork']);
                $update_new['ProfitModel'] = isset($data['ProfitModel']) && !empty($data['ProfitModel']) ? $data['ProfitModel'] : '';
                $update_new['ProfitModel'] = trim($update_new['ProfitModel']);
                $res = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['ID']))->update($update_new);
                if($res == 1){
                    return 11;
                }else{
                    return 0;
                }
            }
        }
    }
    
    function checkuptype($id){
        $sql ="select uptype,state from publisher_update where PublisherId = $id";
        $res = $this->getRow($sql);
        if(empty($res)){
            return 0;
            //首次申请
        }else if( $res['uptype'] == 0 && $res['state'] == 0){
            return 1;//审核状�?
        }else{
            //再次申请or撤销申请
            return 0;
        }
    }
    function getanswer($search){
        $search = addslashes($search);
        $sql = "select * from question WHERE qtext LIKE '%$search%' OR atext LIKE '%$search%'";
        $res = $this->getRows($sql);
        return $res;

    }
    function removeapply($id){
        $uparray = array();
        $uparray['info'] = '';
        $uparray['state'] = 2;
        $res = $this->table('publisher_update')->where('PublisherId = '.$id.'')->update($uparray);
        if($res == 1){
            return 1;
        }else{
            return 0;
        }
    }
    function getupinfo($id){
        $sql ="select info from publisher_update where PublisherId = $id";
        $res = $this->getRow($sql);
        return $res;
    }
    function apply_profile_account($data){
        //检查变动字�?
        $check = $data['checkarray'];
        $pid = $data['ID'];
        $check = json_decode($check,true);
        $check_info = $check['info'];
        $check_detail = $check['detail'];
        $info = $data['check'];
        $info_info = $info['info'];
        $info_detail = $info['detail'];
        $info_value = array_diff_assoc($info_info,$check_info);
        $detail_value = array_diff_assoc($info_detail,$check_detail);
        $updatearray = array();
        if(!empty($info_value) && !empty($detail_value)){
            $updatearray['info'] = $info_value;
            $updatearray['detail'] = $detail_value;
        }else if(!empty($info_value)){
            $updatearray['info'] = $info_value;
        }else if(!empty($detail_value)){
            $updatearray['detail'] = $detail_value;
        }else{
            return 0;
        }
        //有就更新没有就插�?
        $sql = "select ID from publisher_update where PublisherId = $pid";
        $res = $this->getRow($sql);
        if(empty($res)){
            $addarray = array();
            $addarray['info'] = json_encode($updatearray);
            $addarray['PublisherId'] = $pid;
            $addarray['time'] = date("Y-m-d H:i:s");
            $addarray['uptype'] = 0;
            $addarray['state'] = 0;
            $res = $this->table('publisher_update')->insert($addarray);
            if(empty($res)){
                return 0;
            }else{
                return 1;
            }
        }else{
            $addarray = array();
            $addarray['info'] = json_encode_no_zh($updatearray);
            $addarray['PublisherId'] = $pid;
            $addarray['uptype'] = 0;
            $addarray['state'] = 0;
            $addarray['time'] = date("Y-m-d H:i:s");
            $addarray['update_user'] = '';
            $res = $this->table('publisher_update')->where('PublisherId = '.$pid.'')->update($addarray);
            if(empty($res)){
                return 0;
            }else{
                return 1;
            }
        }
    }
    function get_completioninfo($id){

            $account = array();
            $sql = "SELECT tb1.Name,tb1.ZipCode,tb1.Email,tb1.PayPal,tb1.Domain,tb1.Phone,tb1.Company,tb1.CompanyAddr,tb2.PublisherId,tb2.CategoryId,tb1.Country,tb2.CurrentNetwork,tb2.StaffNumber,tb2.ContentProduction,tb2.DevKnowledge,tb2.ProfitModel,tb2.WaysOfTraffic,tb2.TypeOfContent FROM publisher AS tb1 LEFT JOIN publisher_detail AS tb2 ON tb1.ID = tb2.PublisherId WHERE tb1.ID = $id";//tb2.TypeOfContent,
            $res= $this->getRows($sql);
            if(empty($res)){
                return 0;
            }else{
                $account['base'] = $res[0];
                if(!empty($res['0']['PublisherId'])){
                    $account['site'] = $this->table('publisher_account')->where('PublisherId = ' . intval($id))->find();
                    if(empty($account['site'])){
                        $account['site'] = '';
                    }else {
                        foreach ($account['site'] as $key=>$temp){
                            if(!key_exists($temp['ID'], $_SESSION['pubAccList'])){
                                unset($account['site'][$key]);
                            }
                        }
                    }
                }else{
                    $account['base']['ID'] = '';
                    $account['site'] = '';
                }

                return $account;
            }

    }
    
    //获取用户的付款信息
    function getPaymentAccount($id){
        $sql = "SELECT p.PayPal,p.AccountName,p.AccountNumber,p.AccountCountry,p.AccountCity,p.AccountAddress,p.SwiftCode,p.BankName,p.BranchName,p.MinPaymentAmount,p.NotificationEmail from publisher p WHERE p.ID = $id";
        $res= $this->getRow($sql);
        return $res;
    }
    
    //验证用户密码是否正确
    function getPaymentAccountByPassword($id,$pass){
        $sql = "SELECT p.PayPal,p.AccountName,p.AccountNumber,p.AccountCountry,p.AccountCity,p.AccountAddress,p.SwiftCode,p.BankName,p.BranchName,p.MinPaymentAmount,p.NotificationEmail from publisher p WHERE p.ID = $id and UserPass = '".md5($pass)."'";
        $res= $this->getRow($sql);
        return $res;
    }
    
    //修改用户的付款信息
    function updatePaymentAccount($id,$param){
        $sql = "insert into publisher_bank_account_change_log(`publisherId`,`type`,`AccountName`,`AccountNumber`,`AccountCountry`,`AccountCity`,`AccountAddress`,`SwiftCode`,`BankName`,`BranchName`,`addTime`)  select '".$id."','4',p.`AccountName`,p.`AccountNumber`,p.`AccountCountry`,p.`AccountCity`,p.`AccountAddress`,p.`SwiftCode`,p.`BankName`,p.`BranchName`,'".date('Y-m-d H:i:s')."' from publisher p where p.`ID` = '".$id."'";
        $this->query($sql);
        $update_d = array();
        $update_d['AccountName'] = $param['AccountName'];
        $update_d['AccountNumber'] = $param['AccountNumber'];
        $update_d['AccountAddress'] = $param['AccountAddress'];
        $update_d['AccountCountry'] = $param['AccountCountry'];
        $update_d['AccountCity'] = $param['AccountCity'];
        $update_d['SwiftCode'] = $param['SwiftCode'];
        $update_d['BankName'] = $param['BankName'];
        $update_d['BranchName'] = $param['BranchName'];
        $res = $this->table('publisher')->where('ID = ' . intval($id))->update($update_d);
        return $res;
    }
    
    //修改用户的paypal
    function updatePaypalAccount($id,$param){
        $sql = "insert into publisher_bank_account_change_log(`publisherId`,`type`,`PayPal`,`addTime`)  select '".$id."','1',p.`PayPal`,'".date('Y-m-d H:i:s')."' from publisher p where p.`ID` = '".$id."'";
        $this->query($sql);
        $update_d = array();
        $update_d['PayPal'] = $param['paypalEmail'];
        $res = $this->table('publisher')->where('ID = ' . intval($id))->update($update_d);
        return $res;
    }
    
    //修改最低付款金额
    function updatePaymentAmount($id,$param){
        $sql = "insert into publisher_bank_account_change_log(`publisherId`,`type`,`MinPaymentAmount`,`addTime`)  select '".$id."','2',p.`MinPaymentAmount`,'".date('Y-m-d H:i:s')."' from publisher p where p.`ID` = '".$id."'";
        $this->query($sql);
        $update_d = array();
        $update_d['MinPaymentAmount'] = $param['paymentAmount'];
        $res = $this->table('publisher')->where('ID = ' . intval($id))->update($update_d);
        return $res;
    }
    
    //修改通知邮件
    function updateNotifyEmail($id,$param){
        $sql = "insert into publisher_bank_account_change_log(`publisherId`,`type`,`NotificationEmail`,`addTime`)  select '".$id."','3',p.`NotificationEmail`,'".date('Y-m-d H:i:s')."' from publisher p where p.`ID` = '".$id."'";
        $this->query($sql);
        $update_d = array();
        $update_d['NotificationEmail'] = $param['notifyEmail'];
        $res = $this->table('publisher')->where('ID = ' . intval($id))->update($update_d);
        return $res;
    }
    
    function edit_profile_site($data)
    {
        if (!preg_match('/^https?:\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/', $data['site_domain'])) {
            $rs['code'] = 2;
            $rs['errorId'] = "domainHasError";
            $rs['msg'] = "Please enter the correct format Such as http://www.brandreward.com";
            return $rs;
        }
        $geoBreakdown = $SiteTypeNew = '';
        $data['pub_contentCategory'] = array_unique($data['pub_contentCategory']);
        
        $siteOption = "Content";
        foreach ($data['pub_contentCategory'] as $val){
            if($val != ''){
                $SiteTypeNew .= "+".$val;
            }
            if($val == "1_e" || $val == "2_e"){
                $siteOption = "Promotion";
            }
        }
        
        if($data['pub_otherTypeOfContent'] != ''){
            $SiteTypeNew .= "+".trim($data['pub_otherTypeOfContent']);
        }
        if($SiteTypeNew == ''){
            $rs['code'] = 2;
            $rs['errorId'] = "pub_contentHasError";
            $rs['msg'] = "Core business cannot be blank";
            return $rs;
        }
        $data['pub_traffic'] = array_unique($data['pub_traffic']);
        foreach ($data['pub_traffic'] as $val){
            if($val != ''){
                $geoBreakdown .= "+".$val;
            }
        }
        if($geoBreakdown == ''){
            $rs['code'] = 2;
            $rs['errorId'] = "pub_trafficHasError";
            $rs['msg'] = "Traffic Demographics cannot be blank";
            return $rs;
        }
        $geoBreakdown = trim($geoBreakdown, "+");
        $SiteTypeNew = trim($SiteTypeNew, "+");
        
        if ($data['ID'] > 0) {

//             $row = $this->table('publisher_account')->where('ID = ' . intval($data['ID']) . ' AND PublisherId = ' . intval($data['PublisherId']))->findone();
//             if (!$row){
            if(!key_exists($data['ID'], $_SESSION['pubAccList'])){
                $rs['code'] = 2;
                $rs['errorId'] = "commonHasError";
                $rs['msg'] = "An error occurred. Please refresh and try again.";
                return $rs;
            }
        }

        $db_d = array();
        $db_d['Domain'] = isset($data['site_domain']) ? trim($data['site_domain']) : '';
        $db_d['Alias'] = isset($data['site_alias']) ? trim($data['site_alias']) : '';
        $db_d['Name'] = $db_d['Alias'];
        $db_d['GeoBreakdown'] = $geoBreakdown;
        $db_d['SiteTypeNew'] = $SiteTypeNew;
        $db_d['SiteOption'] = $siteOption;
        $db_d['Description'] = isset($data['site_desc']) ? trim($data['site_desc']) : '';

        if ($data['ID'] > 0) {

            $ID = $data['ID'];
            $db_d['LastUpdateTime'] = date('Y-m-d H:i:s');
            $res = $this->table('publisher_account')->where('ID = ' . intval($data['ID']) . ' AND PublisherId = ' . intval($data['PublisherId']))->update($db_d);
            if (!$res){
                $rs['code'] = 2;
                $rs['errorId'] = "commonHasError";
                $rs['msg'] = "Update error. Please refresh and try again.";
                return $rs;
            }
        } else {
            $db_d['AddTime'] = date('Y-m-d H:i:s');
            $db_d['PublisherId'] = intval($data['PublisherId']);

            $res = $this->table('publisher_account')->insert($db_d);
            $ID = $this->objMysql->getLastInsertId();
            if (empty($ID)) {
                $rs['code'] = 2;
                $rs['errorId'] = "commonHasError";
                $rs['msg'] = "Save error. Please refresh and try again.";
                return $rs;
            }
            $ApiKey = md5($ID);
            $res2 = $this->table('publisher_account')->where('ID = ' . intval($ID))->update(array('ApiKey' => $ApiKey));
            if (!$res2){
                $rs['code'] = 2;
                $rs['errorId'] = "commonHasError";
                $rs['msg'] = "Save error. Please refresh and try again.";
                return $rs;
            }
        }
        $this->getPublisherAccountSessionList($data['PublisherId']);
        $this->changePublisherSiteOption();
        $rs['code'] = 1;
        $rs['msg'] = "Success.";
        return $rs;
    }
    
    //修改publisher的siteoption
    function changePublisherSiteOption(){
        $sql = "SELECT distinct(SiteOption) FROM publisher_account pa where pa.PublisherId = '".$_SESSION['u']['ID']."'";
        $res = $this->getRows($sql);
        if(count($res) > 1){
            $siteOptionP = "None";
            //防止publisher_account中有none的情况
            foreach ($res as $temp){
                if($temp['SiteOption'] == "Content"){
                    if($siteOptionP == "Promotion"){
                        $siteOptionP = "Mixed";
                        break;
                    }else {
                        $siteOptionP = "Content";
                        continue;
                    }
                }else if($temp['SiteOption'] == "Promotion"){
                    if($siteOptionP == "Content"){
                        $siteOptionP = "Mixed";
                        break;
                    }else {
                        $siteOptionP = "Promotion";
                        continue;
                    }
                }
            }
        }else {
            $siteOptionP = isset($res[0]['SiteOption'])?$res[0]['SiteOption']:"None";
        }
        $this->table('publisher')->where('ID = ' . intval($_SESSION['u']['ID']))->update(array('SiteOption' => $siteOptionP));
        
    }

    function change_password($data)
    {
        $row = $this->table('publisher')->where('ID = ' . intval($data['ID']) . ' AND UserPass = "' . md5($data['pub_pwd_old']) . '"')->findone();
        if (empty($row))
            return 1;

        if (!preg_match('/^.{8,}$/', $data['pub_pwd'])) {
            return 2;
        }

        if (!isset($data['pub_pwd_ag']) || $data['pub_pwd_ag'] != $data['pub_pwd']) {
            return 3;
        }

        $update_d = array();
        $update_d['UserPass'] = md5($data['pub_pwd']);
        $res = $this->table('publisher')->where('ID = ' . intval($data['ID']))->update($update_d);
        if (!$res)
            return 4;
        return 0;
    }

    function getcategory($id){

        $subsql = '';

        if($id != 1){
            $subsql.=' WHERE PID = '.$id.'';
        }else{
            $subsql.=' WHERE PID = 1 limit 0,20';
        }
        $sqlres = 'select * from category'.$subsql;
        $resrow = $this->getRows($sqlres);
        $sqlcount = 'select count(1) from category'.$subsql;
        $rescount = $this->getRows($sqlcount);
        return array('resrow'=>$resrow,'rescount'=>$rescount[0]);
    }

    function get_account_info($uid)
    {
        $account = array();
        $account['base'] = $this->table('publisher')->where('ID = ' . intval($uid))->findone();
//         $account['site'] = $this->table('publisher_account')->where('PublisherId = ' . intval($uid))->find();
        $i = 0;
        foreach ($_SESSION['pubAccList'] as $temp){
            $account['site'][$i]['Domain'] = $temp['Domain'];
            $account['site'][$i]['ApiKey'] = $temp['ApiKey'];
            $i++;
        }
        
        return $account;
    }

    function login($data)
    {
        $account = trim($data['pub_account']);
        if (empty($account)) {
            return 1;
        }

        $password = isset($data['pub_pwd']) ? $data['pub_pwd'] : '';
        if (empty($password)) {
            return 2;
        }
        if(BASE_URL == 'http://demo.brandreward.com' || stripos(BASE_URL,'http://localhost/')){ //
        	$sql = "SELECT a.*,b.CategoryId,b.AdvancedCategoryId FROM publisher a LEFT JOIN publisher_detail b ON a.id=b.publisherid WHERE UserName = '" . addslashes($account) . "'";
        }else{
        	$sql = "SELECT a.*,b.CategoryId,b.AdvancedCategoryId FROM publisher a LEFT JOIN publisher_detail b ON a.id=b.publisherid WHERE UserName = '" . addslashes($account) . "' AND UserPass = '" . md5($password) ."'";
        }
        //echo $sql.'-----';
        $row = $this->getRows($sql);
        
        $row = isset($row[0])?$row[0]:'';
        
        if($row['Career'] == 'sub_account'){
            $subPubId = $row['ID'];
            $sql = "select DISTINCT(a.ID) as distinctid,a.*,b.CategoryId,b.AdvancedCategoryId from publisher a LEFT JOIN publisher_detail b ON a.id=b.publisherid LEFT JOIN publisher_sub ps on ps.parentPublisherId = a.ID WHERE ps.PublisherId = '".$row['ID']."'";
            $row = $this->getRows($sql);
            $row = isset($row[0])?$row[0]:'';
            $row['Career'] = 'sub_account';
            $row['subPubId'] = $subPubId;
        }
        
//        $row = $this->table('publisher')->where('UserName = "' . addslashes($account) . '" AND UserPass = "' . md5($password) . '"')->findone();
        if (empty($row)) {
            return 3;
        }
        
        if(BASE_URL !== 'http://demo.brandreward.com'){
            if (!empty($row) && $row['Status'] != 'Active') {
                return 4;
            }
        }

        //get user isreferrer
        $sql = 'select * from publisher where RefID = '.intval($row['ID']);
        $rowisreferrer = $this->getRows($sql);
        if($rowisreferrer){
            $row['isreferrer'] = 1;
        }else{
            $row['isreferrer'] = 0;
        }

        //将两个月前的最近六个月的commission找出来存入session中
        $site = $paidList = $pendingList = array();
        if($row['Career'] == 'sub_account'){
            $sql = "SELECT ApiKey FROM publisher_account pa LEFT JOIN publisher_sub ps on ps.AccountId = pa.ID WHERE ps.ParentPublisherId = '".intval($row['ID'])."' and ps.PublisherId = '".$row['subPubId']."'";
        }else {
            $sql = "SELECT ApiKey FROM publisher_account WHERE PublisherId = ".intval($row['ID']);
        }
        $rs = $this->getRows($sql);
        foreach ($rs as $val){
            $site[] = $val['ApiKey'];
        }
        if(!empty($site)){
            $siteText = implode('","', $site);
            $sixMonthAgo = date("Y-m",strtotime('-6 month'));
            $lastMonthAgo = date("Y-m",strtotime('-1 month'));
            $nextMonthAgo = date("Y-m",strtotime('+1 month'));
            //找最近半年付款了的最近的5条数据
            $sql = 'SELECT p.`PaidDate`,DATE_FORMAT(p.`PaidDate`,"%Y-%m") AS paidMonth,SUM(p.Amount) AS commissions FROM payments p where Site in ("'.$siteText.'") and PaidDate >= "'.$sixMonthAgo.'" and PaidDate < "'.$nextMonthAgo.'" and status = "succ" GROUP BY paidMonth HAVING commissions>10 ORDER BY paidMonth desc limit 5';
            $rs = $this->getRows($sql);
            //取出数据放入数组中
            foreach ($rs as $key=>$val){
                $paidList[$key]['paidTime'] = date("M j,Y",strtotime($val['PaidDate']));
                $paidList[$key]['commissions'] = round($val['commissions'],2);
                $paidList[$key]['status'] = 'confirmed';
            }
            //找到最近一次付款时间
            if(isset($rs[0]['paidMonth']) && $rs[0]['paidMonth'] == date('Y-m')){
                $lastPayMonth = $rs[0]['paidMonth'];
            }else {
                $lastPayMonth = $lastMonthAgo;
            }
            //下个月即将付款的月份
            $nextPayMonth = date("Y-m",strtotime("+1 months".$lastPayMonth));
            $lastWorkDay = $this->lastWorkDay($nextPayMonth);
            //根据统计最近60天前数据的需求，推算出最后一次付款时间的前一个月为我们下次需要付钱的月份
//             $beforePayMonth = date('Y-m',strtotime('-1 month'.$lastPayMonth));
            $sql = "SELECT SUM(Amount) as commissions FROM payments_pending WHERE PubLisherId = ".$row['ID']." AND PaymentsID = 0 AND PaidDate = '0000-00-00'";
            $rs = $this->getRow($sql);
            if(isset($rs['commissions'])){
                $pendingList['visitDate'] = date("Y-m",strtotime("-1 months".$lastPayMonth));
                $pendingList['commissions'] = round($rs['commissions'],2);
                $pendingList['status'] = 'pending';
                $pendingList['payDay'] = date("M j,Y",strtotime($lastWorkDay));
            }
        }
        $_SESSION['paidMonthList'] = $paidList;
        $_SESSION['pendingMonthList'] = $pendingList;

        // set session/cookie for user
        $_SESSION['u'] = $row;
        $pid = $row['ID'];
        $ip = $_SERVER["REMOTE_ADDR"];
        $time = Date('Y-m-d H:m:s');
        $sql = 'INSERT INTO publisher_login_log (PublisherId,Ip,LoginTime) VALUES ("' . $pid . '","' . $ip . '","' . $time . '")';
        $this->query($sql);
        
        $this->getPublisherAccountSessionList($pid);
        
        if($_SESSION['u']['Career'] == 'network' || $_SESSION['u']['Career'] == 'advertiser' || $_SESSION['u']['Career'] == 'advertiser_2' || $_SESSION['u']['Career'] == 'advertiser_white'){
            return $_SESSION['u']['Career'];
        }else{
            return 0;
        }

    }
    
    
    function WhiteListLogin($data){
        if(!isset($data['advertiser_account']) || !isset($data['advertiser_pwd']) || trim($data['advertiser_account']) == '' || $data['advertiser_pwd'] == ''){
            return json_encode([
                'code'=>0,
                'msg'=>'username and password cannot be blank'
            ]);
        }
        
        if(BASE_URL == 'http://demo.brandreward.com' || stripos(BASE_URL,'http://localhost/')){ 
            $sql = "SELECT ID,UserName,Name,Status FROM white_list_account WHERE UserName = '".addslashes(trim($data['advertiser_account']))."'";
        }else{
            $sql = "SELECT ID,UserName,Name,Status FROM white_list_account WHERE UserName = '".addslashes(trim($data['advertiser_account']))."' AND UserPass = '" . md5($data['advertiser_pwd']) ."'";
        }

        $row = $this->getRows($sql);
        if(isset($row[0])){
            $whiteAccount = $row[0];
            $storesql = "select ss.id as storeId,ss.`name` from white_list_store wls left join store ss on ss.ID = wls.StoreId where WhiteAccountId = '".$whiteAccount['ID']."' and wls.Status = 'Active' and ss.StoreAffSupport = 'YES' order by DefaultStoreId,wls.ID";
            $storerow = $this->getRows($storesql);
            if(count($storerow)<=0){
                return json_encode([
                    'code'=>0,
                    'msg'=>'There are no available merchants in this account.'
                ]);
            }else {
                $this->getWhiteListSessionList($row,$storerow);
                return json_encode([
                    'code'=>1
                ]);
            }
        }else {
            return json_encode([
                'code'=>0,
                'msg'=>'Your account name or password is incorrect.'
            ]);
        }
    }
    
    //将white list中的store加入session
    function getWhiteListSessionList($row,$storerow){
        $_SESSION['whiteListAccount'] = $row[0];
        $sessionList = array();
        foreach ($storerow as $temp){
            $sessionList[$temp['storeId']]['storeId'] = $temp['storeId'];
            $sessionList[$temp['storeId']]['name'] = $temp['name'];
        }
        $_SESSION['storeList'] = $sessionList;
        if(!isset($_SESSION['storeActiveList']['active'])){
            $_SESSION['storeActiveList']['active'] = current($_SESSION['storeList'])['storeId'];
            $_SESSION['storeActiveList']['data'] = current($_SESSION['storeList']);
        }
    }
    
    //切换white list的store
    function changeStoreSession($storeId){
        if(isset($_SESSION['storeList'][$storeId])){
            $_SESSION['storeActiveList']['active'] = $_SESSION['storeList'][$storeId]['storeId'];
            $_SESSION['storeActiveList']['data'] = array($_SESSION['storeList'][$storeId]['storeId']=>$_SESSION['storeList'][$storeId]);
        }else {
            $_SESSION['storeActiveList']['active'] = current($_SESSION['storeList'])['storeId'];
            $_SESSION['storeActiveList']['data'] = current($_SESSION['storeList']);
        }
        return true;
    }
    
    function WhiteListLogout()
    {
        unset($_SESSION['whiteListAccount']);
        unset($_SESSION['storeList']);
        unset($_SESSION['storeActiveList']);
        
        unset($_SESSION['u']);
        unset($_SESSION['pubAccList']);
        unset($_SESSION['pubAccActiveList']);
        unset($_SESSION['paidMonthList']);
        unset($_SESSION['pendingMonthList']);
    }

    //查询该月的最后一个工作日
    function lastWorkDay($date){
        //该月的月末是周几
        $thisMonthLastWeekDay = date("N",strtotime('next month -1 day'.date('Y-m-01',strtotime($date))));
        //该月的月末是几号
        $thisMonthLastDay = date("Y-m-d",strtotime('next month -1 day'.date('Y-m-01',strtotime($date))));
        switch ($thisMonthLastWeekDay){
            case "6":
                $lastWorkday = date('Y-m-d',strtotime('-1 days'.$thisMonthLastDay));
                break;
            case "7":
                $lastWorkday = date('Y-m-d',strtotime('-2 days'.$thisMonthLastDay));
                break;
            default:
                $lastWorkday = date('Y-m-t',strtotime($thisMonthLastDay));
                break;
        }
        return $lastWorkday;
    }
    
    //获取publisherAccount信息集合
    function getPublisherAccountSessionList($publisherId){
        if($_SESSION['u']['Career'] == "sub_account"){
            $sql = "SELECT pa.* FROM publisher_account pa LEFT JOIN publisher_sub ps on ps.AccountId = pa.ID WHERE ps.ParentPublisherId = '".intval($publisherId)."' and ps.PublisherId = '".$_SESSION['u']['subPubId']."'";
            $row = $this->getRows($sql);
        }else {
            $row = $this->table('publisher_account')->where('PublisherId = '.intval($publisherId))->find();
        }
        $sessionList = array();
        foreach ($row as $temp){
            $sessionList[$temp['ID']]['ID'] = $temp['ID'];
            $sessionList[$temp['ID']]['ApiKey'] = $temp['ApiKey'];
            $sessionList[$temp['ID']]['Domain'] = $temp['Domain'];
            $sessionList[$temp['ID']]['GeoBreakdown'] = $temp['GeoBreakdown'];
            $sessionList[$temp['ID']]['SiteTypeNew'] = $temp['SiteTypeNew'];
        }
        $_SESSION['pubAccList'] = $sessionList;
        if(isset($_SESSION['pubAccActiveList']['active'])){
            if($_SESSION['pubAccActiveList']['active'] == 'all'){
                $_SESSION['pubAccActiveList']['data'] = $_SESSION['pubAccList'];
            }else {
                if(isset($_SESSION['pubAccList'][$_SESSION['pubAccActiveList']['active']])){
                    $_SESSION['pubAccActiveList']['active'] = $_SESSION['pubAccList'][$_SESSION['pubAccActiveList']['active']]['ID'];
                    $_SESSION['pubAccActiveList']['data'] = array($_SESSION['pubAccList'][$_SESSION['pubAccActiveList']['active']]['ID']=>$_SESSION['pubAccList'][$_SESSION['pubAccActiveList']['active']]);
                }else {
                    $_SESSION['pubAccActiveList']['active'] = 'all';
                    $_SESSION['pubAccActiveList']['data'] = $_SESSION['pubAccList'];
                }
            }
        }else {
            $_SESSION['pubAccActiveList']['active'] = 'all';
            $_SESSION['pubAccActiveList']['data'] = $_SESSION['pubAccList'];
        }
    }
    
    //切换site
    function changePublisherAccountSession($publisherAccountId){
        if($publisherAccountId=='all'){
            $_SESSION['pubAccActiveList']['active'] = 'all';
            $_SESSION['pubAccActiveList']['data'] = $_SESSION['pubAccList'];
        }else {
            if(isset($_SESSION['pubAccList'][$publisherAccountId])){
                $_SESSION['pubAccActiveList']['active'] = $_SESSION['pubAccList'][$publisherAccountId]['ID'];
                $_SESSION['pubAccActiveList']['data'] = array($_SESSION['pubAccList'][$publisherAccountId]['ID']=>$_SESSION['pubAccList'][$publisherAccountId]);
            }else {
                $_SESSION['pubAccActiveList']['active'] = 'all';
                $_SESSION['pubAccActiveList']['data'] = $_SESSION['pubAccList'];
            }
        }
        return true;
    }
    
    //验证要生成的子账户的username是否存在
    function authSubPublisher($username,$id){
        $sql = "select count(1) as c from publisher where username = '".addslashes($username)."' and id <> '".addslashes($id)."'";
        $count = $this->getRow($sql)['c'];
        return $count;
    }
    
    //验证要生成的子账户的数量
    function authSubPublisherCount(){
        $sql = "SELECT count(DISTINCT(pp.ID)) as c FROM publisher_sub ps LEFT JOIN publisher pp ON pp.ID = ps.PublisherId LEFT JOIN publisher_account pa ON pa.ID = ps.AccountId  WHERE ps.ParentPublisherId = '".$_SESSION['u']['ID']."' ";
        $count = $this->getRow($sql)['c'];
        return $count;
    }
    
    //存储子账户
    function saveSubPublisher($param){
        if(isset($param['id']) && $param['id'] != ''){
            $db_d = array();
            $db_d['UserName'] = trim($param['username']);
            $res = $this->table('publisher')->where('ID = '.$param['id'].'')->update($db_d);
            if($res){
                $sql = "Delete from publisher_sub where `PublisherId` = '".addslashes($param['id'])."' and `ParentPublisherId` = '".$_SESSION['u']['ID']."'";
                $this->query($sql);
                $db_r = array();
                $db_r['PublisherId'] = $param['id'];
                $db_r['ParentPublisherId'] = $_SESSION['u']['ID'];
                foreach ($param['SubDomain'] as $temp){
                    if(key_exists($temp, $_SESSION['pubAccList'])){
                        $db_r['AccountId'] = addslashes($temp);
                        $this->table('publisher_sub')->insert($db_r);
                    }
                }
                return true;
            }else {
                return false;
            }
        }else {
            $db_d = array();
            $db_d['UserName'] = trim($param['username']);
            $db_d['UserPass'] = md5(trim($param['password']));
            $db_d['Status'] = 'Active';
            $db_d['Career'] = 'sub_account';
            $res = $this->table('publisher')->insert($db_d);
            if($res){
                $ID = $this->objMysql->getLastInsertId();
                $db_r = array();
                $db_r['PublisherId'] = $ID;
                $db_r['ParentPublisherId'] = $_SESSION['u']['ID'];
                foreach ($param['SubDomain'] as $temp){
                    if(key_exists($temp, $_SESSION['pubAccList'])){
                        $db_r['AccountId'] = addslashes($temp);
                        $this->table('publisher_sub')->insert($db_r);
                    }
                }
                return true;
            }else {
                return false;
            }
        }
    }
    
    //查询子账户
    function searchSubAccount($id){
        $sql = "SELECT pp.ID AS subId,pp.UserName,pa.Domain,ps.AccountId FROM publisher_sub ps LEFT JOIN publisher pp ON pp.ID = ps.PublisherId LEFT JOIN publisher_account pa ON pa.ID = ps.AccountId WHERE ps.ParentPublisherId = $id order by pp.id desc";
        $rs = $this->getRows($sql);
        $return = array();
        foreach ($rs as $temp){
            if(isset($return[$temp['subId']])){
                $return[$temp['subId']]['Domain'] .= "<br>".$temp['Domain'];
                $return[$temp['subId']]['AccountId'] .= ",".$temp['AccountId'];
            }else {
                $return[$temp['subId']]['SubId'] = $temp['subId'];
                $return[$temp['subId']]['UserName'] = $temp['UserName'];
                $return[$temp['subId']]['Domain'] = $temp['Domain'];
                $return[$temp['subId']]['AccountId'] = $temp['AccountId'];
            }
        }
        return $return;
    }
    
    //验证要删除的子账户是否是该publisher的子账户
    function checkDeleteSubPublisher($param){
        $sql = "SELECT count(1) as c FROM publisher_sub ps LEFT JOIN publisher pp ON pp.ID = ps.PublisherId WHERE ps.ParentPublisherId = '".$_SESSION['u']['ID']."' AND ps.PublisherId = '".addslashes($param['id'])."' AND UserName = '".addslashes($param['username'])."' AND pp.Career = 'sub_account'";
        $count = $this->getRow($sql)['c'];
        return $count;
    }
    
    //删除publisher的子账户
    function deleteSubPublisher($param){
        $sql = "Delete from publisher_sub where `PublisherId` = '".addslashes($param['id'])."' and `ParentPublisherId` = '".$_SESSION['u']['ID']."'";
        $this->query($sql);
        $sql = "Delete from publisher where `ID` = '".addslashes($param['id'])."' and UserName = '".addslashes($param['username'])."' and `Career` = 'sub_account'";
        $this->query($sql);
        return true;
    }
    
    //修改子账户密码
    function changeSubPublisherPwd($param){
        $db_d = array();
        $db_d['UserPass'] = md5(trim($param['password']));
        $res = $this->table('publisher')->where('ID = '.addslashes($param['id']).' and Career = "sub_account" and UserName = "'.addslashes($param['username']).'"')->update($db_d);
        return true;
    }

    function logout()
    {
        unset($_SESSION['u']);
        unset($_SESSION['pubAccList']);
        unset($_SESSION['pubAccActiveList']);
        unset($_SESSION['paidMonthList']);
        unset($_SESSION['pendingMonthList']);
        
        unset($_SESSION['whiteListAccount']);
        unset($_SESSION['storeList']);
        unset($_SESSION['storeActiveList']);
    }

    function get_login_user()
    {
        if (isset($_SESSION['u'])) {
            return $_SESSION['u'];
        }
        return null;
    }

    function send_contact_message()
    {
        $insert_c = array();
        $insert_c['IP'] = $_SERVER["REMOTE_ADDR"];
        $insert_c['Time'] = Date('Y-m-d H:m:s');
        $insert_c['Name'] = trim($_POST['con_name']);
        $insert_c['Email'] = trim($_POST['con_email']);
        $insert_c['publisher_type'] = trim($_POST['con_type']);
        $insert_c['Message'] = trim($_POST['con_message']);
        $this->table('message')->insert($insert_c);
        $insert_c = array();



        $to = 'support@brandreward.com';
        $subject = 'Contact Us Message';
        $message = trim($_POST['con_message']);

// 当发�?HTML电子邮件�?请始终设�?content-type
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// 更多报头
        $headers .= 'From: ' .trim($_POST['con_email']). "\r\n";
        $result = @mail($to, $subject, $message, $headers);
        return 1;
    }

    function retrieve_password()
    {
        if(!isset($_POST['re_email']) || !isset($_POST['token']) || !isset($_POST['re_pwd']) || !isset($_POST['re_pwd_again'])){
            $rs = array(
                'code' => 2,
                'msg' => 'Please fill in the password'
            );
            return json_encode($rs);
        }
        if(!preg_match('/^.{8,}$/', $_POST['re_pwd'])){
            $rs = array(
                'code' => 2,
                'msg' => 'You need a minimum of 8 characters'
            );
            return json_encode($rs);
        }
        if($_POST['re_pwd'] != $_POST['re_pwd_again']){
            $rs = array(
                'code' => 2,
                'msg' => 'Please confirm new passwords'
            );
            return json_encode($rs);
        }
        $res = $this->table('publisher')->where('Email = "' . addslashes($_POST['re_email']) . '"')->findone();
        if(empty($res)){
            $rs = array(
                'code' => 2,
                'msg' => 'You are not authorized'
            );
            return json_encode($rs);
        }else {
            $userToken = md5($res['ID'].$res['UserName'].$res['UserPass']);
            if($userToken != $_POST['token']){
                $rs = array(
                    'code' => 2,
                    'msg' => 'You are not authorized'
                );
                return json_encode($rs);
            }
        }
        $sql = 'UPDATE publisher SET UserPass = "' . md5($_POST['re_pwd']) . '" WHERE Email = "' . addslashes($_POST['re_email']) . '"';
        $this->objMysql->query($sql);
        $rs = array(
            'code' => 1,
            'msg' => 'success'
        );
        return json_encode($rs);
        //check email
        /*if (empty($_POST['re_email']))
            return 0;
        if (!preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', $_POST['re_email']))
            return 1;


        $sql = 'SELECT Email FROM publisher WHERE Email = "' . $_POST['re_email'] . '"';
        $arr = $this->objMysql->getRows($sql);
        if (empty($arr[0]))
            return 2;*/
//check password
        /* if (empty($_POST['re_pwd']))
            return 3;
        if (!preg_match('/^.{8,}$/', $_POST['re_pwd'])) {
            return 4;
        }
        if ($_POST['re_pwd'] != $_POST['re_pwd_again'])
            return 5;
        $sql = 'UPDATE publisher SET UserPass = "' . md5($_POST['re_pwd']) . '" WHERE Email = "' . $_POST['re_email'] . '"';
        $this->objMysql->query($sql);
        return 6; */
    }

    function send_partnership_message()
    {
        if (empty($_POST['part_email']))
            return 0;
        if (!preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', $_POST['part_email']))
            return 1;
        if (empty($_POST['part_message']))
            return 2;
        $insert_c = array();
        $insert_c['IP'] = $_SERVER["REMOTE_ADDR"];
        $insert_c['Time'] = Date('Y-m-d H:m:s');
        $insert_c['Name'] = trim($_POST['part_name']);
        $insert_c['Email'] = trim($_POST['part_email']);
        $insert_c['PhoneNumber'] = "";
        $insert_c['Type'] = "partnership";
        $insert_c['Message'] = trim($_POST['part_message']);
        $this->table('message')->insert($insert_c);
        $insert_c = array();
        return '3';
    }

    function check_email()
    {
        if (empty($_POST['register-email']))
            return 0;
        if (!preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', $_POST['register-email']))
            return 1;
        $sql = 'SELECT * FROM publisher WHERE Email = "' . $_POST['register-email'] . '"';
        $arr = $this->objMysql->getRows($sql);
        if (empty($arr)) {
            return 2;
        } else {
            $result = $this->send_forgotPwd_email($arr[0]);
            return $result;
        }
    }


    function send_forgotPwd_email($row)
    {
        $uid = $row['ID'];
        $time = date('Y-m-d H:i:s');
        $token = md5($uid . $row['UserName'] . $row['UserPass']);//组合验证�?
        $to = $row['Email'];
        $url = BASE_URL . "/retrievePwd.php?email=" . $row['Email'] . "&token=" . $token . "&time=" . $time;//构造URL
        /* $message = "Hi " . $to . ",<br/><br/>
        			We recently received a request to reset your Brandreward account password on " . $time . ". Please click on the URL below to change your password (Link is valid only 24 hours). If this was not you, please contact us immediately so we can monitor your account for suspicious activity.<br/><br/>
        			Reset Link: <a href='" . $url . "'target='_blank'>" . $url . "</a>"; */
        $kvparam = array(
            "now_time" => $time,
            "publisher_name" => $to,
            "reset_url" => $url
        );
        $doc = get_email_template($kvparam,'email_layout/forgot_password.html');
        $result = send_email($to,'Reset Password',$doc,'ResetPassword');
        if ($result) {//邮件发送成�?
            return 3;
        } else {
            return 4;
        }
        
        /* $uid = $row['ID'];
        $time = date('Y-m-d H:i:s');
        $token = md5($uid . $row['UserName'] . $row['UserPass']);//组合验证�?
        $url = BASE_URL . "/retrievePwd.php?email=" . $row['Email'] . "&token=" . $token . "&time=" . $time;//构造URL
        $to = $row['Email'];
        $subject = "Reset Password";
        $message = "Hi " . $to . ",<br/><br/>
        			We recently received a request to reset your Brandreward account password on " . $time . ". Please click on the URL below to change your password (Link is valid only 24 hours). If this was not you, please contact us immediately so we can monitor your account for suspicious activity.<br/><br/>
        			Reset Link: <a href='" . $url . "'target='_blank'>" . $url . "</a>";

// 当发�?HTML 电子邮件�?请始终设�?content-type
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// 更多报头
        $headers .= 'From: <support@brandreward.com>' . "\r\n";
        $result = @mail($to, $subject, $message, $headers);
        if ($result) {//邮件发送成�?
            return 3;
        } else {
            return 4;
        } */
    }
    
    function sign1($data)
    {
        mysql_query("SET NAMES 'latin1'");
        $insert_d = array();
        //        $res = $this->table('publisher')->where('UserName = "' . trim($data['pub_email']) . '"')->findone();
        $sql = 'SELECT a.ID,b.Name FROM publisher a LEFT JOIN publisher_account b ON a.ID = b.PublisherId WHERE a.UserName = "' . trim($data['pub_email']) . '"';
        $res = $this->getRows($sql);
        if (isset($res[0]['ID']) && $res[0]['ID'] && isset($res['0']['Name']) && $res['0']['Name'])
            return 0;
    
        $insert_d['Email'] = trim($data['pub_email']);
        $insert_d['UserName'] = trim($data['pub_email']);
        $insert_d['UserPass'] = md5($data['pub_pwd']);
        $insert_d['Career'] = 'level1';
        $insert_d['Status'] = 'Unaudited';
        $insert_d['LastUpdateTime'] = date('Y-m-d H:i:s');
        if(isset($_COOKIE['br_refer_p'])){
            $insert_d['RefID'] = intval($_COOKIE['br_refer_p']);
            $insert_d['RefRate'] = 5;
            $insert_d['Tax'] = 10;
        }else{
            $insert_d['RefID'] = 0;
            $insert_d['RefRate'] = 0;
            $insert_d['Tax'] = 15;
        }
    
        if (isset($res[0]['ID']) && $res[0]['ID'] && !$res['0']['Name']) {
            $res = $this->table('publisher')->where('UserName = "' . $insert_d['UserName'] . '"')->update($insert_d);
            return 3;
        }
    
    
        $insert_d['AddTime'] = date('Y-m-d H:i:s');
    
        $res = $this->table('publisher')->insert($insert_d);
        $id = $this->objMysql -> getLastInsertId();
        $did['PublisherId'] = $id;
        $this->table('publisher_detail')->insert($did);
        if ($res) {
            return 1;
        } else
            return 2;
    }
    
    function sign2($data)
    {
    
        // new publisher
        $insert_d = array();
        $insert_d['Name'] = trim($data['pub_firstName']) . ' ' . trim($data['pub_lastName']);
        $insert_d['CompanyAddr'] = trim($data['pub_address1']);
        $insert_d['Domain'] = trim($data['pub_site']);
        $insert_d['Company'] = trim($data['pub_company']);
        $insert_d['PayPal'] = trim($data['pub_paypal']);
        $insert_d['Phone'] = trim($data['pub_phone']);
        $insert_d['Country'] = intval(trim($data['pub_country']));
        $insert_d['ZipCode'] = trim($data['pub_zipCode']);
        $res = $this->table('publisher')->where('UserName = "' . $data['email'] . '"')->update($insert_d);
        if (!$res)
            return 0;
    
        $sql = 'SELECT `ID` FROM publisher WHERE UserName = "' . $data['email'] . '"';
        $arr = $this->objMysql->getRows($sql);
    
        if($insert_d['Country'] >= Constant::COUNTRY_ID_FRANCE && $insert_d['Country'] <= Constant::COUNTRY_ID_FRANCE_SOUTHERN){
            //濞夋洝顕�            $lang = 1;
        }else if($insert_d['Country'] == Constant::COUNTRY_ID_GERMANY){
            //瀵扮柉顕�            $lang = 2;
        }else{
            //閼昏精顕�            $lang = 3;
        }
        // add site to table pulisher_account
        $db_d = array();
        $db_d['PublisherId'] = $arr[0]['ID'];
        $db_d['Name'] = trim($data['pub_siteName']);
        $db_d['Domain'] = $insert_d['Domain'];
        //        $db_d['Alias'] = $insert_d['Name'];
        $db_d['Alias'] =  $db_d['Name'];
        $db_d['AddTime'] = date('Y-m-d H:i:s');
    
        if (!$db_d['Name'])
            return 2;
        if (!$db_d['Domain'])
            return 3;
    
    
        $res = $this->table('publisher_account')->insert($db_d);
        if (!$res) {
            return 0;
        }
        $ID = $this->objMysql->getLastInsertId();
        $ApiKey = md5($ID);
        $res = $this->table('publisher_account')->where('ID = ' . intval($ID))->update(array('ApiKey' => $ApiKey));
        if (!$res) {
            return 0;
        }
    
    
    
        // mail to signup@brandreward.com
        if (DB_HOST != 'dev01.mgsvr.com') {
    
            include_once(INCLUDE_ROOT.'conf_ini.php');
            include_once(INCLUDE_ROOT.'init_ajax.php');
            $objTpl->assign('sys_header', $sys_header);
            $objTpl->assign('info', $insert_d);
            $objTpl->assign('email', $data['email']);
            $objTpl->assign('siteName', $data['pub_siteName']);
            $message = $objTpl->fetch('b_signup_email.html');
    
    
    
    
            $to = 'signup@brandreward.com';
            $subject = "New Publisher !";
            $objTpl->fetch("b_signup_email.html");
    
            // 瑜版挸褰傞敓锟紿TML 閻㈤潧鐡欓柇顔绘閿燂拷鐠囧嘲顫愮紒鍫ｎ啎閿燂拷content-type
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // 閺囨潙顦块幎銉ャ仈
            $headers .= 'From: <support@brandreward.com>' . "\r\n";
            $result = mail($to, $subject, $message, $headers);
    
            $to = $data['email'];
            $subject = "Welcome to Brandreward";
            $objTpl->assign('lang', $lang);
            $objTpl->assign('name', $data['pub_firstName']);
            $message = $objTpl->fetch('b_signup_email1.html');
            $headers .= "From: <signup@brandreward.com>"."\r\n";
            mail($to, $subject, $message, $headers);
        }
         
        unset($insert_d);
        unset($db_d);
        return 1;
    
    }
    
    function sign3($data)
    {
        $sql = 'SELECT a.ID FROM publisher a RIGHT JOIN publisher_detail b ON a.ID = b.PublisherId WHERE a.UserName = "' . trim($data['email']) . '"';
        $res = $this->getRows($sql);
        // new publisher
        $insert_d = array();
        $insert_d['StaffNumber'] = trim($data['StaffNumber']);
        $insert_d['CurrentNetwork'] = trim($data['CurrentNetwork']);
        $insert_d['GeoBreakdown'] = trim($data['GeoBreakdown']);
        $insert_d['DevKnowledge'] = trim($data['DevKnowledge']);
        $insert_d['CategoryId'] = trim($data['CategoryId']);
        $insert_d['ContentProduction'] = trim($data['ContentProduction']);
        $insert_d['ProfitModel'] = trim($data['ProfitModel']);
        $insert_d['SiteType'] = trim($data['SiteType']);
        $insert_d['TypeOfContent'] = trim($data['TypeOfContent']);
        $insert_d['WaysOfTraffic'] = trim($data['WaysOfTraffic']);
        $id = $res[0]['ID'];
        $res = $this->table('publisher_detail')->where('PublisherId = '.$id.'')->update($insert_d);
    
        $update_d['GeoBreakdown'] = $insert_d['GeoBreakdown'];
        $update_d['SiteTypeNew'] = $insert_d['SiteType'];
        $siteOption = "Content";
        $siteTypeArr = explode("+", $insert_d['SiteType']);
        foreach ($siteTypeArr as $val){
            if($val == "1_e" || $val == "2_e"){
                $siteOption = "Promotion";
                break;
            }
        }
        $update_d['SiteOption'] = $siteOption;
        $this->table('publisher_account')->where('PublisherId = '.$id.'')->update($update_d);
//         $this->table('publisher')->where('ID = '.$id.'')->update(array('SiteOption' => $siteOption));
        $this->table('publisher')->where('ID = '.$id.'')->update(array('SiteOption' => $siteOption,'Status'=>'Active'));
        
        //第三步注册完成后发送一封邮件
        $row = $this->table('publisher')->where('ID = '.$id.'')->field('Country,Email')->findone();
        if($row['Country'] == Constant::COUNTRY_ID_FRANCE){
            $html = "email_layout/sign_up_succ_fr.html";
        }else if($row['Country'] == Constant::COUNTRY_ID_GERMANY){
            $html = "email_layout/sign_up_succ_de.html";
        }else {
            $html = "email_layout/sign_up_succ_en.html";
        }
        $kvparam = array(
            'publisher_name' => $row['Email']
        );
        $doc = get_email_template($kvparam,$html);
        $result = send_email($row['Email'],'Congratulation',$doc,'Congratulation');
        
        //新用户创建时默认要block掉不符合条件的联盟
        $this->publisherSignupBlock($id);
    
        if (!$res){
            return 0;
        }
        return 1;
    }

    /* function sign1($data)
    {
        mysql_query("SET NAMES 'latin1'");
        $signinfo = array();
        $insert_d = array();
        //check email
        $email = trim($data['pub_email']);
        $sql = "select Email from publisher where Email ='$email'";
        $check1 = $this->getRows($sql);
        $sql = "select Email from publisher_signup where Email ='$email'";
        $check2 = $this->getRows($sql);
        if(!empty($check1) || !empty($check2)){
            echo 0;
            die;
        }
        $insert_d['Email'] = $email;
        $insert_d['UserName'] = trim($data['pub_email']);
        $insert_d['UserPass'] = md5($data['pub_pwd']);
        $insert_d['Career'] = 'level1';
        $insert_d['Status'] = 'Unaudited';
        $insert_d['AddTime'] = date('Y-m-d H:i:s');

        $signinfo['key'] = md5($insert_d['Email'].date('Y-m-d H:i:s'));
        $signinfo['info'] = json_encode($insert_d);
        $signinfo['Email'] = $insert_d['Email'];
        $signinfo['AddTime'] = date('Y-m-d H:i:s');;
        //邮件发送
        $EmailTo = $signinfo['Email'];
        $BatchName = 'auto_'.date('YmdHis').'_'.$EmailTo.'_signupsucc';
        $emailUniqueID = $BatchName.'_'.floor(rand(0,999)*10000);
        $subject = 'Sign up successfully';
        $MessageName = 'sign_up_verify';
        $SITEID = "s03";
        $email_info = array(
            "method" => "bronto-template",
            "Type" => "edm_couponalert",
            "Site" => $SITEID,
            "Key" => $emailUniqueID,
            "BatchName" => $BatchName,
            "EmailTo" => $EmailTo,
            "EmailSubject" => $subject,
            "EmailFrom" => "support@brandreward.com",
            "EmailCharset" => "utf-8",
            "EmailFormat" => "HTML",
            "MessageName" => $MessageName,
            "templateMailSubject" => $subject,
            "template_publishername" => 'Brandreward',
            "template_key" => '<a target="blank" href="'.BASE_URL.'/signup.php?signkey='.$signinfo['key'].'">'.''.BASE_URL.'/signup.php?signkey='.$signinfo['key'].'</a>',
            "template_baseurl" => BASE_URL,
        );
        $res = send_bronto_email($email_info);
        if($res == 1){
            $res = $this->table('publisher_signup')->insert($signinfo);
            if($res == 1){
                echo 1;
            }
        }else{
            echo 5;
        }
    }

    function sign2($data)
    {
        // new publisher
        if(empty($data['signkey'])){
            return 4;
        }
        $insert_d = array();
        $sql = "select id,info from publisher_signup where id=".$data['signkey'];
        $res = $this->getRow($sql);
        $did = $res['id'];
        $info = json_decode($res['info'],true);
        foreach($info as $k=>$v){
            $insert_d[$k] = $v;
        }
        $insert_d['Name'] = trim($data['pub_firstName']) . ' ' . trim($data['pub_lastName']);
        $insert_d['CompanyAddr'] = trim($data['pub_address1']);
        $insert_d['Domain'] = trim($data['pub_site']);
        $insert_d['Company'] = trim($data['pub_company']);
        $insert_d['PayPal'] = trim($data['pub_paypal']);
        $insert_d['Phone'] = trim($data['pub_phone']);
        $insert_d['Country'] = intval(trim($data['pub_country']));
        $insert_d['ZipCode'] = trim($data['pub_zipCode']);
        $sql = "delete from publisher_signup where id=".$did;
        $this->query($sql);
        $res = $this->table('publisher')->insert($insert_d);
        if (!$res)
            return 0;
        $sql = 'SELECT `ID` FROM publisher WHERE UserName = "' . $insert_d['Email'] . '"';
        $arr = $this->objMysql->getRows($sql);
        $db_d = array();
        $db_d['PublisherId'] = $arr[0]['ID'];
        $db_d['Name'] = trim($data['pub_siteName']);
        $db_d['Domain'] = $insert_d['Domain'];
        $db_d['Alias'] =  $db_d['Name'];
        $db_d['AddTime'] = date('Y-m-d H:i:s');
        if (!$db_d['Name'])
            return 2;
        if (!$db_d['Domain'])
            return 3;
        $res = $this->table('publisher_account')->insert($db_d);
        if (!$res) {
            return 0;
        }
        $ID = $this->objMysql->getLastInsertId();
        $ApiKey = md5($ID);
        $res = $this->table('publisher_account')->where('ID = ' . intval($ID))->update(array('ApiKey' => $ApiKey));
        if (!$res) {
            return 0;
        }
        return $db_d['PublisherId'];
    }

    function sign3($data)
    {
        $insert_d = array();
        $insert_d['StaffNumber'] = trim($data['StaffNumber']);
        $insert_d['CurrentNetwork'] = trim($data['CurrentNetwork']);
        $insert_d['GeoBreakdown'] = trim($data['GeoBreakdown']);
        $insert_d['CategoryId'] = trim($data['CategoryId']);
        $insert_d['ContentProduction'] = trim($data['ContentProduction']);
        $insert_d['ProfitModel'] = trim($data['ProfitModel']);
        $insert_d['SiteType'] = trim($data['SiteType']);
        $insert_d['WaysOfTraffic'] = trim($data['WaysOfTraffic']);
        $id = $data['upid'];
        $res = $this->table('publisher_detail')->where('PublisherId = '.$id.'')->update($insert_d);
        
        $update_d['GeoBreakdown'] = $insert_d['GeoBreakdown'];
        $update_d['SiteTypeNew'] = $insert_d['SiteType'];
        $siteOption = "Content";
        $siteTypeArr = explode("+", $insert_d['SiteType']);
        foreach ($siteTypeArr as $val){
            if($val == "1_e" || $val == "2_e"){
                $siteOption = "Promotion";
                break;
            }
        }
        $update_d['SiteOption'] = $siteOption;
        $this->table('publisher_account')->where('PublisherId = '.$id.'')->update($update_d);
//         $this->table('publisher')->where('ID = '.$id.'')->update(array('SiteOption' => $siteOption));
        //注册完成后直接active
        $this->table('publisher')->where('ID = '.$id.'')->update(array('SiteOption' => $siteOption,'Status'=>'Active'));
        
        //第三步注册完成后发送一封邮件
        $row = $this->table('publisher')->where('ID = '.$id.'')->field('Country,Email')->findone();
        if($row['Country'] == Constant::COUNTRY_ID_FRANCE){
            $html = "email_layout/sign_up_succ_fr.html";
        }else if($row['Country'] == Constant::COUNTRY_ID_GERMANY){
            $html = "email_layout/sign_up_succ_de.html";
        }else {
            $html = "email_layout/sign_up_succ_en.html";
        }
        $kvparam = array(
            'publisher_name' => $row['Email']
        );
        $doc = get_email_template($kvparam,$html);
        $result = send_email($row['Email'],'Congratulation',$doc,'Congratulation');
        
        //新用户创建时默认要被高级别的联盟block掉
        $this->publisherSignupBlock($id);
        
        if (!$res){
            return 0;
        }
        return 1;
    } */
    
    //新注册的用户级别默认为TIER2，要被级别为TIER1的联盟block掉
    function publisherSignupBlock($id){
        $affSql = "SELECT ID FROM wf_aff WHERE `IsActive` = 'YES' AND `Level` = 'TIER1'";
        $affArr = $this->getRows($affSql);
        foreach ($affArr as $aff){
            $sql = "INSERT INTO block_relationship(`BlockBy`,`AccountId`,`AccountType`,`PublisherId`,`ObjId`,`ObjType`,`Status`,`AddTime`,`Source`)
             VALUES('Affiliate','".$id."','PublisherId','".$id."','".$aff['ID']."','Affiliate','Active','".date('Y-m-d H:i:s')."','SYSTEM')";
            $this->query($sql);
        }
        return true;
    }

    function getTrafficRpt($data,$page_size=20){
        $page = isset($data['p'])&&$data['p']?$data['p']:1;
        preg_match('/_([0-9])+/',$data['id'],$matches);
        $data['id'] = $matches['1'];
        $sql = 'SELECT COUNT(*) AS co FROM (SELECT b.pageUrl,c.`Name`,c.IdInAff,b.created,d.Domain,a.sessionId FROM bd_out_tracking_min a LEFT JOIN bd_out_tracking b ON a.ID = b.ID LEFT JOIN program c ON a.programId = c.ID LEFT JOIN publisher_account d ON a.site = d.ApiKey WHERE a.affId = '.$data['id'].' AND a.createddate > "'.$data['tran_from'].'" AND a.createddate < "'.$data['tran_to'].'" ORDER BY a.ID DESC) AS sub';
        $arr = $this->getRows($sql);
        $total = $arr['0']['co'];
        $page_data['total'] = $total;
        $page_data['page_now'] = $page;
        $page_data['page_total'] = ceil($total/$page_size);
        $page_data['page_size'] = $page_size;

        $sql = 'SELECT b.pageUrl,c.`Name`,c.IdInAff,b.created,d.Domain,a.sessionId FROM bd_out_tracking_min a LEFT JOIN bd_out_tracking b ON a.ID = b.ID LEFT JOIN program c ON a.programId = c.ID LEFT JOIN publisher_account d ON a.site = d.ApiKey WHERE a.affId = '.$data['id'].' AND a.createddate > "'.$data['tran_from'].'" AND a.createddate < "'.$data['tran_to'].'" ORDER BY a.ID DESC LIMIT '.($page-1)*$page_size.','.$page_size;
        $arr = $this->getRows($sql);
        $return_d['tran'] = $arr;
        $return_d['page'] = $page_data;
        return $return_d;
    }
 
    function get_spactr_list($storeid,$search){
        $where_str = ' AND b.Domain is not null ';
        if(isset($search['Domain'])){
            $where_str .= ' AND b.Domain like "%'.addslashes($search['Domain']).'%"';
        }
        $sql = 'SELECT a.ID,a.StoreId,a.PAId,a.Status,b.Domain FROM r_store_publisher_ctr as a left join publisher_account as b on a.PAId = b.ID  WHERE a.StoreId = '.intval($storeid).$where_str.' ORDER BY a.ID';
    
        return $this->getRows($sql);
    }

    function get_refer_info($uid){
        $sql = "SELECT count(*) as c FROM publisher WHERE RefID = ".intval($uid);
        $row = $this->getRow($sql);

        $pubnums = $row['c']?$row['c']:0;

        $sql = "SELECT COUNT(*) AS orders,SUM(RefCommission) AS refcommission FROM rpt_transaction_unique WHERE RefPublisherId = ".$uid;
        $row = $this->getRow($sql);
        $order = $row['orders']?$row['orders']:0;
        $refcommission = $row['refcommission']?$row['refcommission']:0;

        return array('pubnums'=>$pubnums,'order'=>$order,'refcommission'=>$refcommission);
    }

}
