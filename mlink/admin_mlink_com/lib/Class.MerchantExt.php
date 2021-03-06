<?php
class MerchantExt extends LibFactory{

    function __construct(){
        $this->objMysql = $GLOBALS['db'];
        $this->time_zone = array(
            "de" => "Europe/Berlin",
            "ca" => "America/Toronto",
            "uk" => "Europe/London",
            "ie" => "Europe/London",
            "nz" => "Pacific/Auckland",
            "us" => "America/Los_Angeles",
            "fr" => "Europe/Paris",
            "in" => "Indian/Antananarivo",
            "au" => "Australia/Sydney");
    }

    function TranslateTime($date,$time_from = "us",$time_to = "us"){
        $time_zone_arr = $this->time_zone;
        if(!isset($time_zone_arr[$time_from])) mydie("No such site");
        $datetime = new DateTime($date,new DateTimeZone($time_zone_arr[$time_from]));
        $datetime->setTimezone(new DateTimeZone($time_zone_arr[$time_to]));
        return $datetime;
    }

    function GetTimeZone($site){
        $time_zone_arr = $this->time_zone;
        if(isset($time_zone_arr[$site]) && $time_zone_arr[$site])
            return $time_zone_arr[$site];
        else{
            return $time_zone_arr['us'];
        }
    }
    function GetContentNew($opts,$page = 1,$pagesize = 20,$export=false,$searchCount=false){
        $this->query("SET NAMES latin1");
        $return = '';
        $where = '';
        $where2 = '';
        //查询各个store的数量
        if($searchCount){
            if(isset($opts['storeIds']) && $opts['storeIds']!=""){
                $where.=" AND a.StoreId in (".$opts['storeIds'].") ";
            }
            $sql = "SELECT a.StoreId,count(a.StoreId) StoreIdCount from content_feed_new a LEFT JOIN store f ON a.`StoreId` = f.id
            where a.`status`='active' $where GROUP BY a.StoreId";
            return $this->objMysql->getRows($sql,'StoreId');
        }
        $sortArray = array();
        $sortBy = isset($opts['sortBy']) ? $opts['sortBy'] : '';
        $sortQuery = '';
        if (!empty($sortBy)) {

            switch ($sortBy) {
                case 'addtime' :
                    $sortQuery = 'a.AddTime DESC';
                    break;
                case 'sales' :
                    $sortQuery = 't.sales DESC';
                    break;
                case 'commission' :
                    $sortQuery = 't.commission DESC';
                    break;
                case 'clicks' :
                    $sortQuery = '(t.clicks - t.rob) DESC';
                    break;
            }

        }
        !empty($sortQuery) && array_push($sortArray, $sortQuery);
         if(isset($opts['download']) && !empty($opts['download'])){
             array_push($sortArray, 'a.EndDate DESC');
        }else{
             $dir = $opts['order'];
             $oname = "a.".$opts['oname'];
             array_push($sortArray, $oname.' '. $dir);
        }
        $sortQuerySql = empty($sortArray) ? '' : implode(',', $sortArray);
        if (isset($opts['categories']) && !empty($opts['categories'])){
            $category_id = trim($opts['categories'],", \t\n\r\0\x0B");
            $categoryArr = explode(',',trim($category_id,','));
            if(!empty($categoryArr))
            {
                $where .= " AND(";
                foreach($categoryArr as $cateid)
                {
                    $where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
                }
                $where = rtrim($where,'OR').")";
            }
        }
        if (isset($opts['type']) && !empty($opts['type'])){
            $type = $opts['type'];
            $where .= " AND a.Type='$type'";
        }
        if (isset($opts['aff']) && !empty($opts['aff'])){
            $pid = $opts['aff'];
            $sql = "select ID from program where Affid in($pid)";
            $res = $this->getRows($sql);
            if(!empty($res)){
                $pid = ' and ProgramId IN(';
                foreach($res as $k){
                    $pid.=$k['ID'].',';
                }
                $where.= rtrim($pid,',').')';
            }else{
                $where.= ' and 0=1';
            }
        }
        if(isset($opts['country']) && !empty($opts['country'])){
            $where .= " AND (a.`country` like '%".addslashes($opts['country'])."%')";
        }
        if (isset($opts['status']) && !empty($opts['status'])){
            $status = $opts['status'];
            $where.= " AND f.SupportType = '$status'";
        }
        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $sql_names_set = 'SET NAMES utf8';
            $this->query($sql_names_set);
            $key = addslashes(trim($opts['keyword']));
            $sql = "select id from store where `Name` like '%$key%' or NameOptimized like '%$key%' AND StoreAffSupport='YES'";
            $res =  $this->getRows($sql);
            $sql_names_set = 'SET NAMES latin1';
            $this->query($sql_names_set);
            if(!empty($res)){
                $sid = "";
                foreach($res as $k){
                    $sid.=$k['id'].',';
                }
                $sid = rtrim($sid,',');
                $where.=" and a.StoreId in($sid)";
            }else{
                $return['data'] = '';
                $return['count'] = '0';
                return  $return;
            }
        }
        if(isset($opts['pstatus']) && !empty($opts['pstatus'])){
            $p = $opts['pstatus'];
            $time = date('Y-m-d H:i:s');
            if($p == 1){
                $where .= "AND (a.StartDate <= '$time' AND a.EndDate >= '$time' OR a.StartDate = '0000-00-00 00:00:00' AND a.EndDate >= '$time' OR a.StartDate <= '$time' AND a.EndDate = '0000-00-00 00:00:00')";
            }else if($p == 2){
                $where .= " AND a.StartDate > '$time'";
            }else if($p == 3){
                $where .= " AND a.EndDate < '$time' AND a.EndDate!='0000-00-00 00:00:00'";
            }
        }
        if(isset($opts['keywords']) && !empty($opts['keywords'])){
            $opts['keywords'] = trim($opts['keywords']);
            $where .= " AND (a.`Title` LIKE '%".addslashes($opts['keywords'])."%' OR a.`Desc` LIKE '%" . addslashes($opts['keywords'])."%')";
        }
        if (isset($opts['linkid']) && !empty($opts['linkid'])) {
            $linkid = intval($opts['linkid']);
            $where .= " AND a.EncodeId = " .$linkid;
        }

