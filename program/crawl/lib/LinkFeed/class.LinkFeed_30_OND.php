<?php

class LinkFeed_30_OND
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->file = "programlog_{$aff_id}_" . date("Ymd_His") . ".csv";
        $this->getStatus = false;
    }

    function GetMerchantListFromAff()
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);

        //step 2,get all exists merchant
        $arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

        echo " Get all merchants  <br>\n";

        $strUrl = "https://aff.onenetworkdirect.com/partners/program_selective.html?time_frame=&category_id=&order_by=program_name&list_all=1";
        $r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
        $result = $r["content"];

        //parse HTML

        $strLineStart = "<tr class='recordData";

        $nLineStart = 0;
        $bStart = true;
        while ($nLineStart >= 0) {
            //print "Process $Cnt  ";
            $nLineStart = stripos($result, $strLineStart, $nLineStart);
            if ($nLineStart === false) break;

            // ID 	Name 	EPC 	Status
            $strEPC = 0;

            //ID
            $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'program_summary.html?program_id=', '"', $nLineStart);
            if ($strMerID === false) break;
            $strMerID = trim($strMerID);

            //name
            $strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '>', '</a></b>', $nLineStart);
            if ($strMerName === false) break;
            $strMerName = html_entity_decode(trim($strMerName));

            $strStatus = $this->oLinkFeed->ParseStringBy2Tag($result, array('program_selective.html?program_id=', '">'), '</a>', $nLineStart);
            if ($strStatus === false) break;
            $strStatus = trim($strStatus);
            if ($strStatus == "Active") $strStatus = 'approval';
            elseif ($strStatus == "Agree to Terms") $strStatus = 'approval';
            elseif ($strStatus == "Pending Approval") $strStatus = 'pending';
            else $strStatus = 'not apply';

            $arr_return["AffectedCount"]++;
            $arr_update = array(
                "AffMerchantId" => $strMerID,
                "AffId" => $this->info["AffId"],
                "MerchantName" => $strMerName,
                "MerchantEPC30d" => "-1",
                "MerchantEPC" => "-1",
                "MerchantStatus" => $strStatus,
                "MerchantRemark" => "",
            );
            $this->oLinkFeed->fixEnocding($this->info, $arr_update, "merchant");
            if ($this->oLinkFeed->UpdateMerchantToDB($arr_update, $arrAllExistsMerchants)) $arr_return["UpdatedCount"]++;

        }

        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
        return $arr_return;
    }

    function getCouponFeed()
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);

        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "feed_xml.dat", "cache_feed");
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            //step 1,login
            $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);

            $strUrl = "https://aff.onenetworkdirect.com/coupon_feed.html?id=7b093cac1b35e12b0dba79da4d445b00";
            $r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
            $result = $r["content"];

            if (stripos($result, "</feed>") === false) {
                print_r($r);
                mydie("die: get feed failed, wrong xml format\n");
            }

            $this->oLinkFeed->fileCachePut($cache_file, $result);
        }
        if (!file_exists($cache_file)) return $arr_return;

        $all_merchant_name = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"], "", "MerchantName");
        $arrToUpdate = array();

        $xml = new DOMDocument();
        $xml->load($cache_file);

        $coupon_list = $xml->getElementsByTagName("coupon");
        foreach ($coupon_list as $coupon) {
            $coupon_info = array();
            $childnodes = $coupon->getElementsByTagName("*");
            foreach ($childnodes as $node) $coupon_info[$node->nodeName] = trim($node->nodeValue);

            if (!isset($coupon_info["coupon_title"])) {
                echo "warning: coupon_title not found, skip it\n";
                continue;
            }
            $promo_type = 'coupon';

            $coupon_info["coupon_title"] = trim($coupon_info["coupon_title"]);
            if ($coupon_info["coupon_title"] == "") {
                if ($coupon_info["offer_description"] != "") {
                    $coupon_info["coupon_title"] = $coupon_info["offer_description"];
                } else {
                    echo "warning: coupon_title not found, skip it\n";
                    print_r($coupon_info);
                    continue;
                }
            }

            $link_desc = ' Country: ' . $coupon_info["country"] . '; Languate: ' . $coupon_info["language"] . '; Coupon Code: ' . $coupon_info["coupon_code"] . '; ' . $coupon_info["product_name"] . '; ' . $coupon_info["coupon_title"] . '; ' . $coupon_info["offer_description"];
            if ($promo_type != 'coupon') $promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($coupon_info["coupon_title"] . ' ' . $link_desc . ' ' . $coupon_info["html_code"]);

            //<link><![CDATA[http://send.onenetworkdirect.net/z/144752/CD129323/]]></link>
            if (preg_match("|onenetworkdirect.net/z/([0-9]+)/CD129323|", $coupon_info["link"], $matches)) {
                $link_id = 'c_' . $matches[1];
            } else {
                echo "warning: link_id not found, skip it\n";
                continue;
            }

            $aff_mer_name = $coupon_info["merchant"];
            if (!isset($all_merchant_name[$aff_mer_name])) {
                echo "warning: merchant name not found in local db, skip it\n";
                continue;
            }
            $aff_mer_id = $all_merchant_name[$aff_mer_name]["AffMerchantId"];

            $arr_one_link = array(
                "AffId" => $this->info["AffId"],
                "AffMerchantId" => $aff_mer_id,
                "AffLinkId" => $link_id,
                "LinkName" => $coupon_info["coupon_title"],
                "LinkDesc" => $link_desc,
                "LinkStartDate" => $coupon_info["start_date"],
                "LinkEndDate" => $coupon_info["expire_date"],
                "LinkPromoType" => $promo_type,
                "LinkHtmlCode" => $coupon_info["html_code"],
                "LinkOriginalUrl" => "",
                "LinkImageUrl" => "",
                "Country" => $this->oLinkFeed->GetCountryCodeByStr($coupon_info["country"]),
                "LinkAffUrl" => "",
                "DataSource" => "3",
            );
            if (preg_match('@href="(.*?)"@', $arr_one_link['LinkHtmlCode'], $g))
                $arr_one_link['LinkAffUrl'] = $g[1];
            $this->oLinkFeed->fixEnocding($this->info, $arr_one_link, "feed");
            $arrToUpdate[] = $arr_one_link;
            $arr_return["AffectedCount"]++;
            if (!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
            $arr_return["Detail"][$aff_mer_id]["AffectedCount"]++;

            if (sizeof($arrToUpdate) > 100) {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
                $arrToUpdate = array();
            }
        }

        if (sizeof($arrToUpdate) > 0) {
            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
            $arrToUpdate = array();
        }
        return $arr_return;
    }

    function GetAllLinksFromAffByMerID($merinfo, $newonly = true)
    {
        $aff_id = $this->info["AffId"];
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);

        $page = 1;
        $count = 0;
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
        $url = 'https://aff.onenetworkdirect.com/partners/get_links.html?program_id=' . $merinfo['IdInAff'] . '&GET_LINKS%5Bcountry_abbr%5D=&GET_LINKS%5Blanguage_id%5D=0&GET_LINKS%5Bprogram_category_id%5D=0&GET_LINKS%5Bproduct_name%5D=&promo_type=0&type=-1&GET_LINKS%5Blanding_page_type_id%5D=0&GET_LINKS%5Bbanner_description%5D=&GET_LINKS%5Bbanner_size_id%5D=0';
        do {
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            $content = $r['content'];
            $links = array();
            if (preg_match_all('@<tr class="exclusive">(.*?)</textarea>@ms', $content, $chapters)) {
                foreach ($chapters[0] as $key => $chapter) {
                    $link = array(
                        "AffId" => $this->info["AffId"],
                        "AffMerchantId" => $merinfo['IdInAff'],
                        "LinkDesc" => '',
                        "LinkStartDate" => '0000-00-00 00:00:00',
                        "LinkEndDate" => '0000-00-00 00:00:00',
                        "LinkPromoType" => 'DEAL',
                        "LinkOriginalUrl" => "",
                        "DataSource" => "3",
                    );
                    if (preg_match('@<b>(\d+ X \d+)</b>@', $chapter, $g))
                        $link['LinkName'] = trim(strip_tags($g[1]));
                    if (preg_match('@<textarea id="url\[(.*?)\]".*?>(.*?)</textarea>@', $chapter, $g)) {
                        $link['AffLinkId'] = $g[1];
                        if (empty($link['LinkName']))
                            $link['LinkName'] = html_entity_decode(trim(strip_tags(trim($g[2]))));
                        if (preg_match('@<img src="(.*?)"@', $g[2], $g1))
                            $link['LinkImageUrl'] = $g1[1];
                        $link['LinkHtmlCode'] = html_entity_decode(trim($g[2]));
                        if (preg_match('@<a href="(.*?)"@', $g[2], $g1))
                            $link['LinkAffUrl'] = trim($g1[1]);
                    }
                    if (preg_match('@<b>Description:</b>(.*?)</td>@mis', $chapter, $g))
                        $link['LinkDesc'] = html_entity_decode(trim($g[1]));
                    if (preg_match('@<b>Coupon Code:</b>(.*?)</td>@mis', $chapter, $g)) {
                        if (!preg_match('@No code@i', $g[1])) {
                            $link['LinkCode'] = trim($g[1]);
                            $link['LinkPromoType'] = 'COUPON';
                        }
                    }
                    if (preg_match('@<b>Coupon Text:</b>(.*?)</td>@mis', $chapter, $g))
                        $link['LinkName'] = html_entity_decode(trim(strip_tags(trim($g[2]))));
                    if (empty($link['AffLinkId']) )
                        continue;
                    elseif(empty($link['LinkName'])){
                        $link['LinkPromoType'] = 'link';
                    }
                    $links[] = $link;
                }
            }
            $url = null;
            $page++;
            if (preg_match('@<b>Pages:</b>(.*?)</div>@ms', $content, $g)) {
                if (preg_match('@<b>\[(\d+)\]</b>@', $g[1], $g1)) {
                    $tmp = substr($g[1], strpos($g[1], $g1[0]), -1);
                    if (preg_match('@<a href="(.*?)"@', $tmp, $g2))
                        $url = 'https://aff.onenetworkdirect.com/partners/get_links.html' . $g2[1];
                }
            }
            if (count($links) > 0) {
                $count += count($links);
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
            }
        } while (!empty($url) && $page < 1000);
        echo "$count link(s) found.\n";
        return $arr_return;
    }

    function GetProgramByPage()
    {
        echo "\tGet Program by page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;

        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
        $reporturl = "https://aff.onenetworkdirect.com/partners/program_selective.html";
        $request["postdata"] = "order_by=program_name&list_all=1";
        $return_arr = $this->oLinkFeed->GetHttpResult($reporturl, $request);
        $result = $return_arr["content"];

        print "<br>\n Get Merchant List<br>\n";

        //parse HTML
        $strLineStart = "<tr class='recordData";

        $nLineStart = 0;
        while ($nLineStart >= 0) {
            $nLineStart = stripos($result, $strLineStart, $nLineStart);
            if ($nLineStart === false) {
                echo "strLineStart: $strLineStart not found, break\n";
                break;
            }

            $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="program_selected[', ']', $nLineStart);
            $strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '<a href="program_summary.html?program_id=' . $strMerID . '">', '</a>', $nLineStart);
            $desc = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
            $CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, '<td nowrap>', '</td>', $nLineStart);
            $EPCDefault = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
            $strStatus = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, "<td wrap align='center'>", '</td>', $nLineStart));

            $StatusInAff = "Active";

            if (!$this->getStatus) {
                $request["method"] = "get";
                $prgm_url = "https://aff.onenetworkdirect.com/partners/program_summary.html?program_id=$strMerID";
                $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                $prgm_detail = $prgm_arr["content"];

                $Contacts = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Summary', "Program Manager:"), '<br>');
                $Contacts .= ", email: " . $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Manager:', "Email:", '<a href="mailto:'), '">');
                $TargetCountryExt = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Localized Creative:', "<td valign='top' align='left'>"), '</td>');

                $CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Categories:', "<td valign='top' align='left'>"), '</td>');
                $JoinDate = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Live Date:', "<td valign='top' align='left'>"), '</td>');

                if ($JoinDate) {
                    $JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
                }




                $ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Referral Period:', "<td valign='top' align='left'>"), '</td>');
                $Homepage = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website:', "<td valign='top' align='left'>"), '</td>'));

                $TermAndCondition = "";
                $hasterm = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Special Terms:', "<td>"), '</td>');
                if ($hasterm != "N/A") {
                    $term_url = "https://aff.onenetworkdirect.com/partners/show_terms.html?program_id=$strMerID";
                    $term_arr = $this->oLinkFeed->GetHttpResult($term_url, $request);
                    $TermAndCondition = $term_arr["content"];
                }

                if (in_array($strMerID, array(513, 534, 524, 564, 578))) {
                    $SupportDeepurl = "NO";
                } else {
                    $SupportDeepurl = "YES";
                }
            }
            //$Partnership = strip_tags(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Relationship Status:', "</b>"), '</font>')));
            $Partnership = "";
            if (stripos($strStatus, "Active") !== false) {
                $Partnership = "Active";
            } elseif (stripos($strStatus, "Pending") !== false) {
                $Partnership = "Pending";
            } elseif (stripos($strStatus, "Declined") !== false) {
                $Partnership = "Declined";
            } elseif (stripos($strStatus, "Expired") !== false) {
                $Partnership = "Expired";
            } else {
                $Partnership = "NoPartnership";
            }

            if(!$this->getStatus) {
                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes(html_entity_decode(trim($strMerName))),
                    "AffId" => $this->info["AffId"],
                    "CategoryExt" => addslashes($CategoryExt),
                    "Contacts" =>  addslashes($Contacts),
                    "TargetCountryExt" => addslashes($TargetCountryExt),
                    "IdInAff" => $strMerID,
                    "JoinDate" => $JoinDate,
                    //"CreateDate" => "",
                    "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                    "StatusInAffRemark" => addslashes($strStatus),
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "Description" => addslashes($desc),
                    "Homepage" => $Homepage,
                    "EPCDefault" => floatval(preg_replace("/[^0-9.]/", "", $EPCDefault)),
                    "CommissionExt" => addslashes($CommissionExt),
                    "CookieTime" => $ReturnDays,
                    //"NumberOfOccurrences" => intval($NumberOfOccurrences),
                    "TermAndCondition" => addslashes($TermAndCondition),
                    //"SubAffPolicyExt" => addslashes($SubAffPolicyExt),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "DetailPage" =>  addslashes($prgm_url),
                    "SupportDeepUrl" =>  addslashes($SupportDeepurl)
                );
            } else {
                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes(html_entity_decode(trim($strMerName))),
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $strMerID,
                    //"CreateDate" => "",
                    "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                    "StatusInAffRemark" => addslashes($strStatus),
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "Description" => addslashes($desc),
                    "CommissionExt" => addslashes($CommissionExt),
                    //"NumberOfOccurrences" => intval($NumberOfOccurrences),
                    //"SubAffPolicyExt" => addslashes($SubAffPolicyExt),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                );
            }

            $program_num++;
            //print_r($arr_prgm);
            if (count($arr_prgm) >= 100) {
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
        }
        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            $arr_prgm = array();
        }

        echo "\tGet Program by page end\r\n";

        if ($program_num < 10) {
            mydie("die: program count < 10, please check program.\n");
        }

        echo "\tUpdate ({$program_num}) program.\r\n";
        echo "\tSet program country int.\r\n";
        $objProgram->setCountryInt($this->info["AffId"]);
    }

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->GetProgramByPage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);

        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function checkProgramOffline($AffId, $check_date)
    {
        $objProgram = new ProgramDb();
        $prgm = array();
        $prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);

        if (count($prgm) > 30) {
            mydie("die: too many offline program (" . count($prgm) . ").\n");
        } else {
            $objProgram->setProgramOffline($this->info["AffId"], $prgm);
            echo "\tSet (" . count($prgm) . ") offline program.\r\n";
        }
    }
}

?>
