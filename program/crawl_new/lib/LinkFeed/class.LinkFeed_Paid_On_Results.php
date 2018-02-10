<?php
/**
 * User: rzou
 * Date: 2017/8/29
 * Time: 13:45
 */
class LinkFeed_Paid_On_Results
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

        $this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);

        $strUrl = sprintf('https://affiliate.paidonresults.com/api/merchant-directory?apikey=%s&Format=CSV&FieldSeparator=comma&AffiliateID=%s&MerchantCategories=ALL&Fields=MerchantID,MerchantCaption,MerchantCategory,AverageBasket,VoidRate,AccountManager,LastFeedUpdate,MerchantName,MerchantStatus,DateLaunched,AffiliateStatus,ConversionRatio,ApprovalRate,AccountManagerEmail,MerchantURL,CookieLength,ProductFeed,AffiliateURL,SampleCommissionRates,AverageCommission,DeepLinks,FullProductFeedURL&JoinedMerchants=YES&MerchantsNotJoined=YES', urlencode($APIKey), urlencode($SiteIdInAff));
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data.dat", $this->batchProgram, $use_true_file_name);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            $results = $this->GetHttpResultMoreTry($strUrl, $request);
            $this->oLinkFeed->fileCachePut($cache_file, $results);
        }

        $result = file_get_contents($cache_file);

        if (strpos($result, 'MerchantID,MerchantCaption') === false) {
            mydie('Can\'t get data, please check the api !');
        }
        $data = $this->csv_string_to_array($result);

        foreach ($data as $val) {
            $ProgramID = intval($val[0]);

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
                'Name' => addslashes(trim($val[7])),
                'MerchantCaption' => addslashes($val[1]),
                'MerchantCategory' => addslashes($val[2]),
                'AverageBasket' => addslashes($val[3]),
                'VoidRate' => addslashes($val[4]),
                'AccountManager' => addslashes($val[5]),
                'LastFeedUpdate' => addslashes($val[6]),
                'MerchantStatus' => addslashes($val[8]),
                'DateLaunched' => addslashes($val[9]),
                'AffiliateStatus' => addslashes($val[10]),
                'ConversionRatio' => addslashes($val[11]),
                'ApprovalRate' => addslashes($val[12]),
                'AccountManagerEmail' => addslashes($val[13]),
                'Homepage' => addslashes($val[14]),
                'CookieLength' => addslashes($val[15]),
                'ProductFeed' => addslashes($val[16]),
                'AffiliateURL' => addslashes($val[17]),
                'SampleCommissionRates' => addslashes($val[18]),
                'AverageCommission' => addslashes($val[19]),
                'DeepLinks' => addslashes($val[20]),
                'FullProductFeedURL' => addslashes($val[21])
            );


            $merDetailUrl = sprintf("https://affiliate.paidonresults.com/cgi-bin/view-merchant.pl?site_id=%s", $ProgramID);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $detail_page = $this->GetHttpResultMoreTry($merDetailUrl, $request, 'Merchant Name');
                if (!$detail_page) {
                    mydie("Can't get detailpage!");
                } else {
                    $this->oLinkFeed->fileCachePut($cache_file, $detail_page);
                }
            }
            $result = file_get_contents($cache_file);
            $result = preg_replace('@>\s+<@', '><', $result);

            $strPosition = 0;
            $arr_prgm[$ProgramID]['LogoUrl'] = 'https://affiliate.paidonresults.com' . addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('>Product Feed<','img src="'), '"', $strPosition)));
            $arr_prgm[$ProgramID]['Description'] = '<table>' . addslashes($this->oLinkFeed->ParseStringBy2Tag($result, array('<table','>'), '</table', $strPosition)) . '</table>';
            $arr_prgm[$ProgramID]['AdditionalInformation'] = addslashes(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('>Additional Information<','</tr>'), '</table', $strPosition)));
            $arr_prgm[$ProgramID]['CommissionRateDetail'] = '<table>' . addslashes($this->oLinkFeed->ParseStringBy2Tag($result, array('>Commission Rates<','<table','>'), '</table', $strPosition)) . '</table>';
            $arr_prgm[$ProgramID]['ProgramRestrictions'] = '<table>' . addslashes($this->oLinkFeed->ParseStringBy2Tag($result, array('>Program Restrictions<','</tr>'), '</table', $strPosition)) . '</table>';

            $program_num++;
            if (count($arr_prgm) > 25) {
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


?>