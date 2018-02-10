<?php
/**
 * User: rzou
 * Date: 2017/10/11
 * Time: 18:36
 */
class LinkFeed_Webgains_UK_BR
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
        $check_date = date("Y-m-d H:i:s");
        echo "\tTransfer Program by api start @ {$check_date}\r\n";

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
            $apiUrl = sprintf('http://api03.brandreward.com/programApi.php?site_name=Webgains_UK_BR&page=%s&pagesize=%s', $page, $pagesize);
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

                $AllowNonaffCoupon ='UNKNOWN';
                $AllowNonaffPromo = 'UNKNOWN';

                $desc = $prgm['Description'];
                if(preg_match('/Affiliates should only promote discount codes that have been provided to them through the Webgains platform./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates may ONLY advertise coupon codes that are distributed by the merchant (or AffiliRed on behalf of the merchant). Any sales registered through other coupon codes will not be considered as valid and will be canceled./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates are not allowed to promote coupon codes that have not been issued via the affiliate channel./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }
                //通用条件
                else if(preg_match('/affiliates can only use the voucher codes supplied/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Voucher sites must only promote codes that have been designated for affiliate use/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates shouldn’t post, use or feature any discount\/voucher codes from offline media sources./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/publishers on the (.)+affiliate program should only use and monetise voucher codes (.)+ This includes user generated content, this cannot be monetised without the relevant permissions./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/It is not allowed to promote vouchers that have not been communicated via the affiliate channel/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/affiliates may only promote voucher codes/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates are not to promote any voucher codes that have not been provided/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates should not display voucher\/discount codes that have been provided for use by other marketing channels./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates found to be promoting unauthorised discount codes or those issued through other marketing channels/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates are ONLY allowed to use voucher codes issued to/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates are requested not to use voucher codes/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Voucher code sites may not list false voucher codes or voucher codes not associated with the affiliate program/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Any sites found to be running voucher codes not specifically authorised/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Affiliates may not use misleading text on affiliate links	 buttons or images to imply that anything besides currently authorized affiliate deals or savings are available./',$desc)){
                    $AllowNonaffCoupon ='NO';
                    $AllowNonaffPromo = 'NO';
                }else if(preg_match('/Any discount promotion of our products by affiliates should be authorized/',$desc)){
                    $AllowNonaffCoupon ='NO';
                    $AllowNonaffPromo = 'NO';
                }else if(preg_match('/The only coupons authorized for use are those that we make directly available to you./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/All coupons must be publicly distributed coupons that are given to the affiliate./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Coupon sites may only post distributed coupons; that is coupons that are given to them or posted within the affiliate interface./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/They need to promote the coupon which we will provide them./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/Publishers may only use coupons and promotional codes that are provided through communication specifically intended for publishers in the affiliate program./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/These are the ONLY promotion codes affiliates are authorized to use in their marketing efforts/',$desc)){
                    $AllowNonaffCoupon ='NO';
                }else if(preg_match('/will review each coupon offering before allowing an affiliate to use./',$desc)){
                    $AllowNonaffCoupon ='NO';
                }

                preg_match('@[^\d]*(\d+)[^\d]*@', $prgm['CookiePeriod'], $m);
                $CookieTime = isset($m[1])? $m[1] : '';

                $supportUrl = trim($prgm['Deeplinks']) == 'Allowed' ? 'YES' : 'NO';

                $pStatusInApi = trim($prgm['PartnershipStatus']);
                $statusInAffInApi = trim($prgm['StatusInAff']);

                switch ($pStatusInApi) {
                    case 'Joined':
                        $partnership = 'Active';
                        break;
                    case 'Not joined':
                        $partnership = 'NoPartnership';
                        break;
                    case 'Pending approval':
                        $partnership = 'Pending';
                        break;
                    case 'Suspended':
                        $partnership = 'Expired';
                        break;
                    case 'Rejected':
                        $partnership = 'Declined';
                        break;
                    case 'siteclosed':
                        $partnership = 'Active';
                        break;
                    default:
                        $partnership = 'NoPartnership';
                        break;
                }

                switch ($statusInAffInApi) {
                    case 'Live':
                        $statusInAff = 'Active';
                        break;
                    case 'Notice of closure':
                        $statusInAff = 'Offline';
                        break;
                    case 'Suspended - non payment':
                        $statusInAff = 'TempOffline';
                        break;
                    case 'Suspended until further notice':
                        $statusInAff = 'TempOffline';
                        break;
                    default:
                        $statusInAff = 'Offline';
                        break;
                }

                $arr_prgm[$programID] = array(
                    "Name" => addslashes(html_entity_decode($prgm['Name'])),
                    "AffId" => $this->info["AffID"],
                    "Homepage" => addslashes($prgm['Url']),
                    "IdInAff" => $programID,
                    "TargetCountryExt" => addslashes(trim($prgm['Country'])),
                    "StatusInAffRemark" => addslashes($pStatusInApi),
                    "StatusInAff" => $statusInAff,
                    "Partnership" => $partnership,
                    "Description" => addslashes($desc),
                    "AllowNonaffCoupon" => $AllowNonaffCoupon,
                    "AllowNonaffPromo" => $AllowNonaffPromo,
                    "CategoryExt" => addslashes(trim($prgm['Category'])),
                    "CookieTime" => $CookieTime,
                    "Contacts" => addslashes(trim($prgm['ContactAccountManagerName'])) . ", Email: " . addslashes(trim($prgm['ContactAccountManagerEmail'])),
                    "SEMPolicyExt" => "PPC Policy Overview:". addslashes(trim($prgm['PPCPolicyOverview'])) . ", Keyword policy details:". addslashes(trim($prgm['KeywordPolicyDetails'])),
                    "CommissionExt" => addslashes(trim($prgm['CommissionDetails'])),
                    "LogoUrl" => addslashes(trim($prgm['LogoUrl'])),
                    "SupportDeepurl" => $supportUrl,
                    "LastUpdateTime" => $check_date,
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

        $this->checkProgramOffline($this->info["AffID"], $check_date);
    }

    function checkProgramOffline($AffId, $check_date){
        $objProgram = new transferHelper();
        $prgm = array();
        $prgm = $objProgram->getNotUpdateProgram($this->info["AffID"], $check_date);

        if(count($prgm) > 30){
            mydie("die: too many offline program (".count($prgm).").\n");
        }else{
            $objProgram->setProgramOffline($this->info["AffID"], $prgm);
            echo "\tSet (".count($prgm).") offline program.\r\n";
        }
    }
}