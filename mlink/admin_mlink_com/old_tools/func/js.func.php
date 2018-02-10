<?php
/*
 * Created on 2007-10-1
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!defined("__FUNC_JS__"))
{
	define("__FUNC_JS__", 1);
	
	function alert($str)
	{
		echo "<script language =\"javascript\"> alert(\"".$str."\")</script>\r\n";
	}
	
	function set_url($url)
	{
		echo "<script language =\"javascript\">window.location = \"".$url."\"</script>\r\n";
		echo "<br>Page not auto-refreshed? <a href='$url'>click here</a> to manual refresh.<br>";
	}
	
	function set_top_url($url)
	{
		echo "<script language =\"javascript\">top.location = \"".$url."\"</script>\r\n";
	}
	
	function top_url_refresh()
	{
		echo "<script language =\"javascript\">top.location.refresh()</script>\r\n";
	}
	
	function url_refresh()
	{
		echo "<script language =\"javascript\">window.location.refresh()</script>\r\n";
	}
	
	function opener_refresh()
	{
		echo "<script language =\"javascript\">  if(window.opener) window.opener.location.reload();</script>\r\n";
	}

	function back()
	{
		echo "<script language =\"javascript\">history.back(-1)</script>\r\n";
	}
	
	function close_window()
	{
		echo "<script language =\"javascript\">window.close()</script>\r\n";
	}
}
?>
