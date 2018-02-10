<?php
class LinkFeed_2045_Red_Fire_Network
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->ApiKey = '7340fd4aa7439f65a769ca3a8610a1bf';
        $this->NetworkId = 'redfirenetwork';
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

        $hasNextPage = true;
        $page = 1;
        while ($hasNextPage) {
            echo "page:$page\t";

            $url = sprintf('http://%s.afftrack.com/api/?key=%s&action=offers&format=json&page=%s&limit=100', $this->NetworkId, $this->ApiKey, $page);
            $result = $this->GetHttpResult($url, $request, 'offers', "affers_page_$page");
            $result = json_decode($result, true);
//            print_r($result);exit;

            if ($result['total_offers'] <= $result['page_requested'] * $result['limit']) {
                $hasNextPage = false;
            }
            $currency = $result['currency'];

            foreach ($result['offers'] as $prgm_info) {
                $IdInAff = $prgm_info['offer_id'];
                if (!$IdInAff)
                    continue;

                $StatusInAffRemark = $prgm_info['offer_status'];
                switch ($StatusInAffRemark) {
                    case 'Approval Required / Must Apply':
                        $Partnership = 'NoPartnership';
                        break;
                    case 'Approved/Available':
                        $Partnership = 'Active';
                        break;
                    default:
                        mydie('Find new partnership: ' . $prgm_info['offer_status']);
                }

                $CommissionExt = $currency . floatval($prgm_info['offer_commission']);

                $arr_prgm[$IdInAff] = array(
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $IdInAff,
                    "Name" => addslashes(trim($prgm_info['offer_name'])),
					"Description" => addslashes($prgm_info['offer_requirements']),
                    "Homepage" => addslashes($prgm_info['preview_link']),
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
                    "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "CommissionExt" => $CommissionExt,
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    'TargetCountryExt' => addslashes(trim($prgm_info['offer_countries_allowed'])),
                    'CategoryExt' => addslashes(trim($prgm_info['offer_category'])),
                );
                $program_num ++;

                if (count($arr_prgm) >= 100) {
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                    $arr_prgm = array();
                }
            }
            $page++;
        }

        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            unset($arr_prgm);
        }
        echo "\n\tGet Program by api end\r\n";

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