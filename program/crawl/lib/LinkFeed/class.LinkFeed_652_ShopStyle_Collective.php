<?php

class LinkFeed_652_ShopStyle_Collective
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;        
    }

    function GetProgramFromAff()
    {
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
        $arr_prgm = array();
        $program_num = 0;
        $request = array("AffId" => $this->info["AffId"], "method" => "get");
  
   		$prgm_url = "http://api.shopstyle.com/api/v2/retailers?pid=uid5600-34524660-78";
		$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
		//if($prgm_arr['code'] == 200){
			$results = $prgm_arr['content'];			
		//}
		$results = json_decode($results, true);								

		foreach($results['retailers'] as $v){
			$IdInAff = trim($v['id']);
			$Name = $v['name'];			
			$Homepage = $v['hostDomain'];
			
			$SupportDeepUrl = 'UNKNOWN';
			if($v['deeplinkSupport'] == 'true') {
				$SupportDeepUrl = 'YES';
			}else{
				$SupportDeepUrl = 'NO';
			}
			
			/*$MobileFriendly = 'UNKNOWN';
			if($v['mobileOptimized'] == 'true') {
				$MobileFriendly = 'YES';
			}else{
				$MobileFriendly = 'NO';
			}*/
			
			$arr_prgm[$IdInAff] = array("Name" => addslashes(trim($Name)),
										"IdInAff" => addslashes($IdInAff),
										"AffId" => $this->info["AffId"],
										"Homepage" => addslashes($Homepage),										
										"StatusInAff" => 'Active',//'Active','TempOffline','Offline'
										"Partnership" => 'Active',//'NoPartnership','Active','Pending','Declined','Expired','Removed'										
										"LastUpdateTime" => date("Y-m-d H:i:s"),
										//"MobileFriendly" => $MobileFriendly,
										"SupportDeepUrl" => $SupportDeepUrl,
										"AffDefaultUrl" => "https://api.shopstyle.com/action/apiVisitRetailer?pid=uid5600-34524660-78&url=[DEEPURL]"						
										);
										
			$program_num++;			
			if (count($arr_prgm) >= 100) {	
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$arr_prgm = array();
			}			
		}
	
		if (count($arr_prgm)) { 
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by api end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}


	function checkProgramOffline($AffId, $check_date)
	{
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if (count($prgm) > 30) {
			mydie("die: too many offline program (" . count($prgm) . ").\n");
			echo print_r($prgm, 1);
		} else {
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (" . count($prgm) . ") offline program.\r\n";
		}
	}


}
