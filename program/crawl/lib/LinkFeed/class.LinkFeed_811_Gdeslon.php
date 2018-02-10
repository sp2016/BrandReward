<?php
require_once 'text_parse_helper.php';
class LinkFeed_811_Gdeslon
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->islogined = false;
        if (SID == 'bdg02'){
            $this->api_token = '0c3d2873722b7d277b47fc0ff2a93202c4364c49';
            $this->blaskList = array(79971,79101,78877,78108,77878,76976,79959,75978,72655,60961,42339,63340,71030);
        }else {
            $this->api_token = '7170cd60b85e172cc5ce1be5c899f2d59be3b456';
            $this->blaskList = array(63340,81062);
        }
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
        $this->info['AffLoginPostString'] = 'authenticity_token='.urlencode($token).'&user_session%5Bemail%5D='.urlencode($this->info['Account']).'&user_session%5Bpassword%5D='.urlencode($this->info['Password']).'&user_session%5Bremember_me%5D=0&user_session%5Bremember_me%5D=1';
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

    function getCouponFeed()
    {
        //计算时区差异导致的时间差（考虑夏令时）。
        $MoscowTime = new DateTime(null, new DateTimeZone('Europe/Moscow'));
        $moscowOff = $MoscowTime->getOffset();
        $Los_Angeles = new DateTime(null, new DateTimeZone('America/Los_Angeles'));
        $Los_AngelesOff = $Los_Angeles->getOffset();
        $offTime = $Los_AngelesOff - $moscowOff;

        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

        $all_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
        $arrToUpdate = array();

        $strUrl = 'https://www.gdeslon.ru/api/coupons.xml?search%5Bkind%5D=4&search%5Bcoupon_type%5D%5B%5D=coupons&search%5Bcoupon_type%5D%5B%5D=promo&api_token=' . $this->api_token;
        echo $strUrl.PHP_EOL;
        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        $result = $r["content"];
        $result = json_decode(json_encode(simplexml_load_string($result)), true);
        if (!isset($result['coupons']['coupon']) || empty($result['coupons']['coupon'])){
            mydie("Error, can't get coupons info");
        }

        foreach($result['coupons']['coupon'] as $cv){
            $link_id = intval($cv['id']);
            if(!$link_id){
                continue;
            }

            $aff_mer_id = intval($cv['merchant-id']);
            if(!isset($all_merchant[$aff_mer_id])) {
                continue;
            }

            $link_name = trim($cv['name']);
            $link_desc = '';
            if(!is_array($cv['description'])) {
                $link_desc = trim($cv['description']);
            }
            $promo_type = "coupon";

            $couponcode = '';
            if(!is_array($cv['code'])) {
                $couponcode = trim($cv['code']);
            }

            $LinkAffUrl = trim($cv['url']);

            if($couponcode == ''){
                $code = get_linkcode_by_text($link_desc);
                if (!empty($code))
                {
                    $couponcode = $code;
                }
            }

            $start_date = "0000-00-00 00:00:00";
            if(isset($cv['start-at']) && $cv['start-at'] > 0){
                $start_date = strtotime(trim($cv['start-at'])) + $offTime;
                $start_date = date("Y-m-d H:i:s", $start_date);
            }

            $end_date = "0000-00-00 00:00:00";
            if(isset($cv['finish-at']) && $cv['finish-at'] > 0){
                $end_date = strtotime(trim($cv['finish-at'])) + $offTime;
                $end_date = date("Y-m-d H:i:s", $end_date);
            }

            $arr_one_link = array(
                "AffId" => $this->info["AffId"],
                "AffMerchantId" => $aff_mer_id,
                "AffLinkId" => $link_id,
                "LinkName" =>  $link_name,
                "LinkDesc" =>  $link_desc,
                "LinkStartDate" => $start_date,
                "LinkEndDate" => $end_date,
                "LinkPromoType" => $promo_type,
                "LinkHtmlCode" => '',
                "LinkCode" => $couponcode,
                "LinkOriginalUrl" => '',
                "LinkImageUrl" => '',
                "LinkAffUrl" => $LinkAffUrl,
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

    function GetTransactionFromAff($start_date, $end_date)
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Transaction from $start_date to $end_date start @ {$check_date}\r\n";

        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'post',
            "postdata" => http_build_query(array(
                        'created_at' => array(
                            'date' => $start_date,
                            'period' => (strtotime($end_date) - strtotime($start_date))/(12*3600) + 1
                        )
                    )),
            "userpwd" => $this->info['TransactionApiKey']
        );
        $url= 'https://www.gdeslon.ru/api/orders/';
        echo "req => {$url} \n";
        $result = $this->GetHttpResult($url, $request, '', 'transaction');
        $result = json_decode($result, true);

        foreach ($result as $v){
            $transactionId = $v['id'];
            $createdTime = date('Y-m-d H:i:s', strtotime($v['created_at']) + 8 * 3600);
            $updateTime = date('Y-m-d H:i:s', strtotime($v['last_updated_at'])  + 8 * 3600);
            $orderid = $v['gdeslon_order_id'];
            $sid = $v['sub_id'];
            $programId = $v['merchant_id'];
            $programname = $v['merchant_name'];

            switch ($v['state']){
                case 0:
                    $tradestatus = 'new';
                    break;
                case 1:
                    $tradestatus = 'canceled';
                    break;
                case 2:
                    $tradestatus = 'postponed ';
                    break;
                case 3:
                    $tradestatus = 'confirmed ';
                    break;
                case 4:
                    $tradestatus = 'paid';
                    break;
                default :
                    $tradestatus = '';
                    break;
            }


            switch ($v['type']) {
                case 0:
                    $tradetype = 'commodity order';
                    break;
                case 1:
                    $tradetype = 'lead';
                    break;
                default :
                    $tradetype = ' ';
                    break;
            }

            $referrer = '';
            $curency = strtoupper($v['currency']);
            $oldSales = (!empty($v['order_payment'])) ? $v['order_payment'] : '';
            $oldCommission = $v['partner_payment'];

            $tdate = date("Y-m-d",strtotime($createdTime));
            require_once "currency_exchange.php";
            $cur_exr = $this->oLinkFeed->cur_exchange($curency, 'USD',$tdate);
            $sales = $oldSales > 0 ? round($oldSales * $cur_exr, 4) : 0;
            $commission = $oldCommission > 0 ? round($oldCommission * $cur_exr, 4) : 0;

            $replace_array = array(
                '{createtime}'      => trim($createdTime),
                '{updatetime}'      => trim($updateTime),
                '{sales}'           => $sales,
                '{commission}'      => $commission,
                '{idinaff}'         => $programId,
                '{programname}'     => trim($programname),
                '{sid}'             => trim($sid),
                '{orderid}'         => trim($orderid),
                '{clicktime}'       => trim($createdTime),
                '{tradeid}'         => trim($transactionId),
                '{tradestatus}'     => trim($tradestatus),
                '{oldcur}'          => $curency,
                '{oldsales}'        => $oldSales,
                '{oldcommission}'   => $oldCommission,
                '{tradetype}'       => trim($tradetype),
                '{referrer}'        => $referrer,
                '{cancelreason}'    => '',
            );
            $_day = date("Y-m-d", strtotime($createdTime));
            $rev_file = AFF_TRANSACTION_DATA_PATH . '/revenue_' . str_replace('-', '', $_day) . '.upd';
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
            }

            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
        }

        foreach ($fws as $file => $f) {
            fclose($f);
        }

        echo "Craw Transaction end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByApi();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function GetProgramByApi()
    {
        echo "\tGet Program by api start\r\n";

        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'get'
        );

        $programInfo = array();
        $pApiUrl = 'https://www.gdeslon.ru/api/users/shops.xml?api_token=' . $this->api_token;
        $shops = $this->GetHttpResult($pApiUrl, $request, 'gdeslon', 'program_list_xml');
        $shops = json_decode(json_encode(simplexml_load_string($shops)),true);

        foreach ($shops['shops']['shop'] as $val){
            $programInfo[$val['id']]['country'] = $val['country'];
            $programInfo[$val['id']]['categories'] = isset($val['categories']['category']) ? $val['categories']['category'] : '';
            $programInfo[$val['id']]['commission'] = $val['gs-commission-mark'];
        }

        $r = $this->oLinkFeed->GetHttpResult('http://api.gdeslon.ru/merchants.json');
        $apiResponse = json_decode($r['content'], true);

        if (empty($apiResponse)){
            mydie("Can't get aff program list!");
        }

        $this->login();
        foreach ($apiResponse as $val){
            $idInAff = intval($val['_id']);
            if (!$idInAff || in_array($idInAff,$this->blaskList)){
                continue;
            }
            echo "$idInAff\t";
            $name = $val['name'];

            $pDetailUrl = 'https://www.gdeslon.ru/users/aliexpress-vip-' . $idInAff;
            $result = $this->GetHttpResult($pDetailUrl, $request, '', 'detail_' . $idInAff);

            if (stripos($result, 'Страница не найдена!') !== false){
                continue;
            }

            preg_match_all('@<td><span (class="no-data")>.*</span></td>@', $result, $m);

            $statusInAff = 'Active';
            if(isset($m[1]) && count($m[1])>1){
                $statusInAff = 'Offline';
            }

            $strPos = 0;
            $LogoUrl = $this->oLinkFeed->ParseStringBy2Tag($result, 'user-image-regular" src="', '"', $strPos);
            $homepage = $this->oLinkFeed->ParseStringBy2Tag($result, array('Адрес','href="'), '"', $strPos);
            $needJoin = $this->oLinkFeed->ParseStringBy2Tag($result, array('<fieldset','button class="'), '"', $strPos);

            if ($needJoin){
                if ($needJoin == 'join'){
                    $Partnership = 'NoPartnership';
                }else{
                    mydie("Find new partnership!");
                }
            }else{
                $Partnership = 'Active';
            }

            $commission = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>Комиссия</td>','</tr', $strPos));
            $CookieTime = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>Время жизни куки</td>','</tr', $strPos));

            $desc = $this->oLinkFeed->ParseStringBy2Tag($result, array('Описание','<div','>'),'</div', $strPos);
            $TermAndCondition = $this->oLinkFeed->ParseStringBy2Tag($result, array('Условия','<div','>'),'</div', $strPos);

            $category = '';
            if (isset($programInfo[$idInAff]) && !empty($programInfo[$idInAff]['categories'])){
                if (isset($programInfo[$idInAff]['categories']['name'])){
                    $category = $programInfo[$idInAff]['categories']['name'];
                }else {
                    foreach ($programInfo[$idInAff]['categories'] as $cv) {
                        $category .= $cv['name'] . ',';
                    }
                    $category = rtrim($category, ',');
                }
            }

            $targetCountryExt = isset($programInfo[$idInAff]['country']) ? $programInfo[$idInAff]['country'] : '';

            $arr_prgm[$idInAff] = array(
                "AffId" => $this->info["AffId"],
                "IdInAff" => $idInAff,
                "Name" => addslashes($name),
                "Description" => addslashes($desc),
                "Homepage" => addslashes($homepage),
                "StatusInAff" => $statusInAff,                        //'Active','TempOffline','Offline'
                "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                "CommissionExt" => addslashes(trim($commission)),
                "LastUpdateTime" => date("Y-m-d H:i:s"),
                "TermAndCondition" => addslashes($TermAndCondition),
                'TargetCountryExt' => addslashes(trim($targetCountryExt)),
                'CategoryExt' => addslashes(trim($category)),
                'AffDefaultUrl' => "https://sf.gdeslon.ru/cf/{$this->api_token}?mid={$idInAff}",
                'LogoUrl' => addslashes($LogoUrl),
                "DetailPage" => $pDetailUrl,
                'CookieTime' => addslashes($CookieTime),
                'SupportDeepUrl' => 'YES'
            );
            $program_num ++;

            if (count($arr_prgm) >= 100) {
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                $arr_prgm = array();
            }
        }

        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            unset($arr_prgm);
        }
        echo "\tGet Program by api end\r\n";

        if ($program_num < 10) {
            mydie("die: program count < 10, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
        echo "\tSet program country int.\r\n";

        $objProgram->setCountryInt($this->info["AffId"]);
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