        $dateType = isset($opts['datetype']) ? $opts['datetype'] : '1';
        $joinType = ' LEFT ';
        switch ($dateType) {
            case '1' :
                if(isset($opts['start']) && !empty($opts['start'])){
                    $where .= " AND a.`StartDate` >= '".date('Y-m-d',strtotime($opts['start']))."'";
                }
                if(isset($opts['end']) && !empty($opts['end'])){
                    $where .= " AND a.`StartDate` <= '".date('Y-m-d 23:59:59',strtotime($opts['end']))."'";
                }
                $joinType = ' LEFT ';
                break;
            case '2' :
                if(isset($opts['start']) && !empty($opts['start'])){
                    $where2.= " AND `createddate` >= '".date('Y-m-d',strtotime($opts['start']))."'";
                }
                if(isset($opts['end']) && !empty($opts['end'])){
                    $where2.= " AND `createddate` <= '".date('Y-m-d',strtotime($opts['end']))."'";
                }
                $joinType = ' RIGHT ';
                break;
        }
        if (isset($opts['publisher']) && !empty($opts['publisher'])) {
            $publisher = trim($opts['publisher']);
            $pSql = "SELECT  b.`ApiKey`  FROM publisher a LEFT JOIN publisher_account b ON a.ID = b.PublisherId
                      WHERE b.`ApiKey` IS NOT NULL AND b.Alias LIKE '%$publisher%' OR a.`Name` LIKE '%$publisher%' OR a.`Domain` LIKE '%$publisher%' 
                      OR a.`Email` LIKE '%$publisher%' OR a.`UserName` LIKE '%$publisher%' OR b.`Domain` LIKE '%$publisher%' 
                      OR b.`Apikey` LIKE '%$publisher%' GROUP BY b.`ApiKey`";
            $joinType = ' RIGHT ';
            $pRows = $this->getRows($pSql);
            if(!empty($pRows)){
                $pKeys = ' and site IN("';
                foreach($pRows as $pRow){
                    $pKeys.=$pRow['ApiKey'].'","';
                }
                $where2.= rtrim($pKeys,',"').'")';
            }else{
                $where2.= ' and 0=1';
            }
        }
        if(isset($opts['coupon']) && !empty($opts['coupon'])){
            $opts['coupon'] = trim($opts['coupon']);
            $where .= " AND a.`CouponCode` LIKE '%".addslashes($opts['coupon'])."%' ";
        }
        if(isset($opts['id']) && !empty($opts['id'])){
            $where.= " AND a.StoreId=".$opts['id'];
        }
        if(isset($opts['pid']) && !empty($opts['pid'])){
            $where.= " AND a.ID=".$opts['pid'];
        }
        if (isset($opts['language']) && !empty($opts['language'])){
            $where.= " AND a.language='".addslashes($opts['language'])."'";
        }

