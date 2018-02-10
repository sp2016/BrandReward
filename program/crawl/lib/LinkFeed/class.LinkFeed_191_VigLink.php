<?php
define('SECRET_KEY', 'c9574e7eec1f985551ca5b499faad49458599cdf');

class LinkFeed_191_VigLink
{

	 
	function __construct($aff_id,$oLinkFeed)
	{
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	    $headers = array( 'Authorization: secret ' . SECRET_KEY );
	    $this->para = array('addheader' => $headers);
	    	
	}
	
    function GetProgramFromAff()
	{	
		 
		$check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
		
		 
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	
	
	function GetProgramByApi()
	{
		echo "\t Program api start\r\n";
		
		$page = 1;
		$toNextPage = true;
		while ($toNextPage){
		    $re = $this->GetProgramByApiPage($page);    //页面的数据
		    if(!$re) $toNextPage = false;
		    $page ++ ;
		}
		
		echo "\t Program api end\r\n";
	
	}
	
	function GetProgramByApiPage($page){
	     
	    $objProgram = new ProgramDb();
	    $arr_prgm   = array();
	    
	    echo "\t Program api start by page:{$page}\r\n";
	    
	    $affUrl = "https://publishers.viglink.com/api/merchant/search?page=$page";
	    $affReponse = $this->oLinkFeed->GetHttpResult($affUrl,$this->para);
	    $totalPages = 0;
	    if($affReponse['code'] == 200){
	        $affData    = json_decode($affReponse['content']);
	        print_r($affData);
	        $totalPages = $affData->totalPages;         //总页数
//	        foreach ($affData->merchants as $v){
//	            //print_r($v);exit;
//
//
//	            if(isset($v) && !empty($v->id)) $strMerID = intval($v->id);
//	            else continue;
//
//	            $strMerName = $v->name;
//	            $commision = "";
//	            foreach($v->rates as $vv){
//	                if($vv->min == $vv->max){
//	                    $commision .= $vv->max . $vv->type ." ". $vv->description . "\r\n";
//	                }else{
//	                    $commision .= $vv->min ."-". $vv->max . $vv->type ." ". $vv->description . "\r\n";
//	                }
//	            }
//
//	            $Partnership = 'NoPartnership';
//	            if($v->approved){
//	                $Partnership = 'Active';
//	            }
//
//	            $Description = implode(',',$v->domains);
//	            if(isset($v->domains[0]) && !empty($v->domains[0])){
//	                preg_match('/[\w][\w-]*\.(?:com\.cn|com|cn|co|net|org|gov|cc|biz|info)(\/|$)/isU', $v->domains[0], $matches);
//	                if(!isset($matches[0]))
//	                    continue;
//	                $Homepage = $matches[0];
//	            }else
//	                continue;
//
//	            $CategoryExt = array();
//	            foreach($v->industryTypes as $tmpv){
//	            	$CategoryExt[] = $tmpv->name;
//	            }
//	            $CategoryExt = implode(',', $CategoryExt);
//
//	            $arr_prgm[$strMerID] = array(
//	                "Name" => addslashes(trim($strMerName)),
//	                "AffId" => $this->info["AffId"],
//	                "IdInAff" => $strMerID,
//	                "Homepage" => addslashes($Homepage),
//	            	"CategoryExt" => addslashes($CategoryExt),
//	                "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
//	                "Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
//	                "Description" => addslashes($Description),
//	                'TargetCountryExt'=> addslashes($v->countries),
//	                "CommissionExt" => addslashes($commision),
//	                "LastUpdateTime" => date("Y-m-d H:i:s"),
//	                "SupportDeepUrl" => 'YES'
//	            );
//	            //print_r($arr_prgm);exit;
//	        }
//
//	        if(count($arr_prgm)){
//	            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//	            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
//	            $arr_prgm = array();
//	        }
	    }
	    echo "\t Program api end by page:{$page}\r\n";
	    return $page >= $totalPages ? false : true;
	}
	
	
	function checkProgramOffline($AffId, $check_date){
	    $objProgram = new ProgramDb();
	    $prgm = array();
	    $prgm = $objProgram->getNotUpdateProgram($AffId, $check_date);
	    if(count($prgm) > 300){
	        mydie("die: too many offline program (".count($prgm).").\n");
	    }else{
	        $objProgram->setProgramOffline($AffId, $prgm);
	        echo "\tSet (".count($prgm).") offline program.\r\n";
	    }
	}
}
?>
