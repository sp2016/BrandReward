<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$init_opt = array('method','filter','date','operator');
$GLOBALS['notice'] = <<<EOT
Example:
    php merchant.php --method=register
    php merchant.php --method=getallcontent
                        --filter=[merchantid|lastchangetime|addtime]
                        --date=[ |[hour(int)|default:12]|[hour(int)|default:12]]
                        --operator=[eq|gt|lt|sw|sf|li]
                     --method=formatcontent
                     --method=getlogo
EOT;

function notice(){
    mydie($GLOBALS['notice']);
}

$option = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
empty($option)?notice():true;
$option = explode('--',$option);
$opts = array();
foreach ($option as $opt){
    $tmp = explode('=',trim($opt));
    if(!in_array($tmp[0],$init_opt))continue;
    $opts[$tmp[0]]=$tmp[1];
}
$merchant = new MerchantExt();
isset($opts['method'])?true:notice();
switch ($opts['method']){
    case 'register':
        $merchant->Register();
        break;
    case 'getallcontent':
        !isset($opts['filter']) ?$opts['filter']='merchantid':true;
        $date = isset($opts['date'])?$opts['date']:12;
        switch ($opts['filter']) {
            case 'merchantid':
                $merchant->GetAllContent($opts['filter'], isset($opts['operator']) ? $opts['operator'] : 'li', '');
                break;
            case 'lastchangetime':
                $merchant->GetAllContent($opts['filter'], isset($opts['operator']) ? $opts['operator'] : 'ge', $date);
                break;
            case 'addtime':
                $merchant->GetAllContent($opts['filter'], isset($opts['operator']) ? $opts['operator'] : 'ge', $date);
                break;
            default:
                notice();
                break;
        }
        break;
    case 'formatcontent':
        $merchant->FormatContent();
        break;
    case 'getlogo':
        $merchant->GetAllMerchantLogo();
        break;
//    case 'getcontent':
//        $merchant->GetContent();
//        break;
    case 'getcsv':
        $merchant->getcsv();
        break;
    default:
        notice();

}