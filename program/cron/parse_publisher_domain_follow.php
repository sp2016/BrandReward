<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

//爬取脚本
//$sql = "select id,domain from publisher where domain != ''  order by id asc";

/*$sql = "SELECT 
  c.id,c.domain,
  a.site,
  IFNULL(SUM(a.revenues), 0) AS commission 
FROM
  statis_affiliate_br a 
  LEFT JOIN publisher_account b 
    ON a.site = b.apikey 
  LEFT JOIN publisher c 
    ON b.publisherid = c.id 
WHERE a.site != 'unknown' 
  AND a.site != '' 
  AND c.id>10
GROUP BY a.site 
ORDER BY commission DESC 
LIMIT 100 ";*/

/*$sql = "SELECT
  c.id,b.id AS accountid,b.domain,
  a.site,
  IFNULL(SUM(a.revenues), 0) AS commission
FROM
  statis_affiliate_br a
  LEFT JOIN publisher_account b
    ON a.site = b.apikey
  LEFT JOIN publisher c
    ON b.publisherid = c.id
WHERE a.site != 'unknown'
  AND a.site != ''
  AND c.id>10 AND c.SiteOption = 'Promotion'
GROUP BY a.site
ORDER BY commission DESC";*/

$sql = "SELECT 
  c.id,
  b.id AS accountid,
  b.domain,
  b.apikey AS site 
FROM
  publisher_account b 
  LEFT JOIN publisher c 
    ON b.publisherid = c.id 
WHERE c.id > 10 
  AND c.SiteOption = 'Promotion' 
GROUP BY b.apikey";

$publish = $objProgram->objMysql->getRows($sql);

//merlinxu@brandreward.com (password:Mega@12345 port-0646a2cbd365d7c)按顺序爬
//merlinxu@meikaitech.com (password:Mega@12345 port-ae913a98406af82) commission 前100的。
//lightzhang@brandreward.com (password:Mega@12345 port-90d2d7c80284c11)
//stanguan@meikaitech.com (password:12345678 port-bd24d074149069d)

$followAccount = array(
    'user'=>'merlinxu@meikaitech.com',
    'password'=>'Mega@12345',
    'token'=>'port-ae913a98406af82',
);



$file_cook = './parse_publish_domain_info.cook';

//登录
$url = 'https://ajax.follow.net/v3/sessions';
$ch = curl_init($url);
$curl_opts = array(
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIEJAR=>$file_cook,
    CURLOPT_COOKIEFILE=>$file_cook,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'email_address='.urlencode($followAccount['user']).'&password='.urlencode($followAccount['password']),
);
curl_setopt_array($ch, $curl_opts);
$ret = curl_exec($ch);
curl_close($ch);


