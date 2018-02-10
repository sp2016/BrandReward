<html>
<head>
<title>Affiliate Management System</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script language="javascript" src="/js/jquery.js"></script>
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2;}
th{text-align:center;background:#346283;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
.td_label{width:280px;text-align:right;background-color:#BFE0F7;font-weight:bold;}
.td_value{text-align:left;background-color:#FFFFFF;}
</style>

<script type="text/javascript">
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
<table width="99%" cellspacing="0" cellpadding="4" bgcolor="#BFE0F7">
  	<tr bgcolor="#FFFFFF"><td align="center"><h1>Affiliate Info</h1></td></tr>
  	<tr>
  		<td>
  			<table width="100%" cellspacing="1" cellpadding="3">
				<tr> 
					<td class="td_label">Name</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.Name}
						&nbsp;&nbsp;&nbsp;&nbsp;<a href="/front/program_list.php?affiliatetype={$data.ID}" target="_blank" >See All Programs</a>
					</td>
				</tr>
				<tr > 
					<td class="td_label">Short Name</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.ShortName}</td>
				</tr>
				<tr> 
					<td class="td_label">Join Date</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.joindate_format}</td>
				</tr>
				<tr> 
					<td class="td_label">Domain</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.Domain}{if $data.Domain}<input type="hidden" id="domain" value="{$data.Domain}"> <input type="button" value="GO" onclick="openDomainURL('domain');">{/if}</td>
				</tr>
				<tr> 
					<td class="td_label">IsActive</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.IsActive}</td>
				</tr>
				<tr> 
					<td class="td_label">IsInHouse</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.type_format}</td>
				</tr>
				<tr>
					<td class="td_label">Login Url</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.LoginUrl}</td>
				</tr>
				<tr> 
					<td class="td_label">Blog</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.BlogUrl}{if $data.BlogUrl}<input type="hidden" id="blog" value="{$data.BlogUrl}"> <input type="button" value="GO" onclick="openDomainURL('blog');">{/if}</td>
				</tr>
				<tr> 
					<td class="td_label">Facebook</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.FacebookUrl}{if $data.FacebookUrl}<input type="hidden" id="facebook" value="{$data.FacebookUrl}"> <input type="button" value="GO" onclick="openDomainURL('facebook');">{/if}</td>
				</tr>
				<tr> 
					<td class="td_label">Twitter</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.TwitterUrl}{if $data.TwitterUrl}<input type="hidden" id="twitter" value="{$data.TwitterUrl}"> <input type="button" value="GO" onclick="openDomainURL('twitter');">{/if}</td>
				</tr>
				{*<tr> 
					<td class="td_label">How To Get Program ID in Network Intro</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.GetProgramIDInNetworkUrl}&nbsp;</td>
				</tr>*}
				<tr> 
					<td class="td_label">AffiliateUrl Keyword List 1</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.AffiliateUrlKeywords_format}</td>
				</tr>
				<tr> 
					<td class="td_label">AffiliateUrl Keyword List 2</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.AffiliateUrlKeywords2_format}<br/>Affiliate Related URL must contain keywords from List 1 <b>AND</b> List 2, if either of list is not empty.<br/>For each List, any Affiliate Related URL must contain one of keyword in the list if it is not empty.</td>
				</tr>
				<tr> 
					<td class="td_label">Support Deep Url</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.SupportDeepUrl}</td>
				</tr>
				<tr> 
					<td class="td_label">Deep Url ParaName</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.DeepUrlParaName}</td>
				</tr>
				<tr> 
					<td class="td_label">Support Sub Tracking</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.SupportSubTracking}</td>
				</tr>
				<tr> 
					<td class="td_label">Sub Tracking Setting 1</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.SubTracking}&nbsp;</td>
				</tr>
				<tr>
					<td class="td_label">Sub Tracking Setting 2</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.SubTracking2}&nbsp;</td>
				</tr>				
				<tr> 
					<td class="td_label">Revenue Account</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{if $data.RevenueAccount}{$data.RevenueAccount}{/if}</td>
				</tr>
				<tr> 
					<td class="td_label">Revenue Cycle</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.RevenueCycle}</td>
				</tr>
				<tr> 
					<td class="td_label">Revenue Remark</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.RevenueRemark|escape|nl2br}</td>
				</tr>
				<tr> 
					<td class="td_label">Program Crawled</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.ProgramCrawled}</td>
				</tr>
				<tr> 
					<td class="td_label">Program Crawl Remark</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.ProgramCrawlRemark}</td>
				</tr>
				<tr> 
					<td class="td_label">Stats Report Crawled</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.StatsReportCrawled}</td>
				</tr>
				<tr> 
					<td class="td_label">Stats Report Crawl Remark</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.StatsReportCrawlRemark}</td>
				</tr>
				<tr>
					<td class="td_label">Stats Affiliate Name</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.StatsAffiliateName}</td>
				</tr>
				<tr>
					<td class="td_label">Importance Rank</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.ImportanceRank}</td>
				</tr>
				<tr>
					<td class="td_label">Program Url Template</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.ProgramUrlTemplate}</td>
				</tr>
				<tr> 
					<td class="td_label">Comment</td>
					<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">{$data.Comment|escape|nl2br}</td>
				</tr>
			<tr>
		<td class="td_label">Countries</td>
		<td class="td_value" onMouseOver="this.style.backgroundColor='#FFFFBB';" onMouseOut="this.style.backgroundColor='#FFFFFF'">
			{foreach from=$countries item=country key=k name="cont"}
				{if in_array($k, $countrySel)}{$country};&nbsp;&nbsp;&nbsp;{/if}
			{/foreach}
		</td>
	</tr>				
			</table>
		</td>
	</tr>
</table>
</body>
</html>
