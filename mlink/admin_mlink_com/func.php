<?php
function call_api($type, $act, $param = '')
{
    if (empty($type)) {
        return '';
    }
    $api_url = '';
    $api_user = '';
    $api_pwd = '';
    switch ($type) {
        case 'ds':
            $api_url = API_DS_URL . 'index.php?action=' . $act;
            if ($param) {
                $api_url .= '&' . $param;
            }
            $api_user = API_DS_USER;
            $api_pwd = API_DS_PWD;
            break;
        case 'url':
            $api_url = $act;
            break;
        default:
            break;
    }
    print_r($api_url . '<br>' . "\n");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_USERPWD, $api_user . ':' . $api_pwd);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                //不直接将结果输出到屏幕
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function skyaddslash($str)
{
    $str = preg_replace("/\s/", "", $str);
    if (stripos($str, ',')) {
        if (stripos($str, "\"")) {
            $str = str_replace("\"", "\"\"", $str);
        }
        $str = "\"" . $str . "\"";
    }

    return $str;
}

function checkScriptProcessCount($file, $max = 1, $cmd_arg = '')
{
    $script_name = $file;
    $cmd = "ps aux|grep '" . $script_name;
    if ($cmd_arg) {
        $cmd .= ' ' . $cmd_arg;
    }
    $cmd .= "'|grep -v 'grep " . $script_name . "'|grep -v 'sh -c'|wc -l";
    $count = intval(trim(exec($cmd)));
    $error_message = "";
    if ($count > $max) {
        $error_message .= "total number of processes more than the setting (" . $script_name . ":" . $max . "), canceled.\n";
    }
    if ($file == "") {
        $error_message .= "file none exists, canceled.\n";
    }
    if ($error_message != "") {
        echo $error_message;
        exit;
    } else {
        return $count;
    }
}

function getDictionary($type)
{
    global $dictionaryData;
    if (isset($dictionaryData[$type])) {
        return $dictionaryData[$type];
    } else {
        $data = array();
        switch ($type) {
            case 'country' :

                $objAccount = new Account;
                $arr = $objAccount->table('country_codes')->find();
                foreach ($arr as $v) {
                    $array[$v['id']] = $v['CountryName'];
                }
                $data['global'] = 'Global';
                $data['UK'] = 'United Kingdom';
                break;
            case 'sitetype' :
                $data = [
                    'Social Network',
                    'Content',
                    'Content - Blog',
                    'Voucher / Coupon / Deal',
                    'Mobile App',
                    'Search Engine',
                    'Sale',
                    'DSA - Coupon',
                    'DSA - Other',
                    'Content - Editorial',
                    'Content - UGC',
                    'Social Media Influencer',
                    'Price Comparison'
                ];
                break;
        }

        return $array;
    }
}

//发送Email
function send_bronto_email(&$_info)
{
    $mailSender = "http://edm.bwe.io/sendmail.php";
    $ch = curl_init($mailSender);
    curl_setopt($ch, CURLOPT_URL, $mailSender);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, "sendmail_edm");
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_info);
    $pagecontent = curl_exec($ch);
    if (isset($_info["RemoteDebug"]) && $_info["RemoteDebug"]) {
        return $pagecontent;
    }
    $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($curl_code != 200) {
        return false;
    }
    if (substr($pagecontent, 0, 1) != "1") {
        return false;
    }

    return true;
}

/**
 * 需要发送邮件的模板内容
 * $param array key为模板使用的字段 value为值
 * @html text 模板页面
 */
function get_email_template($param, $html)
{
    $objTemp = new TemplateSmarty();
    foreach ($param as $key => $temp) {
        $objTemp->assign($key, $temp);
    }
    $text = $objTemp->fetch($html);

    return $text;
}

/**
 * 发送Email
 * $to 收件人
 * $title 邮件标题
 * $content 邮件内容
 * $emailType 邮件类型(存入数据库时使用)
 * $hostInfo 邮件账号
 * $fromname 发件人姓名
 */
