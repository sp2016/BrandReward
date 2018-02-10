<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPublisher = new Publisher();

if(isset($_POST['action']) && isset($_POST['action']) == 'save_publisher_potential'){
    foreach($_POST as $k=>$v){
        $_POST[$k] = trim($v);
    }
    $sql = 'UPDATE publisher_potential SET country = "'.addslashes($_POST['country']).'",category = "'.addslashes($_POST['category']).'",url= "'.addslashes($_POST['url']).'",blogname= "'.addslashes($_POST['blogname']).'",name= "'.addslashes($_POST['name']).'",email= "'.addslashes($_POST['email']).'",comment= "'.addslashes($_POST['comment']).'" WHERE id = '.intval($_POST['ppid']);
    $objPublisher->query($sql);
    exit();
}

$id = $_POST['id'];

$row = $objPublisher->table('publisher_potential')->where('ID = '.intval($id))->findone();
$row_contact = $objPublisher->table('publisher_potential_contact')->where('ppid = '.intval($id))->find();
$data_contact = array();
foreach($row_contact as $k=>$v){
    $data_contact[$v['type']] = $v;
}

$objTpl->assign("publisher",$row);
$objTpl->assign("contactlist",$data_contact);
echo $objTpl->fetch('b_publisher_potential.html');