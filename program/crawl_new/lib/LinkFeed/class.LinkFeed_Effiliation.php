<?php
/**
 * User: rzou
 * Date: 2017/8/31
 * Time: 10:56
 */
class LinkFeed_Effiliation
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;

    }

    function GetProgramFromAff($accountid)
    {
        $this->account = $this->oLinkFeed->getAffAccountById($accountid);
        $this->info['AffLoginUrl'] = $this->account['LoginUrl'];
        $this->info['AffLoginPostString'] = $this->account['LoginPostString'];
        $this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];
        $this->info['AffLoginMethod'] = $this->account['LoginMethod'];
        $this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";


        $this->site = $this->oLinkFeed->getAccountSiteById($accountid);
        foreach ($this->site as $v) {
            echo 'Site:' . $v['Name'] . "\r\n";
            $this->GetProgramByApi($v['SiteID'], $v['SiteIdInAff'], $v['APIKey']);
        }
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->CheckBatch();
    }

    function GetProgramByApi($SiteID, $SiteIdInAff, $APIKey)
    {
        echo "\tGet Program by Api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;
        $request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
        $this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);

        $status = array('active','inactive', 'pending', 'unregistered', 'closed', 'refused', 'recommendation');
        foreach ($status as $v){
            echo "\nget $v program data from api\n";

            $strUrl = sprintf('http://apiv2.effiliation.com/apiv2/programs.json?key=%s&filter=%s&lg=en&fields=1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111', $APIKey, $v);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "{$v}_data.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $results = $this->GetHttpResultMoreTry($strUrl, $request);
                $this->oLinkFeed->fileCachePut($cache_file, $results);
            }
            $result = file_get_contents($cache_file);
            $data = json_decode($result, true);
            print_r($data);exit;
            if (!isset($data['programs']) || empty($data['programs'])) {
                mydie("Can't get $v program data, please check the api !");
            }

            foreach ($data['programs'] as $val) {

            }

        }




        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            $arr_prgm = array();
        }



        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\tGet Program by Api end\r\n";
        if ($program_num < 10) {
            mydie("die: program count < 10, please check program.\n");
        }
        echo "\tUpdate ({$program_num}) program.\r\n";
    }

    function CheckBatch()
    {
        $objProgram = new ProgramDb();
        //$this->oLinkFeed->batchid;
        $objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
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
        }
        return $result;
    }

}


?>