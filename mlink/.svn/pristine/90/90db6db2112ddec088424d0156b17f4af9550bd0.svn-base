<?php
class TaskEmail extends Task
{
	function __construct($objMysql=null)
	{
		parent::__construct($objMysql);
		$this->strPrefixEmail = '@';
		$this->strPrefixContent = '*^_^*';
		$this->mail_dir = INCLUDE_ROOT . "mails/";
	}

	function getEmailInfoByUniqueId($UniqueId)
	{
		$sql = "select * from email_list where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		return $this->objMysql->getFirstRow($sql);
	}
	
	function getAssignedInfoByUniqueId($UniqueId,$site="")
	{
		$sql = "select A.*,B.EmailTitle,B.EmailSenderAddr,B.EmailSendTime from email_assigned A,email_list B where A.EmailUniqueID = B.EmailUniqueID and A.EmailUniqueID = '" . addslashes($UniqueId) . "'";
		if($site)
		{
			//for safe
			$sql .= " and A.SiteName = '" . addslashes($site) . "'";
		}
		return $this->objMysql->getFirstRow($sql);
	}
	
	function updateEmailStatusByUniqueId($UniqueId,$newstatus,$oldstatus="")
	{
		$sql = "update email_list set LastUpdateTime = '" . date("Y-m-d H:i:s") . "',EmailStatus = '" . addslashes($newstatus) . "' where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		if($oldstatus) $sql .= " and EmailStatus = '" . addslashes($oldstatus) . "'";
		return $this->objMysql->query($sql);
	}
	
	function updateEmailCommentByUniqueId($UniqueId,$comment)
	{
		$sql = "update email_list set LastUpdateTime = '" . date("Y-m-d H:i:s") . "',EmailComment = '" . addslashes($comment) . "' where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		return $this->objMysql->query($sql);
	}
	
	function updateEmailSaveFileByEmailId($EmailId)
	{
		$sql = "update email_list set EmailSaveFile = 1 where EmailId = '" . addslashes($EmailId) . "'";
		return $this->objMysql->query($sql);
	}

	function updateEmailUidByUniqueId($UniqueId,$newmailbox,$newuid)
	{
		$sql = "update email_list set LastUpdateTime = '" . date("Y-m-d H:i:s") . "',HistoryMailBox = '" . addslashes($newmailbox) . "',UIDInHistoryFolder = '" . addslashes($newuid) . "' where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		return $this->objMysql->query($sql);
	}
	
	function getRecipientsViaHeader(&$oMailHeader)
	{
		$arr = array();
		if(isset($oMailHeader->toaddress)) $arr[] = $oMailHeader->toaddress;
		if(isset($oMailHeader->ccaddress)) $arr[] = $oMailHeader->ccaddress;
		if(isset($oMailHeader->bccaddress)) $arr[] = $oMailHeader->bccaddress;
		return implode(";",$arr);
	}
	
