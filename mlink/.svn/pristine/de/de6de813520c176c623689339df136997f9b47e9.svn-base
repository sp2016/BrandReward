<?php
class Account extends LibFactory
{
	function new_publisher($data){
		// check account
		if(!isset($data['pub_account']) || empty($data['pub_account'])){
			return 1;
		}
		if(!preg_match('/^[0-9a-zA-Z@_]+$/', $data['pub_account'])){
			return 2;
		}
		$row = $this->table('publisher')->where('UserName = "'.addslashes($data['pub_account']).'"')->findone();
		if($row){
			return 3;
		}

		// check password
		if(!isset($data['pub_pwd']) || empty($data['pub_pwd'])){
			return 4;
		}
		if(!preg_match('/^.{8,}$/', $data['pub_pwd'])){
			return 5;
		}

		// check password again
		if(!isset($data['pub_pwd_ag']) || $data['pub_pwd_ag'] != $data['pub_pwd']){
			return 6;
		}

		// new publisher
		$insert_d = array();
		//base
		$insert_d['UserName'] = $data['pub_account'];
		$insert_d['UserPass'] = md5($data['pub_pwd']);
		//contact
		$insert_d['Name'] = isset($data['pub_name'])&&!empty($data['pub_name'])?$data['pub_name']:'';
		$insert_d['Name'] = trim($insert_d['Name']);
		$insert_d['Domain'] = isset($data['pub_site'])&&!empty($data['pub_site'])?$data['pub_site']:'';
		$insert_d['Domain'] = trim($insert_d['Domain']);
		$insert_d['Email'] = isset($data['pub_email'])&&!empty($data['pub_email'])?$data['pub_email']:'';
		$insert_d['Email'] = trim($insert_d['Email']);
		$insert_d['Phone'] = isset($data['pub_phone'])&&!empty($data['pub_phone'])?$data['pub_phone']:'';
		$insert_d['Phone'] = trim($insert_d['Phone']);
		$insert_d['Company'] = isset($data['pub_company'])&&!empty($data['pub_company'])?$data['pub_company']:'';
		$insert_d['Company'] = trim($insert_d['Company']);

		$insert_d['CompanyAddr'] = isset($data['pub_companyaddress'])&&!empty($data['pub_companyaddress'])?$data['pub_companyaddress']:'';
		$insert_d['CompanyAddr'] = trim($insert_d['CompanyAddr']);
		$insert_d['Country'] = isset($data['pub_country'])&&!empty($data['pub_country'])?$data['pub_country']:'';
		$insert_d['Country'] = intval($insert_d['Country']);
		//status
		$insert_d['Status'] = 'Inactive';
		$insert_d['AddTime'] = date('Y-m-d H:i:s');
		$insert_d['LastUpdateTime'] = $insert_d['AddTime'];

		$res = $this->table('publisher')->insert($insert_d);
		if(!$res){
			return 7;
		}
		return 0;
	}
	//用户申请修改
	function  updatepublisher($data){
		if(isset($data['uptype']) && !empty($data['uptype'])){
			$olddata = array();
			if(!empty($data['oldinfo']) && !empty($data['olddet'])){
				$info = json_decode($data['oldinfo'],true);
				$det = json_decode($data['olddet'],true);
				$olddata['info'] = $info;
				$olddata['detail'] = $det;
			}else if(!empty($data['oldinfo'])){
				$info = json_decode($data['oldinfo'],true);
				$olddata['info'] = $info;
			}else{
				$det = json_decode($data['olddet'],true);
				$olddata['detail'] = $det;
			}
			$json = json_decode($data['info'],true);
			if(!empty($info)){
				$res = $this->table('publisher')->where('ID = ' . intval($data['upid']))->update($json['info']);
			}else{
				$res =1;
			}
			if($res == 1){
				if(!empty($det)){
					$res = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['upid']))->update($json['detail']);
				}else{
					$res = 1;
				}
				if($res == 1){
					$uparr = array();
					$uparr['state'] = 1;
					$uparr['uptype'] = 1;
					$uparr['time'] = date("Y-m-d H:i:s");
					$uparr['update_user'] = $_SERVER['PHP_AUTH_USER'];
					$res = $this->table('publisher_update')->where('PublisherId = ' . intval($data['upid']))->update($uparr);
					$logarr = array();
					$logarr['PublisherId'] = $data['upid'];
					$logarr['time'] = date('Y-m-d H:i:s');
					$logarr['update_user'] = $_SERVER['PHP_AUTH_USER'];
					$logarr['oldinfo'] = json_encode_no_zh($olddata);
					$logarr['newinfo'] = $data['info'];
					$this->table('publisher_update_log')->insert($logarr);
					if($res == 1){
						return 1;
					}else{
						return 2;
					}
				}else{
					return 2;
				}
			}else{
				return 2;
			}

		}
		$update_d = array();
		$update_d['Domain'] = isset($data['info']['Domain']) && !empty($data['info']['Domain']) ? $data['info']['Domain'] : '';
		$update_d['Domain'] = trim($update_d['Domain']);
		$update_d['Email'] = isset($data['info']['Email']) && !empty($data['info']['Email']) ? $data['info']['Email'] : '';
		$update_d['Email'] = trim($update_d['Email']);
		$update_d['Phone'] = isset($data['info']['Phone']) && !empty($data['info']['Phone']) ? $data['info']['Phone'] : '';
		$update_d['Phone'] = trim($update_d['Phone']);
		$update_d['Company'] = isset($data['info']['Company']) && !empty($data['info']['Company']) ? $data['info']['Company'] : '';
		$update_d['Company'] = trim($update_d['Company']);
		$update_d['CompanyAddr'] = isset($data['info']['CompanyAddr']) && !empty($data['info']['CompanyAddr']) ? $data['info']['CompanyAddr'] : '';
		$update_d['CompanyAddr'] = trim($update_d['CompanyAddr']);
		$update_d['Country'] = isset($data['info']['Country']) && !empty($data['info']['Country']) ? $data['info']['Country'] : '';
		$update_d['Country'] = intval($update_d['Country']);
		$update_d['Name'] = isset($data['info']['Name']) && !empty($data['info']['Name']) ? $data['info']['Name'] : '';
		$update_d['Name'] = trim($update_d['Name']);
		$update_d['PayPal'] = isset($data['info']['PayPal']) && !empty($data['info']['PayPal']) ? $data['info']['PayPal'] : '';
		$update_d['PayPal'] = trim($update_d['PayPal']);
		$update_d['Career'] = isset($data['info']['Career']) && !empty($data['info']['Career']) ? $data['info']['Career'] : '';
		$update_d['Career'] = trim($update_d['Career']);
		$update_d['Level'] = isset($data['info']['Level']) && !empty($data['info']['Level']) ? $data['info']['Level'] : '';
		$update_d['Level'] = trim($update_d['Level']);
		$update_d['Status'] = isset($data['info']['Status']) && !empty($data['info']['Status']) ? $data['info']['Status'] : '';
		$update_d['Status'] = trim($update_d['Status']);
		$update_d['ZipCode'] = isset($data['info']['ZipCode'])&&!empty($data['info']['ZipCode'])?$data['info']['ZipCode']:'';
		$update_d['ZipCode'] = trim($update_d['ZipCode']);
		$update_d['Manager'] = isset($data['info']['Manager']) && !empty($data['info']['Manager']) ? $data['info']['Manager'] : '';
		$update_d['Manager'] = trim($update_d['Manager']);
		$update_d['Remark'] = isset($data['info']['Remark']) && !empty($data['info']['Remark']) ? $data['info']['Remark'] : '';
		$update_d['Remark'] = trim($update_d['Remark']);
		$update_d['Tax'] = isset($data['info']['Tax']) && !empty($data['info']['Tax']) ? $data['info']['Tax'] : '';
		$update_d['Tax'] = trim($update_d['Tax']);
		
		$this->change_publisher_level_status($data['ID'], $update_d['Level'], $update_d['Status']);
		
		$res = $this->table('publisher')->where('ID = ' . intval($data['ID']))->update($update_d);
		if (!$res) {
			return 2;
		}else{
			$update_new = array();
			$update_new['CategoryId'] = isset($data['detail']['CategoryId']) && !empty($data['detail']['CategoryId']) ? $data['detail']['CategoryId'] : '';
			$update_new['CategoryId'] = trim($update_new['CategoryId']);
			$update_new['GeoBreakdown'] = isset($data['detail']['GeoBreakdown']) && !empty($data['detail']['GeoBreakdown']) ? $data['detail']['GeoBreakdown'] : '';
			$update_new['GeoBreakdown'] = trim($update_new['GeoBreakdown']);
			$update_new['SiteType'] = isset($data['detail']['SiteType']) && !empty($data['detail']['SiteType']) ? $data['detail']['SiteType'] : '';
			$update_new['SiteType'] = trim($update_new['SiteType']);
			$update_new['StaffNumber'] = isset($data['detail']['StaffNumber']) && !empty($data['detail']['StaffNumber']) ? $data['detail']['StaffNumber'] : '';
			$update_new['StaffNumber'] = trim($update_new['StaffNumber']);
			$update_new['DevKnowledge'] = isset($data['detail']['DevKnowledge']) && !empty($data['detail']['DevKnowledge']) ? $data['detail']['DevKnowledge'] : '';
			$update_new['DevKnowledge'] = trim($update_new['DevKnowledge']);
			$update_new['ContentProduction'] = isset($data['detail']['ContentProduction']) && !empty($data['detail']['ContentProduction']) ? $data['detail']['ContentProduction'] : '';
			$update_new['ContentProduction'] = trim($update_new['ContentProduction']);
			$update_new['WaysOfTraffic'] = isset($data['detail']['WaysOfTraffic']) && !empty($data['detail']['WaysOfTraffic']) ? $data['detail']['WaysOfTraffic'] : '';
			$update_new['WaysOfTraffic'] = trim($update_new['WaysOfTraffic']);
			$update_new['TypeOfContent'] = isset($data['detail']['TypeOfContent']) && !empty($data['detail']['TypeOfContent']) ? $data['detail']['TypeOfContent'] : '';
			$update_new['TypeOfContent'] = trim($update_new['TypeOfContent']);
			$update_new['CurrentNetwork'] = isset($data['detail']['CurrentNetwork']) && !empty($data['detail']['CurrentNetwork']) ? $data['detail']['CurrentNetwork'] : '';
			$update_new['CurrentNetwork'] = trim($update_new['CurrentNetwork']);
			$update_new['ProfitModel'] = isset($data['detail']['ProfitModel']) && !empty($data['detail']['ProfitModel']) ? $data['detail']['ProfitModel'] : '';
			$update_new['ProfitModel'] = trim($update_new['ProfitModel']);
			$one_detail = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['ID']))->findone();
			if (empty($one_detail)) {
				$update_new['PublisherId'] = intval($data['ID']);
				$res = $this->table('publisher_detail')->insert($update_new);
				if(!$res){
					return 1;
				} else {
					return 0;
				}
			} else {
				$res = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['ID']))->update($update_new);
				if($res == 1){
					return 1;
				}else{
					return 0;
				}
			}

		}
	}
	
	/**
	 * 修改publisher的level和status后，需要加相应的block操作
	 * id int publisher的id
	 * level 修改后的level值
	 */
	function change_publisher_level_status($id,$level,$status){
	    $sql = "SELECT Level,Status FROM publisher WHERE ID = '".intval($id)."'";
	    $rs = $this->getRow($sql);
	    $addUser = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
        //状态从Inactive或Unaudited变为active的且是2级的，要看看是否block过
	    if($status == 'Active' && $level == "TIER2"){
	         if($rs['Status'] == 'Inactive' || $rs['Status'] == 'Unaudited'){
	            //只要有过记录，修改publisher状态时不修改block
	            $sql = "SELECT * FROM block_relationship WHERE `Source` = 'SYSTEM' AND `ObjType` = 'Affiliate' AND `PublisherId` = '".$id."'";
	            $brList = $this->getRows($sql);
	            if(empty($brList)){
	                $affSql = "SELECT ID FROM wf_aff WHERE `IsActive` = 'YES' AND `Level` = 'TIER1'";
	                $affIdList = $this->getRows($affSql);
	                foreach ($affIdList as $affId){
	                    $insert_br = array();
	                    $insert_br['BlockBy'] = 'Affiliate';
	                    $insert_br['AccountId'] = $id;
	                    $insert_br['AccountType'] = 'PublisherId';
	                    $insert_br['PublisherId'] = $id;
	                    $insert_br['ObjId'] = $affId['ID'];
	                    $insert_br['ObjType'] = 'Affiliate';
	                    $insert_br['Status'] = 'Active';
	                    $insert_br['AddTime'] = date('Y-m-d H:i:s');
	                    $insert_br['AddUser'] = $addUser;
	                    $insert_br['Source'] = 'SYSTEM';
	                    $this->table("block_relationship")->insert($insert_br);
	                }
	            }
	         }
	    }
	    if($level == "TIER2"){
	        //如果等级是从TIER0或TIER1变为了TIER2，则要新增block
	        if($rs['Level'] == 'TIER0' || $rs['Level'] == 'TIER1'){
	            $sql = "SELECT wa.`ID` FROM wf_aff wa WHERE NOT EXISTS (SELECT ObjId FROM block_relationship br WHERE br.ObjId = wa.`ID` AND br.`PublisherId` = '".$id."' AND br.`Status` = 'Active' AND br.`Source` = 'SYSTEM' AND br.ObjType = 'Affiliate' ) AND  wa.`IsActive` = 'YES' AND wa.`Level` = 'TIER1'";
	            $affIdList = $this->getRows($sql);
	            if(!empty($affIdList)){
	                foreach ($affIdList as $affId){
	                    $insert_br = array();
                        $insert_br['BlockBy'] = 'Affiliate';
                        $insert_br['AccountId'] = $id;
                        $insert_br['AccountType'] = 'PublisherId';
	                    $insert_br['PublisherId'] = $id;
                        $insert_br['ObjId'] = $affId['ID'];
	                    $insert_br['ObjType'] = 'Affiliate';
	                    $insert_br['Status'] = 'Active';
	                    $insert_br['AddTime'] = date('Y-m-d H:i:s');
	                    $insert_br['AddUser'] = $addUser;
	                    $insert_br['Source'] = 'SYSTEM';
	                    $this->table("block_relationship")->insert($insert_br);
	                }
	            }
	        }
	     }else {
	        //如果等级是从TIER2变为了TIER0或TIER1，则要去掉block
	        if($rs['Level'] == 'TIER2'){
	            $update_br = array();
	            $update_br['Status'] = 'Inactive';
	            $update_br['LastUpdateTime'] = date("Y-m-d H:i:s");
	            $this->table('block_relationship')->where("`Status` = 'Active' AND `Source` = 'SYSTEM' AND `ObjType` = 'Affiliate' AND PublisherId = " . $id)->update($update_br);
	        }
	     }
	     return true;
	}
	
	function edit_publish_account($data){
		if(!isset($data['ID']) || empty($data['ID'])){
			return 1;
		}

		$update_d = array();

		$update_d['Name'] = isset($data['pub_name'])&&!empty($data['pub_name'])?$data['pub_name']:'';
		$update_d['Name'] = trim($update_d['Name']);
		$update_d['Domain'] = isset($data['pub_site'])&&!empty($data['pub_site'])?$data['pub_site']:'';
		$update_d['Domain'] = trim($update_d['Domain']);
		$update_d['Email'] = isset($data['pub_email'])&&!empty($data['pub_email'])?$data['pub_email']:'';
		$update_d['Email'] = trim($update_d['Email']);
		$update_d['Phone'] = isset($data['pub_phone'])&&!empty($data['pub_phone'])?$data['pub_phone']:'';
		$update_d['Phone'] = trim($update_d['Phone']);
		$update_d['Company'] = isset($data['pub_company'])&&!empty($data['pub_company'])?$data['pub_company']:'';
		$update_d['Company'] = trim($update_d['Company']);
		$update_d['CompanyAddr'] = isset($data['pub_companyaddr'])&&!empty($data['pub_companyaddr'])?$data['pub_companyaddr']:'';
		$update_d['CompanyAddr'] = trim($update_d['CompanyAddr']);
		$update_d['Country'] = isset($data['pub_country'])&&!empty($data['pub_country'])?$data['pub_country']:'';
		$update_d['Country'] = intval($update_d['Country']);

		$res = $this->table('publisher')->where('ID = '.intval($data['ID']))->update($update_d);
		if(!$res){
			return 2;
		}
		return 0;
	}

	function edit_profile_site($data){
		
		if(empty($data['site_domain'])){
			return -1;
		}

		if($data['ID'] > 0){
			$row = $this->table('publisher_account')->where('ID = '.intval($data['ID']).' AND PublisherId = '.intval($data['PublisherId']))->findone();
			if(!$row)
				return -2;
		}

		$db_d = array();
		$db_d['Domain'] = isset($data['site_domain'])&&!empty($data['site_domain'])?$data['site_domain']:'';
		$db_d['Domain'] = trim($db_d['Domain']);
		$db_d['Alias'] = isset($data['site_alias'])&&!empty($data['site_alias'])?$data['site_alias']:'';
		$db_d['Alias'] = trim($db_d['Alias']);
		$db_d['SiteType'] = isset($data['site_type'])&&!empty($data['site_type'])?$data['site_type']:0;
		$db_d['SiteType'] = intval($db_d['SiteType']);
		$db_d['TargetCountry'] = isset($data['site_country'])&&!empty($data['site_country'])?$data['site_country']:'';
		$db_d['TargetCountry'] = intval($db_d['TargetCountry']);
		$db_d['Description'] = isset($data['site_desc'])&&!empty($data['site_desc'])?$data['site_desc']:'';
		$db_d['Description'] = trim($db_d['Description']);

		if($data['ID'] > 0){
			$ID = $data['ID'];
			$db_d['LastUpdateTime'] = date('Y-m-d H:i:s');
			$res = $this->table('publisher_account')->where('ID = '.intval($data['ID']).' AND PublisherId = '.intval($data['PublisherId']))->update($db_d);
			if(!$res)
				return -3;
		}else{
			$db_d['AddTime'] = date('Y-m-d H:i:s');
			$db_d['PublisherId'] = intval($data['PublisherId']);

			$res = $this->table('publisher_account')->insert($db_d);
			$ID = $this->objMysql->getLastInsertId();
			if(empty($ID)){
				return -4;
			}
			$ApiKey = md5($ID);

			$res2 = $this->table('publisher_account')->where('ID = '.intval($ID))->update(array('ApiKey'=>$ApiKey));
			if(!$res2)
				return -5;
		}

		return $ID;
	}

	function edit_publisher_site($data)
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
			$rs['msg'] = "Traffic Demographics cannot be blank";
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
			$rs['msg'] = "Core business cannot be blank";
			return $rs;
		}
		$geoBreakdown = trim($geoBreakdown, "+");
		$SiteTypeNew = trim($SiteTypeNew, "+");
		$db_d = array();
		$db_d['Domain'] = isset($data['site_domain']) ? trim($data['site_domain']) : '';
		$db_d['Alias'] = isset($data['site_alias']) ? trim($data['site_alias']) : '';
		$db_d['Name'] = $db_d['Alias'];
		$db_d['GeoBreakdown'] = $geoBreakdown;
		$db_d['SiteTypeNew'] = $SiteTypeNew;
		$db_d['SiteOption'] = $siteOption;
		$db_d['Description'] = isset($data['site_desc']) ? trim($data['site_desc']) : '';
		$publisherId = isset($data['PublisherId']) ? intval($data['PublisherId']) : 0;
		if ($data['ID'] > 0) {
			$ID = $data['ID'];
			$db_d['LastUpdateTime'] = date('Y-m-d H:i:s');
			$res = $this->table('publisher_account')->where('ID = ' . intval($data['ID']))->update($db_d);
			$sql = "SELECT PublisherId FROM publisher_account pa where pa.ID = '".$ID."'";
			$pa = $this->getRows($sql);
			$publisherId = isset($pa[0]['PublisherId']) ? $pa[0]['PublisherId'] : 0;
			if (!$res){
				$rs['code'] = 2;
				$rs['errorId'] = "commonHasError";
				$rs['msg'] = "Update error. Please refresh and try again.";
				return $rs;
			}
		} else {
			$db_d['AddTime'] = date('Y-m-d H:i:s');
			$db_d['PublisherId'] = $publisherId;
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
		$this->changePublisherSiteOption($publisherId);
		$rs['code'] = 1;
		$rs['msg'] = "Success.";
		return $rs;
	}

	//修改publisher的siteoption
	function changePublisherSiteOption($pid){
		$sql = "SELECT distinct(SiteOption) FROM publisher_account pa where pa.PublisherId = '".$pid."'";
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
		$this->table('publisher')->where('ID = ' . $pid)->update(array('SiteOption' => $siteOptionP));

	}

	function change_password($data){
		$row = $this->table('publisher')->where('ID = '.intval($data['ID']).' AND UserPass = "'.md5($data['pub_pwd_old']).'"')->findone();
		if(empty($row))
			return 1;

		if(!preg_match('/^.{8,}$/', $data['pub_pwd'])){
			return 2;
		}

		if(!isset($data['pub_pwd_ag']) || $data['pub_pwd_ag'] != $data['pub_pwd']){
			return 3;
		}

		$update_d = array();
		$update_d['UserPass'] = md5($data['pub_pwd']);
		$res = $this->table('publisher')->where('ID = '.intval($data['ID']))->update($update_d);
		if(!$res)
			return 4;
		return 0;
	}

	function get_account_info($uid){
		$account = array();
		$account['base'] = $this->table('publisher')->where('ID = '.intval($uid))->findone();
		$account['site'] = $this->table('publisher_account')->where('PublisherId = '.intval($uid))->find();

		return $account;
	}

	function login($data){
		$account = isset($data['pub_account'])?$data['pub_account']:'';
		$account = trim($data['pub_account']);
		if(empty($account)){
			return 1;
		}

		$password = isset($data['pub_pwd'])?$data['pub_pwd']:'';
		if(empty($password)){
			return 2;
		}

		$row = $this->table('publisher')->where('UserName = "'.addslashes($account).'" AND UserPass = "'.md5($password).'"')->findone();
		if(!$row){
			return 3;
		}

		// set session/cookie for user
		$_SESSION['u'] = $row;
		return 0;
	}

	function logout(){
		unset($_SESSION['u']);
	}

	function get_login_user(){
		if(isset($_SESSION['u'])){
			return $_SESSION['u'];
		}
		return null;
	}

}