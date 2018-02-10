<?php

class Publisher extends LibFactory
{

    function __construct()
    {
        parent::__construct();
        if (!isset($this->mysql))
            $this->mysql = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
    }
    function loginfo($id,$type){
        $sql = "select * from logapi_publisher where publisherid=$id and type='$type' ORDER BY `addtime` DESC ";
        $res = $this->getRows($sql);
        return $res;
    }
    function  getpublierupdate($para,$page,$pagesize){
        $sql_head = 'SELECT tb2.uptype,tb2.state,tb2.update_user,tb1.ID,tb1.Name,tb1.UserName,tb2.time,tb2.info FROM publisher AS tb1 INNER JOIN publisher_update AS tb2 ON tb1.ID = tb2.PublisherId AND tb2.uptype = 0 AND tb2.state = 0  WHERE 1=1 ';
        $page_start = $pagesize * ($page - 1);
        //获取当前页数
        $info['page_now'] = $page;
        //删除当前页数参数
        if (isset($para['p'])) unset($para['p']);
        //搜索条件拼接
        $where = '';
        if(isset($para['name']) && !empty($para['name'])){
            $name = trim($para['name']);
            $where .= " and tb1.Name like '%$name%'";
        }
        if(isset($para['updateuser']) && !empty($para['updateuser'])){
            $updateuser = trim($para['updateuser']);
            $where .= " and tb2.update_user like '%$updateuser%'";
        }
        if(isset($para['status']) && $para['status'] != 'All'){

            $where .= " and tb2.state = {$para['status']}";
        }
        if(!empty($para['id'])){
            $pid = $para['id'];
            $sql = "SELECT tb2.PublisherId,tb2.info FROM publisher AS tb1 INNER JOIN publisher_update AS tb2 ON tb1.ID = tb2.PublisherId WHERE tb2.PublisherId=$pid";
            return $this->mysql->getRows($sql);
        }
        $sql_tail = " ORDER BY tb1.ID Desc limit $page_start,$pagesize";
        //拼接获取list
        $sql = $sql_head . $where . $sql_tail;
        $info['data'] = $this->mysql->getRows($sql);

        foreach($info['data'] as $k=>$v){
            $info['data'][$k]['Number'] = $k+($page-1)*$pagesize+1;

        }
        $sqlcount = "SELECT count(*) FROM publisher AS tb1 INNER JOIN publisher_update AS tb2 ON tb1.ID = tb2.PublisherId AND tb2.uptype = 0 AND tb2.state = 0  WHERE 1=1".$where;
        $total = $this->mysql->getRows($sqlcount);
        $info['page_total'] = ceil($total[0]['count(*)'] / $pagesize);
        return $info;
    }
    function messagelist($para,$page,$pagesize=20){
        $subsql = '';
        if(isset($para['status']) && !empty($para['status'])){
            if($para['status'] != 'all'){
                $subsql.=" and Status = '{$para['status']}'";
            }
        }
        $sql_head = "SELECT * FROM message WHERE 1=1".$subsql;
        $page_start = $pagesize * ($page - 1);
        //获取当前页数
        $data['page_now'] = $page;
        //尾部sql
        $sql_tail = " ORDER BY ID Desc limit $page_start,$pagesize";
        //拼接获取list
        $sql = $sql_head.$sql_tail;
        $data['info'] = $this->mysql->getRows($sql);
        foreach($data['info'] as $k=>$v){
            $data['info'][$k]['Number'] = $k+($page-1)*$pagesize+1;
        }
        $sqlcount = "SELECT count(1) FROM message WHERE 1=1".$subsql;
        $total = $this->mysql->getRows($sqlcount);
        $data['page_total'] = ceil($total[0]['count(1)'] / $pagesize);
        return $data;
    }
    function updatemessage($para){
        $update_new = array();
        $update_new['Status'] = trim($para['val']);
        $update_new['updatetime'] = date("Y-m-d H:i:s");
        $update_new['user'] = $_SERVER['PHP_AUTH_USER'];
        $update_new['remark'] = trim($para['remark']);
        $res = $this->table('message')->where('ID = ' . intval($para['id']))->update($update_new);
        if($res == 1){
            return 1;
        }else{
            return 0;
        }
    }
    function getchangelog($para,$page,$pagesize){
        if(isset($para['check']) && !empty($para['check'])){
            $sql = "SELECT oldinfo,newinfo FROM publisher_update_log WHERE ID={$para['check']}";
            return $this->mysql->getRows($sql);
        }
        $id = $para['id'];
        $sql_head = "SELECT * FROM publisher_update_log WHERE PublisherId=$id";
        $page_start = $pagesize * ($page - 1);
        //获取当前页数
        $data['page_now'] = $page;
        //尾部sql
        $sql_tail = " ORDER BY ID Desc limit $page_start,$pagesize";
        //拼接获取list
        $sql = $sql_head.$sql_tail;
        $data['info'] = $this->mysql->getRows($sql);
        foreach($data['info'] as $k=>$v){
            $data['info'][$k]['Number'] = $k+($page-1)*$pagesize+1;
        }
        $sqlcount = "SELECT count(*) FROM publisher_update_log WHERE PublisherId=$id";
        $total = $this->mysql->getRows($sqlcount);
        $data['page_total'] = ceil($total[0]['count(*)'] / $pagesize);
        return $data;
    }
    //获取列表
    function getList($para,$page,$pagesize)
    {
        $page_start = $pagesize * ($page - 1);
        //获取当前页数
        $info['page_now'] = $page;
        //搜索条件拼接
        $where = '';
        $sort = 'AddTime';
        if(isset($para['sort']) && !empty($para['sort'])){
            $sort = $para['sort'];
        }
        if(isset($para['na']) && !empty($para['na'])){
            $data = addslashes(trim($para['na']));
            $sql = "select  a.`ID` from publisher as a left join publisher_account as b on a.ID=b.PublisherId
                      where b.Alias LIKE '%$data%' OR a.`Name` LIKE '%$data%' OR a.`Domain` LIKE '%$data%' 
                      OR a.`Email` LIKE '%$data%' OR a.`UserName` LIKE '%$data%' OR b.`Domain` LIKE '%$data%' 
                      OR b.`Apikey` LIKE '%$data%' group by a.`ID`";
            $row = $this->getRows($sql);
            if(!empty($row)){
                $where.= ' AND a.ID IN(';
                foreach($row as $k){
                    $where.= "'".$k['ID']."',";
                }
                $where = rtrim($where,',').")";
            }else {
                $where .= ' AND 1=0 ';
            }
        }
        if(isset($para['stime']) && !empty($para['stime'])){
            $where.=" and a.`Addtime`>='{$para['stime']}'";
        }
        if(isset($para['etime']) && !empty($para['etime'])){
            $where.=" and a.`Addtime`<='{$para['etime']}'";
        }
        if(isset($para['categories']) && !empty($para['categories'])){
            $categoryArr = explode(',',trim($para['categories'],','));
            if(!empty($categoryArr))
            {
                $where1 = " AND a.ID IN(";
                $val= "";
                foreach($categoryArr as $cateid)
                {
                    $val.= " FIND_IN_SET('$cateid',CategoryId) OR";
                }
                $val = rtrim($val,'OR').")";
                $sql = "select ID from publisher_detail where(".$val;
                $res = $this->getRows($sql);
                foreach($res as $k){
                    $where1.="'".$k['ID']."',";
                }
                $where.=rtrim($where1,',').")";
            }

        }
        if(isset($para['Status']) && !empty($para['Status'])){
            $where.= " AND a.Status = '{$para['Status']}'";
        }
        if(isset($para['Manager']) && !empty($para['Manager'])){
            $where.= " AND a.Manager = '{$para['Manager']}'";
        }
        if(isset($para['stype']) && !empty($para['stype'])){
            $where.= " AND a.SiteOption = '{$para['stype']}'";
        }
        if(isset($para['GeoBreakdown'])  && !empty($para['GeoBreakdown'])) {
            $gbd = $para['GeoBreakdown'];
            $where .= " AND b.GeoBreakdown REGEXP '^[+]{0,1}" .$gbd ."[+]|[+]" . $gbd ."$|[+]" . $gbd ."[+]|^[+]{0,1}" . $gbd ."$'";
        }
        if(!empty($para['level'])){
            if($para['level'] == "TIER1"){
                $where .=' AND ( a.`Level` = "TIER1" OR a.`Level` = "TIER0" ) ';
            }else {
                $where .=' AND a.`Level` = "'.addslashes($para['level']).'" ';
            }
        }
        $where .= " AND a.Career <> 'advertiser_white'";
        $where .= " AND a.Career <> 'sub_account'";
        //拼接获取list
        $sql = "select count(DISTINCT a.ID) AS `cnt` from publisher a LEFT JOIN publisher_account AS b ON a.id = b.publisherid   LEFT JOIN publisher_detail AS d ON a.id = d.publisherid where 1=1".$where;
        $total = $this->getRow($sql);
        $info['count'] = $total['cnt'];
        $info['page_total'] = ceil($total['cnt'] / $pagesize);
        $sql='SELECT COUNT(*) as c FROM publisher a  LEFT JOIN publisher_account AS b ON a.id = b.publisherid  WHERE a.Status = "Active"'.$where;
        $info['a'] = mysql_fetch_assoc($this->mysql->query($sql));
        $sql='SELECT COUNT(*) as c FROM publisher a  LEFT JOIN publisher_account AS b ON a.id = b.publisherid  WHERE a.Status = "Inactive"'.$where;
        $info['i'] = mysql_fetch_assoc($this->mysql->query($sql));
        $sql='SELECT COUNT(*) as c FROM publisher a  LEFT JOIN publisher_account AS b ON a.id = b.publisherid  WHERE a.Status = "Unaudited"'.$where;
        $info['u'] = mysql_fetch_assoc($this->mysql->query($sql));
        $sql = "SELECT
                  a.ID,
                  a.Name,
                  a.Email,
                  a.Status,
                  a.Career,
                  a.Addtime,
                  a.Manager,
                  a.SiteOption,
                  a.Level
                FROM
                  publisher AS a
                  LEFT JOIN publisher_account AS b
                    ON a.id = b.publisherid
                  LEFT JOIN publisher_detail AS d
                  ON a.id = d.publisherid
                  LEFT JOIN
                    (SELECT
                      site,SUM(clicks) AS click,
                      SUM(revenues) AS commission
                    FROM
                      `statis_affiliate_br`
                    GROUP BY site) AS c
                    ON b.apikey = c.site
                    where 1=1
                    $where
                GROUP BY a.ID
                ORDER BY $sort DESC
                limit $page_start,$pagesize";
        $info['data'] = $this->getRows($sql);
        if(empty($info['data'])){
            return $info;
        }
        $pids = array();
        foreach($info['data'] as $k=>$v){
            $pids[] = $v['ID'];
        }
        if($sort == 'AddTime'){
            $sort = 'commission';
        }
        $sDate = date('Y-m-d',strtotime('-1 month'));
        $sql = "SELECT
                  IFNULL(SUM(s.clicks),0) AS tclick,
                  IFNULL(SUM(s.clicks_robot),0) AS rob,
                  IFNULL(SUM(s.revenues),0) AS commission,
                  IFNULL(SUM(CASE WHEN s.createddate > '$sDate' THEN s.clicks ELSE 0 END),0) AS tclick_30d,
                  IFNULL(SUM(CASE WHEN s.createddate > '$sDate' THEN s.clicks_robot ELSE 0 END),0) AS rob_30d,
                  IFNULL(SUM(CASE WHEN s.createddate > '$sDate' THEN s.revenues ELSE 0 END),0) AS commission_30d,
                  p.PublisherId,
                  p.ApiKey,
                  p.Domain,
                  max(g.JsLastTime) as JsLastTime
                FROM
                   publisher_account p
                   LEFT JOIN
                    statis_affiliate_br s
                    ON p.ApiKey = s.site
                   left JOIN publisher_stats g
                    ON g.`PID` = p.`ID`
                WHERE p.PublisherId IN(".join(',',$pids).")
                GROUP BY p.Apikey";
        $row_cr_tmp = $this->mysql->getRows($sql);
        $row_cr = array();
        foreach($row_cr_tmp as $k=>$v){
            if(isset($row_cr[$v['PublisherId']]))
            {
                if(isset($row_cr[$v['PublisherId']]['click'])){
                    $row_cr[$v['PublisherId']]['click'] .= "<br/>".number_format($v['tclick']-$v['rob']);
                }
                if(isset($row_cr[$v['PublisherId']]['commission'],$v['commission'])){
                    $row_cr[$v['PublisherId']]['commission'] .= "<br/>"."$".number_format($v['commission'],2);
                }
                if(isset($row_cr[$v['PublisherId']]['click_30d'])){
                    $row_cr[$v['PublisherId']]['click_30d'] .= "<br/>".number_format($v['tclick_30d']-$v['rob_30d']);
                }
                if(isset($row_cr[$v['PublisherId']]['commission_30d'],$v['commission_30d'])){
                    $row_cr[$v['PublisherId']]['commission_30d'] .= "<br/>"."$".number_format($v['commission_30d'],2);
                }
                if(isset($row_cr[$v['PublisherId']]['Domain'])){
                    $row_cr[$v['PublisherId']]['Domain'] .= "<br/>".$v['Domain'];
                }
                if(isset($row_cr[$v['PublisherId']]['ApiKey'])){
                    $row_cr[$v['PublisherId']]['ApiKey'] .= "<br/>".$v['ApiKey'];
                }
                if(isset($row_cr[$v['PublisherId']]['JsLastTime']) && !empty($row_cr[$v['PublisherId']]['JsLastTime'])){
                    $row_cr[$v['PublisherId']]['JsLastTime'] .= "<div style='margin-top: 5px;'><span style='font-size: 12px;' class='label label-primary'>{$v['JsLastTime']}</span></div>";
                }
            }
            else
            {
                if(!empty($v['JsLastTime'])){
                    $v['JsLastTime'] = "<div style='margin-top: 5px;'><span style='font-size: 12px;' class='label label-primary'>{$v['JsLastTime']}</span></div>";
                }
                $v['commission'] = "$".number_format($v['commission'],2);
                $v['click'] = number_format($v['tclick']-$v['rob']);
                $v['commission_30d'] = "$".number_format($v['commission_30d'],2);
                $v['click_30d'] = number_format($v['tclick_30d']-$v['rob_30d']);
                $row_cr[$v['PublisherId']] = $v;
            }
        }
        $sql = "select count(1),PublisherId,Add_Violation_Warning from block_relationship where PublisherId in(".join(',',$pids).") and Status='Active' and `Source` = 'Normal' group by PublisherId,Add_Violation_Warning";
        $bres = $this->objMysql->getRows($sql,'PublisherId');
        
        foreach ($bres as $key=>$temp){
            if(isset($bres[$temp['PublisherId']]['Violation_Warning'])){
                if($bres[$temp['PublisherId']]['Violation_Warning'] !== 1){
                    $bres[$temp['PublisherId']]['Violation_Warning'] = $temp['Add_Violation_Warning'];
                }
            }else {
                $bres[$temp['PublisherId']]['Violation_Warning'] = $temp['Add_Violation_Warning'];
            }
        }
        
        foreach($info['data'] as $k=>$v){
            //0灰色 1红色
            if(isset($bres[$v['ID']]['Violation_Warning'])){
                $info['data'][$k]['warning'] = $bres[$v['ID']]['Violation_Warning'];
            }else{
                $info['data'][$k]['warning'] = 2;
            }
            if(isset($row_cr[$v['ID']])){
                $info['data'][$k]['Click'] = $row_cr[$v['ID']]['click'];
                $info['data'][$k]['Revenue'] = $row_cr[$v['ID']]['commission'];
                $info['data'][$k]['Click30Days'] = $row_cr[$v['ID']]['click_30d'];
                $info['data'][$k]['Revenue30Days'] = $row_cr[$v['ID']]['commission_30d'];
                $info['data'][$k]['Domain'] = $row_cr[$v['ID']]['Domain'];
                $info['data'][$k]['ApiKey'] = $row_cr[$v['ID']]['ApiKey'];
                $info['data'][$k]['JsLastTime'] = $row_cr[$v['ID']]['JsLastTime'];
            }
        }
        return $info;
    }
    function getupload($para,$page,$pagesize){
        $where="";
        if(isset($para['name'])){
            $name = $para['name'];
            $where.= " AND uname like '%$name%'";
        }
        if(isset($para['describe'])){
            $name = $para['describe'];
            $where.= " AND describe like '%$name%'";
        }
        $page_start = $pagesize * ($page - 1);
        $count = "select count(*) from publisher_upload WHERE 1=1 $where";
        $sql = "select * from publisher_upload WHERE 1=1 $where ORDER BY addtime DESC limit $page_start,$pagesize";
        $countres = $this->getRow($count);
        $res = $this->getRows($sql);
        $info['data'] = $res;
        $info['page_total'] = ceil($countres['count(*)'] / $pagesize);
        $info['page_now'] = $page;
        return $info;
    }
    function publishercsv($para){
        error_reporting(E_ALL^E_NOTICE);
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition: attachment; filename=publisher.csv");
        print(chr(0xEF).chr(0xBB).chr(0xBF));
        echo "Name,Email,Status,Site Type,Domain,ApiKey,JsCode,Clicks,Commission,Level,Manager,Addtime"."\r\n";
        $where = '';
        $page = 0;
        $pagesize = 500;
        if(isset($para['stime']) && !empty($para['stime'])){
            $where.=" and a.Addtime>='{$para['stime']}'";
        }
        if(isset($para['etime']) && !empty($para['etime'])){
            $where.=" and a.Addtime<='{$para['etime']}'";
        }
        if(isset($para['na']) && !empty($para['na'])){
            $data = addslashes(trim($para['na']));
            $sql = "select  a.`ID` from publisher as a left join publisher_account as b on a.ID=b.PublisherId
                      where b.Alias LIKE '%$data%' OR a.`Name` LIKE '%$data%' OR a.`Domain` LIKE '%$data%'
                      OR a.`Email` LIKE '%$data%' OR a.`UserName` LIKE '%$data%' OR b.`Domain` LIKE '%$data%'
                      OR b.`Apikey` LIKE '%$data%' group by a.`ID`";
            $row = $this->getRows($sql);
            if(!empty($row)){
                $where.= ' AND a.ID IN(';
                foreach($row as $k){
                    $where.= "'".$k['ID']."',";
                }
                $where = rtrim($where,',').")";
            }else {
                $where .= ' AND 1=0 ';
            }
        }
        if(isset($para['categories']) && !empty($para['categories'])){
            $categoryArr = explode(',',trim($para['categories'],','));
            if(!empty($categoryArr))
            {
                $where1 = " AND a.ID IN(";
                $val= "";
                foreach($categoryArr as $cateid)
                {
                    $val.= " FIND_IN_SET('$cateid',CategoryId) OR";
                }
                $val = rtrim($val,'OR').")";
                $sql = "select ID from publisher_detail where(".$val;
                $res = $this->getRows($sql);
                foreach($res as $k){
                    $where1.="'".$k['ID']."',";
                }
                $where.=rtrim($where1,',').")";
            }

        }
        if(isset($para['stype']) && !empty($para['stype'])){
            $where.= " AND a.SiteOption = '{$para['stype']}'";
        }
        if(isset($para['GeoBreakdown'])  && !empty($para['GeoBreakdown'])) {
            $gbd = $para['GeoBreakdown'];
            $where .= " AND b.GeoBreakdown REGEXP '^[+]{0,1}" .$gbd ."[+]|[+]" . $gbd ."$|[+]" . $gbd ."[+]|^[+]{0,1}" . $gbd ."$'";
        }
        if(isset($para['Status']) && !empty($para['Status'])){
            $where.= " AND a.Status = '{$para['Status']}'";
        }
        if(isset($para['Manager']) && !empty($para['Manager'])){
            $where.= " AND a.Manager = '{$para['Manager']}'";
        }
        $where.= " AND a.Career <> 'advertiser_white'";
        //拼接获取list
        $sql = "select count(1) AS `count` from publisher a LEFT JOIN publisher_account AS b ON a.id = b.publisherid  where 1=1".$where;

        $count = $this->getRow($sql);
        $total = ceil($count['count']/$pagesize);
        do{
               $start = $page*$pagesize;
               $sql = "select a.Name,a.Manager,a.SiteOption,a.Status,a.Level,a.Email,a.ID,a.AddTime from publisher a LEFT JOIN publisher_account AS b ON a.id = b.publisherid  WHERE 1=1 $where GROUP BY a.ID order by a.AddTime DESC limit $start,$pagesize ";
               $res = $this->getRows($sql);
               foreach($res as &$k){
                   $sql = "select a.apikey,a.domain,max(b.JsLastTime) as jtime from publisher_account as a left join publisher_stats as b on a.ID = b.PID where publisherid=".$k['ID']." GROUP BY a.`ApiKey`";
                   $res1 = $this->getRows($sql);
                   if(!empty($res1)){
                       $domain = '';
                       $key = '';
                       $jtime = '';
                       $site = '';
                       foreach($res1 as $k1){
                           $domain.=$k1['domain']."\n";
                           $key.=$k1['apikey']."\n";
                           !empty($k1['jtime'])?$jtime.=$k1['jtime']."\n":"\n";
                           $site.='"'.$k1['apikey'].'",';
                       }
                       //获取Commission,clicks
                       $site = rtrim($site,',');
                       $sql = "select IFNULL(SUM(clicks),0) AS click,IFNULL(SUM(clicks_robot),0) AS rob,IFNULL(SUM(revenues),0) AS commission from statis_affiliate_br where site IN($site)";
                       $mres = $this->getRow($sql);
                       $k['clicks'] = number_format($mres['click']-$mres['rob']);
                       $k['commission'] = "$".number_format($mres['commission'],2);
                       $k['domain'] = rtrim($domain,"\n");
                       $k['apikey'] = rtrim($key,"\n");
                       $k['jtime'] = rtrim($jtime,"\n");
                   }else{
                       $k['domain'] = "/";
                       $k['apikey'] = "/";
                       $k['clicks'] = 0;
                       $k['commission'] = "$0.00";
                       $k['jtime'] = "\n";
                   }
                   $tmp = array(
                       $k['Name'],
                       $k['Email'],
                       $k['Status'],
                       $k['SiteOption'],
                       $k['domain'],
                       $k['apikey'],
                       $k['jtime'],
                       $k['clicks'],
                       $k['commission'],
                       $k['Level'],
                       $k['Manager'],
                       $k['AddTime']
                   );
                   echo '"'. join('","',$tmp).'"'."\n";
               }
          $page++;
        } while($page<$total);
        exit;
    }
    function GetCategoryList(){
        $sql = "SELECT a.name AS pname,a.id AS pid,b.name AS cname,b.id AS cid FROM category a LEFT JOIN category b ON a.`ID`=b.`PID` WHERE a.`PID`=0 AND a.`AffId`=1";
        $arr = $this->objMysql->getRows($sql);
        $category = array();
        foreach ($arr as $value){
            $category[$value['pid']]['name'] = $value['pname'];
            $category[$value['pid']]['child'][$value['cid']] = $value['cname'];
        }
        return $category;
    }
    //urldata
    function urldata($where){
        $subsql = "";
        $start_limit = ($where['page'] - 1) * $where['pagesize'];
//        if ($where['type'] != '所有') {
//            $subsql .= " and mission_group_name = '{$where['type']}'";
//        }
//        if (!empty($where['key'])) {
//            $subsql .= " and mission_name like '%{$where['key']}%' or mission_group_name like '%{$where['key']}%'";
//        }
        $limit = " ORDER BY {$where['orderColumn']} {$where['orderType']} limit $start_limit, {$where['pagesize']}";
        $total = "SELECT count(mission_config_id) FROM mission_config  where 1=1 " . $subsql;
        $sql = "SELECT mission_config_id,mission_name,mission_group_name,mission_group_id FROM mission_config  WHERE 1=1" . $subsql . $limit;
//        echo $sql.'----------------------'.$total;
//        die;
        $res = $this->mysql->getRows($sql);
        $count = $this->mysql->getRows($total);

        return array('res' => $res, 'total' => $count['0']['count(mission_config_id)']);
    }

