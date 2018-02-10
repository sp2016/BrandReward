<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New Program();

while(1){
    print_r("Service start at:".date('Y-m-d H:i:s')."\n");
    #reset row while: alexacrawlstatus = 1 when updatetime more then 20min
    $sql = 'UPDATE publisher_potential SET alexacrawlstatus = 0 WHERE alexacrawlstatus = 1 AND alexaupdatetime < "'.date('Y-m-d H:i:s',time() - 1200).'"';
    $objProgram->objMysql->query($sql);
    print_r("20min in doing crawl nums: ".$objProgram->objMysql->getAffectedRows()."\n");
    #reset row while: done in 1 month ago
    $sql = 'UPDATE publisher_potential SET alexacrawlstatus = 0 WHERE alexacrawlstatus = 2 AND alexaupdatetime < "'.date('Y-m-d H:i:s',strtotime('-1 month')).'"';
    $objProgram->objMysql->query($sql);
    print_r("more than 1 month crawl nums: ".$objProgram->objMysql->getAffectedRows()."\n");

    do{
        $sql = 'SELECT * FROM publisher_potential WHERE alexacrawlstatus = 0 limit 10';
        $rows = $objProgram->objMysql->getRows($sql);
        foreach($rows as $k=>$v){
        	$sql = 'UPDATE publisher_potential set alexacrawlstatus = 1,alexaupdatetime = "'.date('Y-m-d H:i:s').'" where id = '.$v['id'].' and alexacrawlstatus = 0';
        	$res = $objProgram->objMysql->query($sql);
        	$affectrows = $objProgram->objMysql->getAffectedRows();
        	if($affectrows > 0){
        		$value_rank = get_site_rank_in_alexa($v['url']);
        		$sql = 'UPDATE publisher_potential set alexacrawlstatus = 2 , alexarank = '.intval($value_rank).', alexaupdatetime = "'.date('Y-m-d H:i:s').'" WHERE id = '.$v['id'];
        		$objProgram->objMysql->query($sql);
        		
        		print_r($v['id']."\t".$v['url']."\t".$value_rank."\n");
        	}
        }
    }while(!empty($rows));

    print_r("Service end at:".date('Y-m-d H:i:s')."\n");
    sleep(1800);
}

function get_site_rank_in_alexa($url){
    preg_match('/https?:\\/\\/([^?&\\/#]*)/',$url,$um);
    if(empty($um[1]))
    	return 0;
    else
    	$url = $um[1];


    $alexa_page_url = 'http://www.alexa.com/siteinfo/'.$url;

    $ch = curl_init($alexa_page_url);
    $curl_opts = array(CURLOPT_HEADER => false,
                CURLOPT_NOBODY => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
    	    CURLOPT_FOLLOWLOCATION => true,
    );
    curl_setopt_array($ch, $curl_opts);
    $res = curl_exec($ch);
    curl_close($ch);

    $res = str_replace("\n",'',$res);
    preg_match('/alt=\'Global rank icon\'><strong.*?<\\/strong>/',$res,$m);

    $str_rank_html = $m[0];
    $value_rank = 0;

    if(preg_match('/-<\\/span>/',$str_rank_html)){
    }else{
        preg_match('/[\d,]+/',$str_rank_html,$n);
        $value_rank = $n[0];
        $value_rank = str_replace(',','',$value_rank);
    }
    return $value_rank;
}
?>
