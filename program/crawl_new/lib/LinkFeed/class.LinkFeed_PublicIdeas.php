<?php
/**
 * User: rzou
 * Date: 2017/9/4
 * Time: 17:59
 */
class LinkFeed_PublicIdeas
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
            $this->GetProgramByPage($v['SiteID'], $v['SiteIdInAff']);
        }
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->CheckBatch();
    }

    function GetProgramByPage($SiteID, $SiteIdInAff)
    {
        echo "\tGet Program by Page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $use_true_file_name = true;
        $request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "", 'header'=>1, 'nobody'=>1);

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);
        $results = $this->GetHttpResultMoreTry('http://publisher.publicideas.com/index.php?action=myprograms', $request);
        echo $results;exit;


        $page = 1;
        $numPrePage = 100;
        $hasNextPage = true;
        while ($hasNextPage) {
            echo "Page:$page\t";

            $listUrl = sprintf("http://publisher.publicideas.com/index.php?action=myprograms&type=&keyword=&nb_page=%s&index=%s", $numPrePage,($page-1)*$numPrePage);
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $results = $this->GetHttpResultMoreTry($listUrl, $request);
                $results = $this->characet($results);
                $this->oLinkFeed->fileCachePut($cache_file, $results);
            }
            $result = file_get_contents($cache_file);
            echo $result;exit;




                $program_num++;

                if (count($arr_prgm) > 0) {
                    $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }


            if ($page > 500) {
                mydie("There have too many page!");
            }
        }

        if (count($arr_prgm) > 0) {
            $objProgram->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\tGet Program by page end\r\n";
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

    function characet($data)
    {
        if(!empty($data)){
            $filetype = mb_detect_encoding($data , array('utf-8','gbk','latin1','big5','ISO-8859-1')) ;
            if( $filetype != 'utf-8'){
                $data = mb_convert_encoding($data ,'utf-8' , $filetype);
            }
        }
        return $data;
    }

}