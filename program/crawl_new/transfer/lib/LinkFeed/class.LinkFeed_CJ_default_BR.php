<?php
/**
 * User: rzou
 * Date: 2017/10/17
 * Time: 19:10
 */
class LinkFeed_CJ_Default_BR
{
    function __construct($site_name,$transferObj)
    {
        $this->transferObj = $transferObj;
        $this->info = $transferObj->getAffAccountSiteByName($site_name);
        $this->httpHelper = new HttpCrawler();
        print_r($this->info);
    }

    function transferDataToDb()
    {
        echo "\tTransfer Program by api start\r\n";

        $request = array(
            "AffID" => $this->info["AffID"],
            "method" => "get",
            "postdata" => "",
        );

        $arr_prgm = array();
        $program_num = 0;

        $page = 1;
        $pagesize = 100;
        while ($page) {
            echo "Page:$page\t";
            $request['userpass'] = 'ryanzou:Meikai@12345';
            $apiUrl = sprintf('http://api03.brandreward.com/programApi.php?site_name=CJ_Default_BR&page=%s&pagesize=%s', $page, $pagesize);
            $result = $this->httpHelper->GetHttpResult($apiUrl, $request);
            if ($result['code'] != 200) {
                mydie("\nRequest api faild : " . $result['error_msg']);
            }

            $data = json_decode($result['content'], true);

            if (!isset($data['data'])) {
                mydie("Can't find data from api!");
            }

            if ($page * $pagesize > $data['total']) {
                $page = false;
            }else {
                $page++;
            }

            foreach ($data['data'] as $prgm) {
                if (!isset($prgm['ProgramId'])) {
                    continue;
                }
                $programID = $prgm['ProgramId'];

                $pStatusInApi = trim($prgm['PartnershipStatus']);
                $statusInAffInApi = trim($prgm['StatusInAff']);

                $partnership = '';
                $statusInAff = '';
                switch ($pStatusInApi) {
                    case 'joined':
                        $partnership = 'Active';
                        break;
                    case 'notjoined':
                        $partnership = 'NoPartnership';
                        break;
                    default:
                        mydie("find new key word({$pStatusInApi}) for PartnershipStatus");
                        break;
                }

                switch ($statusInAffInApi) {
                    case 'Active':
                        $statusInAff = 'Active';
                        break;
                    case 'Setup':
                        $statusInAff = 'Offline';
                        break;
                    default:
                        mydie("find new key word({$statusInAffInApi}) for StatusInAff");
                        break;
                }

                $mobileFriendly = 'UNKNOWN';
                if ($prgm['MobileFriendly'] == 'true') {
                    $mobileFriendly = 'YES';
                } else if ($prgm['MobileFriendly'] == 'false') {
                    $mobileFriendly = 'NO';
                }

                $TermAndCondition = '';
                $AllowNonaffPromo = 'UNKNOWN';
                $AllowNonaffCoupon = 'UNKNOWN';
                $str_allownonaff = 'Publishers may use any coupons or promotional codes that are provided through the affiliate program or otherwise available to the public';
                $str_notallownonaff = 'Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program';
                if(!empty($prgm['Policies'])){
                    $polieies_arr = explode(' # ', $prgm['Policies']);
                    foreach($polieies_arr as $tmp_policy){
                        list($p_t_Id,$p_t_Title,$p_t_text) = explode('::', $tmp_policy);
                        $TermAndCondition .= '<b>' . $p_t_Title . '</b><br />&nbsp;&nbsp;&nbsp;&nbsp;'. $p_t_text . '<br /><br />';
                        if ($p_t_Id == "coupons_and_promotional_codes"){
                            if (strstr($p_t_text, $str_allownonaff)){
                                $AllowNonaffPromo = 'YES';
                                $AllowNonaffCoupon = 'YES';
                            }
                            if (strstr($p_t_text, $str_notallownonaff)){
                                $AllowNonaffCoupon = 'NO';
                            }
                        }
                    }
                }

                $arr_prgm[$programID] = array(
                    "Name" => addslashes(html_entity_decode($prgm['Name'])),
                    "AffId" => $this->info["AffID"],
                    "Homepage" => addslashes($prgm['Url']),
                    "IdInAff" => $programID,
                    "CategoryExt" => addslashes(trim($prgm['Category'])),
                    "StatusInAff" => $statusInAff,
                    "Partnership" => $partnership,
                    "CommissionExt" => addslashes(trim($prgm['CommissionDetails'])),
                    "TargetCountryExt" => addslashes(trim($prgm['ServiceableCountry'])),
                    "Contacts" => 'Contact: ' . addslashes(trim($prgm['ContactPerson'])) . "; Email: " . addslashes(trim($prgm['ContactEmail'])),
                    "RankInAff" => addslashes($prgm['RankInAff']),
                    "JoinDate" => addslashes($prgm['JoinedNetworkDate']),
                    "StatusInAffRemark" => addslashes($pStatusInApi),
                    "Description" => addslashes($prgm['Description']),
                    "EPCDefault" => addslashes($prgm['EPC7day']),
                    "EPC90d" => addslashes($prgm['EPC3month']),
                    "MobileFriendly" => $mobileFriendly,
                    "AllowNonaffCoupon" => $AllowNonaffCoupon,
                    "AllowNonaffPromo" => $AllowNonaffPromo,
                    "TermAndCondition" => addslashes($TermAndCondition),
                    "SupportDeepUrl" => addslashes($prgm['SupportDeepUrl']),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                );
                $program_num ++;

                if(count($arr_prgm) >= 100){
                    $this->transferObj->updateProgram($this->info["AffID"], $arr_prgm);
                    $arr_prgm = array();
                }
            }

        }
        if(count($arr_prgm) >0){
            $this->transferObj->updateProgram($this->info["AffID"], $arr_prgm);
            unset($arr_prgm);
        }

        echo "\n\tTransfer Program by api end\r\n";
    }


}