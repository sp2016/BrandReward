<?php
class LinkFeed_639_digidip
{

	 
	function __construct($aff_id,$oLinkFeed)
	{
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	    
		$username = 'MKdiig2217?';
		$password = ';8pznbKe6{VTNdN,';
	    $headers = array( 'Authorization: Basic ' . base64_encode($username.':'.$password) , 'Accept: application/json');
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
		echo "Program api start\r\n";
	     
	    $objProgram = new ProgramDb();
	    $arr_prgm = array();
	    $program_num = 0;
	    		            
	    $affUrl = "https://api.digidip.net/merchants";
	    $affReponse = $this->oLinkFeed->GetHttpResult($affUrl,$this->para);
	    
	    $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "program_list".date("YmdH") . ".dat","cache_merchant", 1);//返回.cache文件的路径
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{			
			$r = $this->oLinkFeed->GetHttpResult($affUrl,$this->para);
			if($r['code'] == 200){
				$result = $r["content"];			
				$this->oLinkFeed->fileCachePut($cache_file,$result);
			}
		}
		if(!file_exists($cache_file)) mydie("die: list file does not exist. \n");
		
		$cache_file = file_get_contents($cache_file);
		
    	echo "get list done.\r\n";
        $affData = json_decode($cache_file, true);
        //print_r($affData->data);
        foreach ($affData['data'] as $v){
            
            //echo "\t Progarm $v->merchant_id start\r\n";
           
            //print_r($v);
            
            $strMerID = intval($v['merchant_id']);
            if(!$strMerID) {
            	print_r($v);
            	echo '#';
            	continue;
            }
            
            $strMerName = $v['merchant_name'];
            
            $arr_prgm[$strMerID] = array(
                "Name" => addslashes(trim($strMerName)),
                "AffId" => $this->info["AffId"],
                "IdInAff" => $strMerID,	                
                "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
                "Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
                'TargetCountryExt'=> '',
                "LastUpdateTime" => date("Y-m-d H:i:s"),
            );
            
            $prgm_detail_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "program_detail_{$v['merchant_id']}" . ".dat", "cache_merchant", 1);//返回.cache文件的路径
			if(!$this->oLinkFeed->fileCacheIsCached($prgm_detail_file))
			{			
				$r = $this->oLinkFeed->GetHttpResult("https://api.digidip.net/merchants/{$v['merchant_id']}", $this->para);
				if($r['code'] == 200){
					$result = $r["content"];			
					$this->oLinkFeed->fileCachePut($prgm_detail_file,$result);
				}
			}
			if(file_exists($prgm_detail_file)){
	            $prgm_detail_file = file_get_contents($prgm_detail_file);	            
	            
            	$prgm_detail = json_decode($prgm_detail_file, true);
            	//print_r($prgm_detail);exit;
            	$Homepage = current($prgm_detail['data']['merchant']['hosts']);
            	if($Homepage){
            		$arr_prgm[$strMerID]["Homepage"] = addslashes($Homepage);
            		$arr_prgm[$strMerID]["Description"] = addslashes(implode(',', $prgm_detail['data']['merchant']['hosts']));
            	}     
			}         
            //print_r($arr_prgm);exit;
	        if(count($arr_prgm)){
	            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);		           
	            $arr_prgm = array();		           
	            echo "$program_num\t";
	        }
	        
	        $program_num++;
        }
        
        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);	               
            unset($arr_prgm);	            
        }
	    
	    echo "Program api end ($program_num) \r\n";
	}
	
	
	function checkProgramOffline($AffId, $check_date){
	    $objProgram = new ProgramDb();
	    $prgm = array();
	    $prgm = $objProgram->getNotUpdateProgram($AffId, $check_date);
	    if(count($prgm) > 100){
	        mydie("die: too many offline program (".count($prgm).").\n");
	    }else{
	        $objProgram->setProgramOffline($AffId, $prgm);
	        echo "Set (".count($prgm).") offline program.\r\n";
	    }
	}
}
?>
