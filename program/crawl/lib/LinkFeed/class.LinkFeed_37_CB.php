<?php
require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_37_CB
{
	var $info = array(
		"ID" => "37",
		"Name" => "CB (ClickBank)",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_37_CB",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByXml();
		//$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByXml()
	{
		echo "\tGet Program by xml file start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => "",
		);
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("Ymd").".zip", "program", true);
 		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$strUrl = 'https://accounts.clickbank.com/feeds/marketplace_feed_v2.xml.zip';
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			if($r['code'] == 200){
				$results = $r['content'];
				$this->oLinkFeed->fileCachePut($cache_file, $results);
				unset($result);
			}
		}

        $zip=new ZipArchive;//新建一个ZipArchive的对象
        if ($zip->open($cache_file)===true){
            $zip->extractTo(dirname($cache_file));//假设解压缩到在当前路径下images文件夹内
            $zip->close();//关闭处理的zip文件
        }
        $xml_file = dirname($cache_file).'/marketplace_feed_v2.xml';
        if ($this->oLinkFeed->fileCacheIsCached($xml_file)) {
            $xml2arr = new XML2Array();
            $content = file_get_contents($xml_file);
            $xml = $xml2arr->createArray($content);
            unset($content);
            //var_dump($xml);exit;
            $Category_arr = array();
            foreach ($xml['Catalog']['Category'] as $v)
            {
                $CategoryExt = $v['Name'];
                foreach ($v['Site'] as $value)
                {
                    if (isset($v['Site']['Id']))
                        $value = $v['Site'];
                    $strMerID = $value['Id'];
                    if (!$strMerID)
                        continue;
                    $RankInAff = $value['PopularityRank'];
                    $strMerName = $value['Title']['@cdata'];
                    $desc = $value['Description']['@cdata'];
                    $JoinDate = $value['ActivateDate'];
                    if (is_numeric($value['Commission']))
                        $CommissionExt = $value['Commission'].'%';
                    else
                        $CommissionExt = $value['Commission'];
                    $arr_prgm[$strMerID] = array(
                        "Name" => addslashes(html_entity_decode(trim($strMerName))),
                        "AffId" => $this->info["AffId"],
                        //"CategoryExt" => addslashes($CategoryExt),
                        "RankInAff" => addslashes($RankInAff),
                        "JoinDate" => $JoinDate,
                        "IdInAff" => $strMerID,
                        "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
                        "Description" => addslashes($desc),
                        "CommissionExt" => addslashes($CommissionExt),
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                    );

                    $Category_arr[$strMerID][] = $CategoryExt;

                    $program_num++;
                    if(count($arr_prgm) >= 100){
                        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                        $arr_prgm = array();
                    }
                }
                if(count($arr_prgm)){
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                    $arr_prgm = array();
                }
            }
            unset($xml);
            foreach ($Category_arr as $strMerID => $Category)
            {
                $CategoryExt_arr[$strMerID] = array(
                    'IdInAff' => $strMerID,
                    'AffId' => $this->info["AffId"],
                    'CategoryExt' => implode(',', $Category),
                );
            }
            $objProgram->updateProgram($this->info["AffId"], $CategoryExt_arr);
            $prgm_count = count($CategoryExt_arr);
        }else {
            mydie("xml file is empty, please check it.\r\n");
        }

        echo "\tGet Program by xml file end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$prgm_count}) program.\r\n";
		
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$arr_prgm_nohomepage = array();
		$program_num = 0;
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "", 
		);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);		
				
		$hasNextPage = true;
		$page = 1;
		$pageNum = 50;
		$date_from = $date_from = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 120 , date("Y")));
		$date_to = date("Y-m-d");
		$c_token = "";
		while($hasNextPage){
			echo "\t page $page.";
			if($page == 1){
				$strUrl = "https://csshot.accounts.clickbank.com/account/mkplSearchResult.htm?dores=true&includeKeywords=";
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
				
				$c_token = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="c_token"', 'value="'), '"'));				
				$request["addheader"] = array("page: $page");
				
			}elseif($c_token){
				$request["addheader"] = array("page: $page");
			
			}else{
				mydie("die: postdata error.\n");
			}
			
			$strUrl = "https://csshot.accounts.clickbank.com/api2/marketplace?c_token=$c_token&mainCategoryId=&subCategoryId=&analyticsDaysBackStart=$date_from&analyticsDaysBackStop=$date_to&gravityEnabled=false&gravityType=HIGHER&gravityV1=&gravityV2=&futureEarningsEnabled=false&futureEarningsType=HIGHER&futureEarningsV1=&futureEarningsV2=&initialEarningsPerSaleEnabled=false&initialEarningsPerSaleType=HIGHER&initialEarningsPerSaleV1=&initialEarningsPerSaleV2=&averageEarningsPerSaleEnabled=false&averageEarningsPerSaleType=HIGHER&averageEarningsPerSaleV1=&averageEarningsPerSaleV2=&percentPerSaleEnabled=false&percentPerSaleType=HIGHER&percentPerSaleV1=&percentPerSaleV2=&percentPerRebillEnabled=false&percentPerRebillType=HIGHER&percentPerRebillV1=&percentPerRebillV2=&activatedEnabled=false&activatedType=BEFORE&activatedV1=&activatedV2=&productLanguages=&productAttributes=&productTypes=&requireAffiUrl=false&requireSpotlight=false&mobileEnabled=false&whitelistVendor=false&includeKeywords=&requireAnalyticsStats=false&resultsPerPage=$pageNum&sortField=POPULARITY&_sort=on&sortReverse=true";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"product_".date("YmdH")."_{$page}.dat","cache_feed");
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
				//print_r($result);
				$this->oLinkFeed->fileCachePut($cache_file,$result);
			}
			
			$xml = new DOMDocument();
			$xml->load($cache_file);			
		
			$page_info = $xml->getElementsByTagName("totalHitCount");
			$total_pages = $page_info->item(0)->nodeValue;
			if($total_pages <= ($page*$pageNum)){
				$hasNextPage = false;
			}			
			$page++;
			
			//parse XML
			$advertiser_list = $xml->getElementsByTagName("details");		
			foreach($advertiser_list as $advertiser)
			{			
				$advertiser_info = array();
				$childnodes = $advertiser->getElementsByTagName("*");
				foreach($childnodes as $node){
					$advertiser_info[$node->nodeName] = trim($node->nodeValue);				
				}				
							
				$strMerID = addslashes(trim($advertiser_info["site"]));
				if(!$strMerID) continue;
				
				$strMerName = trim($advertiser_info["title"]);
				$desc = trim($advertiser_info["description"]);
				$CategoryExt = trim($advertiser_info["category"]);
				
				$CommissionExt = "Stats: Avg $/sale: $".trim($advertiser_info["averageDollarsPerSale"])." | Initial $/sale: $".trim($advertiser_info["initialDollarsPerSale"])." | Avg %/sale: ".trim($advertiser_info["pctPerSale"])."% | Avg Rebill Total: $".trim($advertiser_info["totalRebill"])." | Avg %/rebill: ".trim($advertiser_info["pctPerRebill"])."% | Grav: ".trim($advertiser_info["gravity"]);
				
				$Country = array();
				if($advertiser_info["en"] == "true"){
					$Country[] = "en";
				}
				if($advertiser_info["de"] == "true"){
					$Country[] = "de";
				}
				if($advertiser_info["es"] == "true"){
					$Country[] = "es";
				}
				if($advertiser_info["fr"] == "true"){
					$Country[] = "fr";
				}
				if($advertiser_info["it"] == "true"){
					$Country[] = "it";
				}
				if($advertiser_info["pt"] == "true"){
					$Country[] = "pt";
				}
				$TargetCountryExt = implode("," , $Country);
				
				$RankInAff = $advertiser_info["marketPlaceStarRating"];
				
				$Homepage = "";
				$HomepageUrl = "http://zzzzz.".strtolower($strMerID).".hop.clickbank.net";
				if($tmp_url = $this->oLinkFeed->findFinalUrl($HomepageUrl)){
					if($HomepageUrl != $tmp_url){
						$Homepage = $tmp_url;
					}
				}
				//echo $Homepage;
				
   				$url = "https://accounts.clickbank.com/info/jmap.htm?affiliate=csshot&promocode=&source=&submit=Create&vendor=".strtolower($strMerID)."&results=";   				
   				$r = $this->oLinkFeed->GetHttpResult($url,$request);   				
				$result = $r["content"];
				
				$AffDefaultUrl = $this->oLinkFeed->ParseStringBy2Tag($result, array('<input class="special"', 'value="'), '"');

				if($Homepage){    
					$arr_prgm[$strMerID] = array(
						"Name" => addslashes(html_entity_decode(trim($strMerName))),
						"AffId" => $this->info["AffId"],
						"CategoryExt" => addslashes($CategoryExt),
						"RankInAff" => addslashes($RankInAff),
						//"JoinDate" => $JoinDate,
						"TargetCountryExt" => addslashes($TargetCountryExt),
						"IdInAff" => $strMerID,
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
						"Description" => addslashes($desc),
						"Homepage" => $Homepage,
						"CommissionExt" => addslashes($CommissionExt),					
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"AffDefaultUrl" => addslashes($AffDefaultUrl)
					);
				}else{
					$arr_prgm_nohomepage[$strMerID] = array(
						"Name" => addslashes(html_entity_decode(trim($strMerName))),
						"AffId" => $this->info["AffId"],
						"CategoryExt" => addslashes($CategoryExt),
						"RankInAff" => addslashes($RankInAff),
						//"JoinDate" => $JoinDate,
						"TargetCountryExt" => addslashes($TargetCountryExt),
						"IdInAff" => $strMerID,
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
						"Description" => addslashes($desc),						
						"CommissionExt" => addslashes($CommissionExt),					
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"AffDefaultUrl" => addslashes($AffDefaultUrl)
					);
				}
				
				//print_r($arr_prgm);print_r($arr_prgm_nohomepage);exit;
				$program_num++;
				
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				if(count($arr_prgm_nohomepage) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm_nohomepage);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm_nohomepage);
					$arr_prgm_nohomepage = array();
				}
			}
		}		
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		if(count($arr_prgm_nohomepage)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm_nohomepage);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm_nohomepage);
			unset($arr_prgm_nohomepage);
		}
		
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
