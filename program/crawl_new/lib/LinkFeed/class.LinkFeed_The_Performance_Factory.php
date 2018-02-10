<?php
/**
 * User: rzou
 * Date: 2017/8/31
 * Time: 15:48
 */
class LinkFeed_The_Performance_Factory
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;

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
            $this->GetProgramByApi($v['SiteID'], $v['SiteIdInAff'], $v['APIKey']);
        }
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->CheckBatch();
    }

    function GetProgramByApi($SiteID, $SiteIdInAff, $APIKey)
    {
        echo "\tGet Program by Api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;
        $request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);

        $strUrl = sprintf('https://%s.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=findAll', $SiteIdInAff, $APIKey);
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data.dat", $this->batchProgram, $use_true_file_name);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            $results = $this->GetHttpResultMoreTry($strUrl, $request);
            $this->oLinkFeed->fileCachePut($cache_file, $results);
        }
        $result = file_get_contents($cache_file);
        $data = json_decode($result, true);

        if (!isset($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200) {
            mydie('Can\'t get data, please check the api !');
        }

        foreach ($data['response']['data'] as $key => $val) {
            $prgm = $val['Offer'];

            $ProgramID = intval($prgm['id']);
            if (!$ProgramID) {
                continue;
            }

            $arr_prgm[$ProgramID] = array(
                "SiteID" => $SiteID,
                "AccountID" => $this->account['AccountID'],
                'AffID' => $this->info['AffID'],
                'IdInAff' => $ProgramID,
                'ProgramID' => $ProgramID,
                'BatchID' => $this->oLinkFeed->batchid,
                'Name' => addslashes(trim($prgm['name'])),
                'Description' => addslashes($prgm['description']),
                'RequireApproval' => addslashes($prgm['require_approval']),
                'RequireTermsAndConditions' => addslashes($prgm['require_terms_and_conditions']),
                'TermsAndConditions' => addslashes($prgm['terms_and_conditions']),
                'PreviewUrl' => addslashes($prgm['preview_url']),
                'Currency' => addslashes($prgm['currency']),
                'DefaultPayout' => addslashes($prgm['default_payout']),
                'Status' => addslashes($prgm['status']),
                'ExpirationDate' => addslashes($prgm['expiration_date']),
                'PayoutType' => addslashes($prgm['payout_type']),
                'PercentPayout' => addslashes($prgm['percent_payout']),
                'Featured' => addslashes($prgm['featured']),
                'ConversionCap' => addslashes($prgm['conversion_cap']),
                'MonthlyConversionCap' => addslashes($prgm['monthly_conversion_cap']),
                'PayoutCap' => addslashes($prgm['payout_cap']),
                'MonthlyPayoutCap' => addslashes($prgm['monthly_payout_cap']),
                'AllowWebsiteLinks' => addslashes($prgm['allow_website_links']),
                'AllowDirectLinks' => addslashes($prgm['allow_direct_links']),
                'ShowCustomVariables' => addslashes($prgm['show_custom_variables']),
                'ShowMailList' => addslashes($prgm['show_mail_list']),
                'EmailInstructions' => addslashes($prgm['email_instructions']),
                'EmailInstructionsFrom' => addslashes($prgm['email_instructions_from']),
                'EmailInstructionsSubject' => addslashes($prgm['email_instructions_subject']),
                'HasGoalsEnabled' => addslashes($prgm['has_goals_enabled']),
                'DefaultGoalName' => addslashes($prgm['default_goal_name']),
                'UseTargetRules' => addslashes($prgm['use_target_rules']),
                'IsExpired' => addslashes($prgm['is_expired']),
                'DneDownloadUrl' => addslashes($prgm['dne_download_url']),
                'DneUnsubscribeUrl' => addslashes($prgm['dne_unsubscribe_url']),
                'DneThirdPartyList' => addslashes($prgm['dne_third_party_list']),
                'ApprovalStatus' => addslashes($prgm['approval_status'])
            );

            //get trackingLink
            $tkLinkUrl = sprintf('https://%s.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=generateTrackingLink&offer_id=%s', $SiteIdInAff, $APIKey, $ProgramID);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "trackingLink_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $detail_page = $this->GetHttpResultMoreTry($tkLinkUrl, $request);
                if (!$detail_page) {
                    mydie("Can't get TrackingLink!");
                } else {
                    $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                }
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);
            if (!isset($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200) {
                mydie('Can\'t get data, please check the api !');
            }

            $arr_prgm[$ProgramID]['TrackingLinkUrl'] = isset($data['response']['data']['click_url']) ? addslashes($data['response']['data']['click_url']) : '';
            $arr_prgm[$ProgramID]['ImpressionPixel'] = isset($data['response']['data']['impression_pixel']) ? addslashes($data['response']['data']['impression_pixel']) : '';

            //get categories
            $tkLinkUrl = sprintf('https://%s.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=getCategories&&ids[]=%s', $SiteIdInAff, $APIKey, $ProgramID);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "Categories_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $detail_page = $this->GetHttpResultMoreTry($tkLinkUrl, $request);
                if (!$detail_page) {
                    mydie("Can't get Categories!");
                } else {
                    $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                }
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);
            if (!isset($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200) {
                mydie('Can\'t get data, please check the api !');
            }

            $ctgr = array();
            foreach ($data['response']['data'][0]['categories'] as $val) {
                $ctgr[] = $val['name'];
            }
            $arr_prgm[$ProgramID]['Categories'] = addslashes(join(',', $ctgr));

            //get payout detail
            $tkLinkUrl = sprintf('https://%s.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=getPayoutDetails&offer_id=%s', $SiteIdInAff, $APIKey, $ProgramID);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "PayoutDetails_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $detail_page = $this->GetHttpResultMoreTry($tkLinkUrl, $request);
                if (!$detail_page) {
                    mydie("Can't get PayoutDetails!");
                } else {
                    $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                }
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);
            if (!isset($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200) {
                mydie('Can\'t get data, please check the api !');
            }

            $arr_prgm[$ProgramID]['PayoutDetails'] = isset($data['response']['data']['offer_payout']) ? addslashes($this->objectArrToString($data['response']['data']['offer_payout'])) : '';

            //get Target Countries
            $tkLinkUrl = sprintf('https://%s.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=getTargetCountries&ids[]=%s', $SiteIdInAff, $APIKey, $ProgramID);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "TargetCountries_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $detail_page = $this->GetHttpResultMoreTry($tkLinkUrl, $request);
                if (!$detail_page) {
                    mydie("Can't get TargetCountries!");
                } else {
                    $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                }
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);
            if (!isset($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200) {
                mydie('Can\'t get data, please check the api !');
            }
            $arr_prgm[$ProgramID]['TargetCountries'] = (isset($data['response']['data']['countries']) && !empty($data['response']['data']['countries'])) ?
                                                        addslashes(join(',', array_values($data['response']['data']['countries']))) : '';

            $program_num++;

            if (count($arr_prgm) > 0) {
                $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                $arr_prgm = array();
            }

        }

        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\tGet Program by Api end\r\n";
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

    function objectArrToString($oArr) {
        $result = '';
        if (empty($oArr) || !is_array($oArr)) {
            return $oArr;
        }

        foreach ($oArr as $key=>$val) {
            if (is_array($val)) {
                $result .= $key . ' : [' . objectArrToString($val) . '];';
            } else {
                $result .= $key . ' : ' . $val . ';';
            }
        }

        return $result;
    }
}

?>