function send_email(
    $to,
    $title,
    $content,
    $emailType = '',
    $hostInfo = 'support',
    $fromname = 'brandreward'
)
{
    if ($hostInfo == 'support') {
        $host = 'mail01.bwe.io';
        $username = 'support@brandreward.com';
        $password = 'Mega@12345';
    } else {
        return false;
    }
    //引入PHPMailer的核心文件 使用require_once包含避免出现PHPMailer类重复定义的警告
    require_once("lib/phpmailer/PHPMailer.php");
    require_once("lib/phpmailer/SMTP.php");
    require_once("lib/phpmailer/Exception.php");
    //实例化PHPMailer核心类
    $mail = new PHPMailer\PHPMailer\PHPMailer;
    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    $mail->SMTPDebug = 1;
    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();
    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth = true;
    //链接qq域名邮箱的服务器地址
    $mail->Host = $host;
    //设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = 'ssl';
    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    $mail->Port = 465;
    //设置smtp的helo消息头 这个可有可无 内容任意
    // $mail->Helo = 'Hello smtp.qq.com Server';
    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    $mail->Hostname = 'https://www.brandreward.com';
    //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
    $mail->CharSet = 'UTF-8';
    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = $fromname;
    //smtp登录的账号 这里填入字符串格式的qq号即可
    $mail->Username = $username;
    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    $mail->Password = $password;
    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $mail->From = $username;
    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML(true);
    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    $mail->addAddress($to, 'brandreward');
    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','lsgo在线通知');
    //添加该邮件的主题
    $mail->Subject = $title;
    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $mail->Body = $content;
    //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    // $mail->addAttachment('./d.jpg','mm.jpg');
    //同样该方法可以多次调用 上传多个附件
    // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
    $status = $mail->send();
    //简单的判断与提示信息
    if ($status) {
        save_send_email_log($emailType, $to, $username);

        return true;
    } else {
        return false;
    }
}

/**
 * 邮件发送记录
 */
function save_send_email_log($emailType, $to, $username)
{
    $sql = "insert into email_send_record(`Type`,`To`,`From`,`Addtime`) VALUES('" . $emailType . "','" . $to . "','" . $username . "','" . date(
            "Y-m-d H:i:s"
        ) . "')";
    mysql_query($sql);
}

function json_encode_no_zh($arr)
{
    $str = str_replace("\\/", "/", json_encode($arr));
    $search = "#\\\u([0-9a-f]+)#ie";
    if (strpos(strtoupper(PHP_OS), 'WIN') === false) {
        $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
    } else {
        $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
    }

    return preg_replace($search, $replace, $str);
}

function get_page_html($page_info)
{
    $query_str = $_SERVER['QUERY_STRING'];                                       //获取的是url ?后面的值
    parse_str(
        $query_str,
        $query_parse
    );                                          //把字符串$query_str解析到数组$query_parse中
    unset($query_parse['p']);                                                    //销毁变量
    $new_query_str = http_build_query(
        $query_parse
    );                             //使用给出的关联（或下标）数组生成一个经过 URL-encode 的请求字符串。
    $url = BASE_URL . '/' . basename(
            $_SERVER['SCRIPT_NAME']
        ) . '?' . $new_query_str . '&p='; //$_SERVER['SCRIPT_NAME']表示当前页路径
    //basename()返回路径最后的文件名
    $page_now = $page_info['page_now'];
    $page_total = $page_info['page_total'];
    $prev = true;
    $next = true;
    $first = true;
    $last = true;
    if ($page_now == 1) {
        $prev = false;
    }
    if ($page_now == $page_total) {
        $next = false;
    }
    $numbers = array();
    $show_num = 5;                //分页栏中显示的页数
    $i = floor($show_num / 2);    //floor：向下舍入最接近的整数
    $start_num = 0;
    do {
        $start_num = $page_now - $i;
        $i--;
    } while ($start_num < 1);
    for ($i = 0; $i < $show_num; $i++) {
        if ($start_num + $i <= $page_total) {
            $numbers[] = $start_num + $i;
        }     //$number[]存储了当前五个要显示的页面号
    }
    if (in_array('1', $numbers)) {
        $first = false;
    }
    if (in_array($page_total, $numbers)) {
        $last = false;
    }
    $html = '';
    if ($prev) {
        $html .= '<li><a href="' . $url . ($page_now - 1) . '">&laquo;</a></li>';
    }//向前退一页
    else {
        $html .= '<li class="disabled"><a href="javascript:void(0)">&laquo;</a></li>';
    }
    if ($first) {
        $html .= '<li><a href="' . $url . '1">1</a></li>';
        if (!in_array('2', $numbers)) {
            $html .= '<li class="disabled"><a href="javascript:void(0)">...</a></li>';
        }
    }
    foreach ($numbers as $d) {           //如果是当前页面，加上蓝色的“active”样式
        if ($d == $page_now) {
            $html .= '<li class="active"><a href="' . $url . $d . '">' . $d . '</a></li>';
        } else {
            $html .= '<li><a href="' . $url . $d . '">' . $d . '</a></li>';
        }
    }
    if ($last) {
        if (!in_array($page_total - 1, $numbers)) {
            $html .= '<li class="disabled"><a href="javascript:void(0)">...</a></li>';
        }
        $html .= '<li><a href="' . $url . $page_total . '">' . $page_total . '</a></li>';
    }
    if ($next) {
        $html .= '<li><a href="' . $url . ($page_now + 1) . '">&raquo;</a></li>';
    } else {
        $html .= '<li class="disabled"><a href="javascript:void(0)">&raquo;</a></li>';
    }
    $page_html = '<div><ul class="pagination">' . $html . '</ul></div>';

    return $page_html;
}

