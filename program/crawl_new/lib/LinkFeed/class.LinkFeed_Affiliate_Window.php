<?php
require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_Affiliate_Window
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->API_KEY_10 = '54bfa4a718a791962c69bd2673dd6ee2';
        $this->USERID = '274181';
        $this->feed_key = '1d3383f9b4bbcc85bd4564990992b9bb';

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
        $this->GetProgramByApi($this->site['SiteID']);

        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->oLinkFeed->checkBatchID = $this->oLinkFeed->batchID;
        $this->oLinkFeed->CheckCrawlBatchData($this->info["AffID"], $this->site['SiteID']);
    }

    function GetProgramByApi($SiteID)
    {
        echo "\tGet Program by api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array("AffId" => $this->info["AffID"], "method" => "post", "postdata" => "",);

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info);

        $use_true_file_name = true;

        //step 2, get programs from csv feed.
        $allstatus = array(
            "active" => "approval",
            "notJoined" => "not apply",
            "pendingApproval" => "pending",
            "merchantSuspended" => "declined",
            "merchantRejected" => "declined",
            "closed" => "siteclosed",
        );
        $title = '"advertiserId","programmeName"';
        foreach ($allstatus as $status_aff => $status) {
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "programCSV_" . "$status_aff" . "_" . date("Ym") . ".dat", $this->batchProgram, $use_true_file_name);//返回.cache文件的路径
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) //fileCacheIsCached函数检查$cache_file是否存在
            {
                $request["method"] = "get";
                $strUrlAllMerchant = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-directory/export/?membershipStatus=" . $status_aff . "&view=+";
                $result = $this->GetHttpResultMoreTry($strUrlAllMerchant, $request);
                if (stripos($result, $title) === false) {
                    mydie("die: get merchant csv file failed, title not found; $strUrlAllMerchant \n");
                }
                $this->oLinkFeed->fileCachePut($cache_file, $result);//生成.cache文件,并将cvs数据写入此文件
            }
            if (!file_exists($cache_file)) mydie("die: merchant csv file does not exist. \n");
            //Open CSV File
            $arr_title = array();
            $col_count = 0;
            $fhandle = fopen($cache_file, 'r');//只读方式打开文件
            $first = true;
            while ($line = fgetcsv($fhandle, 50000))//fgetcsv函数返回csv文件的一行，while循环csv中所有记录
            {
                if ($first) {
                    // [0] => advertiserId [1] => programmeName [2] => conversionRate [3] => approvalRate [4] => validationTime [5] => epc [6] => joinDate [7] => paymentStatus [8] => paymentRiskLevel [9] => awinIndex [10] => feedEnabled [11] => productReporting [12] => commissionMin [13] => commissionMax [14] => leadMin [15] => leadMax [16] => cookieLength [17] => parentSectors [18] => subSectors [19] => primarySector
                    if ($line[0] != 'advertiserId') mydie("die: title is wrong. \n");
                    $arr_title = $line;
                    $col_count = sizeof($arr_title);
                    $first = false;
                    continue;
                }
                if ($line[0] == '' || $line[0] == 'advertiserId') continue;
                if (sizeof($line) != $col_count) {
                    echo "warning: invalid line found: " . implode(",", $line) . "\n";
                    continue;
                }
                $row = array();
                foreach ($arr_title as $i => $title)
                    $row[$title] = $line[$i];//$row是一个存有当前记录的title和值的关联数组

                if ($status_aff == "active") {
                    $Partnership = "Active";//$Partnership代表我们与商家的关系
                    $StatusInAff = "Active";//$StatusInAff代表商家在联盟的状态
                } elseif ($status_aff == "notJoined") {
                    $Partnership = "NoPartnership";
                    $StatusInAff = "Active";
                } elseif ($status_aff == "pendingApproval") {
                    $Partnership = "Pending";
                    $StatusInAff = "Active";
                } elseif ($status_aff == "merchantSuspended" || $status_aff == "merchantRejected") {
                    $Partnership = "Declined";
                    $StatusInAff = "Active";
                } else {
                    $Partnership = "NoPartnership";
                    $StatusInAff = "Offline";
                }

                $arr_prgm[$row["advertiserId"]] = array(
                    "Name" => addslashes(html_entity_decode(trim($row["programmeName"]))),
                    "SiteID" => $SiteID,
                    "AccountID" => $this->account['AccountID'],
                    "BatchID" => $this->oLinkFeed->batchID,
                    "AffID" => $this->info["AffID"],
                    "IdInAff" => $row["advertiserId"],
                    "JoinedNetworkDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
                    "StatusInAffRemark" => $status_aff,
                    "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "CookieTime" => addslashes($row["cookieLength"]),
                    "EPCDefault" => $row["epc"],
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "Category" => addslashes(str_replace("|", ",", $row['parentSectors'])),
                    "PaymentDays" => $row['averagePaymentTime'],
                    "approvalRate" => $row['approvalRate'],
                );

                $prgm_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile/{$row["advertiserId"]}";
                $arr_prgm[$row["advertiserId"]]['DetailPage'] = $prgm_url;
                $request["method"] = "get";
                $request["postdata"] = "";
                $prgm_detail = $this->GetHttpResultMoreTry($prgm_url, $request);
                if ($prgm_detail) {
                    $MobileOptimisedStr = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h4>Mobile Optimised</h4>', '<i class="fa fa-mobile fa-2x"></i>'), '<'));
                    if ($MobileOptimisedStr == 'YES') {
                        $MobileOptimised = 'YES';
                    } else if ($MobileOptimisedStr == 'NO') {
                        $MobileOptimised = 'NO';
                    } else {
                        $MobileOptimised = 'UNKNOWN';
                    }
                    $arr_prgm[$row["advertiserId"]]['MobileOptimised'] = $MobileOptimised;

                    $desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="descriptionLongContent" class="inlineTextArea">', '</div>'));
                    $arr_prgm[$row["advertiserId"]]['Description'] = addslashes($desc);


                    //ParseStringBy2Tag函数返回div标签之间的content
                    $Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('text-center', '<a target="_blank" href="'), '"'));
                    $arr_prgm[$row["advertiserId"]]['Homepage'] = $Homepage;
                    $arr_prgm[$row["advertiserId"]]['ContactPerson'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<i class="fa fa-user"></i>', '<')));
                    $tmp_contacts = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Email', '"mailto:'), '"'));
                    $Contacts = "";
                    if ($tmp_contacts) {
                        $Contacts = "Email: {$tmp_contacts}";
                        $arr_prgm[$row["advertiserId"]]['ContactEmail'] = addslashes($Contacts);
                    }
                    $arr_prgm[$row["advertiserId"]]['ContactTelephone'] = addslashes(trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<td class="col-lg-3 text-light text-right">', 'Telephone:'), '</tr>'))));

                    $arr_prgm[$row["advertiserId"]]['Regions'] = addslashes(trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h2>Regions</h2>', '<ul class="salesRegions list-inline">'), '</ul>'))));
                    $arr_prgm[$row["advertiserId"]]['LogoUrl'] = addslashes($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('viewProfilePicture', '<img src="'), '"'));
                    $arr_prgm[$row["advertiserId"]]['AcceptedDate'] = date('Y-m-d H:i:s', strtotime(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h4>Date Joined</h4>', '<p>'), '</p>'))));
                    $arr_prgm[$row["advertiserId"]]['AutoValidationPeriod'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h4>Auto Validation Period</h4>', '>'), '</p>')));
                    $arr_prgm[$row["advertiserId"]]['TotalProducts'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h4>Total Products</h4>', '>'), '</p>')));
                    $arr_prgm[$row["advertiserId"]]['LastUpdatedFromAff'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h4>Last Updated</h4>', '>'), '</p>')));

                    $Links = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div class="list-group list-group-icons">', '</div>'));
                    $LineStart = 0;
                    $arr_prgm[$row["advertiserId"]]['BlogUrl'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($Links, '<a href="', '" class="list-group-item"><i class="fa fa-feed fa-fw">', $LineStart)));
                    $arr_prgm[$row["advertiserId"]]['Twitter'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($Links, '<a href="', '" class="list-group-item"><i class="fa fa-twitter fa-fw">', $LineStart)));
                    $arr_prgm[$row["advertiserId"]]['Facebook'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($Links, '<a href="', '" class="list-group-item"><i class="fa fa-facebook fa-fw">', $LineStart)));
                }

                //commission
                $check_commission_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile/{$row["advertiserId"]}/xhr-commission-group-search/";
                $comm_detail = $this->GetHttpResultMoreTry($check_commission_url, $request);
                if ($comm_detail) {
                    preg_match_all("/<td class=\"commissionLevel current\">(.*?)<\/td>/i", $comm_detail, $m);
                    if (count($m[1])) {
                        $tmp_comm = array();
                        foreach ($m[1] as $v) {
                            preg_match('@class="tooltipRight">(.*?)<@i', $v, $mm);
                            if (!empty($mm[1])) {
                                $tmp_comm[] = trim($mm[1]);
                            }
                        }
                        $arr_prgm[$row["advertiserId"]]["Commission"] = addslashes(implode('|', $tmp_comm));
                    }else {
                        preg_match_all('@commissionLevel">(.*?)</td>@i', $comm_detail, $m);
                        if (count($m[1])) {
                            $tmp_comm = array();
                            foreach ($m[1] as $v) {
                                preg_match('@class="tooltipRight">(.*?)<@i', $v, $mm);
                                if (!empty($mm[1])) {
                                    $tmp_comm[] = trim($mm[1]);
                                }
                            }
                            $arr_prgm[$row["advertiserId"]]["CommissionExt"] = addslashes(implode('|', $tmp_comm));
                        }
                    }
                }

                //termAndCondition
                $term_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}";
                $term_detail = $this->GetHttpResultMoreTry($term_url, $request);
                if ($term_detail) {
                    $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($term_detail, '<div id="termsFreeTextContent" class="inlineTextArea">', '</div>'));
                    $arr_prgm[$row["advertiserId"]]['TermAndCondition'] = addslashes($TermAndCondition);
                }

                //PPC terms
                $PPC_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}/ppc";
                $PPC_detail = $this->GetHttpResultMoreTry($PPC_url, $request);
                if ($PPC_detail) {
                    $LineStart = 0;
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'If publishers promote you via PPC, will they be entitled to full commission?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['ViaPPCPromote'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['ViaPPCPromote'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['ViaPPCPromote'] = 'UNKNOWN';
                    } else {
                        mydie("ViaPPCPromote is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'website from search engines, will they receive full commission?<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowSearchEnginesLink'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowSearchEnginesLink'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowSearchEnginesLink'] = 'UNKNOWN';
                    } else {
                        mydie("AllowSearchEnginesLink is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'brand name in their display URL, will they receive full commission?<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowDisplayBrandNameInURL'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowDisplayBrandNameInURL'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowDisplayBrandNameInURL'] = 'UNKNOWN';
                    } else {
                        mydie("AllowDisplayBrandNameInURL is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'paid search title and description, will they receive full commission?<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowUseBrandNameInPaidSearch'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowUseBrandNameInPaidSearch'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowUseBrandNameInPaidSearch'] = 'UNKNOWN';
                    } else {
                        mydie("AllowUseBrandNameInPaidSearch is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'negative keyword list, will they receive full commission?<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameIntoNegativeKeywords'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameIntoNegativeKeywords'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameIntoNegativeKeywords'] = 'UNKNOWN';
                    } else {
                        mydie("AllowBrandNameIntoNegativeKeywords is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, '(e.g. vodafone, voda fone)<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameEnteredSearchKeywords'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameEnteredSearchKeywords'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameEnteredSearchKeywords'] = 'UNKNOWN';
                    } else {
                        mydie("AllowBrandNameEnteredSearchKeywords is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, '(e.g. vodofone)<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'UNKNOWN';
                    } else {
                        mydie("AllowMisspellingsBrandNameEnteredSearchKeywords is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, '(e.g. Vodafone Mobile)<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameAndAnotherWordIntoSearchKeywords'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameAndAnotherWordIntoSearchKeywords'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['AllowBrandNameAndAnotherWordIntoSearchKeywords'] = 'UNKNOWN';
                    } else {
                        mydie("AllowBrandNameAndAnotherWordIntoSearchKeywords is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'generated by brand related terms?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['HaveRestrictedPublishEarnCommissionByBrandRelatedTerms'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['HaveRestrictedPublishEarnCommissionByBrandRelatedTerms'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['HaveRestrictedPublishEarnCommissionByBrandRelatedTerms'] = 'UNKNOWN';
                    } else {
                        mydie("HaveRestrictedPublishEarnCommissionByBrandRelatedTerms is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'Google<td><img src="', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $SupportSearchEnginesForPaidSearch[] = 'Google';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'Yahoo<td><img src="', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $SupportSearchEnginesForPaidSearch[] = 'Yahoo';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'Bing<td><img src="', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $SupportSearchEnginesForPaidSearch[] = 'Bing';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'Other<td><img src="', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $SupportSearchEnginesForPaidSearch[] = 'Other';
                    }
                    if (isset($SupportSearchEnginesForPaidSearch)) {
                        $arr_prgm[$row["advertiserId"]]['SupportSearchEnginesForPaidSearch'] = implode(',', $SupportSearchEnginesForPaidSearch);
                    } else {
                        $arr_prgm[$row["advertiserId"]]['SupportSearchEnginesForPaidSearch'] = '';
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($PPC_detail, 'eligible to earn commission</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['HasSpecificSearchKeywordsMakeSaleNoCommissionFromPPCAds'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['HasSpecificSearchKeywordsMakeSaleNoCommissionFromPPCAds'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['HasSpecificSearchKeywordsMakeSaleNoCommissionFromPPCAds'] = 'UNKNOWN';
                    } else {
                        mydie("HasSpecificSearchKeywordsMakeSaleNoCommissionFromPPCAds is '" . $whether_Image . "', please check it\n\r");
                    }
                }

                //transaction terms
                $tran_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}/transaction";
                $tran_detail = $this->GetHttpResultMoreTry($tran_url, $request);
                if ($tran_detail) {
                    $LineStart = 0;
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'VAT / sales tax?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeVATOrSalesTax'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeVATOrSalesTax'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeVATOrSalesTax'] = 'UNKNOWN';
                    } else {
                        mydie("CommissionsIncludeVATOrSalesTax is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'delivery charges?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeDeliveryCharges'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeDeliveryCharges'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeDeliveryCharges'] = 'UNKNOWN';
                    } else {
                        mydie("CommissionsIncludeDeliveryCharges is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'credit card fees?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeCreditCardFees'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeCreditCardFees'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeCreditCardFees'] = 'UNKNOWN';
                    } else {
                        mydie("CommissionsIncludeCreditCardFees is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'service charges?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeGiftWrappingOrOtherServiceCharges'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeGiftWrappingOrOtherServiceCharges'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['CommissionsIncludeGiftWrappingOrOtherServiceCharges'] = 'UNKNOWN';
                    } else {
                        mydie("CommissionsIncludeGiftWrappingOrOtherServiceCharges is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'product categories?</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['HasSomeProductOrCategoriesNotPaidCommission'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['HasSomeProductOrCategoriesNotPaidCommission'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['HasSomeProductOrCategoriesNotPaidCommission'] = 'UNKNOWN';
                    } else {
                        mydie("HasSomeProductOrCategoriesNotPaidCommission is '" . $whether_Image . "', please check it\n\r");
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Order cancelled<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Order cancelled';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Item was returned<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Item was returned';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Customer failed credit check<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Customer failed credit check';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Breach of programme terms and conditions<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Breach of programme terms and conditions';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Duplicate order<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Duplicate order';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Item was out of stock<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Item was out of stock';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($tran_detail, 'Other<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $CommissionDeclinedReson[] = 'Other';
                    }
                    if (isset($CommissionDeclinedReson)) {
                        $arr_prgm[$row["advertiserId"]]['CommissionDeclinedReson'] = implode(';', $CommissionDeclinedReson);
                    } else {
                        $arr_prgm[$row["advertiserId"]]['CommissionDeclinedReson'] = '';
                    }
                }

                //Notice Periods terms
                $period_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}/period";
                $period_detail = $this->GetHttpResultMoreTry($period_url, $request);
                if ($period_detail) {
                    $arr_prgm[$row["advertiserId"]]['ChangeTermsAndConditionDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($period_detail, 'programme how many days notice would you like to give publishers?</b></td><td><p>', '<')));
                    $arr_prgm[$row["advertiserId"]]['UpdateWebsiteDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($period_detail, 'unavailable how many days notice would you like to give publishers?</b></td><td><p>', '<')));
                    $arr_prgm[$row["advertiserId"]]['LowerCommissionDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($period_detail, 'commissions, how many days notice would you like to give publishers?</b></td><td><p>', '<')));
                }

                //publisher terms
                $publisher_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}/affiliate";
                $publisher_detail = $this->GetHttpResultMoreTry($publisher_url, $request);
                if ($publisher_detail) {
                    $LineStart = 0;
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Cashback<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Cashback';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Community<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Community';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Content<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Content';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Discount Code<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Discount Code';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Email<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Email';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Loyalty<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Loyalty';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Search<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Search';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Behavioural Retargeting<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Behavioural Retargeting';
                    }
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'Media Brokers<td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $AllowedPromotionalTypes[] = 'Media Brokers';
                    }
                    if (isset($AllowedPromotionalTypes)) {
                        $arr_prgm[$row["advertiserId"]]['AllowedPromotionalTypes'] = implode(';', $AllowedPromotionalTypes);
                    } else {
                        $arr_prgm[$row["advertiserId"]]['AllowedPromotionalTypes'] = '';
                    }

                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($publisher_detail, 'age restricted products.</b></td><td><img src="', '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['HasOtherRestrictions'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['HasOtherRestrictions'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['HasOtherRestrictions'] = 'UNKNOWN';
                    } else {
                        mydie("HasOtherRestrictions is '" . $whether_Image . "', please check it\n\r");
                    }
                }

                //dedupe terms
                $dedupe_url = "https://ui.awin.com/awin/affiliate/" . $this->USERID . "/merchant-profile-terms/{$row["advertiserId"]}/dedupe";
                $dedupe_detail = $this->GetHttpResultMoreTry($dedupe_url, $request);
                if ($dedupe_detail) {
                    $whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($dedupe_detail, array('advertising channels?','<img src="'), '"', $LineStart));
                    if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png') {
                        $arr_prgm[$row["advertiserId"]]['DeduplicateOtherOnlineAdvertisingChannels'] = 'NO';
                    } elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png') {
                        $arr_prgm[$row["advertiserId"]]['DeduplicateOtherOnlineAdvertisingChannels'] = 'YES';
                    } elseif (!$whether_Image) {
                        $arr_prgm[$row["advertiserId"]]['DeduplicateOtherOnlineAdvertisingChannels'] = 'UNKNOWN';
                    } else {
                        mydie("DeduplicateOtherOnlineAdvertisingChannels is '" . $whether_Image . "', please check it\n\r");
                    }
                }

                $program_num ++;
                echo "$program_num\t";

                if (count($arr_prgm) > 0) {
                    $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }
            }
        }

        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }
        
        echo "\tGet Program by api end\r\n";
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