        if (isset($opts['source']) && !empty($opts['source'])){
            $where.= " AND a.source='".addslashes($opts['source'])."'";
        }
        //total
        $mkWhereSql = mk_publisher_where();
        $sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
        $res = $this->getRows($sql);
        if(!empty($res)){
            $keyid = 'site NOT IN(';
            foreach ($res as $k) {
                $keyid .= '"' . $k['ApiKey'] . '",';
            }
            $keyid = rtrim($keyid, ',') . ")";
        }
        $sql = "SELECT
            COUNT(*) AS `c`
            FROM
            content_feed_new a
            LEFT JOIN store f
            ON a.`StoreId` = f.id
            $joinType JOIN (select linkid,sum(clicks) as clicks,sum(clicks_robot) as rob,sum(clicks_robot_p) as robp,sum(c_sales) as sales,sum(c_revenues) as commission,sum(c_orders) as orders from statis_link WHERE $keyid $where2 group by linkid) as t
            ON t.linkid = a.EncodeId
            WHERE a.`status`='active' $where ORDER BY $sortQuerySql";
        $count = $this->objMysql->getFirstRow($sql);
        if(empty($count['c'])){
            $return['data'] = '';
            $return['count'] = '0';
            return  $return;
        }
        $return['count'] = ceil($count['c']);
        $sql = "SELECT
            a.*,f.id as sid
            FROM
            content_feed_new a
            LEFT JOIN store f
            ON a.`StoreId` = f.id
            $joinType JOIN (select linkid,sum(clicks) as clicks,sum(clicks_robot) as rob,sum(clicks_robot_p) as robp,sum(c_sales) as sales,sum(c_revenues) as commission,sum(c_orders) as orders from statis_link WHERE $keyid $where2 group by linkid) as t
            ON t.linkid = a.EncodeId
            WHERE a.`status`='active' $where ORDER BY $sortQuerySql LIMIT $page,$pagesize";
        $content = $this->objMysql->getRows($sql);
        if($opts['download'] == 0){
            $sql_sum = "SELECT
            sum(t.clicks) as clicks,sum(t.rob) as rob,sum(t.robp) as robp,sum(t.sales) as sales,sum(t.commission) as commission,sum(t.orders) as orders 
            FROM
            content_feed_new a
            LEFT JOIN store f
            ON a.`StoreId` = f.id
            $joinType JOIN (select linkid,sum(clicks) as clicks,sum(clicks_robot) as rob,sum(clicks_robot_p) as robp,sum(c_sales) as sales,sum(c_revenues) as commission,sum(c_orders) as orders from statis_link WHERE $keyid $where2 group by linkid) as t
            ON t.linkid = a.EncodeId
            WHERE a.`status`='active' $where";
            $res = $this->getRow($sql_sum);
            $return['sales'] = number_format($res['sales'],2);
            $return['commission'] = number_format($res['commission'],2);
            $return['clicks'] = number_format($res['clicks']);
            $return['rclicks'] = number_format($res['clicks'] - $res['rob']);
            $return['rob'] = number_format($res['rob']);
            $return['robp'] = number_format($res['robp']);
            $return['orders'] = number_format($res['orders']);
        }
        $linkid = array();
        $pid = array();
        foreach($content as $v ){
            if($v['ProgramId'] == 0 && $v['sid'] == 0){
                continue;
            }
            $linkid[] = $v['EncodeId'];
            $pid[] = array('pid'=>$v['ProgramId'],'sid'=>$v['sid']);
        }
        $lid = join(',',$linkid);
        $sql_det = "select linkid,sum(clicks) as clicks,sum(clicks_robot) as rob,sum(clicks_robot_p) as robp,sum(c_sales) as sales,sum(c_revenues) as commission,sum(c_orders) as orders from statis_link WHERE linkid in($lid) and $keyid $where2 group by linkid";
        $linkres = $this->objMysql->getRows($sql_det,'linkid');
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        foreach($pid as $k=>$v){
           if(!empty($v['sid'])){
            if($v['pid'] == 0){
                //优先获取类型是Perecnt
                $sql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`, d.CommissionType, d.CommissionUsed, d.CommissionCurrency FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell d ON d.`ProgramId` = b.`ProgramId` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$v['sid']} AND d.`CommissionType` = 'Percent' AND g.StatusInAff = 'Active' AND g.Partnership = 'Active' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                $res = $this->getRow($sql);
                if(!empty($res)){
                    $sql = "SELECT d.CommissionType, d.CommissionUsed FROM r_store_program b INNER JOIN `program_manual` d ON d.`ProgramId` = b.`ProgramId` WHERE b.`StoreId` = {$v['sid']} AND d.`CommissionType` = 'Percent' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                    $mres = $this->getRow($sql);
                    if(empty($mres)){
                        $comarray[$k]['com'] = $res['CommissionUsed']."%";
                        $comarray[$k]['sname'] = $res['name'];
                        $comarray[$k]['aname'] = $res['aname'];
                    }else{
                        if($mres['CommissionUsed'] > $res['CommissionUsed']){
                            $comarray[$k]['com'] = $mres['CommissionUsed']."%";
                            $comarray[$k]['sname'] = $res['name'];
                            $comarray[$k]['aname'] = $res['aname'];
                        }else{
                            $comarray[$k]['com'] = $res['CommissionUsed']."%";
                            $comarray[$k]['sname'] = $res['name'];
                            $comarray[$k]['aname'] = $res['aname'];
                        }
                    }
                }else{
                    $sql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`,d.CommissionUsed, d.CommissionCurrency FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell d ON d.`ProgramId` = b.`ProgramId` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$v['sid']} AND d.`CommissionType` = 'Value' AND g.StatusInAff = 'Active' AND g.Partnership = 'Active' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                    $res = $this->getRow($sql);
                    if(!empty($res)){
                        //检查是否手动有更新Commission
                        $sql = "SELECT d.CommissionType, d.CommissionUsed, d.CommissionCurrency AS `count` FROM r_store_program b INNER JOIN `program_manual` d ON d.`ProgramId` = b.`ProgramId` WHERE b.`StoreId` = {$v['sid']} AND d.`CommissionType` = 'Value' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                        $mres = $this->getRow($sql);
                        if(empty($mres)){
                            $comarray[$k]['com'] = !empty($res['CommissionCurrency'])?$res['CommissionCurrency'].$res['CommissionUsed']:'USD'.$res['CommissionUsed'];
                            $comarray[$k]['sname'] = $res['name'];
                            $comarray[$k]['aname'] = $res['aname'];
                        }else{
                            if($mres['CommissionUsed'] > $res['CommissionUsed']){
                                $comarray[$k]['com'] = !empty($mres['CommissionCurrency'])?$mres['CommissionCurrency'].$mres['CommissionUsed']:'USD'.$mres['CommissionUsed'];
                                $comarray[$k]['sname'] = $res['name'];
                                $comarray[$k]['aname'] = $res['aname'];
                            }else{
                                $comarray[$k]['com'] = !empty($res['CommissionCurrency'])?$res['CommissionCurrency'].$res['CommissionUsed']:'USD'.$res['CommissionUsed'];
                                $comarray[$k]['sname'] = $res['name'];
                                $comarray[$k]['aname'] = $res['aname'];
                            }
                        }
                    }else{
                        $nsql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`,d.CommissionUsed, d.CommissionCurrency FROM store a INNER JOIN r_store_domain ON sd.StoreId = a.`ID` INNER JOIN r_store_program b ON b.`DID` = sd.`DomainId` INNER JOIN program_intell d ON d.`ProgramId` = b.`PID` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$v['sid']} AND d.`CommissionType` = 'Value'  ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                        $nres = $this->getRow($nsql);
                        $comarray[$k]['com'] = '--';
                        $comarray[$k]['sname'] = $nres['name'];
                        $comarray[$k]['aname'] = $nres['aname'];
                    }
                }
            }else{
                $sql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`,d.CommissionUsed,d.CommissionType,d.CommissionCurrency FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell d ON d.`ProgramId` = b.`ProgramId` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$v['sid']} and b.ProgramId={$v['pid']}  AND g.StatusInAff = 'Active' AND g.Partnership = 'Active' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                $res = $this->getRow($sql);
                $checksql = "SELECT d.CommissionType, d.CommissionUsed, d.CommissionCurrency AS `count` FROM `program_manual` d WHERE d.`ProgramId` = {$v['pid']} ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                $checkres = $this->getRow($checksql);
                if(empty($res)){
                    $nsql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`,d.CommissionUsed,d.CommissionType,d.CommissionCurrency FROM store a INNER JOIN r_store_domain sd ON sd.StoreId = a.`ID` INNER JOIN r_domain_program b ON b.`DID` = sd.`DomainId` INNER JOIN program_intell d ON d.`ProgramId` = b.`PID` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$v['sid']} and b.PID={$v['pid']}   ORDER BY d.`CommissionUsed` DESC LIMIT 1";
                    $nres = $this->getRow($nsql);
                    $comarray[$k]['com'] = '--';
                    $comarray[$k]['sname'] = $nres['name'];
                    $comarray[$k]['aname'] = $nres['aname'];
                }else{
                    //intell默认利润率
                    if($res['CommissionType'] == 'Percent'){
                        $check = $res['CommissionUsed'].'%';
                    }else{
                        $check = !empty($res['CommissionCurrency'])?$res['CommissionCurrency'].$res['CommissionUsed']:$res['CommissionUsed'].'%';
                    }
                    //如果人工利润率则采用人工
                    if(!empty($checkres)){
                        if($checkres['CommissionType'] == 'Percent'){
                            $check = $checkres['CommissionUsed'].'%';
                        }else if(!empty($checkres['CommissionCurrency'])){
                            $check = $checkres['CommissionCurrency'].$checkres['CommissionUsed'];
                        }
                    }
                    $comarray[$k]['com'] = $check;
                    $comarray[$k]['sname'] = $res['name'];
                    $comarray[$k]['aname'] = $res['aname'];
                }
            }
           }
        }

        foreach ($content as $k=>$v){
            if(isset($linkres[$v['EncodeId']])){
                $content[$k]['com'] = '$'.number_format($linkres[$v['EncodeId']]['commission'],2);
                $content[$k]['sales'] = '$'.number_format($linkres[$v['EncodeId']]['sales'],2);
                $content[$k]['orders'] = number_format($linkres[$v['EncodeId']]['orders']);
                $content[$k]['clicks'] = number_format($linkres[$v['EncodeId']]['clicks']);
                $content[$k]['rclicks'] = number_format($linkres[$v['EncodeId']]['clicks'] - $linkres[$v['EncodeId']]['rob']);
                $content[$k]['rob'] = number_format($linkres[$v['EncodeId']]['rob']);
                $content[$k]['robp'] = number_format($linkres[$v['EncodeId']]['robp']);
            }else{
                $content[$k]['com'] = '$0.00';
                $content[$k]['sales'] = '$0.00';
                $content[$k]['orders'] = 0;
                $content[$k]['clicks'] = 0;
                $content[$k]['rclicks'] = 0;
                $content[$k]['rob'] = 0;
                $content[$k]['robp'] = 0;
            }
            $content[$k]['commission'] = $comarray[$k]['com'];
            $content[$k]['Advertiser_Name'] = $comarray[$k]['sname'];
            $content[$k]['aname'] = $comarray[$k]['aname'];
        }
        $return['data'] = $content;
        return $return;
    }

    function  GetContentCsvFileNew($opts){
        set_time_limit(300);
        $opts['download'] = 1;
        $info = $this->GetContentNew($opts,0,1000);
        $count = $info['count'];
        $page_total = ceil($count/1000);
        $page = 0;
        header('Pragma:public');
        header('Expires:0');
        header("Content-type:text/csv");
        header("Content-type:  application/octet-stream;");
        header('Content-Transfer-Encoding: binary');
        header("Content-Disposition: attachment; filename= ContentFeed.csv");
        print(chr(0xEF).chr(0xBB).chr(0xBF)); //add utf8 bom in csv file
        //csv文件头部
        $dataHead = array('Advertiser','Network','Title','CouponCode','Source','desc','Total Clicks','Real Clicks','Robot','May Be Robot','Orders','Sales','Commission','StartDate','EndDate','AffUrl','Original Url','Link');
        echo implode(',',$dataHead)."\n";
        do{
            $data = $this->GetContentNew($opts,$page*1000,1000,true);
            $content = $data['data'];
            foreach ($content as $k => &$v){
                $data = array(
                    isset($v['Advertiser_Name'])?$v['Advertiser_Name']:'',
                    isset($v['aname'])?$v['aname']:'',
                    str_replace('"','""',$v['Title']),
                    $v['CouponCode'],
                    $v['source'],
                    str_replace('"','""',$v['Desc']),
                    $v['clicks'],
                    $v['rclicks']  ,
                    $v['rob'],
                    $v['robp'],
                    $v['orders'],
                    $v['sales'],
                    $v['com'],
                    ($v['StartDate']!='0000-00-00 00:00:00')?$v['StartDate']:'N/A',
                    ($v['EndDate']!='0000-00-00 00:00:00')?$v['EndDate']:'N/A',
                    $v['AffUrl'],
                    $v['OriginalUrl'],
                    "http://r.brandreward.com/?key=".$opts['key'].'&linkid='.urlencode($v['EncodeId'])
                );
                echo '"'.implode('","',$data).'"'."\n";
            }
            $page++;
        }while($page < $page_total);
        exit();
    }

    function GetAdvertiserCsvCsvFile($search)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $where_str_store = '';
        $where2 = '';
        $dir = "";
        if (isset($search['stime']) && !empty($search['stime']) && isset($search['etime']) && !empty($search['etime'])) {
            $type = 2;
        } else {
            $type = 1;
        }
        if (isset($search['advertiser']) && !empty($search['advertiser'])) {
            $key = addslashes($search['advertiser']);
            $where_str_store .= " AND( b.Name like '%$key%' OR b.NameOptimized like '%$key%' OR b.Domains like '%$key%')";
            if($type == 2){
                $sql = "SELECT DISTINCT a.`DomainId` AS did FROM r_store_domain a LEFT JOIN `store` b ON a.`StoreId` = b.`ID` WHERE b.`Name` LIKE '%$key%' OR b.`NameOptimized` LIKE '%$key%'";
                $row = $this->getRows($sql);
                if(!empty($row)){
                    $did = '';
                    foreach($row as $k){
                        !empty($k['did']) && $did.=$k['did'].',';
                    }
                    $did = rtrim($did,',');
                    $where2.=" and a.domainid in($did)";
                }
            }

        }
        if (isset($search['status']) && !empty($search['status'])) {
            $status = $search['status'];
            $where_str_store .= " AND b.SupportType = '$status'";
        }
        if (isset($search['catestu']) && !empty($search['catestu'])) {
            $status = $search['catestu'];
            if ($status == 'YES') {
                $where_str_store .= " AND b.CategoryId IS NOT NULL AND b.CategoryId != ''";
            } elseif ($status == 'NO') {
                $where_str_store .= " AND (b.CategoryId IS NULL OR b.CategoryId = '')";
            }
        }
        if (isset($search['ppc']) && !empty($search['ppc'])) {
            if ($search['ppc'] == 'none') {
                $ppc = 0;
            } else {
                $ppc = $search['ppc'];
            }
            $where_str_store .= " AND b.PPCStatus = '$ppc'";
        }
        if (isset($search['category']) && !empty($search['category'])) {
            $categoryArr = explode(',', trim($search['category'], ','));
            if (!empty($categoryArr)) {
                $where_str_store .= " AND(";
                foreach ($categoryArr as $cateid) {
                    $where_str_store .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
                }
                $where_str_store = rtrim($where_str_store, 'OR') . ")";
            }
        }
        if (isset($search['aname']) && !empty($search['aname'])) {
            if ($search['aname'] == '1') {
                $where_str_store .= " AND b.NameOptimized != ''";
            } elseif ($search['aname'] == '2') {
                $where_str_store .= " AND ( b.NameOptimized IS NULL OR b.NameOptimized = '') ";
            }
        }
        if (isset($search['logo']) && !empty($search['logo'])) {
            if ($search['logo'] == '1') {
                $where_str_store .= " AND b.LogoName like '%,%'";
            } elseif ($search['logo'] == '2') {
                $where_str_store .= " AND b.LogoName =''";
            }
        }
        if (isset($search['country']) && !empty($search['country']) && $search['country'] != 'null') {
            $search['country'] = explode(',', $search['country']);
            $str = ' a.country in(';
            $where_str_store .= " AND(";
            foreach($search['country'] as $c){
                if(!empty($c)){
                    $where_str_store.= ' FIND_IN_SET("'.$c.'",b.CountryCode) OR';
                    if($c=='UK'){
                        $str.="'uk','gb',";
                        continue;
                    }
                    $str.="'".strtolower($c)."',";
                }
            }
            $where_str_store = rtrim($where_str_store,'OR').")";
            $where2.= ' and '.rtrim($str,',').")";
        }
        if (isset($search['networkid']) && !empty($search['networkid']) && $search['networkid'] != 'null') {
            $search['networkid'] = explode(',', $search['networkid']);
            $str = " AND (";
            foreach ($search['networkid'] as $c) {
                if (!empty($c)) {
                    $str .= ' FIND_IN_SET("' . $c . '",b.Affids) OR';
                }
            }
            $where_str_store .= rtrim($str, 'OR') . ")";

            $where2.= str_replace('b.Affids', 'a.affid', rtrim($str,'OR').")");
        }
        if (isset($search['storeid']) && !empty($search['storeid'])) {
            $where_str_store .= ' AND b.ID IN (' . join(',', $search['storeid']) . ')';
        }
        if (isset($search['datatype']) && !empty($search['datatype'])) {
            $datatype = $search['datatype'];
            if ($datatype == 1) {
                $key1 = 'IFNULL(b.PClicks,0) as clicks,IFNULL(b.Commission_publisher,0) as revenues ,IFNULL(b.Sales_publisher,0) as sales,IFNULL(b.PClicks_robot,0) as rob,IFNULL(b.PClicks_robot_p,0) as robp';
                $oname = "b.PClicks desc";
                if($type == 2){
                    $mkWhereSql = mk_publisher_where();
                    $sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
                    $res = $this->getRows($sql);
                    if(!empty($res)){
                        $keyid=' and a.site NOT IN(';
                        foreach($res as $k){
                            $keyid.='"'.$k['ApiKey'].'",';
                        }
                        $where2.= rtrim($keyid,',').")";
                    }
                }
                
            } else {
                $key1 = 'IFNULL(b.clicks,0) as clicks,IFNULL(b.commission,0) as revenues ,IFNULL(b.sales,0) as sales,IFNULL(b.Clicks_robot,0) as rob,IFNULL(b.Clicks_robot_p,0) as robp';
                $oname = "b.clicks desc";
            }
        }
        if (isset($search['cooperation']) && !empty($search['cooperation'])) {
            if ($search['cooperation'] == '1') {
                $where_str_store .= " AND b.StoreAffSupport = 'Yes'";
            } elseif ($search['cooperation'] == '2') {
                $where_str_store .= " AND b.StoreAffSupport = 'No'";
            }
        }
        $catesql = "select `ID`,`Name` from category_ext";
        $cateres = $this->objMysql->getRows($catesql, 'ID');
        $sql = "select CountryCode,CountryName from country_codes";
        $country = $this->objMysql->getRows($sql, 'CountryCode');
        $country['UK']['CountryName'] = 'United Kingdom';
        $country['GLOBAL']['CountryName'] = 'Global';
        $sql = "SELECT COUNT(*) FROM store b WHERE 1=1 " . $where_str_store;
        $count = current($this->getRow($sql));
        $page_total = ceil($count / 1000);
        print(chr(0xEF) . chr(0xBB) . chr(0xBF)); //add utf8 bom in csv file
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition:  attachment;  filename=Advertiser.csv");
        echo 'Advertiser,Homepage,Clicks,Robot,May Be Robot,Commission,Sales,Commission Rate,Current Status,PPC Status,Cooperation Status,Category,Country Commission' . "\n";
        for ($i = 0; $i <= $page_total; $i++) {
            $page = $i * 1000;
            $sql = "SELECT b.`ID` AS sid,b.StoreAffSupport,b.`Name` AS storeName,b.NameOptimized,b.LogoName,b.CategoryId,b.LogoName,b.`SupportType`,b.Domains,b.`PPC`,b.`PPCStatus`,$key1 FROM store b WHERE 1=1 $where_str_store ORDER BY $oname LIMIT  $page,1000";
            $res1 = $this->getRows($sql);
            $storeIdList = array();
            foreach ($res1 as $k => &$v) {
                array_push($storeIdList, $v['sid']);
                $sql = 'SELECT rsp.`StoreId`,rsp.`ProgramId`,rsp.`Outbound`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue` from r_store_program rsp
                    LEFT JOIN program_intell b on b.`ProgramId` = rsp.`ProgramId` WHERE rsp.`Outbound` != "" and rsp.`StoreId`=' . $v['sid'];
                $rs = $this->objMysql->getRows($sql);
                $commissionRangeArr = array();
                foreach ($rs as $val) {
                    if ($val['CommissionValue'] != '' && $val['CommissionValue'] != null) {
                        $commissionArr = explode("|", $val['CommissionValue'])[0];
                        $commissionValText = trim($commissionArr, "[]");
                        $commissionValArr = explode(",", $commissionValText);
                        foreach ($commissionValArr as $temp) {
                            preg_match("/\d+(\.\d+)?/", $temp, $number);
                            $unit = preg_replace("/[0-9. ]/", '', $temp);
                            $commissionRangeArr[$unit][number_format($number[0], 3)] = $temp;
                        }
                    } else {
                        if ($val['CommissionUsed'] == '0') {
                        } else {
                            if ($val['CommissionType'] == 'Value') {
                                if ($val['CommissionCurrency'] != '') {
                                    $commissionRangeAr[$val['CommissionCurrency']][number_format($val['CommissionUsed'], 3)] = $val['CommissionCurrency'] . $val['CommissionUsed'];
                                } else {
                                    $commissionRangeArr['USD'][number_format($val['CommissionUsed'], 3)] = "USD" . $val['CommissionUsed'];
                                }
                            } else {
                                $commissionRangeArr['%'][number_format($val['CommissionUsed'], 3)] = $val['CommissionUsed'] . '%';
                            }
                        }
                    }
                }

                $rate = '';
                foreach ($commissionRangeArr as $tempK => $tempV) {
                    ksort($tempV);
                    if (count($tempV) <= 1) {
                        $rate .= trim(current($tempV)) . ' || ';
                    } else {
                        $rate .= trim(current($tempV)) . ' ~ ' . trim(end($tempV)) . ' || ';
                    }
                }
                if ($rate != '') {
                    $rate = rtrim($rate, ' || ');
                } else {
                    $rate = 'other';
                }
                $v['rate'] = $rate;

            }
            $storeIds = implode(',', $storeIdList);
            if ($type == 2 && !empty($storeIds)) {
                //clicks
                $sql = "SELECT SUM(a.clicks) as clicks,SUM(a.clicks_robot) as rob,SUM(a.clicks_robot_p) as robp,b.`StoreId` FROM `statis_br` AS a LEFT JOIN r_store_domain AS b ON a.domainId = b.`DomainId` where a.CreatedDate>='{$search['stime']}' and a.CreatedDate<='{$search['etime']}'  AND b.StoreId in($storeIds) $where2 group BY b.StoreId";
                $trares = $this->objMysql->getRows($sql, 'StoreId');
                //commission
                $sqlcom = "Select SUM(Sales) AS sales,SUM(Commission) AS commission,b.`StoreId` FROM `rpt_transaction_unique` AS a LEFT JOIN r_store_domain AS b ON a.`domainId` = b.`DomainId` where a.CreatedDate>='{$search['stime']}' and a.CreatedDate<='{$search['etime']}'  AND b.StoreId in($storeIds) $where2 group BY b.StoreId";
                $comres = $this->objMysql->getRows($sqlcom, 'StoreId');
                foreach ($res1 as &$v) {
                    if (isset($comres[$v['sid']])) {
                        $v['sales'] = $comres[$v['sid']]['sales'];
                        $v['revenues'] = $comres[$v['sid']]['commission'];
                    } else {
                        $v['sales'] = 0;
                        $v['revenues'] = 0;
                    }
                    if (isset($trares[$v['sid']])) {
                        $v['clicks'] = $trares[$v['sid']]['clicks'];
                        $v['rob'] = $trares[$v['sid']]['rob'];
                        $v['robp'] = $trares[$v['sid']]['robp'];
                    } else {
                        $v['clicks'] = 0;
                        $v['rob'] = 0;
                        $v['robp'] = 0;
                    }
                }
            }
            foreach ($res1 as &$v) {
                $com = '';
                $site = '';
                $catetext = '';
                if (strstr($v['CategoryId'], ',')) {
                    $catearr = explode(',', $v['CategoryId']);
                    foreach ($catearr as $k) {
                        $catetext .= $cateres[$k]['Name'] . ' -- ';
                    }
                    $catetext = rtrim($catetext, ' -- ');
                } else {
                    if (empty($v['CategoryId'])) {
                        $catetext = 'No Category';
                    } else {
                        $catetext = $cateres[$v['CategoryId']]['Name'];
                    }
                }
                if ($v['PPC'] == 0) {
                    $ppc = 'UNKNOWN';
                } elseif ($v['PPC'] == 1) {
                    $ppc = 'Google Restricted';
                } elseif ($v['PPC'] == 2) {
                    $ppc = 'Google + Bing Restricted';
                } elseif ($v['PPC'] == 3) {
                    $ppc = 'PPC Allowed';
                } else {
                    $ppc = '100%';
                }
                $ppc = isset($v['PPCStatus']) ? $v['PPCStatus'] : 'UNKNOWN';
                if ($v['SupportType'] == 'All') {
                    $SupportType = 'Content & Promotion';
                } else {
                    $SupportType = $v['SupportType'];
                }
                $sql = 'SELECT d.`Name` AS ProgramName, c.`ProgramId`, c.`CommissionType`, c.`CommissionUsed`, c.`ShippingCountry`,b.Outbound FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell c ON c.`ProgramId` = b.`ProgramId` INNER JOIN program d ON d.`ID` = c.`ProgramId` WHERE  a.`ID` =' . $v['sid'];
                $res = $this->getRows($sql);
                if (!empty($res)) {
                    foreach ($res as $k1) {
                        if (empty($k1['Outbound'])) {
                            continue;
                        }
                        if (strstr($k1['Outbound'], '|')) {
                            $val = explode(',', $k1['Outbound']);
                            foreach ($val as $k) {
                                $key = explode('|', $k);
                                $c = strtoupper($key[1]);
                                isset($country[$c]) && $site .= $country[$c]['CountryName'] . ' -- ' . $key[2] . ' || ';
                                if(isset($search['country']) && !empty($search['country']) && $search['country'] != 'null')
                                {
                                    isset($country[$c]) && in_array($c,$search['country']) && $com .= $country[$c]['CountryName'] . ' -- ' . $k1['CommissionUsed'] . '% || ';
                                } else {
                                    isset($country[$c]) && $com .= $country[$c]['CountryName'] . ' -- ' . $k1['CommissionUsed'] . '% || ';
                                }

                            }
                        } else {
                            $key = explode('-', $k1['Outbound']);
                            $c = strtoupper($key[1]);
                            isset($country[$c]) && $site .= $country[$c]['CountryName'] . ' -- ' . $key[2] . ' || ';
                            if(isset($search['country']) && !empty($search['country']) && $search['country'] != 'null')
                            {
                                isset($country[$c]) && in_array($c,$search['country']) && $com .= $country[$c]['CountryName'] . ' -- ' . $k1['CommissionUsed'] . '% || ';
                            } else {
                                isset($country[$c]) && $com .= $country[$c]['CountryName'] . ' -- ' . $k1['CommissionUsed'] . '% || ';
                            }
                        }
                    }
                }
                $site = '"' . rtrim($site, ' || ') . '"';
                $name = !empty($v['NameOptimized']) ? $v['NameOptimized'] : ucwords($v['storeName']);
                echo $name . ',' . $site . ',' . '"' . number_format(
                        $v['clicks'] - $v['rob']
                    ) . '"' . ',' . '"' . number_format(
                        $v['rob']
                    ) . '"' . ',' . '"' . number_format(
                        $v['robp']
                    ) . '"' . ',' . '"$' . number_format(
                        $v['revenues'],
                        2
                    ) . '"' . ',' . '"$' . number_format(
                        $v['sales'],
                        2
                    ) . '"' . ',' . '"' . $v['rate'] . '"' . ',' . $SupportType . "," . "$ppc" . "," . "{$v['StoreAffSupport']}" . "," . "$catetext" . "," . rtrim(
                        $com,
                        ' || '
                    ) . "'\n";
            }

        }
    }
    function AnalysisCsv($para){
        $where = '';
        if(isset($para['status']) && $para['status'] != 'All'){
            $where.= " and Status = '{$para['status']}'";
        }
        if(isset($para['user']) && !empty($para['user'])){
            $where.= " and `AddUser` = '{$para['user']}'";
        }
        if(!empty($para['search'])){
            $search = trim($para['search']);
            $where.= " and Url like '%{$para['search']}%' or PublisherName like '%$search%'";
        }
        $sql = "SELECT count(1) FROM publisher_domain_info WHERE 1=1".$where;
        $count = current($this->getRow($sql));
        $page_total = ceil($count/1000);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=Publisher_Site_Analysis.csv");
        echo 'Url,AddUser,AddTime,Status,Affiliate,Subaffiliate'."\n";
        for($i=0;$i<=$page_total;$i++){
            $page= $i*1000;
            $sql = 'SELECT ID,PublisherId,PublisherName,Url,AddUser,Addtime,Status,IsPassSubAff,IsPassAff FROM publisher_domain_info WHERE 1=1'.$where." ORDER BY ID Desc limit $page,1000";
            $res = $this->getRows($sql);
            foreach($res as $k){
                if($k['IsPassSubAff'] == ''){
                    $k['IsPassSubAff'] = '/';
                }
                if($k['IsPassAff'] == ''){
                    $k['IsPassAff'] = '/';
                }
                echo $k['Url'].','.$k['AddUser'].','.$k['Addtime'].','.$k['Status'].','.$k['IsPassAff'].','.$k['IsPassSubAff']."\n";
            }
        }
    }
    function AnalysisCsv_Page(){
        $where = '';
        if(isset($para['status']) && $para['status'] != 'All'){
            $where.= " and Status = '{$para['status']}'";
        }
        if(isset($para['user']) && !empty($para['user'])){
            $where.= " and `AddUser` = '{$para['user']}'";
        }
        if(!empty($para['search'])){
            $search = trim($para['search']);
            $where.= " and Url like '%{$para['search']}%' or PublisherName like '%$search%'";
        }
        $sql = "SELECT count(1) FROM publisher_page WHERE 1=1".$where;
        $count = current($this->getRow($sql));
        $page_total = ceil($count/1000);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=Publisher_Site_Analysis.csv");
        echo 'Url,AddUser,AddTime,Status,Affiliate,Subaffiliate'."\n";
        for($i=0;$i<=$page_total;$i++){
            $page= $i*1000;
            $sql = 'SELECT Url,AddUser,Addtime,`Status` FROM publisher_page WHERE 1=1'.$where." ORDER BY ID Desc limit $page,1000";
            $res = $this->getRows($sql);
            foreach($res as $k){
                echo $k['Url'].','.$k['AddUser'].','.$k['Addtime'].','.$k['Status']."\n";
            }
        }
    }
    function AnalysisCsv2($para){
        $where = '';
        if(!empty($para['search'])){
            $where .= " and ExtDomain like '%{$para['search']}%'";
        }
        $sql = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_domain_detail WHERE DomainInfoID = {$para['id']}".$where;
        $count = current($this->getRow($sql));
        $page_total = ceil($count/1000);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=Publisher_Site_Analysis_Detail.csv");
        echo 'Domain,Amount'."\n";
        for($i=0;$i<=$page_total;$i++){
            $page= $i*1000;
            $sql = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_domain_detail WHERE DomainInfoID = {$para['id']} $where GROUP BY ExtDomain ORDER BY amount DESC limit $page,1000";
            $res = $this->getRows($sql);
            foreach($res as $k){
                echo $k['ExtDomain'].','.$k['amount']."\n";
            }
        }
    }
    function AnalysisCsv2_Page($para){
        $where = '';
        if(!empty($para['search'])){
            $where .= " and ExtDomain like '%{$para['search']}%'";
        }
        $sql = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_page_detail WHERE DomainInfoID = {$para['id']}".$where;
        $count = current($this->getRow($sql));
        $page_total = ceil($count/1000);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename=Publisher_Site_Analysis_Detail.csv");
        echo 'Domain,Amount'."\n";
        for($i=0;$i<=$page_total;$i++){
            $page= $i*1000;
            $sql = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_page_detail WHERE DomainInfoID = {$para['id']} $where GROUP BY ExtDomain ORDER BY amount DESC limit $page,1000";
            $res = $this->getRows($sql);
            foreach($res as $k){
                echo $k['ExtDomain'].','.$k['amount']."\n";
            }
        }
    }