function debug($str)
{
    echo $str . "<br>\r\n";
}

function check_user_login()
{
    $objAccount = new Account;
    if (!$objAccount->get_login_user()) {
        jumpUrl(BASE_URL . '#login');
    }
}

function jumpUrl($url)
{
    echo '<script>window.location.href="' . $url . '"</script>';
    exit();
}

function do_upload_file($data)
{
    $return_d = array();
    $dir = isset($data['dir']) ? $data['dir'] : INCLUDE_ROOT . 'data/upload';
    $dir = str_replace('\\', '/', $dir);
    $dir = str_replace('//', '/', $dir);
    $flag = true;
    if (!is_dir($dir)) {
        $flag = false;
    }
    foreach ($_FILES as $k => $file) {
        if (!$flag) {
            $return_d[$k] = array('res' => 0, 'msg' => 'ERROR: dir path error');
            continue;
        }
        if (is_uploaded_file($file['tmp_name']) && empty($file['error'])) {
            $name_info = explode('.', $file['name']);
            $ext = array_pop($name_info);
            $rename = date('ymdHis') . rand(1000, 9999) . '.' . $ext;
            if (isset($data['file_rename_pre']) && !empty($data['file_rename_pre'])) {
                $rename = $data['file_rename_pre'] . $rename;
            }
            $new_file = $dir . '/' . $rename;
            if (move_uploaded_file($file['tmp_name'], $new_file)) {
                $return_d[$k]['res'] = 1;
                $return_d[$k]['oldname'] = $file['name'];
                $return_d[$k]['file'] = $new_file;
                $return_d[$k]['line'] = get_file_line($new_file);
                $return_d[$k]['rename'] = $rename;
            } else {
                $return_d[$k]['res'] = 0;
                $return_d[$k]['msg'] = 'ERROR: move file error';
            }
        } else {
            $return_d[$k]['res'] = 0;
            $return_d[$k]['msg'] = 'ERROR: upload file error';
        }
    }

    return $return_d;
}

function get_file_line($file)
{
    if (!is_file($file)) {
        return 0;
    }
    $file_path = $file;
    $line = 0;
    $fp = fopen($file_path, 'r');
    if ($fp) {
        while (!feof($fp)) {
            fgets($fp);
            $line++;
        }
        fclose($fp);
    }

    return $line;
}

function update_add($table_name, $add)
{
    $temp = array();
    $keys = array();
    $value = array();
    foreach ($add as $k => $val) {
        if (!empty($val)) {
            $temp[$k] = $val; //temp存储有添加值的键值对
        }
    }
    foreach ($temp as $k => $val) {
        $keys[] = $k;
        $value[] = '"' . $val . '"';
    }
    $keys_str = implode(",", $keys);
    $value_str = implode(",", $value);
    //add  $table_name表
    $query = 'INSERT INTO ' . $table_name . '(' . $keys_str . ') VALUES (' . $value_str . ')';
    mysql_query($query);
}

function update_edit($table_name, $edit, $where)
{
    $query = 'SELECT * FROM ' . $table_name . ' WHERE ' . $where;
    $result = mysql_query($query);
    $arr = mysql_fetch_array($result, MYSQL_ASSOC);
    $save_data = array();
    $pre_next = array();
    foreach ($arr as $k => $val) {//要想使用这段代码，传值的所有字段的name都必须和数据表中的字段名相同
        if (isset($edit[$k])) {
            if ($val !== $edit[$k]) {
                $save_data[] = $k;//$save_data存储所有改变了值的字段名
            }
        }
    }
    //----------------------------------------------update  $table_name表----------------------------------------------------
    $condition = array();
    foreach ($save_data as $val) {
        $condition[] = $val . '="' . $edit[$val] . '"';
    }
    $condition = implode(
        ",",
        $condition
    );//将数组转化成字符串，如：Name="Co12",ShortName="bbb22"
    $sql = 'UPDATE ' . $table_name . ' SET ' . $condition . ' WHERE ' . $where;
    mysql_query($sql);
}

