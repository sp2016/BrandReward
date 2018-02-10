<?php
class LinkFeed_9999_Duomai
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

        $this->info = array(
            'AffId' => 9999,
            'Account' => 'contact@extrabux.com',
            'Password' => 'extez88qdg',
            'AffLoginUrl' => 'http://www.duomai.com/index.php?m=siter&a=dologin',
            'referer' => 'http://www.duomai.com/',
            'AffLoginVerifyString' => 'contact@extrabux.com',
            'AffLoginSuccUrl' => 'http://www.duomai.com/index.php?m=siter&a=index'
        );

        $this->islogined = false;
    }

    function Login()
    {
        if ($this->islogined) return $this->islogined;

        $result = $this->oLinkFeed->GetHttpResult('http://www.duomai.com/', array("AffId" => $this->info["AffId"], "method" => "get"));

        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'post',
            "postdata" => 'email='. urldecode($this->info['Account']) .'&password='. urldecode($this->info['Password']) .'&weeklogin=on',
            "no_ssl_verifyhost" => true,
        );
        if (isset($this->info["referer"])) $request["referer"] = $this->info["referer"];
        $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);//返回爬取的AffLoginSuccUrl页面各项信息的数组
        if ($arr["code"] == 0) {
            if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $arr["error_msg"])) {
                $request["no_ssl_verifyhost"] = 1;
                $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
            }
        }

        if ($arr["code"] == 200) {
            if (stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false) {
                $this->islogined = true;
            }
            //handle redir by meta tag
            if (!$this->islogined && stripos($arr["content"], "REFRESH") !== false && isset($this->info["AffLoginSuccUrl"]) && $this->info["AffLoginSuccUrl"]) {
                $url_path = @parse_url($this->info["AffLoginSuccUrl"], PHP_URL_PATH);//parse_url用于解析url，返回一个关联数组。parse_url("xxx", PHP_URL_PATH)返回数组的path值
                if ($url_path && stripos($arr["content"], $url_path) !== false) {
                    $this->islogined = true;
                }
            }
        }

        if ($this->islogined){
            return $this->islogined;
        }else {
            mydie("die: login failed for aff({$this->info['AffId']}) <br>\n");
        }
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        $this->GetProgramByPage();
    }

    function GetProgramByPage()
    {
        $request = array("AffId" => $this->info["AffId"], "method" => "get");
        $this->login();
        $outStr = '';

        $page = 1;
        $hasNextPage = true;
        while ($hasNextPage) {
            if ($page == 1) {
                $url = 'http://www.duomai.com/index.php?m=siter_act&a=index';
            } else {
                $url = 'http://www.duomai.com/index.php?m=siter_act&a=index&p=' . $page;
            }

            $r = $this->oLinkFeed->GetHttpResult($url, $request);
            $result = preg_replace('@>\s+<@', '><', $r['content']);

            preg_match("@条记录 \d+\/(\d+) 页@", $result, $maxPage);
            if (!isset($maxPage[1]) || empty($maxPage[1])) {
                mydie("Can't find the max page number, please check the page!");
            }

            $maxPage = $maxPage[1];
            if ($page >= $maxPage) {
                $hasNextPage = false;
            } else {
                $page++;
            }

            $hasMore = true;
            $strPos = stripos($result, 'input name="ads_id[]"');
            if (!$strPos) {
                mydie("The page display have changed!");
            }

            while ($hasMore) {
                if (stripos($result, 'input name="ads_id[]"', $strPos) === false) {
                    $hasMore = false;
                }
                $IdInAff = intval($this->oLinkFeed->ParseStringBy2Tag($result, 'a href="/index.php?m=siter_act&a=view&ads_id=', '"', $strPos));
                $name = trim($this->oLinkFeed->ParseStringBy2Tag($result, '>', '<', $strPos));
                $type = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td','>'), '</td', $strPos));
                $category = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td','>'), '</td', $strPos));
                $commission = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td','>'), '</td', $strPos));
                $status = strip_tags(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td', '<td', '<td', '<td','>'), '</td', $strPos)));
                $pr = $this->oLinkFeed->GetHttpResult("http://www.duomai.com/index.php?m=siter_act&a=view&ads_id=$IdInAff", $request);
                $homepage = trim($this->oLinkFeed->ParseStringBy2Tag($pr['content'], array('活动基本信息', 'a href="'), '"'));

                $list = array(
                    'IdInAff' => $IdInAff,
                    'Name' => $name,
                    'Type' => $type,
                    'category' => $category,
                    'commission' => $commission,
                    'status' => $status,
                    'homepage' => $homepage
                );
                $outStr .= implode("\t",$list) . "\n";
            }
        }
        $outStr = implode("\t", array_keys($list)) . "\n" . $outStr;

        $filename = 'duomai'.date('YmdHis');
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=".$filename.".xls");
        $strexport=iconv('UTF-8',"GB2312//IGNORE",$outStr);
        exit($strexport);
    }
}
