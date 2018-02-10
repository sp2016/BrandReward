<?php

require_once 'xml2array.php';
require_once 'text_parse_helper.php';
require_once 'program279_IdInAff.php';

//define('APIKEY_279', 'jitow735u-36c7-52zy-7t6xaim8hx');
define('APIKEY_279', 'jil3p5ae5-z485-f9sr-m536tb2ozq');
define('MAX_REQUEST_279', 150);

class LinkFeed_279_Coupilia
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$types = array('bogo', 'coupon', 'deal', 'shipping', 'gwp', 'rebate', 'sale');
		$recordset = 'all';
		$update_to_affiliate = 0;
		$program279_IdInAff = $GLOBALS['program279_IdInAff'];
		foreach ($types as $type)
		{
			$url_base = 'http://www.coupilia.com/feeds/coupons_v2.asp?token=%s&recordset=%s&dealtype=%s';
			$url = sprintf($url_base, APIKEY_279, $recordset, $type);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$dom = new DomDocument();
			@$dom->loadXML($content);
			$data = @XML2Array::createArray($dom);
			if (empty($data) || !is_array($data) || count($data) < 1 || 
					empty($data['coupons']) || !is_array($data['coupons']) ||
					empty($data['coupons']['item']) || !is_array($data['coupons']['item']))
				continue;
			$links = array();
			if (empty($data['coupons']['item']['id'])) //multi line
				$deals = $data['coupons']['item'];
			else
			{
				$deals = array();
				$deals[] = $data['coupons']['item'];
			}
			foreach ($deals as $key => $v)
			{
				if (empty($v) || !is_array($v))
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffLinkId" => $v['id'],
						"LinkName" => $v['offer'],
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00',
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'DEAL',
						"LinkAffUrl" => $v['url'],
						"LinkOriginalUrl" => '',
						"DataSource" => 77,
				);
				switch ($type)
				{
					case 'bogo':
						$link['LinkDesc'] = 'type: Buy one get one, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'deal':
						$link['LinkDesc'] = 'type: Deal, price drop, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'gwp':
						$link['LinkDesc'] = 'type: Gift with purchase, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'rebate':
						$link['LinkDesc'] = 'type: Rebate, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'sale':
						$link['LinkDesc'] = 'type: Sale, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'coupon':
						$link['LinkDesc'] = 'type: Coupon, ';
						$link['LinkPromoType'] = 'DEAL';
						break;
					case 'shipping':
						$link['LinkDesc'] = 'type: Free shipping, ';
						$link['LinkPromoType'] = 'free shipping';
						break;
					default:
						$link['LinkPromoType'] = 'N/A';
						break;
				}
				if (!empty($v['holiday']) && $v['holiday'] != 0)
					$link['LinkDesc'] .= sprintf('holiday: %s, ', $v['holiday']);
				if (!empty($v['rating']) && $v['rating'] != 0)
					$link['LinkDesc'] .= sprintf('rating: %s, ', $v['rating']);
				if (!empty($v['code']))
				{
					$link['LinkCode'] = $v['code'];
					$link['LinkPromoType'] = 'COUPON';
				}
				if (!empty($v['startdate']))
				{
					$date = strtotime($v['startdate']);
					if ($date > 946713600) //2000-1-1 to make sure a real time
						$link['LinkStartDate'] = date('Y-m-d 00:00:00', $date);
				}
				if (!empty($v['enddate']))
				{
					$date = strtotime($v['enddate']);
					if ($date > 946713600)
						$link['LinkEndDate'] = date('Y-m-d 23:59:59', $date);
				}
				if (!empty($v['logo']))
					$link['LinkImageUrl'] = $v['logo'];
				if (empty($program279_IdInAff[$v['merchantid']]))
					continue;
				$link['AffMerchantId'] = $program279_IdInAff[$v['merchantid']];
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
				switch (strtolower($v['network']))
				{
					case 'af': //Affiliate Future
						$link['LinkOriginalUrl'] = '20';
						break;
					case 'av': //AvantLink
						$link['LinkOriginalUrl'] = '8';
						break;
					case 'cj': //Commission Junction
						$link['LinkOriginalUrl'] = '1';
						break;
					case 'dr': //Digital River
						$link['LinkOriginalUrl'] = '30';
						break;
					case 'pj': //Ebay Enterprise Network
						$link['LinkOriginalUrl'] = '6';
						break;
					case 'ir': //Impact Radius
						$link['LinkOriginalUrl'] = '58';
						break;
					case 'lc': //Link Connector
						$link['LinkOriginalUrl'] = '12';
						break;
					case 'ls': //Linkshare
						$link['LinkOriginalUrl'] = '2';
						break;
					case 'sas'://Shareasale
						$link['LinkOriginalUrl'] = '7';
						break;
					case 'wg': //Webgains
						$link['LinkOriginalUrl'] = '13';
						break;
					case 'za': //Zanox
						$link['LinkOriginalUrl'] = '15';
						break;
					default:
						break;
				}
				if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$links[] = $link;
			}
			echo sprintf("type: %s, %s link(s) found.\n", $type, count($links));
			if (count($links) > 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$update_to_affiliate += $this->oLinkFeed->UpdateLinkToAffiliateDB($links);
			}
		}
		echo sprintf("getCouponFeed finished. UpdatedCount: %s/, UpdateToAffiliate:%s\n", $arr_return["UpdatedCount"], $arr_return["UpdatedCount"], $update_to_affiliate);
		return $arr_return;
	}
}
?>