    function getPublisherById($id = 0)
    {
        if ($id) {
            $sql = "SELECT a.ID as aid,p.AddTime,a.ApiKey,a.JsWork,a.JsIgnoreDomain,a.`Name` AS aname,a.Domain AS dom,p.Remark,p.Tax,p.RefId,p.RefRate,p.Career,p.Level,p.Manager,p.UserName,p.UserPass,p.Name,p.ID,p.Domain,p.Status,d.ContentProduction,d.ContentProduction,d.TypeOfContent,d.DevKnowledge,d.CurrentNetwork,d.TypeOfContent,d.WaysOfTraffic,d.ProfitModel,d.DevKnowledge,d.StaffNumber,d.GeoBreakdown,d.CategoryId,d.SiteType,a.Alias,p.Email,p.Company,p.Phone,p.CompanyAddr,p.ZipCode,p.Country,p.PayPal FROM publisher AS p LEFT JOIN publisher_detail AS d ON p.ID = d.PublisherId LEFT JOIN publisher_account AS a ON a.PublisherId = p.ID WHERE p.ID = ".intval($id);
            $result = $this->mysql->getRows($sql);

            if(!empty($result) && $result[0]['RefId'] > 0){
                $refid = $result[0]['RefId'];
                $RefPublisher = $this->table('publisher')->where('ID = '.intval($refid))->findone();
                $result[0]['RefPublisher'] = $RefPublisher;
            }

            return $result;
        }
    }
  
