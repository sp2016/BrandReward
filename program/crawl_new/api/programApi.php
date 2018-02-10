<?php
/**
 * User: rzou
 * Date: 2017/10/9
 * Time: 18:03
 */

include_once(dirname(__FILE__) . "/const.php");

$result = array('code' => 0, 'error_msg' => '');
$params = array();

if (count($_GET)) {
    $params = paramsFilter($_GET);
} elseif(count($_POST)) {
    $params = paramsFilter($_POST);
}

if (!$params) {
    $result['error_msg'] = 'The request parameter can not be empty !';
    echoJson($result);
}

if (!isset($params['site_name'])) {
    $result['error_msg'] = 'The site_name not be provided !';
    echoJson($result);
} elseif (empty($params['site_name'])) {
    $result['error_msg'] = 'The site_name can not be empty !';
    echoJson($result);
}

if (!isset($params['page'])) {
    $params['page'] = 1;
}

if (!isset($params['pagesize'])) {
    $params['pagesize'] = PAGESIZE;
}

$pApi = new ProgramApiDb();
$result = $pApi->getSiteInfoBySiteName($params['site_name']);

if (!$result['code']) {
    echoJson($result);
}

$result = $pApi->getAllProgramInfo($params['page'], $params['pagesize']);
echoJson($result);
