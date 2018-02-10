<?php

/**
 * exception class 
 *
 * @author Lee
 * @package defaultPackage
 */
if (!defined("__CLASS_MYEXCEPTION__")) 
{
	define("__CLASS_MYEXCEPTION__", 1);
	
	class MyException 
	{
		var $errorMsg;
		var $time;
		var $scriptName;
		var $line;
		
		static function raiseError($errorMsg = "", $scripts=__FILE__, $line = __LINE__)
		{
			#$this->errorMsg = $errorMsg;
			$time = date("Y-m-d H:i:s");
			#$this->scriptName = $scripts;
			#$this->line = $line;
			MyException::setErrorLog($time, $scripts, $line, $errorMsg);
			
			if(DEBUG_MODE || 1)
			{
				echo "<style>body{color:#3e3e3e; font-size:12px; font-family: Georgia,Arial,Helvetica,sans-serif;text-decoration:none;}</style>";
				echo "Exception, Session Halted.<br>\n";
				echo "Time:{$time}<br>\n";
				echo "ScriptName:{$scriptName}<br>\n";
				echo "Line:{$line}<br>\n";
				echo "ErrorMsg:{$errorMsg}<br>\n";
				debug_print_backtrace();
				exit;
			}
			else
			{
				echo "<b>500 Internal Error</b>";
				exit;
			}
		}

		static function setErrorLog($time='', $scriptName='', $line='', $errorMsg='')
		{
			$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			$req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

			$logString = "{$time}\t{$scriptName}\t{$line}\t$ip\t$req\t$ref\t{$errorMsg}\n";
			if(file_exists(LOG_LOCATION))
			{	
				$fp = fopen(LOG_LOCATION.ERROR_LOG_FILE, "ab");
				if($fp)
				{
					fwrite($fp, $logString);
					fclose($fp);
				}
			}
		}
	}
}
?>
