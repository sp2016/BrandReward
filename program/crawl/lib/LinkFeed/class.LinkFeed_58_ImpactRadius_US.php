<?php
require_once 'text_parse_helper.php';
class LinkFeed_58_ImpactRadius_US
{
    var $info = array(
        "ID" => "58",
        "Name" => "ImpactRadius US",
        "IsActive" => "YES",
        "ClassName" => "LinkFeed_58_ImpactRadius_US",
        "LastCheckDate" => "1970-01-01",
    );

    function __construct($aff_id, $oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->file = "programlog_{$aff_id}_" . date("Ymd_His") . ".csv";
        $this->getStatus = false;

        if (SID == 'bdg02') {
            define('API_SID_58', 'IRYrCKQmWhbn245060XeRFfN3HQ8QboRi1');
            define('API_TOKEN_58', 'vN3jiEFiYDrJ6rV7GSFcDk9dcwfgGyKE');
            define('AFFID_INAFF_58', '245060');
        } else {
            define('API_SID_58', 'IRAk3sc2TJdK344780PF4JSkd5YxAZ2Tb1');
            define('API_TOKEN_58', 'PwzuaWSCjGiiaEPqw3uivLmPp3g%40%23Axe');
            define('AFFID_INAFF_58', '344780');
        }

    }

