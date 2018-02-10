<?php
/**
 * User: rzou
 * Date: 2017/8/24
 * Time: 19:38
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


        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "get",
            "postdata" => "",
        );

        //Step1 Get all approval merchants
        $strUrl = "http://afuk.affiliate.affiliatefuture.co.uk/programmes/MerchantsJoined.aspx";
        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        $result = $r["content"];


        //parse HTML
        //<table width="100%" cellspacing="0" class="aftable">
        $strLineStart = '<tr onmouseover="bgColor=\'#E7EBF4\'" onmouseout="bgColor=\'#ffffff\'">';

        $nLineStart = 0;
        while ($nLineStart >= 0) {
            $nLineStart = stripos($result, $strLineStart, $nLineStart);
            if ($nLineStart === false) break;


            $arr_prgm[$ProgramID] = array(
                "SiteID" => $SiteID,
                "AccountID" => $this->account['AccountID'],
                'AffID' => $this->info['AffID'],
                'IdInAff' => $ProgramID,
                'BatchID' => $this->oLinkFeed->batchid,

            );
            $program_num++;

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