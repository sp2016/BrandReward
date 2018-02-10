<?php
/**
 * User: rzou
 * Date: 2017/10/27
 * Time: 16:35
 */
class LinkFeed_ShareASale
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchID;

    }

    function GetProgramFromAff($accountid, $affSiteAccName)
    {
        $this->account = $this->oLinkFeed->getAffAccountById($accountid);
        $this->info['AffLoginUrl'] = $this->account['LoginUrl'];
        $this->info['AffLoginPostString'] = $this->account['LoginPostString'];
        $this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];
        $this->info['AffLoginMethod'] = $this->account['LoginMethod'];
        $this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->site = $this->oLinkFeed->getAffAccountSiteByName($affSiteAccName);

        echo 'Site:' . $this->site['Name'] . "\r\n";
        $this->GetProgramByApi($this->site['SiteID'], $this->site['SiteIdInAff']);

        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->oLinkFeed->checkBatchID = $this->oLinkFeed->batchID;
        $this->oLinkFeed->CheckCrawlBatchData($this->info["AffID"], $this->site['SiteID']);
    }

    function GetProgramByApi($SiteID, $SiteIdInAff)
    {
        echo "\tGet Program by Page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;

        //get api content
        $myAffiliateID = '1273061';
        $APIToken = "fseaGaeoRUGCc3Tb";
        $APISecretKey = "RIv8sm9p7RSnuz7aBGd6xk0q7EZulz8z";
        $myTimeStamp = gmdate(DATE_RFC1123);

        $APIVersion = 2.3;
        $actionVerb = "ledger";
        $sig = $APIToken.':'.$myTimeStamp.':'.$actionVerb.':'.$APISecretKey;

        $sigHash = hash("sha256",$sig);

        $myHeaders = array("x-ShareASale-Date: $myTimeStamp","x-ShareASale-Authentication: $sigHash");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.shareasale.com/x.cfm?affiliateId=$myAffiliateID&token=$APIToken&version=$APIVersion&action=$actionVerb");
        curl_setopt($ch, CURLOPT_HTTPHEADER,$myHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $returnResult = curl_exec($ch);

        if ($returnResult) {
            //parse HTTP Body to determine result of request
            if (stripos($returnResult,"Error Code ")) {
                // error occurred
                trigger_error($returnResult,E_USER_ERROR);
            }
            else{
                // success
                echo $returnResult;
            }
        }

        else{
            // connection error
            trigger_error(curl_error($ch),E_USER_ERROR);
        }

        curl_close($ch);









        $page = 1;
        $hasNextPage = true;
        while ($hasNextPage) {
            echo "Page:$page\t";

            $apiUrl = "http://www.webgains.com/publisher/{$SiteIdInAff}/program/list/get-data/joined/all/order/name/sort/asc/keyword//country//category//status/?columns%5B%5D=name&columns%5B%5D=status&columns%5B%5D=exclusive&columns%5B%5D=id&columns%5B%5D=type&columns%5B%5D=categories&columns%5B%5D=keywords&columns%5B%5D=action&subcategory=&page=$page";
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $results = $this->GetHttpResultMoreTry($apiUrl, $request);
                $this->oLinkFeed->fileCachePut($cache_file, $results);
            }
            $result = file_get_contents($cache_file);

            $data = json_decode($result, true);
//			print_r($data);exit;

            if (!isset($data['data']) || empty($data['data'])) {
                mydie('Can\'t get data, please check the api !');
            }
            if ($data['pagesNumber'] <= $page) {
                $hasNextPage = false;
                if ($this->debug) print " NO NEXT PAGE  <br>\n";
            } else {
                $page++;
                if ($this->debug) print " Have NEXT PAGE  <br>\n";
            }

            foreach ($data['data'] as $val) {
                if (!isset($val['id']) || !$val['id']) {
                    continue;
                }
                $ProgramID = $val['id'];
                $Name = $val['name'];
                $Keywords = empty($val['keywords']['long']) ? $val['keywords']['short'] : $val['keywords']['long'];
                $Categories = @empty($val['categories']['long']) ? @$val['categories']['short'] : @$val['categories']['long'];

                $DetailPage = "http://www.webgains.com/publisher/{$SiteIdInAff}/program/view?programID={$ProgramID}";

                $arr_prgm[$ProgramID] = array(
                    "SiteID" => $SiteID,
                    "AccountID" => $this->account['AccountID'],
                    'AffID' => $this->info['AffID'],
                    'IdInAff' => $ProgramID,
                    'BatchID' => $this->oLinkFeed->batchID,
                    'Name' => addslashes(trim($Name)),
                    'MembershipStatus' => addslashes(trim($val['membershipStatus'])),
                    'Partnership' => addslashes(trim($val['membershipStatus'])),
                    'ProgramStatus' => addslashes(trim($val['status'])),
                    'Keywords' => addslashes($Keywords),
                    'Categories' => addslashes($Categories),
                    'Description' => addslashes($val['description']),
                    'ExclusiveToWebgains' => @addslashes($val['exclusiveToWG']),
                    'Type' => @addslashes($val['type']),
                    'NetworkName' => addslashes($val['networkName']),
                    'DetailPage' => $DetailPage,
                );

                if (count($arr_prgm) > 20) {
                    $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }
            }
        }

        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\n\tGet Program by page end\r\n";
        if ($program_num < 10) {
            mydie("die: program count < 10, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
    }
}