<?php
class CBLogin
{
	function check_token($token,$public_salt,$token_salt)
	{
		$oMcryptString = new McryptString();
		$remote_timestamp = $oMcryptString->decode_for_transfer($token,$public_salt,$token_salt);
		if(!is_numeric($remote_timestamp)) return false;
		if(abs($remote_timestamp - time()) < 7200) return true;
		return false;
	}
}
?>