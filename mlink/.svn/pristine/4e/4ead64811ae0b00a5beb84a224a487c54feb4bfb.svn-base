<html>
<head>
<title>Affiliate Program Link List</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/coupon_queue.js"></script>
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2.2;}
th{align:center;background:#464646;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
</style>
<script type="text/javascript">
function selectIgnore(){
	var ignore =$("#status").attr('value');
	if( ignore == 'IGNORED' || ignore == 'DUPLICATED'){
//		$("#ignoreFilter").css("display","block"); 
		$("#ignoreshow").css("visibility","visible");
	}else{
		$("#ignoreshow").css("visibility","hidden");
//		$("#ignoreFilter").css("display","none"); 
	}
}

function queryProgram(affiliate,program){
	affiliate=affiliate.toLowerCase();
	if(affiliate!='' && affiliate!='all'){
		$.post("program_links.php?action=getAffMerchant&affiliate="+affiliate+"&program="+program, function(data) {
			$("#program").html(data);
			//$("#spanProgram").css({ display: "inline"});
		});
	}else{
		$("#program").html("<option value=''>ALL</option>");
		//$("#spanProgram").hide();
	}
}
function site_change(site){
	site=site.toLowerCase();
	if(site!='' && site!='all'){
		$("#spanMerchantId").css({ display: "inline"});
	}else{
		$("#merchantid").val('');
		$("#spanMerchantId").hide();
	}
}
$(document).ready(function(){
	queryProgram('{/literal}{$affiliate}{literal}','{/literal}{$program}{literal}');
	site_change('{/literal}{$site}{literal}');
})
</script>
{/literal}
</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" bgcolor="#E8E8FF">
	<tr bgcolor="#FFFFFF"><td align="center"><h1>Affiliate Program Link List</h1></td></tr>
	<tr bgcolor="#D3D3F7">
		<form name="form1" action="" method="get">
		<td>
	      {*<a href="javascript:void(0);" onclick="openUploadFeedWindow();"><b>Upload Feed File</b></a><br />
		  <b>Filter</b> 
		  Source: <select name="source" id="source">
		  {foreach from=$source_all_arr item=info key=k}
		    <option value="{$k}" {if $source eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach}
		  </select>&nbsp;&nbsp;
		  
		  Promotion Type: <select name="promotiontype" id="promotiontype">
		  {foreach from=$promotiontype_all_arr item=info key=k}
			<option value="{$k}" {if $promotiontype eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach}
		  </select>&nbsp;&nbsp;
		  
		  Task Status <select name="status" id="status">
		  {foreach from=$status_all_arr item=info key=k}
			<option value="{$k}" {if $status eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach} 
		  </select>&nbsp;&nbsp;
		  
		  Show Unknown Expire <select name="showexpire" id="showexpire">
		  {foreach from=$showexpire_arr item=info key=k}
			<option value="{$k}" {if $showexpire eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach} 
		  </select>&nbsp;&nbsp;
		  
		  Affiliate  <select name="affiliate" id="affiliate">
		  {foreach from=$affiliate_all_arr item=info key=k}
			<option value="{$k}" {if $affiliate eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach} 
		  </select>&nbsp;&nbsp;
		  
		  Id End With <select name="endidnum" id="endidnum">
		  {foreach from=$id_end_arr item=info key=k}
		    <option value="{$k}" {if $endidnum eq $k}selected="selected"{/if}>{$info}</option>
		  {/foreach}
		  </select>&nbsp;&nbsp;
		  
		  <label for="hideimage">Hide Image</label>
		  <input name="hideimage" id="hideimage" type="checkbox" value="1" {if $hideimage eq 1}checked="checked"{/if} onclick="this.form.submit();"/>&nbsp;&nbsp;
			<b>Sort</b>
			Order By: <select name="order" id="order">
				{foreach from=$ordey_arr item=info key=k}
					<option value="{$k}" {if $order eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;
			Perpage Num <input name="perpagenum" type="text" value="{$perpage}" size="5">
			&nbsp;&nbsp;<input type="submit" value="Submit">
		
			
			Source:
			<select name="source" id="source">			
				{html_options options=$source_all_arr selected=$source}			
			</select>&nbsp;&nbsp;*}
			
			
			Affiliate:
			<select name="affiliate" id="affiliate" onchange="queryProgram(this.options[this.selectedIndex].value,'');">
				{html_options options=$affiliate_all_arr selected=$affiliate}
			</select>&nbsp;&nbsp;			
			
			Program:
			<select name="program" id="program" style="width:450px;">
				<option value="">ALL</option>
			</select>&nbsp;&nbsp;
  
			Promotion Type:
			<select name="promotiontype" id="promotiontype">
				{html_options options=$promotiontype_all_arr selected=$promotiontype}
			</select>&nbsp;&nbsp;
			
			Status:
			<select name="status" id="status" onchange="selectIgnore()">
				{html_options options=$status_all_arr selected=$status}
			</select>&nbsp;&nbsp; 
			
			<span id="ignoreshow" {if $status eq 'IGNORED' || $status eq 'DUPLICATED'} style="visibility:visible" {else}style="visibility:hidden" {/if}>
				<input type="text" value="{$ignoreFilter}" name="ignoreFilter" id="ignoreFilter" >	
			</span>		<br>
			
<!--			<span id="ignoreshow" style="display:block">-->
<!--				<input type="text" value="{$ignoreFilter}" name="ignoreFilter" id="ignoreFilter" >	-->
<!--			</span>			-->
			
			Site:
			<select name="site" id="site" onchange="site_change(this.options[this.selectedIndex].value);">
				{html_options values=$all_site output=$all_site selected=$site}
			</select>&nbsp;&nbsp;
			
			<span id="spanMerchantId" style="display:none;">
			MerchantId:
			<input type="text" id="merchantid" name="merchantid" size="10" value="{$merchantid}" onchange="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" onkeyup="this.value=this.value.replace(/\D/g,'')" />
			&nbsp;&nbsp;</span>
			
			<!--Editor:
			<select name="editor" id="editor">
				{html_options values=$all_editor output=$all_editor selected=$editor}
			</select>&nbsp;&nbsp;-->
			<label for="hidehtml">Hide HTML Code</label>
			<input name="hidehtml" id="hidehtml" type="checkbox" value="1" {if $hidehtml neq 1}{else}checked="checked"{/if}/>&nbsp;&nbsp;
			
			<label for="hideimage">Hide Image</label>
			<input name="hideimage" id="hideimage" type="checkbox" value="1" {if $hideimage neq 1}{else}checked="checked"{/if}/>&nbsp;&nbsp;
			
			<label for="hideexpired">Hide Expired</label>
			<input name="hideexpired" id="hideexpired" type="checkbox" value="1" {if $hideexpired neq 1}{else}checked="checked"{/if}/>&nbsp;&nbsp;
			<label for="hideexpired">Hide NoNeedToApply Program</label>
			<input name="noNeedToApply" id="noNeedToApply" type="checkbox" value="1" {if $noNeedToApply neq 1}{else}checked="checked"{/if}/>&nbsp;&nbsp;
			<input type="submit" value="Submit">
		</td>
		</form>
	</tr>
	<tr>
		<td>
			<div style="width:50%;float:left">
			</div>
			<div style="width:50%;float:right;">{$pagebar1}</div>
		</td>
	</tr>
	<tr> 
		<td>
			<table width="100%" cellspacing="1" cellpadding="2">
				<tr height="30px">
				    <th>ID</th>			
					<th>Affiliate / Program Name / Program ID</th>					
					<th>Link Name</th>
					<th>Promotion Type</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Coupon Pending Status</th>
				</tr>
				{foreach from=$data item=info}
				<tr bgcolor="#F7FAE0" style="word-break:break-all">
				    <td>{$info.ID}</td>			
					<td align="center">{$info.affiliate_name} / {$info.AffiliateMerchantName} / <a href="{$info.go_strAffMerchantURL}" target="_blank" >{$info.AffiliateMerchantID}</a></td>					
					<td align="center">{$info.LinkName}</td>
					<td align="center">{$info.PromotionType}</td>
					<td align="center" width="70px">{$info.StartDate}</td>
					<td align="center" width="70px">{$info.EndDate}</td>
					<td align="center" width="120px">{$info.Status}<br/>
					
						<a href="/editor/coupon.php?action=addcoupon&site=csus&pendinglink={$info.ID}&source=APL" target="_blank">[CSUS]+coupon</a><br/>
						<a href="/editor/coupon.php?action=addcoupon&site=csuk&pendinglink={$info.ID}&source=APL" target="_blank">[CSUK]+coupon</a><br/>
						<a href="/editor/coupon.php?action=addcoupon&site=csca&pendinglink={$info.ID}&source=APL" target="_blank">[CSCA]+coupon</a><br/>
						<a href="/editor/coupon.php?action=addcoupon&site=csau&pendinglink={$info.ID}&source=APL" target="_blank">[CSAU]+coupon</a><br/>
						<a href="/editor/coupon.php?action=addcoupon&site=csde&pendinglink={$info.ID}&source=APL" target="_blank">[CSDE]+coupon</a><br/>
					</td>
				</tr>
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);" style="word-break:break-all">					
					<td colspan="7">
					    <b>Code:</b> {$info.code_format|escape}<br />
					    <b>Descripton:</b> {$info.link_desc_format|escape}<br />
					    {if $hidehtml neq 1}<b>Html Code:</b> <br />
					    <textarea cols="100" rows="4">{$info.html_code_format}</textarea>&nbsp;&nbsp;{/if}<span class="htmlcodeimg">{$info.html_code_image}</span>
					</td>
				</tr>
				<Links>
				{/foreach}
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<div style="width:50%;float:left">
			</div>
			<div style="width:50%;float:right;">{$pagebar1}</div>
		</td>
	</tr>
</table>
</body>
</html>
