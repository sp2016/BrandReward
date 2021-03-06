<?php

class LinkFeed extends LinkFeedDb
{
    //var $CookiePath = "/tmp/likefeed/";
    var $affiliates = array();
    var $instances = array();
    var $workingdirs = array();
    var $httpinfos = array();
    var $debug = false;

    function __construct($paras = array())
    {
        if (!isset($this->objMysql))
            $this->objMysql = new MysqlExt();
        if (!isset($this->taskDB))
            $this->taskDB = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
        $this->ignorecheck = isset($paras["ignorecheck"]) ? 1 : 0;
        $this->nocache = isset($paras["nocache"]) ? 1 : 0;
        $this->oHttpCrawler = new HttpCrawler();
    }
	
	/**
	 * 根据联盟ID获取联盟的实例
	 * @param $aff_id
	 * @return mixed
	 */
    function getInstance($aff_id)
    {
        if (isset($this->instances[$aff_id])) return $this->instances[$aff_id];
        if (!isset($this->affiliates[$aff_id])) $this->getAffById($aff_id);            //getAffById函数将查询的记录，存入affiliates数组
        if (!isset($this->affiliates[$aff_id])) mydie("aff_id($aff_id) not found.");
        $class_name = $this->getClassNameByAffID($aff_id);                            //getClassNameByAffID返回值return "LinkFeed_" . $aff_id . "_" . $class_name
        $class_file = $this->getClassFilePath($class_name);                              //getClassFilePath返回联盟类文件路径
        if (!is_file($class_file)) $this->createDefaultClassFile($aff_id);
        include_once($this->getClassFilePath($class_name));
        $obj = new $class_name($aff_id, $this);//去看113的构造函数，就知道这里为什么要俩形参								  //php中，如果变量值是一个类名，可以直接new这个变量，即相当于new这个类
        if (!is_object($obj)) mydie("get Instance of $class_name failed");
        $this->instances[$aff_id] = $obj;
        return $obj;
    }
	
	/**
	 * 创建默认位置的功能文件
	 * @param $aff_id
	 * @return bool
	 */
    function createDefaultClassFile($aff_id)
    {
        $class_template_file = INCLUDE_ROOT . "classtemplate.txt";
        $template_text = file_get_contents($class_template_file);                     //file_get_contents函数将文件内容读入一个字符串
        $class_name = $this->getClassNameByAffID($aff_id);
        $class_file = $this->getClassFilePath($class_name);
        if (file_exists($class_file)) {
            echo basename($class_file) . " is existing , skip it ...\n";               //basename()返回路径的文件名部分
            return false;
        }

        $arr_from = array("{class_name}");
        $arr_to = array($class_name);
        foreach ($this->affiliates[$aff_id] as $k => $v) {
            $arr_from[] = "{" . $k . "}";
            $arr_to[] = addslashes($v);
        }

        file_put_contents($class_file, str_replace($arr_from, $arr_to, $template_text));
        chmod($class_file, 0666);                                                       //chmod函数改变文件的读写权限
    }
	
	/**
	 * 根据类名生成并返回类文件的path
	 * @param $class_name
	 * @return string
	 */
    function getClassFilePath($class_name)
    {
        $class_file = "class." . $class_name . ".php";
        return INCLUDE_ROOT . "lib/LinkFeed/" . $class_file;             //INCLUDE_ROOT是当前文件的路径
    }
	
	/**
	 * 根据给定的类名按照类名生成规则返回类文件名称
	 * @param $aff_id
	 * @param $display_name
	 * @return string
	 */
    function getClassNameByDisplayName($aff_id, $display_name)
    {
        $class_name = trim($display_name);
        if (($pos = strpos($class_name, "(")) !== false) $class_name = trim(substr($class_name, 0, $pos));
        $class_name = str_replace(array(" ", ".", "-"), "_", $class_name);
        $class_name = ucfirst($class_name);
        if (!$class_name) mydie("something wrong here");
        return "LinkFeed_" . $aff_id . "_" . $class_name;
    }
	
	/**
	 * 根据affid返回对应的类名
	 * @param $aff_id
	 * @return string
	 */
    function getClassNameByAffID($aff_id)
    {
        if (!isset($this->affiliates[$aff_id])) $this->getAffById($aff_id);
        if (!isset($this->affiliates[$aff_id])) mydie("aff_id($aff_id) not found.");
        $display_name = $this->affiliates[$aff_id]["AffName"];
        return $this->getClassNameByDisplayName($aff_id, $display_name);
    }

    //===============================================================================
	
	/**
	 * 根据指定的affid，返回缓存文件的路径
	 * @param $aff_id
	 * @param $file_name
	 * @param $group_name
	 * @param bool $use_true_file_name = false
	 * @return string
	 */
    function fileCacheGetFilePath($aff_id, $file_name, $group_name, $use_true_file_name = false)
    {
    	if(!$use_true_file_name) $file_name .= "." . date("YmdH") . ".cache";
        $working_dir = $this->getWorkingDirByAffID($aff_id, $group_name);
        return $working_dir . $file_name;
    }
	
	/**
	 * 判断给定的路径是否是一个已经存在的缓存文件
	 * @param $cache_file
	 * @return bool
	 */
    function fileCacheIsCached($cache_file)
    {
        if ($this->nocache) return false;
        return file_exists($cache_file);
    }
	
	/**
	 * 获取指定路径的缓存文件中的内容
	 * @param $cache_file
	 * @return bool|string
	 */
    function fileCacheGet($cache_file)
    {
        if ($this->fileCacheIsCached($cache_file)) return file_get_contents($cache_file);
        return false;
    }
	