    function  updatepublisher($data){
        $update_d = array();
        $update_d['Domain'] = isset($data['info']['Domain']) && !empty($data['info']['Domain']) ? $data['info']['Domain'] : '';
        $update_d['Domain'] = trim($update_d['Domain']);
        $update_d['Email'] = isset($data['info']['Email']) && !empty($data['info']['Email']) ? $data['info']['Email'] : '';
        $update_d['Email'] = trim($update_d['Email']);
        $update_d['Phone'] = isset($data['info']['Phone']) && !empty($data['info']['Phone']) ? $data['info']['Phone'] : '';
        $update_d['Phone'] = trim($update_d['Phone']);
        $update_d['Company'] = isset($data['info']['Company']) && !empty($data['info']['Company']) ? $data['info']['Company'] : '';
        $update_d['Company'] = trim($update_d['Company']);
        $update_d['CompanyAddr'] = isset($data['info']['CompanyAddr']) && !empty($data['info']['CompanyAddr']) ? $data['info']['CompanyAddr'] : '';
        $update_d['CompanyAddr'] = trim($update_d['CompanyAddr']);
        $update_d['Country'] = isset($data['info']['Country']) && !empty($data['info']['Country']) ? $data['info']['Country'] : '';
        $update_d['Country'] = intval($update_d['Country']);
        $update_d['Name'] = isset($data['info']['Name']) && !empty($data['info']['Name']) ? $data['info']['Name'] : '';
        $update_d['Name'] = trim($update_d['Name']);
        $update_d['PayPal'] = isset($data['info']['PayPal']) && !empty($data['info']['PayPal']) ? $data['info']['PayPal'] : '';
        $update_d['PayPal'] = trim($update_d['PayPal']);
        $update_d['Career'] = isset($data['info']['Career']) && !empty($data['info']['Career']) ? $data['info']['Career'] : '';
        $update_d['Career'] = trim($update_d['Career']);
        $update_d['Status'] = isset($data['info']['Status']) && !empty($data['info']['Status']) ? $data['info']['Status'] : '';
        $update_d['Status'] = trim($update_d['Status']);
        $update_d['Manager'] = isset($data['info']['Manager']) && !empty($data['info']['Manager']) ? $data['info']['Manager'] : '';
        $update_d['Manager'] = trim($update_d['Manager']);
        $res = $this->table('publisher')->where('ID = ' . intval($data['ID']))->update($update_d);
        if (!$res) {
            return 2;
        }else{
            echo 1;die;
            $update_new = array();
            $update_new['CategoryId'] = isset($data['detail']['CategoryId']) && !empty($data['detail']['CategoryId']) ? $data['detail']['CategoryId'] : '';
            $update_new['CategoryId'] = trim($update_new['CategoryId']);
            $update_new['GeoBreakdown'] = isset($data['detail']['GeoBreakdown']) && !empty($data['detail']['GeoBreakdown']) ? $data['detail']['GeoBreakdown'] : '';
            $update_new['GeoBreakdown'] = trim($update_new['GeoBreakdown']);
            $update_new['SiteType'] = isset($data['detail']['SiteType']) && !empty($data['detail']['SiteType']) ? $data['detail']['SiteType'] : '';
            $update_new['SiteType'] = trim($update_new['SiteType']);
            $res = $this->table('publisher_detail')->where('PublisherId = ' . intval($data['ID']))->update($update_new);
            if($res == 1){
                return 1;
            }else{
                return 0;
            }
        }
    }

