<?php
class MegaCrawler
{
	var $config = array(
			"ruleXmlFile" => "default.xml",
			"ruleXmlContent" => "",
			"ruleXmlObj" => "",
			"workingDir" => "./",
			"workingDirSession" => "",
			"workingDirSessionCache" => "",
			"workingDirSessionResult" => "",
			"workingDirSessionCacheIndexFile" => "",
			"stopmode" => 0,	//0:stop if any page failed 1:
			"debug" => 0,
		);
	var $rules = array(); //the rules from ruleXmlFile
	var $output = array();
	var $debug = 0;

	function __construct($_ruleXmlFile,$_config=array())
	{
		$this->config = array_merge($this->config, $_config);
		$this->config["ruleXmlFile"] = $_ruleXmlFile;

		//debug mode
		if($this->config["debug"]) $this->debug = 1;
		elseif(defined("DEBUG_MODE") && DEBUG_MODE) $this->debug = 1;
		else $this->debug = 1;
	}

	function initWorkingDir()
	{
		$session = "";
		if(isset($this->rules["pages"]["landingpage"]["attributes"]["url"]))
		{
			$landingpageurl = $this->rules["pages"]["landingpage"]["attributes"]["url"];
			$urlhost = parse_url($landingpageurl,PHP_URL_HOST);
			if($urlhost == "") throw new MegaException("wrong landing page url: $landingpageurl");
			$this->rules["siterooturl"] = "http://" . $urlhost;
			$session = $urlhost . "_" . date("YmdHis");
		}

		//we can set workingDirSession manually , this is usefull when we want to resume a session
		if($this->config["workingDirSession"] == "")
		{
			if(!is_dir($this->config["workingDir"]))
			{
				throw new MegaException("working dir(" . $this->config["workingDir"] . ") does not exists");
			}

			if(!is_writeable($this->config["workingDir"]))
			{
				throw new MegaException("cannot write to (" . $this->config["workingDir"] . ")");
			}

			if(substr($this->config["workingDir"],-1) != "/") $this->config["workingDir"] .= "/";
			$this->config["workingDirSession"] = $this->config["workingDir"] . "$session/";
		}
		else
		{
			if(substr($this->config["workingDirSession"],-1) != "/") $this->config["workingDirSession"] .= "/";
		}
		$this->config["workingDirSessionCache"] = $this->config["workingDirSession"] . "cache/";
		$this->config["workingDirSessionResult"] = $this->config["workingDirSession"];
		$this->config["workingDirSessionCacheIndexFile"] = $this->config["workingDirSession"] . "cache.ini";
		$this->config["workingDirSessionOutputFile"] = $this->config["workingDirSession"] . "out_" . date("YmdHis") . ".tsv";
		
		$tocheckdirlist = array($this->config["workingDirSession"],$this->config["workingDirSessionCache"],$this->config["workingDirSessionResult"]);
		foreach($tocheckdirlist as $dir)
		{
			@mkdir($dir);
			if(!is_dir($dir))
			{
				throw new MegaException("create dir failed: $dir");
			}
		}
		
		if($this->debug) $this->doLog("workingDirSession: " . $this->config["workingDirSession"]);
	}

