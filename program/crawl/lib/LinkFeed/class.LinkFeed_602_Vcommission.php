<?php

/**
 * Created by PhpStorm.
 * User: felix
 * Date: 2016/1/13
 * Time: 13:46
 */
class LinkFeed_602_Vcommission
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

    function GetProgramFromAff()
    {
        $total = 0;
        $start = date("Y-m-d H:i:s");
        echo "linkfeed start: {$start} \n\r";
        $LF = new LinkFeed();
        //get allOffer
        $url = "https://api.hasoffers.com/Apiv3/json?NetworkId=vcm&Target=Affiliate_Offer&Method=findAll&api_key=c3307506180291b31b7fd68619579ecc5da9454acebbf551b53843eb7c813aa4";
        $info = $LF->GetHttpResult($url);
        $info = json_decode($info['content'], true);
        $info = $info['response']['data'];
        //get myOffer
        $myurl = "https://api.hasoffers.com/Apiv3/json?NetworkId=vcm&Target=Affiliate_Offer&Method=findMyOffers&api_key=c3307506180291b31b7fd68619579ecc5da9454acebbf551b53843eb7c813aa4&fields%5B%5D=id";
        $myinfo = $LF->GetHttpResult($myurl);
        $myinfo = json_decode($myinfo['content'], true);
        $myinfo = $myinfo['response']['data'];

        if (empty($info))
            mydie("there is no AllOffer data");

        foreach ($info as $k => &$value) {

            //get AffDefaultUrl
            $default_url = "https://api.hasoffers.com/Apiv3/json?NetworkId=vcm&Target=Affiliate_Offer&Method=generateTrackingLink&api_key=c3307506180291b31b7fd68619579ecc5da9454acebbf551b53843eb7c813aa4&offer_id=" . $value['Offer']['id'];
            $default_info = $LF->GetHttpResult($default_url);
            $default_info = json_decode($default_info['content'], true);
            isset($default_info['response']['data']['click_url']) ? $value['AffDefaultUrl'] = addslashes($default_info['response']['data']['click_url']) : $value['AffDefaultUrl'] = '';

            //TargetCountry
            $countries_url = "https://api.hasoffers.com/Apiv3/json?NetworkId=vcm&Target=Affiliate_Offer&Method=getTargetCountries&api_key=c3307506180291b31b7fd68619579ecc5da9454acebbf551b53843eb7c813aa4&ids%5B%5D=" . $value['Offer']['id'];
            $countries_info = $LF->GetHttpResult($countries_url);
            $countries_info = json_decode($countries_info['content'], true);
            if ($countries_info['response']['status'] == 1) {
                foreach ($countries_info['response']['data'][0]['countries'] as $val) {
                    $value['TargetCountryExt'][] = $val['name'];
                }
                if (!empty($value['TargetCountryExt'])) {
                    $value['TargetCountryExt'] = addslashes(implode("|", $value['TargetCountryExt']));
                } else {
                    $value['TargetCountryExt'] = '';
                }
            } else {
                $value['TargetCountryExt'] = '';
            }

            //CategoryExt
            $category_url = "https://api.hasoffers.com/Apiv3/json?NetworkId=vcm&Target=Affiliate_Offer&Method=getCategories&api_key=c3307506180291b31b7fd68619579ecc5da9454acebbf551b53843eb7c813aa4&ids%5B%5D=" . $value['Offer']['id'];
            $category_info = $LF->GetHttpResult($category_url);
            $category_info = json_decode($category_info['content'], true);
            if ($category_info['response']['status'] == 1) {
                foreach ($category_info['response']['data'][0]['categories'] as $val) {
                    $value['CategoryExt'][] = $val['name'];
                }
                if (!empty($value['CategoryExt'])) {
                    $value['CategoryExt'] = addslashes(implode("|", $value['CategoryExt']));
                } else {
                    $value['CategoryExt'] = '';
                }
            } else {
                $value['CategoryExt'] = '';
            }

            //info
            $value['AffId'] = $this->info['AffId'];
            $value['IdInAff'] = $value['Offer']['id'];
            $value['Name'] = addslashes($value['Offer']['name']);
            $value['Description'] = addslashes($value['Offer']['description']);
            $value['TermAndCondition'] = addslashes($value['Offer']['terms_and_conditions']);
            $value['Homepage'] = addslashes($value['Offer']['preview_url']);
            if ($value['Offer']['status'] == "active") {
                $value['StatusInAff'] = "Active";
            } else {
                mydie("new status(StatusInAff) in affiliate appear,need more code\n\r");
            }
            $value['Partnership'] = "NoPartnership";
            $value['CommissionExt'] = addslashes("payout:" . $value['Offer']['currency'] . $value['Offer']['default_payout'] . " AND " . $value['Offer']['percent_payout'] . "%");
            if (isset($value['Offer']['payout_type']) && !empty($value['Offer']['payout_type'])) {
                $value['CommissionExt'] = addslashes("payout type:" . $value['Offer']['payout_type'] . "; " . $value['CommissionExt']);
            }
            unset($value['Offer']);
            $total++;
        }

        foreach ($myinfo as $key => $data) {
            if (isset($info[$key]) && !empty($info[$key])) {
                $info[$key]['Partnership'] = "Active";
            }
        }
        $end = date("Y-m-d H:i:s");
        $DB = new ProgramDb();
        echo "total: {$total} Offer\n\r";
        $DB->updateProgram($this->info['AffId'], $info);
        $this->checkProgramOffline($this->info['AffId'], $start);
        echo "linkfeed end {$end}\n\r";
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