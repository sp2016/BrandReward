<?php

class Tools extends LibFactory
{
    function get_currency_daily_chart($data_from, $data_to)
    {
        if (empty($data_from) || empty($data_to)) {
            return array();
        }

        $days = round((strtotime($data_to) - strtotime($data_from)) / 86400) + 1;
        if ($days > 60) {
            return array();
        }

        $tmp = $this->table('exchange_rate')->where('`Date` >= "' . addslashes($data_from) . '" AND `Date` <= "' . addslashes($data_to) . '"')->order('Date ASC')->find();

        $dateArr = array();
        $currencyData = array();
        foreach ($tmp as $k => $v) {
            if (!in_array($v['Date'], $dateArr)) {
                $dateArr[] = $v['Date'];
            }

            $currencyData[$v['Name']][$v['Date']] = $v['ExchangeRate'];
        }

        sort($dateArr);

        foreach ($currencyData as $a => $cur) {
            foreach ($dateArr as $v) {
                if (!isset($cur[$v]))
                    $currencyData[$a][$v] = 0;
            }
            ksort($currencyData[$a]);
        }

        return $currencyData;
    }


    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }


    function get_csv_export($listObj, $listFunction, $jsonStr)
    {//原来生成list所用的类、类中方法名、json数据
        error_reporting(E_ALL^E_NOTICE);
        $header = rtrim($jsonStr['field'],',');
        $title = rtrim($jsonStr['t'],',');
        $title = explode(',',$title);
        $data['from'] = $jsonStr['stime'];
        $data['to'] = $jsonStr['etime'];
        $data['advertiser'] = $jsonStr['adv'];
        $data['linkid'] = $jsonStr['linkid'];
        $data['pid'] = $jsonStr['site'];
        $data['type'] = $jsonStr['type'];
        $data['cid'] = $jsonStr['cid'];
        $data['sitetype'] = $jsonStr['sitetype'];
        $aff = '';
        $country = '';
        if(strstr($jsonStr['aff'],',')){
            $affarr = explode(',',$jsonStr['aff']);
            foreach($affarr as $k){
                $aff.=$k.',';
            }
            $aff = rtrim($aff,',');
        }else if($jsonStr['aff'] === 'null'){
            $aff = '';
        }else{
            $aff = $jsonStr['aff'];
        }
        if(strstr($jsonStr['country'],',')){
            $conarr = explode(',',$jsonStr['country']);
            foreach($conarr as $k){
                if($k == 'UK'){
                    $country.='"gb","uk",';
                    continue;
                }
                $country.='"'.strtolower($k).'",';
            }
            $country = rtrim($country,',');
        }else if($jsonStr['country'] == 'null'){
            $country = '';
        }else{
            if($jsonStr['country'] == 'UK'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($jsonStr['country']).'"';
            }
        }
        $data['affiliate'] = $aff;
        if(isset($data['cid']) && !empty($data['cid'])){
            $sql = "select CountryCode from country_codes where CountryName ='{$_POST['cid']}'";
            $c = $listObj->getRow($sql);
            if($c['CountryCode'] == 'UK' || $c['CountryCode'] == 'GB'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($c['CountryCode']).'"';
            }
            $data['country'] = $country;
            $data['cid'] = 1;
        }else{
            $data['country'] = $country;
            $data['cid'] = 1;
        }
        $data['download'] = 1;
        $arr = $listObj->$listFunction($data,0,1);//防止内存爆掉，每次取1k条
        $pagesize = 1000;
        $page_total = ceil($arr['total_num']/$pagesize);
        $cname = 'Outlog'.date('Y-m-d',time());
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=$cname.csv");
        echo $header."\n";
        for($i=0;$i<$page_total;$i++){
            $pz=$i*$pagesize;
            $arr = $listObj->$listFunction($data,$pz,$pagesize);
            $arr = $arr['data'];
            foreach ($arr as $v) {
                $tmp='';
                foreach($title as $k){
                    $val = $v[$k];
                    if($k == 'created'){
                        $tmp.=$val.',';
                    }else{
                        $tmp.=skyaddslash($val).',';
                    }
                }
                echo $tmp."\n";
            }
        }
    }
    function performance_site($listObj, $listFunction, $jsonStr){
        error_reporting(E_ALL^E_NOTICE);
        $data = $this->object_array($jsonStr);
        $data['p'] = 0;
        $aff = '';
        $country = '';
        if(strstr($jsonStr['affiliate'],',')){
            $affarr = explode(',',$jsonStr['affiliate']);
            foreach($affarr as $k){
                $aff.=$k.',';
            }
            $aff = rtrim($aff,',');
        }else if($jsonStr['affiliate'] === 'null'){
            $aff = '';
        }else{
            $aff = $jsonStr['affiliate'];
        }
        if(strstr($jsonStr['country'],',')){
            $conarr = explode(',',$jsonStr['country']);
            foreach($conarr as $k){
                if($k == 'UK'){
                    $country.='gb,uk,';
                    continue;
                }
                $country.=strtolower($k).',';
            }
            $country = rtrim($country,',');
        }else if($jsonStr['country'] == 'null'){
            $country = '';
        }else{
            if($jsonStr['country'] == 'UK'){
                $country = 'gb,uk';
            }else{
                $country = strtolower($jsonStr['country']);
            }
        }
        $data['affiliate'] = $aff;
        $data['country'] = $country;
        $data['p'] = 0;
        $arr = $listObj->$listFunction($data,0,1);//防止内存爆掉，每次取1k条
        $pagesize = 1000;
        $page_total = ceil($arr['count']/$pagesize);
        $cname = 'Performance_Site'.date('Y-m-d',time());
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=$cname.csv");
        echo "Publisher,Domain,Manager,Status,Site Type,Sales,Commission,Orders,Total Clicks,Real Clicks,Robot,May Be Robot,Epc,CommissionRate\r\n";
        for($i=0;$i<$page_total;$i++){
            $pz=$i*$pagesize;
            $arr = $listObj->$listFunction($data,$pz,$pagesize);
            $arr = $arr['data'];
            foreach ($arr as $v) {
                $tmp = array(
                    $v['alias'],
                    $v['domain'],
                    $v['Manager'],
                    $v['Status'],
                    $v['SiteOption'],
                    $v['Sales'],
                    $v['Commission'],
                    $v['orders'],
                    $v['clicks'],
                    $v['realclicks'],
                    $v['rob'],
                    $v['robp'],
                    $v['epc'],
                    $v['commrate'],
                );
                echo '"' . join('","',$tmp) . '"' . "\n";
            }
        }
    }


    function store_performance($jsonStr){
        error_reporting(E_ALL^E_NOTICE);
        $query = $this->object_array($jsonStr);
        $type = isset($query['type']) ? $query['type'] : 1;
        $dataType = isset($query['data']) ? $query['data'] : 1;
        $startDate = isset($query['start_date']) ? $query['start_date'] : '';
        $endDate = isset($query['end_date']) ? $query['end_date'] : '';
        $network = isset($query['networkid']) ? $query['networkid'] : 0;
        $storeName = isset($query['store_name']) ? trim($query['store_name']) : '';
        $publisher = isset($query['publisher']) ? trim($query['publisher']) : '';
        switch ($type) {
            case 1:
                $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-18 DAYS"));
                $ed = !empty($endDate) ? $endDate : date('Y-m-d', strtotime("-9 DAYS"));
                break;
            case 2:
                $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-9 WEEKS"));
                $ed = !empty($endDate) ? $endDate : date('Y-m-d');
                break;
            case 3:
                $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-6 MONTHS"));
                $ed = !empty($endDate) ? $endDate : date('Y-m-d');
                break;
        }
        $sQuery = array(
            'store_name' => $storeName,
            'start_date' => $sd,
            'end_date' => $ed,
            'type' => $type,
            'network' => $network,
            'publisher' => $publisher,
            'export' => 1
        );
        $listObj = new StoreEpc();
        $count = $listObj->getActiveStoreIdCount($sQuery);
        $pageSize = 500;
        $pageTotal = ceil($count/$pageSize);
        $cname = 'Store_Performance'.date('Y-m-d',time());
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=$cname.csv");
        //获取日期列表
        $dateTitles = $listObj->getDateTitle($sd,$ed,$type);
        echo 'Store,"' . join('","',$dateTitles) . '"' . "\n";
        $pQuery = array(
            'store_name' => $storeName,
            'start_date' => $sd,
            'end_date'   => $ed,
            'type'       => $type,
            'network'    => $network,
            'limit'      => $pageSize,
        );
        for ($i = 1; $i <= $pageTotal; $i++) {
            $pQuery['offset'] = $i;
            //获得分页商家ID
            $storeIds = $listObj->getActiveStoreId($pQuery);
            //获得分页商家信息
            $storeArray = $listObj->getStore($storeIds);
            $stores = isset($storeArray['s']) ? $storeArray['s'] : array();
            $programs = isset($storeArray['p']) ? $storeArray['p'] : array();
            if (!empty($programs)) {
                $dQuery = array(
                    'start_date' => $sd,
                    'end_date'   => $ed,
                    'program_id'   => $programs,
                    'type' => $type
                );
                $stores_data = $listObj->getStoreEpcData($dQuery);
            }
            //分组格式化数据
            if (!empty($stores)) {
                foreach ($stores as $store) {
                    $store_id = isset($store['ID']) ? $store['ID'] : 0;
                    $store_name = isset($store['Name']) ? $store['Name'] : md5($store_id);
                    $store_program = isset($store['program']) ? $store['program'] : array();
                    if (empty($store) || empty($store_id)) {
                        continue;
                    }
                    if (!isset($output[$store_name])) {
                        $output[$store_name] = array();
                    }
                    $dArray = array();
                    foreach ($dateTitles as $title) {
                        $rv = $ck = $ecp = 0;
                        foreach ($store_program as $sp) {
                            if (isset($stores_data[$sp . '_' . $title])) {
                                $rv += isset($stores_data[$sp . '_' . $title]['rv']) ? $stores_data[$sp . '_' . $title]['rv'] : 0;
                                $ck += isset($stores_data[$sp . '_' . $title]['ck']) ? $stores_data[$sp . '_' . $title]['ck'] : 0;
                            }
                        }
                        $epc = $ck != 0 ? round($rv/$ck,4) : 0;
                        $value = 0;
                        switch ($dataType) {
                            case 1 :
                                $value = $epc;
                                break;
                            case 2 :
                                $value = $rv;
                                break;
                            case 3 :
                                $value = $ck;
                                break;
                        }
                        array_push($dArray, $value);

                    }
                    echo $store_name . ',"' . join('","',$dArray) . '"' . "\n";
                }
            }

        }
    }

    function performance_adv($listObj, $listFunction, $jsonStr){
        error_reporting(E_ALL^E_NOTICE);
        $data = $this->object_array($jsonStr);
        $aff = '';
        $country = '';
        if(strstr($jsonStr['affiliate'],',')){
            $affarr = explode(',',$jsonStr['affiliate']);
            foreach($affarr as $k){
                $aff.=$k.',';
            }
            $aff = rtrim($aff,',');
        }else if($jsonStr['affiliate'] === 'null'){
            $aff = '';
        }else{
            $aff = $jsonStr['affiliate'];
        }
        if(strstr($jsonStr['country'],',')){
            $conarr = explode(',',$jsonStr['country']);
            foreach($conarr as $k){
                if($k == 'UK'){
                    $country.='"gb","uk",';
                    continue;
                }
                $country.='"'.strtolower($k).'",';
            }
            $country = rtrim($country,',');
        }else if($jsonStr['country'] == 'null'){
            $country = '';
        }else{
            if($jsonStr['country'] == 'UK'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($jsonStr['country']).'"';
            }
        }
        $data['affiliate'] = $aff;
        $data['country'] = $country;
        $data['p'] = 0;
        $arr = $listObj->$listFunction($data,0,1);//防止内存爆掉，每次取1k条
        $pagesize = 1000;
        $page_total = ceil($arr['count']/$pagesize);
        $cname = 'Performance_Advertiser'.date('Y-m-d',time());
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition:  attachment;  filename=$cname.csv");
        echo "Advertiser,Sales,Commission,Orders,Total Clicks,Real Clicks,Robot,May Be Robot,Epc,CommissionRate,Cooperation Status\r\n";
        for($i=0;$i<$page_total;$i++){
            $pz=$i*$pagesize;
            $arr = $listObj->$listFunction($data,$pz,$pagesize);
            $arr = $arr['data'];;
            foreach ($arr as $v) {
                $tmp = array(
                    $v['alias'],
                    $v['Sales'],
                    $v['Commission'],
                    $v['orders'],
                    $v['clicks'],
                    $v['realclicks'],
                    $v['rob'],
                    $v['robp'],
                    $v['epc'],
                    $v['commrate'],
                    $v['status']
                );
                echo '"' . join('","',$tmp) . '"' . "\n";
            }
        }
    }

    function performance_aff($listObj, $listFunction, $jsonStr){
        error_reporting(E_ALL^E_NOTICE);
        $data = $this->object_array($jsonStr);
        $aff = '';
        $country = '';
        if(strstr($jsonStr['affiliate'],',')){
            $affarr = explode(',',$jsonStr['affiliate']);
            foreach($affarr as $k){
                $aff.=$k.',';
            }
            $aff = rtrim($aff,',');
        }else if($jsonStr['affiliate'] === 'null'){
            $aff = '';
        }else{
            $aff = $jsonStr['affiliate'];
        }
        if(strstr($jsonStr['country'],',')){
            $conarr = explode(',',$jsonStr['country']);
            foreach($conarr as $k){
                if($k == 'UK'){
                    $country.='"gb","uk",';
                    continue;
                }
                $country.='"'.strtolower($k).'",';
            }
            $country = rtrim($country,',');
        }else if($jsonStr['country'] == 'null'){
            $country = '';
        }else{
            if($jsonStr['country'] == 'UK'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($jsonStr['country']).'"';
            }
        }
        $data['affiliate'] = $aff;
        $data['country'] = $country;
        $data['p'] = 0;
        $arr = $listObj->$listFunction($data,0,1);//防止内存爆掉，每次取1k条
        $pagesize = 1000;
        $page_total = ceil($arr['count']/$pagesize);
        $cname = 'Performance_Network'.date('Y-m-d',time());
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition:  attachment;  filename=$cname.csv");
        echo "Network,Sales,Commission,Orders,Total Clicks,Real Clicks,Robot,May Be Robot,Epc,CommissionRate,Cooperation Status\r\n";
        for($i=0;$i<$page_total;$i++){
            $pz=$i*$pagesize;
            $arr = $listObj->$listFunction($data,$pz,$pagesize);
            $arr = $arr['data'];;
            foreach ($arr as $v) {
                $tmp = array(
                    $v['alias'],
                    $v['Sales'],
                    $v['Commission'],
                    $v['orders'],
                    $v['clicks'],
                    $v['realclicks'],
                    $v['rob'],
                    $v['robp'],
                    $v['epc'],
                    $v['commrate'],
                    $v['status']
                );
                echo '"' . join('","',$tmp) . '"' . "\n";
            }
        }
    }
    
    function get_transaction_csv_export($listObj, $listFunction, $jsonStr)
    {
        $date = date("Ymd-H:i:m");
        $data = $this->object_array($jsonStr);
        $data['p'] = 0;
        $data['download'] = 1;
        $aff = '';
        $country = '';
        if(strstr($jsonStr['aff'],',')){
            $affarr = explode(',',$jsonStr['aff']);
            foreach($affarr as $k){
                $aff.=$k.',';
            }
            $aff = rtrim($aff,',');
        }else if($jsonStr['aff'] === 'null'){
            $aff = '';
        }else{
            $aff = $jsonStr['aff'];
        }
        if(strstr($jsonStr['country'],',')){
            $conarr = explode(',',$jsonStr['country']);
            foreach($conarr as $k){
                if($k == 'UK'){
                    $country.='"gb","uk",';
                    continue;
                }
                $country.='"'.strtolower($k).'",';
            }
            $country = rtrim($country,',');
        }else if($jsonStr['country'] == 'null'){
            $country = '';
        }else{
            if($jsonStr['country'] == 'UK'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($jsonStr['country']).'"';
            }
        }
        $stateArr = array();
        if(strstr($jsonStr['state'],',')){
            $stateArr = explode(',',$jsonStr['state']);
        }else {
            $jsonStr['state']!='null' && $stateArr = array($jsonStr['state']);
        }
        $data['state'] = $stateArr;
        $data['affiliate'] = $aff;
        if(isset($data['cid']) && !empty($data['cid'])){
            $sql = "select CountryCode from country_codes where CountryName ='{$jsonStr['cid']}'";
            $c = $listObj->getRow($sql);
            if($c['CountryCode'] == 'UK' || $c['CountryCode'] == 'GB'){
                $country = '"gb","uk"';
            }else{
                $country = '"'.strtolower($c['CountryCode']).'"';
            }
            $data['country'] = $country;
            $data['cid'] = 1;
        }else{
            $data['country'] = $country;
            $data['cid'] = 1;
        }
        $data['download'] == 0;
        $arr = $listObj->$listFunction($data,1,1000);//防止内存爆掉，每次取1k条
        $page_total = ceil($arr['total_num']/1000);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= {$listFunction}{$date}.csv");
        echo "ID,CreateTime,ClickTime,SID,LinkID,Sales,Commission,Commission-B,Commission-P,Commission-R,SiteAlias,Site Type,Network,State,Advertiser,Country,ClickPage,Commission History\n";
        do {
            $arr = $listObj->$listFunction($data,$data['p']*1000,1000);
            $arr = $arr['data'];
            foreach ($arr as $v) {
                $reason= empty($v['TradeCancelReason']) ? 'NONE' : $v['TradeCancelReason'];
                $state = isset($v['State']) ? $v['State'] : '';
                $paiddate= isset($v['PaidDate']) ? $v['PaidDate'] : '0000-00-00';
                if ($paiddate!= '0000-00-00') {
                    $state.= '(PAID)';
                }
                if ($v['State'] ===  'CANCELLED') {
                    $state .= '|Reason:'.$reason;
                }
                $tmp = array(
                    $v['BRID'],
                    $v['Created'],
                    $v['Visited'],
                    $v['SID'],
                    $v['linkId'],
                    $v['Sales'],
                    $v['Commission'],
                    $v['TaxCommission'],
                    $v['ShowCommission'],
                    $v['RefPublisherId'] > 0?$v['RefCommission']:0,
                    $v['SiteAlias'],
                    isset($v['SiteOption']) ? $v['SiteOption'] : '',
                    $v['AffName'],
                    $state,
                    $v['StoreName'],
                    $v['Country'],
                    $v['pageUrl'],
                    $v['comstatus'],
                    );
                echo '"' . join('","',$tmp) . '"' . "\n";
            }
            $data['p']++;
        } while ($data['p'] <= $page_total);
    }

    function getClawerAff()
    {
        $data = array();
        $sql = "SELECT id,Name FROM wf_aff where IsActive = 'YES' ORDER BY Name ASC";
        $data = $this->objMysql->getRows($sql, "id");
        return $data;
    }
    
    /*function getClawerAffConf(){
 
        $pengingLinksObj = new Mysql('pendinglinks', 'localhost', 'bdg_go', 'shY12Nbd8J');
        $sql = "select * from aff_crawl_config where status = 'active'";
        $crawl_config = $pengingLinksObj->getRows($sql, "AffId");
        return $crawl_config;
    }*/
    
    function getCrawlerLog($param){
        
        $ret = array();
        $list = array();
        $d = new DateTime();
        $date = isset($param['date']) && !empty($param['date']) ? $param['date']:$d->format('Y-m-d');
        $where = '1 = 1';
        $where .= " and `date` = '".$date."'";
        if(isset($param['affid']) && !empty($param['affid']))
            $where .= " and affid =".$param['affid'];
        
        if(isset($param['method']) && !empty($param['method']))
            $where .= " and method ='".$param['method']."'";
        
        if(isset($param['status']) && !empty($param['status']))
            $where .= " and `status` ='".$param['status']."'";
        
        //if(isset($param['platform']) && !empty($param['platform']))
        //    $where .= " and platform ='".$param['platform']."'";
        
        $sql = "select * from crawl_script_run_log where $where and platform = 'BR'";
        //echo $sql;
        $ret = $this->objMysql->getRows($sql);
        
        return $ret;
        
    }
    
    function getAnaylezeProgram($post){
        
        $data = array();
        $id = $post['id'];
        if(!$id) return $data;
        if($post['type']== 1)
            $field = 'ext1';
        elseif($post['type']== 2)
            $field = 'ext2';
        elseif($post['type']== 3)
            $field = 'ext3';
        elseif($post['type']== 4)
            $field = 'ext4';
        $sql = "select $field from crawl_script_run_log where id=$id";
       // echo $sql;exit;
        $ret = $this->objMysql->getRows($sql);
        //print_r($ret);exit;
        $data = unserialize($ret[0][$field]);
        $pid=array();
        foreach ($data as $k=>$v){
            $pid[] = $k;
        }
        if(!$pid) return array();
        $sql = "select id,affid,idinaff,name,homepage,StatusInAff,Partnership from program where id in (".implode(',', $pid).")";
        $ret = $this->objMysql->getRows($sql);
        
        return $ret;
    }
    
    function getEncodeId($retry = 0){
        
        $key = substr(strtotime(" - " . date("s") . "days"), -5);
        $encodeid = '';
        $encodeid = $this->random(8, $key);
        $sql = "select encodeid from content_feed_new where encodeid = '{$encodeid}'";
        $tmp_arr = array();
        $tmp_arr = $this->objMysql->getFirstRow($sql);
        
        $sql = "select encodeid from product_feed where encodeid = '{$encodeid}'";
        $tmp_arr_product = array();
        $tmp_arr_product = $this->objMysql->getFirstRow($sql);
        
        if(count($tmp_arr) || count($tmp_arr_product)){
            $retry++;
            if($retry < 10){
                $encodeid = $this->getEncodeId($retry);
            }else{
                echo 'warning: retry > 10 , ';
                exit;
            }
        }
        return $encodeid;
    }
    
    function random($length)
    {
        $random = '';
        $pool = '123456789';
        $pool .= substr(microtime(true), -2);//'1234567890';
    
        //srand ((double)microtime()*1000000);
        for($i = 0; $i < $length; $i++)
        {
            $random .= substr($pool,(rand()%(strlen ($pool))), 1);
        }
    
        return $random;
    }

}
