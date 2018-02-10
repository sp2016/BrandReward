<?php
class LinkFeed_2044_WOW_Trk
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->Affiliate_ID = '44579';
        $this->apikey = '15278377c7e9fed8e213fbaf101af941c1fae2e57e6d6cc0685fbe21f3b1c786';
        $this->NetworkId ='wowtrk';

        $this->islogined = false;
    }

    function Login()
    {
        if ($this->islogined) return $this->islogined;

        $LoginURL = $this->info["AffLoginUrl"];
        $request = array("AffId" => $this->info["AffId"], "method" => "get",);
        $result = $this->oLinkFeed->GetHttpResult($LoginURL, $request);
        $content = $result['content'];
        $Token_key = $this->oLinkFeed->ParseStringBy2Tag($content, 'name="data[_Token][key]" value="', '"');
        $Token_fields = $this->oLinkFeed->ParseStringBy2Tag($content, 'name="data[_Token][fields]" value="', '"');
        $this->info["AffLoginPostString"] .= '&'.urlencode('data[_Token][key]').'='.urlencode($Token_key).'&'.urlencode('data[_Token][fields]').'='.urlencode($Token_fields);

        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => $this->info["AffLoginMethod"],
            "postdata" => $this->info["AffLoginPostString"],
            "no_ssl_verifyhost" => true,
        );
        if (isset($this->info["referer"])) $request["referer"] = $this->info["referer"];
        $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);//返回爬取的AffLoginSuccUrl页面各项信息的数组
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
            }
            //handle redir by meta tag
            if (!$this->islogined && stripos($arr["content"], "REFRESH") !== false && isset($this->info["AffLoginSuccUrl"]) && $this->info["AffLoginSuccUrl"]) {
                $url_path = @parse_url($this->info["AffLoginSuccUrl"], PHP_URL_PATH);//parse_url用于解析url，返回一个关联数组。parse_url("xxx", PHP_URL_PATH)返回数组的path值
                if ($url_path && stripos($arr["content"], $url_path) !== false) {
                    echo "good, verify succ (redir by meta tag) <br>\n";
                    $this->islogined = true;
                }
            }
        }

        if ($this->islogined){
            echo "verify login failed(" . $this->info["AffLoginVerifyString"] . ") <br>\n";
            return $this->islogined;
        }
        else
            mydie("die: login failed for aff({$this->info['AffId']}) <br>\n");
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByApi();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

    function GetProgramByApi()
    {
        echo "\tGet Program by api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;

        $strUrl = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=findAll";
        $r = $this->oLinkFeed->GetHttpResult($strUrl);

        if(empty($r['content']))
            mydie("Error type is can not get infomation from Api");

        $apiResponse = @json_decode($r['content'], true);

        if(!isset($apiResponse['response']['status']) || $apiResponse['response']['status'] != 1)
            mydie("API call failed ({$apiResponse['response']['errorMessage']})");

//        print_r($apiResponse);exit;
        $startMinut = floatval(time() / 60);
        $callApiNum = 1;
        $result = $apiResponse['response']['data'];
        foreach($result as $item)
        {
            $v = $item['Offer'];
            $IdInAff = intval(trim($v['id']));
            if(!$IdInAff)
                continue;

            $desc = strip_tags($v['description']);
            $Homepage = $v['preview_url'];
            $TermAndCondition = $v['require_terms_and_conditions'] == 1 ? addslashes(strip_tags($v['terms_and_conditions'])) : '';
            $SupportDeepUrl = intval($v['allow_website_links']) == 1 ? 'YES' : 'NO';

            if($v['payout_type'] == 'cpa_percentage')
                $CommissionExt = $v['percent_payout'].'%';
            else
                $CommissionExt = '£'.round($v['default_payout'],2);

            $StatusInAffRemark = $v['status'];
            if($StatusInAffRemark == 'active')
                $StatusInAff = 'Active';
            else
                mydie("New StatusInAffRemark appeared: $StatusInAffRemark ");

            switch ($v['approval_status'])
            {
                case 'approved':
                    $Partnership = 'Active';
                    break;
                case 'Pending':
                    $Partnership = 'Pending';
                    break;
                case 'rejected':
                    $Partnership = 'Declined';
                    break;
                case null:
                    $Partnership = 'NoPartnership';
                    break;
                case '':
                    $Partnership = 'NoPartnership';
                    break;
                default:
                    mydie("New approval_status appeared: {$v['approval_status']} ");
                    break;
            }

            $AffDefaultUrl = '';
            if ($StatusInAff == 'Active' && $Partnership == 'Active') {
                $find_url = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=findById&id={$IdInAff}";
                $find_result = $this->oLinkFeed->GetHttpResult($find_url);
                $find_result = json_decode($find_result['content'], true);
                $this->checkCallApiRate($callApiNum, $startMinut);

                if ($find_result['response']["status"] != 1) {
                    print_r($find_result['response']['errors']);
                    mydie("Failed request api method findById by {$IdInAff}!");
                }

                $agree = @$find_result['response']['data']['AffiliateOffer']['agreed_terms_and_conditions'];
                $Partnership = is_null($agree) ? 'NoPartnership' : 'Active';

                //get AffDefaultUrl
                $default_url = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=generateTrackingLink&offer_id={$IdInAff}";
                $default_result = $this->oLinkFeed->GetHttpResult($default_url);
                $default_result = json_decode($default_result['content'], true);
                $this->checkCallApiRate($callApiNum, $startMinut);

                if ($default_result['response']["status"] != 1) echo "Failed request api method generateTrackingLink by {$IdInAff} : {$default_result['response']['errorMessage']}! \n\r";

                $AffDefaultUrl = isset($default_result['response']['data']['click_url']) ? addslashes($default_result['response']['data']['click_url']) : '';
            }

            $arr_prgm[$IdInAff] = array(
                "AffId" => $this->info["AffId"],
                "IdInAff" => $IdInAff,
                "Name" => addslashes((trim($v['name']))),
                "Description" => addslashes($desc),
                "Homepage" => addslashes($Homepage),
                "StatusInAffRemark" => addslashes($StatusInAffRemark),
                "StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
                "Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                "CommissionExt" => addslashes($CommissionExt),
                "LastUpdateTime" => date("Y-m-d H:i:s"),
                "TermAndCondition" => $TermAndCondition,
                "SupportDeepUrl" => $SupportDeepUrl,
                'AffDefaultUrl' => $AffDefaultUrl,
            );


            //get TargetCountry
            $countries_url = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=getTargetCountries&ids[]={$IdInAff}";
            $countries_result = $this->oLinkFeed->GetHttpResult($countries_url);
            $countries_result = json_decode($countries_result['content'], true);
            $this->checkCallApiRate($callApiNum, $startMinut);
            $CountryExt = array();

            if ($countries_result['response']['status'] == 1) {
                $TargetCountryExt = '';
                foreach ($countries_result['response']['data'][0]['countries'] as $k=>$val) {
                    $CountryExt[] = $k;
                }
                if (!empty($CountryExt)) {
                    $TargetCountryExt = addslashes(implode(",", $CountryExt));
                }
                $arr_prgm[$IdInAff]['TargetCountryExt'] = addslashes($TargetCountryExt);
            }

            //get CategoryExt
            $category_url = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=getCategories&ids[]={$IdInAff}";
            $category_result = $this->oLinkFeed->GetHttpResult($category_url);
            $category_result = json_decode($category_result['content'], true);
            $this->checkCallApiRate($callApiNum, $startMinut);
            $Category = array();

            if ($category_result['response']['status'] == 1) {
                $CategoryExt = '';
                foreach ($category_result['response']['data'][0]['categories'] as $val) {
                    $Category[] = $val['name'];
                }
                if (!empty($Category)) {
                    $CategoryExt = addslashes(implode(",", $Category));
                }
                $arr_prgm[$IdInAff]['CategoryExt'] = addslashes($CategoryExt);
            }

            //get LogoUrl
            $LogoUrl_url = "https://wowtrk.api.hasoffers.com/Apiv3/json?api_key={$this->apikey}&Target=Affiliate_Offer&Method=getThumbnail&ids[]={$IdInAff}";
            $LogoUrl_result = $this->oLinkFeed->GetHttpResult($LogoUrl_url);
            $LogoUrl_result = json_decode($LogoUrl_result['content'], true);
            $this->checkCallApiRate($callApiNum, $startMinut);

            if ($LogoUrl_result['response']['status'] == 1) {
                $Logo = end($LogoUrl_result['response']['data'][0]['Thumbnail']);
                $arr_prgm[$IdInAff]['LogoUrl'] = addslashes($Logo['url']);
            }

            $program_num++;
            if(count($arr_prgm) >= 1){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
        }

        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            unset($arr_prgm);
        }
        echo "\tGet Program by api end\r\n";

        if($program_num < 10){
            mydie("die: program count < 10, please check program.\n");
        }

        echo "\tUpdate ({$program_num}) program.\r\n";
        echo "\tSet program country int.\r\n";

        $objProgram->setCountryInt($this->info["AffId"]);
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

    function checkCallApiRate(&$callNum, &$startMinut)
    {
        $nowMinut = floatval(time() / 60);
        $callNum ++;
        if ($nowMinut == $startMinut) {
            if ($callNum >= 40) {
                $sleepTime = ($nowMinut + 1) * 60 - time();
                echo "\r\nRun too fast, will take {$sleepTime} second off !\r\n";
                sleep($sleepTime);
            }
        }else {
            $callNum = 1;
            $startMinut = $nowMinut;
        }
    }

}