    function saveAdvertiser($data){
        $update_data = array();
        if(isset($data['ID']) && !empty($data['ID']))
            $update_data['ID'] = intval($data['ID']);
        else
            return;

        if(isset($data['CategoryId']) && !empty($data['CategoryId'])){
            foreach($data['CategoryId'] as $k=>$v){
                $data['CategoryId'][$k] = intval($v);
            }
            $update_data['CategoryId'] = join(',',$data['CategoryId']);
        } else {
            $update_data['CategoryId'] = '';
        }
        if(isset($data['NameOptimized'])){
            $update_data['NameOptimized'] = trim($data['NameOptimized']);
        }

        if(isset($data['PPC'])){
            $update_data['PPC'] = intval($data['PPC']);
        }
        if(isset($data['Description'])){
            $update_data['Description'] = trim($data['Description']);
        }
        if(isset($_FILES) && !empty($_FILES)){
            $file_handle = current($_FILES);
            $name = isset($file_handle['name']) ? $file_handle['name'] : '';
            if (!empty($name)) {
                $ext = end(explode('.',$file_handle['name']));
                $update_data['LogoName'] = 'logo_'.$data['ID'].'.'.$ext;
                move_uploaded_file($file_handle['tmp_name'],UPLOAD_LOGO.$update_data['LogoName']);
            }
        }


        if(isset($data['Exclusive_Code'])){
            $ex = trim($data['Exclusive_Code']);
            $update_data['Exclusive_Code'] = $ex ? 'YES' : 'NO';
        }
        if(isset($data['Allow_Inaccurate_Promo'])){
            $ex = trim($data['Allow_Inaccurate_Promo']);
            $update_data['Allow_Inaccurate_Promo'] = $ex ? 'YES' : 'NO';
        }
        if(isset($data['CPA_Increase'])){
            $ex = trim($data['CPA_Increase']);
            $update_data['CPA_Increase'] = $ex ? 'YES' : 'NO';
        }
        if(isset($data['Allow_to_Change_Promotion_TitleOrDescription'])){
            $ex = trim($data['Allow_to_Change_Promotion_TitleOrDescription']);
            $update_data['Allow_to_Change_Promotion_TitleOrDescription'] = $ex ? 'YES' : 'NO';
        }
        if(isset($data['Promo_Code_has_been_blacklisted'])){
            $update_data['Promo_Code_has_been_blacklisted'] = trim($data['Promo_Code_has_been_blacklisted']);
        }
        if(isset($data['Word_has_been_blacklisted'])){
            $update_data['Word_has_been_blacklisted'] = trim($data['Word_has_been_blacklisted']);
        }
        if(isset($data['Coupon_Policy_Others'])){
            $update_data['Coupon_Policy_Others'] = trim($data['Coupon_Policy_Others']);
        }


        $sql = $this->getUpdateSql($update_data,'store','ID');
        $this->query($sql);
    }

