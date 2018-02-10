<?php
if(isset($_SERVER['HTTP_SHOST']) || @$_SERVER['HTTPS'] == 'on')
    define('SCHEME','https');
else
    define('SCHEME','http');

define('INCLUDE_ROOT', dirname(__FILE__).'/');

define('BASE_URL', SCHEME.'://www.brandreward.com');
define('GO_URL', 'http://r.brandreward.com/');
define('CDN_URL', 'http://cdn.brandreward.com');
define('CDN_URL_CF', 'http://d3vl89pgi9jbtl.cloudfront.net');
define('API_URL', SCHEME.'://api.ezconnexion.com');
define('Brand','Brandreward');

define('DB_HOST', 'localhost');
define('DB_NAME', 'bdg_go_base');
define('DB_USER', 'bdg_go');
define('DB_PASS', 'shY12Nbd8J');

define('REDIS_HOST', '192.168.1.242');
define('REDIS_PORT', 6379);
define('REDIS_USER', '');
define('REDIS_PASS', '');
define('REDIS_DB_NAME', '');
define('MYSQL_ENCODING','UTF8');
define('BR_VER', '20170703');
define('DEBUG_MODE',false);

?>