    function doUpdate($arr = array(), $id)
    {
        //更新时间
        $date = date('Y-m-d h:i:s');


        $sql = "SELECT Status,Career,Remark,Tax,Name,Manager From publisher WHERE ID = $id";
        $old = $this->mysql->getrows($sql);
        foreach($old[0] as $key => $value){
            if($value == $arr[$key]) continue;
            $log['PublisherId'] = $id;
            $log['Operator'] = $_SERVER['PHP_AUTH_USER'];
            $log['UpdateTime'] = $date;
            $log['OldValue'] = addslashes($value);
            $log['NewValue'] = addslashes($arr[$key]);
            $log['Field'] = $key;
            $sql = 'INSERT INTO publisher_change_log ('.implode(',',array_keys($log)).') VALUES(\''.implode('\',\'',array_values($log)).'\')';
            $this->mysql->query($sql);
        }
        //处理参数
        foreach ($arr as &$data) {
            $data = trim(addslashes($data));
        }
        //更新publisher
        $sql = "UPDATE publisher SET Status = '{$arr['Status']}',LastUpdateTime = '{$date}',Career = '{$arr['Career']}',Remark='{$arr['Remark']}',Tax='{$arr['Tax']}',Name='{$arr['Name']}',Manager='{$arr['Manager']}' WHERE ID = {$id}";
        mysql_query($sql);
//echo $sql."</br>";

        //更新publisher_account
        $sql = "UPDATE publisher_account SET Status = '{$arr['Status']}', LastUpdateTime = '{$date}' WHERE PublisherId = {$id}";
        mysql_query($sql);
//        echo $sql."</br>";
    }