	/**
	 * 向指定缓存文件的路径中插入缓存内容
	 * @param $cache_file
	 * @param $content
	 * @return bool|int
	 */
    function fileCachePut($cache_file, &$content)//向$cache_file文件中写入$content内容
    {
        $cache_file_temp = $cache_file . "." . time();
        $r = file_put_contents($cache_file_temp, $content);
        if ($r === false) {
            @unlink($cache_file_temp);
            return false;
        }
        @chmod($cache_file_temp, 0666);
        @rename($cache_file_temp, $cache_file);
        return $r;
    }
	
	/**
	 * 创建LinkFeed_$affId_$groupName对应的工作空间(搭建数据存放目录)，并返回其路径
	 * @param $affId
	 * @param string $groupName
	 * @return string
	 */
    function getWorkingDirByAffID($aff_id, $group_name = "")
    {
        if (isset($this->workingdirs[$aff_id][$group_name])) {
            return $this->workingdirs[$aff_id][$group_name];
        }

        $is_mkdir = false;

        $dir = INCLUDE_ROOT . "data/";                                                        //创建data文件夹
        if (!is_dir($dir)) {
            $is_mkdir = true;
            mkdir($dir);
            chmod($dir, 0777);
        }
        $dir .= $this->getClassNameByAffID($aff_id) . "/";                                    //在data文件夹下，创建LinkFeed_10_AW等文件夹
        if (!is_dir($dir)) {
            $is_mkdir = true;
            mkdir($dir);
            chmod($dir, 0777);
        }

        if ($group_name) {
            $dir .= $group_name . "/";
            if (!is_dir($dir)) {
                $is_mkdir = true;
                mkdir($dir);
                chmod($dir, 0777);
            }
        }
        if ($is_mkdir && !is_dir($dir)) mydie("make Working Dir failed: $dir\n");

        $this->workingdirs[$aff_id][$group_name] = $dir;
        return $dir;
    }
	
	/**
	 * 调用功能类库中的getMerAffIDByURL($strURL)方法(待详细研究)
	 * @param $aff_id
	 * @param $strURL
	 * @param $isNoSidMerchant
	 * @return mixed
	 */
    function getMerAffIDByURL($aff_id, $strURL, &$isNoSidMerchant)
    {
        $_obj = $this->getInstance($aff_id);
        if (!method_exists($_obj, 'getMerAffIDByURL')) {
            $isNoSidMerchant = true;
            return $strURL;
        }

        $isNoSidMerchant = false;
        return $_obj->getMerAffIDByURL($strURL);
    }
	
