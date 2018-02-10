<?php
class AffiliateBlock extends LibFactory
{
    
    //获取block列表
    function getBlockList($param,$start,$pagesize){
        $where = "";
        if((isset($param['Publisher']) && $param['Publisher']!='') || (isset($param['Manager']) && $param['Manager']!='')){
            $pubWhere = $managerWhere = '';
            if(isset($param['Publisher']) && $param['Publisher']!=''){
                $pubWhere = " AND ( ( pp.`Name` LIKE '%{$param['Publisher']}%' OR pp.`Email` LIKE '%{$param['Publisher']}%' OR pp.`UserName` LIKE '%{$param['Publisher']}%' OR pp.`Domain` LIKE '%{$param['Publisher']}%' )  or pa.`Domain` LIKE '%{$param['Publisher']}%' ) ";
            }
            if(isset($param['Manager']) && $param['Manager']!=''){
                $managerWhere = " AND pp.Manager = '".$param['Manager']."'";
            }
            $sql = "select pp.ID as pubId,pa.ID as pubAccId from publisher pp LEFT JOIN publisher_account pa on pp.ID = pa.PublisherId where 1 = 1 $pubWhere $managerWhere ";
            $Ids = $this->getRows($sql);
            $pubIds = $pubAccIds = array();
            foreach ($Ids as $tt){
                if($tt['pubId']>0){
                    $pubIds[$tt['pubId']] = $tt['pubId'];
                }
                if($tt['pubAccId']>0){
                    $pubAccIds[$tt['pubAccId']] = $tt['pubAccId'];
                }
            }
            if(!empty($pubIds) && !empty($pubAccIds)){
                $where .= " AND ( ( br.AccountType = 'PublisherId' and br.AccountId in (".implode(",", $pubIds).") ) OR ( br.AccountType = 'AccountId' and br.AccountId in (".implode(",", $pubAccIds).") ) ) ";
            }else if(!empty($pubIds)){
                $where .= " AND ( br.AccountType = 'PublisherId' and br.AccountId in (".implode(",", $pubIds).") ) ";
            }else if(!empty($pubAccIds)){
                $where .= " AND ( br.AccountType = 'AccountId' and br.AccountId in (".implode(",", $pubAccIds).") ) ";
            }else {
                $where .= " AND 1 = 0";
            }
        }
        if(isset($param['Network']) && $param['Network']!=''){
            $sql = "select wa.ID as affId,pr.ID as programId from wf_aff wa LEFT JOIN program pr on wa.ID = pr.AffId where wa.`IsActive`='YES' and ( ( wa.`Name` LIKE '%{$param['Network']}%' OR wa.`ShortName` LIKE '%{$param['Network']}%' ) or pr.`Homepage` like '%{$param['Network']}%' )";
            $Ids = $this->getRows($sql);
            $affIds = $proIds = array();
            foreach ($Ids as $tt){
                if($tt['affId']>0){
                    $affIds[$tt['affId']] = $tt['affId'];
                }
                if($tt['programId']>0){
                    $proIds[$tt['programId']] = $tt['programId'];
                }
            }
            if(!empty($affIds) && !empty($proIds)){
                $where .= " AND ( ( br.ObjType = 'Affiliate' and br.ObjId in (".implode(",", $affIds).") ) OR ( br.ObjType = 'Program' and br.ObjId in (".implode(",", $proIds).") ) ) ";
            }else if(!empty($affIds)){
                $where .= " AND ( br.ObjType = 'Program' and br.ObjId in (".implode(",", $proIds).") ) ";
            }else if(!empty($proIds)){
                $where .= " AND ( br.ObjType = 'Affiliate' and br.ObjId in (".implode(",", $affIds).") ) ";
            }else {
                $where .= " AND 1 = 0";
            }
        }
        if(isset($param['Store']) && $param['Store']!=''){
            $where .= " AND ( br.ObjType = 'Store' AND br.ObjId in ( select ID from store where ( Name like '%{$param['Store']}%' or NameOptimized like '%{$param['Store']}%' ) ) ) ";
        }
        $where .= " AND `Source` = 'Normal'";
        $countSql = "SELECT count(1) as count FROM block_relationship br WHERE br.`Status` = 'Active' $where ";
        $countNum = $this->getRow($countSql)['count'];
        $sql = "SELECT br.ID,br.AccountId,br.AccountType,br.ObjId,br.ObjType,br.Remark,br.Add_Violation_Warning,br.AddTime FROM block_relationship br WHERE br.`Status` = 'Active' $where ORDER BY br.AddTime DESC LIMIT ". $start . ',' . $pagesize;
        $list = $this->getRows($sql);
        $publisherList = $publisherAccountList = $affList = $programList = $storeList = array();
        foreach ($list as $val){
            if($val['AccountType'] == 'AccountId'){
                $publisherAccountList[$val['AccountId']] = $val['AccountId'];
            }else {
                $publisherList[$val['AccountId']] = $val['AccountId'];
            }
            if($val['ObjType'] == 'Affiliate'){
                $affList[$val['ObjId']] = $val['ObjId'];
            }else if($val['ObjType'] == 'Program'){
                $programList[$val['ObjId']] = $val['ObjId'];
            }else if($val['ObjType'] == 'Store'){
                $storeList[$val['ObjId']] = $val['ObjId'];
            }
        }
        $pubResList = $affResList = $programResList = $storeResList = $affProResList = array();
        if(!empty($publisherList) || !empty($publisherAccountList)){
            $sql = '';
            if(!empty($publisherAccountList)){
                $sql .= " SELECT pp.`ID` as pubId,CONCAT(pp.`Name`,'(',pp.`Company`,')','(',pp.`Status`,')') as publisherName,pp.Manager,pa.`ID` as pubAccId,pa.Domain from publisher_account pa left join publisher pp on pp.ID = pa.PublisherId where pa.ID in (".implode(",", $publisherAccountList).") ";
            }
            if(!empty($publisherList)){
                if($sql !=''){
                    $sql .= " UNION ";
                }
                $sql .= " SELECT pp.`ID` as pubId,CONCAT(pp.`Name`,'(',pp.`Company`,')','(',pp.`Status`,')') as publisherName,pp.Manager,NULL as pubAccId,pa.Domain from publisher pp LEFT JOIN publisher_account pa ON pp.ID = pa.PublisherId WHERE pp.ID in (".implode(",", $publisherList).") ";
            }
            $publisherRes = $this->getRows($sql);
            foreach ($publisherRes as $temp){
                if($temp['pubAccId']!=null){
                    $pubResList['pubAcc'][$temp['pubAccId']]['publisherName'] = $temp['publisherName'];
                    $pubResList['pubAcc'][$temp['pubAccId']]['Domain'] = $temp['Domain'];
                    $pubResList['pubAcc'][$temp['pubAccId']]['Manager'] = $temp['Manager'];
                }else {
                    $pubResList['pub'][$temp['pubId']]['publisherName'] = $temp['publisherName'];
                    if(isset($pubResList['pub'][$temp['pubId']]['Domain'])){
                        $pubResList['pub'][$temp['pubId']]['Domain'] .= "<br>".$temp['Domain'];
                    }else {
                        $pubResList['pub'][$temp['pubId']]['Domain'] = $temp['Domain'];
                    }
                    $pubResList['pub'][$temp['pubId']]['Manager'] = $temp['Manager'];
                }
            }
        }
        if(!empty($affList) || !empty($programList)){
            $sql = '';
            if(!empty($programList)){
                $sql .= " SELECT wa.`ID` as affId,concat(wa.`Name`,'(',wa.`ShortName`,')') as wfName,pp.ID as programId,pp.Homepage from program pp LEFT JOIN wf_aff wa on pp.AffId = wa.ID where pp.ID in (".implode(",", $programList).") ";
            }
            if(!empty($affList)){
                if($sql !=''){
                    $sql .= " UNION ";
                }
                $sql .= " SELECT wa.`ID` as affId,concat(wa.`Name`,'(',wa.`ShortName`,')') as wfName,NULL as programId,NULL as Homepage from wf_aff wa WHERE wa.ID in (".implode(",", $affList).") ";
            }
            $affProRes = $this->getRows($sql);
            foreach ($affProRes as $temp){
                if($temp['programId']!=null){
                    $affProResList['program'][$temp['programId']]['wfName'] = $temp['wfName'];
                    $affProResList['program'][$temp['programId']]['Homepage'] = $temp['Homepage'];
                }else {
                    $affProResList['aff'][$temp['affId']] = $temp['wfName'];
                }
            }
        }
        if(!empty($storeList)){
            $sql = "select ID,IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,ss.Name,ss.NameOptimized) as storeName from store ss where ss.ID in (".implode(",", $storeList).")";
            $storeRes = $this->getRows($sql);
            foreach ($storeRes as $temp){
                $storeResList[$temp['ID']] = $temp['storeName'];
            }
        }
        //只有monica和nico的账号可以删id为901-1043的数据
        $monicaAndNicoCanDeleteID = range(901, 1043);
        $loop = false;
        if(isset($_SERVER['PHP_AUTH_USER'])){
            $loop = true;
        }
        foreach ($list as $key=>$val){
            if($val['AccountType'] == 'AccountId'){
                $list[$key]['PubAccText'] = isset($pubResList['pubAcc'][$val['AccountId']])?$pubResList['pubAcc'][$val['AccountId']]['Domain']:'';
                $list[$key]['PubText'] = isset($pubResList['pubAcc'][$val['AccountId']])?$pubResList['pubAcc'][$val['AccountId']]['publisherName']:'';
                $list[$key]['Manager'] = isset($pubResList['pubAcc'][$val['AccountId']])?$pubResList['pubAcc'][$val['AccountId']]['Manager']:'';
                $list[$key]['Domain'] = isset($pubResList['pubAcc'][$val['AccountId']])?$pubResList['pubAcc'][$val['AccountId']]['Domain']:'';
            }else {
                $list[$key]['PubText'] = isset($pubResList['pub'][$val['AccountId']])?$pubResList['pub'][$val['AccountId']]['publisherName']:'';
                $list[$key]['Manager'] = isset($pubResList['pub'][$val['AccountId']])?$pubResList['pub'][$val['AccountId']]['Manager']:'';
                $list[$key]['Domain'] = isset($pubResList['pub'][$val['AccountId']])?$pubResList['pub'][$val['AccountId']]['Domain']:'';
                $list[$key]['PubAccText'] = '';
            }
            if($val['ObjType'] == 'Affiliate'){
                $list[$key]['affText'] = isset($affProResList['aff'][$val['ObjId']])?$affProResList['aff'][$val['ObjId']]:'';
                $list[$key]['programText'] = '';
                $list[$key]['storeText'] = '';
            }else if($val['ObjType'] == 'Program'){
                $list[$key]['programText'] = isset($affProResList['program'][$val['ObjId']])?$affProResList['program'][$val['ObjId']]['Homepage']:'';
                $list[$key]['affText'] = isset($affProResList['program'][$val['ObjId']])?$affProResList['program'][$val['ObjId']]['wfName']:'';
                $list[$key]['storeText'] = '';
            }else if($val['ObjType'] == 'Store'){
                $list[$key]['storeText'] = isset($storeResList[$val['ObjId']])?$storeResList[$val['ObjId']]:'';
                $list[$key]['affText'] = '';
                $list[$key]['programText'] = '';
            }
            if($val['Add_Violation_Warning'] == 0){
                $list[$key]['Add_Violation_Warning'] = 'NO';
            }else {
                $list[$key]['Add_Violation_Warning'] = 'YES';
            }
            $list[$key]['CanDelete'] = '1';
            if($loop){
                if($_SERVER['PHP_AUTH_USER']!='monica' && $_SERVER['PHP_AUTH_USER']!='nicolas'){
                    if(in_array($val['ID'], $monicaAndNicoCanDeleteID)){
                        $list[$key]['CanDelete'] = '0';
                    }
                }
            }
        }
        $rs['data'] = $list;
        $rs['num'] = $countNum;
        return $rs;
    }
    
