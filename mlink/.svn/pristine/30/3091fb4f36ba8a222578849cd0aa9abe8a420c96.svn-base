<?php
require_once PATH_INCLUDE.'/zanox_api/ApiClient.php';
define('ZANOX_MAX_ITEMS', 50);

define('AFF_NAME',AFFILIATE_NAME);
define('ZANOX_CONNECT_ID', "842953543798E1C7D191");
define('ZANOX_SECRET_KEY', "93bfeAd13ced4c+2bEc3b47e71410d/6e0930c4a");
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if (defined('START_TIME') && defined('END_TIME')) {
    $td = date('Y-m-d', strtotime(END_TIME));
    $fd = date('Y-m-d', strtotime(START_TIME));
} else {
    $td = date('Y-m-d');
    $fd = date('Y-m-d', strtotime('-120 days', strtotime($td)));
}

$begin_dt = $fd;
$end_dt = $td;

$zn = ApiClient::factory(PROTOCOL_JSON, VERSION_2011_03_01);

$zn->setConnectId(ZANOX_CONNECT_ID);
$zn->setSecretKey(ZANOX_SECRET_KEY);

$key_val = '$';
$key_id = '@id';
$comm_all = 0;
while ($td >= $fd) {
    catchCurl($zn,$td,$key_val,$key_id);

    $td = date('Y-m-d', strtotime('-1 day', strtotime($td)));
}

function catchCurl($zn,$td,$key_val,$key_id){
    $file_new = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $td) . '.upd';
    $fw = fopen($file_new, 'w');
    echo $file_new."\n";
    $comm_new = 0;

    $curr = array();
    //get sales
    $page = 0;
    do {
        echo $td."sales_{$page}\r\n";
        $rs = catchSales($zn,$td,$page);
        if(!$rs){
            $page++;
            continue;
        }
        if ($rs->items == 0)
            break;

        foreach ($rs->saleItems->saleItem as $v) {
            if (!isset($curr[$v->currency])) {
                $curr[$v->currency] = cur_exchange(strtoupper($v->currency), 'USD', $td);
            }

            $event_dt = preg_replace('/(^[\d]+-[\d]+-[\d]+)T([\d]+:[\d]+:[\d]+).*/', '\1 \2', $v->trackingDate);//date('Y-m-d H:i:s', strtotime($v->trackingDate));
            $click_dt = @preg_replace('/(^[\d]+-[\d]+-[\d]+)T([\d]+:[\d]+:[\d]+).*/', '\1 \2', $v->clickDate);//date('Y-m-d H:i:s', strtotime($v->clickDate));

            $day = date('Y-m-d',strtotime($event_dt));

            $oldsales = isset($v->amount)?trim($v->amount):0;
            $oldcommission = isset($v->commission)?trim($v->commission):0;
            $oldcur = isset($v->currency)?trim($v->currency):'USD';

            $cur_exr = cur_exchange($oldcur, 'USD', $day);
            $sales = round($oldsales * $cur_exr, 4);
            $commission = round($oldcommission * $cur_exr, 4);

            $idinaff = isset($v->program->$key_id)?trim($v->program->$key_id):'';
            $programname = isset($v->program->$key_val)?trim($v->program->$key_val):'';
            $sid = isset($v->gpps)? (is_array($v->gpps->gpp)? $v->gpps->gpp[0]->$key_val : $v->gpps->gpp->$key_val) : '';
            $sid = trim($sid);

            $orderid = isset($v->$key_id)?trim($v->$key_id):'';
            $tradeid = isset($v->clickId)?trim($v->clickId):'';
            $tradestatus = isset($v->reviewState)?trim($v->reviewState):'';
         
            $cancelreason = '';
            if($tradestatus == 'rejected'){
                 $cancelreason = trim($v->reviewNote);
            }

            $replace_array = array(
                    '{createtime}'      => $event_dt,
                    '{updatetime}'      => $event_dt,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $idinaff,
                    '{programname}'     => $programname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => $click_dt,
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => '',
                    '{cancelreason}'    => $cancelreason,
                    );


            fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
        }

        if ($rs->total > ($page + 1) * ZANOX_MAX_ITEMS)
            $page++;
        else
            $page = 0;
    }while ($page > 0);

    //get leads
    $page = 0;
    do {
        echo $td."leads_{$page}\r\n";
        $rs = catchLeads($zn,$td,$page);
        if(!$rs){
            $page++;
            continue;
        }

        if ($rs->items == 0)
            break;
        
        print_r($rs);exit();

        foreach ($rs->leadItems->leadItem as $v) {
            if (!isset($curr[$v->currency])) {
                $curr[$v->currency] = cur_exchange(strtoupper($v->currency), 'USD', $td);
            }


            $event_dt = preg_replace('/(^[\d]+-[\d]+-[\d]+)T([\d]+:[\d]+:[\d]+).*/', '\1 \2', $v->trackingDate);//date('Y-m-d H:i:s', strtotime($v->trackingDate));
            $click_dt = @preg_replace('/(^[\d]+-[\d]+-[\d]+)T([\d]+:[\d]+:[\d]+).*/', '\1 \2', $v->clickDate);//date('Y-m-d H:i:s', strtotime($v->clickDate));

            $day = date('Y-m-d',strtotime($event_dt));

            $oldsales = isset($v->amount)?trim($v->amount):0;
            $oldcommission = isset($v->commission)?trim($v->commission):0;
            $oldcur = isset($v->currency)?trim($v->currency):'USD';

            $cur_exr = cur_exchange($oldcur, 'USD', $day);
            $sales = round($oldsales * $cur_exr, 4);
            $commission = round($oldcommission * $cur_exr, 4);

            $idinaff = isset($v->program->$key_id)?trim($v->program->$key_id):'';
            $programname = isset($v->program->$key_val)?trim($v->program->$key_val):'';
            $sid = isset($v->gpps)? (is_array($v->gpps->gpp)? $v->gpps->gpp[0]->$key_val : $v->gpps->gpp->$key_val) : '';
            $sid = trim($sid);

            $orderid = isset($v->$key_id)?trim($v->$key_id):'';
            $tradeid = isset($v->clickId)?trim($v->clickId):'';
            $tradestatus = isset($v->reviewState)?trim($v->reviewState):'';

            $replace_array = array(
                    '{createtime}'      => $event_dt,
                    '{updatetime}'      => $event_dt,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $idinaff,
                    '{programname}'     => $programname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => $click_dt,
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => '',
                    );


            fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
        }

        if ($rs->total > ($page + 1) * ZANOX_MAX_ITEMS)
            $page++;
        else
            $page = 0;
    }while ($page > 0);
    fclose($fw);
}


function catchSales($zn,$td,$page,$count=0){
    try{
        if($count > 0){
            echo "{$td}_{$page} has retried for {$count} times";
        }
        $rs = json_decode($zn->getSales($td, NULL, NULL, NULL, NULL, $page, ZANOX_MAX_ITEMS));

        return $rs;
    }catch (Exception $e) {
        if($count > 3){
            return false;
        }
        return catchSales($zn,$td,$page,++$count);
    }
}


function catchLeads($zn,$td,$page,$count=0){
    try{
        if($count > 0){
            echo "{$td}_{$page} has retried for {$count} times";
        }
        $rs = json_decode($zn->getLeads($td, NULL, NULL, NULL, NULL, $page, ZANOX_MAX_ITEMS));
        return $rs;
    }catch (Exception $e) {
        if($count > 3){
            return false;
        }
        return catchLeads($zn,$td,$page,++$count);
    }
}

?>