    /*  function doInsert($arr = array())
      {
          //添加时间
          $date = date('Y-m-d h:i:s');

          //加密
          if($arr['UserPass']) $arr['UserPass'] = md5($arr['UserPass']);
          if($arr['ApiKey']) $arr['ApiKey'] = md5($arr['ApiKey']);

          //处理参数
          foreach ($arr as &$data) {
              $data = trim(addslashes($data));
          }

          //添加publisher表
          $sql = "INSERT INTO publisher (Name,Domain,UserName,UserPass,Status,AddTime,Email,Company,Phone,CompanyAddr,Career)
          VALUES ('{$arr['Name']}','{$arr['Domain']}','{$arr['UserName']}','{$arr['UserPass']}','{$arr['Status']}','{$date}','{$arr['Email']}','{$arr['Company']}','{$arr['Phone']}','{$arr['CompanyAddr']}','{$arr['Career']}' )";
          mysql_query($sql);
          //获取影响id
          $rowid = mysql_insert_id();
          //添加publisher_account
          $sql = "INSERT INTO publisher_account (PublisherId,ApiKey,Name,Domain,Status,AddTime,Alias,Description)
          VALUES ('$rowid','{$arr['ApiKey']}','{$arr['Name']}','{$arr['Domain']}','{$arr['Status']}', '{$date}','{$arr['Alias']}','{$arr['Description']}' )";
          mysql_query($sql);
      }
  */
    public function getLog($id = 0){
        $sql = "SELECT * FROM publisher_change_log WHERE PublisherId = $id ORDER BY UpdateTime ASC";
        $result['data'] = $this->mysql->getRows($sql);
        if(is_array($result['data']) && !empty($result['data'])){
            $result['succ'] = true;
        }
        else{
            $result['succ'] = false;
        }
        echo json_encode($result);die;
    }