    //获取联盟列表
    function getAffiliateList(){
        $sql = "select concat(wa.`Name`,'(',wa.`ShortName`,')') as Name,ID from wf_aff wa where `IsActive`='YES' order by Name ";
        $list = $this->getRows($sql);
        $rs = array();
        foreach ($list as $val){
            $rs[$val['ID']] = $val['Name'];
        }
        return $rs;
    }
    
    //获取data列表
    function getDataList($name,$page,$type){
        $pageSize = 20;
        switch ($type){
            case "publisher":
                $sql = "set names utf8";
                $this->query($sql);
                $sql = "select count(DISTINCT(p.`ID`)) as total from publisher p where ( p.`Name` LIKE '%{$name}%' OR p.`Email` LIKE '%{$name}%' OR p.`UserName` LIKE '%{$name}%' OR p.`Domain` LIKE '%{$name}%' ) ";// p.`Status` = 'Active' and
                $total = $this->getRow($sql);
                $sql = "select DISTINCT(p.`ID`) AS publisherId,p.Name AS publisherName,p.Email from publisher p where ( p.`Name` LIKE '%{$name}%' OR p.`Email` LIKE '%{$name}%' OR p.`UserName` LIKE '%{$name}%' OR p.`Domain` LIKE '%{$name}%' ) order by LENGTH(publisherName),publisherName limit ".($page-1)*$pageSize.",{$pageSize}";// p.`Status` = 'Active' and
                $result = $this->getRows($sql);
                /* $sql = "select count(1) as total from publisher p where `Status` = 'Active' and p.Name like '%{$name}%' ";
                $total = $this->getRow($sql);
                $sql = "select p.`ID` AS publisherId,p.Name AS publisherName from publisher p where `Status` = 'Active' and p.Name like '%{$name}%' order by LENGTH(publisherName),publisherName limit ".($page-1)*$pageSize.",{$pageSize}";
                $result = $this->getRows($sql); */
                break;
            case "store":
                $sql = "select count(1) as total from store s where s.Name like '%{$name}%' or s.NameOptimized like '%{$name}%'";// s.StoreAffSupport = 'yes' and
                $total = $this->getRow($sql);
                $sql = "select s.`ID` AS storeId,IF(s.NameOptimized='' OR s.NameOptimized IS NULL,s.Name,s.NameOptimized) as storeName,IF(s.StoreAffSupport='NO','(No Partnership)','') as StoreAffSupport from store s where ( s.Name like '%{$name}%' or s.NameOptimized like '%{$name}%' ) order by LENGTH(storeName),storeName limit ".($page-1)*$pageSize.",{$pageSize}";// s.StoreAffSupport = 'yes' and
                $result = $this->getRows($sql);
                break;
            default:
                $total['total'] = 0;
                $result = array();
                break;
        }
        
        $rs['more'] = $total['total'];
        $rs['data'] = $result;
        return $rs;
    }
    function saveBlock($param){
        if($param['BlockType'] == 'AccountId'){
            $insert_d['AccountType'] = 'AccountId';
            $insert_d['AccountId'] = $param['PublisherAccount'];
        }else {
            $insert_d['AccountType'] = 'PublisherId';
            $insert_d['AccountId'] = $param['Publisher'];
        }
        $insert_d['PublisherId'] = $param['Publisher'];
        $insert_d['ObjType'] = $param['BlockBy'];
        if($param['BlockBy'] == 'Affiliate'){
            $insert_d['BlockBy'] = 'Affiliate';
            $insert_d['ObjId'] = $param['Affiliate'];
        }else if($param['BlockBy'] == 'Program'){
            $insert_d['BlockBy'] = 'Merchant';
            $insert_d['ObjId'] = $param['Program'];
        }else {
            $insert_d['BlockBy'] = 'Store';
            $insert_d['ObjId'] = $param['Store'];
        }
        $insert_d['Status'] = 'Active';
        $insert_d['AddTime'] = date('Y-m-d H:i:s');
        $insert_d['AddUser'] = $param['AddUser'];
        $insert_d['Remark'] = $param['Remark'];
        $insert_d['Add_Violation_Warning'] = $param['ViolationWarning'];
        if($this->table("block_relationship")->insert($insert_d)){
            //检测是否要被block
            if($param['ViolationWarning'] == 1){
                $this->blockpub($param);
            }
            $result['code'] = 1;
            $result['msg'] = "success";
        }else {
            $result['code'] = 2;
            $result['msg'] = "save error";
        }
        return $result;
    }
    
