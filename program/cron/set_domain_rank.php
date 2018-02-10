<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objProgram = New Program();


$date_now = date("Y-m-d H:i:s");
$date = array(
    '7D' => date("Y-m-d", strtotime("-7 days")),
    '1M' => date("Y-m-d", strtotime("-30 days")),
    '3M' => date("Y-m-d", strtotime("-90 days"))
);
$sql = "update domain set rank = 0 where rank <> 0";
$objProgram->objMysql->query($sql);
foreach($date as $time => $date_his) {
    $sql = "SELECT domainid,createddate, SUM(revenues) as rev, SUM(orders) as ord, SUM(clicks) as cli, SUM(sales) as sal FROM `statis_domain`
		WHERE createddate >= '$date_his' AND domainid > 0 GROUP BY domainid ORDER BY rev DESC, cli DESC";
    $tmp_arr = $objProgram->objMysql->getRows($sql, "domainid");

    $rank = 1;
    $rank_cli = 0;
    foreach ($tmp_arr as $v) {
        if ($rank_cli == 0 && $v["rev"] <= 29) {
            $rank += 100000;
            $rank_cli = 1;
        }
        $sql = "update domain set rank = $rank where id = {$v["domainid"]}";
        $objProgram->objMysql->query($sql);
        $rank++;
    }

    $sql = "SELECT a.domainid, b.alias, SUM(a.revenues) AS rev, SUM(a.orders) AS ord, SUM(a.clicks) AS cli, SUM(a.sales) AS sal FROM `statis_domain` a INNER JOIN publisher_account b ON a.site = b.apikey
		WHERE a.createddate >= '$date_his' AND a.domainid > 0 group by a.domainid, b.alias ORDER BY rev DESC";
    $tmp_arr = $objProgram->objMysql->getRows($sql);

    /*$sql = "select alias, apikey from publisher_account where status = 'active' ";
    $accout_arr = $objProgram->objMysql->getRows($sql, "apikey");*/

    $rank_arr = array();
    foreach ($tmp_arr as $v) {
        $site = "";
        //$tmp_alias = $accout_arr[$v["site"]]["alias"];
        $tmp_alias = $v["alias"];
        $tmp_alias = str_ireplace("cs", "", $tmp_alias);
        $tmp_alias = str_ireplace("ds", "", $tmp_alias);
        if (in_array($tmp_alias, array("us", "uk", "ca", "de", "au", "fr"))) {
            $site = $tmp_alias;
        }
        if ($site) {
            if ($v["rev"] > 100) {
                if (!isset($rank_arr[$site])) $rank_arr[$site] = 1;
                $sql = "insert into domain_stats(domainid, site, rank, revenue{$time}, orders{$time}, clicks{$time}, sales{$time}, LastUpdateTime)
					values({$v["domainid"]}, '{$site}', $rank_arr[$site],  '{$v["rev"]}', '{$v["ord"]}', '{$v["cli"]}', '{$v["sal"]}', '$date_now') 
					on duplicate key update rank =  $rank_arr[$site], revenue{$time} = '{$v["rev"]}', orders{$time} = '{$v["ord"]}', clicks{$time} = '{$v["cli"]}', sales{$time} = '{$v["sal"]}', LastUpdateTime = '$date_now'"; 
                $objProgram->objMysql->query($sql);
                $rank_arr[$site]++;
            } else {
                $sql = "insert into domain_stats(domainid, site, revenue{$time}, orders{$time}, clicks{$time}, sales{$time}, LastUpdateTime)
					values({$v["domainid"]}, '{$site}', '{$v["rev"]}', '{$v["ord"]}', '{$v["cli"]}', '{$v["sal"]}', '$date_now')
					on duplicate key update rank = 0, revenue{$time} = '{$v["rev"]}', orders{$time} = '{$v["ord"]}', clicks{$time} = '{$v["cli"]}', sales{$time} = '{$v["sal"]}', LastUpdateTime = '$date_now'";
                $objProgram->objMysql->query($sql);
            }
        }
    }
}
$sql = "delete from domain_stats where LastUpdateTime < '$date_now'";
$objProgram->objMysql->query($sql);


$date_now = date("Y-m-d");
$date_his = date("Y-m-d", strtotime("-30 days"));
$date_his_90 = date("Y-m-d", strtotime("-90 days"));
$date_his_7 = date("Y-m-d", strtotime("-7 days"));
$sql = "SELECT programid, SUM(revenues) as rev, SUM(orders) as ord, SUM(clicks) as cli, SUM(sales) as sal FROM `statis_program`
		WHERE createddate >= '$date_his' AND programid > 0 group by programid ORDER BY rev DESC";
$tmp_arr = $objProgram->objMysql->getRows($sql, "programid");

$sql = "SELECT programid, SUM(revenues) as rev, SUM(orders) as ord, SUM(clicks) as cli, SUM(sales) as sal FROM `statis_program`
		WHERE createddate >= '$date_his_90' AND programid > 0 group by programid ORDER BY rev DESC";
$tmp_90_arr = $objProgram->objMysql->getRows($sql, "programid");

$sql = "SELECT programid, SUM(revenues) as rev, SUM(orders) as ord, SUM(clicks) as cli, SUM(sales) as sal FROM `statis_program`
		WHERE createddate >= '$date_his_7' AND programid > 0 group by programid ORDER BY rev DESC";
$tmp_7_arr = $objProgram->objMysql->getRows($sql, "programid");

$sql = "update program_int set RevenueOrder = 999999 where RevenueOrder <> 999999";
$objProgram->objMysql->query($sql);

$rank = 1;
//$rank_cli = 0;
foreach($tmp_7_arr as $v){
	if($v["rev"] > 0){
		$sql = "update program_int set RevenueOrder = $rank where programid = {$v["programid"]}";
		$objProgram->objMysql->query($sql);
		$rank++;
	}

	$sql = "replace into program_stats(programid, revenue1m, orders1m, clicks1m, sales1m, revenue3m, orders3m, clicks3m, sales3m, revenue7d, orders7d, clicks7d, sales7d, LastUpdateTime)
			values({$v["programid"]}, '{$tmp_arr[$v["programid"]]["rev"]}', '{$tmp_arr[$v["programid"]]["ord"]}', '{$tmp_arr[$v["programid"]]["cli"]}', '{$tmp_arr[$v["programid"]]["sal"]}', 
					'{$tmp_90_arr[$v["programid"]]["rev"]}', '{$tmp_90_arr[$v["programid"]]["ord"]}', '{$tmp_90_arr[$v["programid"]]["cli"]}', '{$tmp_90_arr[$v["programid"]]["sal"]}',
					'{$tmp_7_arr[$v["programid"]]["rev"]}', '{$tmp_7_arr[$v["programid"]]["ord"]}', '{$tmp_7_arr[$v["programid"]]["cli"]}', '{$tmp_7_arr[$v["programid"]]["sal"]}',
					'$date_now')";
	$objProgram->objMysql->query($sql);
}

$sql = "delete from program_stats where LastUpdateTime < '$date_now'";
$objProgram->objMysql->query($sql);


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;
?>