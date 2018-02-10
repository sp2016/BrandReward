<?php
include_once(dirname(__FILE__) . "/const.php");

$paras = array();
$paras["ignorecheck"] = 1;
$oLinkFeed = new LinkFeed($paras);
$aff_id = 58;
//$oLinkFeed->GetAllTransaction($aff_id,'','');
$oLinkFeed->GetAllProgram($aff_id);
//$oLinkFeed->GetAllLinksFromAff($aff_id);
//$oLinkFeed->getCouponFeed($aff_id, "", "feedonly");
//$oLinkFeed->GetMessageFromAff($aff_id);
//$oLinkFeed->GetAllLinksFromAff($aff_id,'','productonly');

//$oLinkFeed->GetAllLinksFromAff($aff_id, "", "productonly");