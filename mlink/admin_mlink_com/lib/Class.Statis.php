<?php
class Statis extends LibFactory
{
    function getCommission($data,$page,$pageSize){
        $dType = isset($data['dtype']) ? $data['dtype'] : '';
        //按日期统计-新统计方式:
        $sObj = new AffiliateCalculation();
        $sObj->setStartDate($data['stime']);
        $sObj->setEndDate($data['etime']);
        $exceptSiteArray  = array();
        if ($data['datatype'] != 2) {
            $mkWhereSql = mk_publisher_where();
            $sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
            $res = $this->getRows($sql);
            foreach ($res as $apiKey) {
                $aKey = isset($apiKey['ApiKey']) ? $apiKey['ApiKey'] : null;
                if (empty($aKey)) {
                    continue;
                }
                array_push($exceptSiteArray,$aKey);
            }
        }
        $sObj->setExceptSite($exceptSiteArray);
        $calRows = $sObj->doCalculate('Date',false);
        $return['count'] = count($calRows);
        $offset = ceil($page / $pageSize);
        if ($dType == 'sc')
        {
            $calRows = $sObj->doCalculate('Date',true,$offset,$pageSize,array('createddate ASC'));
            foreach ($calRows as &$row) {
                $row['epc'] = $row['rate'] = '-';
                if ($row['click'] > 0 && $row['commission'] > 0 && $row['sales'] > 0) {
                    $epc = $row['commission'] / ($row['click'] - $row['rob']);
                    $row['epc'] = '$' . number_format($epc, 2, '.', ',');
                    $rate = $row['commission'] / $row['sales'] * 100;
                    $row['rate'] = number_format($rate, 2, '.', ',') . "%";
                }
                $row['clicks'] = number_format($row['click'] - $row['rob']);
                $row['commission'] = '$' . number_format($row['commission'], 2);
                $row['sales'] = '$' . number_format($row['sales'], 2);
                $row['order'] = number_format($row['order']);
                $row['rob'] = number_format($row['rob']);
                $row['robp'] = number_format($row['robp']);
                $row['date'] = $row['createddate'];
                unset($row['createddate']);
            }
            $return['data'] = $calRows;
            return $return;
        }

        if ($dType == 'na')
        {
            $xObj = new DomainCalculation();
            $xObj->setStartDate($data['stime']);
            $xObj->setEndDate($data['etime']);
            $xObj->setExceptSite($exceptSiteArray);
            $xObj->setStoreSql("SELECT ID FROM `store` WHERE StoreAffSupport = 'NO' AND ID > 0 and ID != 157639");
            $xcOjct = new Calculation();
            $xcOjct->filterKeyword = true;
            $filterDomains = $xcOjct->processFilterKeyword();
            $filterDomainArray = explode(',', $filterDomains);
            $xObj->setExceptDomain($filterDomainArray);
            $naData = $xObj->doCalculate('Store',true,$offset,$pageSize,array('click DESC'));
            foreach ($naData as &$naValue) {
                $naValue['clicks'] = $naValue['click'];
                $storeId = isset($naValue['storeId']) ? $naValue['storeId'] : 0;
                if (empty($storeId)) {
                    $naValue['name'] = '';
                    continue;
                }
                $subSql = "SELECT IF(NameOptimized = '' OR NameOptimized IS NULL, `Name`, NameOptimized) AS `name` FROM store WHERE ID = $storeId";
                $storeRow = $this->getRow($subSql);
                if (!empty($storeRow) && isset($storeRow['name'])) {
                    $naValue['name'] = $storeRow['name'];
                }
            }
            $return['data'] = $naData;
            $caData = $xObj->doCalculate('Store',false);
            $return['count'] = count($caData);

            return $return;
        }
        //Store Top10 按商家统计
        $aObj = new DomainCalculation();
        $cObj = new Calculation();
        $cObj->advertiserCooperationStatus = 'YES';
        $domainSql = $cObj->doAdvertiserQuery('domain');
        $aObj->setExceptSite($exceptSiteArray);
        $aObj->setStartDate($data['stime']);
        $aObj->setEndDate($data['etime']);
        $aObj->setDomainSql($domainSql);
        $calAdv = $aObj->doCalculate('Store',true,0,10,array('commission DESC'));
        foreach ($calAdv as &$adv) {
            $storeId = isset($adv['storeId']) ? $adv['storeId'] : 0;
            if (empty($storeId)) {
                continue;
            }
            $subSql = "SELECT IF(NameOptimized = '' OR NameOptimized IS NULL, `Name`, NameOptimized) AS `name` FROM store WHERE ID = $storeId";
            $storeRow = $this->getRow($subSql);
            if (!empty($storeRow) && isset($storeRow['name'])) {
                $adv['name'] = $storeRow['name'];
            }
        }
        //Publisher Top10 按Publisher统计
        $pObj = new AffiliateCalculation();
        $pObj->setStartDate($data['stime']);
        $pObj->setEndDate($data['etime']);
        $pObj->setExceptSite($exceptSiteArray);
        $calPub = $pObj->doCalculate('Publisher',true,0,10,array('commission DESC','click DESC'));
        //查询publisher数量
        $sql = "SELECT count(1) AS count FROM publisher WHERE AddTime>='{$data['stime']}' AND Addtime<='{$data['etime']}'";
        $pubTotal = $this->getRow($sql);
        $return['publisher'] = $pubTotal['count'];
        $return['sum'] = $calRows;
        $return['adv'] = $calAdv;
        $return['toppub'] = $calPub;
        return $return;

    }
    function getpub($data,$page,$pagesize){
        $return  = array();
        $exceptSiteArray = array();
        if ($data['datatype'] != 2) {
            $mkWhereSql = mk_publisher_where();
            $sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
            $res = $this->getRows($sql);
            foreach ($res as $apiKey) {
                $aKey = isset($apiKey['ApiKey']) ? $apiKey['ApiKey'] : null;
                if (empty($aKey)) {
                    continue;
                }
                array_push($exceptSiteArray,$aKey);
            }
        }
        $pObj = new AffiliateCalculation();
        $pObj->setStartDate($data['stime']);
        $pObj->setEndDate($data['etime']);
        $pObj->setExceptSite($exceptSiteArray);
        $total = $pObj->doCalculate('Publisher',false);;
        $return['count'] = count($total);
        $offset = ceil($page / $pagesize);
        $res = $pObj->doCalculate('Publisher',true,$offset,$pagesize,array('commission DESC'));
        foreach($res as &$k){
            if(($k['click'] - $k['rob']) >0 && $k['commission'] >0){
                $k['epc'] = '$'.number_format(($k['commission']/($k['click']-$k['rob'])),2,'.',',');
            }else{
                $k['epc'] = '-';
            }
            if($k['sales'] >0 && $k['commission'] >0){
                $k['rate'] = number_format(($k['commission']/$k['sales']*100),2,'.',',')."%";
            }else{
                $k['rate'] = '-';
            }
            $k['date'] = $k['name'];
            $k['clicks'] = number_format($k['click']-$k['rob']);
            $k['commission'] = '$'.number_format($k['commission'],2);
            $k['sales'] = '$'.number_format($k['sales'],2);
            $k['order'] = number_format($k['order']);
            $k['rob'] = number_format($k['rob']);
            $k['robp'] = number_format($k['robp']);
        }
        $return['data'] = $res;
        return $return;
    }
    function download($data){
        print(chr(0xEF).chr(0xBB).chr(0xBF)); //add utf8 bom in csv file
        header('Pragma:public');
        header('Expires:0');
        header("Content-type:text/csv");
        header("Content-type:  application/octet-stream;");
        header('Content-Transfer-Encoding: binary');
        if($data['dtype'] == 'sc' || $data['dtype'] == 'na'){
            $info = $this->getCommission($data,0,1000);
            if($data['dtype'] == 'sc'){
                header("Content-Disposition: attachment; filename= Daily.csv");
                $dataHead = array('Date','Sales','Commission','Order','Clicks','Robot','May Be Robot','Epc','Commission Rate');
            }else{
                header("Content-Disposition: attachment; filename= Daily.csv");
                $dataHead = array('Advertiser','Clicks');
            }
            echo implode(',',$dataHead)."\n";
        }else{
            header("Content-Disposition: attachment; filename=Publisher Daily.csv");
            $info = $this->getpub($data,0,1000);
            $dataHead = array('Publisher','Sales','Commission','Order','Clicks','Robot','May Be Robot','Epc','Commission Rate');
            echo implode(',',$dataHead)."\n";
        }
        if($data['dtype'] == 'na'){
            $count = $info['count']['count'];
        }else{
            $count = $info['count'];
        }
        $page_total = ceil($count/1000);
        $page = 0;
        do{
            if($data['dtype'] == 'sc' || $data['dtype'] == 'na'){
                $datares = $this->getCommission($data,$page*1000,1000);
            }else{
                $datares = $this->getpub($data,0,1000);
            }
            $content = $datares['data'];
            if($data['dtype'] == 'na'){
                foreach ($content as &$v){
                    $datares = array($v['name'], $v['clicks']);
                    echo '"'.implode('","',$datares).'"'."\n";
                }
            }else{
                foreach ($content as &$v){
                    $datares = array($v['date'], $v['sales'], $v['commission'], $v['order'], $v['clicks'], $v['rob'], $v['robp'], $v['epc'], $v['rate']);
                    echo '"'.implode('","',$datares).'"'."\n";
                }
            }
            $page++;
        }while($page < $page_total);
        exit();
    }
    function getCategory(){
        $category = array();
        $sql = "SELECT * from category_std ORDER BY `Name` ASC;";
        $rs = $this->getRows($sql);
        foreach($rs as $item)
        {
            $category[$item['ID']] = $item['Name'];
        }
        return $category;
    }


}
