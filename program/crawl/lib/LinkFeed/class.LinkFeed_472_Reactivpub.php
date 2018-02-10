<?php
/**
 * User: rzou
 * Date: 2017/8/22
 * Time: 16:08
 */
class LinkFeed_472_Reactivpub
{
    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->siteId = 2960157;

        $this->islogined = false;
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByPage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function GetProgramByPage()
    {
        echo "\tGet Program by Page start\r\n";
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);

        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'get'
        );

        $hasNextPage = true;
        $numPrePage = 100;
        $page = 1;
        $dataUrl = 'http://partners.reactivpub.com/pan/aProgramList.action';
        while ($hasNextPage) {
            $request['postData'] = "programGEListParameterTransport.currentPage={$page}&searchPerformed=true&searchType=prog&programGEListParameterTransport.programIdOrName=&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId={$this->siteId}&programGEListParameterTransport.orderBy=programName&programAdvancedListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize={$numPrePage}&programAdvancedListParameterTransport.directAutoApprove=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=";
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"program_page_$page" . ".dat","cache_merchant");
            if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
            {
                $result = $this->GetHttpResultMoreTry($dataUrl, $request);
                $this->oLinkFeed->fileCachePut($cache_file,$result);
            }
            if(!file_exists($cache_file)) {
                mydie("die: merchant csv file does not exist. \n");
            }
            $result = file_get_contents($cache_file);

            print_r($result);exit;

            $page++;
        }

        if (count($arr_prgm)) {
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
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