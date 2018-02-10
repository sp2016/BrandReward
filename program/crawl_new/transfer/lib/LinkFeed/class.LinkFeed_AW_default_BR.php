<?php
/**
 * User: rzou
 * Date: 2017/10/25
 * Time: 10:28
 */
class LinkFeed_AW_default_BR
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
            "userpass" => 'ryanzou:Meikai@12345'
        );

        $arr_prgm = array();
        $program_num = 0;

        $page = 1;
        $pagesize = 100;
        while ($page) {
            echo "Page:$page\t";
            $apiUrl = sprintf('http://api03.brandreward.com/programApi.php?site_name=AW_default_BR&page=%s&pagesize=%s', $page, $pagesize);
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

                $arr_prgm[$programID] = array(
                    "Name" => addslashes(html_entity_decode($prgm['Name'])),
                    "AffId" => $this->info["AffID"],
                    "Homepage" => addslashes($prgm['Url']),
                    "IdInAff" => $programID,
                    "CategoryExt" => addslashes(trim($prgm['Category'])),
                    "StatusInAff" => addslashes(trim($prgm['StatusInAff'])),
                    "Partnership" => addslashes(trim($prgm['PartnershipStatus'])),
                    "StatusInAffRemark" => addslashes(trim($prgm['active'])),
                    "CommissionExt" => addslashes(trim($prgm['Commission'])),
                    "TargetCountryExt" => addslashes(trim($prgm['TargetCountryExt'])),
                    "Contacts" => 'Email:' . addslashes(trim($prgm['ContactEmail'])),
                    "JoinDate" => addslashes(trim($prgm['JoinedNetworkDate'])),
                    "Description" => addslashes(trim($prgm['Description'])),
//                    "SupportDeepurl" => addslashes(trim($prgm['SupportDeepUrl'])),
                    "AllowNonaffCoupon" => addslashes(trim($prgm['AllowNonaffCoupon'])),
                    "TermAndCondition" => addslashes(trim($prgm['TermsAndConditions'])),
                    "CookieTime" => addslashes(trim($prgm['CookieTime'])),
                    "LogoUrl" => addslashes(trim($prgm['LogoUrl'])),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "DetailPage" => addslashes(trim($prgm['DetailPageUrl']))
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