    function getProductFeed($opts, $start = 1, $limit = 20)
    {
        $sql_names_set = 'SET NAMES latin1';
        $this->query($sql_names_set);
        $return = '';
        $where = '';
        $categoryArr = array();
        if (isset($opts['categories']) && !empty($opts['categories'])){
            $category_search = explode(',',$opts['categories']);
            foreach ($category_search as $k => $v) {
                if(is_numeric($v))
                    array_push($categoryArr, $v);
            }
        }
        if(!empty($categoryArr)){
            $where .= " AND(";
            foreach($categoryArr as $cateid)
            {
                $where .= " FIND_IN_SET('$cateid',f.CategoryId) OR";
            }
            $where = rtrim($where,'OR').")";
        }
        if (isset($opts['language']) && !empty($opts['language'])){
            $where.= " AND a.language='".addslashes($opts['language'])."'";
        }
        if(isset($opts['country']) && !empty($opts['country'])){
            if(strtolower($opts['country']) == 'uk' || strtolower($opts['country']) == 'gb'){
                $where .= " AND ( FIND_IN_SET('UK',a.country) OR FIND_IN_SET('GB',a.country) )";
            }else {
                $where .= " AND FIND_IN_SET('".strtolower(addslashes($opts['country']))."',a.country)";
            }
        }
        if(isset($opts['source']) && !empty($opts['source'])){
            $source = trim($opts['source']);
            $where .= " AND a.source='".addslashes($source)."'";
        }
        if(isset($opts['keyword']) && !empty($opts['keyword'])){
            $opts['keyword'] = trim($opts['keyword']);
            $where .= " AND (a.`ProductName` LIKE '%".addslashes($opts['keyword'])."%' OR a.`ProductDesc` LIKE '%" . addslashes($opts['keyword'])."%')";
        }
        if(isset($opts['store']) && !empty($opts['store'])){
            $opts['store'] = trim($opts['store']);
            $where .= " AND ( f.`Name` LIKE '%".addslashes($opts['store'])."%' OR f.`NameOptimized` LIKE '%".addslashes($opts['store'])."%')";
        }
        if(isset($opts['linkid']) && !empty($opts['linkid'])){
            $linkId = trim($opts['linkid']);
            $where .= " AND a.EncodeId='".addslashes($linkId)."'";
        }
        if(isset($opts['pid']) && !empty($opts['pid'])){
            $where.= " AND a.ID=".$opts['pid'];
        }
        if (isset($opts['aff']) && !empty($opts['aff'])){
            $where.= ' AND pi.`AffId` IN(' . $opts['aff'] . ')';
        }
        //查询统计数据
        $where2 = '';
        $dateType = isset($opts['dateType']) && !empty($opts['dateType']) ? $opts['dateType'] : 1;
        $prefix = $dateType != 2 ? '' : 'c_';
        $startDate = isset($opts['startdate']) && !empty($opts['startdate']) ? $opts['startdate'] : '';
        if (!empty($startDate)) {
            $where2 .= " AND createddate >= '" . $startDate . "'";
        }
        $endDate = isset($opts['enddate']) && !empty($opts['enddate']) ? $opts['enddate'] : '';
        if (!empty($endDate)) {
            $where2 .= " AND createddate <= '" . $endDate . "'";
        }
        $ctSql = " SELECT COUNT(DISTINCT a.ID) as `c` FROM product_feed a
            LEFT JOIN store f ON a.`StoreId` = f.id 
            LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
            WHERE  a.`status`='active' $where";
        $ctRs= $this->objMysql->getFirstRow($ctSql);
        $sql = "SELECT
            SUM(clicks) AS clicks,
            SUM(clicks_robot) as rob,
            SUM(clicks_robot_p) as robp,
            SUM(" . $prefix . "sales) AS sales,
            SUM(" . $prefix . "revenues) AS commission,
            SUM(" . $prefix . "orders) AS orders 
            FROM statis_link 
            WHERE  1 $where2
            AND statis_link.linkid IN 
            (
                SELECT a.ID FROM product_feed a
                LEFT JOIN store f ON a.`StoreId` = f.id 
                LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
                WHERE  a.`status`='active' $where
            )";

        $count = $this->objMysql->getFirstRow($sql);
        $return['count'] = isset($ctRs['c']) ? $ctRs['c'] : 0;
        $return['clicks'] = isset($count['clicks']) ? $count['clicks'] : 0;;
        $return['rob'] = isset($count['rob']) ? $count['rob'] : 0;
        $return['robp'] = isset($count['robp']) ? $count['robp'] : 0;
        $return['sales'] = isset($count['sales']) ? $count['sales'] : 0;;
        $return['rclicks'] = ($return['clicks'] - $return['rob'] > 0) ? number_format($return['clicks'] - $return['rob']) : 0;
        $return['orders'] = isset($count['orders']) ? $count['orders'] : 0;;
        $return['commission'] = isset($count['commission']) ? $count['commission'] : 0;;
        $sql = "SELECT
        a.id,a.source,a.Country,a.Language,a.ProductUrl,a.ProductName,a.`ProgramId` AS `pid`,a.ProductDesc,a.AddTime,a.ProductImage,a.ProductLocalImage,a.ProductStartDate,a.ProductEndDate,a.ProductPrice,a.ProductCurrency,a.ProductCurrencySymbol,a.LastUpdateTime,a.EncodeId,f.id as sid,IF(f.NameOptimized='' or f.NameOptimized is null,f.Name,f.NameOptimized) as storeName
        FROM
        product_feed a
        LEFT JOIN store f  ON a.`StoreId` = f.id 
        LEFT JOIN program_intell pi ON a.`ProgramId` = pi.`ProgramId` 
        WHERE a.`Status`='Active' $where ORDER BY a.AddTime desc LIMIT $start,$limit";
        $content = $this->objMysql->getRows($sql);
        $linkList = array();
        foreach ($content as $key => $item)
        {
            isset($item['id']) && array_push($linkList, $item['id']);
        }
        $calList = array();
        if (!empty($linkList))
        {
            $calSql = "SELECT linkid,SUM(clicks) AS clicks,SUM(clicks_robot) as rob,SUM(clicks_robot_p) as robp,SUM(" . $prefix . "sales) AS sales,SUM(" . $prefix . "revenues) AS commission,SUM(" . $prefix . "orders) AS orders FROM statis_link WHERE  1 $where2 AND linkid IN (" .implode(',',$linkList) . ") GROUP BY linkid";
            $calRows = $this->objMysql->getRows($calSql);
            foreach ($calRows as $calRow)
            {
                isset($calRow['linkid']) && $calList[$calRow['linkid']] = $calRow;
            }
        }
        foreach ($content as $key => &$item)
        {
            if (isset($calList[$item['id']]))
            {
                $item = array_merge($item,$calList[$item['id']]);
            }
        }

        $return['data'] = $content;

        return $return;
    }

