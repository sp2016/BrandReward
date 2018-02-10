<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('SERVER_PASS','44w92FmsgNBzB9v4xeN2');
define("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
define("WSDL_SERVICE", "https://api.affili.net/V2.0/AccountService.svc?wsdl"); 

$soapLogon = new SoapClient(WSDL_LOGON);
$token = $soapLogon->Logon(array(
    'Username' => AFFILIATE_USER,
    'Password' => SERVER_PASS,
    'WebServiceType' => 'Publisher'
));

// Set parameters
$startDate = strtotime("-5 month");
$endDate = strtotime("today");
$publisherId = '805794'; // the publisher ID you want to retrieve payments for (mandatory)
 
// Send a request to the Account Service 
$soapRequest = new SoapClient(WSDL_SERVICE); 
$response = $soapRequest->GetPayments(array(
    'CredentialToken' => $token,                
    'EndDate' => $endDate,
    'PublisherId' => $publisherId,
    'StartDate' => $startDate
));
 
// Show response
print_r($response);

?>
