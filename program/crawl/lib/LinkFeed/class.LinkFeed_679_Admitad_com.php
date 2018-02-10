<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_679_Admitad_com
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg02'){
			$this->Identifier = '01a4abea7cb2fed8e518ee93d4892a';
			$this->Secret_key = '9eb5fe166ecec7f2a9932e1f18e787';
			$this->Websites = array('Brandreward' => '566339');
		}else{
			$this->Identifier = '439ea72160b340297ecf7bbc125dc6';
			$this->Secret_key = 'f54cd039fc8c5c30573cf654aa3836';
			$this->Websites = array(
                'fyvor.com' => '563449',
                'frcodespromo' => '627731',
                'Codespromofr.com' => '627747',
                'PromosPro IN' => '684203',
                'AnyCodes.com(formerly PromosPro.com)' => '717393',
                'Promokodo.ru' => '756113'
			);
		}

		$this->partnership_priority_map = array(
            'Removed' => 1,
            'Expired' => 2,
            'Declined' => 3,
            'NoPartnership' => 4,
            'Pending' => 5,
            'Active' => 6,
        );
		$this->islogined = false;
	}

	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		
		$offset = 0;
		$limit = 500;
		
		//step 1 , authorization
		echo "start authorization\n\r";
		$data_b64_encoded = base64_encode($this->Identifier . ':' . $this->Secret_key);
		$query = array(
				'client_id' => $this->Identifier,
				'scope' => 'coupons_for_website',
				'grant_type' => 'client_credentials'
		);
		$ch = curl_init('https://api.admitad.com/token/');
		$curl_opts = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('Authorization: Basic ' . $data_b64_encoded),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($query)
		);
		curl_setopt_array($ch, $curl_opts);
		$reponseToken = curl_exec($ch);
		curl_close($ch);
		$tokenArr = json_decode($reponseToken,true);
		//print_r($tokenArr);exit;
		$access_token = $tokenArr['access_token'];
		
		//step 1 , get coupons by website
		foreach ($this->Websites as $Website => $WebsiteID){
			echo "start get coupons by $Website,WebsiteID is $WebsiteID\n\r";
			while(1){
				$ch = curl_init("https://api.admitad.com/coupons/website/{$WebsiteID}/?offset={$offset}&limit={$limit}");
				$curl_opts = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token),
				);
				curl_setopt_array($ch, $curl_opts);
				$reponseCoupons = curl_exec($ch);
				curl_close($ch);
				$coupons = json_decode($reponseCoupons,true);
				//var_dump($coupons);exit;
				$lastNum = count($coupons['results']);
				foreach ($coupons['results'] as $v)
				{
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['campaign']['id'],
							"AffLinkId" => $v['id'],
							"LinkName" => trim($v['name']),
							"LinkDesc" => trim($v['description']),
							"LinkStartDate" => str_replace('T', ' ', trim($v['date_start'])),
							"LinkEndDate" => str_replace('T', ' ', trim($v['date_end'])),
							"LinkPromoType" => 'COUPON',
							"LinkHtmlCode" => '',
							"LinkCode" => trim($v['promocode']),
							"LinkOriginalUrl" => trim($v['frameset_link']),
							"LinkImageUrl" => trim($v['image']),
							"LinkAffUrl" => trim($v['goto_link']),
							"DataSource" => '350',
							"IsDeepLink" => 'UNKNOWN',
							"Type"       => 'promotion'
					);
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
					if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
						continue;
					$arr_return["AffectedCount"] ++;
					$links [] = $link;
					if(sizeof($links) > 100)
					{
						$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
						$links = array();
					}
				}
				$offset += 500;
				if($lastNum < 500) break;
			}
			echo "finish get coupons by $Website\n\r";
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		echo sprintf("get coupon by api...%s link(s) found.\n", $arr_return['AffectedCount']);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	
    function login($try = 1)
	{
	    
		if ($this->islogined) {
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		
		$this->oLinkFeed->clearHttpInfos($this->info['AffId']);//删除缓存文件，删除httpinfos[$aff_id]变量
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => 'get',
		);
		$loginHtml = $this->oLinkFeed->GetHttpResult("https://www.admitad.com/en/sign_in/?next=".urlencode('https://help.admitad.com/en/'),$request);
		preg_match('/<input type=\'hidden\' name=\'csrfmiddlewaretoken\' value=\'(.+)\' \/>/', $loginHtml['content'],$matches);
	    $loginToken = $matches[1];
	    $this->info['AffLoginPostString'] .= "&csrfmiddlewaretoken=".urlencode($loginToken)."&next=";
	    
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => $this->info["AffLoginMethod"],
			"postdata" => $this->info["AffLoginPostString"],
		    "addheader"=> array('referer:https://www.admitad.com/en/sign_in/?next=https%3A//www.admitad.com/en/webmaster/'),
		    "no_ssl_verifyhost" => true,
		);
		$arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
				
		if ($arr["code"] == 200) {
			if (stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false) {
				echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
				$this->islogined = true;
				return true;
			}
		}
		
		if (!$this->islogined) {
			if ($try < 0) {
				mydie("Failed to login!");
			} else {
				echo "login failed ... retry $try...\n";
				sleep(30);
				$this->login(--$try);
			}
		}
	}
	
	function GetAllProductsByAffId(){
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    $request = array("AffId" => $this->info["AffId"], "method" => "get");
	    
	    //step 1:login
	    $this->login();
	    foreach ($this->Websites as $Website => $WebsiteID){
	        
	        $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	        $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	        $productNumConfigAlert = '';
	        $isAssignMerchant = FALSE;
	        
	        $i=0;
	        foreach ($arr_merchant as $merchant){
	            
	            $merchantId = $merchant['IdInAff'];
	            $crawlMerchantsActiveNum = 0;
	            $setMaxNum  = isset($productNumConfig[$merchant['IdInAff']]) ? $productNumConfig[$merchant['IdInAff']]['limit'] :  100;
	            $isAssignMerchant = isset($productNumConfig[$merchant['IdInAff']]) ? TRUE : FALSE;
	            echo $merchantId.PHP_EOL;
	            
	            $request['addheader'] = array("referer:https://www.admitad.com/en/webmaster/websites/$WebsiteID/");
	            $originalHtml = $this->oLinkFeed->GetHttpResult("https://www.admitad.com/en/webmaster/websites/$WebsiteID/products/original/", $request);
	            preg_match('/var csrfmiddlewaretoken = "(.*?)";/i', $originalHtml['content'],$matches);
	            if(!isset($matches[1])) die('get content html error!');
	            $request['addheader'] = array("referer:https://www.admitad.com/en/webmaster/websites/$WebsiteID/products/original/","x-csrftoken:{$matches[1]}","x-requested-with:XMLHttpRequest");
	            $feedList =  $this->oLinkFeed->GetHttpResult("https://www.admitad.com/en/webmaster/products/original/ajax/feeds/?advcampaign={$merchant['IdInAff']}", $request);
	            $feedArr = json_decode($feedList['content'],true);
	            foreach ($feedArr['feeds'] as $feedValue){
	                $TotalCount = $feedValue['products_count'];
	                if($feedValue['products_count'] > 20000) continue;
	                //download feed data
	                $fileName = 'product_feed_'.$merchant['IdInAff'].'_'.$feedValue['id'].'.csv';
	                $product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileName, "product", true);
	                if(!$this->oLinkFeed->fileCacheIsCached($product_file)){
	                    
	                    //get download url
	                    $downInfo =  $this->oLinkFeed->GetHttpResult("https://www.admitad.com/en/webmaster/products/original/ajax/export_link/?feed={$feedValue['id']}&website=$WebsiteID&template=&extension=csv&products_type=original&last_import=&only_sale=true&currency=", $request);
	                    $downArr = json_decode($downInfo['content'],true);
	                    $downUrl = $downArr['link'];
	                    echo $downUrl.PHP_EOL;
	                    $r = $this->oLinkFeed->GetHttpResult($downUrl,$request);
	                    $this->oLinkFeed->fileCachePut($product_file,$r['content']);
	                }
	                //if file too big, continue;
	                $FileSize = filesize($product_file);
	                if($FileSize>10000000) continue;
	                
	                $productData = array();
	                $file = fopen($product_file,"r");
	                while(! feof($file))
	                {
	                    $productData[] = fgetcsv($file,'',';','"');
	                }
	                fclose($file);
	                 
	                foreach ($productData as $pk=>$pValue){
	                    if($pk == 0) continue;
	                    if(!isset($pValue[19])) continue;
	                    if(!$pValue[3]) continue;
	                    
	                    $AffProductId = $pValue[6];
	                    if(!$AffProductId) continue;
	                    $ProductImage = $pValue[13];
	                    $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchantId}_".urlencode($AffProductId).".png", PRODUCTDIR);
	                    if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
	                    {
	                        $file_content = $this->oLinkFeed->downloadImg($ProductImage);
	                        if(!$file_content) //下载不了跳过。
	                            continue;
	                        $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	                    }
	                    
	                    $link = array(
	                        "AffId" => $this->info["AffId"],
	                        "AffMerchantId" => $merchantId,
	                        "AffProductId" => $AffProductId,
	                        "ProductName" => html_entity_decode(addslashes($pValue[9])),
	                        "ProductCurrency" => $pValue[3],
	                        "ProductPrice" =>   $pValue[14],
	                        "ProductOriginalPrice" =>'',
	                        "ProductRetailPrice" =>'',
	                        "ProductImage" => addslashes($ProductImage),
	                        "ProductLocalImage" => addslashes($product_path_file),
	                        "ProductUrl" => $pValue[19],
	                        "ProductDestUrl" => '',
	                        "ProductDesc" => html_entity_decode(addslashes($pValue[5])),
	                        "ProductStartDate" => '',
	                        "ProductEndDate" => '',
	                    );
	                    $links[] = $link;
	                    $crawlMerchantsActiveNum ++;
	                    if (count($links) >= 100)
	                    {
	                        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                        $links = array();
	                    }
	                    //大于最大数跳出
	                    if($crawlMerchantsActiveNum >= $setMaxNum){
	                        break;
	                    }
	                }
	                
	            }
	            if($isAssignMerchant){
	                $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchant['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
	            }
	        }
	    }
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo $productNumConfigAlert.PHP_EOL;
	    return $arr_return;
	    
	}
	
	function GetStatus()
	{
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}
	
	function GetProgramFromAff()
	{
	    $this->login();exit;
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$program_num = 0;
		$arr_prgm = array();
		$myoffset = 0;
		$alloffset = 0;
		$arr_prgmID_active = array();				//状态和合作关系都是active的program
		$arr_prgmID_apply = array();				//申请过的program
		$partnership_arr = array();

		//step 1 , get my program
		//Client authorization
		$data_b64_encoded = base64_encode($this->Identifier . ':' . $this->Secret_key);
		$query = array(
				'client_id' => $this->Identifier,
				'scope' => 'advcampaigns_for_website',
				'grant_type' => 'client_credentials'
		);
		$ch = curl_init('https://api.admitad.com/token/');
		$curl_opts = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('Authorization: Basic ' . $data_b64_encoded),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($query)
		);
		curl_setopt_array($ch, $curl_opts);
		$reponseToken = curl_exec($ch);
		curl_close($ch);
		$tokenArr = json_decode($reponseToken,true);
		//print_r($tokenArr);exit;
		$access_token = $tokenArr['access_token'];

		foreach ($this->Websites as $Website => $WebsiteID){
			echo "\tGet Program for website was called $Website start\r\n";
            $myoffset = 0;
			while(1){
				echo "\tStart get Program for website was called ".$Website." ".$myoffset."th\r\n";
				//$ch = curl_init('https://api.admitad.com/advcampaigns/');
				$ch = curl_init("https://api.admitad.com/advcampaigns/website/{$WebsiteID}/?offset={$myoffset}&language=en&limit=100");
				$curl_opts = array(
						CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token),
				);
				curl_setopt_array($ch, $curl_opts);
				$reponseprograms = curl_exec($ch);
				curl_close($ch);
				$affiliate_programs = json_decode($reponseprograms,true);
				//var_dump($affiliate_programs);exit;
				$lastNum = count($affiliate_programs['results']);
		
				foreach ($affiliate_programs['results'] as $v){
						
					$strMerID = $v['id'];
					$arr_prgmID_apply[$strMerID] = 1;
					if(isset($arr_prgmID_active[$strMerID]))
						continue;
					$StatusInAffRemark = $v['connection_status'];
					$Partnership = "NoPartnership";
					if($StatusInAffRemark == 'active')
						$Partnership = 'Active';
					elseif($StatusInAffRemark == 'pending')
						$Partnership = 'Pending';
					elseif($StatusInAffRemark == 'declined')
						$Partnership = 'Declined';
					
					if($v['status'] == 'active')
						$StatusInAff = 'Active';
					elseif($v['status'] == 'disabled')
						$StatusInAff = 'Offline';
					
					if($Partnership == 'Active' && $StatusInAff == 'Active')
						$arr_prgmID_active[$strMerID] = 1;
						
					$desc = $v['description'];
						
					$CountryExt = array();
					foreach($v['regions'] as $Countrys){
						$CountryExt[] = $Countrys['region'];
					}
					$TargetCountryExt = implode("|", $CountryExt);
					if($TargetCountryExt == '00') $TargetCountryExt = 'GLOBAL';
						
					$ReturnDays = $v['goto_cookie_lifetime'];
					if($v['allow_deeplink']){
						$SupportDeepUrl = 'YES';
					}else{
						$SupportDeepUrl = 'NO';
					}
					$currency = $v['currency'];
					$CommissionExt = '';
					$Commission = array();
					foreach ($v['actions'] as $action){
						$Commission[] = $action['type'] . ':' . $action['payment_size'];
					}
					$CommissionExt = implode(';', $Commission);
						
					$CategoryExt = array();
					foreach ($v['categories'] as $categories){
						if(isset($categories['parent']['name'])){
							$CategoryExt[] =  $categories['parent']['name'] . '-' . $categories['name'];
						}
					}
					$CategoryExt = implode(",", $CategoryExt);

                    //删选所有网站里的最佳program合作关系
                    if (isset($partnership_arr[$strMerID]['Partnership']) && !empty($partnership_arr[$strMerID]['Partnership'])) {
                        $old_partnership_priority = $this->partnership_priority_map[$partnership_arr[$strMerID]['Partnership']];
                        $new_partnership_priority = $this->partnership_priority_map[$Partnership];
                        if ($new_partnership_priority > $old_partnership_priority) {
                            $partnership_arr[$strMerID]['Partnership'] = $Partnership;
                        }
                    } else {
                        $partnership_arr[$strMerID] = array(
                            'AffId' => $this->info["AffId"],
                            "IdInAff" => $strMerID,
                            'Partnership' => $Partnership,
                        );
                    }

					$arr_prgm[$strMerID] = array(
							"Name" => addslashes($v['name']),
							"AffId" => $this->info["AffId"],
							"TargetCountryExt" => $TargetCountryExt,
							"IdInAff" => $strMerID,
							"JoinDate" => $v['activation_date'],
							"StatusInAffRemark" => $StatusInAffRemark,
							"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
							"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
							"Description" => addslashes($desc),
							"Homepage" => $v['site_url'],
							"CookieTime" => addslashes($ReturnDays),
							"RankInAff" => $v['rating'],
							//"TermAndCondition" => addslashes($TermAndCondition),
							"SupportDeepUrl" => $SupportDeepUrl,
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							"DetailPage" => "https://www.admitad.com/en/webmaster/websites/{$WebsiteID}/offers/{$strMerID}/#information",
							"AffDefaultUrl" => addslashes($v['gotolink']),
							"CommissionExt" => addslashes($CommissionExt),
							"CategoryExt" => addslashes($CategoryExt),
							"LogoUrl" => addslashes($v['image']),
							"PaymentDays" => $v['max_hold_time'],
					);
					$program_num++;
					if(count($arr_prgm) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
				if(count($arr_prgm)){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				echo "\tFinish get Program for website was called ".$Website." ".$myoffset."th\r\n";
				$myoffset += 100;
				if($lastNum < 100) break;
			}
			echo "\tGet Program for website was called $Website end\r\n";
		}

		//step 2, set programs partnership!
        if (count($partnership_arr)) {
            echo "\tStart to set programs partnership!\r\n";
            $objProgram->updateProgram($this->info["AffId"], $partnership_arr);
            echo "\tSet (" . count($partnership_arr) . ") programs partnership success!\r\n";
            unset($partnership_arr);
        }
		
		//step 3 , get all program
		$data_b64_encoded = base64_encode($this->Identifier . ':' . $this->Secret_key);
		$query = array(
				'client_id' => $this->Identifier,
				'scope' => 'advcampaigns arecords banners websites',
				'grant_type' => 'client_credentials'
		);
		$ch = curl_init('https://api.admitad.com/token/');
		$curl_opts = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('Authorization: Basic ' . $data_b64_encoded),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($query)
		);
		curl_setopt_array($ch, $curl_opts);
		$reponseToken = curl_exec($ch);
		curl_close($ch);
		$tokenArr = json_decode($reponseToken,true);
		//print_r($tokenArr);exit;
		$access_token = $tokenArr['access_token'];
		
		echo "\tGet all Program by api start\r\n";
		while(1){
			$ch = curl_init("https://api.admitad.com/advcampaigns/?offset={$alloffset}&language=en&limit=100");
			//$ch = curl_init("https://api.admitad.com/advcampaigns/website/?offset={$alloffset}&limit=100");
			$curl_opts = array(
					CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token),
			);
			curl_setopt_array($ch, $curl_opts);
			$reponseprograms = curl_exec($ch);
			curl_close($ch);
			$affiliate_programs = json_decode($reponseprograms,true);
			//var_dump($affiliate_programs);exit;
			$lastNum = count($affiliate_programs['results']);
		
			foreach ($affiliate_programs['results'] as $v){
						
				$strMerID = $v['id'];
				if(isset($arr_prgmID_apply[$strMerID]))
					continue;
				$Partnership = "NoPartnership";
				if($v['status'] == 'active')
					$StatusInAff = 'Active';
				elseif($v['status'] == 'disabled')
					$StatusInAff = 'Offline';
						
				$desc = $v['description'];
						
				$CountryExt = array();
				foreach($v['regions'] as $Countrys){
					$CountryExt[] = $Countrys['region'];
				}
				$TargetCountryExt = implode("|", $CountryExt);
				if($TargetCountryExt == '00') $TargetCountryExt = 'GLOBAL';
				
				$ReturnDays = $v['goto_cookie_lifetime'];
				if($v['allow_deeplink'])
					$SupportDeepUrl = 'YES';
				else
					$SupportDeepUrl = 'NO';
				
				//$currency = $v['currency'];
				$CommissionExt = '';
				$Commission = array();
				foreach ($v['actions'] as $action){
					$Commission[] = $action['type'] . ':' . $action['payment_size'];
				}
				$CommissionExt = implode(';', $Commission);
					
				$CategoryExt = array();
				foreach ($v['categories'] as $categories){
					if(isset($categories['parent']['name'])){
						$CategoryExt[] =  $categories['parent']['name'] . '-' . $categories['name'];
					}
				}
				$CategoryExt = implode(" & ", $CategoryExt);
					
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($v['name']),
						"AffId" => $this->info["AffId"],
						"TargetCountryExt" => $TargetCountryExt,
						"IdInAff" => $strMerID,
						"JoinDate" => $v['activation_date'],
						//"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						//"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => $v['site_url'],
						"CookieTime" => addslashes($ReturnDays),
						"RankInAff" => $v['rating'],
						//"TermAndCondition" => addslashes($TermAndCondition),
						"SupportDeepUrl" => $SupportDeepUrl,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						//"DetailPage" => "https://www.admitad.com/en/webmaster/websites/{$WebsiteID}/offers/{$strMerID}/#information",
						//"AffDefaultUrl" => addslashes($v['gotolink']),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
				);
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
			$alloffset += 100;
			if($lastNum < 100) break;
		}
		echo "\tGet all Program by api end\r\n";
	
		
		
		

		echo "\tGet Program by api end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
		
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>