<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');


if($_POST && $_POST['alike']){
    
    $id = $_POST['id'];
    $sql = "select publisherId from publisher_domain_whois where id = $id limit 1";
    $res = $db->getRows($sql);
     
    
    $sql = "SELECT a.*,b.name,b.domain,b.username,b.email,b.status,b.domain FROM publisher_alike a left join publisher b on a.alikepublisherId = b.id WHERE a.publisherid = {$res[0]['publisherId']} AND b.status = 'Active'";
    $data = $db->getRows($sql);
    $html = '<table id="example" class="table table-striped">';
    $html .= '<tr>
                 <th>Publisher Info</th>
                 <th>Alike Content</th>
              </tr>';
    foreach ($data as $dataValue){
       
       $AlikeContentStr = '';
       $AlikeContent = json_decode($dataValue['AlikeContent'],true);
       foreach ($AlikeContent as $ck=>$c){
           $AlikeContentStr .= $ck.'  '.$c.'<br/>';
       } 
       
       //print_r($AlikeContent);exit;
       $html .= '<tr class="open-logs tr">
                    <td>Name:'.$dataValue['name'].'<br>Email:'.$dataValue['email'].'<br/>Domain:'.$dataValue['domain'].'<br/>Status'.$dataValue['status'].'</td>
                    <td>'.$AlikeContentStr.'</td>      
                 </tr>';
    }
    $html .= '</table>';
    echo $html;exit;
}




$pagesize = 10;
$where = 'where 1=1 ';
$Keyword = isset($_GET['Keyword']) ? $_GET['Keyword'] : '' ;
$Keyword = trim($Keyword);
if($Keyword){
    $where .= " and a.rawWhoisData like '%".$Keyword."%'";
}
$na = isset($_GET['na']) ? $_GET['na'] : '';
$na = trim($na);
if($na){
    $where .= " and (b.name like '%$na%' or b.domain like '%$na%' or b.username like '%$na%' or b.email like '%$na%' or c.apikey like '%$na%' or c.alias like '%$na%')";    
}

$chk = isset($_GET['chk']) ? $_GET['chk'] : '';
$chk = trim($chk);
if($chk){
    $where .= "  and a.alinkCount >0";
}


$page = isset($_GET['p']) ? $_GET['p'] : 1;
$limit = " LIMIT ".($page-1)*$pagesize.",$pagesize";

$sql_names_set = 'SET NAMES latin1';
$db->query($sql_names_set);

$sql = "SELECT a.*, b.name,b.UserName,b.Status,b.Domain,b.Email,a.alinkCount FROM publisher_domain_whois as a left join publisher b on a.publisherId = b.id left join publisher_account c on c.publisherid = a.publisherId $where $limit";
//echo $sql;exit;
$whoisArr = $db->getRows($sql);

//$sql = "select count(*) as count from publisher_domain_whois $where  ";
$sql = "SELECT count(*) as count FROM publisher_domain_whois as a left join publisher b on a.publisherId = b.id left join publisher_account c on c.publisherid = a.publisherId $where";
$count = $db->getRows($sql);


$page_html = get_page_html(array('page_now'=>$page,'page_total'=>ceil($count[0]['count']/$pagesize))); 


$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['js'][] = BASE_URL.'/js/jquery.filer.min.js';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.filer.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.filer-dragdropbox-theme.css';

$objTpl->assign("title","Publisher Domain Whois");
$objTpl->assign('list', $whoisArr);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('pageHtml', $page_html);
$objTpl->display('b_publisher_domain_whois.html');