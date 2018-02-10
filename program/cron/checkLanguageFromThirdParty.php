<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(dirname(dirname(__FILE__)) . "/func/func.php");


//测试环境
//$objMysqlTestDemo = new MysqlExt('bdg_go_base_demo', 'localhost', 'bdg_demo', 'Dkkd(f8');

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);



$sql = "SELECT id,`title`,`desc` FROM content_feed_new WHERE `status` = 'active' and `language` = 'en' AND ischecklanguage = 'NO'";
$data = $objProgram->objMysql->getRows($sql);

$i = 0;
$j = 0;
foreach ($data as $value) {

    $i++;
    //mark have check language item
    $updateSql = "update content_feed_new set IsCheckLanguage = 'YES' where id = {$value['id']}";
    $objProgram->objMysql->query($updateSql);
    
    
    //第一种接口
    $firstKeywords =  array($value['title'],$value['desc']);
    $firstLanguage = '';
    foreach ($firstKeywords as $firstKeyword){
        if(!$firstKeyword) continue;
        $url = 'https://open.xerox.com/bus/op/LanguageIdentifier/GetLanguageForString';
        $ch = curl_init($url);
        $curl_opts = array(
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'document=' . urlencode($firstKeyword)
        );
        curl_setopt_array($ch, $curl_opts);
        $re = curl_exec($ch);
        curl_close($ch);

        $re = trim($re);
        if(!$re) echo 'Failure, check!';
        preg_match('/(\w)+/',$re,$matches);
        $firstLanguage = $matches[0];
        if($firstLanguage == 'en') break;
        // call the language identifier
        //$text = $value['title'] . $value['desc'];
        //$return = $client->GetLanguageForString(array("document"=>$text));
        //$language = $return->GetLanguageForStringResult;
        // show the results
        //echo("The language of '".$text."' is: ".$language);
    }

    //第二种接口
    $secondKeywords =  $value['title'].' '.$value['desc'];
    $secondLanguage = '';
    $url = 'https://labs.translated.net/language-identifier/';
    $ch = curl_init($url);
    $curl_opts = array(
        CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'text=' . urlencode($secondKeywords)
    );
    curl_setopt_array($ch, $curl_opts);
    $re = curl_exec($ch);
    if(!$re) echo 'Failure, check!';
    curl_close($ch);
    //匹配出语言
    $pregStr = "/\((\w+) <a .+>\?<\/a>\)/is";
    preg_match($pregStr,$re,$matches);

    if(!$matches[1]) continue;
    $secondLanguage = strtolower(trim($matches[1]));

    echo $firstLanguage .'====='. $secondLanguage.PHP_EOL;

    $languageArr = array('fr','de','ru','it','es','pt','nl','se','ukr','jp');
    if($firstLanguage == $secondLanguage){
        $language = $firstLanguage;
        if(in_array($language, $languageArr)){
            $updateSql = "update content_feed_new set `language` = '{$language}', IsFixLanguage = 'YES' where id = {$value['id']}";
            $objProgram->objMysql->query($updateSql);

            //记录日志
            /*$title = addslashes($value['title']);
            $desc  = addslashes($value['desc']);
            $insertSql = "insert into content_feed_compare2 (`CID`,`Title`,`Desc`,`languageFrom`,`languageTo`)
            VALUES ( {$value['id']},'{$title}','{$desc}','en','{$language}' ) ";
            $objMysqlTestDemo->query($insertSql);
            echo $insertSql.PHP_EOL;*/
            $j++;
        }
    }
    
    sleep(3);
}

echo $i.PHP_EOL;
echo $j.PHP_EOL;
exit();
















/**
 * This class provides SOAP access to an Open Xerox Soap Service
 */

class OpenXeroxSoapClient extends SoapClient {

    var $wsdl_string;
    var $user;
    var $password;
    var $proxy_host;
    var $proxy_port;
    var $wrap_access_token;

    /**
     * COnstructor: WSDl is mandatory. Other filds for autentication and proxy are not.
     */
    public function __construct($wsdl, $user = NULL, $password = NULL, $proxy_host = NULL, $proxy_port = NULL){
        $this->wsdl_string = $wsdl;
        $this->user = $user;
        $this->password = $password;
        $this->proxy_host = $proxy_host;
        $this->proxy_port = $proxy_port;
    }
     
    /**
     * Add the proxy and authentication information for all the requests.
     */
    function __doRequest($request, $location, $action, $version, $one_way = 0){
        $headers = array(
            'Method: POST',
            'User-Agent: OpenXerox PHP Client',
            'Content-Type: text/xml',
            'Authorization: WRAP access_token="'.$this->wrap_access_token.'"',
            'SOAPAction: "'.$action.'"'
        );

        $ch = curl_init($location);
        curl_setopt_array($ch,array(
            CURLOPT_VERBOSE=>false,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POST=>true,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_POSTFIELDS=>$request,
            CURLOPT_HEADER=>false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_PROXY => $this->proxy_host,
            CURLOPT_PROXYPORT => $this->proxy_port,
            CURLOPT_HTTPHEADER=>$headers
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
     
     
    /**
     * Send a POST requst using cURL - used for getting the WRAP token.
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return string
     */
    private function curl_post($url, array $post = NULL, array $options = array()) {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FORBID_REUSE => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_PROXY => $this->proxy_host,
            CURLOPT_PROXYPORT =>$this->proxy_port,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
    /**
     * Connect - Establish the connection to the server. This is done in the constructor
     * of the soap_client class
     */
    function connect() {
        // WSDL url verification
        if (!$this->wsdl_string) {
            if (defined("WSDL_URL")) {
                $this->wsdl_string = WSDL_URL;
            } else {
                print("SOAP: URL of the WSDL is not defined. Please set your WSDL_URL environment variable.");
            }
        }

        try {
            // SOAP Client options
            $options = array();
            if ($this->proxy_host && $this->proxy_port) {
                $options['proxy_host'] = $this->proxy_host;
                $options['proxy_port'] = (int)$this->proxy_port;
            }
            // OAuth WRAP implementation
            $post = array();
            if($this->user && $this->password){
                $post['wrap_name'] = $this->user;
                $post['wrap_password'] = $this->password;
                $options["login"] = $this->user;
                $options["password"] = $this->password;
                // get auth token
                $auth_url="https://services.open.xerox.com/api/Auth/Authenticate";
                $auth_content="wrap_name=".$post['wrap_name']."&wrap_password=".$post['wrap_password'];
                $res = $this->curl_post($auth_url, $post);
                $this->wrap_access_token = substr($res,18);
                $options['Authorization'] = 'WRAP access_token='.$this->wrap_access_token;
            }
             
            // construct the SOAP client
            parent::__construct($this->wsdl_string, $options);
            $header = new SoapHeader('https://open.xerox.com/', 'Authorization', $this->wrap_access_token);
            parent::__setSoapHeaders($header);

        } catch (SoapFault $fault) {
            print_r($fault);
        }
    }
}

/*
 Sample how to use it with the Language Identifier service (no proxy set & no login)
 */

// WSDL URL & autentication
$wsdl = "https://services.open.xerox.com/Wsdl.svc/LanguageIdentifier";

$user = 'merlinxu@brandreward.com';
$password = 'Mega@12345';

// proxy configuration
$proxy_host = NULL;
$proxy_port = NULL;

// SOAP client configuration
//$client = new OpenXeroxSoapClient($wsdl, $user, $password, $proxy_host, $proxy_port);
//$client->connect();

?>