<?php
/**
*
* DigitalWindow API Client
*
* Copyright (C) 2008 Digital Window Ltd.
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
*/

define('API', 'PS');
require_once('constants.inc.php');
require_once('classes/class.ClientFactory.php');


# Username and password constants are defined in constants.inc.php
$oClient = ClientFactory::getClient();

# Param Examples for use in API Calls #

// Example refine by structure
// Refine by Merchant (3)
$oRefineBy = new stdClass();
$oRefineBy->iId = 3;
$oRefineBy->sName = '';

// Refine by merchant 1599
$oRefineByDefinition = new stdClass();
$oRefineByDefinition->sId = 1599;
$oRefineByDefinition->sName = '';
$oRefineBy->oRefineByDefinition = $oRefineByDefinition;

$aParams1 = array('iCategoryId'	=> array(4,8));
$aParams2 = array("iCategoryId"	=> 8, "bExpandAllBranches" => false);
$aParams3 = array('iCategoryId'	=> 535);
$aParams4 = array('iCategoryId'	=> 361);
$aParams5 = array('iProductId'	=> array(25654634, 4662114, 7154706));
$aParams6 = array("iMerchantId"	=> 1514, "sMerchantProductId" => "854");
$aParams7 = array("sQuery"		=> "wii", "bAdult" => false, "iLimit"=>10, "oActiveRefineByGroup" => $oRefineBy);

$aParams8 = array('iMerchantId'	=> array(1761));
$aParams9 = array('iCategoryId'	=> 575, 'iMaxResult' => 3);
$aParams10= array('iCategoryId'	=> 173, 'bUseGlobalList' => true);


# API Call Examples #
$oResponse= $oClient->call('getCategory', $aParams1);
#$oResponse= $oClient->call('getCategoryTree', $aParams2);
#$oResponse= $oClient->call('getCategoryPath', $aParams3);
#$oResponse= $oClient->call('getDescendantCategoryIds', $aParams4);
#$oResponse= $oClient->call('getProduct', $aParams5);
#$oResponse= $oClient->call('getMerchantProduct',$aParams6);
#$oResponse= $oClient->call('getProductList', $aParams7);

#$oResponse= $oClient->call('getMerchant', $aParams8);
#$oResponse= $oClient->call('getMerchantList', $aParams9);
#$oResponse= $oClient->call('getQueryList', $aParams10);


echo '<pre>';
	$sOutput= '';
	#$sOutput.= $oClient->__getFunctions();
	$sOutput.= $oClient->__getLastRequest();
	#$sOutput.= $oClient->__getLastRequestHeaders();
	$sOutput.= $oClient->__getLastResponse();
	#$sOutput.= $oClient->__getLastResponseHeaders();

	#print 'Quota:'.$oClient->getQuota();

	$sOutput= str_replace('><', ">\n<", $sOutput);

	print $sOutput;
	print_r($oResponse);
echo '</pre>';