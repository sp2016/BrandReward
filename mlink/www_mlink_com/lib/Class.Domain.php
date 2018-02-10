<?php

class Domain extends LibFactory
{
    //首页上advertise总数
    function getTotalAdvertisesForHomePage($data,$uid){
        $where_str_store=" AND a.SupportType != 'None'";
        $siteType = 'content';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                $sitearr = explode('+',$temp['SiteTypeNew']);
                foreach($sitearr as $k){
                    if($k == '1_e' || $k == '2_e'){
                        $siteType = 'coupon';
                        break;
                    }
                }
            }
            $checksql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
            $checkrow = $this->getRow($checksql);
        }else {
            $checksql = "select sitetype,CategoryId from publisher_detail where PublisherId=$uid";
            $checkrow = $this->getRow($checksql);
            $sitearr = explode('+',$checkrow['sitetype']);
            foreach($sitearr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                    break;
                }
            }
        }

        if($siteType == 'coupon'){
            $where_str_store.=" AND a.SupportType != 'Content' ";
        }else{
            $where_str_store.=" AND a.SupportType != 'Promotion' ";
        }
        $categoryId = trim($checkrow['CategoryId'],", \t\n\r\0\x0B");
        $categoryArr = explode(',',trim($categoryId,','));
        if(!empty($categoryArr)){
            $where_str_store .= " AND (";
            foreach($categoryArr as $cateid)
            {
                $where_str_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
            }
            $where_str_store = rtrim($where_str_store,'OR')." )";
        }
        $sql = "SELECT COUNT(*) as count FROM store a WHERE a.`StoreAffSupport` = 'YES'".$where_str_store;
        $row = $this->objMysql->getFirstRowColumn($sql);
        $count = intval($row);
        return $count;
    }
    
    //page从1开始
    function getDomainListPageNew($search, $page, $page_size = 20){
        
        if(isset($_SESSION['pubAccActiveList']['active'])){
            $site = ' ';
            $siteId = ' ';
            $doaType = '';
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                if($doaType != 'all'){
                    if((stripos($temp["SiteTypeNew"], '1_e') !== false) || (stripos($temp["SiteTypeNew"], '2_e') !== false)){
                        if($doaType == 'content'){
                            $doaType = 'all';
                        }else {
                            $doaType = 'coupon';
                        }
                    }else {
                        if($doaType == 'coupon'){
                            $doaType = 'all';
                        }else {
                            $doaType = 'content';
                        }
                    }
                }
                $site.= "'".$temp['ApiKey']."',";
                $siteId.= "'".$temp['ID']."',";
            }
            $site = rtrim($site,',');
            $siteId = rtrim($siteId,',');
        }else {
            $site = '';
            $siteId = 'NULL';
        }
        
        $where_str_store = $where_str_commissionValue = $where_common = '';
        
        //先查看该publihser的block情况
        $blockSql = "SELECT ID,ObjType,PublisherId,AccountType,AccountId,ObjId FROM block_relationship WHERE (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active') OR (AccountType = 'AccountId' AND  AccountID IN (".$siteId.") AND `Status` = 'Active')";
        $rows_block = $this->getRows($blockSql);
        
        $where_str_store = " a.`StoreAffSupport` = 'YES' ";
        $where_str_store.=" AND a.SupportType != 'None' ";
        //符合支持类型的商家
        if($doaType == 'content'){
            $where_str_store.=" AND doa.SupportType = 'Content' ";
            if(!empty($rows_block)){
                $where_str_commissionValue.=" AND a.SupportType = 'Content' ";
            }else {
                $where_str_commissionValue.=" AND b.SupportType != 'Promotion' ";
            }
        }else if($doaType == 'coupon'){
            $where_str_store.=" AND doa.SupportType = 'Promotion' ";
            if(!empty($rows_block)){
                $where_str_commissionValue.=" AND a.SupportType = 'Promotion' ";
            }else {
                $where_str_commissionValue.=" AND b.SupportType != 'Content' ";
            }
        }

        $blockAffList = $blockProgramList = $blockStoreList = array();
        if(!empty($rows_block)){
            foreach($rows_block as $k=>$v){
                switch($v['ObjType']){
                    case 'Affiliate':
                        if($v['AccountType'] == "PublisherId"){
//                             $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
//                             $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                            $blockAffList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
//                                     $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
//                                     $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                                    $blockAffList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
//                                 $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
//                                 $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                                $blockAffList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                    case 'Program':
                        if($v['AccountType'] == "PublisherId"){
//                             $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
//                             $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                            $blockProgramList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
//                                     $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
//                                     $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                                    $blockProgramList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
//                                 $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
//                                 $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                                $blockProgramList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                    case 'Store':
                        if($v['AccountType'] == "PublisherId"){
//                             $where_str_store .= " AND a.ID != ".$v['ObjId'];
                            $blockStoreList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
//                                     $where_str_store .= " AND a.ID != ".$v['ObjId'];
                                    $blockStoreList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
//                                 $where_str_store .= " AND a.ID != ".$v['ObjId'];
                                $blockStoreList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                }
            }
            if(!empty($blockAffList)){
                $where_str_store .= " AND a.Affids not in ( ".implode($blockAffList, ',')." ) ";
                $where_str_commissionValue .= " AND b.Affid not in ( ".implode($blockAffList, ',')." ) ";
            }
            if(!empty($blockProgramList)){
                $where_str_store .= " AND a.Programids not in ( ".implode($blockProgramList, ',')." ) ";
                $where_str_commissionValue .= " AND b.Programid not in ( ".implode($blockProgramList, ',')." ) ";
            }
            if(!empty($blockStoreList)){
                $where_str_store .= " AND a.ID not in ( ".implode($blockStoreList, ',')." ) ";
            }
        }
        
        if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
            $where_common .= ' AND (a.NameOptimized LIKE "'.addslashes(trim($search['store_keywords'])).'%" OR a.Name LIKE "'.addslashes(trim($search['store_keywords'])).'%")';
        }
        if(isset($search['domain']) && !empty($search['domain'])){
            $search['domain'] = preg_replace('/\s/','',$search['domain']);
            $where_common .= ' AND (a.Domains LIKE "%'.addslashes(trim($search['domain'])).'%")';
        }
        if (isset($search['country']) && !empty($search['country'])){
            if(strtolower($search['country']) == 'uk' || strtolower($search['country']) == 'gb'){
                $where_common .= " AND ( FIND_IN_SET('UK',a.CountryCode) OR FIND_IN_SET('GB',a.CountryCode) )";
            }else {
                $where_common.=' AND FIND_IN_SET("'.addslashes($search['country']).'",a.CountryCode) ';
            }
        }
        if(isset($search['collect']) && !empty($search['collect'])){
            $where_common.=' AND a.ID IN(select sid from publisher_collect where uid = '.$_SESSION['u']['ID'].')';
        }
        
        if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
            $categoryArr = array();
        }else{
            //该publisher属于德国或法国，category不加限制
            $sql = "SELECT Country FROM publisher WHERE ID = ".$_SESSION['u']['ID'];
            $rs = $this->getRow($sql);
            if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
                $categoryArr = array();
            }else {
                $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
                $res = $this->getRow($sql);
                $categoryId = trim($res['CategoryId'],", \t\n\r\0\x0B");
                $categoryArr = explode(',',trim($categoryId,','));
            }
        }
        if(isset($search['categories']) && !empty($search['categories'])){
            $category_search = explode(',',trim($search['categories'],", \t\n\r\0\x0B"));
            if(!empty($categoryArr)){
                $categoryArr = array_intersect($categoryArr,$category_search);
            }
            else{
                $categoryArr = $category_search;
            }
        }
        //如果选择了category，则筛选条件为公共的，如果没有选择category则筛选条件只针对自身能看到数据的部分
        if(isset($search['categories']) && !empty($search['categories'])){
            if(!empty($categoryArr))
            {
                $where_common .= " AND (";
                foreach($categoryArr as $cateid)
                {
                    $where_common .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
                }
                $where_common = rtrim($where_common,'OR')." )";
            }elseif(!isset($search['store_keywords']) || empty($search['store_keywords'])){
                if(isset($rs['Country']) && ($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE)){
        
                }else {
                    $where_common .= ' AND 0=1';
                }
            }
        }else {
            if(!empty($categoryArr))
            {
                $where_str_store .= " AND (";
                foreach($categoryArr as $cateid)
                {
                    $where_str_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
                }
                $where_str_store = rtrim($where_str_store,'OR')." )";
            }elseif(!isset($search['store_keywords']) || empty($search['store_keywords'])){
                if(isset($rs['Country']) && ($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE)){
        
                }else {
                    $where_str_store .= ' AND 0=1';
                }
            }
        }
        $where_common .= ' AND a.IsAffiliate = 0';
        
        if(isset($search['advertiserType']) && !empty($search['advertiserType'])){
            if($search['advertiserType'] == 'Content'){
                $where_common .= " AND a.SupportType <> 'Promotion'";
            }else if($search['advertiserType'] == 'Promotion'){
                $where_common .= " AND a.SupportType <> 'Content'";
            }
        }
        
        //查询有金额的所有的商家数据
        $sql = "select distinct(rsd.`StoreId`) as StoreId from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId left join store a on a.ID = rsd.`StoreId` where site IN ( $site ) and objtype = 'domain' ".$where_common." group by rsd.`StoreId` HAVING sum(pd.showrevenues) > 0 ";
        $row_has_commission_store = $this->objMysql->getRows($sql,'StoreId');
        $notInStoreId = array_keys($row_has_commission_store);
        $hasCommissionNum = count($notInStoreId);//45
        if($hasCommissionNum > 0){
            $where_str_store .= " AND a.`ID` not in ( ".implode($notInStoreId, ',')." ) ";
        }
        //当前页时最多展示的数量
        $nowMaxCount = $page*$page_size;//60
        if($hasCommissionNum>=$nowMaxCount){
            $hasCommissionLastNum = 0;
            $hasCommissionlimit = " LIMIT ".($page - 1)*$page_size.','.$page_size;
        }else if(($hasCommissionNum+$page_size)>=$nowMaxCount){
            $hasCommissionLastNum = $nowMaxCount-$hasCommissionNum;
            $hasCommissionlimit = " LIMIT ".($page - 1)*$page_size.','.$hasCommissionLastNum;
        }else {
            $hasCommissionLastNum = $nowMaxCount-$hasCommissionNum;
            $hasCommissionlimit = "";
        }
        $row_has_commission = $row = array();
        if($hasCommissionlimit != ""){
            $sql = "select rsd.`StoreId` as StoreId, a.LogoName, a.NameOptimized, a.Domains,a.SupportType,a.`StoreAffSupport`,a.Affids,a.Programids,IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS storeName,a.LogoStatus,IF(a.PPCStatus='PPCAllowed','Allowed','Restricted') AS PPC, SUM(pd.showrevenues) AS Commission, SUM(pd.clicks) AS Clicks,SUM(pd.clicks_robot) AS robotClicks from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId left join store a on a.ID = rsd.`StoreId` where site IN ( $site ) and objtype = 'domain' ".$where_common." group by rsd.`StoreId` HAVING Commission > 0 ORDER BY Commission DESC , CLicks DESC , storeName ASC ".$hasCommissionlimit." ";
            $row_has_commission = $this->objMysql->getRows($sql,'StoreId');
        }

        //把数量算出来
        $sql_count = "SELECT count(distinct(a.ID)) as c FROM domain_outgoing_all doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid LEFT JOIN store a ON a.`ID` = rsd.`StoreId` WHERE ".$where_str_store." ".$where_common." ";
        $row_count = $this->objMysql->getFirstRowColumn($sql_count);
        $count = intval($row_count)+$hasCommissionNum;
        if($hasCommissionLastNum != 0){
            //剩余应该显示多少条
            if($page_size>=$hasCommissionLastNum){
                $rowlimit = " LIMIT 0,".$hasCommissionLastNum;
            }else {
                $rowlimit = " LIMIT ".(($page - 1)*$page_size-$hasCommissionNum).','.$page_size;
            }
            $sql = "SELECT rsd.`StoreId` AS StoreId, a.LogoName, a.NameOptimized, a.Domains,a.SupportType,a.`StoreAffSupport`,a.Affids,a.Programids, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS storeName, a.LogoStatus,IF(a.PPCStatus='PPCAllowed','Allowed','Restricted') AS PPC FROM domain_outgoing_all doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid LEFT JOIN store a ON a.`ID` = rsd.`StoreId` WHERE ".$where_str_store." ".$where_common." GROUP BY rsd.`StoreId` ORDER BY storeName ASC ".$rowlimit." ";
            $row = $this->objMysql->getRows($sql,'StoreId');
        }
        //将返回的数据拼起来
        if(!empty($row_has_commission)){
            $row = array_merge($row_has_commission,$row);
        }
        
        //历史数据拿出来
        $historyStoreId = array();
        if(!empty($rows_block)){
            foreach ($row as $key=>$temp){
                if($temp['StoreAffSupport'] != 'YES'){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
                if($doaType=='coupon'){
                    if($temp['SupportType'] == 'Content'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }else if($doaType=='content'){
                    if($temp['SupportType'] == 'Promotion'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }
                if(in_array($temp['Affids'], $blockAffList) || in_array($temp['Programids'], $blockProgramList) || in_array($temp['StoreId'], $blockStoreList)){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
            }
        }else {
            foreach ($row as $key=>$temp){
                if($temp['StoreAffSupport'] != 'YES'){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
                if($doaType=='coupon'){
                    if($temp['SupportType'] == 'Content'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }else if($doaType=='content'){
                    if($temp['SupportType'] == 'Promotion'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }
            }
        }
        //算出最大的commission value
        $storeIdList = array();
        if(!empty($row)){
            foreach ($row as $val){
                if(!in_array($val['StoreId'], $historyStoreId)){
                    $storeIdList[] = $val['StoreId'];
                }
            }
        }
        if(!empty($storeIdList)){
            $storeIdtext = implode($storeIdList, ',');
            if(!empty($rows_block)){
                $sql = " SELECT * FROM ( SELECT rsd.StoreId,a.DID,a.Site,a.DefaultOrder,a.Key,b.`ProgramId`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue`,b.`ShippingCountry` FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` LEFT JOIN r_store_domain AS rsd ON a.did = rsd.domainid WHERE rsd.storeid in ($storeIdtext) AND b.`IsActive` = 'Active' ".$where_str_commissionValue." ORDER BY a.did,a.`Site`,a.`DefaultOrder` ) AS temp GROUP BY temp.did,temp.`Site` ";
                $rs =  $this->objMysql->getRows($sql);
            }else {
                $where_str_commissionValue = " AND b.SupportType != 'None'";
                $sql = 'SELECT rsp.`StoreId`,rsp.`ProgramId`,rsp.`Outbound`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue` from r_store_program rsp
                 LEFT JOIN program_intell b on b.`ProgramId` = rsp.`ProgramId` WHERE rsp.`Outbound` != "" and rsp.`StoreId` in ('.$storeIdtext.')'.$where_str_commissionValue;
                $rs =  $this->objMysql->getRows($sql);
            }
            $result = array();
            $commissionRangeArr = array();
            foreach ($rs as $val){
                if($val['CommissionValue'] != '' && $val['CommissionValue'] != null){
                    $commissionArr = explode("|", $val['CommissionValue'])[0];
                    $commissionValText = trim($commissionArr,"[]");
                    $commissionValArr = explode(",", $commissionValText);
                    foreach ($commissionValArr as $temp){
                        preg_match("/\d+(\.\d+)?/", $temp,$number);
                        $unit = preg_replace("/[0-9. ]/",'', $temp);
                        $commissionRangeArr[$val['StoreId']][$unit][number_format($number[0],3)] = $temp;
                    }
                }else {
                    if($val['CommissionUsed'] == '0'){
        
                    }else if($val['CommissionType'] == 'Value'){
                        if($val['CommissionCurrency'] != ''){
                            $commissionRangeArr[$val['StoreId']][$val['CommissionCurrency']][number_format($val['CommissionUsed'],3)] = $val['CommissionCurrency'].$val['CommissionUsed'];
                        }else{
                            $commissionRangeArr[$val['StoreId']]['USD'][number_format($val['CommissionUsed'],3)] = "USD".$val['CommissionUsed'];
                        }
                    }else{
                        $commissionRangeArr[$val['StoreId']]['%'][number_format($val['CommissionUsed'],3)] = $val['CommissionUsed'].'%';
                    }
                }
            }
        
            foreach ($row as $key=>$val){
                if(isset($commissionRangeArr[$val['StoreId']])){
                    $val['CommissionRange'] = '';
                    foreach ($commissionRangeArr[$val['StoreId']] as $tempK=>$tempV){
                        ksort($tempV);
                        if(count($tempV)<=1){
                            $val['CommissionRange'] .= ','.current($tempV);
                        }else {
                            $val['CommissionRange'] .= ','.current($tempV).'~'.end($tempV);
                        }
                    }
                    if($val['CommissionRange'] != ''){
                        $row[$key]['CommissionRange'] = trim($val['CommissionRange'],',');
                    }else {
                        $row[$key]['CommissionRange'] = 'other';
                    }
                }else {
                    $row[$key]['CommissionRange'] = 'other';
                }
                $row[$key]['historyStore'] = 'no';
            }
        }
        
        if (isset($search['store_keywords']) && $search['store_keywords']){
            $where_arr = array();
            $where_arr[] = "c.Keywords like '".addslashes($search['store_keywords'])."%'";
            $where_arr[] = " b.SupportType != 'None'";
            if($doaType == 'content'){
                $where_arr[] =" b.SupportType != 'Promotion' ";
            }else if($doaType == 'coupon'){
                $where_arr[] =" b.SupportType != 'Content' ";
            }
        
            if (isset($search['country']) && !empty($search['country']))
                $where_arr[] = ' FIND_IN_SET("'.addslashes($search['country']).'",b.CountryCode) ';
        
            if(isset($search['domain']) && !empty($search['domain'])){
                $where_arr[] = "c.ID = 0";
            }
            if(!empty($categoryArr)) {
                # for keywords store category
                $categoryId = implode(',',array_unique($categoryArr));
                $where_arr[] = "c.CategoryId IN ($categoryId)";
                # for recommend store category
                $where_category = " (";
                foreach($categoryArr as $cateid)
                {
                    $where_category .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
                }
                $where_category = rtrim($where_category,'OR').")";
                $where_arr[] = $where_category;
            }
            $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
            $sql = "SELECT COUNT(DISTINCT c.`Keywords`) as c FROM store_multi_brand AS c LEFT JOIN store AS b ON c.`StoreId` = b.`ID` ".$where_str;
            $rows_multi_count = $this->getRow($sql);
            $multi_count = $rows_multi_count['c'];
            $totalStore = $count;
            $count = $count + $multi_count;
            if(count($row)<$page_size){
                //剩下应该显示多少条
                $multi_num = $page_size - count($row);
                $sql = "SELECT c.StoreId,c.Keywords,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) as StoreName,b.ID FROM store_multi_brand AS c LEFT JOIN store AS b ON c.`StoreId` = b.`ID` ".$where_str."  GROUP BY c.StoreId,c.Keywords";
                $rows_multi = $this->getRows($sql);
                $multi_data = array();
                $sort_name = array();
                foreach($rows_multi as $k=>$v){
                    $Keywords = strtolower($v['Keywords']);
                    if(!isset($multi_data[$Keywords])){
                        $sort_name[] = $Keywords;
                        $multi_data[$Keywords]['storeName'] = $Keywords;
                        $multi_data[$Keywords]['Store'][] = array('StoreName'=>$v['StoreName'],'StoreId'=>$v['StoreId'],'ID'=>$v['ID']);
                        $multi_data[$Keywords]['Type'] = 'multi';
                    }else{
                        $multi_data[$Keywords]['Store'][] = array('StoreName'=>$v['StoreName'],'StoreId'=>$v['StoreId'],'ID'=>$v['ID']);
                    }
                }
                array_multisort($sort_name,SORT_ASC,$multi_data);
                //从第几条开始截取
                $start = ($page-1)*$page_size-$totalStore>0?($page-1)*$page_size-$totalStore:0;
                $multi_data = array_slice($multi_data,$start,$multi_num);
                $row = array_merge($row,$multi_data);
            }
        }
        
        //找总共有多少条content feed
        if(!empty($row)){
            $storeIdList = array();
            foreach ($row as $v){
                if(isset($v['StoreId'])){
                    if(!in_array($v['StoreId'], $historyStoreId)){
                        $storeIdList[] = $v['StoreId'];
                    }
                }
            }
            $storeIds = implode($storeIdList, ',');
        
            $merchant = new MerchantExt();
            $storeCount = $merchant->GetContentNew(array("storeIds"=>$storeIds),1,1,$search['uid'],false,true);
            $storeCountArray = array();
            foreach ($storeCount as $store){
                $storeCountArray[$store['StoreId']] = $store['StoreIdCount'];
            }
            foreach ($row as $key=>$val){
                if(!in_array($val['StoreId'], $historyStoreId)){
                    if(isset($storeCountArray[$val['StoreId']])){
                        $row[$key]['StoreCount'] = $storeCountArray[$val['StoreId']];
                    }else {
                        $row[$key]['StoreCount'] = 0;
                    }
                    $row[$key]['historyStore'] = 'no';
                }else {
                    $row[$key]['StoreCount'] = '/';
                    $row[$key]['CommissionRange'] = '/';
                    $row[$key]['PPC'] = '/';
                    $row[$key]['historyStore'] = 'yes';
                }
            }
        }
        $return_d = array();
        $return_d['page_total'] = ceil($count / $page_size);
        $return_d['page_now'] = $page;
        $return_d['total_num'] = $count;
        $return_d['data'] = $row;
        return $return_d;
    }
    
    function getDomainListPage($search, $page, $page_size = 20)
    {
        $blockSql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($blockSql);
        $where_str_store = $where_str_commissionValue = $where_history_store = $where_common = '';
        $siteType = 'content';
        $doaType = '';
        if(isset($_SESSION['pubAccActiveList']['active'])){
            $site = ' site IN(';
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
                $site.= "'".$temp['ApiKey']."',";
            }
            $site = rtrim($site,',').')';
        }else {
            $checksql = "select sitetype from publisher_detail where PublisherId='{$search['uid']}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                    break;
                }
            }
            
            /* $sitesql = 'select ApiKey from publisher_account where publisherId='.$search['uid'];
            $siterow = $this->getRows($sitesql); */
            
            $siterow = array();
            $i = 0;
            foreach ($_SESSION['pubAccList'] as $temp){
                $siterow[$i]['ApiKey'] = $temp['ApiKey'];
                $i++;
            }
            $site = ' site IN(';
            foreach($siterow as $k){
                $site.= "'".$k['ApiKey']."',";
            }
            $site = rtrim($site,',').')';
        }
        
        if(empty($rows_block)){
            $where_str_store.=" AND a.SupportType != 'None'";
            $where_str_commissionValue = " AND b.SupportType != 'None'";
        }
        if($siteType == 'coupon'){
            if(!empty($rows_block)){
//                 $where_str_store.=" AND doa.SupportType != 'Content' ";
//                 $where_str_commissionValue.=" AND a.SupportType != 'Content' ";
            }else {
                $where_str_store.=" AND a.SupportType != 'Content' ";
                $where_str_commissionValue.=" AND b.SupportType != 'Content' ";
            }
        }else{
            if(!empty($rows_block)){
//                 $where_str_store.=" AND doa.SupportType != 'Promotion' ";
//                 $where_str_commissionValue.=" AND a.SupportType != 'Promotion' ";
            }else {
                $where_str_store.=" AND a.SupportType != 'Promotion' ";
                $where_str_commissionValue.=" AND b.SupportType != 'Promotion' ";
            }
        }
        
        if($doaType == 'content'){
            if(!empty($rows_block)){
                $where_str_store.=" AND doa.SupportType = 'Content' ";
                $where_str_commissionValue.=" AND a.SupportType = 'Content' ";
            }
        }else if($doaType == 'coupon'){
            if(!empty($rows_block)){
                $where_str_store.=" AND doa.SupportType = 'Promotion' ";
                $where_str_commissionValue.=" AND a.SupportType = 'Promotion' ";
            }
        }
        
        if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
//             $where_str_store .= ' AND (a.NameOptimized LIKE "'.addslashes(trim($search['store_keywords'])).'%" OR a.Name LIKE "'.addslashes(trim($search['store_keywords'])).'%")';
//             $where_history_store .= ' AND (a.NameOptimized LIKE "'.addslashes(trim($search['store_keywords'])).'%" OR a.Name LIKE "'.addslashes(trim($search['store_keywords'])).'%")';
            $where_common .= ' AND (a.NameOptimized LIKE "'.addslashes(trim($search['store_keywords'])).'%" OR a.Name LIKE "'.addslashes(trim($search['store_keywords'])).'%")';
        }
        if(isset($search['domain']) && !empty($search['domain'])){
            $search['domain'] = preg_replace('/\s/','',$search['domain']);
//             $where_str_store .= ' AND (a.Domains LIKE "%'.addslashes(trim($search['domain'])).'%")';
//             $where_history_store .= ' AND (a.Domains LIKE "%'.addslashes(trim($search['domain'])).'%")';
            $where_common .= ' AND (a.Domains LIKE "%'.addslashes(trim($search['domain'])).'%")';
        }
        if (isset($search['country']) && !empty($search['country'])){
            if(strtolower($search['country']) == 'uk' || strtolower($search['country']) == 'gb'){
//                 $where_str_store .= " AND ( FIND_IN_SET('UK',a.CountryCode) OR FIND_IN_SET('GB',a.CountryCode) )";
//                 $where_history_store .= " AND ( FIND_IN_SET('UK',a.CountryCode) OR FIND_IN_SET('GB',a.CountryCode) )";
                $where_common .= " AND ( FIND_IN_SET('UK',a.CountryCode) OR FIND_IN_SET('GB',a.CountryCode) )";
            }else {
//                 $where_str_store.=' AND FIND_IN_SET("'.addslashes($search['country']).'",a.CountryCode) ';
//                 $where_history_store.=' AND FIND_IN_SET("'.addslashes($search['country']).'",a.CountryCode) ';
                $where_common.=' AND FIND_IN_SET("'.addslashes($search['country']).'",a.CountryCode) ';
            }
        }
        if(isset($search['collect']) && !empty($search['collect'])){
//             $where_str_store.=' AND a.ID IN(select sid from publisher_collect where uid = '.$_SESSION['u']['ID'].')';
//             $where_history_store.=' AND a.ID IN(select sid from publisher_collect where uid = '.$_SESSION['u']['ID'].')';
            $where_common.=' AND a.ID IN(select sid from publisher_collect where uid = '.$_SESSION['u']['ID'].')';
        }
        
        if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
            $categoryArr = array();
        }else{
            //该publisher属于德国或法国，category不加限制
            $sql = "SELECT Country FROM publisher WHERE ID = ".$_SESSION['u']['ID'];
            $rs = $this->getRow($sql);
            if($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE){
                $categoryArr = array();
            }else {
                $sql = "SELECT CategoryId FROM publisher_detail WHERE PublisherId = ".$_SESSION['u']['ID'];
                $res = $this->getRow($sql);
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
        
        if(isset($search['categories']) && !empty($search['categories'])){
            if(!empty($categoryArr))
            {
    //             $where_str_store .= " AND (";
    //             $where_history_store .= " AND (";
                $where_common .= " AND (";
                foreach($categoryArr as $cateid)
                {
    //                 $where_str_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
    //                 $where_history_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
                    $where_common .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
                }
    //             $where_str_store = rtrim($where_str_store,'OR')." )";
    //             $where_history_store = rtrim($where_history_store,'OR')." )";
                $where_common = rtrim($where_common,'OR')." )";
            }elseif(!isset($search['store_keywords']) || empty($search['store_keywords'])){
                if(isset($rs['Country']) && ($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE)){
            
                }else {
    //                 $where_str_store .= ' AND 0=1';
    //                 $where_history_store .= ' AND 0=1';
                    $where_common .= ' AND 0=1';
                }
            }
        }else {
            if(!empty($categoryArr))
            {
                $where_str_store .= " AND (";
                foreach($categoryArr as $cateid)
                {
                    $where_str_store .= " FIND_IN_SET('$cateid',a.CategoryId) OR";
                }
                $where_str_store = rtrim($where_str_store,'OR')." )";
            }elseif(!isset($search['store_keywords']) || empty($search['store_keywords'])){
                if(isset($rs['Country']) && ($rs['Country'] == Constant::COUNTRY_ID_GERMANY || $rs['Country'] == Constant::COUNTRY_ID_FRANCE)){
            
                }else {
                    $where_str_store .= ' AND 0=1';
                }
            }
        }
        
        $where_common .= ' AND a.IsAffiliate = 0';
        
        #filter for block
        /* $sql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($sql); */
        $blockAffList = $blockProgramList = $blockStoreList = array();
        if(!empty($rows_block)){
            foreach($rows_block as $k=>$v){
                switch($v['ObjType']){
                    case 'Affiliate':
                        if($v['AccountType'] == "PublisherId"){
                            $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
                            $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                            $blockAffList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
                                    $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                                    $blockAffList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
                                $where_str_store .= " AND a.Affids != '".$v['ObjId']."'";
                                $where_str_commissionValue .= " AND b.Affid != '".$v['ObjId']."'";
                                $blockAffList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                    case 'Program':
                        if($v['AccountType'] == "PublisherId"){
                            $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
                            $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                            $blockProgramList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
                                    $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                                    $blockProgramList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
                                $where_str_store .= " AND a.Programids != '".$v['ObjId']."'";
                                $where_str_commissionValue .= " AND b.Programid != '".$v['ObjId']."'";
                                $blockProgramList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                    case 'Store':
                        if($v['AccountType'] == "PublisherId"){
                            $where_str_store .= " AND a.ID != ".$v['ObjId'];
                            $blockStoreList[$v['ObjId']] = $v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where_str_store .= " AND a.ID != ".$v['ObjId'];
                                    $blockStoreList[$v['ObjId']] = $v['ObjId'];
                                }
                            }else {
                                $where_str_store .= " AND a.ID != ".$v['ObjId'];
                                $blockStoreList[$v['ObjId']] = $v['ObjId'];
                            }
                        }
                        break;
                }
            }
            if($siteType == 'coupon'){
//                 $where_str_store .= " AND a.SupportType != 'Mixed' ";
            }
        }
//         $where_history_store .= ' AND b.Click > 0 ';
        
        $sqlDomain = "SELECT DISTINCT(objid) FROM publisher_data WHERE $site and objtype = 'domain'";
        $rowDomain = $this->objMysql->getRows($sqlDomain);
        $domainidList = array();
        foreach ($rowDomain as $temp){
            $domainidList[] = $temp['objid'];
        }
        if(!empty($domainidList)){
            $domainIdText = implode($domainidList, ",");
            if(!empty($rows_block)){
                $domainWhere = " doa.DID in ( $domainIdText ) ";
            }else {
                $domainWhere = " b.domainId in ( $domainIdText ) ";
            }
        }else {
            $domainWhere = " 0=1 ";
        }
        
        if(isset($search['advertiserType']) && !empty($search['advertiserType'])){
            if($search['advertiserType'] == 'Content'){
                $where_common .= " AND a.SupportType <> 'Promotion'";
            }else if($search['advertiserType'] == 'Promotion'){
                $where_common .= " AND a.SupportType <> 'Content'";
            }
        }
        
        
        //查看七天内所有commission rate有增长的商家
        $riseWhereSql = '';
        if(!empty($rows_block)){
            $innerRiseWhereSql=" AND a.`SupportType` != 'None' ";
            if($siteType == 'coupon'){
                $innerRiseWhereSql.=" AND a.`SupportType` = 'Promotion' ";
            }else{
                $innerRiseWhereSql.=" AND a.`SupportType` = 'Content' ";
            }
            $InnerJoinSql=" INNER JOIN ( SELECT * FROM (SELECT a.DID, a.Site, a.DefaultOrder, b.`ProgramId` AS PID,a.SupportType FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` WHERE b.`IsActive` = 'Active' ".$where_str_commissionValue." ".$innerRiseWhereSql." ORDER BY a.did, a.`Site`, a.`DefaultOrder`) AS ddd GROUP BY ddd.did, ddd.`Site` ) doatemp on doatemp.`PID` = pccl.`ProgramId` ";
        }else {
            $riseWhereSql .= " AND rsp.`Outbound` <> '' ";
            $riseWhereSql .= " AND pi.`SupportType` != 'None' ";
            if($siteType == 'coupon'){
                $riseWhereSql .= " AND pi.`SupportType` != 'Content' ";
            }else{
                $riseWhereSql .= " AND pi.`SupportType` != 'Promotion' ";
            }
            $InnerJoinSql=" INNER JOIN r_store_program rsp ON pccl.`ProgramId` = rsp.`ProgramId` INNER JOIN program_intell pi on pi.`ProgramId` = pccl.`ProgramId` ";
        }
        if(isset($search['showCommissionRise']) && $search['showCommissionRise'] == 1){
            $showCommissionRise = true;
        }else {
            $showCommissionRise = false;
        }
        $showCommissionRise = false;
        if($showCommissionRise){
            $riseSelect = " ,IF(changeTable.CommissionChangeValue > 0,'1','0') as CommissionImprove ";
            $riseSql = " left join ( SELECT * FROM ( SELECT pccl.* FROM `program_commission_change_log` pccl ".$InnerJoinSql." WHERE pccl.`AddTime` >= '".date('Y-m-d',strtotime('-7 days'))."' ".$riseWhereSql."  ORDER BY pccl.`StoreId`,pccl.`ProgramId`,pccl.`AddTime` DESC,pccl.`CommissionChangeValue` DESC ) temp GROUP BY temp.`StoreId` HAVING CommissionChangeValue > 0 ) changeTable on changeTable.`StoreId` = a.`ID` ";
            $where_common .= " AND changeTable.CommissionChangeValue > 0 ";
        }else {
            $riseSelect = " ,0 as CommissionImprove ";
            $riseSql = " ";
        }
        
        if(!empty($rows_block)){
            $andwhere = " or ( $domainWhere AND b.Click > 0  ) ";
            $sql = "SELECT count(distinct(a.ID)) as c FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid left join store as a on rsd.storeid = a.id LEFT JOIN (select rsd.`StoreId`,pd.objId as domainId, SUM(showrevenues) AS Commission, SUM(clicks) AS Click,SUM(clicks_robot) AS robotClick from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId where $site and objtype = 'domain' group by StoreId) b ON a.`ID` = b.StoreId ".$riseSql." WHERE ( ( a.`StoreAffSupport` = 'YES'".$where_str_store." ) ".$andwhere." ) ".$where_common;
            $row = $this->objMysql->getFirstRowColumn($sql);
            $count = intval($row);
            $sql = "SELECT rsd.StoreId AS StoreId, a.LogoName, a.NameOptimized, a.Domains,a.SupportType,doa.`ID`,a.`StoreAffSupport`,a.Affids,a.Programids, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS storeName, IFNULL(b.Commission, 0) as Commission, IFNULL(b.Click, 0) as Clicks,IFNULL(b.robotClick, 0) as robotClicks,a.LogoStatus,IF(a.PPCStatus='PPCAllowed','Allowed','Restricted') AS PPC ".$riseSelect." FROM domain_outgoing_all AS doa LEFT JOIN r_store_domain AS rsd ON doa.did = rsd.domainid left join store as a on rsd.storeid = a.id LEFT JOIN (select rsd.`StoreId`,pd.objId as domainId, SUM(showrevenues) AS Commission, SUM(clicks) AS Click,SUM(clicks_robot) AS robotClick from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId where $site and objtype = 'domain' group by StoreId) b ON a.`ID` = b.StoreId ".$riseSql." WHERE ( ( a.`StoreAffSupport` = 'YES'".$where_str_store." ) $andwhere ) $where_common GROUP BY rsd.storeid ORDER BY Commission DESC,Clicks DESC,storeName ASC LIMIT ". ($page - 1) * $page_size . ',' . $page_size;
            $row = $this->objMysql->getRows($sql);
        }else {
            $andwhere = " or ( $domainWhere AND b.Click > 0  ) ";
            $sql = "SELECT COUNT(*) as c FROM store a LEFT JOIN (select rsd.`StoreId`,pd.objId as domainId, SUM(showrevenues) AS Commission, SUM(clicks) AS Click,SUM(clicks_robot) AS robotClick from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId where $site and objtype = 'domain' group by StoreId) b ON a.`ID` = b.StoreId ".$riseSql." WHERE ( ( a.`StoreAffSupport` = 'YES'".$where_str_store." ) ".$andwhere." ) ".$where_common;
            $row = $this->objMysql->getFirstRowColumn($sql);
            $count = intval($row);
            $sql = "SELECT a.ID AS StoreId, a.LogoName, a.NameOptimized, a.Domains,a.SupportType,a.`StoreAffSupport`,a.Affids,a.Programids, IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS storeName, IFNULL(b.Commission, 0) as Commission, IFNULL(b.Click, 0) as Clicks,IFNULL(b.robotClick, 0) as robotClicks,a.LogoStatus,IF(a.PPCStatus='PPCAllowed','Allowed','Restricted') AS PPC ".$riseSelect." FROM store a LEFT JOIN (select rsd.`StoreId`,pd.objId as domainId, SUM(showrevenues) AS Commission, SUM(clicks) AS Click,SUM(clicks_robot) AS robotClick from `publisher_data` pd left join r_store_domain rsd on rsd.`DomainId` = pd.objId where $site and objtype = 'domain' group by StoreId) b ON a.`ID` = b.StoreId ".$riseSql." WHERE ( ( a.`StoreAffSupport` = 'YES' $where_str_store ) $andwhere ) $where_common ORDER BY Commission DESC,Clicks DESC,storeName ASC LIMIT ". ($page - 1) * $page_size . ',' . $page_size;
            $row = $this->objMysql->getRows($sql);
        }
        $historyStoreId = array();
        if(!empty($rows_block)){
            foreach ($row as $key=>$temp){
                if($temp['StoreAffSupport'] != 'YES'){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
                if($siteType == 'coupon'){
                    if($temp['SupportType'] == 'Content'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }else {
                    if($temp['SupportType'] == 'Promotion'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }
                if(in_array($temp['Affids'], $blockAffList) || in_array($temp['Programids'], $blockProgramList) || in_array($temp['StoreId'], $blockStoreList)){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
                
               
            }
        }else {
            foreach ($row as $key=>$temp){
                if($temp['StoreAffSupport'] != 'YES'){
                    $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                    continue;
                }
                if($siteType == 'coupon'){
                    if($temp['SupportType'] == 'Content'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }else {
                    if($temp['SupportType'] == 'Promotion'){
                        $historyStoreId[$temp['StoreId']] = $temp['StoreId'];
                        continue;
                    }
                }
            }
        }
        
        //算出最大的commission value
        $storeIdList = array();
        foreach ($row as $val){
            if(!in_array($val['StoreId'], $historyStoreId)){
                $storeIdList[] = $val['StoreId'];
            }
        }
        
        if(!empty($storeIdList)){
            $storeIdtext = implode($storeIdList, ',');
            
            if(!empty($rows_block)){
//                 $sql = "SELECT rsd.StoreId,a.DID,a.Site,MIN(a.DefaultOrder),a.Key,b.`ProgramId`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue`,b.`ShippingCountry` FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` LEFT JOIN r_store_domain AS rsd ON a.did = rsd.domainid WHERE rsd.storeid in ($storeIdtext) AND b.`IsActive` = 'Active' ".$where_str_commissionValue." GROUP BY a.did,a.site";
                $sql = " SELECT * FROM ( SELECT rsd.StoreId,a.DID,a.Site,a.DefaultOrder,a.Key,b.`ProgramId`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue`,b.`ShippingCountry` FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` LEFT JOIN r_store_domain AS rsd ON a.did = rsd.domainid WHERE rsd.storeid in ($storeIdtext) AND b.`IsActive` = 'Active' ".$where_str_commissionValue." ORDER BY a.did,a.`Site`,a.`DefaultOrder` ) AS temp GROUP BY temp.did,temp.`Site` ";
                $rs =  $this->objMysql->getRows($sql);
            }else {
                $sql = 'SELECT rsp.`StoreId`,rsp.`ProgramId`,rsp.`Outbound`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue` from r_store_program rsp
                 LEFT JOIN program_intell b on b.`ProgramId` = rsp.`ProgramId` WHERE rsp.`Outbound` != "" and rsp.`StoreId` in ('.$storeIdtext.')'.$where_str_commissionValue;
                 $rs =  $this->objMysql->getRows($sql);
            }
            $result = array();
            $commissionRangeArr = array();
            foreach ($rs as $val){
                if($val['CommissionValue'] != '' && $val['CommissionValue'] != null){
                    $commissionArr = explode("|", $val['CommissionValue'])[0];
                    $commissionValText = trim($commissionArr,"[]");
                    $commissionValArr = explode(",", $commissionValText);
                    foreach ($commissionValArr as $temp){
                        preg_match("/\d+(\.\d+)?/", $temp,$number);
                        $unit = preg_replace("/[0-9. ]/",'', $temp);
                        $commissionRangeArr[$val['StoreId']][$unit][number_format($number[0],3)] = $temp;
                    }
                }else {
                    if($val['CommissionUsed'] == '0'){
//                         $commissionRangeArr[$val['StoreId']]['value'] = 'other';
                    }else if($val['CommissionType'] == 'Value'){
                        if($val['CommissionCurrency'] != ''){
                            $commissionRangeArr[$val['StoreId']][$val['CommissionCurrency']][number_format($val['CommissionUsed'],3)] = $val['CommissionCurrency'].$val['CommissionUsed'];
                        }else{
                            $commissionRangeArr[$val['StoreId']]['USD'][number_format($val['CommissionUsed'],3)] = "USD".$val['CommissionUsed'];
                        }
                    }else{
                        $commissionRangeArr[$val['StoreId']]['%'][number_format($val['CommissionUsed'],3)] = $val['CommissionUsed'].'%';
                    }
                }
            }
            
            //计算七天内progrom的commission rate有增长的store(以最近的一次变动为基准)
            /* $riseWhereSql = '';
            if(!empty($rows_block)){
                $innerRiseWhereSql=" AND a.`SupportType` != 'None'";
                if($siteType == 'coupon'){
                    $innerRiseWhereSql.=" AND a.`SupportType` = 'Promotion' ";
                }else{
                    $innerRiseWhereSql.=" AND a.`SupportType` = 'Content' ";
                }
                $InnerJoinSql=" INNER JOIN ( SELECT * FROM (SELECT a.DID, a.Site, a.DefaultOrder, b.`ProgramId` AS PID,a.SupportType FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` WHERE b.`IsActive` = 'Active' ".$where_str_commissionValue." ".$innerRiseWhereSql." ORDER BY a.did, a.`Site`, a.`DefaultOrder`) AS ddd GROUP BY ddd.did, ddd.`Site` ) doatemp on doatemp.`PID` = pccl.`ProgramId` ";
            }else {
                $riseWhereSql=" AND pi.`SupportType` != 'None'";
                if($siteType == 'coupon'){
                    $riseWhereSql.=" AND pi.`SupportType` != 'Content' ";
                }else{
                    $riseWhereSql.=" AND pi.`SupportType` != 'Promotion' ";
                }
                $InnerJoinSql=" INNER JOIN program_intell pi on pi.`ProgramId` = pccl.`ProgramId` ";
            } */
            if($showCommissionRise){
                $commissionImproveSql = "SELECT * FROM ( SELECT pccl.`StoreId`,pccl.`ProgramId`,pccl.`AddTime`,pccl.`CommissionChangeValue` FROM `program_commission_change_log` pccl INNER JOIN r_store_program rsp ON pccl.`ProgramId` = rsp.`ProgramId`  ".$InnerJoinSql." WHERE pccl.`StoreId` in ( ".$storeIdtext." ) AND pccl.`AddTime` >= '".date('Y-m-d',strtotime('-7 days'))."'  AND rsp.`Outbound` <> '' ".$riseWhereSql." ORDER BY pccl.`StoreId`,pccl.`ProgramId`,pccl.`AddTime` DESC,pccl.`CommissionChangeValue` DESC ) temp GROUP BY temp.`StoreId`,temp.`ProgramId` HAVING temp.`CommissionChangeValue` > 0";
                $commissionImproveRs =  $this->objMysql->getRows($commissionImproveSql);
                $improveRs = array();
                foreach ($commissionImproveRs as $improve){
                    $improveRs[$improve['StoreId']][] = $improve['ProgramId'];
                }
            }
            
            foreach ($row as $key=>$val){
                
                if($showCommissionRise){
                    if(isset($improveRs[$val['StoreId']])){
                        if(array_intersect(explode(',', $val['Programids']),$improveRs[$val['StoreId']])){
                            $row[$key]['CommissionImprove'] = '1';
                        }
                    }
                }
                
                if(isset($commissionRangeArr[$val['StoreId']])){
                    $val['CommissionRange'] = '';
                    foreach ($commissionRangeArr[$val['StoreId']] as $tempK=>$tempV){
                        ksort($tempV);
                        if(count($tempV)<=1){
                            $val['CommissionRange'] .= ','.current($tempV);
                        }else {
                            $val['CommissionRange'] .= ','.current($tempV).'~'.end($tempV);
                        }
                    }
                    if($val['CommissionRange'] != ''){
                        $row[$key]['CommissionRange'] = trim($val['CommissionRange'],',');
                    }else {
                        $row[$key]['CommissionRange'] = 'other';
                    }
                }else {
                    $row[$key]['CommissionRange'] = 'other';
                }
                $row[$key]['historyStore'] = 'no';
            }
        }
        if (isset($search['store_keywords']) && $search['store_keywords']){
            $where_arr = array();
            $where_arr[] = "c.Keywords like '".addslashes($search['store_keywords'])."%'";
            $where_arr[] = " b.SupportType != 'None'";
            if($siteType == 'coupon'){
                $where_arr[] =" b.SupportType != 'Content' ";
            }else{
                $where_arr[] =" b.SupportType != 'Promotion' ";
            }
        
            if (isset($search['country']) && !empty($search['country']))
                $where_arr[] = ' FIND_IN_SET("'.addslashes($search['country']).'",b.CountryCode) ';
        
            if(isset($search['domain']) && !empty($search['domain'])){
                $where_arr[] = "c.ID = 0";
            }
            if(!empty($categoryArr)) {
                # for keywords store category
                $categoryId = implode(',',array_unique($categoryArr));
                $where_arr[] = "c.CategoryId IN ($categoryId)";
                # for recommend store category
                $where_category = " (";
                foreach($categoryArr as $cateid)
                {
                    $where_category .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
                }
                $where_category = rtrim($where_category,'OR').")";
                $where_arr[] = $where_category;
            }
            $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
            $sql = "SELECT COUNT(DISTINCT c.`Keywords`) as c FROM store_multi_brand AS c LEFT JOIN store AS b ON c.`StoreId` = b.`ID` ".$where_str;
            $rows_multi_count = $this->getRow($sql);
            $multi_count = $rows_multi_count['c'];
            $totalStore = $count;
            $count = $count + $multi_count;
            if(count($row)<$page_size){
                //剩下应该显示多少条
                $multi_num = $page_size - count($row);
                $sql = "SELECT c.StoreId,c.Keywords,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) as StoreName,b.ID FROM store_multi_brand AS c LEFT JOIN store AS b ON c.`StoreId` = b.`ID` ".$where_str."  GROUP BY c.StoreId,c.Keywords";
                $rows_multi = $this->getRows($sql);
                $multi_data = array();
                $sort_name = array();
                foreach($rows_multi as $k=>$v){
                    $Keywords = strtolower($v['Keywords']);
                    if(!isset($multi_data[$Keywords])){
                        $sort_name[] = $Keywords;
                        $multi_data[$Keywords]['storeName'] = $Keywords;
                        $multi_data[$Keywords]['Store'][] = array('StoreName'=>$v['StoreName'],'StoreId'=>$v['StoreId'],'ID'=>$v['ID']);
                        $multi_data[$Keywords]['Type'] = 'multi';
                    }else{
                        $multi_data[$Keywords]['Store'][] = array('StoreName'=>$v['StoreName'],'StoreId'=>$v['StoreId'],'ID'=>$v['ID']);
                    }
                }
                array_multisort($sort_name,SORT_ASC,$multi_data);
                //从第几条开始截取
                $start = ($page-1)*$page_size-$totalStore>0?($page-1)*$page_size-$totalStore:0;
                $multi_data = array_slice($multi_data,$start,$multi_num);
                $row = array_merge($row,$multi_data);
            }
        }
        
        //找总共有多少条content feed
        if(!empty($row)){
            $storeIdList = array();
            foreach ($row as $v){
                if(isset($v['StoreId'])){
                    if(!in_array($v['StoreId'], $historyStoreId)){
                        $storeIdList[] = $v['StoreId'];
                    }
                }
            }
            $storeIds = implode($storeIdList, ',');
            
            $merchant = new MerchantExt();
            $storeCount = $merchant->GetContentNew(array("storeIds"=>$storeIds),1,1,$search['uid'],false,true);
            $storeCountArray = array();
            foreach ($storeCount as $store){
                $storeCountArray[$store['StoreId']] = $store['StoreIdCount'];
            }
            foreach ($row as $key=>$val){
                if(!in_array($val['StoreId'], $historyStoreId)){
                    if(isset($storeCountArray[$val['StoreId']])){
                        $row[$key]['StoreCount'] = $storeCountArray[$val['StoreId']];
                    }else {
                        $row[$key]['StoreCount'] = 0;
                    }
                    $row[$key]['historyStore'] = 'no';
                }else {
                    $row[$key]['StoreCount'] = '/';
                    $row[$key]['CommissionRange'] = '/';
                    $row[$key]['PPC'] = '/';
                    $row[$key]['historyStore'] = 'yes';
                }
            }
        }
        $return_d = array();
        $return_d['page_total'] = ceil($count / $page_size);
        $return_d['page_now'] = $page;
        $return_d['total_num'] = $count;
        $return_d['data'] = $row;
        return $return_d;
    }
    
    function getRecommend($key){
        if($key == Constant::COUNTRY_ID_GERMANY){
            //德国
            $rid = '(91331,9358,11381,5871,10764,25556,15104,34142,21402,11018,10820,8929,2493,19413,994,8837,7897,120217,8868,25556)';
        }else if($key >= Constant::COUNTRY_ID_FRANCE && $key <= Constant::COUNTRY_ID_FRANCE_SOUTHERN){
            //法国
            $rid = '(12158,12141,22256,290,8241,364,11306,5832,7952,33763,12621,13390,10820,38551,21402,63251,15104,2765,593,16566)';
        }else if($key == Constant::COUNTRY_ID_Singapore){
            //新加坡
            $sql = "select id from store where FIND_IN_SET('sg',CountryCode) AND id not in(1394,7860,8143,10109,11381,12181,15133,16566,17363,23675,30742,32590,40956) ORDER BY Commission DESC limit 7 ";
            $res = $this->getRows($sql);
            $rid = '(1394,7860,8143,10109,11381,12181,15133,16566,17363,23675,30742,32590,40956,';
            foreach($res as $k){
                $rid.=$k['id'].',';
            }
            $rid = rtrim($rid,',').")";
        }else if($key == Constant::COUNTRY_ID_Philippines){
            //菲律宾
            $sql = "select id from store where FIND_IN_SET('ph',CountryCode) AND id not in(1201,2765,8839,11381,15221,34142,59016) ORDER BY Commission DESC limit 13 ";
            $res = $this->getRows($sql);
            $rid = '(1201,2765,8839,11381,15221,34142,59016,';
            foreach($res as $k){
                $rid.=$k['id'].',';
            }
            $rid = rtrim($rid,',').")";
        }else if($key == Constant::COUNTRY_ID_Malaysia){
            //马来西亚
            $sql = "select id from store where FIND_IN_SET('my',CountryCode) AND id not in(628,3102,11381,13614,24706,32590) ORDER BY Commission DESC limit 13 ";
            $res = $this->getRows($sql);
            $rid = '(628,3102,11381,13614,24706,32590,';
            foreach($res as $k){
                $rid.=$k['id'].',';
            }
            $rid = rtrim($rid,',').")";
        }
        else{
            //其他国家
            if(isset($_GET['country']) && !empty($_GET['country'])){
                $country = $_GET['country'];
            }else{
                if($key == Constant::COUNTRY_ID_UNITED_KINGDOM){
                    $country = 'uk';//英国
                }else{
                    $csql = "select a.CountryCode from country_codes a where a.id=".$key;
                    $country = $this->getRows($csql);
                    $country = $country[0]['CountryCode'];
                }
            }
            $rid = '(';
            $where=' where FIND_IN_SET("'.$country.'",CountryCode)';
            $sql = "select ID from store".$where." ORDER BY Commission DESC limit 20";
            $res = $this->getRows($sql);
            if(empty($res)){
                $rid = '(12158,12141,22256,290,8241,364,11306,5832,7952,33763,12621,13390,10820,38551,21402,63251,15104,2765,593,16566)';
            }else{
                foreach($res as $k){
                    $rid.=$k['ID'].',';
                }
                $rid = rtrim($rid,',').')';
            }
        }
        $rsql = "select IF(a.NameOptimized='' OR a.NameOptimized IS NULL,`Name`,NameOptimized) AS storeName ,a.LogoName,`ID`,a.LogoStatus from store a where `ID` IN$rid order by `ID`";
        $rstore= $this->objMysql->getRows($rsql,'ID');
        $sid = '(';
        foreach($rstore as &$k){
            if (strstr($k['LogoName'], ',')) {
                $logo = explode(',', $k['LogoName']);
                $k['LogoName'] = $logo[0];
            }
            if($k['LogoName'] == ''){
                $k['LogoName'] = 'brandreward.png';
            }
            $sid.=$k['ID'].',';
            $k['count'] = 0;
            unset($k);
        }
        $sid = rtrim($sid,',').')';
        $sql = "select count(1)total,StoreId from content_feed_new where StoreId IN$sid GROUP BY StoreId ORDER BY `StoreId`";
        $total = $this->objMysql->getRows($sql,'StoreId');
        foreach($rstore as $k=>$v){
            if(isset($total[$k])){
                $rstore[$k]['count'] = $total[$k]['total'];
            }
        }
        return $rstore;
    }
    
    //获取Valentine活动时的store数据
    function getActivityRecommend(){
        
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
        
        $sql = "SELECT IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,`Name`,NameOptimized) AS storeName ,ss.LogoName,`ID`,ss.LogoStatus FROM store ss WHERE ID IN 
         ( SELECT DISTINCT(`StoreId`) FROM content_feed_new cfn LEFT JOIN store s ON s.`ID` = cfn.`StoreId` LEFT JOIN program_intell `pi` ON `pi`.ProgramId = cfn.`ProgramId` WHERE s.`StoreAffSupport` = 'YES' AND cfn.`Status`='Active' AND ( `Title` LIKE '%valentine%' OR `Desc` LIKE '%valentine%' ) $where ) 
         ORDER BY Commission DESC LIMIT 40";
        $activeStore= $this->objMysql->getRows($sql);
        foreach($activeStore as $key=>$val){
            if (strstr($val['LogoName'], ',')) {
                $logo = explode(',', $val['LogoName']);
                $activeStore[$key]['LogoName'] = $logo[0];
            }
            if($val['LogoName'] == ''){
                $activeStore[$key]['LogoName'] = 'brandreward.png';
            }
        }
        return $activeStore;
    }
    
    function get_multi_brands($search)
    {
        $where_str = '';    
        $where_arr = array();

        $where_arr[] = "b.`ID` > 0";

        if(isset($search['store_keywords']) && !empty($search['store_keywords']))
            $where_arr[] = "Keywords like '".$search['store_keywords']."%'";
        else
            return array();

        if (isset($search['country']) && !empty($search['country']))
            $where_arr[] = "b.site = '" . $search['country'] . "'";


        $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
        $sql = "SELECT c.ID,c.StoreId,c.Keywords,c.StoreName FROM store_multi_brand AS c LEFT JOIN r_store_domain AS a ON c.`StoreId` = a.`StoreId` LEFT JOIN domain_outgoing_default_other AS b ON a.`DomainId` = b.`DID`".$where_str."  GROUP BY c.ID";
        $rows_multi = $this->getRows($sql);

        if(empty($rows_multi))
            return array();

        $storeids = array();
        $store_multi = array();
        foreach($rows_multi as $v){
            $storeids[] = $v['StoreId'];
            $store_multi[$v['StoreId']][] = $v['Keywords'];
        }

        $sql = "SELECT ID,`Name`, SupportCoupon, SupportLoyalty FROM store WHERE ID IN (".join(',',$storeids).")";
        $rows_store = $this->getRows($sql);
        foreach($rows_store as $k=>$v){
            $rows_store[$k]['Keywords'] = join("<br>",$store_multi[$v['ID']]);
        }

        return $rows_store;
    }
    function delcollect($id,$uid){
        $id = rtrim($id,',');
        $sql = "Delete from publisher_collect where sid IN ($id) and uid = $uid";
        $res = $this->query($sql);
        if($res == 1){
            return 1;
        }else{
            return 2;
        }
    }
    function checkCollect($uid){
        $sql = "select sid from publisher_collect where uid=$uid";
        return $this->getRows($sql);
    }
    function addcollect($uid,$sid,$type){
        if($type == 0){
            $insert_d['sid'] = $sid;
            $insert_d['uid'] = $uid;
            $this->table('publisher_collect')->insert($insert_d);
            return 1;
        }else{
            $sql = "delete from publisher_collect WHERE uid=$uid AND sid=$sid";
            $this->query($sql);
            return 2;
        }
    }
    function showAdvertiserDomainList($search){
        
        $sql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($sql);
        if(!empty($rows_block)){
            $return_d = $this->showAdvertiserDomainListOrigin($search);
            return $return_d;
        }
        
        $sql = "select CountryCode,CountryName from country_codes";
        $country = $this->objMysql->getRows($sql,'CountryCode');
        $country['UK']['CountryName'] ='United Kingdom';
        $country['GLOBAL']['CountryName'] ='Global';
        $country['other']['CountryName'] ='Other';
        $return_d = array();
        
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
            $checksql = "select sitetype from publisher_detail where PublisherId='{$search['uid']}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                }
            }
        }
        
        $where_str_store=" AND c.SupportType != 'None'";
        if($siteType == 'coupon'){
            $where_str_store.=" AND c.SupportType != 'Content' ";
        }else{
            $where_str_store.=" AND c.SupportType != 'Promotion' ";
        }
        
        $sql = 'SELECT
                  d.`Name` AS ProgramName,
                  c.`ProgramId`,
                  c.`CommissionType`,
                  c.`CommissionUsed`,
                  c.`CommissionCurrency`,
                  c.`CommissionValue`,
                  c.`ShippingCountry`,
                  b.`Outbound`
                FROM
                   r_store_program b
                  INNER JOIN program_intell c
                    ON c.`ProgramId` = b.`ProgramId`
                  INNER JOIN program d
                    ON d.`ID` = c.`ProgramId`
                WHERE b.`StoreId` ='.$search['id'].$where_str_store;
        $domain_arr = $this->getRows($sql);
        
        $sql = "SELECT DomainId FROM r_store_domain where StoreId = '".$search['id']."'";
        $domainId_arr = $this->getRows($sql);
        $domainIdList = array();
        foreach ($domainId_arr as $do){
            $domainIdList[$do['DomainId']] = $do['DomainId'];
        }
        
        //计算七天内store中的progrom的commission rate有增长的(以最近的一次变动为基准)
        $commissionImproveSql = " SELECT * FROM ( SELECT pccl.`StoreId`,pccl.`ProgramId`,pccl.`AddTime`,pccl.`CommissionChangeValue` FROM `program_commission_change_log` pccl WHERE pccl.`StoreId` = '".$search['id']."' AND pccl.`AddTime` >= '".date('Y-m-d',strtotime('-7 days'))."' ORDER BY pccl.`ProgramId`,pccl.`AddTime` DESC,pccl.`CommissionChangeValue` DESC ) temp GROUP BY temp.`ProgramId` HAVING temp.`CommissionChangeValue` > 0";
        $commissionImproveRs =  $this->objMysql->getRows($commissionImproveSql,'ProgramId');

        $result = array();
        foreach($domain_arr as $v=>$k1){
            //$tmp = array();
            if(empty($k1['Outbound']) || $k1['Outbound'] == ''){
//                 unset($domain_arr[$v]);
                continue;
            }
            if (strstr($k1['Outbound'], ',')) {
                $val = explode(',', $k1['Outbound']);
            }else{
                $val = array($k1['Outbound']);
            }
            foreach($val as $k){
                  if(strstr($k,'|')){
                      $key = explode('|',$k);
                  }else{
                      $key = explode('-',$k);
                  }
                  if(!in_array($key[0], $domainIdList)){
                      continue;
                  }
                  $valTemp = strtoupper($key[1]);
                  //若为global且数量多于1的显示other
                  //if ($valTemp=="GLOBAL" && count($val)>1){
                  //    $valTemp = 'other';
                  //}
                  
                  $apikeytxt = isset($_SESSION['pubAccActiveList']['active'])?current($_SESSION['pubAccActiveList']['data'])['ApiKey']:$_SESSION['u']['apikey'];
                  if(substr($key[2], 0,7) != 'http://' && substr($key[2], 0,7) != 'https://'){
                      $url = "<a target=_blank href='".constant("GO_URL").'?key='.$apikeytxt.'&url='.urlencode('http://'.$key[2])."'>".$key[2]."</a>";
                  }else{
                      $url = "<a target=_blank href='".constant("GO_URL").'?key='.$apikeytxt.'&url='.urlencode($key[2])."'>".$key[2]."</a>";
                  }
//                   $tmp[] = array(
//                       $country[$valTemp]['CountryName'],
//                       $url
//                   );

                  if(isset($result[$k1['ProgramId']])){
                      $result[$k1['ProgramId']]['country'] .= ','.$country[$valTemp]['CountryName'];
                  }else {
                      $result[$k1['ProgramId']]['domain'] = $url;
                      $result[$k1['ProgramId']]['country'] = $country[$valTemp]['CountryName'];
                      
                      $commissionRangeArr = array();
                      if($k1['CommissionValue'] != '' && $k1['CommissionValue'] != null){
                          $commissionArr = explode("|", $k1['CommissionValue'])[0];
                          $commissionValText = trim($commissionArr,"[]");
                          $commissionValArr = explode(",", $commissionValText);
                          foreach ($commissionValArr as $temp){
                              preg_match("/\d+(\.\d+)?/", $temp,$number);
                              $commissionRangeArr[$number[0]] = $temp;
                          }
                      }else {
                          if($k1['CommissionUsed'] == '0'){
                              $commissionRangeArr[0] = 'other';
                          }else if($k1['CommissionType'] == 'Value'){
                              if($k1['CommissionCurrency'] != ''){
                                  $commissionRangeArr[0] = $k1['CommissionCurrency'].$k1['CommissionUsed'];
                              }else{
                                  $commissionRangeArr[0] = "USD".$k1['CommissionUsed'];
                              }
                          }else{
                              $commissionRangeArr[0] = $k1['CommissionUsed'].'%';
                          }
                      }
                      ksort($commissionRangeArr);
                      if(count($commissionRangeArr)<=1){
                          $result[$k1['ProgramId']]['CommissionRange'] = current($commissionRangeArr);
                      }else {
                          $result[$k1['ProgramId']]['CommissionRange'] = current($commissionRangeArr).'~'.end($commissionRangeArr);
                      }
                  }

                  /* if(isset($result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']])){
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['country'] .= ','.$country[$valTemp]['CountryName'];
                  }else {
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['domain'] = $url;
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['country'] = $country[$valTemp]['CountryName'];
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['CommissionType'] = $k1['CommissionType'];
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['CommissionUsed'] = $k1['CommissionUsed'];
                      $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['CommissionCurrency'] = $k1['CommissionCurrency'];
                  } */
            }
            $result[$k1['ProgramId']]['Name'] = $k1['ProgramName'];
//             $result[$key[2].$k1['CommissionType'].$k1['CommissionUsed'].$k1['CommissionCurrency']]['Name'] = $k1['ProgramName'];
//             $domain_arr[$v]['Name'] = $k1['ProgramName'];
//             $domain_arr[$v]['Outbound'] = $tmp;

            if(array_key_exists($k1['ProgramId'], $commissionImproveRs)){
                $result[$k1['ProgramId']]['CommissionImprove'] = '1';
            }else {
                $result[$k1['ProgramId']]['CommissionImprove'] = '0';
            }
        }
        $return_d['data'] = $result;
        return $return_d;
    }
    
    function showAdvertiserDomainListOrigin($search){
        $sql = "select CountryCode,CountryName from country_codes";
        $country = $this->objMysql->getRows($sql,'CountryCode');
        $country['UK']['CountryName'] ='United Kingdom';
        $country['GLOBAL']['CountryName'] ='Global';
        $country['OTHER']['CountryName'] ='Other';
        $return_d = array();
        
        
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
            $checksql = "select sitetype from publisher_detail where PublisherId='{$search['uid']}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                }
            }
        }

        $where_str_store="";
        /* if($siteType == 'coupon'){
            $where_str_store.=" AND a.SupportType != 'Content' ";
        } */
        if($doaType == 'content'){
            $where_str_store.=" AND a.SupportType = 'Content' ";
        }else if($doaType == 'coupon'){
            $where_str_store.=" AND a.SupportType = 'Promotion' ";
        }
        
        #filter for block
        $sql = "SELECT * FROM block_relationship WHERE (AccountType = 'AccountId' AND  AccountID IN (SELECT ID FROM publisher_account WHERE PubLisherId = ".intval($_SESSION['u']['ID']).") AND `Status` = 'Active') OR (AccountType = 'PublisherId' AND AccountID = ".intval($_SESSION['u']['ID'])." AND `Status` = 'Active')";
        $rows_block = $this->getRows($sql);
        if(!empty($rows_block)){
            foreach($rows_block as $k=>$v){
                switch($v['ObjType']){
                    case 'Affiliate':
                        if($v['AccountType'] == "PublisherId"){
                            $where_str_store .= " AND b.AffId != ".$v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where_str_store .= " AND b.AffId != ".$v['ObjId'];
                                }
                            }else {
                                $where_str_store .= " AND b.AffId != ".$v['ObjId'];
                            }
                        }
                    break;
                    case 'Program':
                        if($v['AccountType'] == "PublisherId"){
                            $where_str_store .= " AND b.ProgramId != ".$v['ObjId'];
                        }else {
                            if(isset($_SESSION['pubAccActiveList']['active'])){
                                if($_SESSION['pubAccActiveList']['active'] == $v['AccountId']){
                                    $where_str_store .= " AND b.ProgramId != ".$v['ObjId'];
                                }
                            }else {
                                $where_str_store .= " AND b.ProgramId != ".$v['ObjId'];
                            }
                        }
                    break;
                }
            }
        }

//         $sql = "SELECT a.DID,a.Site,MIN(a.DefaultOrder),a.Key,b.`ProgramId`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue`,b.`ShippingCountry` FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` WHERE a.did IN (SELECT domainid FROM r_store_domain WHERE storeid = ".intval($search['id']).") AND b.`IsActive` = 'Active' ".$where_str_store." GROUP BY a.did,a.site";
        $sql = " SELECT * FROM ( SELECT a.DID,a.Site,a.DefaultOrder,a.Key,b.`ProgramId`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue`,b.`ShippingCountry` FROM domain_outgoing_all AS a LEFT JOIN program_intell AS b ON a.`PID` = b.`programID` WHERE a.did IN (SELECT domainid FROM r_store_domain WHERE storeid = ".intval($search['id']).") AND b.`IsActive` = 'Active' ".$where_str_store." ORDER BY a.did,a.`Site`,a.`DefaultOrder` ) AS temp GROUP BY temp.did,temp.`Site` ";
        $rows = $this->getRows($sql);
        $sql = "SELECT DomainId FROM r_store_domain where StoreId = '".$search['id']."'";
        $domainId_arr = $this->getRows($sql);
        $domainIdList = array();
        foreach ($domainId_arr as $do){
            $domainIdList[$do['DomainId']] = $do['DomainId'];
        }

//         $rows = $this->getRows($sql);

        //计算七天内store中的progrom的commission rate有增长的(以最近的一次变动为基准)
        $commissionImproveSql = " SELECT * FROM ( SELECT pccl.`StoreId`,pccl.`ProgramId`,pccl.`AddTime`,pccl.`CommissionChangeValue` FROM `program_commission_change_log` pccl WHERE pccl.`StoreId` = '".$search['id']."' AND pccl.`AddTime` >= '".date('Y-m-d',strtotime('-7 days'))."' ORDER BY pccl.`ProgramId`,pccl.`AddTime` DESC,pccl.`CommissionChangeValue` DESC ) temp GROUP BY temp.`ProgramId` HAVING temp.`CommissionChangeValue` > 0";
        $commissionImproveRs =  $this->objMysql->getRows($commissionImproveSql,'ProgramId');
        $data = array();
        $apikeytxt = isset($_SESSION['pubAccActiveList']['active'])?current($_SESSION['pubAccActiveList']['data'])['ApiKey']:$_SESSION['u']['apikey'];
        foreach($rows as $k=>$v){
            if(empty($v['ProgramId'])){
                continue;
            }
            if(!in_array($v['DID'], $domainIdList)){
                continue;
            }
            $data[$v['ProgramId']]['ProgramId'] = $v['ProgramId'];
//             $data[$v['ProgramId']]['CommissionType'] = $v['CommissionType'];
//             $data[$v['ProgramId']]['CommissionUsed'] = $v['CommissionUsed'];
//             $data[$v['ProgramId']]['CommissionCurrency'] = $v['CommissionCurrency'];
            $data[$v['ProgramId']]['ShippingCountry'] = $v['ShippingCountry'];
            
            $commissionRangeArr = array();
            if($v['CommissionValue'] != '' && $v['CommissionValue'] != null){
                $commissionArr = explode("|", $v['CommissionValue'])[0];
                $commissionValText = trim($commissionArr,"[]");
                $commissionValArr = explode(",", $commissionValText);
                foreach ($commissionValArr as $temp){
                    preg_match("/\d+(\.\d+)?/", $temp,$number);
                    $commissionRangeArr[$number[0]] = $temp;
                }
            }else {
                if($v['CommissionUsed'] == '0'){
                    $commissionRangeArr[0] = 'other';
                }else if($v['CommissionType'] == 'Value'){
                    if($v['CommissionCurrency'] != ''){
                        $commissionRangeArr[0] = $v['CommissionCurrency'].$v['CommissionUsed'];
                    }else{
                        $commissionRangeArr[0] = "USD".$v['CommissionUsed'];
                    }
                }else{
                    $commissionRangeArr[0] = $v['CommissionUsed'].'%';
                }
            }
            ksort($commissionRangeArr);
            if(count($commissionRangeArr)<=1){
                $data[$v['ProgramId']]['CommissionRange'] = current($commissionRangeArr);
            }else {
                $data[$v['ProgramId']]['CommissionRange'] = current($commissionRangeArr).'~'.end($commissionRangeArr);
            }
            
            $domainurl = $v['Key'];
            if(substr($domainurl, 0,4) != 'http'){
                $domainurl = "<a target=_blank href='".constant("GO_URL").'?key='.$apikeytxt.'&url='.urlencode('http://'.$domainurl)."'>".$domainurl."</a>";
            }else{
                $domainurl = "<a target=_blank href='".constant("GO_URL").'?key='.$apikeytxt.'&url='.urlencode($domainurl)."'>".$domainurl."</a>";
            }

            $data[$v['ProgramId']]['domain'] = $domainurl;
            $data[$v['ProgramId']]['country'][] = $country[strtoupper($v['Site'])]['CountryName'];
            
            if(array_key_exists($v['ProgramId'], $commissionImproveRs)){
                $data[$v['ProgramId']]['CommissionImprove'] = '1';
            }else {
                $data[$v['ProgramId']]['CommissionImprove'] = '0';
            }
        }

        $pids = array_keys($data);
        if(!empty($pids)){
            $sql = "SELECT ID,Name FROM program where ID IN (".join(',',$pids).")";
            $rows = $this->getRows($sql);
            
            foreach($data as $k=>$v){
                $data[$k]['ProgramName'] = $rows[$v['ProgramId']]['Name'];
                $data[$k]['Name'] = $rows[$v['ProgramId']]['Name'];
                $data[$k]['country'] = join(',',$v['country']);
            }
        }

        $return_d = array('data'=>$data);
        return $return_d;
    }
}