function call_sys_api($func, $param = array())
{
    $param_str = 'act=' . urlencode($func);
    if (!empty($param)) {
        foreach ($param as $k => $v) {
            $param_str .= '&' . $k . '=' . urlencode($v);
        }
    }
    $url = API_URL . '?' . $param_str;
    $result = file_get_contents($url);
    $result = json_decode($result, true);

    return $result;
}

function get_page_html_ajax($page_info, $jsfunc)
{
    $query_str = $_SERVER['QUERY_STRING'];                                       //获取的是url ?后面的值
    parse_str(
        $query_str,
        $query_parse
    );                                          //把字符串$query_str解析到数组$query_parse中
    unset($query_parse['p']);                                                    //销毁变量
    $new_query_str = http_build_query(
        $query_parse
    );                             //使用给出的关联（或下标）数组生成一个经过 URL-encode 的请求字符串。
    $url = BASE_URL . '/' . basename(
            $_SERVER['SCRIPT_NAME']
        ) . '?' . $new_query_str . '&p='; //$_SERVER['SCRIPT_NAME']表示当前页路径
    //basename()返回路径最后的文件名
    $page_now = $page_info['page_now'];
    $page_total = $page_info['page_total'];
    $prev = true;
    $next = true;
    $first = true;
    $last = true;
    if ($page_now == 1) {
        $prev = false;
    }
    if ($page_now == $page_total) {
        $next = false;
    }
    $numbers = array();
    $show_num = 5;                //分页栏中显示的页数
    $i = floor($show_num / 2);    //floor：向下舍入最接近的整数
    $start_num = 0;
    do {
        $start_num = $page_now - $i;
        $i--;
    } while ($start_num < 1);
    for ($i = 0; $i < $show_num; $i++) {
        if ($start_num + $i <= $page_total) {
            $numbers[] = $start_num + $i;
        }     //$number[]存储了当前五个要显示的页面号
    }
    if (in_array('1', $numbers)) {
        $first = false;
    }
    if (in_array($page_total, $numbers)) {
        $last = false;
    }
    $html = '';
    if ($prev) {
        $html .= '<li><a href="javascript:void(0);" onclick="pageJump(' . ($page_now - 1) . ')">&laquo;</a></li>';
    }//向前退一页
    else {
        $html .= '<li class="disabled"><a href="javascript:void(0)">&laquo;</a></li>';
    }
    if ($first) {
        $html .= '<li><a href="javascript:void(0);" onclick="pageJump(1)">1</a></li>';
        if (!in_array('2', $numbers)) {
            $html .= '<li class="disabled"><a href="javascript:void(0)">...</a></li>';
        }
    }
    foreach ($numbers as $d) {           //如果是当前页面，加上蓝色的“active”样式
        if ($d == $page_now) {
            $html .= '<li class="active"><a href="javascript:void(0);" onclick="pageJump(' . $d . ')">' . $d . '</a></li>';
        } else {
            $html .= '<li><a href="javascript:void(0);" onclick="pageJump(' . $d . ')">' . $d . '</a></li>';
        }
    }
    if ($last) {
        if (!in_array($page_total - 1, $numbers)) {
            $html .= '<li class="disabled"><a href="javascript:void(0)">...</a></li>';
        }
        $html .= '<li><a href="javascript:void(0);" onclick="pageJump(' . $page_total . ')">' . $page_total . '</a></li>';
    }
    if ($next) {
        $html .= '<li><a href="javascript:void(0);" onclick="pageJump(' . ($page_now + 1) . ')" >&raquo;</a></li>';
    } else {
        $html .= '<li class="disabled"><a href="javascript:void(0)">&raquo;</a></li>';
    }
    $page_html = '<div><ul class="pagination">' . $html . '</ul></div>';

    return $page_html;
}

