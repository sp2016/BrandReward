<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TD.php");
class LinkFeed_2046_DT_RU
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->DataSource = array("feed" => 52, "website" => 27);
        $this->getStatus = false;

        $this->file = "programlog_{$aff_id}_" . date("Ymd_His") . ".csv";

        $this->siteId = 2998167;
        $this->contryCode = 'RU';
        $this->token = 'C4FFFFD3B6C9564601584FB7CF6FE4B16C1B5AD5';
        $this->productToken = 'EC54B266CF7339B6B6EA9D7D4860BDC7A804865F';
        $this->apiKey = '226f9cccfd563da6d964ff83dd1a5792';
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
        echo "\tGet Program by Api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array('AffId'=>$this->info['AffId'],'method'=>'get');
        $apiUrl = 'http://api.doubletrade.ru/offers/?web_id='.$this->siteId.'&report_key=' . $this->apiKey;

        $result = $this->GetHttpResult($apiUrl, $request, 'affiliate', 'program_list');
        $result = json_decode(json_encode(simplexml_load_string($result)), true);
        if (empty($result['matrix']['rows']['row'])){
            mydie("Can't get data from api");
        }

        foreach ($result['matrix']['rows']['row'] as $val){
            $programId = intval($val['programId']);
            if (!$programId){
                continue;
            }

            if (!isset($arr_prgm[$programId])) {
                $programName = trim($val['programName']);
                $homepage = trim($val['AdvertiserWebsite']);
                $logo = trim($val['logo']);
                $affDefaultUrl = trim($val['trackingURL']);
                $cookieTime = intval($val['cookieLifetime']);
                $terms = '';
                if (!empty($val['trafficSources'])) {
                    foreach ($val['trafficSources'] as $tk => $tv) {
                        $terms .= $tk . ': ' . $tv . ",\n";
                    }
                }

                $strStatus = $val['status'];
                switch ($strStatus) {
                    case 'Accepted' :
                        $partnership = 'Active';
                        break;
                    case 'Not Applied' :
                        $partnership = 'NoPartnership';
                        break;
                    case 'On Hold' :
                        $partnership = 'NoPartnership';
                        break;
                    case 'Under Consideration' :
                        $partnership = 'Pending';
                        break;
                    case 'Denied' :
                        $partnership = 'Declined';
                        break;
                    case 'Ended' :
                        $partnership = 'Expired';
                        break;
                    default :
                        mydie("Find new partnership inaff ({$val['status']})");
                        break;
                }

                $arr_prgm[$programId] = array(
                    "Name" => addslashes($programName),
                    "AffId" => $this->info["AffId"],
                    "TargetCountryExt" => 'RU',
                    "IdInAff" => $programId,
                    "StatusInAffRemark" => addslashes($strStatus),
                    "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                    "Partnership" => $partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "Homepage" => addslashes($homepage),
                    "TermAndCondition" => addslashes($terms),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "AffDefaultUrl" => addslashes($affDefaultUrl),
                    "CookieTime" => $cookieTime,
                    "LogoUrl" => addslashes($logo)
                );
            }

            if ($val['isPercentage'] == 'yes'){
                $commission = $val['programTariffAmount'] . '%';
            }else{
                $commission = $val['programTariffCurrency'] . ' ' . $val['programTariffAmount'];
            }

            if (!isset($arr_prgm[$programId]['CommissionExt']) || empty($arr_prgm[$programId]['CommissionExt'])){
                $arr_prgm[$programId]['CommissionExt'] = $commission;
            }else{
                $arr_prgm[$programId]['CommissionExt'] .= ', ' . $commission;
            }

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