    //当block记录一条都不存在时通知publisher
    function notifypub($param){
        $sql = "SELECT * FROM publisher WHERE ID = '".$param['Publisher']."' AND `Status` = 'Active' ";
        $publisher = $this->getRow($sql);
        if(!empty($publisher)){
            if($param['BlockBy'] == 'Store'){
                $sql = "SELECT IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,ss.Name,ss.NameOptimized) AS storeName FROM store ss WHERE ss.ID = '".$param['Store']."'";
                $advertiserName = $this->getRow($sql);
            }else if($param['BlockBy'] == 'Program'){
                $sql = "SELECT IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,ss.Name,ss.NameOptimized) AS storeName FROM store ss LEFT JOIN  r_store_domain rsd ON rsd.StoreId = ss.ID LEFT JOIN r_domain_program rdp ON rdp.DID = rsd.DomainId WHERE rdp.Status = 'Active' AND  rdp.PID = '".$param['Program']."'";
                $advertiserName = $this->getRow($sql);
            }
            if(isset($advertiserName['storeName']) && !empty($advertiserName['storeName'])){
                $kvparam = array(
                    'templateTime'=>date('Y-m-d H:i:s'),
                    'advertiserName'=>$advertiserName['storeName']
                );
                $doc = get_email_template($kvparam,'email_layout/violation_warning.html');
                send_email($publisher['Email'],'PPC Violation Warning',$doc,'Violation Warning');
            }
        }
        return true;
    }
    