function get_domain($url)
{
    if (!$url) {
        return null;
    }
    $url = strtolower($url);
    $tmpUrl = explode('.', $url);
    if (count($tmpUrl) == 1) {
        return null;
    } else {
        if (count($tmpUrl) == 2) {
            foreach ($tmpUrl as $v) {
                if ($v == '') {
                    return;
                }
            }
        }
    }
    if (!preg_match("/^https?:\\/\\//i", $url)) {
        $url = "http://" . $url;
    }
    $rs = parse_url($url);
    $url = $rs["host"];
    $objProgram = New Program();
    $sql = "select Domain from domain_top_level";
    $topDomain_tmp = $objProgram->objMysql->getRows($sql);
    $topDomain = array();
    foreach ($topDomain_tmp as $v) {
        $topDomain[] = '\.' . $v['Domain'];
    }
    $country_arr = explode(",", $objProgram->global_c);
    foreach ($country_arr as $country) {
        if ($country) {
            $country = "\." . strtolower($country);
            $topDomain[] = "\.com?" . $country;
            $topDomain[] = "\.org?" . $country;
            $topDomain[] = "\.net?" . $country;
            $topDomain[] = "\.gov?" . $country;
            $topDomain[] = "\.edu?" . $country;
            $topDomain[] = $country . "\.com";
            $topDomain[] = $country;
        }
    }
    //TODO add judgement of blogspot and wordpress
    if (stristr($url, 'blogspot')) {
        for ($i = 0; $i < count($topDomain); $i++) {
            $topDomain[$i] .= '\.blogspot';
        }
    } else {
        if (stristr($url, 'wordpress')) {
            for ($i = 0; $i < count($topDomain); $i++) {
                $topDomain[$i] .= '\.wordpress';
            }
        }
    }
    //$exception = array(
    //	'blogspot','wordpress'
    //);
    //foreach($exception as $item)
    //{
    //	if(stristr($url,$item)){
    //		for($i = 0;$i < count($topDomain);$i++){
    //			$topDomain[$i] = '.'.$item.$topDomain[$i];
    //		}
    //		break;
    //	}
    //}
    $pattern = "/([^\.]*)(" . implode("|", $topDomain) . ")$/mi";
    preg_match($pattern, $url, $matches);
    if (count($matches) > 0) {
        return $matches[0];
    } else {
        $main_url = $url;
        if (!strcmp(long2ip(sprintf("%u", ip2long($main_url))), $main_url)) {
            print_r($main_url);

            return $main_url;
        } else {
            $arr = explode(".", $main_url);
            $count = count($arr);
            $endArr = array("com", "net", "org");//com.cn net.cn
            if (in_array($arr[$count - 2], $endArr)) {
                $domain = $arr[$count - 3] . "." . $arr[$count - 2] . "." . $arr[$count - 1];
            } else {
                $domain = $arr[$count - 2] . "." . $arr[$count - 1];
            }

            return $domain;
        }
    }
}

/*
 *@param $logArr = array(array('ProgramId'=>'','FieldName'=>'','FieldValueOld'=>'','FieldValueNew'=>'','ModifyUser'=>''),.....)
 *
 */
function insert_program_manual_change_log($logArr)
{
    $objProgram = New Program();
    $currentTime = date('Y-m-d H:i:s');
    if (!empty($logArr)) {
        $sql = '';
        foreach ($logArr as $id => $log) {
            $logOldValue = addslashes($log['FieldValueOld']);
            $logNewValue = addslashes($log['FieldValueNew']);
            if ($id == 0) {
                $sql = "insert into program_manual_change_log (ProgramId, FieldName,FieldValueOld,FieldValueNew,ModifyUser,AddTime,LastUpdateTime) VALUES ({$log['ProgramId']},{$log['FieldName']},'{$logOldValue}','{$logNewValue}','{$log['ModifyUser']}','{$currentTime}','{$currentTime}')";
            } else {
                $sql .= ",({$log['ProgramId']},{$log['FieldName']},'{$logOldValue}','{$logNewValue}','{$log['ModifyUser']}','{$currentTime}','{$currentTime}')";
            }
        }
        $objProgram->objMysql->query($sql);
    }
}

function _array_column($input, $column_key, $index_key = null)
{
    if (empty($input)) {
        return array();
    }
    if (!is_array($input)) {
        return array();
    }
    $column_arr = array();
    $index_arr = array();
    foreach ($input as $k => $v) {
        if (!empty($column_key) && isset($v[$column_key])) {
            $column_arr[] = $v[$column_key];
        }
        if (!empty($index_key) && isset($v[$index_key])) {
            $index_arr[] = $v[$index_key];
        }
    }
    if (!empty($index_key)) {
        $output = array();
        foreach ($index_arr as $k => $v) {
            $output[$v] = $column_arr[$k];
        }

        return $output;
    } else {
        return $column_arr;
    }
}

function mk_publisher_where($tag = true)
{
    return $tag ? ' (PublisherId <= 10 OR PublisherId IN (90692,54,432))' : " (PublisherId > 10 AND PublisherId NOT IN (90692,54,432))";
}

function get_sys_am()
{

    $query = 'SELECT * FROM `user_admin` WHERE Role = "am"';
    $result = mysql_query($query);
    $users = array();
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
        if(isset($row['Value']))
        {
            array_push($users, $row['Value']);
        }
    }

    return $users;
}

?>