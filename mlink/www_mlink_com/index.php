<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

if(isset($_GET['brref'])){
    $objAccount = new Account();
    $brref = $_GET['brref'];

    $currentUrl = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    preg_match('/(\\?|&)brref=([^&]*)($|&)/i',$currentUrl,$m);
    if(count($_GET) > 1){
        $removeStr = ( ($m[1]=='?')?'':$m[1] ).'brref='.$m[2];
    }else{
        $removeStr = $m[0];    
    }
    $newUrl = str_replace($removeStr,'',$currentUrl);

    $sql = 'SELECT * FROM publisher WHERE ID = '.intval($brref);
    $row = $objAccount->getRow($sql);
    if($row){
        #set cookie
        setcookie("br.refer.p", intval($brref), time() + 86400*30);
    }

    #jumpTo index
    Header("HTTP/1.1 302 Temporarily Moved");
    header('Location: '.$newUrl);
    exit();
}

//切换地区
if(isset($_POST['changeArea']) && isset($_POST['area'])){
    setcookie("area", $_POST['area'], time()+60*60*24*30);
    echo json_encode(array('code'=>1,'msg'=>'success'));
    exit;
}
//判断应该是哪个地区
if(isset($_COOKIE["area"])){
    $area = $_COOKIE["area"];
}else {
    $acceptLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
    if (preg_match("/zh/i", $acceptLang)){
        $area = "zh";
    }else if(preg_match("/fr/i", $acceptLang)){
        $area = "fr";
    }else if (preg_match("/de/i", $acceptLang)){
        $area = "de";
    }else{
        $area = "us";
    }
    setcookie("area", $area, time()+60*60*24*30);
}




$objTpl->assign('url', BASE_URL);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title', 'Brandreward - Your Complete Monetization Solution');
/* if(isset($_GET['language']) && !empty($_GET['language']) && $_GET['language'] == 'German'){
    $objTpl->assign('language', 'German');
    $objTpl->display('index_g.html');
}else if(isset($_GET['language']) && !empty($_GET['language']) && $_GET['language'] == 'French'){
    $objTpl->assign('language', 'French');
    $objTpl->display('index_f.html');
}else if(isset($_GET['language']) && !empty($_GET['language']) && $_GET['language'] == 'English')
{
    $objTpl->assign('language', 'English');
    $objTpl->display('index.html');
}else{
    $objTpl->assign('language', 'English');
    $objTpl->display('index.html');
} */

/* if(in_array($area, array('zh,sg'))){
    $objTpl->display('index.html');
}else  */ if(in_array($area, array('de'))){
    $objTpl->display('index_g.html');
}else if(in_array($area, array('fr'))){
    $objTpl->display('index_f.html');
}else {
    $objTpl->display('index.html');
}
?>
