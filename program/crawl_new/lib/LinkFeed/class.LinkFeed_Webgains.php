<?php

/**
 * User: rzou
 * Date: 2017/7/28
 * Time: 10:15
 */
class LinkFeed_Webgains
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchID;
		
	}
	
	function GetProgramFromAff($accountid, $affSiteAccName)
	{
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->site = $this->oLinkFeed->getAffAccountSiteByName($affSiteAccName);

        echo 'Site:' . $this->site['Name'] . "\r\n";
        $this->GetProgramByPage($this->site['SiteID'], $this->site['SiteIdInAff']);

		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->oLinkFeed->checkBatchID = $this->oLinkFeed->batchID;
        $this->oLinkFeed->CheckCrawlBatchData($this->info["AffID"], $this->site['SiteID']);
	}

	function GetProgramByPage($SiteID, $SiteIdInAff)
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
		$hasNextPage = true;
		while ($hasNextPage) {
			echo "Page:$page\t";

            $apiUrl = "http://www.webgains.com/publisher/{$SiteIdInAff}/program/list/get-data/joined/all/order/name/sort/asc/keyword//country//category//status/?columns%5B%5D=name&columns%5B%5D=status&columns%5B%5D=exclusive&columns%5B%5D=id&columns%5B%5D=type&columns%5B%5D=categories&columns%5B%5D=keywords&columns%5B%5D=action&subcategory=&page=$page";
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$results = $this->GetHttpResultMoreTry($apiUrl, $request);
				$this->oLinkFeed->fileCachePut($cache_file, $results);
			}
			$result = file_get_contents($cache_file);

			$data = json_decode($result, true);
