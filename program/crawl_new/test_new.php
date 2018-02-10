<?php
/**
 * User: rzou
 * Date: 2017/7/31
 * Time: 10:05
 */
include_once(dirname(__FILE__) . "/const.php");

$paras = array();

$oLinkFeed = new LinkFeed($paras);
$aff_id = 'a598081cd9eb7d';
$account_id = 'aa598082cd74a2e';
$affSiteAccName = 'Webgains_UK_BR';
$oLinkFeed->batchID = 'b59cb45dad4db5';

$oLinkFeed->GetAllProgram($aff_id, $account_id, $affSiteAccName);
//$oLinkFeed->CheckCrawlBatchData($aff_id);
//$oLinkFeed->GetAllLinksFromAff($aff_id);
//$oLinkFeed->getCouponFeed($aff_id, "", "feedonly");
//$oLinkFeed->GetMessageFromAff($aff_id);