	/**
	 * 调用功能类库中的getMerAffIDByURL($strURL)方法，获取所有的商家和link
	 * @param $aff_id
	 */
    function GetAllMerchantAndLink($aff_id)
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
        );

        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);
        $this->GetMerchantListFromAff($aff_id);
        $this->GetAllLinksFromAff($aff_id);
        $this->endJob($arr_log);
    }
	
	/**
	 * 调用功能类库中的getMerAffIDByURL($strURL)方法，获取多有的商家列表
	 * @param $aff_id
	 * @return array
	 */
    function GetMerchantListFromAff($aff_id)
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "DefaultErrorMsg" => "Method " . __METHOD__ . " in LinkFeed Object(AffId=$aff_id) not found",
        );

        return $arr_return;

        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'GetMerchantListFromAff')) {
            //add log
            $arr_log = array(
                "JobName" => __METHOD__,
                "AffId" => $aff_id,
                "MerchantId" => "",
                "SiteId" => 0,
                "AffectedCount" => 0,
                "UpdatedCount" => 0,
                "Detail" => "",
            );
            $this->addJob($arr_log);

            $arr_return = $_obj->GetMerchantListFromAff();

            $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
            $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
            $this->endJob($arr_log);
        }

        if ($this->debug && !isset($arr_return["DefaultErrorMsg"])) {
            print "GetMerchantListFromAff for aff $aff_id is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }
        return $arr_return;
    }
	
	/**
	 * 清除http访问记录（cookie文件），并清除保存的this->info信息
	 * @param $aff_id
	 */
    function clearHttpInfos($aff_id)
    {
        $cookiejar = $this->getCookieJarByAffId($aff_id);
        if (file_exists($cookiejar)) @unlink($cookiejar);//unlink() 函数删除文件。若成功，则返回 true，失败则返回 false。
        if (isset($this->httpinfos[$aff_id])) unset($this->httpinfos[$aff_id]);
    }
	
	/**
	 * 返回对应affid的文件的路径（类似data/LinkFeed_10_AW/aff_10.cookie）
	 * @param $aff_id
	 * @return mixed
	 */
    function getCookieJarByAffId($aff_id)
    {
        if (!isset($this->httpinfos[$aff_id]["cookiejar"])) {
            $this->httpinfos[$aff_id]["cookiejar"] = $this->getWorkingDirByAffID($aff_id) . "aff_" . $aff_id . ".cookie";
        }
        return $this->httpinfos[$aff_id]["cookiejar"];
    }
	
	/**
	 * 按照$_para数组中指定的参数定义的规则获取URL的资源
	 *（该方法自动获取该affid对应的cookie文件位置并设置进$_para["cookiejar"]参数内）
	 * @param $_url
	 * @param array $_para
	 * @param string $ch
	 * @return array
	 */
    function GetHttpResult($_url, $_para = array(), $ch = "")
    {
        if (isset($_para["AffId"]) && !isset($_para["cookiejar"])) {
            $_para["cookiejar"] = $this->getCookieJarByAffId($_para["AffId"]);
        }
        return $this->oHttpCrawler->GetHttpResult($_url, $_para, $ch);//HttpCrawler类中的函数，返回爬取的页面各项信息的数组
        //return HttpCrawler::GetHttpResult($_url,$_para);
    }
	
	/**
	 * 模拟一个常规登录方式联盟的登录
	 * @param $aff_id
	 * @param $info
	 * @param int $retry
	 * @param bool $processverify
	 * @param bool $forcedefaultlogin
	 * @param bool $checkpreviousseesion
	 * @return bool
	 */
    function LoginIntoAffService($aff_id, $info, $retry = 3, $processverify = true, $forcedefaultlogin = false, $checkpreviousseesion = true)//模拟登陆
    {
        if (isset($this->httpinfos[$aff_id]["islogined"])) return $this->httpinfos[$aff_id]["islogined"];

        if ($checkpreviousseesion && isset($info["AffLoginSuccUrl"]) && isset($info["AffLoginVerifyString"]) && $info["AffLoginSuccUrl"] && $info["AffLoginVerifyString"]) {
            //try to use previous seesion,看cookie文件中是否有postdata的信息，如果有，就能模拟登陆了，如果没有，跳出此if，向下执行
            $request = array("AffId" => $info["AffId"], "no_ssl_verifyhost" => true,);
            $arr = $this->GetHttpResult($info["AffLoginSuccUrl"], $request);//返回爬取的AffLoginSuccUrl页面各项信息的数组
            if (stripos($arr["content"], $info["AffLoginVerifyString"]) !== false) {
                echo "very good, previous session found, VerifyString is '" . $info["AffLoginVerifyString"] . "' <br>\n";
                $this->httpinfos[$aff_id]["islogined"] = true;
                return true;
            }
        }
        if (!isset($info['AffLoginUrl'])) return false;
        $this->clearHttpInfos($aff_id);//删除缓存文件，删除httpinfos[$aff_id]变量
        $islogined = false;
        $_obj = $this->getInstance($aff_id);
        if (!$forcedefaultlogin && method_exists($_obj, 'LoginIntoAffService'))//method_exists()检查类的方法是否存在
        {
            echo "processing self LoginIntoAffService ...\n";
            $islogined = $_obj->LoginIntoAffService();
            if ($islogined === "stophere") return false;
        } else {
            //default login method,第一次登陆都要走这里
            $request = array(
                "AffId" => $info["AffId"],
                "method" => $info["AffLoginMethod"],
                "postdata" => $info["AffLoginPostString"],
                "no_ssl_verifyhost" => true,
            );
            if (isset($info["referer"])) $request["referer"] = $info["referer"];
            $arr = $this->GetHttpResult($info['AffLoginUrl'], $request);//返回爬取的AffLoginSuccUrl页面各项信息的数组
// 			echo "<pre>";
// 			$arr['content'] = str_ireplace("<", "#;", $arr['content']);
// 			print_r($arr);
// 			exit;

            //if code = 0, set ssl verifyhost false
            if ($arr["code"] == 0) {
                if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $arr["error_msg"])) {
                    $request["no_ssl_verifyhost"] = 1;
                    $arr = $this->GetHttpResult($info['AffLoginUrl'], $request);
                }
            }

            if ($arr["code"] == 200) {
                if ($processverify && isset($info["AffLoginVerifyString"]) && $info["AffLoginVerifyString"]) {
                    //checking login page result
                    if (stripos($arr["content"], $info["AffLoginVerifyString"]) !== false) {
                        echo "verify succ: " . $info["AffLoginVerifyString"] . "\n";
                        $islogined = true;
                    }
                    //handle redir by meta tag
                    if (!$islogined && stripos($arr["content"], "REFRESH") !== false && isset($info["AffLoginSuccUrl"]) && $info["AffLoginSuccUrl"]) {
                        $url_path = @parse_url($info["AffLoginSuccUrl"], PHP_URL_PATH);//parse_url用于解析url，返回一个关联数组。parse_url("xxx", PHP_URL_PATH)返回数组的path值
                        if ($url_path && stripos($arr["content"], $url_path) !== false) {
                            echo "good, verify succ (redir by meta tag) <br>\n";
                            $islogined = true;
                        }

                    }
                    if (!$islogined) echo "verify login failed(" . $info["AffLoginVerifyString"] . ") <br>\n";
                } elseif (isset($info["AffLoginSuccUrl"]) && isset($info["AffLoginVerifyString"]) && $info["AffLoginSuccUrl"] && $info["AffLoginVerifyString"]) {
                    //checking AffLoginSuccUrl
                    //try to use previous seesion
                    $request = array("AffId" => $info["AffId"],);
                    $arr = $this->GetHttpResult($info["AffLoginSuccUrl"], $request);
                    if (stripos($arr["content"], $info["AffLoginVerifyString"]) === false) {
                        print_r($arr);
                        mydie("die: login failed for aff($aff_id) by double checking AffLoginSuccUrl <br>\n");
                    }
                } else {
                    $islogined = true;
                }
            }

        }
        if (!$islogined) {
            if ($retry > 1) {
                if ($retry > 10) $retry = 10;
                if ($retry < 2) $retry = 2;

                $sec = 300 - $retry * 60;
                if ($sec < 60) $sec = 60;
                echo "login failed ... wait $sec and retry ...\n";
                sleep($sec);
                return $this->LoginIntoAffService($aff_id, $info, --$retry, $processverify, $forcedefaultlogin, $checkpreviousseesion);
            }
            print_r($arr);
            mydie("die: login failed for aff($aff_id) <br>\n");
        }

        if ($this->debug) print "good, Aff($aff_id) is Logined! <br>\n";

        $this->httpinfos[$aff_id]["islogined"] = $islogined;
        return $islogined;
    }
	
	/**
	 * ?
	 * @param $_file
	 * @param $_arr
	 */
    function logarray($_file, &$_arr)
    {
        foreach ($_arr as $k => $v) $_arr[$k] = $this->trimfortsv(trim($v));
        $msg = implode("\t", $_arr) . "\t" . date("Y-m-d H:i:s") . "\n";
        error_log($msg, 3, $_file);//error_log方法，将信息写入文件
    }
	
	/**
	 * 按指定规则的正则匹配指定的字符串
	 * @param $str
	 * @param $pattern
	 * @param int $offset
	 * @param string &$result
	 * @return bool
	 */
    function str_seek(&$str, $pattern, $offset = 0, &$result = "")
    {
        $result = array();
        if (preg_match('|^/.*/[imseADSUXu]*$|', $pattern)) {
            if (preg_match($pattern, $str, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $result = array(
                    "pos" => $matches[0][1],
                    "string" => $matches[0][0],
                    "length" => strlen($matches[0][0]),
                );
            } else return false;
        } else {
            $pos = stripos($str, $pattern, $offset);
            if ($pos === false) return false;
            $result = array(
                "pos" => $pos,
                "string" => $pattern,
                "length" => strlen($pattern),
            );
        }
        return true;
    }
	
	/**
	 * 获取$strSource字符串中，介于$patternBefore和$patternAfter之间的内容
	 * @param $strSource
	 * @param $patternBefore
	 * @param string $patternAfter
	 * @param int $nOffset
	 * @param string $result
	 * @return bool|string
	 */
    function ParseStringBy2Tag(&$strSource, $patternBefore, $patternAfter = "", &$nOffset = 0, &$result = "")
    {
        $result = array();
        $last_matched_pos = $nOffset;
        $last_matched_str_length = 0;
        if (!empty($patternBefore)) {

            if (is_string($patternBefore)) {
                $patternBefore = array($patternBefore);
            }
            for ($i = 0; $i < sizeof($patternBefore); $i++) {
                $pattern = $patternBefore[$i];
                if ($this->str_seek($strSource, $pattern, $last_matched_pos + $last_matched_str_length, $seek_result))//str_seek函数判断$patter是不是$strSource的子字符串
                {   //$strSource是爬取页面返回的content
                    $result[] = $seek_result;
                    $last_matched_pos = $seek_result["pos"];
                    $last_matched_str_length = $seek_result["length"];
                } else return false;
            }
        }
// 		echo "<pre>";
// 		print_r($result);
// 		exit;
        if ($patternAfter == '') {
            $nOffset = $end_pos = strlen($strSource);
        } else if ($this->str_seek($strSource, $patternAfter, $last_matched_pos + $last_matched_str_length, $seek_result)) {
            $result[] = $seek_result;
            $end_pos = $seek_result["pos"];
            //$nOffset = $end_pos + $seek_result["length"];
            $nOffset = $end_pos;
        } else return false;//end pattern not found

        $strResult = substr($strSource, $last_matched_pos + $last_matched_str_length, $end_pos - $last_matched_pos - $last_matched_str_length);//取<select>和</select>中间的content

        if ($this->debug) print "ParseStringBy2Tag($last_matched_pos,$nOffset) result: $strResult  <br>\n";
        return $strResult;
    }
	
	/**
	 * 获取$strSource字符串中，介于$patternBefore数组最后一个元素和$patternAfter之间的内容
	 * （$patternBefore数组的非末尾元素都是用来指路匹配的。）
	 * @param $strSource
	 * @param $patternBefore
	 * @param $patternAfter
	 * @param int $nOffset
	 * @return array
	 */
    function ParseStringBy2TagToArray(&$strSource, $patternBefore, $patternAfter, &$nOffset = 0)
    {
        $arr_return = array();
        while ($str = $this->ParseStringBy2Tag($strSource, $patternBefore, $patternAfter, $nOffset)) {
            $arr_return[] = $str;
        }
        return $arr_return;
    }
	
	/**
	 * ?
	 * @param $strContent
	 * @return string
	 */
    function getPromoTypeByLinkContent($strContent)
    {
        $couponKeywordRegx = array(
        	'/\bcoupon(s)?\b/is',
	        '/voucher\s+code/is',
	        '/with\s+the\s+use\s+of\s+code/is',
            '/(use|using|with|enter|promo|promotion|promotional|discount|voucher|exclusive|this|w|w\/)(\s+(the|this))?\s*code(s)?/is',
	        '/code:/is'
        );
        $couponStrictFilterKeywordRegx = array(
        	'/(discount|coupon)\s+code\s*[:|：]+\s*(none\s+necessary|no\s+code|no\s+code\s+required|none|n\/a|not\s+required|none\s+required|no\s+code\+needed)/is',
            '/no\s+(coupon\s+|promo\s+)?code\s+(need|needed|required|necessary)/is',
	        '/no\s+coupon\s+necessary/is'
        );
        $maybeCouponRegx = array(
        	'/\b(?:code|Code|CODE)\s*("|\'|“|‘)\s*[a-zA-Z0-9-]+\s*\\1/s',
	        '/\b(?:code|Code|CODE)\s*(?::|：)\s*[a-zA-Z0-9-]+/s',
	        '/\b(?:code|Code|CODE)\s+[A-Z0-9-]{4,}(?:\b|\s+)/s'
        );
        $freeShippingKeywordRegx = array(
        	'/free\s*(shipping|delivery|S&H)/is',
	        '/\bfree\s+[^\s]+\s+shipping\b/is',
	        '/\bflat-rate\s+shipping\b/is',
	        '/\bfree\s+[^\s]+\s+.*delivery\b/is',
            '/ships\s+free/is'
        );
        $dealRegx = array('@\b(deal|save|less|purchase|give|discount|off|cash back|cashback|shop now)\b@i');

        $strContent = html_entity_decode($strContent);
        $strContent = strip_tags($strContent);
        $ret_val = 'N/A';
        $isCouponFlag = false;
        foreach ($couponKeywordRegx as $val) {
            if (preg_match($val, $strContent, $match)) {
                $ret_val = 'coupon';
                $isCouponFlag = true;
                foreach ($couponStrictFilterKeywordRegx as $val1) {
                    if (preg_match($val1, $strContent, $matches)) {
                        $ret_val = 'N/A';
                        break;
                    }
                }
                if ($ret_val == 'coupon') break;
            }
        }
        if ($ret_val == 'N/A' && !$isCouponFlag) {
            foreach ($maybeCouponRegx as $val) {
                if (preg_match($val, $strContent, $match)) {
                    $ret_val = 'coupon';
                    break;
                }
            }
        }
        if ($ret_val != 'coupon') {
            foreach ($freeShippingKeywordRegx as $val) {
                if (preg_match($val, $strContent, $match)) {
                    $ret_val = 'free shipping';
                    break;
                }
            }
        }
        if ($ret_val == 'N/A') { //并且不是一个banner，再判断是否deal
            if (!preg_match('@\d+\s*x\s*\d+@i', $strContent, $match)) {
                foreach ($dealRegx as $val) {
                    if (preg_match($val, $strContent, $match)) {
                        $ret_val = 'deal';
                        break;
                    }
                }
            }
            
        }
        return $ret_val;
    }

    //TODO Links only
    function GetAllLinksFromAff($aff_id, $strCheckDate = "", $tp = "")
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
        );

        //add log
        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);

        switch ($tp) {
            case '':
                $_obj = $this->getInstance($aff_id);
                if (method_exists($_obj, 'GetAllLinksByAffId')){
                    $arr_link_result = $_obj->GetAllLinksByAffId();
                    $arr_return["AffectedCount"] += $arr_link_result["AffectedCount"];
                    $arr_return["UpdatedCount"] += $arr_link_result["UpdatedCount"];
                }else{
                    {
                        $arr_feed_result = $this->getCouponFeed($aff_id);
                        if (!isset($arr_feed_result["DefaultErrorMsg"])) {
                            $arr_return["AffectedCount"] += $arr_feed_result["AffectedCount"];
                            $arr_return["UpdatedCount"] += $arr_feed_result["UpdatedCount"];
                            if ($arr_feed_result["AffectedCount"] == 0) {
                                echo "*******Warning: No Feed Find for Aff: $aff_id <br>\n";
                            }
                        }
                    }
                    {
                        $arr_link_result = $this->getPageLinks($aff_id, $strCheckDate);
                        $arr_return["AffectedCount"] += $arr_link_result["AffectedCount"];
                        $arr_return["UpdatedCount"] += $arr_link_result["UpdatedCount"];
                    }

                }
                break;
            case 'feedonly':
                $arr_feed_result = $this->getCouponFeed($aff_id);
                if (!isset($arr_feed_result["DefaultErrorMsg"])) {
                    $arr_return["AffectedCount"] += $arr_feed_result["AffectedCount"];
                    $arr_return["UpdatedCount"] += $arr_feed_result["UpdatedCount"];
                    if ($arr_feed_result["AffectedCount"] == 0) {
                        echo "*******Warning: No Feed Find for Aff: $aff_id <br>\n";
                    }
                }
                break;
            case 'linkonly':
            	$_obj = $this->getInstance($aff_id);
        		if (method_exists($_obj, 'GetAllLinksByAffId')){
                    $arr_link_result = $_obj->GetAllLinksByAffId();
                    $arr_return["AffectedCount"] += $arr_link_result["AffectedCount"];
                    $arr_return["UpdatedCount"] += $arr_link_result["UpdatedCount"];
                }else{
	                $arr_link_result = $this->getPageLinks($aff_id, $strCheckDate);
	                $arr_return["AffectedCount"] += $arr_link_result["AffectedCount"];
	                $arr_return["UpdatedCount"] += $arr_link_result["UpdatedCount"];
                }
                break;
            case 'productonly':
                $_obj = $this->getInstance($aff_id);
                if (method_exists($_obj, 'GetAllProductsByAffId')){
                    $arr_link_result = $_obj->GetAllProductsByAffId();
                    $arr_return["AffectedCount"] += $arr_link_result["AffectedCount"];
                    $arr_return["UpdatedCount"] += $arr_link_result["UpdatedCount"];
                }
                break;
        }




        if ($this->debug) {
            print "GetAllLinksFromAff for aff $aff_id is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }

        $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
        $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
        $this->endJob($arr_log);

        return $arr_return;
    }

    function GetInvalidLinksFromAff($aff_id)
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);
        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'getInvalidLinks')) {
            $r = $_obj->getInvalidLinks();
            if (!empty($r) && is_array($r))
                $this->invalidLinks = $r;
            else if (!empty($r))
                $arr_return['DefaultErrorMsg'] = $r;
            else
                $arr_return['DefaultErrorMsg'] = 'unexcept error.';
            $arr_return['AffectedCount'] = count($this->invalidLinks);
            $arr_return['UpdatedCount'] = $this->saveInvalidLinks();
            $arr_log['Detail'] = sprintf("Get invalid links... %s/%s result(s) found. <br>\n", $arr_return['UpdatedCount'], $arr_return['AffectedCount']);
        } else
            $arr_return['DefaultErrorMsg'] = "Method " . __METHOD__ . " in LinkFeed Object(AffId=$aff_id) not found";
        echo $arr_log['Detail'];
        $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
        $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
        if (!empty($arr_return['DefaultErrorMsg']))
            $arr_log['Detail'] = $arr_return['DefaultErrorMsg'];
        $this->endJob($arr_log);
        if (!empty($arr_return['DefaultErrorMsg']))
            mydie($arr_return['DefaultErrorMsg']);
        return $arr_return;
    }

    function GetMessageFromAff($aff_id)
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);
        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'getMessage')) {
            $r = $_obj->getMessage();
            if (!empty($r) && is_array($r)) {
                foreach ($r as $key => $data) {
                    if (!empty($data['content_url'])) {
                        if ($this->checkMessageExist($data)) {
                            unset($r[$key]);
                        } else if (method_exists($_obj, 'getMessageDetail')) {
                            $detail = $_obj->getMessageDetail($data);
                            if (is_array($detail))
                                $r[$key] = $detail;
                        }
                    }
                    if (isset($r[$key]['content_url']))
                        unset($r[$key]['content_url']);
                }
                $this->message = $r;
            } else if (!empty($r))
                $arr_return['DefaultErrorMsg'] = $r;
            else if (is_array($r) && empty($r))
                $arr_return['DefaultErrorMsg'] = "empty result.\n";
            else
                $arr_return['DefaultErrorMsg'] = "unexcept error.\n";
            $arr_return['AffectedCount'] = count($this->message);
            $arr_return['UpdatedCount'] = $this->saveMessage();
            $arr_log['Detail'] = sprintf("Get message... %s/%s result(s) found. <br>\n", $arr_return['UpdatedCount'], $arr_return['AffectedCount']);
        } else
            $arr_return['DefaultErrorMsg'] = "Method " . __METHOD__ . " in LinkFeed Object(AffId=$aff_id) not found";
        echo $arr_log['Detail'];
        $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
        $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
        if (!empty($arr_return['DefaultErrorMsg']))
            $arr_log['Detail'] = $arr_return['DefaultErrorMsg'];
        $this->endJob($arr_log);
        if (!empty($arr_return['DefaultErrorMsg']))
            mydie($arr_return['DefaultErrorMsg']);
        return $arr_return;
    }

    function getPageLinks($aff_id, $strCheckDate = "")
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
        );

        if (!$strCheckDate) $strCheckDate = date("Y-m-d H:i:s", time() - 3600 * 12);

        //add log
        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => 0,
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);

        //get all merchants
        $arr_merchant = $this->getApprovalAffMerchant($aff_id);
        foreach ($arr_merchant as $AffMerchantId => $theMerchant) {
            if ($theMerchant['LastUpdateLinkTime'] >= $strCheckDate && !$this->ignorecheck) {
                print "Merchant " . $theMerchant["Name"] . " has been updated at " . $theMerchant['LastUpdateLinkTime'] . " but the check date is $strCheckDate \n";
                continue;
            }

            //for links
            $arr_one_mer_result = $this->GetAllLinksFromAffByMerID($aff_id, $theMerchant);
            if (isset($arr_one_mer_result["DefaultErrorMsg"])) {
                echo "warning:" . $arr_one_mer_result["DefaultErrorMsg"] . "\n";
                echo "*******Warning: No page links found for Aff($aff_id) <br>\n";
                break;
            }

            $arr_return["AffectedCount"] += $arr_one_mer_result["AffectedCount"];
            $arr_return["UpdatedCount"] += $arr_one_mer_result["UpdatedCount"];
        }

        $this->updateLinkInfoInAffTable($aff_id, $arr_return["AffectedCount"], "link");
        $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
        $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
        $this->endJob($arr_log);

        //if($this->debug)
        //{
        print __METHOD__ . " for aff $aff_id is finished. <br>\n";
        print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        //}

        return $arr_return;
    }

    function getCouponFeed($aff_id)
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "DefaultErrorMsg" => "Method " . __METHOD__ . " in LinkFeed Object(AffId=$aff_id) not found",
        );

        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'getCouponFeed')) {
            //add log
            $arr_log = array(
                "JobName" => __METHOD__,
                "AffId" => $aff_id,
                "MerchantId" => 0,
                "SiteId" => 0,
                "AffectedCount" => 0,
                "UpdatedCount" => 0,
                "Detail" => "",
            );
            $this->addJob($arr_log);

            $arr_return = $_obj->getCouponFeed();

//			$this->updateLinkInfoInAffTable($aff_id,$arr_return["AffectedCount"],"feed");
            if (isset($arr_return["Detail"])) {
                foreach ($arr_return["Detail"] as $AffMerchantId => $info) {
                    if (isset($info["AffectedCount"]) && is_numeric($info["AffectedCount"])) {
                        $this->updateLinkInfoInMerchantTable($aff_id, $AffMerchantId, $info["AffectedCount"], "feed");
                    }
                }
            }

            $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
            $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
            $this->endJob($arr_log);
        }

        if ($this->debug && !isset($arr_return["DefaultErrorMsg"])) {
            print __METHOD__ . " for aff $aff_id is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }

        return $arr_return;
    }

    function GetAllLinksFromAffByMerID($aff_id, $mer_info)
    {
        if (is_string($mer_info)) {
            $arr_temp = $this->getApprovalAffMerchantFromTask($aff_id, $mer_info);
            if (empty($arr_temp)) mydie("die:GetAllLinksFromAffByMerID failed, merchant id($mer_info) not found.\n");
            $mer_info = $arr_temp;
        }

        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "DefaultErrorMsg" => "Method " . __METHOD__ . " in LinkFeed Object(AffId=$aff_id) not found",
        );

        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'GetAllLinksFromAffByMerID')) {
            $arr_return = $_obj->GetAllLinksFromAffByMerID($mer_info);

            $this->updateLinkInfoInMerchantTable($aff_id, $mer_info["AffMerchantId"], $arr_return["AffectedCount"], "link");
        }

        if ($this->debug && !isset($arr_return["DefaultErrorMsg"])) {
            print __METHOD__ . " for aff $aff_id merchant " . $mer_info["AffMerchantId"] . " is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }

        return $arr_return;
    }
    
    function GetAllProduct($aff_id)
    {
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "DefaultErrorMsg" => "Method " . __METHOD__ . " in ProductFeed Object(AffId=$aff_id) not found",
        );
        
        //add log
        $arr_log = array(
            "JobName" => __METHOD__,
            "AffId" => $aff_id,
            "MerchantId" => 0,
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $this->addJob($arr_log);
        
        $strCheckDate = date("Y-m-d H:i:s", time() - 3600 * 12);
    
        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'getProductFeed')) {
            
            $arr_return = $_obj->getProductFeed();
    
            $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
            $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
            
            
        }elseif(method_exists($_obj, 'getProductFeedFromAffByMerID')){
            $arr_merchant = $this->getApprovalAffMerchant($aff_id);
            foreach ($arr_merchant as $AffMerchantId => $theMerchant) {
                if ($theMerchant['LastUpdateLinkTime'] >= $strCheckDate && !$this->ignorecheck) {
                    print "Merchant " . $theMerchant["Name"] . " has been updated at " . $theMerchant['LastUpdateLinkTime'] . " but the check date is $strCheckDate \n";
                    continue;
                }
            
                //for links
                $arr_one_mer_result = $this->GetAllProductsFromAffByMerID($aff_id, $theMerchant);
                if (isset($arr_one_mer_result["DefaultErrorMsg"])) {
                    echo "warning:" . $arr_one_mer_result["DefaultErrorMsg"] . "\n";
                    echo "*******Warning: No page links found for Aff($aff_id) <br>\n";
                    break;
                }
            
                $arr_return["AffectedCount"] += $arr_one_mer_result["AffectedCount"];
                $arr_return["UpdatedCount"] += $arr_one_mer_result["UpdatedCount"];
            }
            $arr_log["AffectedCount"] = $arr_return["AffectedCount"];
            $arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
        }
        $this->endJob($arr_log);
        if ($this->debug && !isset($arr_return["DefaultErrorMsg"])) {
            print __METHOD__ . " for aff $aff_id is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }
    
        return $arr_return;
    }

    function GetAllProductsFromAffByMerID($aff_id, $mer_info){
        
        if (is_string($mer_info)) {
            $arr_temp = $this->getApprovalAffMerchantFromTask($aff_id, $mer_info);
            if (empty($arr_temp)) mydie("die:GetAllProductsFromAffByMerID failed, merchant id($mer_info) not found.\n");
            $mer_info = $arr_temp;
        }
        
        $arr_return = array(
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "DefaultErrorMsg" => "Method " . __METHOD__ . " in ProductFeed Object(AffId=$aff_id) not found",
        );
        
        $_obj = $this->getInstance($aff_id);
        if (method_exists($_obj, 'getProductFeedFromAffByMerID')) {
            $arr_return = $_obj->getProductFeedFromAffByMerID($mer_info);
        }
        
        if ($this->debug && !isset($arr_return["DefaultErrorMsg"])) {
            print __METHOD__ . " for aff $aff_id merchant " . $mer_info["AffMerchantId"] . " is finished. <br>\n";
            print "here is the result: " . print_r($arr_return, true) . "<br>\n";
        }
        
        return $arr_return;
    }

    function GetAllTransaction($aff_id, $start_dt, $end_dt)
    {
        global $CURRENCY_HIS;
        $_obj = $this->getInstance($aff_id);

        preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $start_dt, $start_time);
        preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $end_dt, $end_time);
        $start_time = isset($start_time[0]) ? $start_time[0] : '';
        $end_time = isset($end_time[0]) ? $end_time[0] : '';
        $tmpStartTime = strtotime($start_time);
        $tmpEndTime = strtotime($end_time);

        if (!$start_time || !$end_time){
            $end_date   = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-90 days'));
        }elseif ($tmpStartTime > $tmpEndTime){
            mydie("Please input correct date! (start_date : $start_time > end_date : $end_time).");
        }else{
            $end_date   = $end_time;
            $start_date = $start_time;
        }

        define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");
        define('TRANSACTION_DATA_PATH', INCLUDE_ROOT . '/data/Transaction');
        define('AFF_TRANSACTION_DATA_PATH', TRANSACTION_DATA_PATH . "/" . $this->affiliates[$aff_id]['Alias']);

        if(!file_exists(TRANSACTION_DATA_PATH)){
            mkdir(TRANSACTION_DATA_PATH);
        }
        if(!file_exists(AFF_TRANSACTION_DATA_PATH)){
            mkdir(AFF_TRANSACTION_DATA_PATH);
        }

        $_obj->GetTransactionFromAff($start_date, $end_date);
    }
    
    function trimfortsv($s)
    {
        $s = str_replace(array("\n", "\r"), "", $s);
        $s = str_replace("\t", " ", $s);
        return $s;
    }

    //fixEnocding()方法用于转换linkfeed表某些字段的编码
    function fixEnocding(&$aff_id_or_info, &$arr, $forwhat)//$aff_id_or_info是affiliate表内所有字段的数组，$arr是$link数组
    {
        if (is_string($aff_id_or_info)) $arr_info = $this->getAffById($aff_id_or_info);
        else $arr_info = $aff_id_or_info;

        if ($forwhat == "merchant") $encoding_field = "AffMerchantEncoding";
        elseif ($forwhat == "feed") $encoding_field = "AffFeedEncoding";
        elseif ($forwhat == "link") $encoding_field = "AffLinkEncoding";
        else mydie("die: wrong fixEnocding para $forwhat \n");

        $to_encoding = "UTF-8";
        $from_encoding = "";
        if (isset($arr_info[$encoding_field]) && $arr_info[$encoding_field]) $from_encoding = $arr_info[$encoding_field];
        else return;

        $from_encoding = strtoupper($from_encoding);
        if ($from_encoding == $to_encoding) return;

        if ($forwhat == "merchant") $arrColNeedFix = array("MerchantName", "MerchantRemark");
        elseif ($forwhat == "feed" || $forwhat == "link") $arrColNeedFix = array("LinkName", "LinkDesc", "LinkHtmlCode");

        foreach ($arrColNeedFix as $col) {
            if (!isset($arr[$col])) continue;
            if ($arr[$col] == "") continue;
            $iconvres = @iconv($from_encoding, $to_encoding, $arr[$col]);
            if ($iconvres === false) {
                echo "warning: iconv failed for string: " . $arr[$col] . "\n";
                continue;
            }
            $arr[$col] = $iconvres;
        }
    }

    function GetAllProgram($aff_id)
    {
        $arr_log = array(
            "JobName" => __METHOD__,//魔术变量返回“类：：方法名”
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        $_obj = $this->getInstance($aff_id);                          //返回aff_id对应的类的对象，在文件夹LinkFeed中，一个类对应一个class文件
        $_obj->GetProgramFromAff($aff_id);
    }

    function GetAllStatus($aff_id)
    {
        $arr_log = array(
            "JobName" => __METHOD__,//魔术变量返回“类：：方法名”
            "AffId" => $aff_id,
            "MerchantId" => "",
            "SiteId" => 0,
            "AffectedCount" => 0,
            "UpdatedCount" => 0,
            "Detail" => "",
        );
        //$this->addJob($arr_log);
        $_obj = $this->getInstance($aff_id);                          //返回aff_id对应的类的对象，在文件夹LinkFeed中，一个类对应一个class文件
        $_obj->GetStatus($aff_id);
        //$this->endJob($arr_log);
    }

    function saveLogs($aff_id, $filename, $logs = array())
    {
        return true;
        /*$class_name = $this->getClassNameByAffID($aff_id);

        $f_dir = INCLUDE_ROOT . "logs/programlogs/";
        if(!is_dir($f_dir)){
            mkdir($f_dir, 0777);
        }
        $f_dir = $f_dir.$class_name."/";
        if(!is_dir($f_dir)){
            mkdir($f_dir, 0777);
        }

        if(!empty($filename) && count($logs)){
            $log_file = fopen($f_dir.$filename, 'a');
            if(count($logs)){
                $i = 0;
                foreach($logs as $v){
                    if($i == 0){
                        fputcsv($log_file, array_keys($v), "\t");
                    }
                    fputcsv($log_file, $v, "\t");
                    $i++;
                }
            }
            fclose($log_file);
        }*/
    }

    function findFinalUrl($url, $request_arr = array())
    {
        $return_url = "";
        if ($url) {
            $default_request = array("header" => 1, "nobody" => 1, "no_ssl_verifyhost" => 1, "maxredirs" => 15, "timeout" => 60);
            $default_request = array("FinalUrl" => 1);
            foreach ($request_arr as $k => $v) {
                if ($v == "unset") {
                    unset($default_request[$k]);
                } else {
                    $default_request[$k] = $v;
                }
            }
            $r = $this->GetHttpResult($url, $default_request);
            $header = $r["content"];
            return empty($header) ? $url : $header;
            // print_r($header);
            if (strlen($header < 1000)) {
                //find JS
                preg_match("/window\.location\.replace\((['|\"])([^\1\)]*)\1\)/si", $header, $matches);
                if (isset($matches[2]) && strlen($matches[2]) > 10) {
                    echo "\r\nfind JS redirect: {$matches[2]}\r\n";
                    $return_url = $matches[2];
                    /*$default_request["nobody"] = 1;
                    $r = $this->GetHttpResult($matches[2], $default_request);
                    $header = $r["content"];*/
                }
            }

            if (empty($return_url)) {
                preg_match_all("/Location:(.*)\r\n/i", $header, $matches);
                if (count($matches[1])) {
                    $i = 0;
                    while (count($matches[1])) {
                        $loc = array_pop($matches[1]);
                        if ($return_url = stristr($loc, "http")) {
                            $return_url = preg_replace("/[\"']+/i", "", $return_url);
                            break;
                        }
                        if ($i > 10) {
                            break;
                        }
                        $i++;
                    }
                }
            }

            if (empty($return_url)) {
                $return_url = $url;
            }
        }
        return $return_url;
    }
    
    
    //product img path
    function getProductImgFilePath($aff_id, $file_name, $productDir)
    {
        
        $group_name = 'product';
        if (isset($this->ProductWorkingdirs[$aff_id][$group_name])) {
            return $this->ProductWorkingdirs[$aff_id][$group_name].$file_name;
        }
         
        $is_mkdir = false;
         
        $dir = $productDir;//创建data文件夹
        if (!is_dir($dir)) {
            $is_mkdir = true;
            mkdir($dir);
            chmod($dir, 0777);
        }
        $dir .= $aff_id . "/";                                    //在data文件夹下，创建LinkFeed_10_AW等文件夹
        if (!is_dir($dir)) {
            $is_mkdir = true;
            mkdir($dir);
            chmod($dir, 0777);
        }
         
        if ($group_name) {
            $dir .= $group_name . "/";
            if (!is_dir($dir)) {
                $is_mkdir = true;
                mkdir($dir);
                chmod($dir, 0777);
            }
        }
        if ($is_mkdir && !is_dir($dir)) mydie("make ProductWorkingdirs Dir failed: $dir\n");
        $this->ProductWorkingdirs[$aff_id][$group_name] = $dir;
        return $dir . $file_name;
    }
    
    function downloadImg($imgUrl){
        $imgUrl =  str_replace(' ','%20',$imgUrl);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$imgUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET' );    
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');
        $file_content = curl_exec($ch);
        $response = curl_getinfo( $ch );
        curl_close($ch);
        
		if($response['http_code'] != 200){
		   return false;
		} 
        return $file_content;
    }
}//end class

