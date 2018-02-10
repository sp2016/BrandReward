<?php
require_once PATH_CODE.'/lib/PHPExcel/IOFactory.php';
require_once PATH_CODE.'/lib/PHPExcel.php';
require_once PATH_CODE.'/lib/currency_exchange.php';
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
try {
        define('AFF_NAME', AFFILIATE_NAME);
        define('USER_NAME', AFFILIATE_USER);
        define('USER_PASS', AFFILIATE_PASS);

    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d', strtotime(END_TIME));
        $begin_dt = date('Y-m-d', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d');
        $begin_dt = date('Y-m-d', strtotime('-120 days'));
    }

    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

    $file_temp = PATH_TMP . '/' . AFF_NAME . '.xlsx';
    if (file_exists($file_temp))
        unlink($file_temp);
    $file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';
    if (file_exists($file_cook))
        unlink($file_cook);
    //login
    $url = 'https://admin.omgpm.com/en/clientarea/login.asp';
    $rtry = 0;
    $pass = true;
    do {
        if(++$rtry > 3)
            break;
        $url = "https://admin.optimisemedia.com/v2/login_welcome.aspx";//login

        $posts = array();
        array_push($posts, 'EmailAddress=' . urlencode(USER_NAME));
        array_push($posts, 'Password=' . urlencode(USER_PASS));
        array_push($posts, 'Submit=Login');
        echo "req=>{$url}\n";
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => implode('&', $posts),
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);

        $url = 'https://admin.optimisemedia.com/v2/reports/affiliate/leads/leadSummaryReport.aspx';//get some post data

        echo "req=>{$url}\n";
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);


// get report list
       
        $posts = array();
        if (preg_match_all('#id=\"__VIEWSTATE\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__VIEWSTATE=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"__VIEWSTATEGENERATOR\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__VIEWSTATEGENERATOR=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"__EVENTVALIDATION\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__EVENTVALIDATION=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#name=\"ContentPlaceHolder1_gvReport_custwindowWS\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, 'ContentPlaceHolder1_gvReport_custwindowWS=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"ContentPlaceHolder1_gvReport_CallbackState\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, 'ContentPlaceHolder1_gvReport_CallbackState=' . urlencode($m[1][0]));
        }
        if (preg_match('#src=\"/v2/DXR\.axd\?r=(.*)-PFEN9#', $rs, $m)) {
            array_push($posts, 'DXScript=' . urlencode($m[1]));
        }


        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLQuickPeriod') . '=' . urlencode('Today'));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$dpStart$txtProcessDate') . '=' . urlencode($begin_dt));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$dpEnd$txtProcessDate') . '=' . urlencode($end_dt));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLSearchDate') . '=2');
        array_push($posts, urlencode('ctl00$Uc_Navigation1$ddlNavSelectMerchant') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLMerchant') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLProduct') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLStatus') . '=-1');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLCountry') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$ButtonGet') . '=Get Report');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$gvReport$DXKVInput') . '=' . urlencode('[]'));
        array_push($posts, 'DXCss=' . urlencode('1_4,1_12,1_14,1_5,1_3,1_8,1_10,1_7,1_1,/Templates/118_sec.css,/templates/CustomMerchantCSS/382508.css,../../../Dashboard/Styles/jquery-ui-1.8.20.custom.css,/v2/WebResource.axd?d=em64lc8cGDwPcV5nSvCsoCNTCRxlQdyKN19XR-u0-91m5KxoYcs9-I5LJ-SfjGfesTP2AZ7GDB-c3qjDrRMJaS0IUJJRiXDbcIo8GJD2WulO1odRuITVS3r660c99pJ7N_qZiZ0N6ZiWiwSPws7jM9Qj0eTO4xY68Q-8BERtHBg1&t=635645202682132223,/Templates/1_reports.css'));

        echo "req=>{$url}\n";
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => implode('&', $posts),
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);
        


        //download excel
        $posts = array();
        if (preg_match_all('#id=\"__VIEWSTATE\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__VIEWSTATE=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"__VIEWSTATEGENERATOR\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__VIEWSTATEGENERATOR=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"__EVENTVALIDATION\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, '__EVENTVALIDATION=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#name=\"ContentPlaceHolder1_gvReport_custwindowWS\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, 'ContentPlaceHolder1_gvReport_custwindowWS=' . urlencode($m[1][0]));
        }
        if (preg_match_all('#id=\"ContentPlaceHolder1_gvReport_CallbackState\" value=\"(.*?)\" \/>#', $rs, $m)) {
            array_push($posts, 'ContentPlaceHolder1_gvReport_CallbackState=' . urlencode($m[1][0]));
        }
        if (preg_match('#src=\"/v2/DXR\.axd\?r=(.*)-PFEN9#', $rs, $m)) {
            array_push($posts, 'DXScript=' . urlencode($m[1]));
        }

        array_push($posts, urlencode('__EVENTTARGET') . '=' . urlencode('ctl00$ContentPlaceHolder1$btnXlsExport'));
        array_push($posts, urlencode('__EVENTARGUMENT') . '=' . urlencode('Click'));
        array_push($posts, urlencode('ctl00$Uc_Navigation1$ddlNavSelectMerchant') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLQuickPeriod') . '=' . urlencode('Today'));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$dpStart$txtProcessDate') . '=' . urlencode($begin_dt));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$dpEnd$txtProcessDate') . '=' . urlencode($end_dt));
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLSearchDate') . '=2');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLMerchant') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLProduct') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLStatus') . '=-1');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$DDLCountry') . '=0');
        array_push($posts, urlencode('ctl00$ContentPlaceHolder1$gvReport$DXKVInput') . '=' . urlencode('[]'));
        array_push($posts, 'DXCss=' . urlencode('1_4,1_12,1_14,1_5,1_3,1_8,1_10,1_7,1_1,/Templates/118_sec.css,/templates/CustomMerchantCSS/382508.css,../../../Dashboard/Styles/jquery-ui-1.8.20.custom.css,/v2/WebResource.axd?d=em64lc8cGDwPcV5nSvCsoCNTCRxlQdyKN19XR-u0-91m5KxoYcs9-I5LJ-SfjGfesTP2AZ7GDB-c3qjDrRMJaS0IUJJRiXDbcIo8GJD2WulO1odRuITVS3r660c99pJ7N_qZiZ0N6ZiWiwSPws7jM9Qj0eTO4xY68Q-8BERtHBg1&t=635645202682132223,/Templates/1_reports.css'));

        $ch = curl_init($url);
        $fw = fopen($file_temp, 'w');
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => implode('&', $posts),
            CURLOPT_FILE => $fw,
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);

    } while (!$pass);


