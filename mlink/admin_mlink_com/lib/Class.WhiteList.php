<?php
class WhiteList extends LibFactory
{
    
	function getStoreList($name,$page){
	    $pageSize = 20;
		$sql = "select count(1) as total from store s where `StoreAffSupport` = 'YES' and (s.NameOptimized like '%{$name}%' or s.Name like '%{$name}%')";
		$total = $this->getRow($sql);
		$sql = "select s.`ID` AS storeId,IF(s.NameOptimized='' OR s.NameOptimized IS NULL,trim(s.Name),trim(s.NameOptimized)) AS storeName from store s where `StoreAffSupport` = 'YES' and (s.NameOptimized like '%{$name}%' or s.Name like '%{$name}%') order by LENGTH(storeName),storeName limit ".($page-1)*$pageSize.",{$pageSize}";
		$result = $this->getRows($sql);
		$rs['more'] = $total['total'];
		$rs['data'] = $result;
		return $rs;
	}
	
	//若有id则为修改，否则为新增
	function saveAccount($param){
	    if(isset($param['id']) && $param['id']!=''){
	        $storeTxt = implode("','", $param['store']);
	        $sql = "select IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,trim(ss.Name),trim(ss.NameOptimized)) AS storeName from white_list_store wls left join store ss on ss.`ID`=wls.`StoreId` where wls.`StoreId` in ('".$storeTxt."') and wls.WhiteAccountId <> '".$param['id']."'";
	        $rs = $this->getRow($sql);
	        if(isset($rs['storeName'])){
	            $result['code'] = 2;
	            $result['msg'] = "The store '".$rs['storeName']."' has been used";
	            return $result;
	        }else {
	            $set = '';
	            if(isset($param['password']) && $param['password']!=''){
	                $set = ",`UserPass` = '".md5($param['password'])."'";
	            }
	            $sql = "UPDATE white_list_account set `UserName` = '".addslashes($param['username'])."',`Name` = '".addslashes($param['name'])."',`Status` = '".addslashes($param['status'])."',`Remark` = '".addslashes($param['remark'])."',`StoreIds` = '".addslashes(implode(',', $param['store']))."' $set WHERE `ID` = '".addslashes($param['id'])."'";
	            $this->query($sql);
	            $sql = "Delete from white_list_store where `WhiteAccountId` = '".addslashes($param['id'])."'";
	            $this->query($sql);
	            foreach ($param['store'] as $val){
	                $sql = "insert into white_list_store(`WhiteAccountId`,`StoreId`) values ('".$param['id']."','".intval(addslashes($val))."')";
	                $this->query($sql);
	            }
	            $result['code'] = 1;
	            $result['msg'] = "success";
	            return $result;
	        }
	    }else {
	        $sql = "select id from white_list_account where UserName = '".addslashes($param['username'])."'";
	        $exist = $this->getRow($sql);
	        if($exist){
	            $result['code'] = 2;
	            $result['msg'] = "Username has been used";
	            return $result;
	        }
	        $storeTxt = implode("','", $param['store']);
	        $sql = "select IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,trim(ss.Name),trim(ss.NameOptimized)) AS storeName from white_list_store wls left join store ss on ss.`ID`=wls.`StoreId` where wls.`StoreId` in ('".$storeTxt."')";
	        $rs = $this->getRow($sql);
	        if(isset($rs['storeName'])){
	            $result['code'] = 2;
	            $result['msg'] = "The store '".$rs['storeName']."' has been used";
	            return $result;
	        }
	        //用户信息存入表中   
	        $value = "'".addslashes($param['username'])."','".md5(addslashes($param['password']))."','".addslashes($param['name'])."','".addslashes($param['status'])."','".addslashes($param['remark'])."','".addslashes(implode(',', $param['store']))."','".date("Y-m-d H:i:s")."'";
	        $sql = "insert into white_list_account(`UserName`,`UserPass`,`Name`,`Status`,`Remark`,`StoreIds`,`AddTime`) values ($value)";
	        $this->query($sql);
	        //插入white_list_account返回的id
	        $accountID = mysql_insert_id();
	        foreach ($param['store'] as $val){
	            $sql = "insert into white_list_store(`WhiteAccountId`,`storeId`) values ('".$accountID."','".intval(addslashes($val))."')";
	            $this->query($sql);
	        }
	        $result['code'] = 1;
            $result['msg'] = "success";
            return $result;
	    }
	}
	
	//获取列表
	function getAccountList(){
	    $sql = "select wla.`ID`,wla.`Name` ,wla.`UserName` ,wla.`UserPass` ,wla.`Status`,wla.`Remark`,wls.`StoreId`,IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,trim(ss.Name),trim(ss.NameOptimized)) AS storeName from white_list_account wla left join white_list_store wls on wls.`WhiteAccountId`=wla.`ID` left join store ss on ss.`ID`=wls.`StoreId` order by wla.`LastUpdateTime` desc";
	    $list = $this->getRows($sql);
	    $rs = array();
	    foreach ($list as $val){
	        if(!isset($rs[$val['ID']])){
	            $rs[$val['ID']]['ID'] = $val['ID'];
	            $rs[$val['ID']]['Name'] = $val['Name'];
	            $rs[$val['ID']]['UserName'] = $val['UserName'];
	            $rs[$val['ID']]['UserPass'] = $val['UserPass'];
	            $rs[$val['ID']]['Status'] = $val['Status'];
	            $rs[$val['ID']]['Remark'] = $val['Remark'];
	        }
	        $rs[$val['ID']]['storeName'][$val['StoreId']] = $val['storeName'];
	    }
	    return $rs;
	}
	
	function getAccountDetail($id){
	    $sql = "select wla.`ID`,wla.`Name` ,wla.`UserName` ,wla.`Status`,wla.`Remark`,wls.`StoreId`,IF(ss.NameOptimized='' OR ss.NameOptimized IS NULL,trim(ss.Name),trim(ss.NameOptimized)) AS storeName from white_list_account wla left join white_list_store wls on wls.`WhiteAccountId`=wla.`ID` left join store ss on ss.`ID`=wls.`StoreId` where wla.`ID` = ".$id;
	    $list = $this->getRows($sql);
	    $rs = array();
	    foreach ($list as $val){
	        if(!isset($rs[$val['ID']])){
	            $rs[$val['ID']]['id'] = $val['ID'];
	            $rs[$val['ID']]['Name'] = $val['Name'];
	            $rs[$val['ID']]['UserName'] = $val['UserName'];
	            $rs[$val['ID']]['Status'] = $val['Status'];
	            $rs[$val['ID']]['Remark'] = $val['Remark'];
	        }
	        $rs[$val['ID']]['storeName'][$val['StoreId']] = $val['storeName'];
	    }var_dump($rs);exit;
	    return $rs;
	}
	

	
	
}
