<?php

require_once 'text_parse_helper.php';

class LinkFeed_TDs{
    function LoginIntoAffService()
    {
        //get para __VIEWSTATE and then process default login
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "post",
            "postdata" => $this->info["AffLoginPostString"],
            "maxredirs" => 4,//if we dont set this, it will be failed at the fifth redir
            //"verbose" => 1, //for debug
            //"referer" => "https://publisher.tradedoubler.com/index.html",
            //"autoreferer" => 1,
        );
        $request["addheaders"] =array('Host:affiliates.visualsoft.co.uk','Origin:http://affiliates.visualsoft.co.uk','Referer:http://affiliates.visualsoft.co.uk/pan/public/','Upgrade-Insecure-Requests:1');
        $strUrl = $this->info["AffLoginUrl"];
        $arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        $request = array("AffId" => $this->info["AffId"], "method" => "get",);
        $strUrl = "http://affiliates.visualsoft.co.uk/publisher/aStartPage.action";
        $arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        if($this->info["AffLoginVerifyString"] && stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false)
        {
            echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
            return true;
        }
        else
        {
            print_r($arr);
            echo "verify failed: " . $this->info["AffLoginVerifyString"] . "\n";
        }
        return false;
    }

    function getSiteId()
    {
        $arr_return = array();
        switch($this->info["AffId"])
        {
            case 5: // UK
                $arr_return["en_GB"]["1550868"] = "UK"; //"Couponsnapshot UK";
                break;
            case 133: // UK
                $arr_return["en_GB"]["1470197"] = "UK"; //"Coupon Snapshot";note: us is also for GB
                break;
            case 412:
                $arr_return["en_GB"]['2476196'] = "UK";
            case 27: // IE
                $arr_return["en_IE"]["1634367"] = "IE"; //"Irelandvouchercodes.com [IE]";
                break;
            case 35: // DE
                $arr_return["de_DE"]["1781705"] = "DE"; //"CouponSnapshot DE";
                break;
            case 415: // AT
                $arr_return["de_DE"]["2489540"] = "AT"; //"CouponSnapshot DE";
                break;
            case 429: // CH
                $arr_return["de_DE"]["2502032"] = "CH"; //"CouponSnapshot DE";
                break;
            case 469: // FR
                $arr_return["de_DE"]["2525860"] = "FR"; //"CouponSnapshot DE";
                break;
            default:
                mydie("die:Wrong AffID for LinkFeed_TD\n");
        }
        return $arr_return;
    }







    function GetProgramByPage()
    {
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
        $arrCountrySite = $this->getSiteId();
        foreach($arrCountrySite as $conutry => $sites)
        {
            foreach($sites as $site_id => $contry_code)
            {
                $arr_result = $this->GetProgramBySiteId($site_id,$contry_code);
            }
        }
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByPage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramBySiteId($site_id,$contry_code)
    {
        $start = date("Y-m-d H:i:s");
        echo "\tGet Program by page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;

        $arr_return = array();
        $request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);

        $nNumPerPage = 100;
        $nPageNo = 1;

        while(1)
        {
            if ($nPageNo == 1){
                $strUrl = "http://affiliates.visualsoft.co.uk/pan/aProgramList.action";
                $request["method"] = "post";
                $request["postdata"] = "programGEListParameterTransport.currentPage=".$nPageNo."&searchPerformed=true&searchType=prog&programGEListParameterTransport.programIdOrName=&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=" . $site_id . "&programGEListParameterTransport.orderBy=statusId&programAdvancedListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=" . $nNumPerPage . "&programAdvancedListParameterTransport.directAutoApprove=&programAdvancedListParameterTransport.mobile=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=";
                $this->oLinkFeed->GetHttpResult($strUrl,$request);
            }
            $strUrl = "http://affiliates.visualsoft.co.uk/pan/aProgramList.action?categoryChoosen=false&programGEListParameterTransport.currentPage=".$nPageNo."&programGEListParameterTransport.pageSize=".$nNumPerPage."&programGEListParameterTransport.pageStreamValue=true";
            $request["postdata"] = "";
            $request["method"] = "get";

            $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
            $result = $r["content"];

            //parse HTML
            $strLineStart = 'showPopBox(event, getProgramCodeAffiliate';
            $nLineStart = 0;
            $bStart = 1;
            while(1)
            {
                $nLineStart = stripos($result,$strLineStart,$nLineStart);
                if($nLineStart === false && $bStart == 1) break 2;
                if($nLineStart === false) break;
                $bStart = 0;


                //Id
                $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'getProgramCodeAffiliate(', ',', $nLineStart);
                if($strMerID === false) break;
                $strMerID = trim($strMerID);

                //name
                $strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, ">","</a>", $nLineStart);
                if($strMerName === false) break;
                $strMerName = html_entity_decode(trim($strMerName));

                $CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);

                $Prepayment = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Keywords = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Productfeeds = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $UV = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Click = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Leads = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Sales =$this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $EPC90th = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $AvgpaidEPC = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $TDinfo = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                $Websitestatus = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
                if(stripos($Websitestatus,"Accepted") !== false){$strState = "approval";}
                elseif(stripos($Websitestatus,"Apply") !== false){$strState = "not apply";}
                elseif(stripos($Websitestatus,"Ended") !== false){$strStatus = 'declined';}
                else{mydie("Unknown Websitestatus:$Websitestatus");}

                if($strState == "approval"){
                    $Partnership = "Active";
                    $StatusInAff = "Active";
                }elseif($strState == "not apply"){
                    $Partnership = "NoPartnership";
                    $StatusInAff = "Active";
                }elseif($strState == "declined"){
                    $Partnership = "Declined";
                    $StatusInAff = "Active";
                }else{
                    mydie("Unknown Strstatus:$strState");
                }

                $request["method"] = "get";
                $request["postdata"] = "";
                $prgm_url = "http://affiliates.visualsoft.co.uk/pan/aProgramTextRead.action?programId={$strMerID}&affiliateId={$site_id}";
                $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                $prgm_detail = $prgm_arr["content"];
                $desc = "<div>" . trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail,'<div id="publisher-body">', '<div id="publisher-footer">'));
                $desc = preg_replace("/[\\r|\\n|\\r\\n|\\t]/is", '', $desc);
                $desc = preg_replace('/<([a-z]+?)\s+?.*?>/i','<$1>', $desc);

                preg_match_all('/<([a-z]+?)>/i', $desc, $res_s);
                preg_match_all('/<\/([a-z]+?)>/i', $desc, $res_e);
                //$desc   description
                $tags_arr = array();
                foreach($res_s[1] as $v){
                    if(strtolower($v) != "br"){
                        if(isset($tags_arr[$v])){
                            $tags_arr[$v] += 1;
                        }else{
                            $tags_arr[$v] = 1;
                        }
                    }
                }
                foreach($res_e[1] as $v){
                    if(strtolower($v) != "br" && isset($tags_arr[$v])){
                        $tags_arr[$v] -=1;
                    }
                }
                foreach($tags_arr as $k => $v){
                    for($i = 0; $i < $v; $i++){
                        $desc .= "</$k>";
                    }
                }

                $overview_url = "http://affiliates.visualsoft.co.uk/pan/aProgramInfoApplyRead.action?programId={$strMerID}&affiliateId={$site_id}";
                $overview_arr = $this->oLinkFeed->GetHttpResult($overview_url, $request);
                $overview_detail = $overview_arr["content"];

                //Home
                $Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Visit the site', '<a href="'), '"'));
                //Commission
                $CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, '<b>Commission structure for your segment:</b>', '</table>')) . "</table>";

                $SupportDeepUrl = strtoupper(trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Deep linking', '<td nowrap="nowrap">'), '</td>')));
                if($SupportDeepUrl == "YES"){
                    $SupportDeepUrl = "YES";
                }elseif($SupportDeepUrl == "NO"){
                    $SupportDeepUrl = "NO";
                }else{
                    $SupportDeepUrl = "UNKNOWN";
                }
                $links_url = "http://affiliates.visualsoft.co.uk/pan/aProgramInfoLinksRead.action?programId={$strMerID}&affiliateId={$site_id}";
                $links_arr = $this->oLinkFeed->GetHttpResult($links_url, $request);
                $links_detail = $links_arr["content"];

//                $g_id = intval($this->oLinkFeed->ParseStringBy2Tag($links_detail, array('/pan/aInfoCenterLinkInfo.action', 'geId='), '&'));
                $AffDefaultUrl = "";
//                if($g_id > 0){
//                    $AffDefaultUrl = "http://clkuk.tradedoubler.com/click?p({$strMerID})a({$site_id})g({$g_id})";
//                }

                $TargetCountryExt = $contry_code;

                //find homepage
                if($tmp_url = $this->oLinkFeed->findFinalUrl($Homepage)){
                    $Homepage = $tmp_url;
                }

                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes($strMerName),
                    "AffId" => $this->info["AffId"],
                    //"Contacts" => $Contacts,
                    "TargetCountryExt" => $TargetCountryExt,
                    "IdInAff" => $strMerID,
                    //"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
                    "StatusInAffRemark" => $Websitestatus,
                    "StatusInAff" => trim($StatusInAff),						//'Active','TempOffline','Offline'
                    "Partnership" => trim($Partnership),						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "Description" => addslashes($desc),
                    "Homepage" => $Homepage,
//                    "EPCDefault" => preg_replace("/[^0-9.]/", "", $EPCDefault),
//                    "EPC90d" => preg_replace("/[^0-9.]/", "", $EPC90d),
                    //"TermAndCondition" => addslashes($TermAndCondition),
                    "SupportDeepUrl" => $SupportDeepUrl,
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "DetailPage" => $prgm_url,
//                    "MobileFriendly" => $MobileFriendly,
                    "AffDefaultUrl" => addslashes($AffDefaultUrl),
                    "CommissionExt" => addslashes($CommissionExt),
                );
                $program_num++;
            }
            $nPageNo++;

            if(count($arr_prgm)){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
        }
        echo "\tGet Program by page end\r\n";
        if($program_num < 10){
            mydie("die: program count < 10, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
//        echo "\tSet program country int.\r\n";
        $this->checkProgramOffline(412,$start);
//        $objProgram->setCountryInt($this->info["AffId"]);
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
}

