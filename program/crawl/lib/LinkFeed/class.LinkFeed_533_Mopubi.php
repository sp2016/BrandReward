<?php
require_once 'text_parse_helper.php';

class LinkFeed_533_Mopubi
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->getStatus = false;
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$strUrl = "http://console.mopubi.com/affiliates/Extjs.ashx?s=contracts";
		$hasNextPage = true;
		$page = 1;
		while($hasNextPage){
		    echo "page:$page\r\n";

            $startNum = ($page - 1) * 100;
			$request['postdata'] = sprintf('groupBy=&groupDir=ASC&cu=0&c=&cat=0&sv=&cn=&pf=&st=0&m=&ct=&pmin=&pmax=&mycurr=false&t=&p=%s&n=100', $startNum);
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$res = json_decode($r["content"],true);
			//var_dump($res);exit;
			if($res['total'] < $page * 100)
				$hasNextPage = false;
			
			foreach ($res['rows'] as $v)
			{
				if ($v['status'] != 'Active')
					continue;
				$url = "http://console.mopubi.com/affiliates/Extjs.ashx?s=creatives&cont_id={$v['contract_id']}";
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = json_decode($r['content'], true);
				//var_dump($content);exit;
				$links = array();
				foreach ($content['rows'] as $c)
				{
					if($c['type'] != 'Link' && $c['type'] != 'Image' && $c['type'] != 'Text')
						continue;
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['campaign_id'],
							"AffLinkId" => $c['id'],
							"LinkName" => $c['name'],
							"LinkDesc" => '',
							"LinkAffUrl" => $c['unique_link'],
							"LinkStartDate" => '',
							"LinkEndDate" => '',
							"LinkPromoType" => 'link',
							"LinkCode" => '',
							"LinkImageUrl" => '',
							"LinkOriginalUrl" => '',
							"DataSource" => '415',
							"IsDeepLink" => 'UNKNOWN',
							"Type"       => 'link'
					);
					
					if(empty($link['LinkHtmlCode']))
						$link['LinkHtmlCode'] = create_link_htmlcode($link);
					if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
						continue;
					$this->oLinkFeed->fixEnocding($this->info, $link, "link");
					$links[] = $link;
					$arr_return["AffectedCount"] ++;
				}
				echo sprintf("%s link(s) found.\n", count($links));
				if(sizeof($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $v['campaign_id']);
			}
			
			$page++;
			
			if($page > 20) {
				mydie("die: Page overload.\n");			
			}
		}
		return $arr_return;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->GetProgramFromByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}


	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		//program management adv
		echo "get program \r\n";
		$strUrl = "http://console.mopubi.com/affiliates/Extjs.ashx?s=contracts";
		$hasNextPage = true;
		$page = 1;
		$arr_prgm = array();
		while($hasNextPage){
			echo "\t page $page.\n";
			$postdata = array(
				'groupBy' => '',
				'groupDir' => 'ASC',
				'cu' => 0,
				'c' => '',
				'cat' => 0,
				'sv' => '',
				'cn' => '',
				'pf' => '',
				'st' => 0,
				'm' => '',
				'ct' => '',
				'pmin' => '',
				'pmax' => '',
				'mycurr' => false,
				't' => '',
				'p' => ($page - 1) * 100,
				'n' => 100,
			);
			$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => http_build_query($postdata),);

			$this->showCrawlPageTime($strUrl);
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$res = json_decode($r["content"],true);
			//var_dump($res);exit;
			if(($res['total'] - ($page - 1) * 100) < 100)
				$hasNextPage = false;
			$result = $res['rows'];
			foreach($result as $item)
			{
				$strMerID = $item['campaign_id'];
				if (!$strMerID)
					break;
				$strMerName = trim($item['name']);
				if ($strMerName === false)
					break;
				$StatusInAffRemark = trim($item['status']);
				$Homepage = '';
				$AffDefaultUrl = $Partnership = '';
				$StatusInAff = 'Active';
				if($StatusInAffRemark == 'Active')
				{
					$Partnership = 'Active';
					$contid = $item['contract_id'];
					$detailUrl = "http://console.mopubi.com/affiliates/Extjs.ashx?s=contract_info&cont_id=$contid";
					$request = array("AffId" => $this->info["AffId"],"method" => "get", "postdata" => "", );

					$this->showCrawlPageTime($detailUrl);

					$detailFull = $this->oLinkFeed->GetHttpResult($detailUrl,$request);
					$detail = json_decode($detailFull['content'],true)['rows'];
					$Homepage = $detail[0]['preview_link'];
					$detailDefaulUrl = "http://console.mopubi.com/affiliates/Extjs.ashx?s=creatives&cont_id=$contid";
					$request = array("AffId" => $this->info["AffId"],"method" => "post", "postdata" => "s=creatives&cont_id=$contid", );

					$this->showCrawlPageTime($detailDefaulUrl);

					$detailDefaulUrlFull = $this->oLinkFeed->GetHttpResult($detailDefaulUrl,$request);
					$detailDefaul = json_decode($detailDefaulUrlFull['content'],true)['rows'];
					$AffDefaultUrl = $detailDefaul[0]['unique_link'];
				}elseif($StatusInAffRemark == 'Pending'){
					$Partnership = 'Pending';
				}elseif($StatusInAffRemark == 'Apply To Run' || $StatusInAffRemark == 'Inactive' || $StatusInAffRemark == 'Public'){
					$Partnership = 'NoPartnership';
				}else{
					mydie ("die: unknown $strMerName partnership: $StatusInAffRemark.\n");
				}

                $CommissionExt = '';
                switch ($item['price_format_id']) {
                    case 5 :
                        $CommissionExt =addslashes(trim($item['price_converted'])."%");
                        break;
                    case 1 :
                        $CommissionExt =addslashes("$".trim($item['price_converted']));
                        break;
                    case 2 :
                        $CommissionExt =addslashes("€".trim($item['price_converted']));
                        break;
                    default :
                        mydie("There find new currency!");
                }

				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode(trim($strMerName))),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => $StatusInAffRemark,
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"CommissionExt" => $CommissionExt,
					"Homepage" => addslashes($Homepage),
					"AffDefaultUrl" => addslashes($AffDefaultUrl),
                    "CategoryExt" => addslashes($item['vertical_name']),
				);

                if (SID == 'bdg02'){
                    $arr_prgm[$strMerID]['PublisherPolicy'] = addslashes($item['restrictions']);
                }

				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page++;
			if($page > 300){
				mydie("die: Page overload.\n");
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}

		echo "\tGet Program by page end\r\n";
		if ($program_num < 10) {
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

    function showCrawlPageTime($Url)
    {
        echo "\n", $Url,"\t",date('Y-m-d H:i:s', time()), "\n";
    }
}