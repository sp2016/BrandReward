<?php


try {
    define('AFF_NAME', AFFILIATE_NAME);
    define('USER_NAME', AFFILIATE_USER);
    define('USER_PASS', 'UEGbG8UV8hYyZ1Q6WLZV');//jU3QXucpWBKsgfPr5jzs
    //define('USER_PASS', 'Uw2JEcl93D');//jU3QXucpWBKsgfPr5jzs
    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');

    define('WSDL_SERVER', 'http://api.belboon.com/?wsdl');
    // SOAP options (http://de.php.net/manual/de/soapclient.soapclient.php)
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");


    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d', strtotime(END_TIME));
        $start_dt = date('Y-m-d', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d');
        $start_dt = date('Y-m-d', strtotime('-100 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";

    $config = array('login' => USER_NAME,
        'password' => USER_PASS,
        'trace' => true
    );

    $client = new SoapClient(WSDL_SERVER, $config);

    $offset = 0;
    $comm_all = 0;
    $fws = $file_comm = $curr = array();
    
    do {
        $result = $client->getEventList(
                                         null, // adPlatformIds
                                         null, // programId
                                         null, // eventType
                                         null, // eventStatus
                                         null, // eventCurrency
                                         $start_dt, // eventDateStart
                                         $end_dt, // eventDateEnd
                                         null, // eventChangeDateStart
                                         null, // eventChangeDateEnd
                                         array('eventdate' => 'ASC'), // orderBy
                                         null, // limit
                                         $offset // offset
                            );
        
        if (!isset($result->handler) || !isset($result->handler->events) || count($result->handler->events) == 0)
            throw new Exception("No data download");

    
        /*
          [handler] => stdClass Object
          (
                  [events] => Array
                  (
                          [0] => Array
                          (
                                  [eventid] => 421459079
                                  [programid] => 18635
                                  [programname] => Tailorstore UK - Tailor-Made Men's Clothing
                                  [platformid] => 576890
                                  [platformname] => Couponsnapshot
                                  [eventstatus] => PENDING
                                  [eventdate] => 2013-01-26 00:30:27
                                  [lastchangedate] => 2013-01-26 00:42:19
                                  [eventtype] => SALE
                                  [ordercode] => 330487
                                  [eventcurrency] => GBP
                                  [eventcommission] => 7.32
                                  [eventcondition] => SALE
                                  [netvalue] => 73.23
                                  [eventinfo] =>
                                  [subid] =>
                          )
     *
     */
        $rows = 0;
        foreach ($result->handler->events as $v) {
            $rows++;    
            $date = date('Y-m-d', strtotime($v['eventdate']));

            $file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $date) . '.upd';
            if (!isset($fws[$file])) {
                $fws[$file] = fopen($file, 'w');
                if (!$fws[$file])
                    throw new Exception("{$file} open failed\n");
                $file_comm[$file] = 0;
                $curr = array();
            }
            
            $v['subid'] = preg_replace('/^subtracing=(.*)/', '\1', $v['subid']);

            if (!isset($curr[$v['eventcurrency']]))
                $curr[$v['eventcurrency']] = cur_exchange($v['eventcurrency'], 'USD', $date);

            $cancelreason = '';

            $replace_array = array(
                '{createtime}'      => $v['eventdate'],
                '{updatetime}'      => $v['eventdate'],
                '{sales}'           => round($v['netvalue'] * $curr[$v['eventcurrency']], 4),
                '{commission}'      => round($v['eventcommission'] * $curr[$v['eventcurrency']], 4),
                '{idinaff}'         => $v['programid'],
                '{programname}'     => $v['programname'],
                '{sid}'             => rtrim($v['subid'], '++'),
                '{orderid}'         => $v['ordercode'],
                '{clicktime}'       => $v['eventdate'],
                '{tradeid}'         => $v['eventid'],
                '{tradestatus}'     => $v['eventstatus'],
                '{oldcur}'          => $v['eventcurrency'],
                '{oldsales}'        => $v['netvalue'],
                '{oldcommission}'   => $v['eventcommission'],
                '{tradetype}'       => '',
                '{referrer}'        => '',
                '{cancelreason}'    => $cancelreason,
            );
            fwrite($fws[$file], strtr(FILE_FORMAT,$replace_array) . "\n");
            
            //fwrite($fws[$file], $v['lastchangedate'] . "\t" . $v['eventdate'] . "\t" . round($v['netvalue'] * $curr[$v['eventcurrency']], 4) . "\t" . round($v['eventcommission'] * $curr[$v['eventcurrency']], 4) . "\t" . $v['programid'] . "\t" . $v['programname'] . "\t" . rtrim($v['subid'], '++') . "\t" . $v['ordercode'] . "\t" . $v['eventdate'] . "\t" . $v['eventid'] . "\t" . $v['eventstatus'] . "\t" . $v['eventcurrency'] . "\t" . $v['netvalue'] . "\t" . $v['eventcommission'] . "\n");

            $file_comm[$file] += $v['eventcommission'] * $curr[$v['eventcurrency']];
            $comm_all+= $v['eventcommission'] * $curr[$v['eventcurrency']];
        }

        if ($rows < 500)
            break;
            
        $offset += $rows;

    }while (true);

	print_r($file_comm);
    foreach ($fws as $file => $fp) {
        fclose($fp);
		/*
        $file_old = str_replace('.upd', '.dat', $file);
        rename($file, $file_old);
        
        if (!file_exists($file_old)) {
            //echo "mv {$file}, {$file_old}\n";
            rename($file, $file_old);
        } else {
            $comm_old = 0;
            $fp = fopen($file_old, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $lr = trim(fgets($fp));

                    if ($lr == '' || strpos($lr, 'SUMARIZE:') !== false)
                        continue;

                    $lr = explode("\t", $lr);
                    $comm_old += $lr[3];
                }
            }
            fclose($fp);

            $comm_new = isset($file_comm[$file]) ? $file_comm[$file] : 0;
            if (round($comm_new, 2) != round($comm_old, 2)) {

                //echo "mv {$file}, {$file_old} --- {$comm_new} : {$comm_old}\n";
                rename($file, $file_old);
            } else {
                unlink($file);
            }
        }
         */
         
    }
/*
    $fp = fopen(REV_DATA, 'a');
    if (!file_exists(REV_DATA))
        throw new Exception(REV_DATA . " not exist");
    $comm_all=round($comm_all,2);
    fwrite($fp, "{$start_dt}~{$end_dt}\t{$comm_all}\n");
    fclose($fp);
*/	
} catch (Exception $e) {
    var_dump($e);
}
?>
