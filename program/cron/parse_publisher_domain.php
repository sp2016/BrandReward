<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

//爬取脚本
$sql = "select id,domain from publisher where domain != '' order by id asc";
$publish = $objProgram->objMysql->getRows($sql);
$crawlNum = 0;
foreach ($publish as $domain){

    //https://www.whois.com/whois/brandreward.com
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

        $file_name = '/home/bdg/program/temp/publisher_whois_file/'.$domain['id'].'_'.$str.'.dat';
        if(!file_exists($file_name)){
            $requestUrl = 'https://www.whois.com/whois/'.$str;
            echo $requestUrl.PHP_EOL;
            $reInfo =  file_get_contents($requestUrl);

            file_put_contents($file_name, $reInfo);
            $crawlNum ++;
            if($crawlNum >= 10){
                break;
            }
            sleep(10);
        }
        /*$ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $requestUrl);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_HEADER, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
         $output = curl_exec($ch);
         if(curl_exec($ch) === false)
         {
         echo 'Curl error: ' . curl_error($ch);
         }
         curl_close($ch);
         var_dump($output);exit;*/
    }

}


//分析脚本
$dir = '/home/bdg/program/temp/publisher_whois_file';
$file=scandir($dir);
$newAddNum = 0;
foreach ($file as $value){
    
    if($value == '.' || $value == '..' ){
        continue;
    }
    
    $content = file_get_contents($dir.'/'.$value);
    $fileNameArr = explode('_', $value);
    
    $publisherId = $fileNameArr[0];
    $domainName = str_replace('.dat','',$value);
    $domainInformation = '';
    $registrantContact = '';
    $administrativeContact = '';
    $technicalContact = '';
    $rawWhoisData = '';
    
    preg_match_all('/<div class="df-block">(.*?)<\/div><\/div><\/div>/is', $content,$matches);
    if($matches){
        //echo count($matches[0]).PHP_EOL;
        /*if(count($matches[0]) == 1){
            
            print_r($matches[0]);exit;
        }*/
        foreach ($matches[0] as $matValue){
        
             
            if(stripos($matValue,'Domain Information') !== false){
                $domainInformation = $matValue;
            }
            if(stripos($matValue,'Registrant Contact') !== false){
                $registrantContact = $matValue;
            }
            if(stripos($matValue,'Administrative Contact') !== false){
                $administrativeContact = $matValue;
            }
            if(stripos($matValue,'Technical Contact') !== false){
                $technicalContact = $matValue;
            }
        }
    }
    preg_match('/<div class="df-block-raw">(.*?)<\/pre><\/div>/is', $content,$matcheRaw);
    if($matcheRaw){
        $rawWhoisData = $matcheRaw[0];
    }
    
    $sql = "SELECT ID FROM publisher_domain_whois WHERE publisherId = $publisherId";
    $whoisInfo = $objProgram->objMysql->getFirstRow($sql);
    if($whoisInfo){
        
    }else {
        $insert_col = array(
            'publisherId','domainName','domainInformation','registrantContact','administrativeContact','technicalContact','rawWhoisData'
        );
        $insert_value = array($publisherId,addslashes($domainName),addslashes($domainInformation),addslashes($registrantContact),addslashes($administrativeContact),addslashes($technicalContact),addslashes($rawWhoisData));
        $insertSql = "insert into publisher_domain_whois (".implode(',', $insert_col).") values ( '".implode("','", $insert_value)."' )";
        $objProgram->objMysql->query($insertSql);
        $newAddNum ++;
    }
    
    
}

echo 'Add count:'.$newAddNum.PHP_EOL;
exit;

?>