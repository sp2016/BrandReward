<?php
class LinkFeed_64_Effiliation
{
	private $aff_id;

	function __construct($aff_id,$oLinkFeed)
	{	
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;	
		$this->aff_id = $aff_id;
		if(SID == 'bdg01')
			$this->token = '06N25sEaz07iyTXl1czKMuGwRb90xLMy';
		else 
			$this->token = 'uttyjD1Pe4IqG6VncIKisVyokclFrheZ';
	}
		

    function getCouponFeed()
    {
        $check_date = date('Y-m-d H:i:s');
    	$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
    	
		$objProgram = new ProgramDb();
		$sql = "SELECT NAME, IDINAFF, SECONDIDINAFF FROM program WHERE AFFID = {$this->aff_id} AND SECONDIDINAFF <> ''" ;
		$row = $objProgram->objMysql->getRows($sql);
		$advs = array();
		foreach ($row as $k=>$v){
			$advs[$v['SECONDIDINAFF']][strtolower($v['NAME'])] = $v['IDINAFF'];
		}


        $urls = array(266 => array('country'=>'fr', 'lang'=>'fr', 'Code de réduction'=>'COUPON'),
                      269 => array('country'=>'ge', 'lang'=>'de', 'Gutscheincode' =>'COUPON'),
                      272 => array('country'=>'uk', 'lang'=>'en', 'Voucher code'=>'COUPON'),
                      );
    	$request = array('method'=>'get');
    	
    	/* 
    	 * get idinaff(id_programme) => SecondIdInAff(id_affilieur)
    	 * return = array(123 => array(IdInAff => 123, SecondIdInAff => 321));    	
    	 */ 
    	//$objProgram = new ProgramDb();
    	//$idinaff_map = $objProgram->getSecondIdInAff($this->info["AffId"]);

        $links = array();
        foreach ($urls as $sourceid=> $u) {
    	    $r = $this->oLinkFeed->GetHttpResult( "http://apiv2.effiliation.com/apiv2/commercialtrades.json?key=".urlencode($this->token)."&country={$u['country']}&lg={$u['lang']}&filter=active",$request);
    	    $data = json_decode($r['content'], true);
	        if (!$data) {
                echo $r['content']."\n";
	            continue;
            }

            if (count($data['supports']) == 0)
                continue;

            foreach ($data['supports'] as $v) {
            	//get indaff
            	$programid = $v['id_affilieur'];
            	if (isset($advs[$programid])) {
            		if (isset($advs[$programid][strtolower($v['nomprogramme'])]))
            			$programid = $advs[$programid][strtolower($v['nomprogramme'])];
            		else
            			list(, $programid) = each($advs[$programid]);
            	}
                

                $link = array(
                         "AffId" => $this->info["AffId"],
                         "AffMerchantId" => $programid,
                         "AffLinkId" => $v['id_lien'],
                         "LinkName" =>  $v['nom'] != ""? $v['nom'] : $v['intitule'],
                         "LinkDesc" =>  $v['description'],
                         "LinkStartDate" => preg_replace('/^([\d]{2})\/([\d]{2})\/([\d]{4}) ([\d:]+).*/', '\3-\2-\1 \4', $v['date_debut']),
                         "LinkEndDate" =>   preg_replace('/^([\d]{2})\/([\d]{2})\/([\d]{4}) ([\d:]+).*/', '\3-\2-\1 \4', $v['date_fin']),
                         "LinkPromoType" => isset($u[$v['type']])? $u[$v['type']] : 'DEAL',
                         "LinkHtmlCode" => $v['code'],
                         "LinkCode" => isset($u[$v['type']])? $v['intitule'] : '',
                         "LinkOriginalUrl" => $v['url_affilieur'],
                         "LinkImageUrl" => $v['url_logo'],
                         "LinkAffUrl" => $v['url_redir'],
                         "DataSource" => $sourceid,
                         "IsDeepLink" => 'UNKNOWN',
                         "Type"       => 'promotion'
                        );
                  
                if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
                    continue;
                
                $links[] = $link;
                $arr_return["AffectedCount"] ++;

            }
        
        }
        if(count($links) > 0){
            $c_links = array_chunk($links,100);
            foreach ($c_links as $links) {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
            }
        }
        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
        return $arr_return;
    }

