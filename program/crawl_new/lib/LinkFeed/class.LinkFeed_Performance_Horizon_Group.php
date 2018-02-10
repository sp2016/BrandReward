<?php
/**
 * User: rzou
 * Date: 2017/9/1
 * Time: 15:05
 */
class LinkFeed_Performance_Horizon_Group
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

        $three_status = array(
            "approved" => sprintf('https://p3tew145y3tag41n:%s@api.performancehorizon.com/user/publisher/%s/campaign/a/tracking', $APIKey, $SiteIdInAff),
            "pending" => sprintf('https://p3tew145y3tag41n:%s@api.performancehorizon.com/user/publisher/%s/campaign/p/tracking', $APIKey, $SiteIdInAff),
            "rejected" => sprintf('https://p3tew145y3tag41n:%s@api.performancehorizon.com/user/publisher/%s/campaign/r/tracking', $APIKey, $SiteIdInAff)
        );

        foreach ($three_status as $key => $val) {
            echo "\tGet $key program start!\r\n";

            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_$key" . date("Ym") . "_data.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $results = $this->GetHttpResultMoreTry($val, $request);
                $this->oLinkFeed->fileCachePut($cache_file, $results);
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);

            if (!isset($data['campaigns']) || empty($data['campaigns'])) {
                mydie('Can\'t get data, please check the api !');
            }

            foreach ($data['campaigns'] as $v) {
                $prgm = $v['campaign'];
                $ProgramID = trim($prgm['campaign_id']);

                if (!$ProgramID) {
                    continue;
                }
                $desc = $this->ObjectArrToString($prgm['description']);
                $contactDetails = strip_tags($this->oLinkFeed->ParseStringBy2Tag($desc, '<b>Contact Details</b>','<b'));

                $arr_prgm[$ProgramID] = array(
                    "SiteID" => $SiteID,
                    "AccountID" => $this->account['AccountID'],
                    'AffID' => $this->info['AffID'],
                    'IdInAff' => $ProgramID,
                    'ProgramID' => $ProgramID,
                    'BatchID' => $this->oLinkFeed->batchid,
                    'PartnershipStatus' => $key,
                    'Name' => addslashes($prgm['title']),
                    'Status' => addslashes($prgm['status']),
                    'ReportingTimezone' => addslashes($prgm['reporting_timezone']),
                    'ConversionType' => addslashes($prgm['conversion_type']),
                    'DestinationUrl' => addslashes($prgm['destination_url']),
                    'MultipleConversionsPerClick' => addslashes($prgm['multiple_conversions_per_click']),
                    'CookieStatus' => addslashes($prgm['cookie_status']),
                    'TqEnabled' => addslashes($prgm['tq_enabled']),
                    'IsCpc' => addslashes($prgm['is_cpc']),
                    'CampaignLogo' => addslashes($prgm['campaign_logo']),
                    'Description' => addslashes($desc),
                    'AssociatedCampaigns' => addslashes($prgm['associated_campaigns']),
                    'RestrictedDeepLinking' => addslashes($prgm['restricted_deep_linking']),
                    'ExtraRestrictedDeepLinkingDomains' => addslashes($prgm['extra_restricted_deep_linking_domains']),
                    'TestMode' => addslashes($prgm['test_mode']),
                    'AllowDeepLinking' => addslashes($prgm['allow_deep_linking']),
                    'AllowThirdPartyPixel' => addslashes($prgm['allow_third_party_pixel']),
                    'LeadConfirmationUrlSuccess' => addslashes($prgm['lead_confirmation_url_success']),
                    'LeadConfirmationUrlFail' => addslashes($prgm['lead_confirmation_url_fail']),
                    'CookiePeriod' => addslashes($prgm['cookie_period']),
                    'VerticalName' => addslashes($prgm['vertical_name']),
                    'DefaultCommissionRate' => addslashes($prgm['default_commission_rate']),
                    'DefaultCommissionValue' => isset($prgm['default_commission_value']) ? addslashes($prgm['default_commission_value']) : '',
                    'Commissions' => empty($prgm['commissions']) ? '' : addslashes($this->ObjectArrToString($prgm['commissions'])),
                    'Terms' => addslashes($this->ObjectArrToString($prgm['terms'])),
                    'CampaignCurrencyConversions' => empty($prgm['campaign_currency_conversions']) ? '' : addslashes($this->ObjectArrToString($prgm['campaign_currency_conversions'])),
                    'PublisherStatus' => addslashes($prgm['publisher_status']),
                    'TrackingLink' => addslashes($prgm['tracking_link']),
                    'Camref' => addslashes($prgm['camref']),
                    'ContactDetails' => addslashes($contactDetails),
                );

                $program_num++;

                if (count($arr_prgm) > 0) {
                    $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }
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

    function ObjectArrToString($oArr) {
        $result = '';
        if (empty($oArr) || !is_array($oArr)) {
            return $oArr;
        }

        foreach ($oArr as $key=>$val) {
            if (is_array($val)) {
                $result .= $key . ' : [' . $this->ObjectArrToString($val) . '];';
            } else {
                $result .= $key . ' : ' . $val . ';';
            }
        }

        return $result;
    }
}


?>