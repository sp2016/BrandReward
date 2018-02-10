<?php
class MerchantExt{

    function __construct(){
        if(!isset($this->objMysql)) $this->objMysql = new MysqlExt();
        if(!isset($this->objPendingMysql)) $this->objPendingMysql = new MysqlExt(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
        $this->user='felixgu';
        $this->password='598412732';
        $this->PUT_SECRET_KEY_HERE = '575fc7088a6ea3.01011629';
        $this->site = array('us','uk','de');

    }


    function TranslateTime($date,$time_from = "us",$time_to = "us"){
        $time_zone_arr = array(
            "de" => "Europe/Berlin",
            "ca" => "America/Toronto",
            "uk" => "Europe/London",
            "ie" => "Europe/London",
            "nz" => "Pacific/Auckland",
            "us" => "America/Los_Angeles",
            "fr" => "Europe/Paris",
            "in" => "Indian/Antananarivo",
            "au" => "Australia/Sydney");
        if(!isset($time_zone_arr[$time_from])) mydie("No such site");
        $datetime = new DateTime($date,new DateTimeZone($time_zone_arr[$time_from]));
        $datetime->setTimezone(new DateTimeZone($time_zone_arr[$time_to]));
        return $datetime;
    }

    function GetTimeZone($site){
        $time_zone_arr = array(
            "de" => "Europe/Berlin",
            "ca" => "America/Toronto",  
            "uk" => "Europe/London",
            "ie" => "Europe/London",
            "nz" => "Pacific/Auckland",
            "us" => "America/Los_Angeles",
            "fr" => "Europe/Paris",
            "in" => "Indian/Antananarivo",
            "au" => "Australia/Sydney"
        );
        if(isset($time_zone_arr[$site]) && $time_zone_arr[$site])
            return $time_zone_arr[$site];
        else{
            return $time_zone_arr['us'];
        }
    }

    function Register(){
        $sql = "SELECT COUNT(*) AS c FROM domain_outgoing_default_other a INNER JOIN domain b ON a.DID = b.ID WHERE a.Site IN ('us','uk','de','global')";
        $count = $this->objMysql->getRows($sql);
        $count = $count[0]['c'];
        echo "\ttotal:{$count}\n\r";
        $start = 0;
        $total = 0;
        do{
            $result = array();
            $sql = "SELECT a.Site AS Site,a.DID AS DomainId,b.Domain AS Domain FROM domain_outgoing_default_other a INNER JOIN domain b ON a.DID = b.ID WHERE a.Site IN ('us','uk','global') LIMIT $start,100";
            $arr = $this->objMysql->getRows($sql);
            foreach ($arr as $domain){
                if($domain['Site'] == 'global'){
                    foreach ($this->site as $country){
                        $tmp = $this->GetMerchantId($domain['Domain'],$country,$domain['Domain'],$domain['DomainId']);
                        if($tmp){
                            ++$total;
                            $result[] = "('".implode("','",$tmp)."')";
                        }
                    }
                }else{
                    $tmp = $this->GetMerchantId($domain['Domain'],$domain['Site'],$domain['Domain'],$domain['DomainId']);
                    if($tmp){
                        ++$total;
                        $result[] = "('".implode("','",$tmp)."')";
                    }
                }
            }
            if(count($result)<1) continue;
            $value = implode(",",$result);
            $sql = "INSERT IGNORE INTO r_domain_bcg_merchant (DomainId,MerchantId,Site,AddTime) VALUES $value";
            $this->objMysql->query($sql);

            $start+=100;
        }while($start < $count);

        echo "\t----New merchant:$total----\n\r";

    }

    function GetAllMerchantLogo(){
        $sql = "SELECT COUNT(*) as c FROM r_domain_bcg_merchant";
        $count = $this->objMysql->getFirstRow($sql);
        $count = isset($count['c'])&& is_numeric($count['c']) ? $count['c']:0;
        $pagesize = 1000;
        $start = 0;
        $update = '';
        do{
            $sql = "SELECT * FROM r_domain_bcg_merchant LIMIT ".$start*$pagesize.",$pagesize";
            $res = $this->objMysql->getRows($sql);
            foreach ($res as $info){
                $tmp = $this->GetMerchantLogo($info['MerchantId'],$info['Site']);
                if($tmp){
                    $sql = "UPDATE r_domain_bcg_merchant SET Logo = '{$tmp['Logo']}' WHERE MerchantId = {$tmp['MerchantId']} AND Site = '{$tmp['Site']}'";
                    $this->objMysql->query($sql);
                }
            }

//merchant site domain
            $start++;
            echo "\t========".$start*$pagesize."========\n\r";
        }while($start*$pagesize < $count);


    }

    function GetMerchantLogo($id,$site){
//        echo "$id\n\r";
        require_once INCLUDE_ROOT.'api/api_bcg/Common/Lib/Bcgapi.php';
        try {
            $return = array(
                'MerchantId' => $id,
                'Site' => $site
            );
            $o = new BcgApi();
            $s = $o->GetService('merchant');
            $s->Select("market", $site);
            $s->Select("id", $id, "li");
            $p = 1;
            $n = 1000;
            $s->Page($p, $n);
            $rtn = $s->get();
            if(isset($rtn->succ) && $rtn->succ){
                $arr = $rtn->msg;
                if(isset($arr[0]->LOGO) && !empty($arr[0]->LOGO)){
                    $return['Logo'] = addslashes($arr[0]->LOGO);
                    return $return;
                }
            }
            return false;
        }
        catch (Exception $e) {
//            var_dump($e);
        }
    }

    function GetMerchantId($domain,$site,$name,$domainId){
        $post_data = array(
            "gdebug"=> 1,
            "site"=> $site,
            "name"=> $name,
            "url"=> $domain,
        );
        $url = "http://$this->user:$this->password@task.meigexinxi.com/app/public/?act=api&pact=add_merchant&key=$this->PUT_SECRET_KEY_HERE";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        print_r($output."\r\n");
        if(stripos($output,'failed,') !== false){
            preg_match('/(\d+)/',$output,$merchantId);
        }

//        echo $output;
        $return = array(
            'DomainId' => $domainId,
            'MerchantId' => isset($merchantId[1])?$merchantId[1]:'',
            'Site' => $site,
            'AddTime' => date('Y-m-d H:i:s'),
        );

        if(is_numeric($return['MerchantId'])){
            return $return;
        }else{
            return false;
        }
    }

    function GetAllContent($filter,$operator,$date){

        //format lastupdatetime or addtime
        $date = date('Y-m-d H:i:s',strtotime("-$date hours"));

        //get all merchant info count
        $sql = "SELECT COUNT(*) as c FROM r_domain_bcg_merchant";
        $count = $this->objMysql->getFirstRow($sql);
        $count = $count['c'];

        //init page start number
        $start = 0;
        do{
            // foreach merchantid 100/page
            $sql = "SELECT MerchantId,DomainId,Site FROM r_domain_bcg_merchant LIMIT $start,100";
            $arr = $this->objMysql->getRows($sql);
            foreach ($arr as $merchant){
                $this->GetContent($filter,$merchant['Site'],$operator,$date,$merchant['MerchantId']);
            }
            $start+=100;
        }while($start < $count);
    }

    function GetContent($filter,$site,$operator,$date,$merchantId){
//        echo INCLUDE_ROOT.'api/api_bcg/Common/Lib/Bcgapi.php';
        require_once INCLUDE_ROOT.'api/api_bcg/Common/Lib/Bcgapi.php';
        try {
            $o = new BcgApi();

            //Generate promotion service
            $s = $o->GetService('promo');
            //choose site
            $s->Select("market", $site);

            if($filter=='merchantid'){
                $s->Select($filter, $merchantId, $operator);
            }elseif($filter=='lastchangetime'|| $filter=='lastchangetime'){
                $s->Select($filter, $date, $operator);
            }

            $count = 0;
            $p = 1;
            //max pagenumber 1000
            $n = 1000;
            do {
                //Result Navigation, Max return rows is 1000
                $s->Page($p, $n);

                //page detail/content
                $rtn = $s->get();

                if (!isset($rtn->msg) || !$rtn->msg || empty($rtn->msg) || !is_array($rtn->msg))
                    break;

                $col = '';//init col
                $values = '';//init values to insert
                foreach ($rtn->msg as $v) {
//                    backend.meigexinxi.com//couponImage/144360094298.png
                    $v = (array)$v;
                    $v['IdInBcg'] = $v['ID'];
                    $v['Site'] = $site;
                    $v['LastUpdateTime'] = date('Y-m-d H:i:s');
                    unset($v['ID']);
                    if(count($v)!=28) {
                        @mail('felix@brandreward.com','GetAllContent Error','merchantId:'.$merchantId."\n\r".implode(',',array_keys($v)));
                        die("Data Format Error!\n\r");
                    }
                    $col = array_keys($v);
                    foreach ($v as &$val)
                        $val = trim(addslashes($val));
                    $values[] = "('".implode("','",array_values($v))."')";
                }
                if(count($col)!=28) continue;
                $update ='';
                foreach ($col as $k){
                    $update[] = "`$k`=VALUES(`$k`)";
                }
                $update = implode(',',$update);
                $col = "`".strtolower(implode('`,`',array_keys($v)))."`";
                $sql = "INSERT INTO merchant_content ($col) VALUES ".implode(',',$values)." ON DUPLICATE KEY UPDATE $update";
                $this->objMysql->query($sql);
//                file_put_contents('a.txt',$sql);
//                echo $sql;die;
                echo "==============================".($p*$n) ." | ". $rtn->totalrows .'======================'."\n";
            } while (++$p*$n <= $rtn->totalrows);
        }
        catch (Exception $e) {
            var_dump($e);
        }
    }

    function FormatContent(){
        $update_time = date('Y-m-d H:i:s');
        $upda_time = date('Y-m-d');
        echo " \tformat start @$update_time\n\r";

        //get total number
        $sql="SELECT COUNT(*) as c FROM merchant_content WHERE IsActive='YES'";
        $result = $this->objMysql->getFirstRow($sql);


        $update_arr = '';
//        $keys = '';
        $start = 0;
        $pagesize = 10000;
        do{
            $sql = "SELECT a.*,b.Logo FROM merchant_content a LEFT JOIN r_domain_bcg_merchant b ON (a.MerchantId=b.MerchantId AND a.Site=b.Site) WHERE IsActive='YES' LIMIT ".$start*$pagesize.",$pagesize";
            echo "\t========".$start*$pagesize."=========\n\r";
            $arr = $this->objMysql->getRows($sql);
            foreach ($arr as $value){
                $tmp['StartTime'] = $value['StartTime'];
                $tmp['ExpireTime'] = $value['ExpireTime'];
                $tmp['TimeZone'] = $this->GetTimeZone($value['Site']);

                $tmp_translate_start = $this->TranslateTime($tmp['StartTime'],$value['Site']);
                $tmp_translate_start = (array)$tmp_translate_start;
                $tmp_translate_expire = $this->TranslateTime($tmp['ExpireTime'],$value['Site']);
                $tmp_translate_expire = (array)$tmp_translate_expire;

                $j1 = date('Y-m-d H:i:s',strtotime("+1 day"));
                $j3 = date('Y-m-d H:i:s',strtotime("-3 day"));
//                if(!($tmp_translate_start['date'] < $j1 || $tmp_translate_expire['date'] > $j3))
                if(!($tmp_translate_start['date'] < $update_time && $tmp_translate_expire['date']>$update_time))
                    continue;

                $tmp['ImgUrl'] = $tmp['ImgFile'] = $img_path_tmp ='';

                //http://csusbackend.megainformationtech.com/merImage/
                if($value['Logo']){
                    $img_tmp['advertiser'] = addslashes("http://cs".$value['Site']."backend.megainformationtech.com/merImage/".$value['Logo']);
                    $tmp['ImgUrl'] = json_encode($img_tmp);
                    $img_file['advertiser'] = addslashes("/BDG/".$value['Logo']);
                    $tmp['ImgFile'] = json_encode($img_file);
                }

                if(empty($value['Title']) || empty($value['DstUrl'])) continue;

                $tmp['Title'] = addslashes($value['Title']);
                $tmp['Code'] = addslashes($value['Code']);

                if($value['Code']){
                    $tmp['Type'] = "coupon";
                }else{
                    $tmp['Type'] = "promotion";
                }

                $tmp['Desc'] = addslashes($value['Remark']);
                $tmp['Url'] = addslashes($value['DstUrl']);
                $tmp['Source'] = "BCG";
                $tmp['SourceKey'] = $value['IdInBcg'];

                $tmp['UpdateTime'] = $tmp['CreateTime'] = $update_time;
                $tmp['Updated'] = $tmp['Created'] = $upda_time;
                $tmp['IsActive'] = 'YES';

                $sql = "SELECT s.name FROM r_domain_bcg_merchant a INNER JOIN r_store_domain b ON a.domainid = b.domainid INNER JOIN store s ON b.storeid = s.id WHERE a.merchantid= ".$value['MerchantId']." AND a.Site = '".$value['Site']."'";
                $advertiser = $this->objMysql->getFirstRow($sql);
                $tmp['Advertiser_Name'] = isset($advertiser['name']) && !empty($advertiser['name'])? addslashes($advertiser['name']):'';
//                $tmp['ImgIsDownload'] = ;
                $update_arr[] = $tmp;
            }
            $this->UpdateContent($update_arr);
            $update_arr = '';
            $start+=1;
        }while($start*$pagesize < $result['c']);
        $sql = "UPDATE c_content_feed SET IsActive = 'NO' WHERE UpdateTime < '{$update_time}' AND Created > '2016-07-06'";
//        echo $sql;
        $this->objMysql->query($sql);
        echo "\tformat end @".date('Y-m-d H:i:s');
    }

    function UpdateContent($arr){
        if(!is_array($arr)) return;
        $keys = $update = $end_update = '';
        foreach ($arr as $v){
            if(empty($keys)) $keys = array_keys($v);
            $update[] = "('".implode("','",$v)."')";
        }
        if(empty($keys)) return;

        //insert key
        $col = "(`".implode('`,`',$keys)."`)";

        //insert value
        $value = implode(",",$update);

        //update key and value
        foreach ($keys as $val){
            if(in_array($val,array('Created','CreateTime'))) continue;
            $end_update[] = "`$val` =VALUES(`$val`)";
        }

        $end_update = implode(',',$end_update);

        $sql = "INSERT INTO c_content_feed $col VALUES $value ON DUPLICATE KEY UPDATE $end_update";
//        echo $sql;die;
        $this->objMysql->query($sql);
    }






    function getcsv(){
        $sql = "SELECT MerchantId,Site from r_domain_bcg_merchant";
        $arr = $this->objMysql->getRows($sql);
        exec('touch merchant.csv');
        $handle = fopen('merchant.csv','w');
        foreach ($arr as $row){
            fputcsv($handle,$row);
        }
        fclose($handle);
    }
}