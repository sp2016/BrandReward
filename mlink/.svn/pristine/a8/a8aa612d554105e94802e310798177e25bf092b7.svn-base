<?php
define('AFF_NAME', AFFILIATE_NAME);
define('USR_NAME', AFFILIATE_USER);
define('USR_PASS', AFFILIATE_PASS);//9rCLDldjefws
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");


$file_temp = PATH_TMP . '/' . AFF_NAME . '.csv';
$file_zip  = PATH_TMP . '/' . AFF_NAME . '.zip';    
$file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';    

//setting request steps
$steps[0]['url'] = 'http://publisher.tradedoubler.com/public/aLogin.action';
$steps[0]['req'] = 'GET';
$steps[0]['err'] = 'landing failed!';
$steps[0]['tag'] = 'j_username';
$steps[0]['dat'] = '';
$steps[0]['try'] = 0;


$steps[1]['url'] = 'http://publisher.tradedoubler.com/pan/login';
$steps[1]['req'] = 'POST';
$steps[1]['err'] = 'login failed!';
$steps[1]['tag'] = 'Account balance';
$steps[1]['dat'] = 'j_username=' . urlencode(USR_NAME) . '&j_password=' . urlencode(USR_PASS);
$steps[1]['try'] = 3;

$steps[2]['url'] = 'http://publisher.tradedoubler.com/pan/aReport3.action';
$steps[2]['req'] = 'POST';
$steps[2]['err'] = 'reporting failed';
//$steps[2]['tag'] = 'EPI;;Program;Program;Order value;Commission';
$steps[2]['tag'] = '';
$steps[2]['zip'] = 'is too big to be shown as normal. If you want you can download the result from';
$steps[2]['dom'] = 'http://publisher.tradedoubler.com/';    
$steps[2]['try'] = 0;

