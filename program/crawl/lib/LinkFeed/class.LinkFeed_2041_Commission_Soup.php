<?php
class LinkFeed_2041_Commission_Soup
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->islogined = false;
    }

    function login($try = 6)
    {
        if ($this->islogined) {
            echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
            return true;
        }

        $this->oLinkFeed->clearHttpInfos($this->info['AffId']);//删除缓存文件，删除httpinfos[$aff_id]变量
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'get'
        );
        $r = $this->oLinkFeed->GetHttpResult('https://www.commissionsoup.com/login.aspx', $request);
        $VIEWSTATE = $this->oLinkFeed->ParseStringBy2Tag($r['content'], 'id="__VIEWSTATE" value="', '"');
        $VIEWSTATEGENERATOR = $this->oLinkFeed->ParseStringBy2Tag($r['content'], 'id="__VIEWSTATEGENERATOR" value="', '"');
        $EVENTVALIDATION = $this->oLinkFeed->ParseStringBy2Tag($r['content'], 'id="__EVENTVALIDATION" value="', '"');

        $this->info["AffLoginPostString"] = http_build_query(array(
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__VIEWSTATE' => $VIEWSTATE,
            '__VIEWSTATEGENERATOR' => $VIEWSTATEGENERATOR,
            '__EVENTVALIDATION' => $EVENTVALIDATION,
            'ctl00$cphMain$txtUsr' => $this->info['Account'],
            'ctl00$cphMain$txtPwd' => $this->info['Password'],
            'ctl00$cphMain$btnLogin' => 'Login'
        ));

        $this->info["referer"] = true;
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => $this->info["AffLoginMethod"],
            "postdata" => $this->info["AffLoginPostString"],
            "no_ssl_verifyhost" => true,
        );

        $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
        if ($arr["code"] == 0) {
            if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $arr["error_msg"])) {
                $request["no_ssl_verifyhost"] = 1;
                $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
            }
        }

        if ($arr["code"] == 200) {
            if (stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false) {
                echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
                $this->islogined = true;
                return true;
            }
        }

        if (!$this->islogined) {
            if ($try < 0) {
                mydie("Failed to login!");
            } else {
                echo "login failed ... retry $try...\n";
                sleep(30);
                $this->login(--$try);
            }
        }
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramBypage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function GetProgramBypage()
    {
        echo "\tGet Program by page start\r\n";
        $this->login();

        //劫持cookie
        $cookie_path = $this->oLinkFeed->getCookieJarByAffId($this->info["AffId"]);
        $cookie_str = file_get_contents($cookie_path);
        $cookie_arr = explode("\r\n", $cookie_str);
        $cookie = '';
        foreach ($cookie_arr as $coo) {
            if (stripos($coo, 'ASP.NET_SessionId') !== false) {
                list($a,$b) = explode('ASP.NET_SessionId', $coo);
                $cookie .= 'ASP.NET_SessionId=' . trim($b);
            }
            if (stripos($coo, 'CommSoup') !== false) {
                list($x,$y) = explode('CommSoup', $coo);
                $cookie .= '; CommSoup=' . trim($y);
            }
        }

        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'get',
            "postdata" => "",
            "cookie" => $cookie,
            "referer" => 'https://www.commissionsoup.com/affiliates/'
        );

        $strUrl = "https://www.commissionsoup.com/affiliates/programs/";
        $r = $this->GetHttpResultMoreTry($strUrl, $request, 'Affiliate Programs');
        if (!$r) {
            mydie("Get program list failed!");
        }

        $result = preg_replace('/>\\s+</i', '><', $r);