//			print_r($data);exit;

			if (!isset($data['data']) || empty($data['data'])) {
				mydie('Can\'t get data, please check the api !');
			}
			if ($data['pagesNumber'] <= $page) {
				$hasNextPage = false;
				if ($this->debug) print " NO NEXT PAGE  <br>\n";
			} else {
				$page++;
				if ($this->debug) print " Have NEXT PAGE  <br>\n";
			}

			foreach ($data['data'] as $val) {
				if (!isset($val['id']) || !$val['id']) {
					continue;
				}
				$ProgramID = $val['id'];
				$Name = $val['name'];
				$Keywords = empty($val['keywords']['long']) ? $val['keywords']['short'] : $val['keywords']['long'];
				$Categories = @empty($val['categories']['long']) ? @$val['categories']['short'] : @$val['categories']['long'];

				$DetailPage = "http://www.webgains.com/publisher/{$SiteIdInAff}/program/view?programID={$ProgramID}";

				$arr_prgm[$ProgramID] = array(
					"SiteID" => $SiteID,
					"AccountID" => $this->account['AccountID'],
					'AffID' => $this->info['AffID'],
					'IdInAff' => $ProgramID,
					'BatchID' => $this->oLinkFeed->batchID,
					'Name' => addslashes(trim($Name)),
					'MembershipStatus' => addslashes(trim($val['membershipStatus'])),
					'Partnership' => addslashes(trim($val['membershipStatus'])),
					'ProgramStatus' => addslashes(trim($val['status'])),
					'Keywords' => addslashes($Keywords),
					'Categories' => addslashes($Categories),
					'Description' => addslashes($val['description']),
					'ExclusiveToWebgains' => @addslashes($val['exclusiveToWG']),
					'Type' => @addslashes($val['type']),
					'NetworkName' => addslashes($val['networkName']),
					'DetailPage' => $DetailPage,
				);

				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$detail_page = $this->GetHttpResultMoreTry($DetailPage, $request);
					if (!$detail_page) {
						mydie("Can't get detailpage!");
					} else {
						$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
					}
				}
				$result = file_get_contents($cache_file);

				$strPosition = 0;
				$LogoUrl = $this->oLinkFeed->ParseStringBy2Tag($result, array('div class="wrapper">', '<img src="'), '"', $strPosition);
				if (stripos($LogoUrl, 'http://') === false || stripos($LogoUrl, 'https://') === false) {
					$LogoUrl = 'http://www.webgains.com' . $LogoUrl;
				}

				$Homepage = $this->oLinkFeed->ParseStringBy2Tag($result, 'class="homepageUrl" href="', '"', $strPosition);
				$Country = $this->oLinkFeed->ParseStringBy2Tag($result, array('<b>Country:</b>', '<img src="'), '"', $strPosition);
				preg_match('@images/flag_\w+_([A-Z]+)\.png@', $Country, $con);
                $Country = @$con[1];

				$LiveSinceDate = $this->oLinkFeed->ParseStringBy2Tag($result, '<b>Live since:</b>', '<br/>', $strPosition);

				$PPCTermAndCondition = $this->oLinkFeed->ParseStringBy2Tag($result, 'PPC Terms and Conditions', '<a id="desc-view-less">');
				if ($PPCTermAndCondition) {
					$PPCTermAndCondition = strip_tags($PPCTermAndCondition);
				}

				$CommissionDetails = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Commission details:', '<h2>'), '</h2>', $strPosition));
				$CommissionDetails = trim(preg_replace('/View&nbsp;/', '', $CommissionDetails));
				$CookiePeriod = $this->oLinkFeed->ParseStringBy2Tag($result, array('Cookie period:', '<h2>'), '</h2>', $strPosition);
				$ConversionRate = $this->oLinkFeed->ParseStringBy2Tag($result, array('Conversion rate:', '<h2>'), '</h2>', $strPosition);
				$EPHC = $this->oLinkFeed->ParseStringBy2Tag($result, array('EPHC:', '<h2>'), '</h2>', $strPosition);
				$AverageValidationPeriod = $this->oLinkFeed->ParseStringBy2Tag($result, array('Average validation period:', '<h2>'), '</h2>', $strPosition);
				$AOV = $this->oLinkFeed->ParseStringBy2Tag($result, array('AOV:', '<h2>'), '</h2>', $strPosition);
				$ProgramKeyFacts = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('key facts', '>'), '</table>', $strPosition));
				$PPCPolicyOverview = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('PPC Policy Overview:', '</strong>'), '<br/>', $strPosition), '&nbsp;');
				$Geotargeting = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Geotargeting:', '</strong>'), '<br/>', $strPosition), '&nbsp;');

				$marketing_clumns_content = $this->oLinkFeed->ParseStringBy2Tag($result, 'id="widget_info">', '</table>', $strPosition);

				$newPos = 1;
				$marketing_clumns_arr = array();
				while ($newPos) {
					$icon = $this->oLinkFeed->ParseStringBy2Tag($marketing_clumns_content, 'src="/images/icons/', '_sml', $newPos);
					if (!$icon) {
						break;
					}
					$clumns = $this->oLinkFeed->ParseStringBy2Tag($marketing_clumns_content, '<td>', '</td>', $newPos);
					$clumns = preg_replace('@\s+@', '', ucwords($clumns));
					$marketing_clumns_arr[$clumns] = $icon;
				}

				$Currency = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Currency:', 'class="sideRight">'), '</td>', $strPosition));
				$PaymentType = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Payment type:', 'class="sideRight">'), '</td>', $strPosition));
				$LastPaymenDate = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Last payment', 'class="sideRight">'), '</td>', $strPosition));
				$PayOnPostage = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Pays on postage<', 'class="sideRight">'), '</td>', $strPosition));
				$PayOnVAT = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Pays on VAT:', 'class="sideRight">'), '</td>', $strPosition));
				$ContactAccountManagerName = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'Account manager:', '</td>', $strPosition));
				$ContactAccountManagerEmail = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'href="mailto:', '"', $strPosition));

				$adStrpos = 0;
                $ContactAdvertiserName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('>Advertiser:<', '<h2', '>'), '</h2', $adStrpos)));
				if (!empty($ContactAdvertiserName)) {
					$ContactAdvertiserEmail = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'a href="mailto:', '"', $adStrpos));
					$ContactAdvertiserPhoneNumber = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('name="mobile', '<td>'), '</td>', $adStrpos)));
				}

				if ($TADpos = strpos($result, 'Terms & Conditions')) {
					$TermsAndConditions = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Terms and conditions</h3>', 'class="modal-body">'), '</div>', $TADpos));
					$arr_prgm[$ProgramID]['TermsAndConditions'] = addslashes($TermsAndConditions);
				} else {
                    $arr_prgm[$ProgramID]['TermsAndConditions'] = '';
                }

				if ($KPDpos = strpos($result, 'Keyword policy details')) {
					$KeywordPolicyDetails = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Keyword policy details</h3>', 'class="modal-body">'), '</div>', $TADpos));
					$arr_prgm[$ProgramID]['KeywordPolicyDetails'] = addslashes($KeywordPolicyDetails);
				} else {
                    $arr_prgm[$ProgramID]['KeywordPolicyDetails'] = '';
                }

				$arr_prgm[$ProgramID] += array(
					'LogoUrl' => addslashes(trim($LogoUrl)),
					'Homepage' => addslashes(trim($Homepage)),
					'Country' => addslashes(trim($Country)),
					'LiveSinceDate' => addslashes(trim($LiveSinceDate)),
					'PPCTermAndCondition' => addslashes(trim($PPCTermAndCondition)),
					'Currency' => addslashes(trim($Currency)),
					'PaymentType' => addslashes(trim($PaymentType)),
					'LastPaymenDate' => addslashes(trim($LastPaymenDate)),
					'PayOnPostage' => addslashes(trim($PayOnPostage)),
					'PayOnVAT' => addslashes(trim($PayOnVAT)),
					'ContactAccountManagerName' => addslashes(trim($ContactAccountManagerName)),
					'ContactAccountManagerEmail' => addslashes(trim($ContactAccountManagerEmail)),
					'ContactAdvertiserName' => isset($ContactAdvertiserName) ? addslashes(trim($ContactAdvertiserName)) : '',
					'ContactAdvertiserEmail' => isset($ContactAdvertiserEmail) ? addslashes(trim($ContactAdvertiserEmail)) : '',
					'ContactAdvertiserPhoneNumber' => isset($ContactAdvertiserPhoneNumber) ? addslashes(trim($ContactAdvertiserPhoneNumber)) : '',
					'CommissionDetails' => addslashes(trim($CommissionDetails)),
					'CookiePeriod' => addslashes(trim($CookiePeriod)),
					'ConversionRate' => addslashes(trim($ConversionRate)),
					'EPHC' => addslashes(trim($EPHC)),
					'AverageValidationPeriod' => addslashes(trim($AverageValidationPeriod)),
					'AOV' => addslashes(trim($AOV)),
					'ProgramKeyFacts' => addslashes(trim($ProgramKeyFacts)),
					'PPCPolicyOverview' => addslashes(trim($PPCPolicyOverview)),
					'Geotargeting' => addslashes(trim($Geotargeting)),
					'Blogs' => (isset($marketing_clumns_arr['Blogs']) && $marketing_clumns_arr['Blogs'] == 'tick') ? 'Yes' : 'No',
					'BrowserPlugins' => (isset($marketing_clumns_arr['BrowserPlugins']) && $marketing_clumns_arr['BrowserPlugins'] == 'tick') ? 'Yes' : 'No',
					'CompetitionOrFreebieSites' => (isset($marketing_clumns_arr['Competition/FreebieSites']) && $marketing_clumns_arr['Competition/FreebieSites'] == 'tick') ? 'Yes' : 'No',
					'ContentRewards' => (isset($marketing_clumns_arr['ContentRewards']) && $marketing_clumns_arr['ContentRewards'] == 'tick') ? 'Yes' : 'No',
					'DatafeedDriven' => (isset($marketing_clumns_arr['DatafeedDriven']) && $marketing_clumns_arr['DatafeedDriven'] == 'tick') ? 'Yes' : 'No',
					'DirectPPC' => (isset($marketing_clumns_arr['DirectPPC']) && $marketing_clumns_arr['DirectPPC'] == 'tick') ? 'Yes' : 'No',
					'DiscountVoucherSites' => (isset($marketing_clumns_arr['DiscountVoucherSites']) && $marketing_clumns_arr['DiscountVoucherSites'] == 'tick') ? 'Yes' : 'No',
					'EmailMarketing' => (isset($marketing_clumns_arr['EmailMarketing']) && $marketing_clumns_arr['EmailMarketing'] == 'tick') ? 'Yes' : 'No',
					'EmployeeMalls' => (isset($marketing_clumns_arr['EmployeeMalls']) && $marketing_clumns_arr['EmployeeMalls'] == 'tick') ? 'Yes' : 'No',
					'IncentiveOrloyaltySites' => (isset($marketing_clumns_arr['Incentive/loyaltySites']) && $marketing_clumns_arr['Incentive/loyaltySites'] == 'tick') ? 'Yes' : 'No',
					'PPCToOwnSite' => (isset($marketing_clumns_arr['PPCToOwnSite']) && $marketing_clumns_arr['PPCToOwnSite'] == 'tick') ? 'Yes' : 'No',
					'PriceComparison' => (isset($marketing_clumns_arr['PriceComparison']) && $marketing_clumns_arr['PriceComparison'] == 'tick') ? 'Yes' : 'No',
					'Remarketing' => (isset($marketing_clumns_arr['Remarketing']) && $marketing_clumns_arr['Remarketing'] == 'tick') ? 'Yes' : 'No',
					'Retargeting' => (isset($marketing_clumns_arr['Retargeting']) && $marketing_clumns_arr['Retargeting'] == 'tick') ? 'Yes' : 'No',
					'SEO' => (isset($marketing_clumns_arr['SEO']) && $marketing_clumns_arr['SEO'] == 'tick') ? 'Yes' : 'No',
					'SocialCommerce' => (isset($marketing_clumns_arr['SocialCommerce']) && $marketing_clumns_arr['SocialCommerce'] == 'tick') ? 'Yes' : 'No',
					'Subnetworks' => (isset($marketing_clumns_arr['Subnetworks']) && $marketing_clumns_arr['Subnetworks'] == 'tick') ? 'Yes' : 'No',
					'TechnologyPartners' => (isset($marketing_clumns_arr['TechnologyPartners']) && $marketing_clumns_arr['TechnologyPartners'] == 'tick') ? 'Yes' : 'No',
					'Toolbars' => (isset($marketing_clumns_arr['Toolbars']) && $marketing_clumns_arr['Toolbars'] == 'tick') ? 'Yes' : 'No',
				);

				$deeplinkUrl = "http://www.webgains.com/front/publisher/program/get-tools/programid/{$ProgramID}?callback=&_=";
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_DeeplinksAPI_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$result = $this->GetHttpResultMoreTry($deeplinkUrl, $request);
					$this->oLinkFeed->fileCachePut($cache_file, $result);
				}
				$deeplinkMsg = file_get_contents($cache_file);
				if ($deeplinkMsg) {
					$deeplinkMsg = json_decode($deeplinkMsg, true);
					$arr_prgm[$ProgramID]['Deeplinks'] = $deeplinkMsg['deep_links'];
					$arr_prgm[$ProgramID]['AffDefaultUrl'] = "http://track.webgains.com/click.html?wgcampaignid={$SiteIdInAff}&wgprogramid={$ProgramID}";
				} else {
                    $arr_prgm[$ProgramID]['Deeplinks'] = '';
                    $arr_prgm[$ProgramID]['AffDefaultUrl'] = '';
                }
				$program_num++;

				if (count($arr_prgm) > 20) {
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
		}

		if (count($arr_prgm) > 0) {
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			unset($arr_prgm);
		}

		echo "\n\tGet Program by page end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
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


?>