    public function getPotentialData($search,$info_type=false){

        $where_arr = array();
        if(isset($search['country']) && !empty($search['country']))
            $where_arr[] = 'country like "%'.addslashes(trim($search['country'])).'%"';

        if(isset($search['category']) && !empty($search['category']))
            $where_arr[] = 'category like "%'.addslashes(trim($search['category'])).'%"';

        if(isset($search['url']) && !empty($search['url']))
            $where_arr[] = 'url like "%'.addslashes(trim($search['url'])).'%"';

        if(isset($search['email']) && !empty($search['email']))
            $where_arr[] = 'email like "%'.addslashes(trim($search['email'])).'%"';

        if(isset($search['datafile']) && !empty($search['datafile']))
            $where_arr[] = 'datafile = "'.addslashes(trim($search['datafile'])).'"';

        if(isset($search['Status']) && !empty($search['Status']) && $search['Status'] != 'All')
            $where_arr[] = 'Status = "'.addslashes(trim($search['Status'])).'"';

        if(isset($search['am']) && !empty($search['am']))
            $where_arr[] = 'am = "'.addslashes(trim($search['am'])).'"';

        $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
        $pagesize = isset($search['pagesize'])?$search['pagesize']:10;
        $page = isset($search['p'])?$search['p']:1;
        if($pagesize < 0 ){
            $limit_str = '';
        }else{
            $limit_str = ' LIMIT '.($page - 1)*$pagesize.','.$pagesize;
        }

        $order_str = '';
        if(isset($search['orderby']) && !empty($search['orderby'])){
            $order_str = ' ORDER BY '.str_replace('-',' ',$search['orderby']);
        }



        if($info_type == 'pagination'){
            $sql = 'SELECT count(*) as c FROM publisher_potential'.$where_str;
            $row = $this->getRow($sql);
            $pageinfo = array();
            $pageinfo['num_all'] = $row['c'];

            $pageinfo['num_st'] = ($page - 1)*$pagesize;
            $pageinfo['page_now'] = $page;
            $pageinfo['page_total'] = ceil($row['c']/$pagesize);

            $sql = 'SELECT status,count(*) as c FROM publisher_potential'.$where_str.' group by status';
            $rows = $this->getRows($sql);
            foreach($rows as $k=>$v){
                $pageinfo['status'][$v['status']] = $v['c'];
            }

            return $pageinfo;
        }elseif($info_type == 'groupstatus'){
            $sql = 'SELECT `status`,count(*) as c FROM publisher_potential'.$where_str.' GROUP BY `status`';
            $row = $this->getRows($sql);
            $tmp = array();
            foreach($row as $k=>$v){
                $tmp[$v['status']] = $v['c'];
            }

            return $tmp;
        }else{
            $sql = 'SELECT * FROM publisher_potential'.$where_str.$order_str.$limit_str;
            return $this->getRows($sql);
        }
    }