$runNum = 0;
$newAddNum = 0;
foreach ($publish as $domain){

    $publisherId = $domain['id'];
    $publisherAccountId = $domain['accountid'];
    
    //如果数据库中有保存，跳过此publisher
    $followInfo = array();
    $sql = "SELECT ID FROM crawl_publish_domain_follow_new WHERE publisherId = $publisherId AND publisherAccountId = $publisherAccountId";
    $followInfo = $objProgram->objMysql->getFirstRow($sql);
    if($followInfo){
        continue;    
    }
    
    
    
    //过滤掉domain（facebook.com，blogger.com，brandreward.com）
    $ignoreDomain = false;
    $filterDomain = array('facebook.com,blogger.com,brandreward.com');
    foreach ($filterDomain as $filterD){
        if(stripos($domain['domain'],$filterD) !== false){
            $ignoreDomain = true;
            continue;
        }
    }
    if($ignoreDomain){
        echo 'filter domain continue'.PHP_EOL;
        continue;
    } 
    
        
    $str = '';
    if(preg_match('/^http.+/i',$domain['domain'])){
        $domainInfo = parse_url($domain['domain']);
        if(!isset($domainInfo['host']))
            continue;
        $str = $domainInfo['host'];
    }elseif(preg_match('/\/.*/',$domain['domain'])){
        $temArr = explode('/', $domain['domain']);
        $str = $temArr[0];
    }
    else{
        $str = $domain['domain'];
    }
     

    if($str){
        $runNum ++;
        echo $str.PHP_EOL;
        echo 'The:'.$runNum.'  num.' .PHP_EOL;
        
        //country US UK DE SG FR AU CA
        $countryArr = array('US','GB','DE','SG','FR','AU','CA');
        $semKeywords = array();
        $semRTextAds = array();
        $seoKeywords = array();
        $outFlag = false;
        foreach ($countryArr as $country){
            
            //sem
            $requestUrl = "https://ajax.follow.net/v3/portfolios/{$followAccount['token']}/domains/$str/overviewarticles?vendor=keywordspy&country_code=$country";
            //https://ajax.follow.net/v3/portfolios/port-ae913a98406af82/domains/www.promospro.com/overviewarticles?vendor=keywordspy&country_code=US
            //https://ajax.follow.net/v3/portfolios/port-ae913a98406af82/domains/promospro.com/overviewarticles?vendor=keywordspy&country_code=US
            //https://ajax.follow.net/v3/portfolios/port-ae913a98406af82/domains/promospro.com/overviewarticles?vendor=keywordspy&country_code=US
            
            echo $requestUrl.PHP_EOL;
            
            $ch = curl_init($requestUrl);
            $curl_opts = array(
                //CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_COOKIEJAR=>$file_cook,
                CURLOPT_COOKIEFILE=>$file_cook,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            );
            curl_setopt_array($ch, $curl_opts);
            $retSem = curl_exec($ch);
            curl_close($ch);
            //print_r($retSem);exit;
            $data = json_decode($retSem,true);
            if(isset($data['error'])){
                //print_r($data);
                if($data['error']['message'] == "You've exceeded your monthly domain overview lookup quota for this month. Please upgrade your account to view more."){
                    $outFlag = true;
                }
            }
            
            if(!empty($data['data'])){
                $semKeywords[$country] = $data['data'][0];
                $semRTextAds[$country] = $data['data'][1];
            }
            else{
                $semKeywords[$country] = array();
                $semRTextAds[$country] = array();
            }
            
            
            //seo
            $requestUrl = "https://ajax.follow.net/v3/portfolios/{$followAccount['token']}/domains/$str/overviewarticles?vendor=semrush&country_code=$country";
            echo $requestUrl.PHP_EOL;
            $ch = curl_init($requestUrl);
            $curl_opts = array(
                //CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_COOKIEJAR=>$file_cook,
                CURLOPT_COOKIEFILE=>$file_cook,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            );
            curl_setopt_array($ch, $curl_opts);
            $retSem = curl_exec($ch);
            curl_close($ch);
            //print_r($retSem);exit;
            $data = json_decode($retSem,true);
            if(isset($data['error'])){
                //print_r($data);
                if($data['error']['message'] == "You've exceeded your monthly domain overview lookup quota for this month. Please upgrade your account to view more."){
                    $outFlag = true;
                }
            }
            
            if(!empty($data['data'])){
                $seoKeywords[$country] = $data['data'];
            }
            else{
                $seoKeywords[$country] = array();
            }
             
        }
        if($outFlag){//跳过此domain
            continue;
        }
        
        $semKeywords = json_encode($semKeywords);
        $semRTextAds = json_encode($semRTextAds);
        $seoKeywords = json_encode($seoKeywords);
        
        
        
        //Whois
        $requestUrl = "https://ajax.follow.net/v3/portfolios/{$followAccount['token']}/domains/$str/overviewarticles?section=whois";
        $ch = curl_init($requestUrl);
        $curl_opts = array(
            //CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR=>$file_cook,
            CURLOPT_COOKIEFILE=>$file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
        );
        curl_setopt_array($ch, $curl_opts);
        $retWhois = curl_exec($ch);
        curl_close($ch);
        $whois = json_decode($retWhois,true);
        $whois = json_encode($whois);
         
        //入库
        $domainName = $domain['domain'];
        $sql = "SELECT ID FROM crawl_publish_domain_follow_new WHERE publisherId = $publisherId AND publisherAccountId = $publisherAccountId";
        $followInfo = $objProgram->objMysql->getFirstRow($sql);
        if($followInfo){
            $updateSql = "UPDATE crawl_publish_domain_follow_new SET semKeywords = '".addslashes($semKeywords)."', semRTextAds = '" .addslashes($semRTextAds)."', semKeywords = '".addslashes($semKeywords)."' WHERE publisherId = $publisherId AND publisherAccountId = $publisherAccountId";
            //echo $updateSql;exit;
            $objProgram->objMysql->query($updateSql);
        }else {
            $insert_col = array(
                'publisherId','publisherAccountId','domainName','semKeywords','seoKeywords','semRTextAds','whois'
            );
            $insert_value = array($publisherId,$publisherAccountId,$domainName,addslashes($semKeywords),addslashes($seoKeywords),addslashes($semRTextAds),addslashes($whois));
            //print_r($insert_value);exit;
            $insertSql = "insert into crawl_publish_domain_follow_new (".implode(',', $insert_col).") values ( '".implode("','", $insert_value)."' )";
            $objProgram->objMysql->query($insertSql);
            $newAddNum ++;
        }
        
    }
    sleep(5);
    if($runNum>=50){
        break;
    }
}




echo 'Run count:'.$runNum.PHP_EOL;

echo 'Add count:'.$newAddNum.PHP_EOL;
exit;

?>