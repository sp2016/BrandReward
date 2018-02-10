<?php
/**
 * User: rzou
 * Date: 2017/10/11
 * Time: 14:31
 */

class LinkFeed_8888_webgains_uk_br
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->DataSource = array("feed" => 14, "website" => 15);

        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->GetProgramByApi();

        $this->checkProgramOffline($this->info["AffId"], $check_date);

        echo "\tSet program country int.\r\n";
        $objProgram = new ProgramDb();
        $objProgram->setCountryInt($this->info["AffId"]);

        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramByApi()
    {
        echo "\tGet Program by api start\r\n";
        $objProgram = new ProgramDb();
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

        $arr_prgm = array();
        $program_num = 0;

        $page = 1;
        $pagesize = 100;
        while ($page) {
            $apiUrl = sprintf('http://localhost/bdg_outgoing/program/crawl_new/api/programApi.php?site_name=Webgains_UK_BR&page=%s&pagesize=%s', $page, $pagesize);
            $result = $this->oLinkFeed->GetHttpResult($apiUrl, $request);
            $data = json_decode($result['content'], true);
            if (!isset($data['data'])) {
                mydie("Can't find data from api!");
            }

            foreach ($data['data'] as $prgm) {
                if (!isset($prgm['ProgramId'])) {
                    continue;
                }
                $programID = $prgm['ProgramId'];
                $arr_prgm[$programID] = array(
                    "Name" => addslashes(html_entity_decode($prgm['Name'])),
                    "AffId" => $this->info["AffId"],
                    "Homepage" => addslashes($prgm['Url']),
                    "IdInAff" => $programID,
                    "TargetCountryExt" => addslashes(trim($prgm['Country'])),
                    "StatusInAff" => addslashes(trim($prgm['StatusInAff'])),
                    "Partnership" => addslashes(trim($prgm['PartnershipStatus'])),
                    "Description" => addslashes($prgm['PartnershipStatus']),
                    "AllowNonaffCoupon"=>$AllowNonaffCoupon,
                    "AllowNonaffPromo"=>$AllowNonaffPromo,
                    //"JoinDate" => $JoinDate,
                    //"CreateDate" => $CreateDate,
                    //"CommissionExt" => addslashes($CommissionExt),
                    //"CookieTime" => $ReturnDays,
                    //"SEMPolicyExt" => addslashes($SEMPolicyExt),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    //"DetailPage" => $prgm_url,
                    //"SupportDeepUrl" => $SupportDeepurl,
                );
            }

        }

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

}