<html>
<head>
<title>Add Affiliate</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script language="javascript" src="/js/jquery.js"></script>
<script language="javascript" src="/js/check.js"></script>
<script type="text/javascript" src="/js/My97DatePicker/WdatePicker.js"></script>
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2;}
th{text-align:center;background:#346283;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
.td_label{width:280px;text-align:right;background-color:#EEEEEE;font-weight:bold;border:1px solid #DDDDDD;}
.td_value{text-align:left;background-color:#FFFFFF;border:1px solid #DDDDDD;}
.btn_large{width:120px;height:40px;font-family:Tahoma,Arial;font-size:16px;} 
</style>
<script type="text/javascript">
$(document).ready(function(){
	$("#SupportDeepUrl").bind('change', function(){
		if ($(this).val() == 'YES') $("#deepurlparaname").removeAttr('readonly');
		else if ($(this).val() == 'NO') $("#deepurlparaname").attr('readonly', 'readonly');
	});
	if ($("#SupportDeepUrl").val() == 'NO') $("#deepurlparaname").attr('readonly', 'readonly');

	$("#SupportSubTracking").bind('change', function(){
		if ($(this).val() == 'YES') {
			$("#subtrackingset,#subtrackingset2").removeAttr('readonly');
		} else if ($(this).val() == 'NO') {
			$("#subtrackingset,#subtrackingset2").attr('readonly', 'readonly');
		}
	});

	if ($("#SupportSubTracking").val() == 'NO') {
		$("#subtrackingset,#subtrackingset2").attr('readonly', 'readonly');
	}
});

function regUrl(url) {
	var pattern = /^http:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/gi;
	if (url != '' && pattern.test(url) == false) {
		return false;
	} else {
		return true;
	}
}
function checkForm() {
	var blog = $("input[name='blog']").val();
	var facebook = $("input[name='facebook']").val();
	var twitter = $("input[name='twitter']").val();
	var deepurlparaname = $.trim($("#deepurlparaname").val());
	var subtrackingset = $.trim($("#subtrackingset").val());
	var subtrackingset2 = $.trim($("#subtrackingset2").val());
	
	var res = true;
	$("#blogmsg").html('');
	$("#facebookmsg").html('');
	$("#twittermsg").html('');
	$("#deepurlparanamemsg").html('');
	$("#subtrackingsetmsg").html('');
	$("#subtrackingset2msg").html('');

	if (regUrl(blog) == false) {
		$("#blogmsg").html('Url格式不正确');
		res = false;
	}
	if (regUrl(facebook) == false) {
		$("#facebookmsg").html('Url格式不正确');
		res = false;
	}
	if (regUrl(twitter) == false) {
		$("#twittermsg").html('Url格式不正确');
		res = false;
	}

	if ($("#SupportDeepUrl").val() == 'YES' && deepurlparaname == '') {
		$("#deepurlparanamemsg").html('此项不能为空');
		res = false;
	}

	if ($("#SupportSubTracking").val() == 'YES') {
		if (subtrackingset == '') {
			$("#subtrackingsetmsg").html('此项不能为空');
			res = false;
		}
		if (subtrackingset2 == '') {
			$("#subtrackingset2msg").html('此项不能为空');
			res = false;
		}
	}
	
	return res;
}

function get_ssl_rd_url(url){
	return "https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent(url);
}

function openDomainURL(id){
	var url = $("#"+id).val();
	url = url.replace(/(^\s*)|(\s*$)/g, '');
	if (url != '') {
		url = get_ssl_rd_url(url);
		window.open(url);
	}
}
</script>
{/literal}
</head>
<body>
<form name="form1" method="post" action="" onSubmit="if(!checkForm())return false;return Validator.Validate(this,3)">
<div style="text-align:center;width:100%;"><h1>Add Affiliate</h1></div>
<table width="100%" align="center" cellspacing="1" cellpadding="5" bgcolor="#BFE0F7">
	<tr><td colspan="2" style="font-weight:bold">--- Basic ---</td></tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> Name</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="name" DataType="Require" size="40"></td>
	</tr>
	<tr>
		<td class="td_label">Short Name</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="shortname"></td>
	</tr>
	<tr> 
		<td class="td_label">Importance Rank</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="ImportanceRank" id="ImportanceRank" size="80"></td>
	</tr>
	<tr> 
		<td class="td_label">Join Date</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="joindate" id="joindate" size="15" onFocus="{literal}WdatePicker({readOnly:true});{/literal}"></td>
	</tr>
	<tr><td colspan="2" style="font-weight:bold">--- Control ---</td></tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> IsActive</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			<select name="isactive" id="type" DataType="Require">
			{foreach from=$isactive_arr item=info key=k}
				<option value="{$k}">{$info}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> IsInHouse</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			<select name="isinhouse" id="type" DataType="Require">
			{foreach from=$type_arr item=info key=k}
				<option value="{$k}">{$info}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="td_label">Login Url</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="loginurl" size="80"></td>
	</tr>
	<tr> 
		<td class="td_label">Program Url Template</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="ProgramUrlTemplate" id="ProgramUrlTemplate" size="80"></td>
	</tr>
	{*<tr>
		<td class="td_label">How To Get Program ID in Network Intro</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="proidinnetword" id="proidinnetword" size="60"> <input type="button" value="GO" onclick="openDomainURL('proidinnetword');"> <font color="blue">请输入以 http:// 开头的Url</font></td>
	</tr>*}
	<tr><td colspan="2" style="font-weight:bold">--- Additional ---</td></tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> Domain</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="domain" id="domain" size="40" DataType="Url" msg="Url格式不正确"> <input type="button" value="GO" onclick="openDomainURL('domain');"> <font color="blue">Start with http:// OR https://</font></td>
	</tr>
	<tr>
		<td class="td_label">Blog</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="blog" id="blog" size="60"> <input type="button" value="GO" onclick="openDomainURL('blog');"> <font color="blue">可输入以 http:// 开头的Url</font><span style="color:red" id="blogmsg"></span></td>
	</tr>
	<tr>
		<td class="td_label">Facebook</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="facebook" id="facebook" size="60"> <input type="button" value="GO" onclick="openDomainURL('facebook');"> <font color="blue">可输入以 http:// 开头的Url</font><span style="color:red" id="facebookmsg"></span></td>
	</tr>
	<tr>
		<td class="td_label">Twitter</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="twitter" id="twitter" size="60"> <input type="button" value="GO" onclick="openDomainURL('twitter');"> <font color="blue">可输入以 http:// 开头的Url</font><span style="color:red" id="twittermsg"></span></td>
	</tr>
	<tr>
		<td class="td_label">Countries</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			<table border="0" style="width:80%;">
				<tr>
				{foreach from=$countries item=country key=k name="cont"}
					{if $smarty.foreach.cont.index % 5 == 0}
					</tr><tr>
					{/if}
					<td><input type="checkbox" name="countries[]" value="{$k}"/>&nbsp;<label>{$country}</label></td>
				{/foreach}
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2" style="font-weight:bold">--- Aff Url Control ---</td></tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> AffiliateUrl Keyword List 1</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="affurlkw" cols="45" rows="8"></textarea></td>
	</tr>
	<tr>
		<td class="td_label"><font color="#FF0000">*</font> AffiliateUrl Keyword List 2</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="affurlkw2" cols="45" rows="8"></textarea><br/>Affiliate Related URL must contain keywords from List 1 <b>AND</b> List 2, if either of list is not empty.<br/>For each List, any Affiliate Related URL must contain one of keyword in the list if it is not empty.</td>
	</tr>
	<tr>
		<td class="td_label">Support Deep Url</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			<select name="SupportDeepUrl" id="SupportDeepUrl">
				<option value="YES">YES</option>
				<option value="NO">NO</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="td_label">Deep Url ParaName</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="deepurlparaname" id="deepurlparaname" size="80"><span style="color:red" id="deepurlparanamemsg"></span></td>
	</tr>
	<tr>
		<td class="td_label">Support Sub Tracking</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			<select name="SupportSubTracking" id="SupportSubTracking">
				<option value="YES">YES</option>
				<option value="NO">NO</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="td_label">Sub Tracking Setting 1</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="subtrackingset" id="subtrackingset" size="80"><span style="color:red" id="subtrackingsetmsg"></span></td>
	</tr>
	<tr>
		<td class="td_label">Sub Tracking Setting 2</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="subtrackingset2" id="subtrackingset2" size="80"><span style="color:red" id="subtrackingset2msg"></span></td>
	</tr>
	<tr><td colspan="2" style="font-weight:bold">--- Revenue Control ---</td></tr>
	<tr> 
		<td class="td_label">Revenue Account</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><select name="RevenueAccount" id="RevenueAccount">{html_options options=$fin_rev_acc_list selected=$revacc}</select></td>
	</tr>
	<tr> 
		<td class="td_label">Revenue Cycle {$revacc}</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="RevenueCycle" id="RevenueCycle" cols="45" rows="8"></textarea></td>
	</tr>
	<tr> 
		<td class="td_label">Revenue Remark</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="RevenueRemark" id="RevenueRemark" cols="45" rows="8"></textarea></td>
	</tr>
	<tr><td colspan="2" style="font-weight:bold">--- Stats ---</td></tr>
	<tr> 
		<td class="td_label">Program Crawled</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><select name="ProgramCrawled" id="ProgramCrawled">{html_options values=$program_crawled_arr output=$program_crawled_arr}</select></td>
	</tr>
	<tr> 
		<td class="td_label">Program Crawl Remark</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="ProgramCrawlRemark" id="ProgramCrawlRemark" cols="45" rows="8"></textarea></td>
	</tr>
	<tr> 
		<td class="td_label">Stats Report Crawled</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><select name="StatsReportCrawled" id="StatsReportCrawled">{html_options values=$stats_report_crawled_arr output=$stats_report_crawled_arr}</select></td>
	</tr>
	<tr> 
		<td class="td_label">Stats Report Crawl Remark</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="StatsReportCrawlRemark" id="StatsReportCrawlRemark" cols="45" rows="8"></textarea></td>
	</tr>
	<tr> 
		<td class="td_label">Stats Affiliate Name</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><input type="text" name="StatsAffiliateName" id="StatsAffiliateName" size="80"></td>
	</tr>
	<tr> 
		<td class="td_label">Comment</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'"><textarea name="Comment" id="Comment" cols="45" rows="8"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input name="action" type="hidden" value="addfinish">
			<input type="submit" class="btn_large" value="Submit">
			<input type="reset" value="Reset">
		</td>
	</tr>
</table>
</form>
</body>
</html>
