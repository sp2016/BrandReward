<html>
<head>
<title>Affiliate List</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2;}
th{align:center;background:#346283;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
</style>
<script type="text/javascript">
function addColor(obj) {
    originColor = obj.bgColor;
    obj.bgColor = "#FFFFBB";
}
function removeColor(obj) {
	obj.bgColor = originColor;
}
</script>
{/literal}
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="3"  bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF"><td align="center"><h1>Affiliate List</h1></td></tr>
	<form name="form1" action="" method="get">
	<tr>
	 	<td>
			<b>Filter</b>&nbsp;&nbsp;
			Type: 
			<select name="type" id="type" onchange='this.form.submit();'>
			{foreach from=$type_arr item=info key=k}
				<option value="{$k}" {if $type eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			IsActive: 
			<select name="isactive" id="isactive" onchange='this.form.submit();'>
			{foreach from=$isactive_all_arr item=info key=k}
				<option value="{$k}" {if $isactive eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}			
			</select>&nbsp;&nbsp;
			Revenue Account:
			<select name="RevenueAccount" id="RevenueAccount">
			<option value="" {if $RevenueAccount eq ''}selected="selected"{/if}>ALL</option>
			{html_options options=$fin_rev_acc_list selected=$RevenueAccount}
			</select>&nbsp;&nbsp;
			Program Crawled:
			<select name="ProgramCrawled" id="ProgramCrawled">
			{foreach from=$program_crawled_arr item=info key=k}
				<option value="{$k}" {if $ProgramCrawled eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			Name: <input name="name" type="text" value="{$name}">&nbsp;&nbsp;
			URL Keyword: <input name="affurlkw" type="text" value="{$affurlkw}">&nbsp;&nbsp;
			Country:
			<select name="country" id="country">
			{foreach from=$countries item=country key=k}
				<option value="{$k}" {if $countrySel eq $k}selected="selected"{/if}>{$country}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			<input type="submit" value="Submit">&nbsp;&nbsp;
			|&nbsp;&nbsp;
			<a href="affiliate_list.php?action=add"><b>Add New Affiliate</b></a>&nbsp;&nbsp;<br/>
			<b>Sync Operation</b>&nbsp;&nbsp;
			1.<a href="affiliate_list.php?action=export"><b>Export Affiliate Files</b></a>&nbsp;&rsaquo;&nbsp;
			2.<a href="affiliate_list.php?action=sync"><b>Synchronize Affiliate Files</b></a>&nbsp;&rsaquo;&nbsp;
			3.<a href="http://bcg.mgsvr.com/front/affiliate_list.php?action=updateall" onclick="return confirm('Confirm update all sites?');"><b>Update All Sites Databases</b></a>
		</td>
	</tr>
	</form>
	<tr>
		<td align="right">{$pagebar}</td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellspacing="1" cellpadding="3" style="word-break:break-all">
				<tr> 
					<th width="25px">ID</th>
					<th width="150px">Name (Short)</th>
					<th width="60px">Type</th>
					<th width="220px">Domain</th>
					<th width="100px">Deep-Url<br/>ParaName</th>
					<th width="180px">AffiliateUrl Keyword List 1</th>
					<th width="180px">AffiliateUrl Keyword List 2</th>
					<th>Sub Tracking Setting [1]<hr width="150px">Sub Tracking Setting [2]</th>
					<th width="40px">Rank</th>
					<th width="100px">Revenue<br/>Account</th>
					<th width="60px">Program<br/>Crawled</th>
					<th width="90px">Stats Report Crawled</th>
					<th width="100px">Operation</th>
				</tr>
				{foreach from=$data item=info}
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);" > 
					<td align="center">{$info.ID}</td>
					<td align="left"><a href="/front/program_list.php?affiliatetype={$info.ID}" target="_blank">{$info.Name}</a><br/>({$info.ShortName})</td>
					<td align="center"><span style="color:{if $info.type_format=='Network'}blue{elseif $info.type_format=='InHouse'}orange{else}black{/if};">{$info.type_format}</span></td>
					<td align="left"><a href="{$info.Domain}" target="_blank">{$info.Domain}</a></td>
					<td align="left">{$info.DeepUrlParaName}</td>
					<td align="left">{$info.AffiliateUrlKeywords_format}</td>
					<td align="left">{$info.AffiliateUrlKeywords2_format}</td>
					<td align="left">{if $info.SubTracking}[1] {/if}{$info.SubTracking}{if $info.SubTracking2}<br />[2] {/if}{$info.SubTracking2}</td>
					<td align="center">{$info.ImportanceRank}</td>
					<td align="left">{$fin_rev_acc_list[$info.RevenueAccount]}</td>
					<td align="center">{if $info.ProgramCrawled eq 'YES'}<b>{$info.ProgramCrawled}</b>{else}{$info.ProgramCrawled}{/if}</td>
					<td align="center">{if $info.StatsReportCrawled eq 'YES'}<b>{$info.StatsReportCrawled}</b>{else}{$info.StatsReportCrawled}{/if}</td>
					<td align="center">
						[<a href="affiliate_list.php?action=edit&id={$info.ID}" target="_blank">Full Edit</a>]<br />
						[<a href="affiliate_detail.php?id={$info.ID}" target="_blank">Full Detail</a>]<br />
						[<a href="program_add.php?affid={$info.ID}" target="_blank">Add Program</a>]
					</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
	<tr>
		<td align="right">{$pagebar1}</td>
	</tr>
</table>
</body>
</html>
