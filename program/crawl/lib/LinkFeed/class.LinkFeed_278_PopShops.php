<?php

require_once 'xml2array.php';
require_once 'text_parse_helper.php';

define('APIKEY_278', 'e7tl51c086cd86urq7i32n050');
define('MAX_REQUEST_278', 150);

class LinkFeed_278_PopShops
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->request_count = 0;
	}		

	function GetLinkPromoTypeByDealType($types)
	{
		if (empty($types))
			return 'N/A';
		$type = 'N/A';
		$ids = explode(',', $types);
		foreach ($ids as $id)
		{
			switch($id)
			{
				case 2:  //Coupon Code
				case 14: //Exclusive
					$type = 'coupon';
					break;
				case 18: //1-2-3 Day Only
				case 8:  //Deal of Day
				case 6:  //Dollars Off Coupon
				case 3:  //Free Gift
				case 16: //Hot Product
				case 12: //New Customer
				case 17: //No Minimum
				case 5:  //Percent Off Coupon
				case 9:  //Rebate
				case 4:  //Sale/Clearance
				case 11: //Seasonal Deal
				case 19: //Virtual coupon
					$type = 'deal';
					break;
				case 1:  //Free Shipping
				case 15: //Shipping Promo
					$type = 'free shipping';
					return $type;
			}
		}
		return $type;
	}

	function GetLinkTypeDescription($deal_type_ids)
	{
		$type_name = '';
		if (!empty($deal_type_ids))
		{
			$type_str = array();
			$ids = explode(',', $deal_type_ids);
			foreach ($ids as $id)
			{
				switch($id)
				{
					case 2:  //Coupon Code
						$type_str[] = 'Coupon Code';
						break;
					case 14: //Exclusive
						$type_str[] = 'Exclusive';
						break;
					case 18: //1-2-3 Day Only
						$type_str[] = '1-2-3 Day Only';
						break;
					case 8:  //Deal of Day
						$type_str[] = 'Deal of Day';
						break;
					case 6:  //Dollars Off Coupon
						$type_str[] = 'Dollars Off Coupon';
						break;
					case 3:  //Free Gift
						$type_str[] = 'Free Gift';
						break;
					case 16: //Hot Product
						$type_str[] = 'Hot Product';
						break;
					case 12: //New Customer
						$type_str[] = 'New Customer';
						break;
					case 17: //No Minimum
						$type_str[] = 'No Minimum';
						break;
					case 5:  //Percent Off Coupon
						$type_str[] = 'Percent Off Coupon';
						break;
					case 9:  //Rebate
						$type_str[] = 'Rebate';
						break;
					case 4:  //Sale/Clearance
						$type_str[] = 'Sale/Clearance';
						break;
					case 11: //Seasonal Deal
						$type_str[] = 'Seasonal Deal';
						break;
					case 19: //Virtual coupon
						$type_str[] = 'Virtual coupon';
						break;
					case 1:  //Free Shipping
						$type_str[] = 'Free Shipping';
						break;
					case 15: //Shipping Promo
						$type_str[] = 'Shipping Promo';
						break;
				}
			}
			$type_name = implode(',', $type_str);
		}
		$r = sprintf("Deal type: %s", $type_name);
		return $r;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$types = array('2',);
		$update_to_affiliate = 0;
		foreach ($types as $type)
		{
			$offset = 0;
			$sum = 0;
			$deal_limit = 100;
			$url_base = 'http://api.popshops.com/v2/cx5amgnjr2mcmamm9j5jljik4/deals.xml?catalog_key=%s&deal_type_id=%s&deal_limit=%s&deal_offset=%s&include_deal_ids=1&deal_sort=start_on_desc';
			do 
			{
				$url = sprintf($url_base, APIKEY_278, $type, $deal_limit, $offset);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$this->request_count ++;
				$content = $r['content'];
				$dom = new DomDocument();
				@$dom->loadXML($content);
				$data = @XML2Array::createArray($dom);
				if (empty($data) || !is_array($data) || count($data) < 1 || 
						empty($data['search_results']) || !is_array($data['search_results']) ||
						empty($data['search_results']['deals']) || !is_array($data['search_results']['deals']))
					break;
				if ($sum == 0)
				{
					$sum = (int)$data['search_results']['deals']['@attributes']['total_count'];
					if ($sum < 1)
						break;
				}
				$offset += $deal_limit;
				$links = array();
				if (empty($data['search_results']['deals']['deal']['@attributes']['id'])) //multi line
					$deals = $data['search_results']['deals']['deal'];
				else
				{
					$deals = array();
					$deals[] = $data['search_results']['deals']['deal'];
				}
				foreach ($deals as $key => $v)
				{
					if (empty($v['@attributes']) || !is_array($v['@attributes']))
						continue;
					$v = $v['@attributes'];
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffLinkId" => $v['id'],
							"LinkName" => html_entity_decode(str_force_utf8($v['name'])),
							"LinkDesc" => '',
							"LinkStartDate" => '0000-00-00',
							"LinkEndDate" => '0000-00-00',
							"LinkPromoType" => 'DEAL',
							"LinkAffUrl" => $v['url'],
							"LinkOriginalUrl" => '',
							"DataSource" => 76,
					);
					$deal_type_ids = empty($v['deal_type_ids']) ? '' : $v['deal_type_ids'];
					$link['LinkPromoType'] = $this->GetLinkPromoTypeByDealType($deal_type_ids);
					if (!empty($v['code']))
					{
						$link['LinkCode'] = $v['code'];
						$link['LinkPromoType'] = 'COUPON';
					}
					if (!empty($v['start_on']))
					{
						$date = strtotime($v['start_on']);
						if ($date > 946713600) //2000-1-1 to make sure a real time
							$link['LinkStartDate'] = date('Y-m-d 00:00:00', $date); 
					}
					if (!empty($v['end_on']))
					{
						$date = strtotime($v['end_on']);
						if ($date > 946713600)
							$link['LinkEndDate'] = date('Y-m-d 23:59:59', $date);
					}
					if (!empty($v['image_url']))
						$link['LinkImageUrl'] = $v['image_url'];
					$link['AffMerchantId'] = $v['network_merchant_id'];
					
					switch ($v['network_id'])
					{
						case '14':
							$link['LinkOriginalUrl'] = '8';
							break;
						case '15':
							$link['LinkOriginalUrl'] = '58';
							break;
						case '16':
							break;
						case '2':
							$link['LinkOriginalUrl'] = '1';
							break;
						case '4':
							$link['LinkOriginalUrl'] = '2';
							break;
						case '1':
							$link['LinkOriginalUrl'] = '7';
							break;
						case '7':
							$link['LinkOriginalUrl'] = '12';
							break;
						case '8':
							break;
						case '11':
							$link['LinkOriginalUrl'] = '13';
							break;
						case '12':
							$link['LinkOriginalUrl'] = '10';
							break;
						case '13':
							$link['LinkOriginalUrl'] = '133';
							break;
					}
					$link['LinkDesc'] = $this->GetLinkTypeDescription($deal_type_ids);
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
					if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkHtmlCode']))
						continue;
					$links[] = $link;
				}
				echo sprintf("type: %s, offset: %s/%s, %s link(s) found.\n", $type, $offset, $sum, count($links));
				if (count($links) > 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$update_to_affiliate += $this->oLinkFeed->UpdateLinkToAffiliateDB($links);
				}
			}while ($offset < $sum && $this->request_count < MAX_REQUEST_278);
		}
		echo sprintf("getCouponFeed finished. Api request count: %s, UpdatedCount: %s/, UpdateToAffiliate:%s\n", $this->request_count, $arr_return["UpdatedCount"], $update_to_affiliate);
		return $arr_return;
	}
	
}
?>