	function GetProgramFromAff()
	{	print_r($this->info);exit;
		$closedArr = array();
		$refusedArr = array();
		$mergeArr = array();
		$crossIdArr = array();
		$onlyClosedIdArr = array();
		$onlyRefusedIdArr = array();
		$matches = array();
		$prgmArr = array();
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$objProgram = new ProgramDb();
		$request = array("AffId" => $this->info["AffId"],"method" => "get");
		$status = array('active','inactive', 'pending', 'unregistered', 'closed', 'refused', 'recommendation');
		foreach ($status as $v){
			$this->GetProgramByCsv($v);
		}
		//-------------------closed和refused需要另外计算，因为两者的交叉影响了statusInAff和partnerShip两个字段--------------------------------------
		/*$closedArr = $this->GetProgramByCsv('closed');
		$refusedArr = $this->GetProgramByCsv('refused');
		$onlyClosedIdArr = array_diff(array_keys($closedArr), array_keys($refusedArr));
		$onlyRefusedIdArr = array_diff(array_keys($refusedArr),array_keys($closedArr));
	    $crossIdArr = array_intersect(array_keys($closedArr),array_keys($refusedArr));
	    $tempArr = $closedArr;
	    foreach ($onlyRefusedIdArr as $v){
	    	$tempArr[$v] = $refusedArr[$v];
	    }
		$mergeArr = $tempArr;//两集合的并集
		foreach ($mergeArr as $k => $v){
			$prgmArr[$k]['IdInAff'] = $k;
			$prgmArr[$k]['AffId'] = $this->info["AffId"];
			$prgmArr[$k]['Description'] = addslashes($v['description']);
			$prgmArr[$k]['Homepage'] = addslashes($v['url']);
			$prgmArr[$k]['AffDefaultUrl'] = addslashes($v['url_tracke']);
			$prgmArr[$k]['Name'] = addslashes($v['nom']);
			$prgmArr[$k]['TargetCountryExt'] = addslashes($v['pays']);
			$prgmArr[$k]['CommissionExt'] = addslashes($v['remuneration']);
			$prgmArr[$k]['LastUpdateTime'] = date("Y-m-d H:i:s");
			preg_match_all('#.*\/(.*)#', $v['responsable'],$matches);
			$prgmArr[$k]['Contacts'] = isset($matches[1][0])?'Email: '.addslashes(trim($matches[1][0])) : 'Email: ';
			if(in_array($k, $onlyClosedIdArr)){
				$prgmArr[$k]['Partnership'] = 'NoPartnership';
			}else{
				$prgmArr[$k]['Partnership'] = 'Declined';
			}
			if(in_array($k, $onlyRefusedIdArr)){
				$prgmArr[$k]['StatusInAff'] = 'Active';
			}else{
				$prgmArr[$k]['StatusInAff'] = 'Offline';
			} 
			if($v['mobile'] == 'No' && $v['applimobile'] == 'No'){//支持移动端自适应或者有app，都算作mobilefriendly
				$prgmArr[$v['id_programme']]['MobileFriendly'] = 'No';
			}else{
				$prgmArr[$v['id_programme']]['MobileFriendly'] = 'Yes';
			}
		}
		$objProgram->updateProgram($this->info["AffId"], $prgmArr);
		$prgmArr = array();*/

		
			//-------------------DeepUrl字段需要另外从页面爬取----------------------------------
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true
		//$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		$result = $this->oLinkFeed->GetHttpResult('http://www.effiliation.com/affiliev2/secure/factory.html?tab=deeplink',$request);//有必要写$request数组，因为curl需要通过$request中的AffId定位到其对应的cookie文件
		$result = $this->oLinkFeed->ParseStringBy2Tag($result['content'], array('input-with-feedback no-right-padding not-zero', 'input-with-feedback no-right-padding not-zero'), 'input-with-feedback no-right-padding not-zero');
		preg_match_all('#value=\"(\d*)\"#', $result,$matches);
		unset($matches[1][0]);
		foreach($matches[1] as $v){
			/*$requestLoad = $request;
			$requestLoad['method'] = 'post';
			$requestLoad['postdata'] = 'id_program='.$v;
			$loadArr = $this->oLinkFeed->GetHttpResult('http://www.effiliation.com/affiliev2/secure/ajaxloaddeeplinks.html',$requestLoad);
			preg_match_all('#value=\"([1-9]\d*)\"#', $loadArr['content'],$matches);
			$deeplink = $matches[1][0];
			$requestGetDeeplink = $request;
			$requestGetDeeplink['method'] = 'post';
			$requestGetDeeplink['postdata'] = 'program='.$v.'&deeplink='.$deeplink.'&url=&code=&urlencoded=1';
			$getArr = $this->oLinkFeed->GetHttpResult('http://www.effiliation.com/affiliev2/secure/ajaxgetcodedeeplink.html',$requestGetDeeplink);*/
			
			
			//$prgmArr[$v]['DeepUrl'] = $getArr['content'];//DeepUrl字段目前还没有！！！
			$prgmArr[$v]['AffId'] = $this->info["AffId"];
			$prgmArr[$v]['IdInAff'] = $v;
			$prgmArr[$v]['LastUpdateTime'] = date("Y-m-d H:i:s");
			$prgmArr[$v]['SupportDeepUrl'] = 'YES';
		}
		$objProgram->updateProgram($this->info["AffId"], $prgmArr);
		
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByCsv($prgmStatus){
		$objProgram = new ProgramDb();
		$prgmArr = array();
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"$prgmStatus" . ".dat","cache_merchant");//返回.cache文件的路径
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)) //fileCacheIsCached函数检查$cache_file是否存在。cache文件的命名精确为YDH，精确到时，意思就是，一个小时之内爬取多次，用的都是同一个cache文件
		{	
			$request["method"] = "get";
			//$strUrlAllMerchant = "http://apiv2.effiliation.com/apiv2/programs.csv?key={$this->token}&filter=".$prgmStatus."&lg=en&fields=0110010000001011011010001000000000000000000000000000000000000000000000000000000000000000000000000000101";
			$strUrlAllMerchant = "http://apiv2.effiliation.com/apiv2/programs.csv?key={$this->token}&filter=".$prgmStatus."&lg=en&fields=0110010000011011011011101000000000000000000000000000000000000000000000000000000000000000000000010000101";
			$r = $this->oLinkFeed->GetHttpResult($strUrlAllMerchant,$request);
			$result = $r["content"];//所有“csv文件中的program信息”
			$result = mb_convert_encoding($result,'UTF-8','CP1252');//西欧编码转utf8
			$this->oLinkFeed->fileCachePut($cache_file,$result);//生成.cache文件,并将cvs数据写入此文件
		}
		if(!file_exists($cache_file)) mydie("die: merchant csv file does not exist. \n");
		//Open CSV File
		$fhandle = fopen($cache_file, 'r');//只读方式打开文件
		$first = true;
		while($line = fgetcsv($fhandle, 50000,'|'))//fgetcsv函数返回csv文件的一行，while循环csv中所有记录
		{
			if($first)
			{
				$arr_title = $line;
				$col_count = sizeof($arr_title);
				$first = false;
				continue;
			}
			if(sizeof($line) != $col_count)
			{
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				continue;
			}
			$row = array();
			foreach($arr_title as $i => $title){
				$row[$title] = $line[$i];//$row是一个存有当前记录的title和值的关联数组
			}
			//if(!in_array($prgmStatus,array('closed','refused'))){
			//print_r($row);exit;
				$prgmArr[$row['id_programme']]['IdInAff'] = $row['id_programme'];
				$prgmArr[$row['id_programme']]['AffId'] = $this->info["AffId"];
				$prgmArr[$row['id_programme']]['Description'] = addslashes($row['description']);
				$prgmArr[$row['id_programme']]['Homepage'] = addslashes($row['url']);
				$prgmArr[$row['id_programme']]['AffDefaultUrl'] = addslashes(str_replace('(new)','',$row['url_tracke']));
				$prgmArr[$row['id_programme']]['Name'] = addslashes($row['nom']);
				$prgmArr[$row['id_programme']]['TargetCountryExt'] = addslashes($row['pays']);
				$prgmArr[$row['id_programme']]['CommissionExt'] = addslashes($row['remuneration']);
				//$prgmArr[$row['id_programme']]['StatusInAff'] = 'Active';
				//preg_match_all('#.*\/(.*)#', $row['responsable'],$matches);
				$prgmArr[$row['id_programme']]['Contacts'] = addslashes($row['responsable']);
				$prgmArr[$row['id_programme']]['LastUpdateTime'] = date("Y-m-d H:i:s");
				$prgmArr[$row['id_programme']]['SecondIdInAff'] = addslashes($row['id_affilieur']);
				$prgmArr[$row['id_programme']]['CategoryExt'] = addslashes($row['categories']);
				$prgmArr[$row['id_programme']]['CookieTime'] = addslashes($row['dureecookies']);
				$prgmArr[$row['id_programme']]['EPCDefault'] = addslashes($row['epc']);
				
				/*if($prgmStatus == 'inactive' || $prgmStatus == 'unregistered'){
					$prgmArr[$row['id_programme']]['StatusInAff'] = 'Offline';
				}elseif($prgmStatus == 'active'){
					$prgmArr[$row['id_programme']]['StatusInAff'] = 'Active';
				}elseif($prgmStatus == 'pending'){
					$prgmArr[$row['id_programme']]['StatusInAff'] = 'Offline';
				}*/
				
				$prgmArr[$row['id_programme']]['StatusInAff'] = 'Active';
				if($prgmStatus == 'closed'){
					$prgmArr[$row['id_programme']]['StatusInAff'] = 'Offline';
				}
				
				
				if($row['mobile'] == 'No' && $row['applimobile'] == 'No'){//支持移动端自适应或者有app，都算作mobilefriendly
					$prgmArr[$row['id_programme']]['MobileFriendly'] = 'No';
				}else{
					$prgmArr[$row['id_programme']]['MobileFriendly'] = 'Yes';
				}
				if($row['etat'] == 'Joined'){
					$prgmArr[$row['id_programme']]['Partnership'] = 'Active';
				}elseif($row['etat'] == 'Refused'){
					$prgmArr[$row['id_programme']]['Partnership'] = 'Declined';
				}elseif($row['etat'] == 'Pending'){
					$prgmArr[$row['id_programme']]['Partnership'] = 'Pending';
				}else{
					$prgmArr[$row['id_programme']]['Partnership'] = 'NoPartnership';
				}



		 		if(count($prgmArr) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $prgmArr);
					$prgmArr = array();
				} 
			//}elseif($prgmStatus == 'closed'){
			//	$closedArr[$row['id_programme']] = $row;
			//}elseif($prgmStatus == 'refused'){
			//	$refusedArr[$row['id_programme']] = $row;
			//}
		}//while
		
 		 if(count($prgmArr)){//记录数也许不是100的整数，所有会有余下的没有插进数据库的记录，在这里进行处理
			$objProgram->updateProgram($this->info["AffId"], $prgmArr);
			$prgmArr = array();
		}
