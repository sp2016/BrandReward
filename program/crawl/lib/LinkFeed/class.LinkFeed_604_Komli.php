<?php
include_once('xml2array.php');
class LinkFeed_604_Komli {

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
        echo "LinkFeed @start\n\r";
        $start = date("Y-m-d H:i:s");
        //login
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "post",
            "postdata" => "u=info%40couponsnapshot.com&p=cu30Nkd3DB",
            "maxredirs" => 4,
        );
        $request['addheader'] = array("Host:login.komli.com", "Origin:http://login.komli.com", "Referer:http://login.komli.com/", "Upgrade-Insecure-Requests:1");
        $post_url = "http://login.komli.com/login.ashx?tp=1";
        $info = $this->oLinkFeed->GetHttpResult($post_url, $request);
        //login success
        if (stripos($info['content'], "Logout") !== false) {
            echo "login succ\n\r";

            //getData
            $post_url = "http://login.komli.com/affiliates/Extjs.ashx?s=contracts";
            $request = array(
                "AffId" => $this->info['AffId'],
                "method" => "post",
                "postdata" => "groupBy=&groupDir=ASC&cu=0&c=&cat=0&sv=&cn=&pf=&st=0&m=&ct=&pmin=&pmax=&mycurr=false&p=0&n=9999",
                "maxredirs" => 4,
            );
            $json_info = $this->oLinkFeed->GetHttpResult($post_url, $request);
            $json_info = json_decode($json_info['content'], true);
            $xml = new XML2Array();
            $list = array();
            foreach ($json_info['rows'] as $campaign) {
                if (empty($campaign['contract_id'])) {
                    continue;
                }
                //getDetailData BY api
                $url = "http://login.komli.com/affiliates/api/2/offers.asmx/GetCampaign?api_key=QrWo88qaPQiDgiajccYZA&affiliate_id=10616&campaign_id=" . $campaign['contract_id'];
                $result = $this->oLinkFeed->GetHttpResult($url);
                $result = $xml->createArray($result['content']);
                //SUCCESS
                if ($result['campaign_response']['success'] == "true" && isset($result['campaign_response']['campaign'])) {
                    $result = $result['campaign_response']['campaign'];
                    //AffId
                    $list[$campaign['id']]['AffId'] = $this->info['AffId'];
                    //IdInAff
                    $list[$campaign['id']]['IdInAff'] = $campaign['id'];
                    //Name
                    $list[$campaign['id']]['Name'] = addslashes($result['offer_name']);
                    //Description
                    $list[$campaign['id']]['Description'] = addslashes($result['description']);
                    $list[$campaign['id']]['Description'] = str_replace("₹","RU",$list[$campaign['id']]['Description']);
                    //CommissionExt
                    $list[$campaign['id']]['CommissionExt'] = $result['price_format'] . " " . $result['payout'];
                    $list[$campaign['id']]['CommissionExt'] = str_replace("₹","RU",$list[$campaign['id']]['CommissionExt']);
                    //TargetCountryExt
                    $list[$campaign['id']]['TargetCountryExt'] = "India";
                    //Homepage
                    if (isset($result['creatives']['creative_info'][0]['unique_link']) && !empty($result['creatives']['creative_info'][0]['unique_link'])) {
                        $homepage = @get_headers($result['creatives']['creative_info'][0]['unique_link'], true);
                        if (is_array($homepage['Location']) && !empty($homepage['Location'])) {
                            $homepage = addslashes(end($homepage['Location']));
                        } else {
                            $homepage = addslashes($result['creatives']['creative_info'][0]['unique_link']);
                        }
                    } else {
                        $homepage = "";
                    }
                    $list[$campaign['id']]['Homepage'] = $homepage;

                    //status_name
                    if (stripos($result['status_name'], "active") !== false) {
                        $list[$campaign['id']]['Partnership'] = "Active";
                    } elseif (stripos($result['status_name'], "public") !== false) {
                        $list[$campaign['id']]['Partnership'] = "NoPartnership";
                    } elseif (stripos($result['status_name'], "Apply") !== false) {
                        $list[$campaign['id']]['Partnership'] = "NoPartnership";
                    } elseif (stripos($result['status_name'], "pending") !== false) {
                        $list[$campaign['id']]['Partnership'] = "Pending";
                    } else {
                        mydie("Unknown status => {$result['status_name']}!\n\r");
                    }

                    //StatusInAff
                    $list[$campaign['id']]['StatusInAff'] = "Active";

                }
            }
            $DB = new ProgramDb();
            $DB->updateProgram($this->info['AffId'], $list);
            $this->checkProgramOffline($this->info['AffId'],$start);
            echo "LinkFeed @end\n\r";
        }
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