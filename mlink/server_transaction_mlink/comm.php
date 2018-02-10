<?php
define('PATH_CODE', dirname(__FILE__));
define('PATH_COOKIE', PATH_CODE.'/cookie');
define('PATH_TMP', PATH_CODE.'/tmp');
define('PATH_DATA', PATH_CODE.'/data');
define('PATH_INCLUDE', PATH_CODE.'/include');
define('PATH_DATA_LOG',PATH_CODE.'/datalog');
define('DB_CONFIG', PATH_CODE.'/etc/db.ini');
define('MAX_TRY', 10);

set_include_path(PATH_CODE.'/include');

//数据库连接信息
require_once DB_CONFIG;

date_default_timezone_set('America/Los_Angeles');

//联盟ID  useless just for remember
$AFF_IDS = array(
    10  => "affwin",
    26  => "affili",
    63  => "affili_de",
    500 => "affili_fr",
    360 => "adcell",
    15  => "zanox",
    32  => "avangate",
    70  => "ebay",
    172 => "ebay",
    173 => "ebay",
    174 => "ebay",
    579 => "ebay",
    30  => "ond",
    133 => "td",
    7   => "sas",
    181 => "avt_ca",
    22  => "afffuk",
    20  => "afffus",
    12  => "lc",
    62  => "commissionmonster",
    115 => "cf",
    50  => "sr",
    2   => "ls",
    1   => "cj",
    14  => "wg",
    58  => "impradus",
    59  => "impraduk",
    46  => "cg",
    49  => "tagau",
    124 => "taguk",
    196 => "tagsg",
    197 => "tagas",
    191 => "viglink",
    223 => "skimlinks",
    6   => "pjn",
    28  => "dgmnew_au",
    31  => "dgmnew_nz",
    152 => "belboon",
    29  => "por",
    23  => "silvertap",
    188 => "phg",
    398 => "zoobax",
    408 => "gameladen",
    163 => "omgpm_asia",
    240 => "omgpm_india",
    57  => "omgpm_uk",
    125 => "omgpm_au",
    110 => "omnicom",
    412 => "visualsoft",
    578 => "yieldkit",
    533 => "mopubi",
    276 => "autopartsway",
    225 => "automotivetouchup",
    37  => "clickbank",
    193 => "clixie",
    160 => "flexoffers",
    164 => "vcommission",
    113 => "effiliation",
    557 => "glopss",

);
//自加载类
function __autoload($class){
    $class_file = PATH_CODE."/lib/class.{$class}.php";
    if(file_exists($class_file)) include_once($class_file);
}


function mydie($string = ''){
    echo $string; die;
}
?>
