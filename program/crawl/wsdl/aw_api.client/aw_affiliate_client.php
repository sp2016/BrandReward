<?php
/**
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
 */

define('API', 'AW');



require_once ('constants.inc.php');
require_once ('classes/class.ClientFactory.php');

function getmerchantdetail($IdInAff){
	# Username and password constants are defined in constants.inc.php
	$oClient = ClientFactory::getClient();
	
	# Example parameters to pass
	$aParams1 = array(
	    //'aMerchantIds'	=> array(),
		'dStartDate' => '2014-02-01T00:00:00', 
		'dEndDate' => '2014-02-28T23:59:59', 
		'sDateType' => 'transaction',
		'sTransactionStatus' => 'declined',
		'iLimit' => 2
	);
	$aParams2 = array(
	    'aTransactionIds' => array(15048244, 15048246), 
	    'dStartDate' => '2007-08-01T00:00:00', 
	    'dEndDate' => '2007-08-22T23:59:59', 
	    'sDateType' => 'transaction'
	);
	$aParams3 = array(
		'sRelationship' => 'joined'
	);
	$aParams4 = array(
		'aMerchantIds' => array($IdInAff)
	);
	$aParams5 = array(
	    //'aMerchantIds'=> array(),
		'dStartDate' =>	'2014-02-01T00:00:00', 
		'dEndDate' => '2014-02-28T23:59:59', 
		'sDateType' => 'transaction',
		'iLimit' =>	2
	);
	$aParams6 = array(
		'aStatus' => Array('pending'),//approved、declined
		'aClickRefs' => array('myClickRef2013', 'myClickRef2014')
	);
	$aParams7 = array(
		'aTransactionIds' => array(15048244, 15048246)
	);
	$aParams8 = array(
		'dStartDate' =>	'2014-02-01T00:00:00', 
		'dEndDate' => '2014-02-28T23:59:59', 
		'sDateType' => 'transaction',
		'iLimit' =>	2
	);
	$aParams9 = array(
		'iMerchantId' => 1599,
		'sCommissionGroupCode' => 'DEFAULT'
	);
	$aParams10 = array(
		'iMerchantId' => 1688,
	);
	
	#$oResponse = $oClient->call('getTransactionList', $aParams1);
	#$oResponse = $oClient->call('getTransaction', $aParams2);
	#$oResponse = $oClient->call('getMerchantList', $aParams3);
	$oResponse = $oClient->call('getMerchant', $aParams4);
	#$oResponse = $oClient->call('getClickStats', $aParams5);
	#$oResponse = $oClient->call('getTransactionQuerys', $aParams6);
	#$oResponse = $oClient->call('getTransactionProduct', $aParams7);
	#$oResponse = $oClient->call('getImpressionStats', $aParams8);
	#$oResponse = $oClient->call('getCommissionGroup', $aParams9);
	#$oResponse = $oClient->call('getCommissionGroupList', $aParams10);
	
	echo '<pre>';
	$sOutput = '';
	#$sOutput.= $oClient->__getFunctions();
	$sOutput .= $oClient->__getLastRequest();
	#$sOutput.= $oClient->__getLastRequestHeaders();
	$sOutput .= $oClient->__getLastResponse();
	#$sOutput.= $oClient->__getLastResponseHeaders();
	
	#print 'Quota:'.$oClient->getQuota();
	
	$sOutput = str_replace('><', ">\n<", $sOutput);
	
	echo $sOutput;
	return $oResponse;
	
	echo '</pre>';	
}

function getAllMerchant(){
	$oClient = ClientFactory::getClient();
	
	$aParams3 = array(
			//'sRelationship' => 'joined'                            //无参数可以爬取除了closed之外的所有商家，包括rejected、pending、no joined、suspended
	);
	$oResponse = $oClient->call('getMerchantList', $aParams3);
	return $oResponse;
}






