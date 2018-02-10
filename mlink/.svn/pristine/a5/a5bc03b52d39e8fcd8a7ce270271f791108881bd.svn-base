<?php
global $_cf,$_req;
$row = array();
$content = '';
$uid = $_user['PublisherId'];
$time = date('Y-m-d H:i:s');
if(isset($_req['createddate']) || isset($_req['updateddate']) || isset($_req['datafile']) ){
	$filter = array();

	if(isset($_req['createddate']) && $_req['createddate']){
		$filter[] = 'a.CreatedDate = "'.addslashes($_req['createddate']).'"';
	}

	if(isset($_req['updateddate']) && $_req['updateddate']){
		$filter[] = 'a.UpdatedDate = "'.addslashes($_req['updateddate']).'"';
	}

	if(isset($_req['afname'])){
		$filter[] = 'a.Af = "'.addslashes($_req['afname']).'"';
	}

	if(isset($_req['site'])){
		$filter[] = 'a.site = "'.addslashes($_req['site']).'"';
	}

	if(isset($_req['datafile'])){
		$filter[] = 'a.datafile = "'.addslashes($_req['datafile']).'"';
	}

	$where_str = join(' AND ',$filter);
	$sql = "SELECT a.Af,a.Created,a.Updated,a.Sales,a.Commission,a.IdInAff,a.ProgramName,a.OrderId,a.TradeKey,a.SID,a.PublishTracking,a.Site,a.Alias,a.ProgramId,'BR' FROM rpt_transaction_unique as a WHERE ".$where_str." AND a.Af != 'mega' AND a.Af != 'bdg' AND a.Af != 'mk'";
        $row = $_cf->getRows($sql);
	if(!empty($row)){
		foreach($row as $k=>$v){
			$content .= join("\t",$v)."\n";
		}
	}


       $sql = "SELECT b.Af,a.Created,a.Updated,a.Sales,a.Commission,b.IdInAff,b.ProgramName,b.OrderId,a.TradeKey,a.SID,a.PublishTracking,a.Site,a.Alias,b.ProgramId,b.Source FROM rpt_transaction_unique AS a LEFT JOIN rpt_transaction_unique_inner AS b ON a.`TradeId` = b.`TradeKey` WHERE ".$where_str." AND (b.`Source` = 'mk' OR b.`Source` = 'bdg')";
       $row = $_cf->getRows($sql);
        
       if(!empty($row)){
           foreach($row as $k=>$v){
               if($v['Source'] == 'mk'){
                   $v['Source'] = 'MK';
               }elseif($v['Source'] == 'bdg'){
                   $v['Source'] = 'PL';
               }
               $content .= join("\t",$v)."\n";
           }
       }

}
echo $content;
exit();
?>
