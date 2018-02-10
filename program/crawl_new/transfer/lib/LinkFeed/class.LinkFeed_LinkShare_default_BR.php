<?php
/**
 * User: rzou
 * Date: 2017/10/19
 * Time: 15:08
 */
class LinkFeed_LinkShare_default_BR
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
            "cookiejar" => ''
        );

        $arr_prgm = array();
        $program_num = 0;

        $page = 1;
        $pagesize = 100;
        while ($page) {
            echo "Page:$page\t";
            $request['userpass'] = 'ryanzou:Meikai@12345';
            $apiUrl = sprintf('http://api03.brandreward.com/programApi.php?site_name=LinkShare_default_BR&page=%s&pagesize=%s', $page, $pagesize);
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

                $StatusInAff = $Partnership = '';
                $StatusInAffRemark = trim($prgm['PartnershipStatus']);
                if(stripos($StatusInAffRemark,"Approved") !== false){
                    $StatusInAff = "Active";
                    $Partnership = "Active";
                }elseif(stripos($StatusInAffRemark,"Declined") !== false){
                    $StatusInAff = "Active";
                    $Partnership = "Declined";
                }elseif(stripos($StatusInAffRemark,"Removed") !== false){
                    $StatusInAff = "Active";
                    $Partnership = "Removed";
                }elseif(stripos($StatusInAffRemark,"Pending") !== false){
                    $StatusInAff = "Active";
                    $Partnership = "Pending";
                }elseif(stripos($StatusInAffRemark,"No Relationship") !== false){
                    $StatusInAff = "Active";
                    $Partnership = "NoPartnership";
                }else{
                    mydie("Find wrong status:($StatusInAffRemark) in api!");
                }

                $Contact_Name = addslashes(trim($prgm['ContactPerson']));
                $Contact_Title = addslashes(trim($prgm['ContactTitle']));
                $Contact_Email = addslashes(trim($prgm['ContactEmail']));
                $Contact_Phone = addslashes(trim($prgm['ContactPhone']));
                $Contact_Address = addslashes(trim($prgm['ContactCompany']));

                $Contacts = "$Contact_Name($Contact_Title), Email: $Contact_Email, Phone: $Contact_Phone, Address: $Contact_Address.";


                $arr_prgm[$programID] = array(
                    "Name" => addslashes(html_entity_decode($prgm['Name'])),
                    "AffId" => $this->info["AffID"],
                    "Homepage" => addslashes($prgm['Url']),
                    "IdInAff" => $programID,
                    "CategoryExt" => addslashes(trim($prgm['Category'])),
                    "StatusInAff" => $StatusInAff,
                    "Partnership" => $Partnership,
                    "CommissionExt" => addslashes(trim($prgm['Commission'])),
                    "TargetCountryExt" => addslashes(trim($prgm['TargetCountryExt'])),
                    "Contacts" => addslashes($Contacts),
                    "JoinDate" => addslashes(trim($prgm['JoinedNetworkDate'])),
                    "StatusInAffRemark" => addslashes($StatusInAffRemark),
                    "Description" => addslashes(trim($prgm['Description'])),
                    "SupportDeepurl" => addslashes(trim($prgm['SupportDeepUrl'])),
                    "AllowNonaffCoupon" => addslashes(trim($prgm['AllowNonaffCoupon'])),
                    "TermAndCondition" => addslashes(trim($prgm['TermAndCondition'])),
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