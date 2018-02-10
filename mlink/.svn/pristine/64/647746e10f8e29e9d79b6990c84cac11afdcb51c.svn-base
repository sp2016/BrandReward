<?php

class Tools extends LibFactory
{



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
        $date = date("Ymd-H:i:m");
        $stdArr = json_decode($jsonStr);
        $data = $this->object_array($stdArr);
        $data['p'] = 0;
        $arr = $listObj->$listFunction($data, 1000);//防止内存爆掉，每次取1k条
        $page_total = $arr['page']['page_total'];
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= {$listFunction}{$date}.csv");
        echo "Created,Program Name,Program Id,Site Url,Page Url,Session Id\n";
        do {
            $data['p']++;
            $arr = $listObj->$listFunction($data, 1000);
            $arr = $arr['tran'];
            foreach ($arr as $v) {
                echo $v['created'] . "," . $v['Name'] . "," . $v['IdInAff'] . "," . $v['Domain'] . "," . $v['pageUrl'] . "," . $v['sessionId'] . "\n";
            }
        } while ($data['p'] <= $page_total);
    }
    
    /**
     * 看是否是能为我们赚钱的网站
     */
    function find_is_our_domain($url,$siteType){
        //验证字符串为http://或https://或者没有http信息为开头，中间格式为xx.xx   xx.xx.xx   xx.xx.xx.xx  例如：baidu.com   www.baidu.com  www.baidu.com.cn
        preg_match('/^(http[s]?:\/\/)?([A-Za-z0-9_]*[.][A-Za-z0-9_]*[.][A-Za-z0-9_]*[.][A-Za-z0-9_]*|[A-Za-z0-9_]*[.][A-Za-z0-9_]*[.][A-Za-z0-9_]*|[A-Za-z0-9_]*[.][A-Za-z0-9_]*)/', $url,$arr);
        if(isset($arr[2]) && $arr[2]!=null){
            $where_str_store = '';
            /* $checksql = "select sitetype from publisher_detail where PublisherId='{$publisherId}'";
            $checkrow = $this->getRow($checksql);
            $checkarr = explode('+',$checkrow['sitetype']);
            $siteType = 'content';
            foreach($checkarr as $k){
                if($k == '1_e' || $k == '2_e'){
                    $siteType = 'coupon';
                    break;
                }
            } */
            $where_str_store.=" AND s.SupportType != 'None'";
            if($siteType == 'coupon'){
                $where_str_store.=" AND s.SupportType != 'Content' ";
            }else{
                $where_str_store.=" AND s.SupportType != 'Promotion' ";
            }
            if(substr($arr[2], 0,4)=="www."){
                $url = substr($arr[2],4);
                $where_str_store.=" AND ( d.Domain = '$url' ) ";
            }else {
                $url = $arr[2];
                $mainUrl = substr($arr[2], strpos($arr[2], '.')+1);
                $where_str_store.=" AND ( d.Domain = '$url' or d.Domain = '$mainUrl' ) ";
            }
            $sql = 'SELECT * FROM domain d LEFT JOIN r_store_domain rsd on rsd.DomainId = d.ID LEFT JOIN store s on rsd.StoreId = s.ID WHERE 1=1 '.$where_str_store;
            $row = $this->getRows($sql);
            if(!empty($row)){
                try {
                    $redis = RedisManage::getRedis();
                    if($redis->get(':D:'.$url)){
                        return 1;
                    }else if(isset($mainUrl) && $redis->get(':D:'.$mainUrl)){
                        return 1;
                    }else {
                        return 0;
                    }
                } catch (\Exception $e) {
                    return 0;
                }
            }else {
                return 0;
            }
        }else {
            return 0;
        }
    }

}