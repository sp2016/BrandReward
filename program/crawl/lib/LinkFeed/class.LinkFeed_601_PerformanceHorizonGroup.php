<?php

class LinkFeed_601_PerformanceHorizonGroup
{

    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $oLinkFeed = new LinkFeed();
        $this->info = $oLinkFeed->getAffById($aff_id);
        if (!isset($this->info) || empty($this->info)) {
            $this->info = $oLinkFeed->getAffById($aff_id);
        }
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
    }

    public function GetProgramFromAff()
    {
        echo "affiliate 601 @start\n\r";
        $start = date("Y-m-d H:i:s");
        $three_status = array(
            "approved" => "https://p3tew145y3tag41n:QgHB8VMI@api.performancehorizon.com/user/publisher/305556/campaign/approved.json",
            "pending" => "https://p3tew145y3tag41n:QgHB8VMI@api.performancehorizon.com/user/publisher/305556/campaign/pending.json",
            "rejected" => "https://p3tew145y3tag41n:QgHB8VMI@api.performancehorizon.com/user/publisher/305556/campaign/rejected.json",
        );
        $list = array();
        foreach ($three_status as $status => $url) {
            $info = $this->oLinkFeed->GetHttpResult($url);
            $info = json_decode($info['content'], true);
            foreach ($info['campaigns'] as $data) {
                //AffId
                $list[$data['campaign']['campaign_id']]['AffId'] = $this->info['AffId'];
                //IdInAff
                $list[$data['campaign']['campaign_id']]['IdInAff'] = $data['campaign']['campaign_id'];
                //Homepage&AffDefaultUrl
                $list[$data['campaign']['campaign_id']]['Homepage'] = addslashes($data['campaign']['destination_url']);
                $list[$data['campaign']['campaign_id']]['AffDefaultUrl'] = addslashes($data['campaign']['destination_url']);
                //Name
                $list[$data['campaign']['campaign_id']]['Name'] = addslashes(trim($data['campaign']['title']));
                //CommissionExt
                $list[$data['campaign']['campaign_id']]['CommissionExt'] = implode("|", $data['campaign']['commissions']);
                if (!empty($list[$data['campaign']['campaign_id']]['CommissionExt'])) {
                    $list[$data['campaign']['campaign_id']]['CommissionExt'] = addslashes($list[$data['campaign']['campaign_id']]['CommissionExt']);
                }
                //TermAndCondition
                if (isset($data['campaign']['terms']) && !empty($data['campaign']['terms'])) {
                    foreach ($data['campaign']['terms'] as $key => $value) {
                        $list[$data['campaign']['campaign_id']]['TermAndCondition'][] = $key . ":<br>" . $value['terms'];
                    }
                    if ($list[$data['campaign']['campaign_id']]['TermAndCondition']) {
                        $list[$data['campaign']['campaign_id']]['TermAndCondition'] = implode("|||", $list[$data['campaign']['campaign_id']]['TermAndCondition']);
                        $list[$data['campaign']['campaign_id']]['TermAndCondition'] = addslashes($list[$data['campaign']['campaign_id']]['TermAndCondition']);
                    }
                } else {
                    $list[$data['campaign']['campaign_id']]['TermAndCondition'] = "";
                }
                //SupportDeepUrl
                if ($data['campaign']['allow_deep_linking'] == "y") $list[$data['campaign']['campaign_id']]['SupportDeepUrl'] = 'YES';
                elseif ($data['campaign']['allow_deep_linking'] == "n") $list[$data['campaign']['campaign_id']]['SupportDeepUrl'] = 'NO';
                else $list[$data['campaign']['campaign_id']]['SupportDeepUrl'] = 'UNKNOWN';
                //SecondIdInAff    => advertiser_id
                $list[$data['campaign']['campaign_id']]['SecondIdInAff'] = $data['campaign']['advertiser_id'];
                //Description
                if (!empty($data['campaign']['description'])) {
                    foreach ($data['campaign']['description'] as $key => $value) {
                        $list[$data['campaign']['campaign_id']]['Description'][] = addslashes($value);
                    }
                }
                if (isset($list[$data['campaign']['campaign_id']]['Description']) && !empty($list[$data['campaign']['campaign_id']]['Description'])) {
                    $list[$data['campaign']['campaign_id']]['Description'] = implode("|", $list[$data['campaign']['campaign_id']]['Description']);
                } else {
                    $list[$data['campaign']['campaign_id']]['Description'] = "";
                }
                //partnership
                if ($status == "approved") {
                    $list[$data['campaign']['campaign_id']]['Partnership'] = 'Active';
                } elseif ($status == "pending") {
                    $list[$data['campaign']['campaign_id']]['Partnership'] = 'Pending';
                } elseif ($status == "rejected") {
                    $list[$data['campaign']['campaign_id']]['Partnership'] = 'Declined';
                } else {
                    $list[$data['campaign']['campaign_id']]['Partnership'] = 'NoPartnership';
                }
                //StatusInAff
                if ($data['campaign']['status'] == "a") {
                    $list[$data['campaign']['campaign_id']]['StatusInAff'] = 'Active';
                } elseif ($data['campaign']['status'] == "r" || stripos($list[$data['campaign']['campaign_id']]['Name'],"retired")!==false) {
                    $list[$data['campaign']['campaign_id']]['StatusInAff'] = 'Offline';
                } else {
                    mydie("new status(StatusInAff) => {$data['campaign']['status']} apprear");
                }
            }
        }

        $db = new ProgramDb();
        $db->updateProgram($this->info['AffId'], $list);
        $this->checkProgramOffline($this->info['AffId'], $start);
        echo "affiliate 601 @end\n\r";
    }


    function checkProgramOffline($AffId, $check_date)
    {
        $prgm = array();
        $DB = new ProgramDb();
        $prgm = $DB->getNotUpdateProgram($AffId, $check_date);
        if (count($prgm) > 30) {
            mydie("die: too many offline program (" . count($prgm) . ").\n");
        } else {
            $DB->setProgramOffline($AffId, $prgm);
            echo "\tSet (" . count($prgm) . ") offline program.\r\n";
        }
    }

}