<html>
<head>
<title>Program Links List</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/program.js"></script>
<script language="JavaScript" src="/js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="/js/program_search.js"></script>
<link type="text/css" rel="stylesheet" href="/css/jquery.autocomplete.css" />
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2.2;}
th{align:center;background:#346283;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
.external{color:#0066FF;}
.internal{color:#FF3333;}
</style>
{/literal}
</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF">
		<td align="center">
			<h1>Program {if $type eq "product"}Products{else}Links{/if} List</h1>			
		</td>
	</tr>
	<tr>
		<form name="form1" action="" method="get">
		<td>
			<b>Filter</b>			
			Affiliate: <input type="text" id="affiliatename" name="affiliatename" value="{$affiliatename}" size="30" />&nbsp;<input type="button" value="reset" onclick="resetAff()" />
			<input type="hidden" name="affiliatetype" id="affiliatetype" value="{$affid}" />&nbsp;&nbsp;
			
			Program: <input type="text" id="program_search" name="name" value="{$name}" size="30" />&nbsp;&nbsp;
			
			<input type="hidden" name="type" value="{$type}" />
			<input type="submit" value="Query">
		</td>
		</form>
	</tr>
	<tr>
		<td align="right">{$pagebar}</td>
	</tr>
	<tr>
		<td>
			<table  width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all">
				<tr>
					<th width="160px">Program</th>
					<th width="160px">Image</th>
					<th width="200px">Name<hr width="80%" />Desc</th>
					<th width="800px">HtmlCode</th>
					<th width="120px">AddTime<hr width="80%" />EndTime</th>					
				</tr>
				{foreach from=$links_arr item=info}
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center" width="160px">
						<a href="/front/program_edit.php?ID={$prgm_arr[$info.AffMerchantId].id}" target="_blank">{$prgm_arr[$info.AffMerchantId].name}({$prgm_arr[$info.AffMerchantId].idinaff})</a>
					</td>
					<td align="center" width="160px">{if $info.LinkImageUrl}<img src="{$info.LinkImageUrl}" border=0 width=150 height=150 />{/if}</td>
					<td width="200px">{$info.LinkName}<hr>{$info.LinkDesc}</td>
					<td width="800px">
						<textarea style="width:100%" rows=3>{$info.LinkHtmlCode|escape:"html"}</textarea>
						{$info.LinkHtmlCode}
						{if $info.LinkAffUrl}
							<br />Affiliate Url: {$info.LinkAffUrl}
						{/if}
						{*{$info.OutUrl} {$info.DstUrl} {$info.OriginalUrl}*}
					</td>					
					<td>{$info.AddTime}<hr width="80%" />{$info.LinkEndDate}</td>
				</tr>
				{*<tr bgcolor="{cycle values="#EEEEEE,#FFFFFF"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center">LinkHtmlCode</td>
					<td colspan="20"><textarea style="width:100%" rows=2>{$info.LinkHtmlCode|escape:"html"}</textarea></td>
				</tr>
				<tr height="3px"></tr>*}
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