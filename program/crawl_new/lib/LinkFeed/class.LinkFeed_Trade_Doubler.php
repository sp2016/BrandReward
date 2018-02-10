<?php
/**
 * User: rzou
 * Date: 2017/8/2
 * Time: 14:54
 */
class LinkFeed_Trade_Doubler
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchid;
	}
	
	function GetProgramFromAff($accountid)
	{
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		
		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		foreach($this->site as $v){
			echo 'Site:' . $v['Name']. "\r\n";
			$this->GetProgramByPage($v['SiteID'],$v['SiteIdInAff']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	function GetProgramByPage($SiteID,$SiteIdInAff)
	{
		echo "\tGet Program by Page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$use_true_file_name = true;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);
		
		$page = 1;
		$nNumPerPage = 100;
		while (1)
		{
			echo "page$page\t";
			if ($page == 1){
				$strUrl = "https://publisher.tradedoubler.com/pan/aProgramList.action";
				$request["method"] = "post";
				$request["postdata"] = "programGEListParameterTransport.currentPage=".$page."&searchPerformed=true&searchType=prog&programGEListParameterTransport.programIdOrName=&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=" . $SiteIdInAff . "&programGEListParameterTransport.orderBy=statusId&programAdvancedListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=" . $nNumPerPage . "&programAdvancedListParameterTransport.directAutoApprove=&programAdvancedListParameterTransport.mobile=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=";
				$this->GetHttpResultMoreTry($strUrl,$request);
			}
			$strUrl = "https://publisher.tradedoubler.com/pan/aProgramList.action?categoryChoosen=false&programGEListParameterTransport.currentPage=".$page."&programGEListParameterTransport.pageSize=".$nNumPerPage."&programGEListParameterTransport.pageStreamValue=true";
			$request["postdata"] = "";
			$request["method"] = "get";
			
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$results = $this->GetHttpResultMoreTry($strUrl, $request);
				if (!$results) {
					mydie("Can't get page content!");
				}
				$this->oLinkFeed->fileCachePut($cache_file, $results);
			}
			$result = file_get_contents($cache_file);
			
			//parse HTML
			$strLineStart = 'showPopBox(event, getProgramCodeAffiliate';
			$nLineStart = 0;
			$bStart = 1;
			while(1)
			{
				echo "$nLineStart\t";
				$nLineStart = stripos($result,$strLineStart,$nLineStart);
				if($nLineStart === false && $bStart == 1) break 2;
				if($nLineStart === false) break;
				$bStart = 0;
				
				$ProgramID = $this->oLinkFeed->ParseStringBy2Tag($result, 'getProgramCodeAffiliate(', ',', $nLineStart);
				if($ProgramID === false) break;
				$ProgramID = trim($ProgramID);
				if(empty($ProgramID)) continue;
				
				$Name = $this->oLinkFeed->ParseStringBy2Tag($result, ">","</a>", $nLineStart);
				if($Name === false) break;
				$Name = html_entity_decode(trim($Name));
				
				$Category = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$Prepayment = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$Keywords = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$ProductFeeds = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$TariffsClick = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td>','<td>'), '</td>', $nLineStart));
				$TariffsLeads = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$TariffsSales = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$Performance90thEPC = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$PerformanceAvgPaidEPC = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$MobileFriendly = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td>','<td>'), '</td>', $nLineStart));
				$WebsiteStatus = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				
				$DetailPage = "https://publisher.tradedoubler.com/pan/aProgramInfoApplyRead.action?programId={$ProgramID}&affiliateId={$SiteIdInAff}";
				$ProgramInfoPage = "https://publisher.tradedoubler.com/pan/aProgramTextRead.action?programId={$ProgramID}&affiliateId={$SiteIdInAff}";
				
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$detail_page = $this->GetHttpResultMoreTry($DetailPage, $request);
					if (!$detail_page) {
						mydie("Can't get detailpage!");
					} else {
						$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
					}
				}
				$detail_page = file_get_contents($cache_file);
				
				$strPosition = 0;
				$HomePage = trim($this->oLinkFeed->ParseStringBy2Tag($detail_page, array('Visit the site','a href="'), '"', $strPosition));
				$HomePage = $this->oLinkFeed->findFinalUrl($HomePage,$request);
				
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($detail_page, 'img src="','"', $strPosition));
				
				$Commission = $this->oLinkFeed->ParseStringBy2Tag($detail_page, array('Commission structure','</tr>'),'</table>', $strPosition);
				$Commission = preg_replace("@>\s+<@",'><',$Commission);
				$Commission = preg_replace("@0\s+%@",'0%',$Commission);
				$Commission = preg_replace("@</td><td></td><td>@",':',$Commission);
				$Commission = strip_tags(preg_replace("@</tr><tr>@",';',$Commission));
				
				$PaymentInformation = $this->oLinkFeed->ParseStringBy2Tag($detail_page, 'Payment information</b>','</table>');
				$PaymentInformation = preg_replace("@>\s+<@",'><',$PaymentInformation);
				$PaymentInformation = strip_tags(preg_replace("@\s+&nbsp;\s+@",' ',$PaymentInformation));
				
				$technical_info = $this->oLinkFeed->ParseStringBy2Tag($detail_page, 'Technical info</b>','</table>');
				$PaymentCurrency = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Payment currency','>'),'</tr>')));
				$BatchedSalesUpdates = trim($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Batched sales updates','<td','>'),'</'));
				$CookieTime = trim($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Cookie time','<td','>'),'</'));
				$DeepLinking = trim($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Deep linking','<td','>'),'</'));
				$CreateLinks = trim($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Create links','<td','>'),'</'));
				$KeywordPolicy = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Keyword policy','>'),'</tr>')));
				$RecurringEvents = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Recurring events','>'),'</tr>')));
				$ImplementationType = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Implementation Type','>'),'</tr>')));
				
				$ContactDetails = $this->oLinkFeed->ParseStringBy2Tag($detail_page, array('Contact Details:','</tr>'),'</table>');
				
				$BonusRules = trim($this->oLinkFeed->ParseStringBy2Tag($detail_page, array('Bonus rules',':'),'<'));
				$SegmentationRule = trim($this->oLinkFeed->ParseStringBy2Tag($detail_page, array('Segmentation rules',':'),'<'));
				
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "ProgramInfo_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$programInfo_page = $this->GetHttpResultMoreTry($ProgramInfoPage, $request);
					if (!$programInfo_page) {
						mydie("Can't get programInfoPage!");
					} else {
						$this->oLinkFeed->fileCachePut($cache_file, $programInfo_page);
					}
				}
				$programInfo_page = file_get_contents($cache_file);
				
				$ProgramInfo = $programInfo_page;
				
				$CommissionFromInfo = $this->oLinkFeed->ParseStringBy2Tag($programInfo_page, array('-- Commissies --','<table','>'),'</table>');
				if ($CommissionFromInfo) {
					$CommissionFromInfo = '<table>' . $CommissionFromInfo . '</table>';
				}
				
				$ContactPersonen = $this->oLinkFeed->ParseStringBy2Tag($programInfo_page, array('-- Contactpersonen --','<table','>'),'</table>');
				if ($ContactPersonen) {
					$ContactPersonen = '<table>' . $ContactPersonen . '</table>';
				}
				
				$AanvullendeInformatie = $this->oLinkFeed->ParseStringBy2Tag($programInfo_page, array('-- Aanvullende informatie --','<table','>'),'</table>');
				if ($AanvullendeInformatie) {
					$AanvullendeInformatie = '<table>' . $AanvullendeInformatie . '</table>';
				}
				
				if (!$CookieTime) {
					$CookieTime = trim($this->oLinkFeed->ParseStringBy2Tag($technical_info, array('Cookie-Laufzeit','>'),'<'));
				}
				
				$Policies  = $this->oLinkFeed->ParseStringBy2Tag($programInfo_page, array('-- Policies --','<table','>'),'</table>');
				if ($Policies) {
					$Policies = '<table>' . $Policies . '</table>';
				}
				
				$arr_prgm[$ProgramID] = array(
					"SiteID" => $SiteID,
					"AccountID" => $this->account['AccountID'],
					"Name" => addslashes($Name),
					"BatchID" => $this->oLinkFeed->batchid,
					"IdInAff" => $ProgramID,
					'Partnership' => addslashes($WebsiteStatus),
					"AffID" => $this->info["AffID"],
					'Category' => addslashes($Category),
					'Prepayment' => addslashes($Prepayment),
					'Keywords' => addslashes($Keywords),
					'ProductFeeds' => addslashes($ProductFeeds),
					'TariffsClick' => addslashes($TariffsClick),
					'TariffsLeads' => addslashes($TariffsLeads),
					'TariffsSales' => addslashes($TariffsSales),
					'Performance90thEPC' => addslashes($Performance90thEPC),
					'PerformanceAvgPaidEPC' => addslashes($PerformanceAvgPaidEPC),
					'MobileFriendly' => addslashes($MobileFriendly),
					'WebsiteStatus' => addslashes($WebsiteStatus),
					'DetailPage' => addslashes($DetailPage),
					'ProgramInfoPage' => addslashes($ProgramInfoPage),
					'LogoUrl' => addslashes($LogoUrl),
					'HomePage' => addslashes($HomePage),
					'Commission' => addslashes($Commission),
					'PaymentInformation' => addslashes($PaymentInformation),
					'PaymentCurrency' => addslashes($PaymentCurrency),
					'BatchedSalesUpdates' => addslashes($BatchedSalesUpdates),
					'CookieTime' => addslashes($CookieTime),
					'DeepLinking' => addslashes($DeepLinking),
					'CreateLinks' => addslashes($CreateLinks),
					'KeywordPolicy' => addslashes($KeywordPolicy),
					'RecurringEvents' => addslashes($RecurringEvents),
					'ImplementationType' => addslashes($ImplementationType),
					'ContactDetails' => addslashes($ContactDetails),
					'BonusRules' => addslashes($BonusRules),
					'SegmentationRule' => addslashes($SegmentationRule),
					'ProgramInfo' => addslashes($ProgramInfo),
					'CommissionDetails' => addslashes($CommissionFromInfo),
					'ContactPersonen' => addslashes($ContactPersonen),
					'AanvullendeInformatie' => addslashes($AanvullendeInformatie),
					'Policies' => addslashes($Policies)
				);
				$program_num++;
				
				if (count($arr_prgm) > 10) {
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page ++;
		}
		
		if (count($arr_prgm) > 0) {
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by page end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}
	
	function CheckBatch(){
		$objProgram = new ProgramDb();
		//$this->oLinkFeed->batchid;
		$objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
	}
	
	function GetHttpResultMoreTry($url, $request, $checkstring = '', $retry = 3)
	{
		$result = '';
		while ($retry) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if ($checkstring) {
				if (strpos($r['content'], $checkstring) !== false) {
					return $result = $r['content'];
				}
			} elseif (!empty($r['content'])) {
				return $result = $r['content'];
			}
			$retry--;
		}
		return $result;
	}
}