    function getCouponFeed()
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        return $arr_return;
    }

    function GetAllLinksByAffId()
    {
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);

        // coupon by api
        $AccountSid = API_SID_58;
        $AccountToken = API_TOKEN_58;
        $base_url = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com";
        $nextPage = null;
        $pages = 1;
        do {
            if (empty($nextPage))
                $url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/PromoAds.json?";
            else
                $url = $base_url . $nextPage;
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            if (empty($r) || $r['code'] != 200 || empty($r['content']))
                break;
            $r = json_decode($r['content'], true);
            if (empty($r) || !is_array($r) || empty($r['PromotionalAds']))
                break;
            $nextPage = $r['@nextpageuri'];
            $totalPage = $r['@numpages'];
            $data = $r['PromotionalAds'];
            $links = array();
            if (!empty($data) && is_array($data)) {
                if (!empty($data['Id']) && (int)$data['Id']) // only one record.
                    $data = array($data);
                foreach ($data as $v) {
                    if ($v['Status'] == 'DEACTIVATED')
                        continue;
                    $link = array(
                        "AffId" => $this->info["AffId"],
                        "AffMerchantId" => $v['CampaignId'],
                        "AffLinkId" => $v['Id'],
                        "LinkName" => $v['LinkText'],
                        "LinkDesc" => '',
                        "LinkStartDate" => parse_time_str($v['StartDate'], null, false),
                        "LinkEndDate" => parse_time_str($v['EndDate'], null, true),
                        "LinkPromoType" => 'N/A',
                        "LinkHtmlCode" => '',
                        "LinkOriginalUrl" => "",
                        "LinkImageUrl" => $v['ProductImageUrl'],
                        "LinkAffUrl" => $v['TrackingLink'],
                        "DataSource" => "62",
                        "IsDeepLink" => 'UNKNOWN',
                        "Type" => 'promotion'
                    );
                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                    if ($v['PromoType'] == 'FREESHIPPING')
                        $link['LinkPromoType'] = 'free shipping';
                    if (!empty($v['PromoCode'])) {
                        $link['LinkCode'] = $v['PromoCode'];
                        $link['LinkPromoType'] = 'coupon';
                    }

                    $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
                    if (!empty($code)) {
                        $link['LinkPromoType'] = 'COUPON';
                        $link['LinkCode'] = $code;
                    }

                    if (!empty($v['DiscountClassification'])) {
                        if (!empty($v['DiscountClassificationDetail']))
                            $link['LinkDesc'] .= sprintf('Discount Classification: %s, %s, ', ucwords(strtolower($v['DiscountClassification'])), $v['DiscountClassificationDetail']);
                        else
                            $link['LinkDesc'] .= sprintf('Discount Classification: %s, ', ucwords(strtolower($v['DiscountClassification'])));
                    }
                    if (!empty($v['DiscountAmount']))
                        $link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
                    if (!empty($v['DiscountPercent']))
                        $link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
                    if (!empty($v['AdHtml']))
                        $link['LinkHtmlCode'] .= str_replace('</h3>', '', str_replace('<h3>', '', $v['AdHtml']));
                    if (empty($link['AffLinkId']) || empty($link['LinkName']) || $link['LinkName'] == '')
                        continue;

                    $links[] = $link;
                    $arr_return['AffectedCount']++;
                }
            }
            echo sprintf("get coupon by api...%s link(s) found.\n", count($links));
            sleep(1);
            if (count($links) > 0)
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
            $pages++;
        } while (!empty($nextPage) && ($pages <= $totalPage));

        // text/banner by api
        $nextPage = null;
        $pages = 1;
        do {
            if (empty($nextPage))
                $url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/Ads.json?";
            else
                $url = $base_url . $nextPage;
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            if (empty($r) || $r['code'] != 200 || empty($r['content']))
                break;
            $r = json_decode($r['content'], true);
            if (empty($r) || !is_array($r) || empty($r['Ads']))
                break;
            $nextPage = $r['@nextpageuri'];
            $totalPage = $r['@numpages'];
            $data = $r['Ads'];
            $links = array();
            if (!empty($data) && is_array($data)) {
                if (!empty($data['Id']) && (int)$data['Id']) // only one record.
                    $data = array($data);
                foreach ($data as $v) {
                    $link = array(
                        "AffId" => $this->info["AffId"],
                        "AffMerchantId" => $v['CampaignId'],
                        "AffLinkId" => $v['Id'],
                        "LinkName" => $v['Name'],
                        "LinkDesc" => $v['Description'],
                        "LinkStartDate" => parse_time_str($v['StartDate'], null, false),
                        "LinkEndDate" => parse_time_str($v['EndDate'], null, true),
                        "LinkPromoType" => 'N/A',
                        "LinkHtmlCode" => '',
                        "LinkOriginalUrl" => $v['LandingPageUrl'],
                        "LinkImageUrl" => '',
                        "LinkAffUrl" => $v['TrackingLink'],
                        "DataSource" => "62",
                        "IsDeepLink" => 'UNKNOWN',
                        "Type" => 'link'
                    );
                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                    if (!empty($v['DealDefaultPromoCode'])) {
                        $link['LinkCode'] = $v['DealDefaultPromoCode'];
                        $link['LinkPromoType'] = 'coupon';
                    }

                    if (!empty($v['Labels'])) {
                        $link['LinkDesc'] .= sprintf('Labels: %s, ', ucwords(strtolower($v['Labels'])));
                    }
                    if (!empty($v['DiscountAmount']))
                        $link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
                    if (!empty($v['DiscountPercent']))
                        $link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
                    if (!empty($v['Code']))
                        $link['LinkHtmlCode'] .= str_replace('</h3>', '', str_replace('<h3>', '', $v['Code']));
                    if (empty($link['AffLinkId']) || empty($link['LinkName']) || $link['LinkName'] == '')
                        continue;

                    $links[] = $link;
                    $arr_return['AffectedCount']++;
                }
            }
            echo sprintf("get link by api...%s link(s) found.\n", count($links));
            sleep(1);
            if (count($links) > 0)
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
            $pages++;
        } while (!empty($nextPage) && ($pages <= $totalPage));

        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, '');
        return $arr_return;
    }

    function GetAllLinksFromAffByMerID($merinfo)
    {

        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);

        // coupon by api
        $AccountSid = API_SID_58;
        $AccountToken = API_TOKEN_58;
        $base_url = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com";
        $nextPage = null;
        $pages = 1;
        do {
            if (empty($nextPage))
                $url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/PromoAds.json?CampaignId={$merinfo['IdInAff']}";
            else
                $url = $base_url . $nextPage;
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            if (empty($r) || $r['code'] != 200 || empty($r['content']))
                break;
            $r = json_decode($r['content'], true);
            if (empty($r) || !is_array($r) || empty($r['PromotionalAds']))
                break;
            $nextPage = $r['@nextpageuri'];
            $data = $r['PromotionalAds'];
            $links = array();
            if (!empty($data) && is_array($data)) {
                if (!empty($data['Id']) && (int)$data['Id']) // only one record.
                    $data = array($data);
                foreach ($data as $v) {
                    if ($v['Status'] == 'DEACTIVATED')
                        continue;
                    $link = array(
                        "AffId" => $this->info["AffId"],
                        "AffMerchantId" => $v['CampaignId'],
                        "AffLinkId" => $v['Id'],
                        "LinkName" => $v['LinkText'],
                        "LinkDesc" => '',
                        "LinkStartDate" => parse_time_str($v['StartDate'], null, false),
                        "LinkEndDate" => parse_time_str($v['EndDate'], null, true),
                        "LinkPromoType" => 'N/A',
                        "LinkHtmlCode" => '',
                        "LinkOriginalUrl" => "",
                        "LinkImageUrl" => $v['ProductImageUrl'],
                        "LinkAffUrl" => $v['TrackingLink'],
                        "DataSource" => "62",
                        "IsDeepLink" => 'UNKNOWN',
                        "Type" => 'promotion'
                    );
                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                    if ($v['PromoType'] == 'FREESHIPPING')
                        $link['LinkPromoType'] = 'free shipping';
                    if (!empty($v['PromoCode'])) {
                        $link['LinkCode'] = $v['PromoCode'];
                        $link['LinkPromoType'] = 'coupon';
                    }

                    $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
                    if (!empty($code)) {
                        $link['LinkPromoType'] = 'COUPON';
                        $link['LinkCode'] = $code;
                    }

                    if (!empty($v['DiscountClassification'])) {
                        if (!empty($v['DiscountClassificationDetail']))
                            $link['LinkDesc'] .= sprintf('Discount Classification: %s, %s, ', ucwords(strtolower($v['DiscountClassification'])), $v['DiscountClassificationDetail']);
                        else
                            $link['LinkDesc'] .= sprintf('Discount Classification: %s, ', ucwords(strtolower($v['DiscountClassification'])));
                    }
                    if (!empty($v['DiscountAmount']))
                        $link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
                    if (!empty($v['DiscountPercent']))
                        $link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
                    if (!empty($v['AdHtml']))
                        $link['LinkHtmlCode'] .= str_replace('</h3>', '', str_replace('<h3>', '', $v['AdHtml']));
                    if (empty($link['AffLinkId']) || empty($link['LinkName']) || $link['LinkName'] == '')
                        continue;

                    $links[] = $link;
                    $arr_return['AffectedCount']++;
                }
            }
            echo sprintf("get coupon by api...%s link(s) found.\n", count($links));
            sleep(1);
            if (count($links) > 0)
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
            $pages++;
        } while (!empty($nextPage) && ($pages < 100));

        // coupon, text & banner by page
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
        $adTypes = array('COUPON', 'TEXT_LINK', 'BANNER');

        foreach ($adTypes as $adType) {

            $links = array();
            //get cookies
            $url = 'https://member.impactradius.com/secure/mediapartner/ads/searchAdsDirectoryMP.ihtml';
            $r = $this->oLinkFeed->GetHttpResult($url, $request);

            $startIndex = 0;
            $pageSize = 150;

            //get total number
            $url = sprintf('https://member.impactradius.com/secure/nositemesh/campaigns/searchAdsDirectoryMPJSON.ihtml?adSubType=ALL&statsPeriod=&season=&deal=ALL&language=&mobileReady=&deepLinking=&searchString=&campaign=%s&adType=%s&dealType=ALL&tableId=529&page=1&startIndex=%s&pageSize=%s', $merinfo['IdInAff'], $adType, $startIndex, $pageSize);
            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            $r = json_decode($r['content'], true);
            $count = isset($r['totalCount']) ? $r['totalCount'] : 0;
            if (!$count) continue;

            //get content pages
            while ($startIndex <= $count) {
                $url = sprintf('https://member.impactradius.com/secure/nositemesh/campaigns/searchAdsDirectoryMPJSON.ihtml?adSubType=ALL&statsPeriod=&season=&deal=ALL&language=&mobileReady=&deepLinking=&searchString=&campaign=%s&adType=%s&dealType=ALL&tableId=529&page=1&startIndex=%s&pageSize=%s', $merinfo['IdInAff'], $adType, $startIndex, $pageSize);
                $r = $this->oLinkFeed->GetHttpResult($url, $request);
                $r = json_decode($r['content'], true);
                foreach ($r['records'] as $row) {


                    $detail_url = sprintf('https://member.impactradius.com/secure/directory/mediapartner-gethtml-flow.ihtml?adType=%s&adId=%s&campaignId=%s&d=lightbox', $adType, $row['bulkActionCol']['dv'], $merinfo['IdInAff']);
                    $r = $this->oLinkFeed->GetHttpResult($detail_url, $request);
                    $r = $r['content'];
                    //echo $detail_url."\r\n";
//					if($row['bulkActionCol']['dv'] == 294260){
//						print_r($r);
//						echo $detail_url;die;
//					}
                    $link = array(
                        "AffId" => $this->info["AffId"],
                        "AffMerchantId" => $merinfo['IdInAff'],
                        "AffLinkId" => $row['bulkActionCol']['dv'],
                        "LinkName" => '',
                        "LinkDesc" => '',
                        "LinkStartDate" => '0000-00-00',
                        "LinkEndDate" => '0000-00-00',
                        "LinkPromoType" => 'N/A',
                        "LinkHtmlCode" => '',
                        "LinkOriginalUrl" => '',
                        "LinkImageUrl" => '',
                        "LinkAffUrl" => '',
                        "DataSource" => "62",
                        "IsDeepLink" => 'UNKNOWN',
                        "Type" => 'link'
                    );

                    $code_url = sprintf('https://member.impactradius.com/nositemesh/directory/mediapartner/listads/genhtml.ihtml?adid=%s&cid=%s&mpid=%s', $link['AffLinkId'], $link['AffMerchantId'], AFFID_INAFF_58);
                    $code_detail = $this->oLinkFeed->GetHttpResult($code_url, $request);
                    $code_detail = $code_detail['content'];
                    preg_match('/class="adNameTitle">(.*?)<\/span>/', $row['name']['dv'], $linkName);
                    if (isset($linkName[1]) && $linkName[1])
                        $link['LinkName'] = $linkName[1];

                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                    if (preg_match('/Description[\s\S]*?uitkColapsibleText[\S\s]*?>([\s\S]*?)<\/span>/', $r, $desc)) {
                        $link['LinkDesc'] = trim($desc[1]);
                    }


                    if (preg_match('@<textarea.*?>(.*?)</textarea>@ms', $code_detail, $g)) {
                        $link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
                        if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
                            $link['LinkAffUrl'] = "http:" . $g[1];
                    }
                    if ($adType == 'BANNER') {
                        if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
                            $link['LinkImageUrl'] = "http:" . $g[1];
                    }
                    if ($adType == 'COUPON') {
                        $link['LinkCode'] = $this->get_linkcode_by_text_58($link['LinkName']);
                        if (empty($link['LinkCode']))
                            $link['LinkCode'] = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
                        if (empty($link['LinkCode'])) {
                            if (preg_match('/Code:<\/span>(.*?)<\/div>/', $r, $coupon_code)) {
                                $link['LinkCode'] = trim($coupon_code[1]);
                                $link['Type'] = 'promotion';
                            }
                        }
                        $link['LinkPromoType'] = 'COUPON';
                    }

                    if ($adType == 'TEXT_LINK') {
                        $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
                        $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
                        if (!empty($code)) {
                            $link['LinkPromoType'] = 'COUPON';
                            $link['LinkCode'] = $code;
                            $link['Type'] = 'promotion';
                        }
                    }

                    if (preg_match('/Dates Active:<\/span>(.*?)-(.*?),(.*?)<\/div>/', $r, $datearea)) {
                        $tmp_year = (date('Y-m-d', strtotime($datearea[1])) > date('Y-m-d', strtotime($datearea[2])) ? $datearea[3] - 1 : $datearea[3]);
                        $link['LinkStartDate'] = date("Y-m-d H:i:s", strtotime($datearea[1] . " " . $tmp_year));
                        $link['LinkEndDate'] = date("Y-m-d 23:59:59", strtotime($datearea[2] . " " . $datearea[3]));

                    }

                    if ($link['LinkStartDate'] == '0000-00-00' || $link['LinkEndDate'] == '0000-00-00') {
                        if (preg_match('@(.*?)-(.*?),(.*?)(\d{4})@', $r, $datearea)) {
                            $LinkStartDate = date('Y-m-d H:i:s', strtotime($datearea[1] . ',' . $datearea[4]));
                            $LinkEndDate = date('Y-m-d H:i:s', strtotime($datearea[2] . ',' . $datearea[4]));
                            if (strtotime($datearea[1] . ',' . $datearea[4]) > strtotime($datearea[2] . ',' . $datearea[4])) {
                                $tmpYear = $datearea[4] - 1;;
                                $LinkStartDate = date('Y-m-d H:i:s', strtotime($datearea[1] . ',' . $tmpYear));
                            }
                            $link['LinkStartDate'] = $LinkStartDate;
                            $link['LinkEndDate'] = $LinkStartDate;
                        }
                    }

//					print_r($link);
                    if (empty($link['AffLinkId']) || empty($link['LinkName']) || $link['LinkName'] == '')
                        continue;
                    $links[] = $link;
                    $arr_return['AffectedCount']++;


                }
                echo sprintf("get %s by page...%s link(s) found.\n", $adType, count($links));
                if (count($links) > 0)
                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

                $startIndex += $pageSize;
            };

        }
        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, '', $merinfo['IdInAff']);
        return $arr_return;
    }

    function GetAllProductsByAffId()
    {

        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");

        $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
        $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
        $productNumConfigAlert = '';
        $isAssignMerchant = FALSE;
        $mcount = 0;
        $AccountSid = API_SID_58;
        $AccountToken = API_TOKEN_58;
        foreach ($arr_merchant as $merchatInfo) {
            echo $merchatInfo['IdInAff'] . PHP_EOL;
            $crawlMerchantsActiveNum = 0;
            $setMaxNum = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] : 100;
            $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
            $Catelog_url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com/Mediapartners/{$AccountSid}/Catalogs?CampaignId=" . $merchatInfo['IdInAff'];
            $request['addheader'] = array('accept: application/json');
            $r = $this->oLinkFeed->GetHttpResult($Catelog_url, $request);
            $r = json_decode($r['content'], true);
            if (!$r['@total'])
                continue;
            $links = array();
            foreach ($r['Catalogs'] as $value) {

                $TotalCount = $value['NumberOfItems'];
                $pages = 1;
                do {

                    if ($pages == 1)
                        $url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com" . $value['ItemsUri'] . "?PageSize=100";
                    else
                        $url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com" . $nextPageUri;
                    $re = $this->oLinkFeed->GetHttpResult($url, $request);
                    $re = json_decode($re['content'], true);
                    if (!isset($re['Items']) || empty($re['Items'])) {
                        break;
                    }
                    $nextPageUri = $re['@nextpageuri'];
                    foreach ($re['Items'] as $v) {

                        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"], "{$merchatInfo['IdInAff']}_" . urlencode($v['Id']) . ".png", PRODUCTDIR);
                        if (!$this->oLinkFeed->fileCacheIsCached($product_path_file)) {
                            $file_content = $this->oLinkFeed->downloadImg($v['ImageUrl']);
                            if (!$file_content) //下载不了跳过。
                                continue;
                            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
                        }
                        if (!isset($v['Name']) || empty($v['Name']) || !isset($v['Id'])) {
                            continue;
                        }

                        $link = array(
                            "AffId" => $this->info["AffId"],
                            "AffMerchantId" => $merchatInfo['IdInAff'],
                            "AffProductId" => trim($v['Id']),
                            "ProductName" => addslashes($v['Name']),
                            "ProductCurrency" => trim($v['Currency']),
                            "ProductPrice" => trim($v['CurrentPrice']),
                            "ProductOriginalPrice" => trim($v['OriginalPrice']),
                            "ProductRetailPrice" => '',
                            "ProductImage" => addslashes($v['ImageUrl']),
                            "ProductLocalImage" => addslashes($product_path_file),
                            "ProductUrl" => addslashes($v['Url']),
                            "ProductDestUrl" => '',
                            "ProductDesc" => addslashes($v['Description']),
                            "ProductStartDate" => '',
                            "ProductEndDate" => '',
                        );
                        $links[] = $link;
                        $arr_return['AffectedCount']++;
                        $crawlMerchantsActiveNum++;
                    }
                    if (count($links)) {
                        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                        $links = array();
                        //echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
                    }
                    //大于最大数跳出
                    if ($crawlMerchantsActiveNum >= $setMaxNum) {
                        break;
                    }
                    $pages++;
                } while (1);
                if ($isAssignMerchant) {
                    $productNumConfigAlert .= "AFFID:" . $this->info["AffId"] . ",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
                }
                $mcount++;

            }
        }
        echo 'merchant count:' . $mcount . PHP_EOL;
        $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
        echo $productNumConfigAlert . PHP_EOL;
        echo 'END time' . date('Y-m-d H:i:s') . PHP_EOL;
        return $arr_return;
    }

    private function get_linkcode_by_text_58($text)
    {
        if (preg_match('@ - (\w+)$@', $text, $g))
            return $g[1];
        return '';
    }

    function GetProgramByPage()
    {
        echo "\tGet Program by page start\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();
        $program_num = 0;

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);

        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "get",
            "postdata" => "",
        );

        $time = time() . "123";

        //get pending and not applied programs
        $status_arr = array(
            'Pending' => 'PENDING_MP_APPROVAL%2CPENDING_CAMPAIGN_APPROVAL',
            'NoPartnership' => 'NOT_APPLIED'
        );
        foreach ($status_arr as $partnership => $param) {
            echo "\r\nget $partnership programs\r\n";
            $startIndex = 0;
            $size = 100;
            $hasNextPage = true;
            while ($hasNextPage) {
                $strUrl = "https://member.impactradius.com/secure/nositemesh/market/campaign/all.ihtml?_dc=1518229631883&categories=&servicearea=&actions=&rstatus=$param&ads=&rating=&additional=&dealtype=&countries=&q=&tab=all&sortBy=name&sortOrder=ASC&page=1&startIndex=$startIndex&pageSize=$size";
                $result = $this->GetHttpResult($strUrl, $request, 'results', $partnership . "_program_index_$startIndex");
                $result = json_decode($result, true);
                if (empty($result)) {
                    mydie("Get data failed from page.");
                }
                if ($result['numRecords'] > $size + $startIndex) {
                    $startIndex += $size;
                } else {
                    $hasNextPage = false;
                }

                foreach ($result['results'] as $pv) {
                    $strMerID = intval($pv['id']);
                    if (!$strMerID) {
                        continue;
                    }
                    echo "$strMerID\t";

                    $strMerName = trim($pv['name']);
                    $CategoryExt = trim($pv['subTitle']);
                    $LogoUrl = trim($pv['logoSrc']);
                    $Homepage = trim($pv['landingPage']);
                    $CommissionExt = '';
                    if (!empty($pv['slides'])) {
                        foreach ($pv['slides'] as $val) {
                            $CommissionExt .= $val['value'] . '|';
                        }
                        $CommissionExt = rtrim($CommissionExt, '|');
                    }

                    $prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
                    $prgm_detail = $this->GetHttpResult($prgm_url, $request, 'Impact Radius', 'program_detail_' . $strMerID);

                    preg_match('/id="serviceAreas".*?>(.*?)<\/div>/', $prgm_detail, $TargetCountryExt);
                    $TargetCountryExt = isset($TargetCountryExt[1]) ? $TargetCountryExt[1] : "";
                    $JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Active Since', '<'));
                    if ($JoinDate) {
                        $JoinDate = date("Y-m-d H:i:s", strtotime(trim($JoinDate)));
                    }
                    $desc = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="dirPubDesc"','>'), '<');

                    $supportDeepLink = 'UNKNOWN';
                    $attrStr = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('class="campaignAttributesList"','>'), '</ul');
                    $attrStr = preg_replace('@>\s+<@', '><', $attrStr);
                    $attrArr = explode('</li><li', $attrStr);
                    if (isset($attrArr[7])) {
                        preg_match('@span class="uitkCheck([a-zA-Z]+)"@', $attrArr[7], $deep);
                        $supportDeepLink = $deep[1] == 'True' ? 'YES' : 'NO';
                    }

                    $arr_prgm[$strMerID] = array(
                        "Name" => addslashes(html_entity_decode($strMerName)),
                        "AffId" => $this->info["AffId"],
                        "CategoryExt" => addslashes($CategoryExt),
                        "IdInAff" => $strMerID,
                        "JoinDate" => $JoinDate,
                        "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
                        "Partnership" => $partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        "Description" => addslashes($desc),
                        "CommissionExt" => addslashes($CommissionExt),
                        "Homepage" => addslashes($Homepage),
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                        "TargetCountryExt" => addslashes($TargetCountryExt),
                        "LogoUrl" => addslashes($LogoUrl),
                        "SupportDeepUrl" => $supportDeepLink
                    );
                    $program_num ++;

                    if(count($arr_prgm) >= 100){
                        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                        $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                        $arr_prgm = array();
                    }
                }
            }
            if(count($arr_prgm)){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                $arr_prgm = array();
            }
        }

		//get active program
        echo "\r\nget active programs\r\n";
        $strUrl = "https://member.impactradius.com/secure/mediapartner/campaigns/mp-manage-active-ios-flow.ihtml?execution=e31s1";
        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);

        $page = 1;
		$hasNextPage = true;
		while($hasNextPage){
			$start = ($page - 1) * 100;
			$strUrl = "https://member.impactradius.com/secure/nositemesh/mediapartner/mpCampaignsJSON.ihtml?_dc=$time&startIndex=$start&pageSize=100&tableId=myCampaignsTable&page=$page";
            $result = $this->GetHttpResult($strUrl,$request, 'records', 'active_program_page_' . $page);
			$result = json_decode($result);
			$total = intval($result->totalCount);
			if($total < ($page * 100)){
				$hasNextPage = false;
			}
			$page++;
			$data = $result->records;
			foreach($data as $v){
				$strMerID = intval($v->id->crv);
				if (empty($strMerID)) {
				    continue;
                }
                echo "$strMerID\t";

				$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($v->name->dv, '">' , "</a>"));
				if ($strMerName === false) break;

				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($v->name->dv, 'uitkHiddenInGridView\">' , "</p>"));
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($v->id->dv, '<img src="' , '"'));
				$CreateDate = trim($v->launchDate->dv);
				if($CreateDate){
					$CreateDate = date("Y-m-d H:i:s", strtotime(str_replace(",", "", $CreateDate)));
				}

				$RankInAff = intval($v->irrating->crv);

				$prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
                $prgm_detail = $this->GetHttpResult($prgm_url, $request, 'Impact Radius', 'program_detail_' . $strMerID);

				$CommissionExt = "";
				preg_match_all('/notificationItem">(.*?)<\/li>/',$prgm_detail,$CommissionExt);
				$size = sizeof(isset($CommissionExt[1])?$CommissionExt[1]:array());
				if($size>0){
					unset($CommissionExt[1][$size-1]);
					$CommissionExt = strip_tags(implode('|',$CommissionExt[1]));
				}else{
					$CommissionExt='';
				}

				preg_match('/id="serviceAreas".*?>(.*?)<\/div>/',$prgm_detail,$TargetCountryExt);
				$TargetCountryExt = isset($TargetCountryExt[1])?$TargetCountryExt[1]:"";
				$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<span class="uitkDisplayTooltip uitkImageTooltip normalText">','onclick="parent.Ext.WindowMgr.getActive().hide()','">') , "<"));
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Active Since', '<'));
				if($JoinDate){
					$JoinDate = date("Y-m-d H:i:s", strtotime(trim($JoinDate)));
				}

				preg_match('/id="dirPubDesc".*?>(.*?)<\/p>/',$prgm_detail,$desc);
				$desc = isset($desc[1])?$desc[1]:'';
				$Homepage = "";
				preg_match("/<a href=(\"|')([^\"']*)\\1.*?>Company Home Page/i", $prgm_detail, $m);
				if(count($m) && strlen($m[2])){
					$Homepage = trim($m[2]);
				}

                $attrStr = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('class="campaignAttributesList"','>'), '</ul');
                $attrStr = preg_replace('@>\s+<@', '><', $attrStr);
                $attrArr = explode('</li><li', $attrStr);
                preg_match('@span class="uitkCheck([a-zA-Z]+)"@', $attrArr[7], $deep);
                $supportDeepLink = $deep == 'True' ? 'YES' : 'NO';

				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],
					"CategoryExt" => addslashes($CategoryExt),
					"CreateDate" => $CreateDate,
					"RankInAff" => $RankInAff,
					"IdInAff" => $strMerID,
					"JoinDate" => $JoinDate,
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Description" => addslashes($desc),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
					"TargetCountryExt" => addslashes($TargetCountryExt),
					"LogoUrl" => addslashes($LogoUrl),
                    "SupportDeepUrl" => $supportDeepLink
				);

				if(!empty($CommissionExt)){
					$arr_prgm[$strMerID]["CommissionExt"] = $CommissionExt;
				}
				if(!empty($Homepage)){
					$arr_prgm[$strMerID]["Homepage"] = $Homepage;
				}

				$program_num++;
				//print_r($arr_prgm);exit;
				if(count($arr_prgm) >= 1){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}

		if(!$this->getStatus) {
			//通过csv仅获取contacts
			//https://member.impactradius.com/secure/account/emaillist/myCampaignContacts.csv
			$str_header = "First Name,Last Name,Email,Campaign,Campaign Id";
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "myCampaignContacts.csv", "cache_contact");
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$strUrl = "https://member.impactradius.com/secure/account/emaillist/myCampaignContacts.csv";
				$request["postdata"] = "";

				$r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
				$result = $r["content"];
				print "Get Contacts <br>\n";
				if (stripos($result, $str_header) === false) mydie("die: wrong header: " . strstr($result, 0, stripos($result, "\n")));
				$this->oLinkFeed->fileCachePut($cache_file, $result);

				//Open CSV File
				$objProgram = new ProgramDb();
				$arr_prgm = array();
				$fhandle = fopen($cache_file, 'r');

				$arr_prgm = array();
				while ($line = fgetcsv($fhandle, 5000)) {
					foreach ($line as $k => $v) $line[$k] = trim($v);

					if ($line[0] == '' || $line[0] == 'First Name') continue;
					if (!isset($line[4])) continue;
					if (!isset($line[2])) continue;
					$arr_prgm[$line[4]] = array(
						"AffId" => $this->info["AffId"],
						"IdInAff" => $line[4],
						"Contacts" => addslashes($line[0] . " " . $line[1] . ", Email:" . $line[2]),
						//"LastUpdateTime" => date("Y-m-d H:i:s"),
					);

					if (count($arr_prgm) >= 100) {
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
				if (count($arr_prgm)) {
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			} else {
				echo "using previous file $cache_file <br>\n";
			}
		}
		echo "\tGet Program by page end\r\n";

		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}

		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function GetProgramDetailByPage()
	{
		echo "\tGet Program detail by page start\r\n";
	}

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

		$AccountSid = API_SID_58;
		$AccountToken = API_TOKEN_58;

		$hasNextPage = true;
		$perPage = 100;
		$page = 1;
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		while($hasNextPage){
			$strUrl = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com/2010-09-01/Mediapartners/{$AccountSid}/Campaigns.json?PageSize={$perPage}&Page=$page";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			$result = json_decode($result);
//			print_r($result);exit;

			$page++;

			$numpages = "@numpages";
			$numReturned = intval($result->$numpages);
			if(!$numReturned) break;
			if($page > $numReturned){
				$hasNextPage = false;
			}

			$mer_list = $result->Campaigns;
			
			//print_r($mer_list);exit;
			foreach($mer_list as $v)
			{
				$strMerID = intval($v->CampaignId);
				if(!$strMerID) continue;

				$strMerName = $v->CampaignName;
				$Homepage = $v->AdvertiserUrl;

				$StatusInAffRemark = $v->InsertionOrderStatus;
				if($StatusInAffRemark == "Expired"){
					$Partnership = "Expired";
				}elseif($StatusInAffRemark == "Active"){
					$Partnership = "Active";
				}else{
					$Partnership = "NoPartnership";
				}

				//$prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
				$TrackingLink = $v->TrackingLink;
				$AllowsDeeplinking = $v->AllowsDeeplinking;

				if(stripos($AllowsDeeplinking, "true") !== false){
					$SupportDeepurl = 'YES';
				}else{
					$SupportDeepurl = 'NO';
				}
				
				$deepDomains = implode(",",$v->DeeplinkDomains);
				//print_r($deepDomains."\t");
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"StatusInAffRemark" => $StatusInAffRemark,
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Homepage" => addslashes($Homepage),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => addslashes(trim($deepDomains)),
					"SupportDeepUrl" => $SupportDeepurl,
					"AffDefaultUrl" => addslashes($TrackingLink)
				);
				$program_num++;

				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}

		echo "\tGet Program by api end\r\n";

		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}

		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function GetStatus(){
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->GetProgramByPage();
		if(!$this->getStatus) {
			$this->GetProgramByApi();
		}
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
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

	function timezoneConvert($time, $timeZoneTo = "America/Los_Angeles", $reverse = false){
		if($time == '0000-00-00 00:00:00') return $time;
		if(trim($time) == ""){
			return "";
		}
		if(trim($timeZoneTo) == ""){
			$timeZoneTo = "America/Los_Angeles";
		}
		$timezoneOld = date_default_timezone_get();
		$curTime = strtotime($time);
		date_default_timezone_set($timeZoneTo);
		if($reverse){
			$curTime = strtotime($time);
			date_default_timezone_set($timezoneOld);
		}
		$curDate = date("Y-m-d H:i:s", $curTime);
		date_default_timezone_set($timezoneOld);
		return $curDate;
	}

    function GetHttpResult($url, $request, $valStr, $cacheFileName, $retry=3)
    {
        $results = '';
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "data_" . date("Ymd") . "_{$cacheFileName}.dat", 'data', true);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            while ($retry) {
                $r = $this->oLinkFeed->GetHttpResult($url, $request);
                if ($valStr) {
                    if (strpos($r['content'], $valStr) !== false) {
                        $results = $r['content'];
                        break;
                    }
                } elseif (!empty($r['content'])) {
                    $results = $r['content'];
                    break;
                }
                $retry--;
            }

            if (!$results) {
                mydie("Can't get the content of '{$url}', please check the val string !\r\n");
            }
            $this->oLinkFeed->fileCachePut($cache_file, $results);

            return $results;
        }
        $result = file_get_contents($cache_file);

        return $result;
    }

}
?>