if (defined('START_TIME') && defined('END_TIME')) {
	$end_dt = END_TIME;
	$begin_dt = START_TIME;
} else {
	$end_dt = date('Y-m-d');
	$begin_dt = date('Y-m-d', strtotime("-120 days"));
}
$temp_begin = $begin_dt;
$temp_end = $end_dt;
$dateArr = get_date_range_arr($begin_dt,$end_dt,'15 day','d/m/y');
krsort($dateArr);
foreach($dateArr as $dateRange){
	$begin_dt = $dateRange['start_dt'];
	$end_dt = $dateRange['end_dt'];
	echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";
	$steps[2]['dat'] = 'reportName=aAffiliateEventBreakdownReport&tabMenuName=&isPostBack=&showAdvanced=true&showFavorite=false&run_as_organization_id=&minRelativeIntervalStartTime=0&maxIntervalSize=0&interval=&reportPrograms=&reportTitleTextKey=REPORT3_SERVICE_REPORTS_AAFFILIATEEVENTBREAKDOWNREPORT_TITLE&setColumns=true&latestDayToExecute=0&affiliateId=&currencyId=USD&pending_status=1&emptyPlaceHolder_0=&sortBy=timeOfEvent&breakdownOption=1&programId=&period=custom_period&startDate=' . urlencode($begin_dt) . '&endDate=' . urlencode($end_dt) . '&event_id=0&filterOnTimeHrsInterval=false&dateSelectionType=1&includeWarningColumn=true&autoCheckbox=columns&autoCheckbox=columns&columns=programId&autoCheckbox=columns&autoCheckbox=columns&columns=timeOfEvent&autoCheckbox=columns&autoCheckbox=columns&columns=lastModified&autoCheckbox=columns&columns=epi1&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&columns=pendingStatus&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&autoCheckbox=columns&columns=affiliateCommission&columns=link&autoCheckbox=columns&columns=leadNR&autoCheckbox=columns&columns=orderNR&autoCheckbox=columns&autoCheckbox=columns&columns=orderValue&autoCheckbox=columns&autoCheckbox=useMetricColumn&customKeyMetricCount=0&metric1.name=&metric1.midFactor=&metric1.midOperator=%2F&metric1.columnName1=orderValue&metric1.operator1=%2F&metric1.columnName2=orderValue&metric1.lastOperator=%2F&metric1.factor=&metric1.summaryType=NONE&format=CSV&separator=%2C&dateType=1&favoriteId=&favoriteName=&favoriteDescription=';

	foreach ($steps as $k => $v) {
		$rtry = 0;
		do {

			//download signature order report for all networks
			if (file_exists($file_temp))
				unlink($file_temp);

			$fw = fopen($file_temp, 'w');
			if (!$fw)
				throw new Exception("File open failed {$file_temp}");

			$url = $v['url'];

			$ch = curl_init($url);
			$curl_opts = array(CURLOPT_HEADER => false,
				CURLOPT_NOBODY => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_COOKIEJAR => $file_cook,
				CURLOPT_COOKIEFILE => $file_cook,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_FILE => $fw,
			);

			if ($v['dat'] != '') {
				$curl_opts[CURLOPT_POST] = true;
				$curl_opts[CURLOPT_POSTFIELDS] = $v['dat'];
			}
			curl_setopt_array($ch, $curl_opts);

			echo "req => {$url} ";
			$pass = curl_exec($ch);
			curl_close($ch);
			fclose($fw);

			if (!$pass) {
				echo " failed, waiting for 60 seconds retry \n";
				sleep(60);
			} else {
				$rs = file_get_contents($file_temp);
				if ($v['tag'] != '' && stripos($rs, $v['tag']) === false) {
					$pass = false;
					echo " {$v['err']}, waiting for 60 seconds retry \n";
                } 
                elseif (isset($v['zip'])) {
                    $pos = stripos($rs, $v['zip']);
                    //dump zip file
                    if ($pos !== false && preg_match('/href="([^"]+)"/', substr($rs, $pos), $m) && $m[1] != '') {
        				if (file_exists($file_zip))
		            		unlink($file_zip);

        				$fw = fopen($file_zip, 'w');
		            	if (!$fw)
        					throw new Exception("File open failed {$file_zip}");                     
        
                        $url = $v['dom'].$m[1];                    
        				$ch = curl_init($url);
		            	$curl_opts = array(CURLOPT_HEADER => false,
                        					CURLOPT_NOBODY => false,
                        					CURLOPT_RETURNTRANSFER => true,
                        					CURLOPT_FOLLOWLOCATION => true,
                        					CURLOPT_COOKIEJAR => $file_cook,
                        					CURLOPT_COOKIEFILE => $file_cook,
                        					CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                        					CURLOPT_FILE => $fw,
                                    );
        				curl_setopt_array($ch, $curl_opts);

		            	echo "\n req => {$url} ";
        				$pass = curl_exec($ch);
		            	curl_close($ch);
                        fclose($fw);                            

                        //unzip
                        exec("unzip -p {$file_zip} > {$file_temp}", $output, $rs);
                        if ($rs !== 0)
                            throw new Exception ("unzip file failed");
                    }
					echo " successed \n";
                }
                else {
					echo " successed \n";                    
                }
			}

			if (!$pass && $v['try'] > 0 && $v['try'] < ++$rtry)
				throw new Exception("Exceed max re-try limits {$v['try']}");
		}
		while (!$pass);
	}


	$fp = fopen($file_temp, 'r');
	if (!$fp)
		throw new Exception("File open failed {$file_temp}");

	$k = 0;
	$dump = array();
	$curr = array();
    $fws = array();
	while (!feof($fp)) {
        
		$lr = fgetcsv($fp);

		if (++$k <= 2)
			continue;

		if ($lr[0] == "")
			continue;

		/*
		  0===>Name
		  1===>Prepayment Status
		  2===>ID
		  3===>of event
		  4===>Last modified
		  5===>Lead nr
		  6===>Order nr
		  7===>EPI 1
		  8===>Status
		  9===>Order Value
		  10===>Publisher
     */
		if (preg_match('/([\d]{2})\/([\d]{2})\/([\d]{2}) ([\d]{2}:[\d]{2}:[\d]{2})/', $lr[3], $m)) {
			$event_dt = substr(date('Y'), 0, 2) . $m[3] . '-' . $m[2] . '-' . $m[1] . ' ' . $m[4];
		} else {
			continue;
		}
		$date = date('Y-m-d', strtotime($event_dt));
        $tradestatus = trim($lr[8]);
        if(strtoupper($tradestatus) == 'D'){
            $oldcommission =0;
            $oldsales = 0;
        }else{
            $oldcommission = str_replace(',', '', $lr[10]);
            $oldsales = str_replace(',', '', $lr[9]);
        }
        
        $idinaff = trim($lr[2]);
        $programname = trim($lr[0]);
        $sid = trim($lr[7]);
        $orderid = trim($lr[6]);
        $cancelreason = '';

        $replace_array = array(
                    '{createtime}'      => $event_dt,
                    '{updatetime}'      => $event_dt,
                    '{sales}'           => $oldsales,
                    '{commission}'      => $oldcommission,
                    '{idinaff}'         => $idinaff,
                    '{programname}'     => $programname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => '',
                    '{tradeid}'         => '',
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => 'USD',
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => '',
                    '{cancelreason}'    => $cancelreason,
                    );

        $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
            $comms[$rev_file] = 0;
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");

    }
	fclose($fp);
    foreach ($fws as $file => $f) {
        fclose($f);
    }
	
}



function get_date_range_arr($startDate,$endDate,$range,$format='Y-m-d'){
	$startDate = date('Y-m-d',strtotime($startDate));
	$endDate = date('Y-m-d',strtotime($endDate));
	$d = new DateTime($startDate);

	$return_d = array();

	while($d->format('Y-m-d') <= $endDate){

        $start_dt = $d->format($format);
        $d->modify('+'.$range);
        if($d->format('Y-m-d') > $endDate){
            $end_dt = date($format,strtotime($endDate));
        }else{
            $end_dt = $d->format($format);
        }
        $d->modify('+1 day');

        $return_d[] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt);
    }
    return $return_d;
}
?>