	function getAllPageCacheIndex($_returnFormat=0)
	{
		$arrReturn = array();
		if(!is_file($this->config["workingDirSessionCacheIndexFile"])) return $arrReturn;
		$handle = @fopen($this->config["workingDirSessionCacheIndexFile"], "r");
		if ($handle)
		{
			while (!feof($handle))
			{
				$line = trim(fgets($handle));
				if($line == "") continue;
				list($cachefile,$name,$status,$datetime,$urlpattern,$url) = explode("\t",$line);
				if(!$urlpattern) continue;
				switch($_returnFormat)
				{
					case 1:
						//arrCacheIndex_2
						$arrReturn[$name][$cachefile]["status"] = $status;
						$arrReturn[$name][$cachefile]["datetime"] = $datetime;
						$arrReturn[$name][$cachefile]["urlpattern"] = $urlpattern;
						$arrReturn[$name][$cachefile]["url"] = $url;
						$arrReturn[$name][$cachefile]["cachefile"] = $cachefile;
						$arrReturn[$name][$cachefile]["name"] = $name;
						break;
					case 0:
					default:
						//arrCacheIndex_1
						$arrReturn[$cachefile]["name"] = $name;
						$arrReturn[$cachefile]["status"] = $status;
						$arrReturn[$cachefile]["datetime"] = $datetime;
						$arrReturn[$cachefile]["urlpattern"] = $urlpattern;
						$arrReturn[$cachefile]["url"] = $url;
						$arrReturn[$cachefile]["cachefile"] = $cachefile;
						break;
				}
				
		    }
			fclose($handle);
		}
		return $arrReturn;
	}

	function login()
	{
		if(!isset($this->rules["login"]["method"])) return;
		$this->rules["login"]["islogined"] = false;
		
		//$curlCookie = $this->config["workingDirSession"];
		//$this->rules["login"]["cookiejar"] = tempnam($curlCookie,'curl_cookie_');
		$this->rules["login"]["cookiejar"] = $this->config["workingDirSession"] . "curl_cookie";
		
		if($this->rules["login"]["method"] == "post")
		{
			$this->rules["pages"]["loginpage"]["status"] = $this->getRealPage($this->rules["login"]["loginurl"],"loginpage","login");
		}
		
		if(isset($this->rules["login"]["verifystring"]))
		{
			$content = $this->getHtmlContentByUrl($this->rules["login"]["loginurl"]);
			if(stripos($content, $this->rules["login"]["verifystring"]) === false)
			{
				throw new MegaException("login failed:  verifystring(" . $this->rules["login"]["verifystring"] . ") not found!\n");
			}
		}
		$this->rules["login"]["islogined"] = true;
		if($this->debug) $this->doLog("login successfully");
	}
	
	function getHtmlContentByUrl($_url)
	{
		$cachefile = $this->getCacheFileNameByUrl($_url);
		$cachefilr_fullpath = $this->config["workingDirSessionCache"] . $cachefile;
		if(!file_exists($cachefilr_fullpath)) return "";
		return file_get_contents($cachefilr_fullpath);
	}
	
	function getAllPages()
	{
		//first landing page
		$this->arrCacheIndex_1 = $this->getAllPageCacheIndex(0);
		//$this->arrCacheIndex_2 = $this->getAllPageCacheIndex(1);
		
		$this->login();
		
		$this->rules["pages"]["landingpage"]["status"] = $this->getPagesByName("landingpage");
		
		while(1)
		{
			$flagdone = 1;
			foreach($this->rules["pages"] as $pagename => $pagerule)
			{
				if(!isset($pagerule["status"]) || $pagerule["status"] == false)
				{
					$flagdone = 0;
					$this->rules["pages"][$pagename]["status"] = $this->getPagesByName($pagename);
				}
			}
			if($flagdone == 1) break;
		}
	}

	function getPagesByName($_pagename)
	{
		//if(isset($this->arrCacheIndex_2[$_pagename]))
		if(isset($this->rules["pages"][$_pagename]["attributes"]["url"]))
		{
			$url = $this->rules["pages"][$_pagename]["attributes"]["url"];
			return $this->getRealPage($url,$_pagename);
		}
		
		if(isset($this->rules["pages"][$_pagename]["attributes"]["urlpattern"]))
		{
			//default parent is landingpage
			$arrMatchedUrl = $this->getFollowingPageUrl($_pagename);
			foreach($arrMatchedUrl as $_url)
			{
				$this->getRealPage($_url,$_pagename);
			}
			return true;
		}
		
		return false;
	}
	
