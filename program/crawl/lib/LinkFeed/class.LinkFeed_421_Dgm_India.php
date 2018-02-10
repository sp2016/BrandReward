<?php
require_once 'text_parse_helper.php';
class LinkFeed_421_Dgm_India
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01')
			$this->affiliateID = '70803';
		else 
			$this->affiliateID = '';
	}

	function GetAllLinksByAffId()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$check_date = date('Y-m-d H:i:s');
		
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->getProgramByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function getProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "", 
		);
		
		//login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		
		$hasNextPage = true;
		$page = 1;
		$pageNum = 50;
		$safe_cnt = 10;		
		while($hasNextPage){
			echo "\t page $page.";
			$strUrl = "http://www.dgperform.com/affiliates/index.cfm?fuseaction=campaigns.all_campaigns&sort_order=0&Start=" . ((($page - 1) * 50) + 1);
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			$m = array();
			preg_match("/Campaign \\d* to \\d* of (\\d+)/i", $result, $m);
			if(count($m) && isset($m[1])){
				if((int)$m[1] < (int)($pageNum * $page)){
					$hasNextPage = false;
				}
				
				$result = preg_replace("/>\\s+</i", "><", $result);
			
				$nLineStart = 0;
				while ($nLineStart >= 0){
					$nLineStart = stripos($result, '<tr><td align="left" class="lightblueRow">', $nLineStart);
					if ($nLineStart === false) break;
	
					$name = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('<td align="left" class="lightblueRow">', '<td align="left" class="lightblueRow">'), '</td>', $nLineStart));
					$commission = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td align="center" width="100" class="lightblueRow">', '</td>', $nLineStart));
					if(stripos($commission, "Get more info") !== false){
						$commission = "Get more info";						
					}
				
					$status_remark = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td align="center" class="lightblueRow">', '</td>', $nLineStart));
					switch($status_remark)
					{
						case "Pending":
							$partnership = "Pending";//'NoPartnership','Active','Pending','Declined','Expired','Removed'
							break;
						case "Joined":
							$partnership = "Active";
							break;
						default:
							$partnership = "NoPartnership";
							break;
					}
				
					$mer_status = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td align="center" class="lightblueRow">', '</td>', $nLineStart));
					switch($mer_status)
					{
						case "Live":
							$status = "Active";
							break;				
						default:
							$status = "Offline";
							break;
					}
				
					$tmp_info = $this->oLinkFeed->ParseStringBy2Tag($result, 'href="index.cfm?fuseaction=dgmPro.moreinfo&cpid=', '"><img', $nLineStart);
					$m = array();
					preg_match("/cmid=(\\d+)/i", $tmp_info, $m);
					
					$cpid = intval($tmp_info);
					
					$idinaff = 0;
					if(isset($m[1])){
						$idinaff = intval($m[1]);
					}
					if(!$idinaff) continue;
					
					$detail_page = "http://www.dgperform.com/affiliates/index.cfm?fuseaction=dgmPro.moreinfo$tmp_info";					
   					$r = $this->oLinkFeed->GetHttpResult($detail_page,$request);
   					if($r["code"] != 200) continue;
   					
					$prgm_detail = $r["content"];					
					if($commission == "Get more info"){
						//only for a while						
						$tmp_stop = array("Company Description", "Campaign Desscription", "Company  Description");
						foreach($tmp_stop as $stop_word){
							$commission = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Commission', $stop_word));
							if($commission) break;
						}						
					}
					
					$contact_info = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Account Manager:', "</a>");
					$contact = strip_tags($contact_info) . " | " .$this->oLinkFeed->ParseStringBy2Tag($contact_info, 'mailto:', '"');
					
					$Homepage = '';
					$HPrequest = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "fuseaction=creatives.text_links&cpid=$cpid&cmid=$idinaff");
					$HPresult = $this->oLinkFeed->GetHttpResult('http://www.dgperform.com/affiliates/index.cfm?fuseaction=creatives.text_links', $HPrequest);
					$HPcontent = $HPresult['content'];
					
					preg_match('/<a\s*href=\"(https?:\/\/[^"]+)\"\starget=\"_blank\">Destination Page<\/a>/i', $HPcontent, $m);
					if (isset($m[1])) {
						if($tmp_url = $this->oLinkFeed->findFinalUrl($m[1], array("nobody" => "unset"))){
							$Homepage = $tmp_url;
						}
					}
					
					$AffDefaultUrl = "http://www.s2d6.com/x/?x=c&z=s&campaignid=$idinaff&affiliateid={$this->affiliateID}&k=[SUBTRACKING]";
					
					$name = trim(str_ireplace("&nbsp;", "", $name), "\t\r\n ");
					$commission = trim($commission, "\t\r\n ");
					
					$arr_prgm[$idinaff] = array(
						"Name" => addslashes($name),
						"AffId" => $this->info["AffId"],
						"Homepage" => addslashes($Homepage),
						"IdInAff" => $idinaff,						
						"StatusInAffRemark" => addslashes($status_remark),
						"StatusInAff" => $status,						//'Active','TempOffline','Offline'
						"Partnership" => $partnership,				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"CommissionExt" => addslashes($commission),
						"Contacts" => addslashes($contact),
						//"Description" => addslashes($desc),
						"SupportDeepUrl" => "YES",						
						"DetailPage" => $detail_page,
						"AffDefaultUrl" => $AffDefaultUrl, 
						"LastUpdateTime" => date("Y-m-d H:i:s")
					);
					
					//print_r($arr_prgm);exit;
					
					$program_num++;
		
					if(count($arr_prgm) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
				
				//print_r($arr_prgm);exit;
			}	
			
			$page++;
			if($page > $safe_cnt){
				echo "over while.";
				break;
			}		
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
	
		
		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";	
	}
	

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
				
		try {
			$client = new SoapClient("http://webservices.dgperform.com/dgmpublisherwebservices.cfc?wsdl", array('trace'=> true));
			$xx = array('username'  => "Ran.Chen6",
						'password'  => "Aqdkkd55ff",
						'approvaltype' => "approved"
						);
						
			$approvaltype = array("approved", "rejected", "pending");			
			
			foreach($approvaltype as $v){
				$xx['approvaltype'] = $v;
				$req = $client->__soapCall("GetCampaigns", $xx);
				print_r($req);
			}
			
			exit;			
			
		} catch( Exception $e ) {
			mydie("die: Api error.\n");
		}
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

