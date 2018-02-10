<?php
include_once('text_parse_helper.php');
class LinkFeed_2047_Actionpay
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->apiKey = 'LlGC5ZPTT3ofiWfi';
        $this->countryExt = array(
            6537 => 'RU',
            6825 => 'RU',
            9398 => 'RU',
            2741 => 'RU',
            8056 => 'RU',
            9735 => 'RU',
            10415 => 'RU',
            10276 => 'RU',
            5712 => 'RU',
            9789 => 'RU',
            9926 => 'RU',
            7934 => 'RU',
            8035 => 'RU',
            8678 => 'RU',
            10955 => 'RU',
            8338 => 'RU',
            4554 => 'RU',
            9982 => 'RU',
            4213 => 'RU',
            8057 => 'RU',
            10303 => 'RU',
            10224 => 'RU',
            8719 => 'RU',
            3781 => 'RU',
            11074 => 'RU',
            6755 => 'RU',
            10449 => 'RU',
            11184 => 'RU',
            11166 => 'RU',
            11050 => 'RU',
            11049 => 'BY,RU',
            8611 => 'RU',
            8309 => 'RU',
            10522 => 'RU',
            3898 => 'RU',
            10756 => 'BR',
            10757 => 'BR',
            11023 => 'BR',
            10913 => 'BR',
            9752 => 'BR',
            10281 => 'BR',
            10808 => 'BR',
            9485 => 'BR',
            10738 => 'BR',
            8626 => 'BR',
        );
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->GetProgramByApi();
        $this->checkProgramOffline($this->info["AffId"], $check_date);

        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramByApi()
    {
        echo "\tGet Program by api start\r\n";
        $objProgram = new ProgramDb();
        $countryArr = $objProgram->getCountryCode();
        $arr_prgm = array();
        $program_num = 0;
        $request = array('AffId'=>$this->info['AffId'],'method'=>'get');

        //Get all programs whose have partnership with Brandreward
        $avilableArr = array();
        $url = sprintf('https://api.actionpay.net/en/apiWmMyOffers/?key=%s&format=json', $this->apiKey);
        $r = $this->GetHttpResult($url, $request, 'favouriteOffers', 'Active_program_data');
        $data = json_decode($r, true);
        if (empty($data['result']['favouriteOffers'])) {
            mydie("Failed to get programs whose have partnership with Brandreward!");
        }
        foreach ($data['result']['favouriteOffers'] as $v){
            if ($v['available']){
                $avilableArr[] = $v['offer']['id'];
            }
        }


        //Get all programs and detail
        $page = 1;
        $hasNextPage = true;
        while ($hasNextPage){
            echo "page:$page\t";
            $url = sprintf('https://api.actionpay.net/en/apiWmOffers/?key=%s&format=json&page=%s', $this->apiKey, $page);
            $r = $this->GetHttpResult($url, $request, 'offers', "offers_page$page");
            $data = json_decode($r, true);
            if ($page >= $data['result']['pageCount']) {
                $hasNextPage = false;
            }

            if (empty($data['result']['offers'])) {
                continue;
            }

            foreach ($data['result']['offers'] as $val) {
                $idInAff = intval($val['id']);

                if (!$idInAff) {
                    continue;
                }

                $statusInRemark = $val['status']['name'];
                if ($statusInRemark == 'Active'){
                    $status = 'Active';
                }else{
                    mydie("Find new status({$val['status']['name']}) in api!");
                }

                $partnership = 'NoPartnership';
                if (in_array($idInAff, $avilableArr)) {
                    $partnership = 'Active';
                }

                $countryExt = '';
                if (is_array($val['geo']['includeCountries']) && !empty($val['geo']['includeCountries'])){
                    $countryExt = join(',', $val['geo']['includeCountries']);
                }elseif(is_array($val['geo']['excludeCountries']) && !empty($val['geo']['excludeCountries'])){
                    $cArr = $countryArr;
                    foreach ($val['geo']['excludeCountries'] as $ecv) {
                        unset($cArr[$ecv]);
                    }
                    $countryExt = join(',', array_keys($cArr));
                }elseif(is_array($val['geo']['includeCities']) && !empty($val['geo']['includeCities'])){
                    if (isset($this->countryExt[$idInAff])){
                        $countryExt = $this->countryExt[$idInAff];
                    }else{
                        mydie("Find new merchant(idInAff=$idInAff,name={$val['name']}) only allow traffic of some city!");
                    }
                }elseif(is_array($val['geo']['excludeCities']) && !empty($val['geo']['excludeCities'])){
                    mydie("Find new merchant(idInAff=$idInAff,name={$val['name']}) only disallow traffic of some city!");
                }elseif($val['geoString'] == 'All countries'){
                    $countryExt = 'Global';
                }

                $categoryExt = '';
                if (is_array($val['categories']) && !empty($val['categories'])) {
                    foreach ($val['categories'] as $cv) {
                        $categoryExt .= $cv['name'] . ',';
                    }
                    $categoryExt = rtrim($categoryExt, ',');
                }

                $commissionExt = '';
                if (is_array($val['aims']) && !empty($val['aims'])) {
                    foreach ($val['aims'] as $cv) {
                        $commissionExt .= $cv['price'] . ',';
                    }
                    $commissionExt = rtrim($commissionExt, ',');
                }

                $termAndCondition = 'deniedTrafficTypes: ';
                if (!empty($val['deniedTrafficTypes'])) {
                    foreach ($val['deniedTrafficTypes'] as $tv) {
                        $termAndCondition .= $tv['name'] . ',';
                    }
                    $termAndCondition = rtrim($termAndCondition, ',');
                }
                $termAndCondition = $termAndCondition . ' ; trafficTypes: ';
                if (!empty($val['trafficTypes'])) {
                    foreach ($val['trafficTypes'] as $tv) {
                        $termAndCondition .= $tv['name'] . ',';
                    }
                    $termAndCondition = rtrim($termAndCondition, ',');
                }

                $AffDefaultUrl = '';
                $secondId = '';
                if ($status == 'Active' && $partnership == 'Active'){
                    $url = sprintf('https://api.actionpay.net/en/apiWmLinks/?key=%s&format=json&offer=%s', $this->apiKey, $idInAff);
                    $r = $this->GetHttpResult($url, $request, 'links', "links_$idInAff");
                    $data = json_decode($r, true);
                    $AffDefaultUrl = @$data['result']['links'][0]['url'];
                    $AffDefaultUrl = str_ireplace('subaccount', '[SUBTRACKING]', $AffDefaultUrl);

                    if ($val['deeplink']) {
                        $lastLink = end($data['result']['links']);
                        $deepUrl = $lastLink['url'];
                        if (stripos($deepUrl, '/url=') !== false) {
                            $secondId = $this->oLinkFeed->ParseStringBy2Tag($deepUrl, 'click/', '/');
                        }

                    }
                }

                $arr_prgm[$idInAff] = array(
                    'AffId' => $this->info["AffId"],
                    'IdInAff' => $idInAff,
                    'Name' => addslashes($val['name']),
                    'Description' => addslashes($val['description']),
                    'Homepage' => addslashes($val['link']),
                    'CommissionExt' => addslashes($commissionExt),
                    'CreateDate' => addslashes($val['createDate']),
                    'StatusInAffRemark' => addslashes($statusInRemark),
                    'StatusInAff' => $status,
                    'Partnership' => $partnership,
                    'SupportDeepUrl' => $val['deeplink'] ? 'YES' : 'NO',
                    'TargetCountryExt' => addslashes($countryExt),
                    'CategoryExt' => addslashes($categoryExt),
                    'TermAndCondition' => addslashes($termAndCondition),
                    'LogoUrl' => addslashes($val['logo']),
                    'LastUpdateTime' => date("Y-m-d H:i:s"),
                    'AffDefaultUrl' => $AffDefaultUrl,
                    'SecondIdInAff' => addslashes($secondId)
                );

                $program_num++;
                if(count($arr_prgm) >= 100){
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                    $arr_prgm = array();
                }
            }
            $page ++;
        }

        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            unset($arr_prgm);
        }
        echo "\n\tGet Program by api end\r\n";

        if($program_num < 10){
            mydie("die: program count < 10, please check program.\n");
        }

        echo "\tUpdate ({$program_num}) program.\r\n";
        echo "\tSet program country int.\r\n";

        $objProgram->setCountryInt($this->info["AffId"]);
    }

    function getCouponFeed()
    {
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

        $all_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
        $arrToUpdate = array();

        $strUrl = 'https://actionpay.net/ru-en/couponFeed/xmlFilter/id:6043';
        echo $strUrl.PHP_EOL;
        $result = $this->GetHttpResult($strUrl,$request, '', 'couponfeed_6043');
        $result = json_decode(json_encode(simplexml_load_string($result)), true);
        if (!isset($result['promotion']) || empty($result['promotion'])){
            mydie("Error, can't get coupons info");
        }

        foreach($result['promotion'] as $cv){
            $link_id = intval($cv['id']);
            if(!$link_id){
                continue;
            }

            $aff_mer_id = intval($cv['offer_id']);
            if(!isset($all_merchant[$aff_mer_id])) {
                continue;
            }

            $link_name = trim($cv['title']);
            $link_desc = trim($cv['description']);
            $promo_type = "coupon";

            $couponcode = '';
            if(!is_array($cv['code'])) {
                $couponcode = trim($cv['code']);
            }

            $LinkAffUrl = trim($cv['landing']);

            if($couponcode == ''){
                $code = get_linkcode_by_text($link_desc);
                if (!empty($code))
                {
                    $couponcode = $code;
                }
            }

            $start_date = "0000-00-00 00:00:00";
            if(isset($cv['begin_date']) && $cv['begin_date'] > 0){
                $start_date = strtotime(trim($cv['begin_date']));
                $start_date = date("Y-m-d H:i:s", $start_date);
            }

            $end_date = "0000-00-00 00:00:00";
            if(isset($cv['end_date']) && $cv['end_date'] > 0){
                $end_date = strtotime(trim($cv['end_date']));
                $end_date = date("Y-m-d H:i:s", $end_date);
            }

            $arr_one_link = array(
                "AffId" => $this->info["AffId"],
                "AffMerchantId" => $aff_mer_id,
                "AffLinkId" => $link_id,
                "LinkName" =>  addslashes($link_name),
                "LinkDesc" =>  addslashes($link_desc),
                "LinkStartDate" => $start_date,
                "LinkEndDate" => $end_date,
                "LinkPromoType" => $promo_type,
                "LinkHtmlCode" => '',
                "LinkCode" => addslashes($couponcode),
                "LinkOriginalUrl" => '',
                "LinkImageUrl" => '',
                "LinkAffUrl" => addslashes($LinkAffUrl),
                "DataSource" => 0,
                "IsDeepLink" => 'UNKNOWN',
                "Type"       => 'promotion'
            );
            $arr_one_link['LinkHtmlCode'] = create_link_htmlcode_image($arr_one_link);
            $this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
            $arrToUpdate[] = $arr_one_link;
            $arr_return["AffectedCount"] ++;
            if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"]))
                $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
            $arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;
            if(sizeof($arrToUpdate) > 100)
            {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
                $arrToUpdate = array();
            }
        }

        if(sizeof($arrToUpdate) > 0)
        {
            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
            $arrToUpdate = array();
        }
        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
        return $arr_return;
    }

    function checkProgramOffline($AffId, $check_date)
    {
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

    function GetAllLinksByAffId()
    {
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
        $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
        $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);

        foreach ($arr_merchant as $merinfo){
            $url = sprintf('https://api.actionpay.net/en/apiWmLinks/?key=%s&format=json&offer=%s', $this->apiKey, $merinfo['IdInAff']);
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            if (empty($r) || empty($r['code']) || $r['code'] != 200 || empty($r['content']))
                continue;
            $content = $r['content'];
            $data = @json_decode($content, true);

            if (empty($data) || !is_array($data))
                continue;
            $links = array();
            foreach ($data['result']['links'] as $v)
            {
                $link = array(
                    "AffId" => $this->info["AffId"],
                    "AffMerchantId" => $merinfo['IdInAff'],
                    "AffLinkId" => $v['landing']['id'],
                    "LinkName" => $v['landing']['name'],
                    "LinkDesc" => '',
                    "LinkStartDate" => '0000-00-00',
                    "LinkEndDate" => '0000-00-00',
                    "LinkPromoType" => 'N/A',
                    "LinkOriginalUrl" => "",
                    "LinkHtmlCode" => '',
                    "LinkAffUrl" => $v['url'],
                    "DataSource" => "0",
                    "IsDeepLink" => 'UNKNOWN',
                    "Type"       => 'link'
                );
                $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
                if (empty($link['AffLinkId']) || empty($link['LinkName']))
                    continue;
                $arr_return['AffectedCount'] ++;
                $links[] = $link;
            }
            echo sprintf("%s link(s) found. \n", count($links));
            if (count($links) > 0)
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
        }
        print_r($arr_return);
        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');

        return $arr_return;

    }

    function GetHttpResult($url, $request, $valStr, $cacheFileName, $retry=3)
    {
        $results = '';
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "data_" . date("YmdH") . "_{$cacheFileName}.dat", 'data', true);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            while ($retry) {
                $r = $this->oLinkFeed->GetHttpResult($url, $request);
                if ($valStr) {
                    if (strpos($r['content'], $valStr) !== false) {
                        $results = $r['content'];
                        break;
                    }
                } elseif (!empty($r['content'])) {
                    $results = $r['content'];
                    break;
                }
                $retry--;
            }

            if (!$results) {
                mydie("Can't get the content of '{$url}', please check the val string !\r\n");
            }
            $this->oLinkFeed->fileCachePut($cache_file, $results);

            return $results;
        }
        $result = file_get_contents($cache_file);

        return $result;
    }

}