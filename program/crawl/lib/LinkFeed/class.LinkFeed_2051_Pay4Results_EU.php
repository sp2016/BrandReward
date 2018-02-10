<?php
require_once 'text_parse_helper.php';

class LinkFeed_2051_Pay4Results_EU
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
        $this->getStatus = false;
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->GetProgramFromByPage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);

        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramFromByPage()
    {
        echo "\tGet Program by page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

        //program management adv
        $strUrl = "http://my.pay4results.eu/affiliates/Extjs.ashx?s=contracts";
        $hasNextPage = true;
        $page = 1;
        $arr_prgm = array();
        while($hasNextPage){
            echo "page $page\t";
            $postdata = array(
                'groupBy' => '',
                'groupDir' => 'ASC',
                'cu' => 1,
                'c' => '',
                'cat' => 0,
                'sv' => '',
                'cn' => '',
                'pf' => '',
                'st' => 0,
                'm' => '',
                'ct' => '',
                'pmin' => '',
                'pmax' => '',
                'mycurr' => true,
                't' => '',
                'p' => ($page - 1) * 100,
                'n' => 100,
            );
            $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => http_build_query($postdata));

            $r = $this->GetHttpResult($strUrl,$request,'total', "program_list_page$page");
            $res = json_decode($r,true);
            if(($res['total'] - ($page - 1) * 100) < 100)
                $hasNextPage = false;
            $result = $res['rows'];
            foreach($result as $item)
            {
                $strMerID = $item['campaign_id'];
                if (!$strMerID) {
                    continue;
                }

                $strMerName = trim($item['name']);
                if (!$strMerName) {
                    continue;
                }

                $country = '';
                if (!empty($item['countries']) && $item['countries'][0] != '-1'){
                    foreach ($item['countries'] as $c){
                        $country .= $c . ',';
                    }
                }
                $country = rtrim($country, ',');

                $StatusInAffRemark = trim($item['status']);
                $Homepage = trim($item['preview_link']);
                $AffDefaultUrl = $Partnership = '';
                $StatusInAff = 'Active';
                if($StatusInAffRemark == 'Active')
                {
                    $Partnership = 'Active';
                    $contid = $item['contract_id'];
                    $detailDefaulUrl = "http://my.pay4results.eu/affiliates/Extjs.ashx?s=creatives&cont_id=$contid";
                    $request = array("AffId" => $this->info["AffId"],"method" => "post", "postdata" => "s=creatives&cont_id=$contid", );
                    $detailDefaulUrlFull = $this->GetHttpResult($detailDefaulUrl,$request,'unique_link',"defaultUrl_$strMerID");
                    $detailDefaul = json_decode($detailDefaulUrlFull,true)['rows'];
                    $AffDefaultUrl = $detailDefaul[0]['unique_link'];
                }elseif($StatusInAffRemark == 'Pending'){
                    $Partnership = 'Pending';
                }elseif($StatusInAffRemark == 'Apply To Run' || $StatusInAffRemark == 'Inactive' || $StatusInAffRemark == 'Public'){
                    $Partnership = 'NoPartnership';
                }else{
                    mydie ("die: unknown $strMerName partnership: $StatusInAffRemark.\n");
                }

                if ($Partnership == 'Active' && stripos($strMerName, 'paused') !== false) {
                    $Partnership = 'NoPartnership';
                }

                $CommissionExt = '';
                switch ($item['price_format_id']) {
                    case 5 :
                        $CommissionExt =addslashes(trim($item['price_converted'])."%");
                        break;
                    case 1 :
                        $CommissionExt =addslashes("€".trim($item['price_converted']));
                        break;
                    default :
                        mydie("There find new currency! id={$item['currency_id']}");
                }

                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes(html_entity_decode(trim($strMerName))),
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $strMerID,
                    "StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
                    "StatusInAffRemark" => $StatusInAffRemark,
                    "Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "CommissionExt" => $CommissionExt,
                    "Homepage" => addslashes($Homepage),
                    "AffDefaultUrl" => addslashes($AffDefaultUrl),
                    "CategoryExt" => addslashes($item['vertical_name']),
                    "TargetCountryExt" => addslashes($country),
                    "Description" => addslashes($item['description']),
                    //"PublisherPolicy" => addslashes($item['restrictions'])
                );
                $program_num++;

                if(count($arr_prgm) >= 100){
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                    $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                    $arr_prgm = array();
                }
            }
            $page++;
            if($page > 300){
                mydie("die: Page overload.\n");
            }
        }
        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            unset($arr_prgm);
        }

        echo "\tGet Program by page end\r\n";
        if ($program_num < 10) {
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