	function addEmail($oMailHeader,$uniqueMd5Id,$historyUid,$status="",$mailbox="INBOX.History")
	{
		if(!$status) $status = "NEW";
		$status = addslashes($status);
		$subject = addslashes($oMailHeader->subject);
		$senderaddress = addslashes($oMailHeader->from[0]->mailbox . "@" .$oMailHeader->from[0]->host);//$objmailheader->senderaddress;
		$addDate = date("Y-m-d H:i:s");
		
		$sendDateStr = addslashes($oMailHeader->date);
		$sendDate = $this->getDateTimeByMailDate($oMailHeader->date);
		
		$EmailCharset = isset($oMailHeader->charset) ? $oMailHeader->charset : "";
		$EmailCharset = addslashes($EmailCharset);
		
		$EmailRecipients = addslashes($this->getRecipientsViaHeader($oMailHeader));
		
		$forced_rule_type = $this->getForcedRuleType($oMailHeader);
		
		$sql = "insert into email_list(EmailId,EmailUniqueID,EmailTitle,EmailSenderAddr,EmailSendTime,EmailSendTimeStr,HistoryMailBox,UIDInHistoryFolder,EmailStatus,EmailAddTime,EmailCharset,EmailSaveFile,LastUpdateTime,Flag,EmailRecipients) values(null,'$uniqueMd5Id','$subject','$senderaddress','$sendDate','$sendDateStr','" . addslashes($mailbox) . "','$historyUid','$status','$addDate','$EmailCharset','0','$addDate','','$EmailRecipients')";
		$this->objMysql->query($sql);
		$email_id = $this->objMysql->getLastInsertId();
		if(!is_numeric($email_id)) return false;
		
		if($forced_rule_type != "BD")
		{
			$con_status = 'NEW';
			$sql = "SELECT * FROM email_list WHERE EmailTitle = '$subject' AND EmailSenderAddr = '$senderaddress' AND EmailSendTime > '".date('Y-m-d H:i:s',strtotime("-1 days"))."'";
			$same_email = $this->objMysql->getRows($sql);
			if(count($same_email)>1){
				$con_status = 'IGNORED';
			}
			$sql = "insert into `email_content_list` (`ID`, `EmailId`, `EmailUniqueId`, `Site`, `MerchantID`, `MerchantName`, `Resolver`, `IsNewMerchant`, `Status`, `AddTime`, `LastUpdateTime`) values (null, '$email_id', '$uniqueMd5Id', '', '0', '', '', 'NO', '{$con_status}', now(), now());";
			$this->objMysql->query($sql);
		}
		
		$bd_ignore = false;
		if(!$bd_ignore && stripos($EmailRecipients,"int.content.notify@megainformationtech.com") != false)
		{
			$bd_ignore = true;
		}
		
		if(!$bd_ignore && stripos($senderaddress,"@megainformationtech.com") != false)
		{
			$bd_ignore = true;
		}
		
		if($forced_rule_type != "CONTENT" && $bd_ignore == false)
		{
			$IsTrustedEmailAddress = $this->isTrustedEmailAddress($senderaddress);
			$IsTrustedEmailAddress = $IsTrustedEmailAddress ? "YES" : "NO";
			
			$sql = "select taskid from task_bd_email where EmailTitle = '$subject' and EmailSenderAddr = '$senderaddress' and TaskAddTime > '".date("Y-m-d H:i:s", strtotime("-1 hour"))."' limit 1";
			$tmp_arr = array();
			$tmp_arr = $this->objMysql->getFirstRow($sql);
			if(!count($tmp_arr)){			
				$sql = "insert into `task_bd_email`(`TaskId`, `EmailId`, `EmailUniqueId`, `Site`, `MerchantID`, `MerchantName`, `Resolver`, `TaskStatus`, `IsNewMerchant`, `TaskAddTime`, `LastUpdateTime`,EmailTitle,EmailSenderAddr,EmailSendTime,IsTrustedEmailAddress,Rank,EmailRecipients)
	values (null, '$email_id', '$uniqueMd5Id', '', '0', '', '', 'NEW', 'NO', now(), now(),'$subject','$senderaddress','$sendDate','$IsTrustedEmailAddress',0,'$EmailRecipients')";
				$this->objMysql->query($sql);
				$task_id = $this->objMysql->getLastInsertId();
				if(is_numeric($task_id) && $task_id > 0)
				{
					$rank = ($IsTrustedEmailAddress == "YES") ? $task_id + 50000 : $task_id;
					$sql = "update task_bd_email set Rank = '$rank' where TaskId = '$task_id'";
					$this->objMysql->query($sql);
				}
			}
		}
		
		return $email_id;
	}
	
	function isTrustedEmailAddress($address)
	{
		$address = trim(strtolower($address));
		if(!isset($this->arrTrustedEmailAddress)) $this->arrTrustedEmailAddress = array();
		if(isset($this->arrTrustedEmailAddress[$address])) return true;
		$sql = "select EmailAddress from email_trusted_address where EmailAddress = '" . addslashes($address) . "'";
		$IsTrustedEmailAddress = $this->objMysql->getFirstRowColumn($sql);
		if($IsTrustedEmailAddress)
		{
			$this->arrTrustedEmailAddress[$address] = 1;
			return true;
		}
		return false;
	}
	
	function getDateTimeByMailDate($str)
	{
		if(!is_string($str)) $str = "";
		if(preg_match("/(.*) UT$/",$str,$matches)) $str = $matches[1] . " +0000";
		elseif(preg_match("/(.*) ([0-9]{4})$/",$str,$matches)) $str = $matches[1] . " +" . $matches[2];
		elseif(preg_match("/(.*) \\((.*)\\)$/",$str,$matches)) $str = $matches[1];
		
		$timestamp = @strtotime($str);
		if($timestamp !== false && is_numeric($timestamp)) $str_date = date("Y-m-d H:i:s",$timestamp);
		else $str_date = "0000-00-00 00:00:00";
		return $str_date;
	}
	
	function delEmailByUniqueId($uniqueMd5Id)
	{
		$sql = "delete from email_list where EmailStatus = 'LOST' and EmailUniqueID = '" . addslashes($uniqueMd5Id) . "'";
		$this->objMysql->query($sql);
	}

	function loadOneSiteEmailRule($site,&$arrMerEditor,&$arrRules)
	{
		$strPrefixEmail = $this->strPrefixEmail;
		$strPrefixContent = $this->strPrefixContent;

		$objMysql = $this->getSiteMysqlObj($site);
		
		$sql = "select A.ID,A.Name,B.AssignedEditor from normalmerchant A,normalmerchant_addinfo B where A.ID = B.ID";
		$all_merchant = $objMysql->getRows($sql,"ID");
		
		//get match rules
		$sql = "SELECT * FROM email_matchrule where SiteName = '" . addslashes($site) . "'";
		$qry = $this->objMysql->query($sql);
	
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['MerchantId'];
			if($mer_id > 0 && !isset($all_merchant[$mer_id])) continue;
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => $row["RuleType"]);
			
			//Email1 Content1 	Email2 	Content2 	Email3 	Content3 	Email4 	Content4 
			$key_mail = "Email1";
			$key_content = "Content1";
			
