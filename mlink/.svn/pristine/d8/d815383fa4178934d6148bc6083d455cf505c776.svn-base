<?php
global $_cf,$_db,$_req;
selfcheck();

echo "begin\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

$where_date = "";
if(isset($_req['datemonth'])){
    $where_date = " AND left(PaidDate,7) = '".addslashes($_req['datemonth'])."'";
}else{
    $sql = "SELECT * FROM payments ORDER BY PaidDate DESC";
    $row = $_db->getFirstRow($sql);
    $last_paid_date = $row['PaidDate'];
    $where_date = " AND PaidDate = '".$last_paid_date."'";
}
$sql = "select a.*,b.`Alias`,b.`Domain`,c.`Name` as PublisherName,c.NotificationEmail,c.`Email` from payments as a left join publisher_account as b on a.`Site` = b.`ApiKey` left join publisher as c on a.`PublisherId` = c.`ID` where a.EmailSend = 'no' ".$where_date;
$rows = $_db->getRows($sql);

$email_task = array();
foreach($rows as $k=>$v){
    if(!isset($email_task[$v['PublisherId']])){
        $email_task[$v['PublisherId']] = array(
            'Amount'=>$v['Amount'],
            'Email'=>$v['Email'],
            'BillingEmail'=>$v['NotificationEmail'],
            'PublisherId'=>$v['PublisherId'],
            'PublisherName'=>$v['PublisherName'],
            'Detail'=>array(array('Site'=>$v['Site'],'Alias'=>$v['Alias'],'Domain'=>$v['Domain'],'InvoiceFile'=>$v['InvoiceFile'],'Amount'=>$v['Amount'])),
            'PaymentType'=>$v['PaymentType'],
            'PaymentDetail'=>$v['PaymentDetail'],
            'TransactionId'=>$v['TransactionId'],
            'PaidDate'=>$v['PaidDate'],
        );
    }else{
        $email_task[$v['PublisherId']]['Amount'] = bcadd($email_task[$v['PublisherId']]['Amount'], $v['Amount'] , 4);
        $email_task[$v['PublisherId']]['Detail'][] = array('Site'=>$v['Site'],'Alias'=>$v['Alias'],'Domain'=>$v['Domain'],'InvoiceFile'=>$v['InvoiceFile'],'Amount'=>$v['Amount']);
    }
}

foreach($email_task as $email_data){
    $email_content = make_mail_content($email_data);
print_r($email_content);exit();
}
print_r($rows);exit();

echo "end\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

function make_mail_content($data){
    $email_support = "support@brandreward.com";
    $email_finance = "par.finance@brandreward.com";

    $content_tpl = <<<EOF
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40">
<head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 14 (filtered medium)">
<style>
body{
font-family:"Times New Roman","serif";font-size:11.0pt;}
</style>
</head>
<body lang=EN-US link=blue vlink=purple>
    <div>
        <p>Hello {{publisher_name}},</p>
        <p>Your <b>{{payment_month}} {{payment_year}} Invoice</b> &amp; <b>Monthly Transactions Report</b> are attached for {{payment_domain}}</p>
        <p>Payment will be made through {{payment_type}} within {{payment_receive_hour}} hours. Please confirm if the following account information is correct.</p>
        <p><b>{{payment_detail}}</b></p>
        <p>We have also attached a quick Payment FAQ Guide of the most popular questions we receive from clients. If there are any additional questions, please contact the finance team (<i><a href="mailto:{{email_finance}}">{{email_finance}}</a></i>) or through support (<i><a href="mailto:{{email_support}}">{{email_support}}</a></i>).</p>
        <p>Kind regards,</p>
        <p>
            <b>Brandreward Finance Team</b><br>
            <b>Email&nbsp; | </b><a href="mailto:{{email_finance}}">{{email_finance}}</a><br>
            <b>Skype</b> <b>|</b> brandreward</span>
        </p>
    </div>
</body>
</html>

EOF;

    $replace_arr = array();
    $replace_arr['{{publisher_name}}'] = $data['PublisherName'];
    $replace_arr['{{payment_month}}'] = date('M',strtotime($data['PaidDate']));
    $replace_arr['{{payment_year}}'] = date('y',strtotime($data['PaidDate']));
    $domain_str = '';
    if(count($data['Detail']) > 1){
        $domain_str .= "<br>".$data['Detail'][0]['Domain'];
    }else{
        $domain_str = $data['Detail'][0]['Domain'];
    }
    
    $replace_arr['{{payment_domain}}'] = $domain_str;
    $replace_arr['{{payment_detail}}'] = $data['PaymentDetail'];
    $replace_arr['{{email_finance}}'] = $email_finance;
    $replace_arr['{{email_support}}'] = $email_support;
    if($data['PaymentType'] == 'paypal'){
        $replace_arr['{{payment_type}}'] = 'Paypal';
        $replace_arr['{{payment_receive_hour}}'] = '24';
    }elseif($data['PaymentType'] == 'bank'){
        $replace_arr['{{payment_type}}'] = 'Direct wire Transfer';
        $replace_arr['{{payment_receive_hour}}'] = '72';
    }
    $content_tpl = str_replace(array_keys($replace_arr),array_values($replace_arr),$content_tpl);

    return $content_tpl;
}

function selfcheck(){
    global $_req;
    $cmd = "ps aux | grep '\-act=".$_req['act']."' | wc -l";
    //echo $cmd."\n";
    exec($cmd, $res);
    //print_r($res);exit();
    if($res[0] > 3){
        echo "stop\t|\tprocess is doing\n";exit();
    }else{
        return true;
    }
}

function dir_check($root_data_dir,$file_name){
    $dirname = dirname($file_name);
    $dir_path_arr = explode('/',trim($dirname,'/'));

    foreach($dir_path_arr as $k=>$v){
        $num = $k + 1;
        $tmp_path_arr = array_slice($dir_path_arr,0,$num);
        $dir = $root_data_dir.'/'.join('/',$tmp_path_arr);
        if(!is_dir($dir)){
            mkdir($dir,0755);
        }
    }

    if(is_dir($root_data_dir.'/'.ltrim($dirname,'/')))
        return true;
    else
        return false;
}

?>
