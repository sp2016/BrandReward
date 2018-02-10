<?php
include_once(dirname(__FILE__) . "/const.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>";

$objProgram = new ProgramDb();
$oLinkFeed = new LinkFeed();

$aff_arr = array();
$aff_arr = $objProgram->getClawerAff();

echo "get Aff list succ.\n";
/*if(date("h") === 0){
	$datetime = date("Y-m-d", strtotime(" -1 days"));
}else{*/
	$datetime = date("Y-m-d H", strtotime(" -1 hours"));
//}
foreach($aff_arr as $aff_id => $null){
	if($aff_id == 7){
		/*if(date("H") == "16"){
			$datetime = date("Y-m-d", strtotime(" -1 days"));			
		}else{*/
			continue;
		//}
	}
		
	$prgm_arr = array();
	$prgm_arr = $objProgram->getNewActiveProgramByAffId($aff_id, $datetime);
	if(count($prgm_arr)){
		echo "\tget Aff $aff_id: ".count($prgm_arr)."\n";
		$mid = array();
		foreach ($prgm_arr as $v)
		{
			//$oLinkFeed->GetAllLinksFromAffByMerID($aff_id, $v["idinaff"]);
			$mid[$v["idinaff"]] = $v["idinaff"]; 
		}		
		$cmd = "php /home/bdg/program/crawl/job.data.php --affid=$aff_id --method=onepagelink --daemon --silent --merid=".implode(",", $mid)." &";
		system($cmd);
	}
}

echo "<< Succ @ ".date("Y-m-d H:i:s").">>\n\n";
