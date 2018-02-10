<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
//include_once ('func.php');
mysql_query("SET NAMES 'latin1'");

isset($_GET['p']) ? $p = $_GET['p'] : $p = 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : 30;
isset($_GET['type']) ? $type = $_GET['type'] : $type = 1;
$domain = new Domain();
//添加Url分析
if(isset($_POST['id']) && isset($_POST['domain']))
{
	$id = $_POST['id'];
	$domain = $_POST['domain'];
	$sql = "SELECT ExtUrl FROM publisher_page_detail WHERE DomainInfoID='$id' AND ExtDomain = '$domain'";
	$objProgram = new Program();
	$data = $objProgram->objMysql->getRows($sql);
	$extUrl = array();
	foreach($data as $item)
	{
		$extUrl[] = $item['ExtUrl'];
	}
	echo json_encode($data);
	die;
}
if(isset($_POST['url']) && !empty($_POST['url'])){
    $url = $_POST['url'];
    $name = $_SERVER['PHP_AUTH_USER'];
    $urlarr = explode(',',$url);
    $newarr = array();
    foreach($urlarr as $k=>$v){
        $newarr[$k]['AddUser'] = $name;
        $newarr[$k]['AddTime'] = date("Y-m-d H:i:s");
        $newarr[$k]['Origin'] = 'UserAdd';
        $newarr[$k]['Status'] = 'pending';
        $newarr[$k]['Url'] = $v;
        $newarr[$k]['Domain'] = get_domain($v);
    }
    $res = $domain->innserturl($newarr);
    echo $res;
    die;
}
$users = array('monica','sarahli','nicolas','senait','lillianguo','Vivienne','alain','giulia');
if($type == 1){
    $title = 'Publisher Page Analysis';
    $list = $domain->get_publisher_page($_GET,$p,$pagesize);
    $seachtype = 1;
    if($list == 'No Data'){
        $type1 = 'null';
        $list1 = 'No Data';
        $page_html = '';
    }else{
        $list1 = $list['data'];
        $page_html = get_page_html($list);
        $type1 = 1;
    }
}else if($type == 2){
    $type1 = 1;
    $objTpl->assign('url','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    $list = $domain->get_publisher_page_detail($_GET,$p,$pagesize);
	$seachtype = 2;
    $net = $list['network'];
    if($list == 'No Data'){
        $type = 'null';
        $list1 = 'No Data';
        $page_html = '';
    }else{
        $list1 = $list['data'];
        $page_html = get_page_html($list);

    }
    $title = $_GET['name'];
    $objTpl->assign('network',$net = $list['network']);
}
if(!empty($_GET)){
    foreach($_GET as $key => $data){
        $objTpl->assign($key,$data);
    }
}
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign("title",$title);
$objTpl->assign('type', $type);
$objTpl->assign('users', $users);
$objTpl->assign('list',$list1);
$objTpl->assign('type1', $type1);
$objTpl->assign('seachtype',$seachtype);
$objTpl->assign("pageHtml",$page_html);
$objTpl->display('b_publisher_page.html');