    function deletePotential($search){
        $where_arr = array();
        if(isset($search['country']) && !empty($search['country']))
            $where_arr[] = 'country like "%'.addslashes(trim($search['country'])).'%"';

        if(isset($search['category']) && !empty($search['category']))
            $where_arr[] = 'category like "%'.addslashes(trim($search['category'])).'%"';

        if(isset($search['url']) && !empty($search['url']))
            $where_arr[] = 'url like "%'.addslashes(trim($search['url'])).'%"';

        if(isset($search['email']) && !empty($search['email']))
            $where_arr[] = 'email like "%'.addslashes(trim($search['email'])).'%"';

        if(isset($search['datafile']) && !empty($search['datafile']))
            $where_arr[] = 'datafile = "'.addslashes(trim($search['datafile'])).'"';

        if(isset($search['Status']) && !empty($search['Status']) && $search['Status'] != 'All')
            $where_arr[] = 'Status = "'.addslashes(trim($search['Status'])).'"';

        if(isset($search['am']) && !empty($search['am']))
            $where_arr[] = 'am = "'.addslashes(trim($search['am'])).'"';

        if(isset($search['checkboxall']) && $search['checkboxall']){
            unset($search['ppid']);
        }
        if(isset($search['ppid']) && $search['ppid']){
            $where_arr[] = 'id IN ('.join(',',$search['ppid']).')';
        }

        $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
        $sql = 'DELETE FROM publisher_potential'.$where_str;
        $this->query($sql);
    }


    function save_file_to_potential($file){
        ini_set("auto_detect_line_endings", true);//用于 自动识别 \r以及\n
        $am = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
        $flag = false;

        if(file_exists($file['file'])){
            $csv = array();

            if (($handle = fopen($file['file'], "r")) !== FALSE) {
                $line = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $line++;
                    if($line<2)
                        continue;

                    if(empty($data[2])){
                        continue;
                    }

                    foreach($data as $k=>$v){
                        $data[$k] = trim($v);
                    }

                    $encode = '';
                    //get string encoding,change into utf8
                    $encode = mb_detect_encoding($data[0], array('ASCII','UTF-8','GB2312','GBK','BIG5'));
                    if($encode != 'UTF-8'){
                        foreach($data as $k=>$v){
                            $data[$k] = mb_convert_encoding($v,'UTF-8',$encode);
                        }
                    }

                    $urlformat = str_replace(array('http://','https://'),'',$data[2]);
                    $urlformat = trim($urlformat,'/');

                    $csv[] = array(
                        'country'=>$data[0],
                        'category'=>$data[1],
                        'url'=>$data[2],
                        'blogname'=>$data[3],
                        'name'=>$data[4],
                        'email'=>$data[5],
                        'comment'=>$data[6],
                        'datafile' => basename($file['file']),
                        'status'=>'new',
                        'createtime'=>date('Y-m-d H:i:s'),
                        'am'=>$am,
                        'urlformat'=>$urlformat,
                    );
                }

                fclose($handle);
            }

            $sql = 'TRUNCATE publisher_potential_upload';
            $this->query($sql);

            $sql = $this->getInsertSql($csv,'publisher_potential_upload',true);
            $flag = $this->query($sql);
        }

