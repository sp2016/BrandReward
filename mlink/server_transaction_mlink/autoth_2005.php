<?php
/**
 * User: rzou
 * Date: 2017/8/14
 * Time: 17:39
 */
define('AFF_NAME', AFFILIATE_NAME);
define('API_UNAME', AFFILIATE_USER);
define('API_PASS', AFFILIATE_PASS);
define('FILE_FORMAT', "{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
$file_cook = PATH_CODE . '/cookie/' . AFF_NAME . '.cook';

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

list($s_y, $s_m, $s_d) = explode('-', $begin_dt);
list($e_y, $e_m, $e_d) = explode('-', $end_dt);

$user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2';

$url_aff = 'http://automotivetouchup.com/affiliatewiz/affiliate/';
$url_login = 'http://automotivetouchup.com/affiliatewiz/affiliate/login.aspx';
$url_report = 'http://automotivetouchup.com/affiliatewiz/affiliate/Reports.aspx?D=Reports';
$url_report_sales = 'http://automotivetouchup.com/affiliatewiz/affiliate/Reports.aspx?D=Reports';
$url_download = 'http://automotivetouchup.com/affiliatewiz/affiliate/Reports_SalesDetail.aspx?D=Reports';

#step 1 => get post data
echo 'Req : ' . $url_aff . "\n";
$ch = curl_init($url_aff);
$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);

preg_match('/<input.*name="__EVENTVALIDATION".*value="(.*)".*\/>/', $pass, $m);
preg_match('/<input.*name="__VIEWSTATE".*value="(.*)".*\/>/', $pass, $n);

$__EVENTVALIDATION = $m[1];
$__VIEWSTATE = $n[1];

#step 2 => do login
echo 'Req : ' . $url_login . "\n";
$ch = curl_init($url_login);

$opt = array();
$opt[] = urlencode('__EVENTTARGET') . '=';
$opt[] = urlencode('__EVENTARGUMENT') . '=';
$opt[] = urlencode('__LASTFOCUS') . '=';
$opt[] = urlencode('__VIEWSTATE') . '=' . urlencode($__VIEWSTATE);
$opt[] = urlencode('__EVENTVALIDATION') . '=' . urlencode($__EVENTVALIDATION);
$opt[] = urlencode('_ctl0:drpLanguageID') . '=' . '1';
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:LoginName') . '=' . API_UNAME;
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:LoginPassword') . '=' . API_PASS;
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:SubmitButton') . '=' . 'Login';

$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_NOBODY => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => join('&', $opt),
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);

#step 3 => get post data
echo 'Req : ' . $url_report . "\n";
$ch = curl_init($url_report);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);

preg_match('/<input.*name="__EVENTVALIDATION".*value="(.*)".*\/>/', $pass, $m);
preg_match('/<input.*name="__VIEWSTATE".*value="(.*)".*\/>/', $pass, $n);

$__EVENTVALIDATION = $m[1];
$__VIEWSTATE = $n[1];

#step 4 => get post data
echo 'Req : ' . $url_report_sales . "\n";

$opt = array();
$opt[] = urlencode('__EVENTTARGET') . '=' . urlencode('_ctl0$ContentPlaceHolder1$ReportList1$ReportName');
$opt[] = urlencode('__EVENTARGUMENT') . '=';
$opt[] = urlencode('__LASTFOCUS') . '=';
$opt[] = urlencode('__VIEWSTATE') . '=' . urlencode($__VIEWSTATE);
$opt[] = urlencode('__EVENTVALIDATION') . '=' . urlencode($__EVENTVALIDATION);
$opt[] = urlencode('_ctl0:drpLanguageID') . '=' . '1';
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:ReportList1:ReportName') . '=' . urlencode('Reports_SalesDetail.aspx?D=Reports');

$ch = curl_init($url_report_sales);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => join('&', $opt),
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);

preg_match('/<input.*name="__EVENTVALIDATION".*value="(.*)".*\/>/', $pass, $m);
preg_match('/<input.*name="__VIEWSTATE".*value="(.*)".*\/>/', $pass, $n);

$__EVENTVALIDATION = $m[1];
$__VIEWSTATE = $n[1];

#step 5 => get csv download data
$fw = fopen($file_temp, 'w');
echo 'Req : ' . $url_download . "\n";
$ch = curl_init($url_download);

$opt = array();
$opt[] = urlencode('__EVENTTARGET') . '=';
$opt[] = urlencode('__EVENTARGUMENT') . '=';
$opt[] = urlencode('__LASTFOCUS') . '=';
$opt[] = urlencode('__VIEWSTATE') . '=' . urlencode($__VIEWSTATE);
$opt[] = urlencode('__EVENTVALIDATION') . '=' . urlencode($__EVENTVALIDATION);
$opt[] = urlencode('_ctl0:drpLanguageID') . '=' . '1';
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:ReportList1:ReportName') . '=' . urlencode('Select A Report');
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:StartYear') . '=' . $s_y;
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:StartMonth') . '=' . intval($s_m);
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:StartDay') . '=' . intval($s_d);
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:EndYear') . '=' . $e_y;
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:EndMonth') . '=' . intval($e_m);
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:EndDay') . '=' . intval($e_d);
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:SaleStatus') . '=' . urlencode('Approved');
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:ReportFormat') . '=' . urlencode('CSV');
$opt[] = urlencode('_ctl0:ContentPlaceHolder1:btnCreateReport') . '=' . urlencode('Create Report');

$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_NOBODY => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => join('&', $opt),
    CURLOPT_FILE => $fw,
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);
fclose($fw);

$fp = fopen($file_temp, 'r');
if (!$fp) {
    mydie("File open failed {$file_temp}");
}

$line = 0;
$fws = array();
$Curency = 'USD';
while (($data = fgetcsv($fp)) !== false) {
    $line++;
    if ($line < 2)
        continue;

    $event_dt = date('Y-m-d H:i:s', strtotime($data[2]));
    $oldCommission = $data[9];
    $cur_exr = cur_exchange($Curency, 'USD', $event_dt);
    $Commission = $oldCommission > 0 ? round($oldCommission * $cur_exr, 4) : 0;
    $cancelreason = '';

    $replace_array = array(
        '{createtime}' => trim($event_dt),
        '{updatetime}' => trim($event_dt),
        '{sales}' => null,
        '{commission}' => $Commission,
        '{idinaff}' => null,
        '{programname}' => null,
        '{sid}' => trim($data[10]),
        '{orderid}' => trim($data[1]),
        '{clicktime}' => trim($event_dt),
        '{tradeid}' => trim($data[0]),
        '{tradestatus}' => 'Accepted',
        '{oldcur}' => $Curency,
        '{oldsales}' => null,
        '{oldcommission}' => $oldCommission,
        '{tradetype}' => 'sales',
        '{referrer}' => trim($data[11]),
        '{cancelreason}'    => $cancelreason,
    );

    $tdate = date("Y-m-d",strtotime($data[2]));
    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $tdate) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }
    fwrite($fws[$rev_file], strtr(FILE_FORMAT, $replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}

?>