//use PHPExcel library to parse the .xlsx file to an array


    function getascii($c)
    {
        if (strlen($c) == 1)
            return ord($c) - 65;
        return ord($c[1]) - 38;
    }



    function excelParse($file_temp)
    {
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($file_temp)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($file_temp)) {
                echo 'no Excel';
            }
        }

        $PHPExcel = $PHPReader->load($file_temp);
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得一共有多少列*/

        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/

        $allRow = $currentSheet->getHighestRow();
        $all = array();
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {

            $flag = 0;
            $col = array();
            for ($currentColumn = 'A'; getascii($currentColumn) <= getascii($allColumn); $currentColumn++) {

                $address = $currentColumn . $currentRow;

                $string = $currentSheet->getCell($address)->getValue();

                $col[$flag] = $string;

                $flag++;
            }
            $all[] = $col;
        }
        return $all;
    }



    function excelTime($t){
        return gmdate("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($t));
    }


    $all = excelParse($file_temp);
    fclose($fw);

    $curr = array();

    foreach ($all as $k => $v) {
        if ($v[0] == 'Row#')
            continue;
        /*        if (!isset($v[1]) || empty($v[1]))
                    continue;*/

        $click_dt = excelTime(trim($v[1]));
        $transaction_dt = excelTime(trim($v[2]));
        $oldsale = round(floatval(trim($v[22])),4);
        $oldcomm = round(floatval(trim($v[15])),4);
        $idInAff = trim($v[12]);
        $pName = trim($v[11]);
        //$sid = trim($v[5]);
        $sid = '';
        if($v[5])
            $sid = trim($v[5]);
        elseif($v[6])
            $sid = trim($v[6]);
        elseif($v[7])
            $sid = trim($v[7]);
        elseif($v[8])
            $sid = trim($v[8]);
        elseif($v[9])
            $sid = trim($v[9]);
        
        $orderId = trim($v[3]);
        $tradeStatus = trim($v[18]);
        $currency = trim($v[23]);
        $tradeId = trim($v[21]);
        $tradeType = trim($v[13]);

        $date = date('Y-m-d', strtotime($transaction_dt));
        if(!isset($curr[$currency][$date])){
            $curr[$currency][$date] = cur_exchange($currency, 'USD', $date);
        }

        $cur_exr = $curr[$currency][$date];
        $sale = round($oldsale * $cur_exr, 4);
        $rev = round($oldcomm * $cur_exr, 4);

        $referrer = trim($v[14]);
        $cancelreason = '';
        
        $replace_array = array(
            '{createtime}'      => $transaction_dt,
            '{updatetime}'      => $transaction_dt,
            '{sales}'           => $sale,
            '{commission}'      => $rev,
            '{idinaff}'         => $idInAff,
            '{programname}'     => $pName,
            '{sid}'             => $sid,
            '{orderid}'         => $orderId,
            '{clicktime}'       => $click_dt,
            '{tradeid}'         => $tradeId,
            '{tradestatus}'     => $tradeStatus,
            '{oldcur}'          => $currency,
            '{oldsales}'        => $oldsale,
            '{oldcommission}'   => $oldcomm,
            '{tradetype}'       => '',
            '{referrer}'        => $referrer,
            '{cancelreason}'    => $cancelreason,
        );

        $dump[$date][] = strtr(FILE_FORMAT,$replace_array)."\n";
        //$dump[$date][] = $transaction_dt . "\t" . $transaction_dt . "\t" . $sale . "\t" . $rev . "\t" . $idInAff . "\t" . $pName . "\t" . $sid . "\t" . $orderId. "\t" . $click_dt. "\t" . $tradeId. "\t" . $tradeStatus. "\t" . $currency. "\t" . $oldsale. "\t" . $oldcomm. "\t" . $tradeType."\n";
    }

    $file = PATH_DATA . '/' . AFF_NAME;
    echo $file . '/n';
    if (!is_dir($file))
        mkdir($file, 0755);
    foreach ($dump as $d => $val) {
        $file_new = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $d) . '.upd';
        $fw = fopen($file_new, 'w');
        if (!$fw)
            continue;
        foreach ($val as $k => $v) {
            fwrite($fw, $v);
        }
        fclose($fw);
    }
//    fclose($fw);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}
?>