//		if($prgmStatus == 'closed'){
//			return $closedArr;
//		}elseif($prgmStatus == 'refused'){
//			return $refusedArr;
//		}
	}

	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);

		if(count($prgm) > 30){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}

	function GetAllLinksByAffId()
	{
		echo "Start Curl :".date("Y-m-d H:i:s")."\n";
		$check_date = date('Y-m-d H:i:s');
		$count_end = 0;
		do{

			$datum = $this->getCurlLinksContent();
			$datum = json_decode($datum,true);
			$datum = $datum['links'];

			$count = 0;

			if(count($datum) == 0)
				break;
			foreach($datum as $data){
				if(!array_key_exists('code',$data) || $data['code'] == ''){
					$data = $this->getCurlLinksCounter($data['id_session'],$data['id_lien']);
//					var_dump($data);
					$data = json_decode($data,true);
					$data = $data['counters'][0];
					if($data['id_compteur'] == '' || $data['id_compteur'] == null){
						//TODO
					}
					$count++;
					unset($data);
				}
			}
			unset($datum);

			if($count_end >= 3)
				break;
			$count_end++;
		} while($count != 0);

		echo "End Curl :".date("Y-m-d H:i:s")."\n";

		if(isset($url)){
			unset($url);
		}
		$url = "http://apiv2.effiliation.com/apiv2/programs.json?key={$this->token}&filter=all";
		if(isset($curl_opts)){
			unset($curl_opts);
		}
		$curl_opts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_RETURNTRANSFER => true,
		);
		$ch = curl_init($url);
		curl_setopt_array($ch, $curl_opts);
		$programs_links_tmp1 = curl_exec($ch);
		curl_close($ch);
		$programs_links_tmp2 = json_decode($programs_links_tmp1,true);
		$programs_links_tmp3 = $programs_links_tmp2['programs'];
		$programs_link = array();
		foreach($programs_links_tmp3 as $c){
			if(!in_array($c['id_programme'],$programs_link[$c['id_affilieur']])){
				$programs_link[$c['id_affilieur']][] = $c['id_programme'];
			}
		}


		if(isset($datum))
			unset($datum);
		$datum = $this->getCurlLinksContent();
		$datum = json_decode($datum,true);
		$datum = $datum['links'];

		$link_mer_ids = array();
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);

		foreach($datum as $data){
			$link['LinkPromoType'] = 'link';
			$link['AffId'] = $this->info["AffId"];
			$link['AffLinkId'] = $data['id_lien'];

			$link['LinkStartDate'] = !empty($data['date_debut']) ? date('Y-m-d H:i:s',strtotime($data['date_debut'])):'';
			$link['LinkEndDate'] = !empty($data['date_fin'])?date('Y-m-d H:i:s',strtotime($data['date_fin'])):'';
			$link['LinkName'] = $data['nom']?$data['nom']:' ';

			$link['LinkHtmlCode'] = '';
			$link['LinkImageUrl'] = '';
			$link['LastUpdateTime'] = date('Y-m-d H:i:s');
			$link['Type'] = 'link';

			//url_redir
			preg_match('/<a href="(.*?)"/',$data['code'],$linkurl);
			if(isset($linkurl[1]) && $linkurl[1]){
				$link['LinkAffUrl'] = $linkurl[1];
			}else{
				$link['LinkAffUrl']='';
				continue;
			}

			$link['LinkDesc'] = '';

			$link['LinkCode'] = '';


			foreach($programs_link[$data['id_affilieur']] as $value){
				$link['AffMerchantId'] = $value;

				//		var_dump(count($program));
				if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
					continue;
				$link['DataSource'] = 272;

				$arr_return['AffectedCount']++;
				$links[] = $link;
				$link_mer_ids[$link['AffMerchantId']][$link['AffLinkId']] = $link['AffLinkId'];
				unset($link);

			}

			if(count($links,0) >= 100){
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				unset($links);
			}
		}
		if(count($links,0) >= 0){
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;

	}

	function getCurlLinksContent(){
		$url = "http://apiv2.effiliation.com/apiv2/links.json?key={$this->token}&filter=mines&lg=en";
		$curl_opts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_RETURNTRANSFER => true,
		);
		$ch = curl_init($url);
		curl_setopt_array($ch, $curl_opts);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	function getCurlLinksCounter($id_session,$id_link){
		$url = "http://apiv2.effiliation.com/apiv2/counter.json?key={$this->token}&session_id=".$id_session."&link_id=".$id_link;
		$curl_opts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_RETURNTRANSFER => true,
		);
		$ch = curl_init($url);
		curl_setopt_array($ch, $curl_opts);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

}

