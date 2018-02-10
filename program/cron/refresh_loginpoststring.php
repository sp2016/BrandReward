<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

echo "<< Start @ " . date("Y-m-d H:i:s") . " >>\r\n";
$obj = new Mysql(PENDING_DB_NAME,PENDING_DB_HOST,PENDING_DB_USER,PENDING_DB_PASS);
$mysql = new Mysql();
$list = array(
    '1---info@couponsnapshot.com---Gfd86tCj215H',
    '2---couponsnap---d74v8&l6Fw',
    '3---pf@couponsnapshot.com---kl&dcYGL97uk&d',
    '4---couponsnap---fd79&G6Fv',
    '5---couponsnapshot---fadfUe923fs',
    '6---info@couponsnapshot.com---f#sg876YF6VD',
    '7---csrobot---greatrobot4432',
    '8---info@couponsnapshot.com---c^gf*Bko(had8',
    '10---info@couponsnapshot.co.uk---3^f8hnUY9VvJ1',
    '12---snapshot---7FDS^Ugid78&0',
    '13---info@couponsnapshot.com---jenvD36v8H$0O',
    '14---info@couponsnapshot.com---jenvD36v8H$0O',
    '15---info@couponsnapshot.com---2mWfj3foR0f2Op',
    '18---info@couponsnapshot.com---jenvD36v8H$0O',
    '20---couponsnapshot---fasdh&b7B8&oq',
    '22---couponsnapshotUK---T&b*b90dUdV',
    '23---info@couponsnapshot.co.uk---67BYt9HFh92c#',
    '25---pf@couponsnapshot.com---khv6ohN7nlD',
    '26---499752---7g8Gp8vy8GS',
    '27---couponsnapshot---fadfUe923fs',
    '28---Ran.Chen1---8kP^FD0n3rc',
    '29---19933---dfLgd5DF9Q1',
    '30---info@couponsnapshot.com---S&F9Bv8l1if#',
    '32---info@couponsnapshot.com---Uu^f9fa765FFDA3',
    '34---info@couponsnapshot.com---jenvD36v8H$0O',
    '35---couponsnapshot---fadfUe923fs',
    '36---couponsnopshotEU---rnf9EJFld',
    '46---cg@couponsnapshot.com---vcdf#2ID*c8u',
    '49---info@couponsnapshot.co.uk---jc6faf*b1V(D40C',
    '52---couponsnapshot---ipfpdA&FGw5jhJF',
    '53---couponsnapshot---g7BWdf7gh1pg',
    '58---Ran Chen---7ud8XDFH#prjh9',
    '59---200061---N8yfhfC0ohQ0ji4',
    '62---info@couponsnapshot.co.uk---Sf90fgob9q%2d9',
    '63---571453---HfxxD1UqNUtRduXQn9A7',
    '133---couponsnapshot---fadfUe923fs',
    '65---snapshot---py2RYvT*FOmPyf6',
    '181---info@couponsnapshot.com---c^gf*Bko(had8',
    '191---info@couponsnapshot.com---OR4uYvEOvVcEb2E',
    '124---info@couponsnapshot.co.uk---1flB2*RU9Iedc',
    '152---couponsnapshot---Uw2JEcl93D',
    '63---571453---cje3fj93D8i5A9H',
    '97---info@couponsnapshot.co.uk---fds6Hjs9w54SD',
    '160---info@couponsnapshot.com---or0DF7BGdwhE8t',
    '196---info@couponsnapshot.com---fd8GB5^xWdm0A7',
    '197---info@couponsnapshot.com---#4d3fGdf8Dc9',
    '182---couponsnap---Pu9n6%2J6ibf',
    '208---info@couponsnapshot.com---jenvD36v8H$0O',
    '360---186348---LEipneFJ9f4h',
    '189---couponsnapshot---FSF7GvFFD*h89e',
    '243---snapshot---Uh47vgf76RT89ovq',
    '123---info@couponsnapshot.com---i4fd8GW3Ql1',
    '397---info@couponsnapshot.com---c^gf*Bko(had8',
    '415---couponsnapshot---fadfUe923fs',
    '421---Ran.Chen6---Ep0g0vV7F1X',
    '469---couponsnapshot---fadfUe923fs',
    '429---couponsnapshot---fadfUe923fs',
    '503---couponsnapshot---vkc8mZOl',
    '539---info@couponsnapshot.com---2ds5fkd14s7j&d',
    '418---728004---lAes343TGcMC1zDX2tMF',
    '491---634921---QL2H4uLf0H6Y956HoQFA',
    '500---712731---6BCg0ay5QCG4RTo0ZZSW'

);
$sql = "select AffId as id,AffLoginPostString as string from affiliate where AffloginPostString not like ''";
$stringList = $obj->getRows($sql);
foreach($stringList as $val){
    $arr[$val['id']] = $val['string'];
}
unset($stringList);
foreach($list as $value){
    $user = explode("---",$value);
    if(isset($arr[$user[0]])){
        $arr[$user[0]] = str_replace(trim(addslashes(urlencode($user[1]))),'XXXXXX',$arr[$user[0]]);
        $arr[$user[0]] = str_replace(trim(addslashes(urlencode($user[2]))),'YYYYYY',$arr[$user[0]]);
        $sql = sprintf("UPDATE affiliate SET AffLoginPostString = '%s' where AffId = %s ",$arr[$user[0]],$user[0]);
        $obj->query($sql);
    }
    $sql = sprintf("UPDATE wf_aff SET Account='%s',Password='%s' WHERE ID = %s",$user[1],$user[2],$user[0]);
//    print_r($sql."\n\r");
    $mysql->query($sql);
}
$sql = array(
    "UPDATE affiliate SET AffLoginPostString = 'email=XXXXXX&password=YYYYYY' WHERE AffId = 8",
    "UPDATE affiliate SET AffLoginPostString = 'txtUsername=XXXXXX&txtPassword=YYYYYY&btnLogin=LOGIN' WHERE AffId = 22",
    "UPDATE affiliate SET AffLoginPostString = 'txt_UserName=XXXXXX&txt_Password=YYYYYY&chk_remember=&chk_simple=&cmd_login.x=33&cmd_login.y=15&__EVENTTARGET=cmd_login&__EVENTARGUMENT=' WHERE AffId = 46",
    "UPDATE affiliate SET AffLoginPostString = 'username=XXXXXX&password=YYYYYY&submit=Login' WHERE AffId = 49",
    "UPDATE affiliate SET AffLoginPostString = 'username=XXXXXX&password=YYYYYY&rememberMe=0&submitLogin=Einloggen&redirectURL=&__FORM=d4281434d727a2d3e9fba50e5ebf7dc94c616096' WHERE AffId = 65",
    "UPDATE affiliate SET AffLoginPostString = 'email=XXXXXX&password=YYYYYY' WHERE AffId = 181",
    "UPDATE affiliate SET AffLoginPostString = 'username=XXXXXX&password=YYYYYY&submit=Login' WHERE AffId = 124",
    "UPDATE affiliate SET AffLoginPostString = 'username=XXXXXX&password=YYYYYY&rememberme=0&login=Log+in' WHERE AffId = 189",
    "UPDATE affiliate SET AffLoginPostString = 'email=XXXXXX&password=YYYYYY' WHERE AffId = 397"
);
foreach($sql as $s)
    $obj->query($s);



unlink(__FILE__);
echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";