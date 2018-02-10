<?php
/**
 * User: rzou
 * Date: 2017/8/24
 * Time: 11:29
 */
class LinkFeed_Affiliate_Future_UK
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;
    }

    function LoginIntoAffService()
    {
        //get para __VIEWSTATE and then process default login
        if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
        $request = array("AffId" => $this->info["AffID"], "method" => "post", "postdata" => "",);
        if(isset($this->info["loginUrl"])){
            $this->info["AffLoginUrl"] = $this->info["loginUrl"];
        }
        $strUrl = $this->info["AffLoginUrl"];

        echo "login url:".$strUrl."\r\n";

        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        $result = $r["content"];

        if(stripos($result, "__VIEWSTATE") === false) mydie("die: login for LinkFeed_22_AFFF_UK failed, __VIEWSTATE not found\n");

        $nLineStart = 0;
        $strViewState = $this->oLinkFeed->ParseStringBy2Tag($result, 'id="__VIEWSTATE" value="', '" />', $nLineStart);

        if($strViewState === false) mydie("die: login for LinkFeed_22_AFFF_UK failed, __VIEWSTATE not found\n");

        $this->info["AffLoginPostString"] = '__VIEWSTATE=' . urlencode($strViewState) . '&'. $this->info["AffLoginPostStringOrig"];

        $this->oLinkFeed->LoginIntoAffService($this->info["AffID"],$this->info,2,true,true,false);
        return "stophere";
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
            $this->GetProgramByPage($v['SiteID'], $v['SiteIdInAff']);
        }
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->CheckBatch();
    }

    function GetProgramByPage($SiteID, $SiteIdInAff)
    {
        echo "\tGet Program by Page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;
        $request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
        $detailPageReq = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);

        //step 1,login
        $this->LoginIntoAffService();

        $page = 1;
        $hasNextPage = true;
        while ($hasNextPage){

            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                if ($page == 1){
                    $results = $this->GetHttpResultMoreTry('http://afuk.affiliate.affiliatefuture.co.uk/programmes/AdvertiserDirectory.aspx', $request);
                } else {
                    $results = $this->GetHttpResultMoreTry('  ', $request);
                }

                $this->oLinkFeed->fileCachePut($cache_file, $results);
            }
            $result = file_get_contents($cache_file);
            $result = preg_replace('@>\s+<@', '><', $result);

//            echo $result;exit;

            $nLineStart = stripos($result, 'class="table_core programmes_list"');
            if (!$nLineStart) {
                mydie('Can\'t get data, please check the Page !');
            }

            while ($nLineStart) {
                if (stripos($result, "window.location='Details.aspx?id", $nLineStart) === false) {
                    break;
                }

                $ProgramID = intval($this->oLinkFeed->ParseStringBy2Tag($result, "window.location='Details.aspx?id=", "'", $nLineStart));
                if (!$ProgramID) {
                    continue;
                }

                $detailPge = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'a href="', '"', $nLineStart));

                $detailPage = 'http://afuk.affiliate.affiliatefuture.co.uk/programmes/' . $detailPge;

                $LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, "src='", "'", $nLineStart));
                $Name = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'i class="wordwrap">','<', $nLineStart));
                $Sector = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td class="wordwrap">','<', $nLineStart));
                $ClickCommissiom = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $CommissiomPercentage = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $CommisiionMoney = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $EPC = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $ConversionRate = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $CookieLength = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">','</td', $nLineStart));
                $DedupePolicySupport = trim($this->oLinkFeed->ParseStringBy2Tag($result, "title='","'", $nLineStart));
                $PPCsupport = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">',"</td", $nLineStart));
                preg_match("@title='([a-zA-Z]+)'@", $PPCsupport, $m);
                if (isset($m[1]) && $m[1]) {
                    $PPCSupport = $m[1];
                } else {
                    $PPCSupport = strip_tags($PPCsupport);
                }

                $mobileEnableSupport = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">',"</td", $nLineStart));
                preg_match("@title='([a-zA-Z ]+)'@", $mobileEnableSupport, $mes);
                if (isset($mes[1]) && $mes[1]) {
                    $MobileEnableSupport = $mes[1];
                }else {
                    $MobileEnableSupport = '';
                }

                $datafeed= trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">',"</td", $nLineStart));
                preg_match("@title='([a-zA-Z ]+)'@", $datafeed, $df);
                if (isset($df[1]) && $df[1]) {
                    $Datafeed = $df[1];
                }else {
                    $Datafeed = '';
                }

                $exclusive= trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">',"</td", $nLineStart));
                preg_match("@title='([a-zA-Z ]+)'@", $exclusive, $e);
                if (isset($e[1]) && $e[1]) {
                    $Exclusive = $e[1];
                }else {
                    $Exclusive = '';
                }

                $partnershipStatus= trim($this->oLinkFeed->ParseStringBy2Tag($result, 'td align="center">',"</td", $nLineStart));
                preg_match("@title='([a-zA-Z ]+)'@", $partnershipStatus, $ps);
                if (isset($ps[1]) && $ps[1]) {
                    $PartnershipStatus = $ps[1];
                }else {
                    mydie("Cant't get partnershipStatus!");
                }

                //get detailPage!
                $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
                if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                    $detailResults = $this->GetHttpResultMoreTry($detailPage, $detailPageReq);
                    $this->oLinkFeed->fileCachePut($cache_file, $detailResults);
                }
                $detailResult = file_get_contents($cache_file);
                $detailResult = preg_replace('@>\s+<@', '><', $detailResult);

                $detailPos = stripos($detailResult, 'Merchant Overview');
                if (!$detailPos) {
                    mydie('Can\'t get data, please check the Page !');
                }

                $Description = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabs-1"','>'),"</div", $nLineStart));
                $PPCPolicy = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabs-2"','>'),"</div", $nLineStart));
                $DepudePolicy = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabs-3"','>'),"</div", $nLineStart));
                $Documents = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabs-4"','>'),"</div", $nLineStart));
                $DatafeedDetail = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabs-5"','>'),"</div", $nLineStart));
                $HomePage = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailResult, 'id="lnkAdvertiserWebsite" href="','"', $nLineStart));
                $CurrentRates = '<table'. $this->oLinkFeed->ParseStringBy2Tag($detailResult, array('id="tabsPro-1"','<table'),'</table', $nLineStart) . '</table>';
                preg_match_all("@b>\s+$Name\s+</b\.*&programmeID=(\d+)&@", $detailResult, $pn);
                print_r($pn);exit;

            }


            $nLineStart = stripos($result, '<tr style="color:Black;', $nLineStart);
            if ($nLineStart === false)
                break;



            $arr_prgm[$ProgramID] = array(
                "SiteID" => $SiteID,
                "AccountID" => $this->account['AccountID'],
                'AffID' => $this->info['AffID'],
                'IdInAff' => $ProgramID,
                'BatchID' => $this->oLinkFeed->batchid,

            );
            $program_num ++;

            echo $program_num . "\t";

            if (count($arr_prgm) > 0) {
                $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                $arr_prgm = array();
            }
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

    function GetAfffUKPrgmList($url, $request = array())
    {

    }
}


?>