//        category爬去
//        $ctgerStr = $this->oLinkFeed->ParseStringBy2Tag($result, "Filter Enrolled Programs by Category", "</select");
//        preg_match_all('@value="(\d+)">([a-zA-Z\s]+)</option@i', $ctgerStr, $c);
//        $ctgrArr = array();
//        if (!empty($c[1])) {
//            foreach ($c[1] as $key => $val) {
//                $ctgrArr[$val] = $c[2][$key];
//            }
//        }
//        print_r($ctgrArr);exit;

        $startString = 'id="cphMain_gvMyProgs"';
        $strPos= stripos($result, $startString);

        while($strPos){
            if (($strPos = stripos($result, 'td class="prog-status-cell', $strPos)) === false) {
                break;
            }
            $StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, "title='", "'", $strPos));
            switch ($StatusInAffRemark){
                case 'Active':
                    $Partnership = 'Active';
                    break;
                case 'Suspended':
                    $Partnership = 'NoPartnership';
                    break;
                case 'Deactivated':
                    $Partnership = 'Declined';
                    break;
                case 'Pending':
                    $Partnership = 'Pending';
                    break;
                case 'Paused':
                    $Partnership = 'NoPartnership';
                    break;
                default :
                    mydie("There find new StatusInAffRemark:$StatusInAffRemark !");
            }

            $name = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '<', $strPos));
            $IdInAff = intval($this->oLinkFeed->ParseStringBy2Tag($result, '>[', ']<', $strPos));
            if (!$IdInAff)
                continue;
            echo $IdInAff . "\t";

            $joinDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td', '>'), '<', $strPos));

            $request['referer'] = 'https://www.commissionsoup.com/affiliates/programs/';
            $detailPageUrl = sprintf('https://www.commissionsoup.com/affiliates/programs/details.aspx?p=%s', $IdInAff);
            $detailPageStr = $this->GetHttpResultMoreTry($detailPageUrl, $request, 'Program ID');
            if (!$detailPageStr) {
                mydie("Get program(id=$IdInAff) detail failed!");
            }

            $detailPageStr = preg_replace('/>\\s+</i', '><', $detailPageStr);

            $affDefualtUrl = trim($this->oLinkFeed->ParseStringBy2Tag($detailPageStr, array('Program ID #', 'href="'), '"'));
            $homepage = $this->findFinalUrl($affDefualtUrl);

            $logoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($detailPageStr, array('Program ID #', 'img src="'), '"'));

            $description = $this->oLinkFeed->ParseStringBy2Tag($detailPageStr, 'div id="prog-desc">', "</div");

            $TermAndCondition = $publisherPolicy = '';
            $hasTerm = $this->oLinkFeed->ParseStringBy2Tag($detailPageStr, 'id="guidelines">', "<");
            if ($hasTerm) {
                $request['referer'] = $detailPageUrl;
                $termStr = $this->oLinkFeed->ParseStringBy2Tag($detailPageStr, 'id="cphMain_divTerms">', "</div");
                preg_match("@a href='(terms.aspx\?t=\d+)'\s+onclick=[^>]*>Marketing Guidelines</a@", $termStr, $m1);
                if (isset($m1[1]) && !empty($m1[1])) {
                    $termUrl = sprintf('https://www.commissionsoup.com/affiliates/programs/%s', $m1[1]);
                    $TermAndCondition = $this->GetHttpResultMoreTry($termUrl, $request);
                }

                preg_match("@a href='(terms.aspx\?t=\d+)'\s+onclick=[^>]*>Paid Search Guidelines</a@", $termStr, $m2);
                if (isset($m2[1]) && !empty($m2[1])) {
                    $termUrl = sprintf('https://www.commissionsoup.com/affiliates/programs/%s', $m2[1]);
                    $publisherPolicy = $this->GetHttpResultMoreTry($termUrl, $request);
                }
            }

            $commission = trim($this->oLinkFeed->ParseStringBy2Tag($detailPageStr, array('>Commission Description</td', 'class="td-rounded-first"', '<td', '>'), '</td'));


            $arr_prgm[$IdInAff] = array(
                "AffId" => $this->info["AffId"],
                "IdInAff" => $IdInAff,
                "Name" => addslashes($name),
                "Description" => addslashes($description),
                "Homepage" => addslashes($homepage),
                "StatusInAffRemark" => addslashes($StatusInAffRemark),
                "StatusInAff" => 'Active',
                "Partnership" => $Partnership,
                "CommissionExt" => addslashes($commission),
                "LastUpdateTime" => date("Y-m-d H:i:s"),
                "TermAndCondition" => addslashes($TermAndCondition),
                "LogoUrl" => addslashes($logoUrl),
                "DetailPage" => $detailPageUrl,
                "PublisherPolicy" => addslashes($publisherPolicy),
                "AffDefaultUrl" => addslashes($affDefualtUrl),
                'JoinDate' => date('Y-m-d H:i:s', strtotime($joinDate)),
            );

            $program_num ++;

            if (count($arr_prgm) >= 1) {
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
            sleep(rand(30,80));
        }

        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            unset($arr_prgm);
        }
        echo "\tGet Program by page end\r\n";

        if ($program_num < 8) {
            mydie("die: program count < 8, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";

        echo "\tSet program country int.\r\n";
        $objProgram->setCountryInt($this->info["AffId"]);

        echo "\tGet program category int.\r\n";

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
            sleep(rand(60,120));
        }
        return $result;
    }

    function findFinalUrl($url, $max_req_num = 10, $filter = array())
    {
        if ($max_req_num < 0)
            return '';
        $r = $this->oLinkFeed->GetHttpResult($url);
        $result = $r["content"];
        if (strlen($result) < 200)
            return '';

        if (preg_match('@http-equiv="refresh"\s+content="\d+;\s*url=([^"]+)"@is',$result,$u)){
            $deepUrl = $u[1];
            $hd = @get_headers($deepUrl);
            if (!$hd)
                mydie("Here found the wrong link: $deepUrl from $url\n");

            return $this->findFinalUrl($deepUrl, --$max_req_num, $filter);

        }elseif (preg_match('@var\s+path = "([^"]+)"@is',$result,$m)){
            $deepUrl = $m[1];
            $hd = @get_headers($deepUrl);
            if (!$hd)
                mydie("Here found the wrong link: $deepUrl from $url\n");

            return $this->findFinalUrl($deepUrl, --$max_req_num, $filter);
        } else {
            $r = $this->oLinkFeed->GetHttpResult($url, array('FinalUrl' => 1));

            if ($r['code'] == 200) {
                $result = $r["content"];
                foreach ($filter as $item) {
                    if (stripos($result,$item) !== false)
                        return '';
                }
                return $result;

            }else {
                return '';
            }
        }
    }

}