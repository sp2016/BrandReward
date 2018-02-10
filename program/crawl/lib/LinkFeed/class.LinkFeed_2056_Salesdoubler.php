<?php
include_once "xml2array.php";
class LinkFeed_2056_Salesdoubler
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->file = "programlog_{$aff_id}_" . date("Ymd_His") . ".csv";
        $this->apiKey = 'cGFydG5lcnNoaXBzQGJyYW5kcmV3YXJkLmNvbQ==';
        $this->islogined = false;
        $this->aId = '63038';

        $this->country_ru_code_map = array(
            '/Украина/' => 'UA',
            '/Казахстан/' => 'KZ',
            '/Весь мир/' => '',
            '/Россия/' => 'RU',
            '/Беларусь/' => 'BY',
            '/Молдова/' => 'MD',
            '/Польша/' => 'PL'
        );
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByApi();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
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
        $r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
        $token = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('authenticity_token', 'value="'), '"');
        $this->info['AffLoginPostString'] = 'utf8=%E2%9C%93&authenticity_token='.urlencode($token).'&email='.urlencode($this->info['Account']).'&password='.urlencode($this->info['Password']).'&commit=To+come+in';
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => $this->info["AffLoginMethod"],
            "postdata" => $this->info["AffLoginPostString"],
            "no_ssl_verifyhost" => true,
            "header" => 1,
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

    function GetProgramByApi()
    {
        echo "\tGet Program by Api start\r\n";

        $this->login();

        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array('AffId'=>$this->info['AffId'],'method'=>'get');

        //first get the data from api (The data from api just part of all programs data);
        $api_program_info = array();
        $apiUrl = "https://www.salesdoubler.com.ua/affiliate/offers_xml?api_key=" . $this->apiKey;
        $result = $this->GetHttpResult($apiUrl, $request, 'offer', 'program_list');
        XML2Array::init('1.0', 'UTF-8');
        $result = XML2Array::createArray($result);
        if (empty($result['rsp']['offer'])){
            mydie("Can't get data from api");
        }
        foreach ($result['rsp']['offer'] as $val) {
            $programId = intval($val['offer_group_id']);
            if (!$programId) {
                continue;
            }
            $programName = trim($val['name']['@cdata']);
            if (!$programName) {
                continue;
            }
            $desc = $val['description']['@cdata'];
            $homepage = trim($val['preview_url']);
            $currency = addslashes(trim($val['currency']));
            $affDefaultUrl = trim($val['affiliate_link']);

            if ($val['payout_type'] == 'cpa_percentage') {
                $commission = floatval($val['payout']) . '%';
            } elseif ($val['payout_type'] == 'cpa_flat') {
                $commission = $currency . ' ' . floatval($val['payout']);
            } else {
                mydie("Find new payout type({$val['payout_type']}).");
            }

            $country = '';
            if (isset($val['regions']['region'])) {
                if (is_array($val['regions']['region'])) {
                    foreach ($val['regions']['region'] as $ctryv) {
                        $country .= $ctryv . ',';
                    }
                    $country .= rtrim($country, ',');
                } else {
                    $country = $val['regions']['region'];
                }
            }

            $api_program_info[$programId] = array(
                "Homepage" => addslashes($homepage),
                "AffDefaultUrl" => addslashes($affDefaultUrl),
                "CommissionExt" => $commission,
                "TargetCountryExt" => addslashes($country),
                "Description" => $desc
            );
        }

        //second get all programs data from page.
        $offersUrl = "https://www.salesdoubler.com.ua/affiliate/offers";
        $result = $this->GetHttpResult($offersUrl, $request, 'Кампании', 'program_offers_page');
        $result = preg_replace('@>\s+<@', '><', $result);
        $p_list_str = $this->oLinkFeed->ParseStringBy2Tag($result, array('<tbody','>'), '</tbody>');
        $p_list_arr = explode('</tr><tr>', $p_list_str);
        if (empty($p_list_arr)){
            mydie("Can't get data of programs from page.");
        }

        foreach ($p_list_arr as $val){
            $strPos = 0;
            preg_match('@href="\/affiliate\/offer_details\/(\d+)"@', $val, $m);
            $programId = $m[1];
            if (!$programId){
                continue;
            }
            echo $programId . "\t";

            $logo = 'http:'.trim($this->oLinkFeed->ParseStringBy2Tag($val, array('<img','src="'), '"', $strPos));
            $programName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($val, 'center>', '</center', $strPos)));
            $countryRU = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($val, array('<td','<td','<td','<td','<td','<td>'), '</td', $strPos)));
            $category = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($val, '<td>', '</td', $strPos)));

            $detailPage = "https://www.salesdoubler.com.ua/affiliate/offer_details/$programId";
            $result = $this->GetHttpResult($detailPage, $request, 'Новости', 'program_detail_' . $programId);
            $result = preg_replace('@>\s+<@', '><', $result);
            $commissionStr = $this->oLinkFeed->ParseStringBy2Tag($result, array('>Тариф<','<ul','>'), '</ul');
            $commissionArr = explode('</li><li', $commissionStr);

            $commission = $partnership = $desc = $homepage = $country = '';
            foreach ($commissionArr as $cv){
                $commissionItem = trim($this->oLinkFeed->ParseStringBy2Tag($cv , array('class="right"','>'), '<'));
                if (stripos($commissionItem, '%') === false){
                    $commissionItem = preg_replace('@\(.+\)@', '', $commissionItem);
                    $commissionItem = preg_replace('@грн\.@', 'UAH', $commissionItem);
                    $commissionItem = preg_replace('@,@', '.', $commissionItem);
                }
                $commission .= $commissionItem . ',';
            }
            $commission = rtrim($commission, ',');
            $statusStr = trim($this->oLinkFeed->ParseStringBy2Tag($result , array('>Тариф<','Аппрув', '<div', '>'), '</div'));
            if (stripos($statusStr, 'class="getApproveBtn getCode"') !== false){
                $partnership = 'NoPartnership';
            }elseif (stripos($statusStr, 'Аппрув получен') !== false){
                $partnership = 'Active';
            }else{
                mydie("Find new partnership status string($statusStr).");
            }

            if (isset($api_program_info[$programId])){
                $homepage = $api_program_info[$programId]['Homepage'];
                $affDefaultUrl = $api_program_info[$programId]['AffDefaultUrl'];
                $commission = $api_program_info[$programId]['CommissionExt'];
                $country = $api_program_info[$programId]['TargetCountryExt'];
                $desc = $api_program_info[$programId]['Description'];
            }else{
                $affDefaultUrl = "https://rdr.salesdoubler.com.ua/in/offer/$programId?aid=" . $this->aId;
                $homepage = $this->oLinkFeed->findFinalUrl($affDefaultUrl);
            }
            if (empty($country)){
                $country = preg_replace(array_keys($this->country_ru_code_map), array_values($this->country_ru_code_map), $countryRU);
            }

            $arr_prgm[$programId] = array(
                "Name" => addslashes($programName),
                "AffId" => $this->info["AffId"],
                "IdInAff" => $programId,
                "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                "Partnership" => $partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                "Homepage" => addslashes($homepage),
                "LastUpdateTime" => date("Y-m-d H:i:s"),
                "AffDefaultUrl" => addslashes($affDefaultUrl),
                "CommissionExt" => $commission,
                "CategoryExt" => addslashes($category),
                "TargetCountryExt" => addslashes($country),
                "Description" => addslashes($desc),
                "LogoUrl" => addslashes($logo),
                "SupportDeepUrl" => 'YES'
            );
            $program_num++;
            if(count($arr_prgm) >= 100){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                $arr_prgm = array();
            }
        }
        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            unset($arr_prgm);
        }
        if ($program_num < 1) {
            mydie("die: program count < 1, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
        echo "\tSet program country int.\r\n";
        $objProgram->setCountryInt($this->info["AffId"]);

        echo "\tGet Program by Api end\r\n";
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


?>
