<?php
class LinkFeed_2022_Opie_Network
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->apikey = 'AFF7DkezzD4sjPowJUTjy82OlQkP3R';

        $this->islogined = false;
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";
        $this->GetProgramByApi();
        $this->checkProgramOffline($this->info["AffId"], $check_date);
        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramByApi()
    {
        echo "\tGet Program by api start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;

        $strUrl = "http://partners.opienetwork.com/offers/offers.json?api_key={$this->apikey}";
        $r = $this->oLinkFeed->GetHttpResult($strUrl);

        if(empty($r['content']))
            mydie("Error type is can not get infomation from Api");

        $apiResponse = @json_decode($r['content'], true);
        if(!isset($apiResponse['data']) || empty($apiResponse['data']))
            mydie("API call failed !");

//        print_r($apiResponse);exit;
        $result = $apiResponse['data']['offers'];
        foreach($result as $v)
        {
            $IdInAff = intval(trim($v['id']));
            if(!$IdInAff)
                continue;
            echo "$IdInAff\t";
            $CommissionExt = trim($v['currency']) . trim($v['payout']);

            $arr_prgm[$IdInAff] = array(
                "AffId" => $this->info["AffId"],
                "IdInAff" => $IdInAff,
                "Name" => addslashes((trim($v['name']))),
                "Description" => addslashes($v['description']),
                "Homepage" => addslashes($v['preview_url']),
//                "StatusInAffRemark" => addslashes($StatusInAffRemark),
                "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
                "Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                "CommissionExt" => addslashes($CommissionExt),
                "LastUpdateTime" => date("Y-m-d H:i:s"),
//                "TermAndCondition" => $TermAndCondition,
//                "SupportDeepUrl" => $SupportDeepUrl,
                'TargetCountryExt'=> addslashes($v['countries_short']),
                'AffDefaultUrl' => addslashes($v['tracking_url']),
                'CategoryExt' => addslashes($v['categories']),
            );
            $program_num++;
            if(count($arr_prgm) >= 100){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
        }

        if(count($arr_prgm)){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            unset($arr_prgm);
        }
        echo "\n\tGet Program by api end\r\n";

        if($program_num < 5){
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
}