    public function getCommissionRate($sid = 0 ,$pid = 0)
    {
        if (empty($sid)) {
            return false;
        }
        if (!empty($pid)) {
            //优先获取类型是Perecnt
            //根据程序id查询
            $pSql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`, d.CommissionType, d.CommissionUsed, d.CommissionCurrency FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell d ON d.`ProgramId` = b.`ProgramId` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$sid} AND d.`CommissionType` = 'Percent' AND g.StatusInAff = 'Active' AND g.Partnership = 'Active' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
            $pRes = $this->getRow($pSql);
            //根据商家id查询
            $sSql = "SELECT d.CommissionType, d.CommissionUsed FROM r_store_program b INNER JOIN `program_manual` d ON d.`ProgramId` = b.`ProgramId` WHERE b.`StoreId` = {$sid} AND d.`CommissionType` = 'Percent' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
            $sRes = $this->getRow($sSql);
            $sCud = isset($sRes['CommissionUsed']) ? $sRes['CommissionUsed'] : 0;
            $pCud = isset($pRes['CommissionUsed']) ? $pRes['CommissionUsed'] : 0;
            //联盟名称（根据ProgramId获取的信息）
            $affiliate = isset($pRes['aname']) ? $pRes['aname'] : '';
            //商家名称（根据ProgramId获取的信息）
            $store = isset($pRes['name']) ? $pRes['name'] : '';
            
            //优先程序利润率，若2者兼有，则取较大者
            $commission = empty($sRes) ? $pCud . "%" : ($sCud > $pCud ? $sCud : $pCud) . "%";
        } else {
            //查询
            $pSql = "SELECT w.`Name` AS aname,d.`ProgramId`,IF(a.NameOptimized = '', a.`NAME`, a.`NameOptimized`) AS `name`,d.CommissionUsed,d.CommissionType,d.CommissionCurrency FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell d ON d.`ProgramId` = b.`ProgramId` INNER JOIN program g ON g.`ID` = d.`ProgramId` INNER JOIN wf_aff w ON d.`AffId` = w.`ID` WHERE a.id = {$sid} and b.ProgramId={$pid}  AND g.StatusInAff = 'Active' AND g.Partnership = 'Active' ORDER BY d.`CommissionUsed` DESC LIMIT 1";
            $pRes = $this->getRow($pSql);
            //人工修改
            $mSql = "SELECT d.CommissionType, d.CommissionUsed, d.CommissionCurrency AS `count` FROM `program_manual` d WHERE d.`ProgramId` = {$pid} ORDER BY d.`CommissionUsed` DESC LIMIT 1";
            $mRes = $this->getRow($mSql);
            $pCud = !empty($pRes['CommissionCurrency']) ? $pRes['CommissionCurrency'] . $pRes['CommissionUsed'] : $pRes['CommissionUsed'] . '%';
            $commission = empty($pRes) ? false : $pCud;
            //联盟名称（根据ProgramId获取的信息）
            $affiliate = isset($pRes['aname']) ? $pRes['aname'] : '';
            //商家名称（根据ProgramId获取的信息）
            $store = isset($pRes['name']) ? $pRes['name'] : '';
            if (!empty($pRes) && !empty($mRes)) {
                //如果人工利润率则采用人工
                if ($mRes['CommissionType'] == 'Percent') {
                    $commission = $mRes['CommissionUsed'] . '%';
                } else if (!empty($mRes['CommissionCurrency'])) {
                    $commission = $mRes['CommissionCurrency'] . $mRes['CommissionUsed'];
                }
            }
        }

        return array(
            'commission' => $commission,
            'affiliate' => $affiliate,
            'store' => $store
        );
    }

    /**
     * @return mixed
     */
    public function partnershipTempCsv($data)
    {

        $sql_names_set = 'SET NAMES latin1';
        $this->query($sql_names_set);
        $where = '1=1';
        $date = isset($data['sdate']) ? $data['sdate'] : date('Y-m-d',strtotime('-7 days'));
        if(!empty($date)){
            $where .= " and a.AddTime >= '$date 00:00:00'";
        }
        $edate = isset($data['edate']) ? $data['edate'] : date('Y-m-d');
        if(!empty($edate)){
            $where .= " and a.AddTime <= '$edate 23:59:59'";
        }
        $program = isset($data['program']) ? $data['program'] : '';
        if(!empty($program)){
            $where .= " and a.name like '%$program%'";
        }
        $network = isset($data['network']) ? $data['network'] : '';
        if(!empty($network)){
            $where .= " and a.affname like '%$network%'";
        }
        $country = isset($data['country']) ?  $data['country'] : '';
        if (!empty($country)) {
            foreach ($country as $ct) {
                $ct = strtolower(trim($ct));
                $where .= " and  FIND_IN_SET('$ct',b.`ShippingCountry`)";
            }
        }
        $status = isset($data['status']) ? $data['status'] : '';
        $status = trim($status);
        if($status){
            $where .= " and b.SupportType = '$status'";
        }
        $homepage = isset($data['homepage']) ? $data['homepage'] : '';
        $homepage = trim($homepage);
        if (!empty($homepage)) {
            $where .= " and a.homepage like '%$homepage%'";
        }

        $sql = "select distinct a.Name,count(1) from temp_partership a LEFT JOIN program_intell b ON b.ProgramId = a.ProgramId  where $where";
        $count = current($this->getRow($sql));
        $page_total = ceil($count/1000);
        header("Content-Type: text/html; charset=UTF-8");
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges: bytes");
        header("Content-Disposition:  attachment;  filename=PartnershipTemp.csv");
        echo 'ProgramName,Network,SupportType,Commission Value,Homepage,Date'."\n";
        for($i=0;$i<=$page_total;$i++){
            $page= $i*1000;
            $sql = "select distinct a.Name,a.AffName,a.Homepage,a.AddTime,b.SupportType,a.ProgramID,b.CommissionType,b.CommissionUsed,b.CommissionCurrency from temp_partership a LEFT JOIN program_intell b ON b.ProgramId = a.ProgramId  where $where limit $page,1000";
            $res = $this->getRows($sql);
            foreach($res as $k){

                $cType = isset($k['CommissionType']) ? $k['CommissionType'] : '';
                $cValue = isset($k['CommissionUsed']) ? $k['CommissionUsed'] : 0;
                $cCurrent = isset($k['CommissionCurrency']) ? $k['CommissionCurrency'] : 0;
                switch ($cType) {
                    case  'Percent' :
                        $cv = $cValue . '%';
                        break;
                    case  'Value' :
                        $cv = !empty($cCurrent) ? $cCurrent . $cValue : $cValue;
                        break;
                    default :
                        $cv = '$' . $cValue ;
                }
                $name = str_replace(',', ' ',$k['Name']);
                echo iconv("ISO-8859-5", "UTF-8", $name) . ','.$k['AffName'] . ','.$k['SupportType'] . ','.$cv . ','.trim($k['Homepage']).','.$k['AddTime']."\n";
            }
        }
    }
}