			$row[$key_mail] = trim($row[$key_mail]);
			$row[$key_content] = trim($row[$key_content]);
			//skip empty rule
			if($row[$key_mail] != "" || $row[$key_content] != "")
			{
				$rule_mail = ($row[$key_mail] == "") ? $strPrefixEmail : $row[$key_mail];
				$rule_content = ($row[$key_content] == "") ? $strPrefixContent : $row[$key_content];
				$rule_content = stripslashes($rule_content);
				$arrRules[$rule_mail][$rule_content][$full_mer_id] = $arr_mer_id;
			}
		}
		
		//get common rules for CJ	//01
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 1 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			$strEmail = 'cj.com';
			$strContent = "/advertiserid=" . $row['MerIDinAff'] . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if($host)
			{
				$strContent = "/\\b" . preg_quote($host) . "\\b/i";
				$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			}
		}
	
		//get common rule for LS	//02
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 2 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			//http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=116095&mid=3178
			$strEmail = $this->strPrefixEmail;//all email
			list($real_aff_mer_id,$null) = explode("_",$row['MerIDinAff']);
			$strContent = "/advertiser_detail\\.php\\?oid=[0-9]+&mid=$real_aff_mer_id\\b/";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
			
			$strEmail = $host;
			$strContent = 'LinkShare';
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
	
			$strContent = $strEmail;
			$strEmail = 'linkshare.com';
			
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rule for GAN	//03
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 3 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
			
			$strEmail = '@google.com';
			$strContent = "/\\b" . preg_quote($host) . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rules for PJN	//06
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 6 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			$strEmail = 'pepperjamnetwork.com';
			$strContent = "/(pid|programId)=" . $row['MerIDinAff'] . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
			
			$strContent = "/\\b" . preg_quote($host) . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rule for SAS //07
		//$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 7 and A.IsUsing = 1";
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl,C.Name as AffMerName FROM wf_mer_in_aff A,normalmerchant B,aff_mer_list C WHERE A.MerID = B.ID and A.AffID in (7) and C.AffID = A.AffID and C.ID = A.MerIDinAff and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			if($row['AffMerName'])
			{
				$strEmail = $this->strPrefixEmail;
				$strContent = "your Merchant Partners, " . $row['AffMerName'] . ",";
				$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			}
			
			if(!$OriginalUrl) continue;
		
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
			
			$strEmail = $host;
			$strContent = 'shareasale.com';
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rules for AVANT	//08
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 8 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			$strEmail = 'avantlink.com';
			$strContent = "/mid=" . $row['MerIDinAff'] . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			
			if(!$OriginalUrl) continue;
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
			
			$strContent = "/\\b" . preg_quote($host) . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rules for AW //10
		$sql = "SELECT B.ID,A.MerIDinAff,C.Name as AffMerName FROM wf_mer_in_aff A,normalmerchant B,aff_mer_list C WHERE A.MerID = B.ID and A.AffID in (10) and C.AffID = A.AffID and C.ID = A.MerIDinAff and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			$strEmail = $this->strPrefixEmail;
			$strContent = "/&id=80151&merchant_id=" . $row['MerIDinAff'] . "\\b/i";
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
		
		//get common rules for webgain //13,14,18,34
		//You have received this message because you are a member of the Rowland's Clothing affiliate program
		$sql = "SELECT B.ID,A.MerIDinAff,B.OriginalUrl FROM wf_mer_in_aff A,normalmerchant B WHERE A.MerID = B.ID and A.AffID = 10 and A.IsUsing = 1";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			if($row['AffMerName'])
			{
				$strEmail = 'webgains';
				$strContent = "You have received this message because you are a member of the " . $row['AffMerName'] . " program";
				$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
			}
		}
		
		//for all
		$sql = "SELECT A.ID,A.OriginalUrl FROM normalmerchant A WHERE A.OriginalUrl <> ''";
		$qry = $objMysql->query($sql);
		while($row = mysql_fetch_array($qry))
		{
			$mer_id = $row['ID'];
			$OriginalUrl = trim($row['OriginalUrl']);
			$full_mer_id = "$site\t$mer_id";
			$arr_mer_id = array("site" => $site,"mer_id" => $mer_id,"rule_type" => "BDANDCONTENT");
			
			$mer_name = isset($all_merchant[$mer_id]["Name"]) ? $all_merchant[$mer_id]["Name"] : "";
			$AssignedEditor = isset($all_merchant[$mer_id]["AssignedEditor"]) ? $all_merchant[$mer_id]["AssignedEditor"] : "";
			
			$arrMerEditor[$site][$mer_id] = array("AssignedEditor" => $AssignedEditor,"mer_name" => $mer_name);
			
			if(!$OriginalUrl) continue;
			$host = $this->getDomainByOrigUrl($OriginalUrl);
			if(!$host) continue;
		
			$strContent = $strEmail = $host;
			$arrRules[$strEmail][$strContent][$full_mer_id] = $arr_mer_id;
		}
	}
	
	function getSiteMerchantInfoById($site,$merid)
	{
		$sql = "SELECT A.ID,A.Name,A.OriginalUrl,B.AssignedEditor,B.AllowNonAffPromo, B.Grade FROM normalmerchant A,normalmerchant_addinfo B WHERE A.ID = B.ID and A.ID = '$merid'";
		$objMysql = $this->getSiteMysqlObj($site);
		return $objMysql->getFirstRow($sql);
	}
	
	function getForcedRuleType($objmailheader)
	{
		if(isset($objmailheader->to[0]->mailbox) && strtolower($objmailheader->to[0]->host) == "megainformationtech.com")
		{
			//promotions@megainformationtech.com,promotionsuk@megainformationtech.com,promotionsau@megainformationtech.com 
			if(stripos($objmailheader->to[0]->mailbox,"promotion") !== false)
			{
				return "CONTENT";
			}
		}
		return "";
	}
	
	function isSPAMemail($objmailheader)
	{
		//check email address
		foreach($this->NormalEmailAddress as $strHost => $arrMailBox)
		{
			if(strtolower($objmailheader->to[0]->host) == $strHost)
			{
				$bMatched = false;
				foreach($arrMailBox as $strMailBox)
				{
					if(strtolower($objmailheader->to[0]->mailbox) == $strMailBox)
					{
						$bMatched = true;
						break;
					}
				}
				if(!$bMatched) return true;
			}
		}
		return false;
	}

	function getDomainByOrigUrl($url)
	{
		if(!$url) return "";
		$host = @parse_url($url,PHP_URL_HOST);
		if(!$host) return "";
		$host = str_ireplace("www.","",$host);
		return $host;
	}
	
	function getOneRulePreMatchResultArr(&$mer_info,&$arrMerEditor,$rule_mail,$rule_content,$forced_rule_type="")
	{
		$site = $mer_info["site"];
		$mer_id = $mer_info["mer_id"];
		$rule_type = isset($mer_info["rule_type"]) ? $mer_info["rule_type"] : "BDANDCONTENT";
		if(empty($rule_type)) $rule_type = "BDANDCONTENT";
		if($forced_rule_type) $rule_type = $forced_rule_type;
		
		$mer_name = $mer_editor = "";
		if(isset($arrMerEditor[$site][$mer_id]))
		{
			$mer_name = $arrMerEditor[$site][$mer_id]["mer_name"];
			$mer_editor = $arrMerEditor[$site][$mer_id]["AssignedEditor"];
		}

		if($rule_mail == $this->strPrefixEmail) $rule_mail = "[All Email Address]";
		if($rule_content == $this->strPrefixContent) $rule_content = "[All Email Content]";
		$arrInfo = array();
		$arrInfo[] = "From:" . $rule_mail . ";";
		$arrInfo[] = "MailContent:" . $rule_content . ";";
		$arrInfo[] = "MerchantEditor:" . $mer_editor . ";";
		
		return array(
			"SiteName" => $site,
			"MerchantID" => $mer_id,
			"MerchantName" => $mer_name,
			"MatchedRuleInfo" => implode("\n",$arrInfo),
			"RuleType" => $rule_type,
		);
	}
	
	function prematch(&$dbmail,&$oMailHeader,&$arrMailBody,&$arrRules,&$arrMerEditor)
	{
		$arr_matched = array();
		
		$forced_rule_type = $this->getForcedRuleType($oMailHeader);
		
		if($this->isSPAMemail($oMailHeader))
		{
			$arr_matched["\t-1"] = array(
				"SiteName" => "",
				"MerchantID" => "-1",//Junk mail
				"MerchantName" => "",
				"MatchedRuleInfo" => "Junk Mail Rule",
				"RuleType" => $forced_rule_type ? $forced_rule_type : "BDANDCONTENT",
			);
			return $arr_matched;
		}
		
		$restrict_site = array();
		/*
		if(!is_array($oMailHeader->to))
		{
			echo "warning: to address not found!\n";
			print_r($oMailHeader);
		}
		else
		{
			foreach($oMailHeader->to as $to)
			{
				$to_host = strtolower($to->host);
				if(isset($this->MailDomainToSite[$to_host]))
				{
					$site = $this->MailDomainToSite[$to_host];
					$restrict_site[$site] = $site;
				}
			}
		}
		
		if(empty($restrict_site))
		{
			//preform all site
			foreach($this->MailDomainToSite as $site)
			{
				$restrict_site[$site] = $site;
			}
		}
		else
		{
			foreach($restrict_site as $site)
			{
				$arr_matched[$site . "\t" . "0"] = array(
					"SiteName" => $site,
					"MerchantID" => "0",//restrict site
					"MerchantName" => "",
					"MatchedRuleInfo" => "to " . $oMailHeader->toaddress,
				);
			}
		}
		*/
		
		//here ,we always perform all sites
		foreach($this->MailDomainToSite as $site)
		{
			$restrict_site[$site] = $site;
		}
		
		$strEmailAddress = "";
		if(isset($oMailHeader->from[0]))
		{
			$strEmailAddress .= $oMailHeader->from[0]->mailbox.'@'.$oMailHeader->from[0]->host . "\n";
		}
		
		if(isset($oMailHeader->fromaddress))
		{
			$strEmailAddress .= $oMailHeader->fromaddress . "\n";
		}
		//$strEmailAddress .= $oMailHeader->from[0]->mailbox.'@'.$oMailHeader->from[0]->host . "\n";
		
		$strContent = $oMailHeader->subject . "\n" . $arrMailBody["htmlmsg"] . "\n" . $arrMailBody["plainmsg"] . "\n" . html_entity_decode(strip_tags($arrMailBody["htmlmsg"]));
		$strContent = str_ireplace("=3D", "=", $strContent);
		
		foreach($arrRules as $rule_mail => $arr_rule_content)
		{
			//email not matched
			if(stripos($strEmailAddress, $rule_mail) === false) continue;

			foreach($arr_rule_content as $rule_content => $matched_merchants)
			{
				if($this->isContentMatched($rule_content,$strContent))
				{
					foreach($matched_merchants as $full_mer_id => $mer_info)
					{
						$site = $mer_info["site"];
						$mer_id = $mer_info["mer_id"];
						if(!isset($restrict_site[$site])) continue;
						$arr_matched[$full_mer_id] = $this->getOneRulePreMatchResultArr($mer_info,$arrMerEditor,$rule_mail,$rule_content,$forced_rule_type);
					}//foreach
				}//if
			}//foreach
		}//foreach
		return $arr_matched;
	}//function
	
	function isContentMatched(&$rule_content,&$strContent)
	{
		$rule_content = trim($rule_content);
		if($rule_content == "") return false;
		if($rule_content == $this->strPrefixContent) return true;
		
		if(substr($rule_content,0,1) == "/" && (substr($rule_content,-2) == "/i" || substr($rule_content,-1) == "/"))
		{
			return @preg_match($rule_content,$strContent);
		}
		else
		{
			return (stripos($strContent,$rule_content) !== false);
		}
	}
	
	function assignMerchant($_arr)
	{
		$sql = "REPLACE INTO email_assigned(EmailUniqueID, SiteName, MerchantID, MerchantName, MerchantEditor, Editor, LastUpdateTime) VALUES('".addslashes($_arr["EmailUniqueID"])."', '".addslashes($_arr["SiteName"])."','".addslashes($_arr["MerchantID"])."','".addslashes($_arr["MerchantName"])."','".addslashes($_arr["MerchantEditor"])."','".addslashes($this->getAuthUser())."','".date("Y-m-d H:i:s")."')";
		$this->objMysql->query($sql);
	}
	
	function deleteAssignMerchant($UniqueId)
	{
		$sql = "DELETE from email_assigned where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		$this->objMysql->query($sql);
	}
	
	function getEmailAssignedInfoByUniqueId($UniqueId)
	{
		$sql = "select * from email_assigned where EmailUniqueID = '" . addslashes($UniqueId) . "'";
		return $this->objMysql->getFirstRow($sql);
	}
	
	function createContentTask($_arr)
	{
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO task_content(TaskId,ResourceId,ResourceType,SiteName,MerchantId,Resolver,TaskStatus,TaskAddTime,LastUpdateTime) VALUES (null,'".addslashes($_arr["ResourceId"])."','".addslashes($_arr["ResourceType"])."','".addslashes($_arr["SiteName"])."','".addslashes($_arr["MerchantId"])."','".addslashes($_arr["Resolver"])."','NEW','$now','$now')";
		$this->objMysql->query($sql);
		return $this->objMysql->getLastInsertId();
	}
	
	function deleteContentTask($ResourceId)
	{		
		$sql = "DELETE FROM task_content WHERE ResourceId ='".addslashes($ResourceId)."'";
		$this->objMysql->query($sql);		
	}
	
	function getMailFromDBRow(&$row,$what)
	{
		$save_dir = $this->hasMailFile($row);
		if(!$save_dir) return false;
		return $this->getMailFromFile($save_dir,$row["EmailId"],$what);
	}
	
	function hasMailFile(&$row)
	{
		$save_dir = Email_IMAP::getSaveDirBySendDate($row["EmailSendTime"],false);
		$file = $save_dir . "/" . $row["EmailId"] . ".header";
		if(file_exists($file)) return $save_dir;
		
		$save_dir = Email_IMAP::getSaveDirByEmailId($row["EmailId"]);
		$file = $save_dir . "/" . $row["EmailId"] . ".header";
		if(file_exists($file)) return $save_dir;
		
		return false;
	}
	
	function getMailFromFile($dir,$filename,$what)
	{
		if(substr($dir,-1) != "/") $dir .= "/";
		
		if($what == "header") $file = $dir . $filename . ".header";
		elseif($what == "body") $file = $dir . $filename . ".body";
		else return false;
		
		if(!is_file($file)) return false;
		$content = file_get_contents($file);
		if(!$content) return false;
		if($what == "body")
		{
			$content = gzinflate($content);
			if(!$content) return false;
		}
		$obj = @unserialize($content);
		return $obj;
	}
	
	function filterEmailBody(&$mailbody)
	{
		if(!is_array($mailbody) || !isset($mailbody["htmlmsg"])) return;
		
//		if(isset($_REQUEST["debug4"]))
//		{
				/*$arr_htmlarr = preg_split("|</body>|i",$mailbody["htmlmsg"]);
				foreach ($arr_htmlarr as $key=>$value) {
					if($key > 0){
						$mailbody["htmlmsg"].=preg_replace('/\<body\>/isU','',$value);
					}else{
						$mailbody["htmlmsg"].=$value;
					}
				}*/
//		}
		
		$new_html = "";
		$arr_html = preg_split("|</html>|i",$mailbody["htmlmsg"]);
		$mailbody["htmlmsg"] = "";
		if(isset($mailbody["charset"]) && $mailbody["charset"])
		{
			$mailbody["htmlcharset"] = $mailbody["charset"];
		}
		
		foreach($arr_html as $v)
		{
			if(preg_match("|(.*)<body[^>]*>(.*)|ism",$v,$matches))
			{
				$v = $matches[2];
				$v = str_ireplace(array("</body>","</html>"),"",$v);
				
				//<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				if(preg_match("|http-equiv=.*Content-Type.*|i",$matches[1],$charsetmatches))
				{
					if(empty($mailbody["htmlcharset"]) && preg_match("|charset=([a-z0-9-]+)|i",$charsetmatches[0],$matches))
					{
						$mailbody["htmlcharset"] = $matches[1];
					}
				}
			}
			
			$mailbody["htmlmsg"] .= $v;
		}
	}
	
	function getLeaderResult($uniqueid){
		$sql = "SELECT * FROM email_leader_result WHERE EmailUniqueID = '".addslashes($uniqueid)."'";
		return $this->objMysql->getRows($sql);
	}
	
	function replaceLeaderResult($uniqueid,$ActionId,$Leader,$ActionResult){
		$now = date("Y-m-d H:i:s");
		$sql = "REPLACE INTO email_leader_result(EmailUniqueID,ActionId,Leader,ActionResult,LastUpdateTime) VALUES ('".addslashes($uniqueid)."','".addslashes($ActionId)."','".addslashes($Leader)."','".addslashes($ActionResult)."','$now')";
		$this->objMysql->query($sql);
	}
	
	function deleteLeaderResult($uniqueid)
	{
		$sql = "DELETE FROM email_leader_result WHERE EmailUniqueID = '".addslashes($uniqueid)."'";
		$this->objMysql->query($sql);	
	}
	
	function getTag(){
		$sql = "SELECT * FROM tag";
		return $this->objMysql->getRows($sql);
	}
	
	function checkTag($tagname){
		$sql = "SELECT * FROM tag WHERE TagName = '".addslashes($tagname)."'";
		return $this->objMysql->getFirstRow($sql);
	}
	
	function getEmailTag($uniqueid){
		$sql = "SELECT * FROM email_tag WHERE EmailUniqueID = '".addslashes($uniqueid)."'";
		return $this->objMysql->getRows($sql);
	}
	
	function delTag($tagid){
		$sql = " INSERT INTO tag_404 SELECT * FROM tag WHERE ID = '$tagid'";
		$res = $this->objMysql->query($sql);
		$sql = "DELETE FROM tag WHERE TagID = ".intval($tagid)."";
		$this->objMysql->query($sql);
	}
	
	function addTag($tagname){
		$sql = "INSERT INTO tag (TagName) VALUES('".addslashes($tagname)."')";
		$this->objMysql->query($sql);
	}
	
	function updateTag($tagid,$tagname){
		$sql = "UPDATE tag SET TagName = '".addslashes($tagname)."' WHERE TagID = ".intval($tagid);
		$this->objMysql->query($sql);
	}
	
	function addEmailTag($uniqueid,$tagname){
		$sql = "INSERT INTO email_tag (EmailUniqueID,TagName) VALUES('".addslashes($uniqueid)."','".addslashes($tagname)."')";
		$this->objMysql->query($sql);
	}
	
	function getWhereByCond($cond)
	{
		$arr_where = array();
		if(isset($cond["filterStatus"]) && $cond["filterStatus"])
		{
			/*
			1)	PENDING(��ݿ������email״ֵ̬ΪPrematched) , ��״̬ΪĬ��ѡ��
			2)	JUNKED(״ֵ̬ΪJunk����Junk_todo)
			3)	NEW(״ֵ̬ΪNEW)
			4)	OLD(״ֵ̬ΪOLD)
			5)	ALL(��ѯʱ����email status����filter)
			*/
			/*if($cond["filterStatus"] == "JUNKED"){
				$arr_where[] = "(EmailStatus ='JUNK' OR EmailStatus ='JUNK_TODO')";*/
			if($cond["filterStatus"] == "NEW"){
				$arr_where[] = "EmailStatus ='NEW'";
			}elseif($cond["filterStatus"] == "OLD"){
				$arr_where[] = "EmailStatus ='OLD'";
			}elseif($cond["filterStatus"] == "PROCESSED"){
				//$arr_where[] = "EmailStatus ='ASSIGNED'";
			}elseif($cond["filterStatus"] == "ALL"){
				
			}else{
				$arr_where[] = "EmailStatus = 'PREMATCHED'";
			}			
		}else{
			$arr_where[] = "EmailStatus = 'PREMATCHED'";
		}		
		
		if(isset($cond["filterSite"]) && $cond["filterSite"] && $cond["filterStatus"] == "PENDING")
		{
			if($cond["filterSite"] == "unmatched"){
				$arr_where[] = "EmailUniqueID NOT IN (SELECT EmailUniqueID FROM email_pre_matched) ";
			}elseif($cond["filterSite"] == "junk"){
				$arr_where[] = "EmailUniqueID IN (SELECT EmailUniqueID FROM email_pre_matched WHERE MerchantID < 0)";
			}elseif($cond["filterSite"] != "all"){
				$arr_where[] = "EmailUniqueID IN (SELECT EmailUniqueID FROM email_pre_matched WHERE SiteName = '".$cond["filterSite"]."' AND  MerchantID >= 0)";
			}
		}elseif($isleader){
			//$arr_where[] = "EmailUniqueID IN (SELECT EmailUniqueID FROM email_pre_matched WHERE SiteName in (" . implode(",",$arr_leader[$user]) . "))";
		}
		
		if(isset($cond["filterProcess"]) && $cond["filterProcess"] && $cond["filterStatus"] == "PROCESSED")
		{
			//if($cond["filterProcess"] == "Content"){
				//$arr_where[] = "EmailStatus ='ASSIGNED' AND EmailUniqueID IN (SELECT EmailUniqueID FROM email_leader_result WHERE ActionName = 'assign')";
			if(is_numeric($cond["filterProcess"])){
				$arr_where[] = "EmailStatus ='ASSIGNED' AND EmailUniqueID IN (SELECT EmailUniqueID FROM email_leader_result WHERE ActionId = '". $cond["filterProcess"] ."')";
			}elseif($cond["filterProcess"] == "junk"){
				$arr_where[] = "(EmailStatus ='JUNK' OR EmailStatus ='JUNK_TODO')";
			}elseif($cond["filterProcess"] == "assign"){
				$arr_where[] = "EmailStatus ='ASSIGNED' AND EmailUniqueID IN (SELECT EmailUniqueID FROM email_assigned WHERE MerchantID > 0)";
			}elseif($cond["filterProcess"] == "noassign"){
				$arr_where[] = "EmailStatus ='ASSIGNED' AND EmailUniqueID IN (SELECT EmailUniqueID FROM email_assigned WHERE MerchantID = 0)";
			}else{
				$arr_where[] = "(EmailStatus ='JUNK' OR EmailStatus ='JUNK_TODO' OR EmailStatus ='ASSIGNED')";
			}
			
			if(isset($cond["filterSite"]) && $cond["filterSite"])		
			{		
				if($cond["filterSite"] == "unmatched"){
					//$arr_where[] = "EmailUniqueID IN (SELECT EmailUniqueID FROM email_leader_result WHERE ActionResult <> '')";
					$arr_where[] = "EmailUniqueID NOT IN (SELECT EmailUniqueID FROM email_assigned)";
				}elseif($cond["filterSite"] != "all"){
					$arr_where[] = "EmailUniqueID IN (SELECT EmailUniqueID FROM email_assigned WHERE SiteName = '".$cond["filterSite"]."')";
				}
			}
		}
		
		if(isset($cond["filterSubject"]) && $cond["filterSubject"])
		{
			$arr_where[] = "EmailTitle like '%" . addslashes($cond["filterSubject"]) . "%'";
		}
		
		if(isset($cond["filterSender"]) && $cond["filterSender"])
		{
			$arr_where[] = "EmailSenderAddr like '%" . addslashes($cond["filterSender"]) . "%'";
		}
		
		return $arr_where;
	}
	
	function resetprematch($uniqueid,$newsite)
	{
		$sql = "delete from email_pre_matched where EmailUniqueID = '" . addslashes($uniqueid) . "'";
		$this->objMysql->query($sql);
		
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "user";
		
		$sql = "INSERT ignore INTO email_pre_matched(EmailUniqueID, SiteName, MerchantID, MerchantName, MatchedRuleInfo, LastUpdateTime)
VALUES ('" . addslashes($uniqueid) . "', '" . addslashes($newsite) . "', '0', '', 'reset by $user', now());";
		$this->objMysql->query($sql);
	}
	
	function addSafeEmail($email,$editor)
	{
		$sql = "INSERT ignore INTO email_safe_list (Email,Editor,LastUpdateTime) VALUES('" . addslashes($email) . "', '" . addslashes($editor) . "', '" . date("Y-m-d H:i:s") . "')";
		$this->objMysql->query($sql);
	}
	
	function delSafeEmail($email)
	{
		$sql = "DELETE FROM email_safe_list WHERE Email = '" . addslashes($email) . "'";
		$this->objMysql->query($sql);
	}
	
	function editSafeEmail($email,$new_email,$editor)
	{
		$sql = "UPDATE email_safe_list SET Email = '" . addslashes($new_email) . "', Editor = '" . addslashes($editor) . "', LastUpdateTime = '" . date("Y-m-d H:i:s") . "' WHERE Email = '" . addslashes($email) . "'";
		$this->objMysql->query($sql);		
	}
	
	function checkSafeEmail($email)
	{
		$sql = "SELECT Email FROM email_safe_list WHERE Email = '" . addslashes($email) . "'";
		return $this->objMysql->getRows($sql);
	}
	
	function loadBDEmailRule()
	{
		$return_arr = array();
		$rule_arr = array();
		//$sql = "SELECT ID, SenderAddress, ToAddress, EmailSubject, EmailBody, CheckRule FROM email_matchrule_bd";
		$sql = "SELECT ID, Type, CheckRule FROM email_matchrule_bd";
		$rule_arr = $this->objMysql->getRows($sql);
		foreach($rule_arr as $v){
			$tmp_arr = array();
			$tmp_arr = explode("|||", $v["CheckRule"]);
			if($tmp_arr[0] != $v["Type"]) continue;
			
			//$return_arr = array("type" => "", "addr" => "", "boxexcept" => "", "box" => "", "titleexcept" => "", "title" => "", "bodyexcept" => "", "body" => "");
			$return_arr[$tmp_arr[0]][$tmp_arr[1]][$v["ID"]] = $tmp_arr;
		}
		return $return_arr;
	}
	
	function checkMailByBDRule($oMailHeader, $arrMailBody, $arrBDRules)
	{
		$rule_id = 0;
		$fromaddress = "";
		$toaddress = "";
		
		if(isset($oMailHeader->from[0]))
		{
			$fromaddress = $oMailHeader->from[0]->mailbox.'@'.$oMailHeader->from[0]->host;
		}
		
		if(isset($oMailHeader->to[0]))
		{
			$toaddress = $oMailHeader->to[0]->mailbox.'@'.$oMailHeader->to[0]->host;
		}
		//$strEmailAddress .= $oMailHeader->from[0]->mailbox.'@'.$oMailHeader->from[0]->host . "\n";
		$subject = $oMailHeader->subject;
		$body = $arrMailBody["plainmsg"];		
		
		if(isset($arrBDRules["SenderAddress"][$fromaddress])){			
			$rule_id = $this->checkBD($fromaddress, $toaddress, $subject, $body, $arrBDRules["SenderAddress"][$fromaddress]);
			if($rule_id > 0){
				return $rule_id;
			}
		}
		
		if(isset($arrBDRules["ToAddress"][$toaddress])){			
			$rule_id = $this->checkBD($fromaddress, $toaddress, $subject, $body, $arrBDRules["ToAddress"][$toaddress]);
			if($rule_id > 0){
				return $rule_id;
			}
		}
		
		
		foreach($arrBDRules["None"] as $arr_rule_content){
			$rule_id = $this->checkBD($fromaddress, $toaddress, $subject, $body, $arr_rule_content);
			if($rule_id > 0){
				return $rule_id;
			}
		}
		return false;
	}
	
	function checkBD($fromaddress, $toaddress, $subject, $body, $rules)
	{
		$rule_id = 0;
		foreach($rules as $rid => $rule_arr){
			//$rule_arr = array("type" => "", "addr" => "", "boxexcept" => "", "box" => "", "titleexcept" => "", "title" => "", "bodyexcept" => "", "body" => "");
			$check_box = "";
			if($rule_arr[0] == "SenderAddress"){
				$check_box = $toaddress;
			}elseif($rule_arr[0] == "ToAddress"){
				$check_box = $fromaddress;
			}
			
			if($rule_arr[0] == "SenderAddress" && empty($rule_arr[3]) && empty($rule_arr[5]) && empty($rule_arr[7])){
				$rule_id = $rid;
				break;
			}			
			
			if(!empty($rule_arr[3]) && !empty($check_box)){
				$tmp_arr = array();
				$tmp_arr = explode(",", $rule_arr[3]);
				foreach($tmp_arr as $v){
					if($rule_arr[2] == 1){						
						if($this->isContentMatched($v, $check_box)){
							$rule_id = $rid;
							break 2;
						}
					}else{
						if(!$this->isContentMatched($v, $check_box)){
							$rule_id = $rid;
							break 2;
						}
					}
				}
			}
			
			if(!empty($rule_arr[5]) && !empty($subject)){
				$tmp_arr = array();
				$tmp_arr = explode(",", $rule_arr[5]);
				foreach($tmp_arr as $v){
					if($rule_arr[4] == 1){						
						if($this->isContentMatched($v, $subject)){
							$rule_id = $rid;
							break 2;
						}
					}else{
						if(!$this->isContentMatched($v, $subject)){
							$rule_id = $rid;
							break 2;
						}
					}
				}
			}
			
			if(!empty($rule_arr[7]) && !empty($body)){
				$tmp_arr = array();
				$tmp_arr = explode(",", $rule_arr[7]);
				foreach($tmp_arr as $v){
					if($rule_arr[6] == 1){						
						if($this->isContentMatched($v, $body)){
							$rule_id = $rid;
							break 2;
						}
					}else{
						if(!$this->isContentMatched($v, $body)){
							$rule_id = $rid;
							break 2;
						}
					}
				}
			}
		}
		return $rule_id;
	}
}
?>