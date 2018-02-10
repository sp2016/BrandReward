<?php
/**
 * User: rzou
 * Date: 2017/8/30
 * Time: 9:56
 */
class LinkFeed_ClixGalore
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;

    }

    function Login()
    {
        $request = array("method" => "get", "postdata" => "", 'SSLV' => 3 );
        $r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'],$request);
        $result = $r["content"];
        $__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
        $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
        $this->info["AffLoginPostString"] = "__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE={$__VIEWSTATE}&__EVENTVALIDATION={$__EVENTVALIDATION}&{$this->info['AffLoginPostString']}&cmd_login.x=27&cmd_login.y=10";
        $loginStatus = $this->oLinkFeed->LoginIntoAffService($this->info["AffID"],$this->info);
        if ($loginStatus) {
            echo "\tLogin success!\n";
        }
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
        foreach ($this->site as $v) {
            echo 'Site:' . $v['Name'] . "\r\n";
            $this->GetProgramByPage($v['SiteID']);
        }
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->CheckBatch();
    }

    function GetProgramByPage($SiteID)
    {
        echo "\tGet Program by Page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;
        $request = array("AffId" => $this->info["AffID"], "method" => "post", "postdata" => "", 'SSLV'=> 3);
        $temp_request = array("AffId" => $this->info["AffID"], "method" => "get", 'SSLV'=> 3);
        $this->Login();

        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], date("Ym") . "search_page.dat", $this->batchProgram, $use_true_file_name);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            $results = $this->GetHttpResultMoreTry('http://www.clixgalore.com/SearchMerchants.aspx', $temp_request);
            $this->oLinkFeed->fileCachePut($cache_file, $results);
        }
        $dataSearchResult = file_get_contents($cache_file);

        if (strpos($dataSearchResult, 'clixGalore - Search Merchants') === false) {
            mydie("Can't get search page!");
        }

        $hasNextPage = true;
        $page = 1;
        $__EVENTTARGET = '';
        while ($hasNextPage) {
            echo "\t page $page.";

            if ($page == 1) {
                $__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('name="__EVENTVALIDATION"', 'value="'), '"'));
                $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('name="__VIEWSTATE"', 'value="'), '"'));
                $request["postdata"] = "__EVENTTARGET={$__EVENTTARGET}&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE={$__VIEWSTATE}&__EVENTVALIDATION={$__EVENTVALIDATION}&chk_psale=on&chk_sale=on&chk_lead=on&chk_click=on&txt_keywords=&dd_currency=&dd_saved=0&txt_save_name=&txt_banner_width=&txt_banner_height=&cmd_search=Search+Active+Merchant+Programs";
            } else {
                $__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('name="__EVENTVALIDATION"', 'value="'), '"'));
                $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('name="__VIEWSTATE"', 'value="'), '"'));
                $request["postdata"] = "__EVENTTARGET={$__EVENTTARGET}&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE={$__VIEWSTATE}&__EVENTVALIDATION={$__EVENTVALIDATION}&chk_psale=on&chk_sale=on&chk_lead=on&chk_click=on&txt_keywords=&dd_currency=&dd_saved=0&txt_save_name=&txt_banner_width=&txt_banner_height=";
            }

            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $dataSearchResult = $this->GetHttpResultMoreTry('http://www.clixgalore.com/SearchMerchants.aspx', $request);
                $this->oLinkFeed->fileCachePut($cache_file, $dataSearchResult);
            }
            $dataSearchResult = file_get_contents($cache_file);
            $dataSearchResult = preg_replace('@>\s+<@', '><', $dataSearchResult);

            if (strpos($dataSearchResult, 'clixGalore - Search Merchants') === false) {
                mydie('Can\'t get data, please check the page !');
            }

            $nLineStart = 0;
            while ($nLineStart >= 0) {
                $nLineStart = stripos($dataSearchResult, "javascript:window.status='View Merchant Details'", $nLineStart);
                if ($nLineStart === false)
                    break;

                $ProgramID = intval($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, 'href="javascript:OpenDetails(', ')', $nLineStart));
                if (!$ProgramID)
                    continue;

                $Name = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, '>', '<', $nLineStart));
                $datailPage = 'http://www.clixgalore.com/PopupMerchantDetails.aspx?ID=' . $ProgramID;
                $OfferRate = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('_Label1"','>'), '<', $nLineStart));
                $MerchantEPC = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('<td', '<td', '>'), '</td', $nLineStart));
                $CookieExpiryPeriod = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('_lbl_CookiePeriod', '>'), '<', $nLineStart));
                $AffiliateApprovalRate = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('<td', '>'), '</td', $nLineStart));

                //get program deatail page!
                $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
                if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                    $detail_page = $this->GetHttpResultMoreTry($datailPage, $temp_request, 'Merchant Details');
                    if (!$detail_page) {
                        mydie("Can't get detailpage, programID:$ProgramID!");
                    } else {
                        $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                    }
                }
                $result = file_get_contents($cache_file);
                $result = preg_replace('@>\s+<@', '><', $result);

                $Homepage =trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="hyp_website_url"', 'href="'), '"'));
                $ProgramCurrency = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_Currency"', '>'), '<'));
                $ProgramType = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_program_type"', '>'), '<'));
                $CommissionRate = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_commission_rate"', '>'), '<'));
                $WebsiteDescription = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_description"', '>'), '</span')));
                $OrderConfirmation = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_approve_after"', '>'), '<'));
                $AutoRedeposit = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_auto_redeposit"', '>'), '</td')));
                $PendingSalesRedeposit = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_pending_redeposit"', '>'), '</td')));
                $NotAcceptingTrafficFrom = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_traffic"', '>'), '</td');
                $NotAcceptingRequestsFrom = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_aff_exclusion"', '>'), '</span');
                $LastRecordedTransaction = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="lbl_compliance"', '>'), '</span'));

                //get program termsAndCondition page!
                $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "termsPage_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
                if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                    $termsPage = $this->GetHttpResultMoreTry("http://www.clixgalore.com/popup_ViewMerchantTC.aspx?ID={$ProgramID}", $temp_request, 'Terms And Conditions');
                    if (!$termsPage) {
                        mydie("Can't get terms page, programID:$ProgramID!");
                    } else {
                        $this->oLinkFeed->fileCachePut($cache_file, $termsPage);
                    }
                }
                $termsPage = file_get_contents($cache_file);
                $TermsAndConditions = $this->oLinkFeed->ParseStringBy2Tag($termsPage, array('id="txt_tc"', '>'), '</textarea');

                $arr_prgm[$ProgramID] = array(
                    "SiteID" => $SiteID,
                    "AccountID" => $this->account['AccountID'],
                    'AffID' => $this->info['AffID'],
                    'IdInAff' => $ProgramID,
                    'ProgramID' => $ProgramID,
                    'BatchID' => $this->oLinkFeed->batchid,
                    'Name' => addslashes($Name),
                    'Homepage' => addslashes($Homepage),
                    'OfferRate' => addslashes($OfferRate),
                    'MerchantEPC' => addslashes($MerchantEPC),
                    'CookieExpiryPeriod' => addslashes($CookieExpiryPeriod),
                    'AffiliateApprovalRate' => addslashes($AffiliateApprovalRate),
                    'ProgramCurrency' => addslashes($ProgramCurrency),
                    'ProgramType' => addslashes($ProgramType),
                    'CommissionRate' => addslashes($CommissionRate),
                    'WebsiteDescription' => addslashes($WebsiteDescription),
                    'OrderConfirmation' => addslashes($OrderConfirmation),
                    'AutoRedeposit' => addslashes($AutoRedeposit),
                    'PendingSalesRedeposit' => addslashes($PendingSalesRedeposit),
                    'NotAcceptingTrafficFrom' => addslashes($NotAcceptingTrafficFrom),
                    'NotAcceptingRequestsFrom' => addslashes($NotAcceptingRequestsFrom),
                    'LastRecordedTransaction' => addslashes($LastRecordedTransaction),
                    'TermsAndConditions' => addslashes($TermsAndConditions),
                );

                $program_num++;
                if (count($arr_prgm) > 0) {
                    $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }
            }

            $tmp_target = trim($this->oLinkFeed->ParseStringBy2Tag($dataSearchResult, array('Pages Found:', "<span>$page</span>", 'href="javascript:__doPostBack(\'merchant_results$ctl14$ct'), "'"));
            if ($tmp_target == false) {
                $hasNextPage = false;
                break;
            } else {
                $__EVENTTARGET = urlencode('merchant_results$ctl14$ct' . $tmp_target);
                $page++;
            }

            if ($page > 500) {
                mydie("too many program in page!");
            }

        }
        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\n\tGet Program by Page end\r\n";
        if ($program_num < 10) {
            mydie("die: program count < 10, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
    }

    function CheckBatch()
    {
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

    function csv_string_to_array($content)
    {
        $r = array();
        $line_delimiter = "\n";
        $lines = explode($line_delimiter, $content);
        if (empty($lines) || !is_array($lines))
            return $r;
        for($i = 0; $i < count($lines); $i ++)
        {
            if ($i == 0)
                continue;
            $line = preg_replace('/[^(\x20-\x7F)]*/','', $lines[$i]);

            $temp = fopen("php://memory", "rw");
            fwrite($temp, $line);
            fseek($temp, 0);
            $fields = fgetcsv($temp, 4096, ',', '"');
            fclose($temp);

            if (empty($fields) || !is_array($fields) || count($fields) < 1)
                continue;
            $r[] = $fields;
        }
        return $r;
    }


}