    //block publisher
    function blockpub($param){
        $sql = "SELECT a.PublisherId AS pid, b.`Email`, COUNT(1) AS c FROM block_relationship a LEFT JOIN publisher b ON a.`PublisherId` = b.`ID` WHERE a.`Status` = 'Active' AND a.`AddTime` <= '2017-09-30 23:59:59' AND a.PublisherId='".$param['Publisher']."' AND a.`Source` = 'Normal' AND a.Add_Violation_Warning = 1 AND b.ViolationsStatus=0 GROUP BY a.PublisherId HAVING COUNT(1) = 1";
        $checkres = $this->objMysql->getRows($sql,'pid');
        $sql = "SELECT a.PublisherId AS pid, b.`Email`,b.`Level`, COUNT(1) AS c FROM block_relationship a LEFT JOIN publisher b ON a.`PublisherId` = b.`ID` WHERE a.`Status` = 'Active' AND a.`AddTime` > '2017-09-30 23:59:59' AND a.PublisherId='".$param['Publisher']."' AND a.`Source` = 'Normal' AND a.Add_Violation_Warning = 1 AND b.ViolationsStatus=0 GROUP BY a.PublisherId";
        $res = $this->getRow($sql);
        $check = 0;
        if(!empty($res)){
            if($res['c'] >= 1){
                    $check = 1;
            }elseif($res['c'] == 1){
                if(isset($checkres[$res['pid']])){
                    $check = 1;
                }
            }
            if($check == 1){
                //第一次由高级变为低级，第二次才将账户block
                if($res['Level'] == 'TIER0' || $res['Level'] == 'TIER1'){
                    $Account = new Account();
                    $Account->change_publisher_level_status($res['pid'], 'TIER2', 'Active');
                    
                    $update_d['Level'] = 'TIER2';
                    $this->table('publisher')->where('ID = ' . intval($res['pid']))->update($update_d);
                    
                    $kvparam = array(
                        'template_time'=>date('Y-m-d H:i:s')
                    );
                    $doc = get_email_template($kvparam,'email_layout/violation_tier1totier2.html');
                    send_email($res['Email'],'Brandreward Account Notification',$doc,'Violation TIER1 to TIER2');
                    
                    
                }else {
                    $sql = "update publisher set Status='Inactive',ViolationsStatus=1 where ID=".$res['pid'];
                    $this->query($sql);
                    $kvparam = array(
                        'template_time'=>date('Y-m-d H:i:s')
                    );
                    $doc = get_email_template($kvparam,'email_layout/violation.html');
                    send_email($res['Email'],'BRANDREWARD ACCOUNT SUSPENSION NOTICE',$doc,'Violation');
                }
                /* $EmailTo = $res['Email'];
                $BatchName = 'auto_'.date('YmdHis').'_'.$EmailTo.'_signupsucc';
                $time = date('Y-m-d H:i:s');
                $emailUniqueID = $BatchName.'_'.floor(rand(0,999)*10000);
                $subject = 'BRANDREWARD ACCOUNT SUSPENSION NOTICE';
                $MessageName = 'violation';
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
                    "template_time" => $time,
                    "template_baseurl" => 'http://www.brandreward.com/',
                );
                $sql = "update publisher set Status='Inactive',ViolationsStatus=1 where ID=".$res['pid'];
                $this->query($sql);
                $logsql = "insert into publisher_violation_log(`pid`,`email`,`addtime`)VALUES('{$res['pid']}','{$res['Email']}','$time')";
                $this->query($logsql);
                send_bronto_email($email_info); */
            }
//             else {
//                 $this->notifypub($param);
//             }
        }
    }
    //逻辑删除block的publisher
    function deleteBlock($param){
        $update_d['Status'] = 'Inactive';
        $update_d['LastUpdateTime'] = date("Y-m-d H:i:s");
        if($this->table('block_relationship')->where('ID = ' . intval($param['id']))->update($update_d)){
            $result['code'] = 1;
            $result['msg'] = "success";
        }else {
            $result['code'] = 2;
            $result['msg'] = "delete error";
        }
        return $result;
    }
    
    //查询publisher account
    function searchPublisherAccount($param){
        $sql = "SELECT pa.ID,pa.Domain FROM publisher_account pa WHERE pa.PublisherId = ".addslashes($param['publisherId'])."";
        $list = $this->getRows($sql);
        $rs = array();
        foreach ($list as $key=>$val){
            $rs[$key]['ID'] = $val['ID'];
            $rs[$key]['Domain'] = $val['Domain'];
        }
        $result['code'] = 1;
        $result['data'] = $rs;
        return $result;
    }
    
    //查询program
    function searchProgram($param){
        $sql = "set names utf8";
        $this->query($sql);
        $sql = "SELECT p.ID,p.Homepage FROM program p WHERE p.AffId = ".addslashes($param['affId'])."";
        $list = $this->getRows($sql);
        $rs = array();
        foreach ($list as $key=>&$val){
            $rs[$key]['ID'] = $val['ID'];
            $rs[$key]['Homepage'] = $val['Homepage'];
        }
        $result['code'] = 1;
        $result['data'] = $rs;
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
}
