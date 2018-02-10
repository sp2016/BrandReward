<?php
class LinkFeed_223_Skimlinks
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		
		//step 1,login	
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$api_key = "bcdc6eb2381445e529d36c9249465096";
		$account_id = '101853';

		//get domains			
		$hasNextPage = true;			
		$perPage = 200;				
		$page = 0;
		$totalnumFound = 0;
		
		$ignored_domain = array();
		$ignored_domain = $this->getIgnoredMerDomain();
		//print_r($ignored_domain);
		
		$aff_url_keyword = array();
		$aff_url_keyword = $objProgram->getAffiliateUrlKeywords();
		
		// for check if through main aff
		$commint_zero_prgm = array();
		$commint_zero_prgm = $objProgram->getCommIntProgramByAffId($this->info["AffId"]);
		
		// Internal EPC=0 
		$internal_prgm = array();
		/*$fp = fopen("http://couponsn:IOPkjmN1@reporting.megainformationtech.com/dataapi/offline_program.php?affid=".$this->info["AffId"], "r");
		if($fp){
			echo "\t get Internal EPC=0 program succeed.\n";
			$i = 0;
			while(!feof($fp))
			{
				$line = trim(fgets($fp));
				if(!$line) continue;
				$tmp_arr = explode("\t", $line);//Id in Aff, Program Name, Sales, Commission, CR(Commission Rate)
				
				$affid = intval($tmp_arr[0]);				
				$idinaff = trim($tmp_arr[1]);
				
		
				if($affid == $this->info["AffId"]){					
					$internal_prgm[$idinaff] = 1;
					$i++;
				}
			}
			fclose($fp);
			echo "\t get ($i) Internal EPC=0 program\n";
		}else{
			echo "\t Internal EPC=0 program failed.\n";
		}*/
		
		while($hasNextPage){
			$start = $page*$perPage;
			$strUrl = "http://merchants.skimlinks.com/v3/merchants?apikey={$api_key}&account_type=publisher_admin&account_id={$account_id}&limit={$perPage}&offset={$start}";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			
			if($r["code"] != 200) continue;
			
			echo "\t$page";
			
			$result = $r["content"];
			
			$result = json_decode($result);
			//print_r($result);exit;
			
			$numFound = intval($result->num_returned);
			if(!$numFound) break;
			$totalnumFound += $numFound;
			if($perPage > $numFound){
				$hasNextPage = false;
			}
			
			//print_r($result);exit;
			$merchant_list = $result->merchants;
			foreach($merchant_list as $v)
			{
				//$v = $merchants[0];
				$IdInAff = intval($v->merchant_id);	
				if(!$IdInAff) continue;
				
				//print_r($v);
				
				$CommissionExt = $v->calculated_commission_rate * 100 . '%';
				
				$StatusInAffRemark = "";
				$Partnership = "Active";					
				
				$Homepage = $v->domain;
				$desc = array();
				if(isset($v->domains)){
					$domains = $v->domains;
					//print_r($domains);
					foreach($domains as $domain){
						$desc[] = $domain;
						if(isset($ignored_domain[$domain])){
							$StatusInAffRemark = "merchant don't cooperate with skimlinks";
							$Partnership = "NoPartnership";
						}
					}
				}
				$desc = implode(", \r\n", $desc);
									
				$TargetCountryExt = "";
				if(isset($v->countries)){
					$countries = $v->countries;
					foreach($countries as $country){
						if($country){
							$TargetCountryExt = $country;
							break;
						}
					}
				}
				
				$CategoryExt = "";
				if(isset($v->verticals)){
					$categories = $v->verticals;
					foreach($categories as $category){
						if($category){
							$CategoryExt = $category;
							break;
						}
					}
				}
				
				//$AffDefaultUrl = "http://go.redirectingat.com?id=7438X662619&xcust=[SUBTRACKING]&xs=1&url=http%3A%2F%2Fwww.{$Homepage}";
				
				$arr_prgm[$IdInAff] = array(
					"Name" => addslashes((trim($v->name))),
					"AffId" => $this->info["AffId"],
					"CategoryExt" => addslashes($CategoryExt),							
					"TargetCountryExt" => addslashes($TargetCountryExt),
					"Description" => addslashes($desc),
					"IdInAff" => $IdInAff,
					"StatusInAffRemark" => addslashes($StatusInAffRemark),					
					"StatusInAff" => "Active",						//'Active','TempOffline','Offline'							
					"Partnership" => addslashes($Partnership),						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'							
					"Homepage" => addslashes($Homepage),
					"CommissionExt" => addslashes($CommissionExt),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SupportDeepUrl" => 'YES',
					//"AffDefaultUrl" => addslashes($AffDefaultUrl)
					//"DetailPage" => $prgm_url,
				);
				//print_r($arr_prgm);exit;
				
				$program_num++;
				//print_r($arr_prgm);exit;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();						
				}					
			}
			
			//print_r($result);exit;
			$page++;
		}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\r\nGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		$this->compare_prgmNum = array(
				'total' => $totalnumFound,
				'prgm_num' => $program_num
		);
		//print_r($this->compare_prgmNum);
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();		
		$this->checkProgramOffline($this->info["AffId"], $check_date, $this->compare_prgmNum);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function checkProgramOffline($AffId, $check_date, $compare_prgmNum){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		
		if(count($prgm) > 500 && $compare_prgmNum['total'] != $compare_prgmNum['prgm_num']){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{			
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
	
	function getIgnoredMerDomain(){
		$domain_arr = array();
		$fhandle = fopen(INCLUDE_ROOT."lib/slimlinks_ignored_mer.csv", 'r');
		if($fhandle){
			while($line = fgetcsv ($fhandle, 5000))
			{
				foreach($line as $k => $v) $line[$k] = trim($v);			
				if ($line[0] == '') continue;	
				
				$domain_arr[$line[0]] = 1;				
			}
		}
		return $domain_arr;
	}
}
?>
