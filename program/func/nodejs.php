<?php
function getUrlByNode($domain, $checkurl = "")
{
	$cmd = `node /app/nodejs/server_outgoing_nodejs/get_domain.js url '$domain' '$checkurl'`;
	$return = ''.$cmd.'';
	return $return;
}

?>