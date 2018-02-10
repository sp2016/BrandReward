<?php
require_once 'text_parse_helper.php';

class LinkFeed_2021_My_Commerce
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->AffiliateID = '622687';
		
	}
	
	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$url = "http://datafeed.regnow.com/$this->AffiliateID/coupon/xml/1487112614/59cbdbab6969b60c1bf6e3f5e697163846277a32";
		$result = $this->oLinkFeed->GetHttpResult($url);
		$r = simplexml_load_string($result['content']);
		$data_list = json_decode(json_encode($r), true);
		//var_dump($data_list);exit;
		foreach ($data_list['CouponProduct'] as $v)
		{
			$data = $v['@attributes'];
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $data['PRODUCT_VENDOR'],
					"AffLinkId" => $data['PRODUCT_ID'].'-'.$data['COUPON_CODE'],
					"LinkName" =>  $data['OFFER_NAME'].'-'.$data['PRODUCT_NAME'],
					"LinkDesc" =>  $data['PRODUCT_DESCRIPTION'],
					"LinkStartDate" => parse_time_str($data['START_TIME'], null, false),
					"LinkEndDate" => parse_time_str($data['END_TIME'], null, true),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkCode" => $data['COUPON_CODE'],
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => $data['BOXSHOT'],
					"LinkAffUrl" => $data['BUY_LINK'],
					"DataSource" => '',
					"Type"       => 'promotion'
			);
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			if(!$link['LinkAffUrl'] || !$link['LinkName'] || !$link['AffLinkId']) 
				continue;
			$links[] = $link;
			$arr_return["AffectedCount"]++;
			
			if(count($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

    function GetAllProductsByAffId()
    {
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
        $result = '';
        $product_arr = array();

        $page = 1;
        $hasNextPage = true;
        while($hasNextPage){
            echo $page . "\t";
            if ($page == 1) {
                //sortoptions=most_successful该项是为了仅仅爬去收益更多的product
                $product_url = "https://admin.mycommerce.com/app/cp/marketing/search/product?sortoptions=most_successful&rows_per_page=100";
            }else {
                $token = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('id="_xsrf_prevention_token"', 'value="'), '"'));
                $product_url = "https://admin.mycommerce.com/app/cp/marketing/search/product?&page={$page}&sortoptions=most_successful&_xsrf_prevention_token={$token}&rows_per_page=50";
            }
            $result = $this->oLinkFeed->GetHttpResult($product_url, $request);
            $result = preg_replace('@>\s+<@', '><', $result['content']);

            $total_div = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'div class="items">', '</div'));
            if (stripos($total_div, 'class="totalItems"') !== false) {
                $last_num = intval($this->oLinkFeed->ParseStringBy2Tag($total_div, 'class="lastItem">', '<'));
                $total_num = intval($this->oLinkFeed->ParseStringBy2Tag($total_div, 'class="totalItems">', '<'));
                if ($last_num == $total_num) {
                    $hasNextPage = false;
                }else {
                    $page ++;
                }
            }else {
                $hasNextPage = false;
            }

            $links = array();
            $dataTable = $this->oLinkFeed->ParseStringBy2Tag($result, array('table class="dataTable" id="Product"','tbody class="expandingTable">'), '</tbody');
            $data_arr = explode('</tr><tr>', $dataTable);
            foreach ($data_arr as $val) {
                if (stripos($val, 'td class="col-name"') === false) {
                    continue;
                }
                $strPos = 0;
                $pId = trim($this->oLinkFeed->ParseStringBy2Tag($val, 'vendors/product/', '"', $strPos));
                if (in_array($pId, $product_arr)) {
                    continue;
                }else {
                    $product_arr[] = $pId;
                }
                $pDetailUrl = sprintf('https://admin.mycommerce.com/app/cp/vendors/product/%s', $pId);
                $pName = trim($this->oLinkFeed->ParseStringBy2Tag($val, '>', '<', $strPos));
                if (!$pId || !$pName){
                    continue;
                }
                $marIdInAff = intval($this->oLinkFeed->ParseStringBy2Tag($val, 'vendors/relationships/', "'"));
                $pCommission = trim($this->oLinkFeed->ParseStringBy2Tag($val, 'class="col-display_commission">', '<'));
                $pPrice = trim($this->oLinkFeed->ParseStringBy2Tag($val, 'class="col-display_price">', '<'));
                $pCurrency = $pPrice[0];
                $pPrice = intval(substr($pPrice, 1));
                $pCategory = trim($this->oLinkFeed->ParseStringBy2Tag($val, 'class="col-display_category">', '<'));

                $dStrPos = 0;
                $detailPage = $this->oLinkFeed->GetHttpResult($pDetailUrl, $request);
                $detailPage = preg_replace('@>\s+<@', '><', $detailPage['content']);
                $pUrl = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailPage, array('Sales Link:', 'a href="'), '"', $dStrPos)));
                $pDesc = $this->oLinkFeed->ParseStringBy2Tag($detailPage, array('Description:', 'textarea', '>'), '</textarea', $dStrPos);
                preg_match('@option value="(.*?(:?jpg|jpeg|gif|png))">Boxshot</option@i', $detailPage, $m);
                $pImage = '';
                if (isset($m[1]) && !empty($m[1])) {
                    $pImage = $m[1];
                }
                $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$marIdInAff}_".urlencode($pId).".png", PRODUCTDIR);
                if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
                {
                    $file_content = $this->oLinkFeed->downloadImg($pImage);
                    if(!$file_content) //下载不了跳过。
                        continue;
                    $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
                }

                $link = array(
                    "AffId" => $this->info["AffId"],
                    "AffMerchantId" => 1,
                    "AffProductId" => $pId,
                    "ProductName" => addslashes($pName),
                    "ProductCategory" => addslashes($pCategory),
                    "ProductCurrency" => addslashes($pCurrency),
                    "CommissionExt" => addslashes($pCommission),
                    "ProductPrice" =>addslashes($pPrice),
                    "ProductOriginalPrice" =>'',
                    "ProductRetailPrice" =>'',
                    "ProductImage" => addslashes($pImage),
                    "ProductLocalImage" => addslashes($product_path_file),
                    "ProductUrl" => addslashes($pUrl),
                    "ProductDestUrl" => '',
                    "ProductDesc" => addslashes($pDesc),
                    "ProductStartDate" => '',
                    "ProductEndDate" => '',
                );
                $links[] = $link;
                $arr_return['AffectedCount'] ++;

            }
            if (count($links)) {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                $links = array();
            }
        }

        $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);

        echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
        return $arr_return;
    }
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$end_date = urlencode(date('Y-m-d\TH:i:s', time()));
		$start_date = urlencode(date('Y-m-d\TH:i:s', strtotime('-1 year')));
		
		//1.login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		
		//2.get Relationships
		$Relationships_url = "https://admin.mycommerce.com/app/cp/vendors/relationships";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ""
		);
		$result = $this->oLinkFeed->GetHttpResult($Relationships_url, $request);
		$result = $result['content'];
		//print_r($result);exit;
		$_xsrf_prevention_token = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('input id="_xsrf_prevention_token"', 'value="'), '"'));
		$status_arr = array(
				'Active' => 'A',
				'Disabled' => 'D',
				'Unreviewed Application' => 'U',
				'Rejected Application' => 'R',
		);
		
		foreach ($status_arr as $StatusInAffRemark => $statusID)
		{
			
			$limit = 100;
			$page = 1;
			$HasNextPage = true;
			while ($HasNextPage)
			{	
			
				$url = "https://admin.mycommerce.com/app/cp/vendors/relationships?listAction=refresh&sortColumn=&sortDirection=&searchExpanded=true&page=$page&related_id=&related_name.where=starts_with&related_name.value=&status=$statusID&buttons.search=Search&date.begin=&date.end=&commission.cmp=%3E%3D&commission.value=&commission.type=B&rows_per_page=$limit&_xsrf_prevention_token=$_xsrf_prevention_token";
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$r = $r['content'];
				//print_r($r);exit;
				if (stripos($r, 'onclick="return false;" title="Next Page"') === false)
					$HasNextPage = false;
				$LineStartString = '<tbody class="expandingTable">';
				$LineStart = stripos($r, $LineStartString);
				while (1)
				{
					$Detail_url = trim($this->oLinkFeed->ParseStringBy2Tag($r, '<td class="col-vendor"><a href='."'", "'", $LineStart));
					if (!$Detail_url)
						break;
					$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($Detail_url, 'https://admin.mycommerce.com/app/cp/vendors/relationships/', ''));
					$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($r, '>', '<', $LineStart));
					$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($r, '<td class="col-homepage"><a href='."'", "'", $LineStart));
					$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($r, '<td class="col-affiliate_amount">', '<', $LineStart));
					$CreateDate = parse_time_str(trim($this->oLinkFeed->ParseStringBy2Tag($r, '<td class="col-display_insert_time">', '<', $LineStart)));
					$detail_r = $this->oLinkFeed->GetHttpResult($Detail_url, $request);
					$detail_r = $detail_r['content'];
					$Contacts = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, array('Customer Support Email:', '3px">'), '<'));
					$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, array('Custom Affiliate Terms:', '3px">'), '<'));
					
					//category
					$product_url = "http://datafeed.regnow.com/$this->AffiliateID/auto/xml?pt=B&kt=1&vid=$strMerID&ed=$end_date&sd=$start_date&s=1";
					$product_list = $this->oLinkFeed->GetHttpResult($product_url);
					$product_list = simplexml_load_string($product_list['content']);
					$product_list = json_decode(json_encode($product_list), true);
					$categories = '';
					if (empty($product_list['Product']))
						$CategoryExt = '';
					elseif (isset($product_list['Product']['@attributes']))
						$categories = $product_list['Product']['@attributes']['CategoryName'];
					else
					{
						foreach ($product_list['Product'] as $v)
						{
							$product = $v['@attributes'];
							if (!empty($categories))
								$categories .= ','.$product['CategoryName'];
							 else
								$categories = $product['CategoryName'];
						}
					}
					if (!empty($categories))
					{
						$categories = str_replace('::', ',', $categories);
						$categories_arr = explode(',', $categories);
						$categories = array_unique($categories_arr);
						$CategoryExt = implode(',', $categories);
					}
						
					if ($StatusInAffRemark == 'Active')
					{
						$StatusInAff = 'Active';
						$Partnership = 'Active';
					}elseif ($StatusInAffRemark == 'Disabled')
					{
						$StatusInAff = 'Offline';
						$Partnership = 'NoPartnership';
					}elseif ($StatusInAffRemark == 'Unreviewed Application')
					{
						$StatusInAff = 'Active';
						$Partnership = 'Pending';
					}elseif ($StatusInAffRemark == 'Rejected Application')
					{
						$StatusInAff = 'Active';
						$Partnership = 'Declined';
					}
					
					$arr_prgm[$strMerID] = array(
							"Name" => addslashes($strMerName),
							"AffId" => $this->info["AffId"],
							"Contacts" => $Contacts,
							//"TargetCountryExt" => $TargetCountryExt,
							"IdInAff" => $strMerID,
							"JoinDate" => $CreateDate,
							"StatusInAffRemark" => $StatusInAffRemark,
							"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
							"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
							//"Description" => addslashes($desc),
							"Homepage" => $Homepage,
							"TermAndCondition" => addslashes($TermAndCondition),
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							"DetailPage" => $Detail_url,
							//"AffDefaultUrl" => addslashes($AffDefaultUrl),
							"CommissionExt" => addslashes($CommissionExt),
							"CategoryExt" => addslashes(trim($CategoryExt)),
							"SupportDeepUrl"=>'UNKNOWN',
					);
					//print_r($arr_prgm[$strMerID]);
					$program_num++;
					if(count($arr_prgm) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
				if(count($arr_prgm)){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				$page++;
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

	
}
?>