	function getFollowingPageUrl($_pagename)
	{
		$parentname = "landingpage";
		$urlpattern = "/" . $this->rules["pages"][$_pagename]["attributes"]["urlpattern"] . "/S";
		if(isset($this->rules["pages"][$_pagename]["attributes"]["parent"]))
		{
			$parentname = $this->rules["pages"][$_pagename]["attributes"]["parent"];
		}
		
		$arrCachedParentPage = $this->getCachedPagesByName($parentname);
		if(sizeof($arrCachedParentPage) == 0)
		{
			throw new MegaException("parent pages is not avalible: $parentname");
		}
		
		$arrMatchedUrl = array();
		foreach($arrCachedParentPage as $_parentpageinfo)
		{
			$parentUrl = $_parentpageinfo["url"];
			$xml = new DOMDocument();
			@$xml->loadHTMLFile($this->config["workingDirSessionCache"] . $_parentpageinfo["cachefile"]);
			$aList = $xml->getElementsByTagName("a");
			if($aList->length == 0) return $arrMatchedUrl;
			
			for($i=0;$i<$aList->length;$i++)
			{
				$theA = $aList->item($i);
				$href = $theA->getAttribute("href");
				if(empty($href) || $href == "")
				{
					continue;
				}
				
				if(@preg_match($urlpattern, $href))
				{
					$fixedHref = $this->fixRelativeUrl($href,$parentUrl);
					$arrMatchedUrl[$fixedHref] = $fixedHref;
				}
			}
		}
		
		return $arrMatchedUrl;
	}
	
	function fixRelativeUrl($_url,$parentUrl)
	{
		if(strtolower(substr($_url,0,4)) == "http") return $_url;
		//[scheme] => http
		//[host] => hostname
		//[user] => username
		//[pass] => password
		//[path] => /path
		//[query] => arg=value
		//[fragment] => anchor

		$arrUrlInfo = parse_url($parentUrl);
		
		if(substr($_url,0,1) == "/") return $arrUrlInfo["scheme"] . "://" . $arrUrlInfo["host"] .  $_url;
		if(substr($_url,0,3) == "../")
		{
			$_url = substr($_url,3);
			if(substr($arrUrlInfo["path"],-1) == "/") $arrUrlInfo["path"] .= "aaa";
			$path = dirname(dirname($arrUrlInfo["path"]));
			return $this->fixRelativeUrl($_url,$arrUrlInfo["scheme"] . "://" . $arrUrlInfo["host"] . $path . "/");
		}
		if(substr($_url,0,2) == "./") $_url = substr($_url,2);
		if(substr($arrUrlInfo["path"],-1) == "/") return $parentUrl . $_url;
		$path = dirname($arrUrlInfo["path"]);
		return $arrUrlInfo["scheme"] . "://" . $arrUrlInfo["host"] . $path . $_url;
	}
	
	function getCachedPagesByName($_pagename,$_only200=true)
	{
		$arrReturn = array();
		foreach($this->arrCacheIndex_1 as $cachefile => $cacheinfo)
		{
			$name = $cacheinfo["name"];
			if($_pagename == $name)
			{
				if($_only200)
				{
					if($cacheinfo["status"] == 200) $arrReturn[] = $cacheinfo;
				}
				else
				{
					$arrReturn[] = $cacheinfo;
				}
			} 
		}
		return $arrReturn;
	}
	
	function getCacheFileNameByUrl($_url)
	{
		return md5(trim(strtolower($_url)));
	}
	
	function doLog($_str)
	{
		echo date("Y-m-d H:i:s") . ": " . $_str . "\n";
	}
	
	function getRealPage($_url,$_pagename,$_tp="")
	{
		$cachefile = $this->getCacheFileNameByUrl($_url);
		if($this->debug) $this->doLog("getting page: $_pagename($cachefile:$_url)");
		if(isset($this->arrCacheIndex_1[$cachefile]) && $this->arrCacheIndex_1[$cachefile]["status"] == 200)
		{
			if($this->debug) $this->doLog("get page from cache: $_pagename($cachefile)");
			return true;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		if(isset($this->rules["login"]["cookiejar"]))
		{
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->rules["login"]["cookiejar"]);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->rules["login"]["cookiejar"]);
		}

		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
		
