<?php
/*
 * Created on 2007-10-1
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!defined("__FUNC_CHECK__"))
{
	define("__FUNC_CHECK__", 1);
	function checkUrl($str){
	  	return preg_match("/^(http|https|ftp)\:\/\/[A-Za-z0-9\-]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str);
	 }
	 function checkFreeshipping($string){
		$patten = "[^a-zA-Z]";
		preg_match_all("/free[^a-zA-Z]{0,10}shipping/ix", $string, $match);
		if(count($match[0]) > 0){
			return true;
		}else{
			return false;
		}
	 }
}

?>
