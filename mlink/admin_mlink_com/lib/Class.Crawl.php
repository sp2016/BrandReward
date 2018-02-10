<?php

class Crawl extends LibFactory
{

    function __construct()
    {
        if (!isset($this->objMysql))
            $this->objMysql = new Mysql(PROD_DB_NAME, PROD_DB_HOST, PROD_DB_USER, PROD_DB_PASS);
        if (!isset($this->mysql))
            $this->mysql = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);

        $this->Administrator = array(
            'monica',
            'stanguan',
            'jennyzeng',
            'venusyue',
            'gordonpan',
            'sarahli',
            'seandiao',
//            'nicolas',
//            'senait',
//        	'giulia',
//            'alexzheng',
            'mcsky',
//            'lillianguo',
//            'Vivienne'
        );
    }

    public function checkLimit()
    {
        if (isset($_SERVER['PHP_AUTH_USER']) && in_array($_SERVER['PHP_AUTH_USER'], $this->Administrator)) {
            return true;
        } else{
            return false;
        }
    }

    public function doUpdateAffiliateAccount($post = array())
    {
        //初始返回信息
        $r['succ'] = false;

        //权限验证
        if(!$this->checkLimit()){
            $r['error'] = "权限不足";
            return $r;
        };

        //AffId Lost
        if (!isset($post['AffId'])) {
            $r['error'] = "Affid Lost!";
            return $r;
        }

        //获取log所需信息
        $AffId = intval($post['AffId']);
        $r['Account'] = $Account = $post['Account'];
        $r['Password'] = $Password = $post['Password'];
        $sql = "SELECT ID as AffId,Name as Name,Account as OldAccount,Password as OldPassword FROM wf_aff WHERE ID = " . $AffId;
        $old_info = $this->mysql->getRows($sql);
        $log = $old_info = $old_info[0];

        //数据不变不进行操作
        if ($log['OldAccount'] == $Account && $log['OldPassword'] == $Password){
            $r['succ'] = true;
            return $r;
        }

        //更新账密
        $sql = "UPDATE wf_aff SET Account='" . trim(addslashes($Account)) . "',Password='" . trim(addslashes($Password)) . "' WHERE ID = " . $AffId;
        if ($this->mysql->query($sql)) {
            $log['NewAccount'] = $Account;
            $log['NewPassword'] = $Password;

            //error:记录日志
            if (!$this->doUpdateChangeLog($log)) {
                $r['error'] = "update changelog failed !  ";
                $sql = "UPDATE wf_aff SET Account='" . trim(addslashes($log['OldAccount'])) . "',Password='" . trim(addslashes($log['OldPassword'])) . "' WHERE ID = " . $AffId;
                $this->mysql->query($sql);
                return $r;
            }

            //error:更新爬虫登陆模块信息
//            if (!$this->doUpdatePendinglinks($log)) {
//                $r['error'] = "update pendinglinks failed !  ";
//                return $r;
//            }
            //无异常 操作成功
            $r['succ'] = true;
            return $r;
        } else {
            $r['error'] = "update account failed ! ";
        }
        return $r;
    }

    public function doUpdateChangeLog($logInfo = array())
    {
        $d = new DateTime();
        $logInfo['time'] = $d->format("Y-m-d H:i:s");
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $logInfo['Operator'] = $_SERVER['PHP_AUTH_USER'];
        } else {
            $logInfo['Operator'] = 'System';
        }
        $sql = "INSERT INTO aff_account_change_log (" . implode(",", array_keys($logInfo)) . ") VALUES ('" . implode("','", array_values($logInfo)) . "')";
        $result = $this->mysql->getAffectedRows($sql);
        return $result;
    }

    public function doUpdatePendinglinks($info = array())
    {
        $sql = "SELECT * FROM affiliate WHERE AffId = " . $info['AffId'];
        $p = $this->objMysql->getRows($sql);
        if(!isset($p[0])) return 1;
        $AffLoginPostString = $p[0]['AffLoginPostString'];
        $AffLoginPostString = str_replace(urlencode($info['OldAccount']),urlencode($info['NewAccount']),$AffLoginPostString);
        $AffLoginPostString = str_replace(urlencode($info['OldPassword']),urlencode($info['NewPassword']),$AffLoginPostString);
        $sql = "UPDATE affiliate SET AffLoginPostString = '".$AffLoginPostString."' WHERE AffId = ".$p[0]['AffId'];
        $result = $this->objMysql->query($sql);
        return $result;
    }
}