        if($flag)
            return 'true';
        else
            return 'false';
    }

    function get_potential_upload_info(){
        #delete duplicate data when urlform or email is same
        $sql = 'DELETE FROM publisher_potential_upload WHERE id IN (
                    SELECT id FROM(
                        SELECT MAX(id) AS id,urlformat,COUNT(*) AS c FROM publisher_potential_upload GROUP BY urlformat HAVING c > 1
                    ) AS cc
                )';
        $this->query($sql);

        $sql = 'DELETE FROM publisher_potential_upload WHERE id IN (
                    SELECT id FROM(
                        SELECT MAX(id) AS id, email ,COUNT(*) AS c FROM publisher_potential_upload WHERE email NOT LIKE "FILL OUT%" AND email NOT LIKE "CONTACT FORM%" GROUP BY email HAVING c > 1
                    ) AS cc
                )';
        $this->query($sql);
        #delete duplicate data end

        $info = array();
        $sql = 'SELECT * FROM publisher_potential_upload WHERE urlformat NOT IN (SELECT urlformat FROM publisher_potential) AND (email LIKE "FILL OUT%" OR email LIKE "CONTACT FORM%" OR email NOT IN (SELECT email FROM publisher_potential) )';
        $info['unq_data'] = $this->getRows($sql);
        $info['unq_count'] = count($info['unq_data']);

        $sql = 'SELECT count(*) as c FROM publisher_potential_upload';
        $row = $this->getRow($sql);
        $info['all_count'] = $row['c'];
        return $info;
    }

    function save_upload_to_potential(){
        $sql = 'INSERT INTO publisher_potential(country,category,url,blogname,name,email,comment,status,laststatustime,createtime,am,datafile,urlformat) SELECT country,category,url,blogname,name,email,comment,status,laststatustime,createtime,am,datafile,urlformat FROM publisher_potential_upload WHERE urlformat NOT IN (SELECT urlformat FROM publisher_potential) AND (email LIKE "FILL OUT%" OR email LIKE "CONTACT FORM%" OR email NOT IN (SELECT email FROM publisher_potential) )';
        $this->query($sql);

        $sql = 'TRUNCATE publisher_potential_upload';
        $this->query($sql);
    }

    function get_upload_history(){
        $sql = 'SELECT  datafile,createtime,count(*) as c FROM publisher_potential group by datafile order by createtime desc';
        $rows = $this->getRows($sql);
        return $rows;
    }

    function truncate_potential_upload(){
        $sql = 'TRUNCATE publisher_potential_upload';
        $this->query($sql);
    }

    function publisher_potential_mail($search,$action){
        $where_arr = array();
        if(isset($search['datafile'])){
            $where_arr[] = 'datafile = "'.addslashes(trim($search['datafile'])).'"';
        }

        if(isset($search['country']) && !empty($search['country']))
            $where_arr[] = 'country like "%'.addslashes(trim($search['country'])).'%"';

        if(isset($search['category']) && !empty($search['category']))
            $where_arr[] = 'category like "%'.addslashes(trim($search['category'])).'%"';

        if(isset($search['url']) && !empty($search['url']))
            $where_arr[] = 'url like "%'.addslashes(trim($search['url'])).'%"';

        if(isset($search['email']) && !empty($search['email']))
            $where_arr[] = 'email like "%'.addslashes(trim($search['email'])).'%"';

        if(isset($search['datafile']) && !empty($search['datafile']))
            $where_arr[] = 'datafile = "'.addslashes(trim($search['datafile'])).'"';

        if(isset($search['Status']) && !empty($search['Status']) && $search['Status'] != 'All')
            $where_arr[] = 'Status = "'.addslashes(trim($search['Status'])).'"';

        if(isset($search['am']) && !empty($search['am']) )
            $where_arr[] = 'am = "'.addslashes(trim($search['am'])).'"';

        if(isset($search['checkboxall']) && $search['checkboxall'] > 0){
            unset($search['ppid']);
        }elseif(isset($search['ppid']) && !empty($search['ppid'])){
            $where_arr[] = 'id IN ('.join(',',$search['ppid']).')';
        }


        $mailorderlist = array('coldcall_1','coldcall_2','coldcall_3','welcome_1','welcome_2','welcome_3');
        if(in_array($action, $mailorderlist)){
            $pos = array_search($action,$mailorderlist);
            $mail_status_reject = array_slice($mailorderlist, $pos+1);
            if(!empty($mail_status_reject)){
                $where_arr[] = '`status` NOT IN ("'.join('","',$mail_status_reject).'")';
            }
        }else{
            return ;
        }

        $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);

        $sql = 'SELECT id FROM publisher_potential '. $where_str;
        $rows = $this->getRows($sql);

        $ppids = array();
        $contacts = array();

        $operator = isset($_SERVER['PHP_AUTH_USER'])?trim($_SERVER['PHP_AUTH_USER']):'';

        if(!empty($rows)){
            foreach($rows as $k=>$v){
                $ppids[] = $v['id'];
                $contacts[] = array('ppid'=>$v['id'],'type'=>$action,'time'=>date('Y-m-d H:i:s'),'operator'=>$operator);
            }
            $sql = 'UPDATE publisher_potential SET `status` = "'.$action.'",laststatustime="'.date('Y-m-d H:i:s').'" WHERE id in ('.join(',',$ppids).')';
            $this->query($sql);

            $sql = $this->getInsertSql($contacts,'publisher_potential_contact',true);
            $this->query($sql);
        }
    }
}