		if($_tp == "login")
		{
			if($this->rules["login"]["method"] == "post")
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->rules["login"]["loginpostdata"]);
			}
		}
		$pagecontent = curl_exec($ch);
		$curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($this->debug) $this->doLog("getting page finished: $_pagename($curl_code)");
		
		//write to cache index file
		$urlpattern = "";
		if(isset($this->rules["pages"][$_pagename]["attributes"]["urlpattern"]))
			$urlpattern = $this->rules["pages"][$_pagename]["attributes"]["urlpattern"];
		$info = array(
			"cachefile" => $cachefile,
			"name" => $_pagename,
			"status" => $curl_code,
			"datetime" => date("Y-m-d H:i:s"),
			"urlpattern" => $urlpattern,
			"url" => $_url,
		);

		$this->addToCacheIndexFile($info);
	    if($curl_code == 200)
		{
			$this->arrCacheIndex_1[$cachefile] = $info;
			file_put_contents($this->config["workingDirSessionCache"] . $cachefile,$pagecontent);
	    }
		elseif($curl_code == 404)
		{
			$this->arrCacheIndex_1[$cachefile] = $info;
			file_put_contents($this->config["workingDirSessionCache"] . $cachefile,$pagecontent);
	    }
		else{
			throw new MegaException("get page failed $_url : $curl_code");
			//return false;
		}
		return true;
	}

	function addToCacheIndexFile($_info)
	{
		//list($cachefile,$name,$status,$datetime,$urlpattern,$url) = explode("\t",$line);
		$newline = implode("\t",$_info) . "\n";
		error_log($newline, 3,$this->config["workingDirSessionCacheIndexFile"]);
	}
	
	function start()
	{
		$this->loadRuleXml();
		$this->initWorkingDir();
		$this->getAllPages();
		$this->output();
	}

	function parseField($_taginfo,$_node=null,$_level=0)
	{
		if($_node == null)
		{
			//top field level
			$frompagename = $_taginfo["attributes"]["from"];
			$arrFromPageName = explode(",",$frompagename);
			foreach($arrFromPageName as $frompagename)
			{
				$arrCachedFromPage = $this->getCachedPagesByName($frompagename);
				if(sizeof($arrCachedFromPage) == 0)
				{
					throw new MegaException("from pages is not avalible: frompagename");
				}
				
				if(!isset($_taginfo["childNodes"]) || !is_array($_taginfo["childNodes"]))
				{
					throw new MegaException("childNodes for field " . $_taginfo["attributes"]["name"] . " not found");
				}
				
				foreach($arrCachedFromPage as $_pageinfo)
				{
					$xml = new DOMDocument();
					@$xml->loadHTMLFile($this->config["workingDirSessionCache"] . $_pageinfo["cachefile"]);
					foreach($_taginfo["childNodes"] as $_childTag)
					{
						$this->parseField($_childTag,$xml,$_level+1);
					}
				}
			}
			return;
		}
		
		$tagname = $_taginfo["nodeName"];
		$outputfieldindex = $_taginfo["outputfieldindex"];
		if(!(is_a($_node,'DOMDocument') || is_a($_node,'DOMNode'))) return;
		$tagList = $_node->getElementsByTagName($tagname);
		if($tagList->length == 0) return;
		for($i=0;$i<$tagList->length;$i++)
		{
			$theItem = $tagList->item($i);
			$bMatched = 1;
			foreach($_taginfo["attributes"] as $_attrName => $attrValue)
			{
				if(strtolower($_attrName) == "output") continue;

				//try to match attrs
				if(preg_match("/(.*)pattern$/i", $_attrName, $matches))
				{
					$itemAttrName = $matches[1];
					$itemAttrValue = $theItem->getAttribute($itemAttrName);
					if(!preg_match("/$attrValue/", $itemAttrValue))
					{
						$bMatched = 0;
						break;
					}
					//if(strtolower(substr($_attrName,-7)) == "pattern") continue;
				}
				else
				{
					$itemAttrName = $_attrName;
					$itemAttrValue = $theItem->getAttribute($itemAttrName);
					
					if (strcasecmp($itemAttrValue, $attrValue) != 0)
					{
						$bMatched = 0;
						break;
					}
				}
			}
			
			//here: the tag is matched.
			if(!$bMatched) continue;

			if(isset($_taginfo["attributes"]["output"]) && $_taginfo["attributes"]["output"])
			{
				$outputwhat = $_taginfo["attributes"]["output"];
				if($outputwhat == "innertext")
				{
					$this->output[$outputfieldindex][] = $theItem->nodeValue;
				}
				elseif($outputwhat == "src" || $outputwhat == "alt" || $outputwhat == "title" || $outputwhat == "href" || $outputwhat == "value")
				{
					$this->output[$outputfieldindex][] = $theItem->getAttribute($outputwhat);
				}
			}
			
			foreach($_taginfo["childNodes"] as $_childTag)
			{
				$this->parseField($_childTag,$theItem,$_level+1);
			}

		}
	}
	
	function output()
	{
		$outputfile = $this->config["workingDirSessionOutputFile"];
		$handle_output = fopen($outputfile, 'a');
		if(!$handle_output)
		{
			throw new MegaException("Cannot open file ($outputfile)");
		}
		$this->config["workingDirSessionOutputFileHandle"] = $handle_output;
		
		foreach($this->rules["output"] as $_index => $_taginfo)
		{
			if(!isset($_taginfo["attributes"]["from"]) || $_taginfo["attributes"]["from"] == "")
			{
				@$fieldname = isset($_taginfo["attributes"]["name"])?$_taginfo["attributes"]["name"]:"";
				throw new MegaException("field ($fieldname): from page not defined");
			}
			
			$this->parseField($_taginfo);
		}
		
		$fieldcount = sizeof($this->output);
		if($fieldcount > 0)
		{
			$maxline = 0;
			foreach($this->output as $_fieldContent)
			{
				if(sizeof($_fieldContent) > $maxline)
				{
					$maxline = sizeof($_fieldContent);
				}
			}
			
			for($i=0;$i<$maxline;$i++)
			{
				$cols = array();
				for($j=0;$j<$fieldcount;$j++)
				{
					if(isset($this->output[$j][$i]))
					{
						$field = $this->output[$j][$i];
						$field = str_replace("\t", " ", $field);
						$field = str_replace(array("\n","\r"), "", $field);
					}
					else
					{
						$field = "";
					}
					
					$cols[] = $field;
				}
				
				$line = implode("\t",$cols) . "\n";
				if(fwrite($handle_output, $line) === FALSE)
				{
		        	throw new MegaException("Cannot write to file ($outputfile)");
	    		}
			}
		}

		fclose($handle_output);
    }

   	function loadRuleXml()
	{
		$xmlfile = $this->config["ruleXmlFile"];
		if(!is_file($xmlfile)) throw new MegaException("unable to open file ($xmlfile)");

		$xml = new DOMDocument();
		$xml->load($xmlfile);

		//root
		$rootnodelist = $xml->getElementsByTagName("crawlerconfig");
		if($rootnodelist->length == 0) throw new MegaException("root node crawlerconfig not found in ruleXmlFile");
		$rootnode = $rootnodelist->item(0);

		//landingpage
		$landingpagelist = $rootnode->getElementsByTagName("landingpage");
		if($landingpagelist->length == 0) throw new MegaException("node landingpage not found in ruleXmlFile");
		$landingpage = $landingpagelist->item(0);
		$this->rules["pages"]["landingpage"]["attributes"] = array();
		$tmpArr = &$this->rules["pages"]["landingpage"]["attributes"];
		foreach($landingpage->attributes as $attr)
		{
			$tmpArr[$attr->nodeName] = $attr->nodeValue;
		}
		
		if(!isset($tmpArr["url"])) throw new MegaException("landingpage url is needed in ruleXmlFile");
		
		//followingpage (optional)
		$pagenameindex = 0;
		$followingpagelist = $rootnode->getElementsByTagName("followingpage");
		for($i=0;$i<$followingpagelist->length;$i++)
		{
			$thepage = $followingpagelist->item($i);
			$pagename = $thepage->getAttribute("name");
			if(empty($pagename))
			{
				$pagename = "autopage_" . ($pagenameindex++);
			}
			
			$this->rules["pages"][$pagename]["attributes"] = array();
			$tmpArr = &$this->rules["pages"][$pagename]["attributes"];
			
			foreach($thepage->attributes as $attr)
			{
				$tmpArr[$attr->nodeName] = $attr->nodeValue;
			}
			
			if(!isset($tmpArr["urlpattern"])) throw new MegaException("urlpattern is needed for followingpage($pagename) in ruleXmlFile");
			if(!isset($tmpArr["parent"])) $tmpArr["parent"] = "landingpage";
		}
		
		//output
		$outputlist = $rootnode->getElementsByTagName("output");
		$fieldcount = 0;
		if($outputlist->length == 0) throw new MegaException("output node not found in ruleXmlFile");
		for($i=0;$i<$outputlist->length;$i++)
		{
			$theoutput = $outputlist->item($i);
			$fieldlist = $theoutput->getElementsByTagName("field");
			for($j=0;$j<$fieldlist->length;$j++)
			{
				$thefield = $fieldlist->item($j);
				$this->rules["output"][$fieldcount] = $this->parseRuleOutputField($thefield);
				$fieldcount++;
			}
		}
		
		if($fieldcount == 0) throw new MegaException("output field not defined.");
		
		//login
		$loginlist = $rootnode->getElementsByTagName("login");
		if($loginlist->length > 0)
		{
			for($i=0;$i<$loginlist->length;$i++)
			{
				$logininfo = $loginlist->item($i);
				$method = $logininfo->getElementsByTagName("method")->item(0)->nodeValue;
				switch($method)
				{
					case "post":
						$loginurl = $this->getNodeFirstValue($logininfo,"loginurl");
						$loginpostdata = $this->getNodeFirstValue($logininfo,"loginpostdata");
						$verifystring = $this->getNodeFirstValue($logininfo,"verifystring");
						$this->rules["login"]["method"] = $method;
						$this->rules["login"]["loginurl"] = $loginurl;
						$this->rules["login"]["loginpostdata"] = $loginpostdata;
						if($verifystring) $this->rules["login"]["verifystring"] = $verifystring;
						break;
				}
			}
		}
	}
	
	function getNodeFirstValue($node,$nodename)
	{
		$list = $node->getElementsByTagName($nodename);
		if($list->length == 0) return "";
		$item = $list->item(0);
		return $item->nodeValue;
	}
	
	function parseRuleOutputField($_node,$_level=0)
	{
		$arrReturn = array(
			"attributes" => array(),
			"childNodes" => array(),
			"nodeName" => $_node->nodeName,#to match html tag
			"nodeValue" => $_node->nodeValue,
			"outputfieldindex" => -1,
		);
		
		if($_node->hasAttributes()) 
		{
			foreach($_node->attributes as $attr)
			{
				$arrReturn["attributes"][$attr->nodeName] = $attr->nodeValue;
				if($attr->nodeName == "output")
				{
					$arrReturn["outputfieldindex"] = sizeof($this->output);
					$this->output[] = array();
				}
			}
		}
		
		if(!$_node->hasChildNodes())
		{
			//$arrReturn["innertext"] = $_node->nodeValue;
			return $arrReturn;
		}
		
		$childNodesCount = 0;
		foreach($_node->childNodes as $childNode)
		{
			if(substr($childNode->nodeName,0,1) == "#") continue;
			$arrReturn["childNodes"][$childNodesCount++] = $this->parseRuleOutputField($childNode,$_level + 1);
		}
		return $arrReturn;
	}

}//end class

?>