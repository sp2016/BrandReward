<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'utf8'");
$Publisher = new Publisher();
$res = $msg = $csv = '';

if(isset($_FILES) && !empty($_FILES) && !empty($_FILES['upload_transaction']['tmp_name'])){
    $file_name = $_FILES['upload_transaction']['tmp_name'];
    ini_set("auto_detect_line_endings", true);//用于 自动识别 \r以及\n
    $res = true;
    $msg = '';
    $csv = array();
    if(file_exists($file_name)){
        if (($handle = fopen($file_name, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;
                if($line<2)
                    continue;

                foreach($data as $k=>$v){
                    $data[$k] = trim($v);
                }

                if(empty($data[2])){
                    continue;
                }

                $apikey = $data[4];
                $sql = 'select * from publisher_account WHERE apikey = "'.addslashes($apikey).'"';
                $row = $Publisher->getRows($sql);
                if(empty($row)){
                    echo '<pre>';print_r($data);exit();
                    $res = false;
                    $msg = 'apikey is not exist "'.$apikey.'" , in line '.$line;
                    break;
                }

                $tmp = $data;
                $tmp[2] = 'http://r.brandreward.com?key='.$apikey.'&url='.urlencode($data[2]);
                $csv[] = $tmp;
            }

            fclose($handle);
        }
    }
}

$objTpl->assign('res', $res);
$objTpl->assign('msg', $msg);
$objTpl->assign('csv', $csv);
$objTpl->assign('title','Quick Link');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_quicklink.html');