<html>
<head>
<title>Program List</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/program.js"></script>
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="../js/program_search.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
<link rel="stylesheet" href="../css/base.css?" type="text/css"/>
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
.contry{cursor:pointer;width:100px;}
.contry.less{ width:100px;height: 1.5em;margin-bottom: 0.5em;overflow: hidden;position: relative;}
.contry.less:after{width:100px;}
</style>
<script type="text/javascript">
function downCsv(){
	$("#down").val(1);
	document.form1.submit();
	$("#down").val(0);
}
$(function(){
	$(".contry").click(function(){
		$(this).toggleClass("less");
	});
	
});
</script>
{/literal}
</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF">
		<td align="center">
			<h1>Program List</h1>
			{if $isRemind}
			<font color="red">Has Remind</font>
			{/if}			
			[<a href="program_add.php" target="_blank">Add Program</a>]
		</td>
	</tr>
	<tr>
		<form name="form1" action="" method="get">
		<td>
			<b>Filter</b>			
			Affiliate: <input type="text" id="affiliatename" name="affiliatename" value="{$affiliatename}" size="30" />&nbsp;<input type="button" value="reset" onclick="resetAff()" />
			<input type="hidden" name="affiliatetype" id="affiliatetype" value="{$affiliatetype}" />&nbsp;&nbsp;
			{*<select name="affiliatetype" id="affiliatetype" >
			{foreach from=$affiliteTypeAllArr item=info key=k}
			<option value="{$k}" {if $affiliatetype eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;*}
			
			Program: <input type="text" id="program_search" name="name" value="{$name}" size="30" />&nbsp;&nbsp;
			
			Site: <select name="site" id="site">
				{html_options options=$siteArr selected=$site}
			</select>&nbsp;&nbsp;
			Merchant: <input type="text" id="merchantname" name="merchantname" value="{$merchantname}" size="30" />
			<input type="hidden" name="merchantid" id="merchantid" value="{$merchantid}" />&nbsp;&nbsp;
			
			Partnership Create Date: 
			<input name="createdatestart" type="text" id="createdatestart" size="20" value="{$createdatestart}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}"> 
			 ~ 
			<input name="createdatend" type="text" id="createdatend" size="20" value="{$createdatend}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}">&nbsp;&nbsp;
			
			Program Add Date: 
			<input name="addtimestart" type="text" id="addtimestart" size="20" value="{$addtimestart}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}"> 
			 ~ 
			<input name="addtimeend" type="text" id="addtimeend" size="20" value="{$addtimeend}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}">&nbsp;&nbsp;
			
			
			{if $isRemind}<input name='expireremind' type="checkbox" value="1" {if $expireremind eq 1}checked="checked"{/if} id="expireremind" onclick="this.form.submit();"><font color="red">Has Remind</font>{/if}
			&nbsp;&nbsp;&nbsp;&nbsp;			
			
			</br>
			
			Country: <select name="country" id="country">
			{foreach from=$countryAllArr item=info key=k}
			<option value="{$k}" {if $country eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			Partnership: <select name="partnership" id="partnership">
			{foreach from=$partnerShipAllArr item=info key=k}
				<option value="{$k}" {if $partnership eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			Declined by Mega: <select name="wedeclined" id="wedeclined">
				<option value="All" {if $wedeclined eq 'All'}selected="selected"{/if}>All</option>
				<option value="YES" {if $wedeclined eq 'YES'}selected="selected"{/if}>YES</option>
				<option value="NO" {if $wedeclined eq 'NO'}selected="selected"{/if}>NO</option>
				<option value="NoNeedToApply" {if $wedeclined eq 'NoNeedToApply'}selected="selected"{/if}>NoNeedToApply</option>
			</select>&nbsp;&nbsp;
			Status In Affiliate: <select name="statusinaff" id="statusinaff">
			{foreach from=$statusInAffiliateAllArr item=info key=k}
				<option value="{$k}" {if $statusinaff eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			
			P-M Relationship: <select name="hasPM">
				{html_options options=$pmArr selected=$hasPM}
			</select>&nbsp;&nbsp;
			
			P-S Relationship: <select name="hasPS">
				{html_options options=$psArr selected=$hasPS}
			</select>&nbsp;&nbsp;
			
			Cooperate With Coupon Site: <select name="hasCoop">
				<option value="All" {if $hasCoop eq 'All'}selected="selected"{/if}>All</option>
				<option value="YES" {if $hasCoop eq 'YES'}selected="selected"{/if}>YES</option>
				<option value="NO" {if $hasCoop eq 'NO'}selected="selected"{/if}>NO</option>
			</select>&nbsp;&nbsp;

			Group Inc: <select name="group">
				<option value="All">All</option>
				{html_options options=$groupArr selected=$group}
			</select>&nbsp;&nbsp;
			
			Mobile Friendly: <select name="mobilefriendly">				
				{html_options values=$mobilefriendlyArr output=$mobilefriendlyArr selected=$mobilefriendly}
			</select>&nbsp;&nbsp;
			
			<b>Sort</b>
			Order By: <select name="order" id="order">
			{foreach from=$orderbyArr item=info key=k}
			<option value="{$k}" {if $order eq $k}selected="selected"{/if}>{$info}</option>
			{/foreach}
			</select>&nbsp;&nbsp;
			
			<input type="submit" class="submit" value="Query">
			<br/>
			<input type="hidden" name="down" id="down" />
			<input type="button" value="Down" onclick="downCsv()" />
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
					<th width="140px">Program(Rank In Mega)</th>
					<th width="100px">Info In Affiliate</th>
					<th width="100px">Status<hr style="width:90%"/>Partnership</th>
					<th width="500px">P-M Relationship</th>
					<th width="100px">Country</th>
					<th width="200px">Categories</th>
					{*<th>Commission</th>*}			
					{*<th>SEM Policy</th>*}
					{*<th>Unacceptable Web Sites</th>*}
					{*<th>Coupon Codes Policy</th>*}
					<th width="150px">Remark</th>
					<th width="80px">Action</th>
				</tr>
				{foreach from=$data item=info}
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center" width="100px;">
						<a href="{$info.go_Homepage}" target="_blank">{$info.Name|escape}</a>{if $info.isremind} <font color='red'>*</font>{/if}{if $info.RevenueOrder}<font color='green'>({$info.RevenueOrder})</font>{/if}
						<hr width="95%"/>{$info.affiliatename|escape}
					</td>
					<td align="center" width="80px;">{if $info.idInAffUrl}<a href="{$info.idInAffUrl}" target="_blank">{/if}{$info.IdInAff|escape}{if $info.idInAffUrl}</a>{/if}<hr width="95%"/>Rank: <span style="font-weight:bold;">{$info.RankInAff}</span></td>
					<td align="center">
						<span style="font-weight:bold;">{$info.StatusInAff|escape}</span><br /><span style="color:#FF0000;">{$info.StatusInAffRemark|escape}</span><hr width="95%"/>
						<span style="font-weight:bold;">{$info.Partnership|escape}</span><br /><span style="color:#FF0000;">{$info.PartnershipChangeReason|escape}{if $info.WeDeclined == "YES"}(We Declined){elseif $info.WeDeclined == "NoNeedToApply"}(NoNeedToApply){/if}</span>
					</td>
					<td>
						{if !empty($info.ps)}						
						{foreach from=$info.ps item=ps}
							Store: {if $s_default[$ps.StoreId].SEM == 'YES'}<font color='red'>Has SEM</font>{/if}<br />
							[<font color="{if $ps.Status == "Active"}green{else}red{/if}">{$ps.Status}</font>]<a href="/front/store_edit_bd.php?id={$ps.StoreId}" target="_blank">{$ps.Name|escape}({$ps.StoreId})</a> 
							{if $s_default[$ps.StoreId].AffiliateDefaultUrl}
								{assign var="pid" value=$s_default[$ps.StoreId].AffiliateDefaultUrl}
								Default Url : {$p_default[$pid].AffName|escape} - <a href="/front/program_edit.php?ID={$pid}" target="_blank">{$p_default[$pid].Name|escape}</a>({$p_default[$pid].IdInAff|escape});
							{/if}
							{if $s_default[$ps.StoreId].DeepUrlTemplate}
								{assign var="pid" value=$s_default[$ps.StoreId].DeepUrlTemplate}
								Deep Url Template : {$p_default[$pid].AffName|escape} - <a href="/front/program_edit.php?ID={$pid}" target="_blank">{$p_default[$pid].Name}</a>({$p_default[$pid].IdInAff|escape})								
							{/if}
							<br />
							{if !empty($info.sm)}
							<hr>
							Merchant: <a href="/front/store_edit_bd.php?id={$ps.StoreId}#rel_info" target="_blank">S-M Rank</a><br />
							{foreach from=$info.sm key=key item=spm}								
								{if $key eq $ps.StoreId}
									{foreach from=$spm item=pm}
									[<font color="{if $pm.Status == "Active"}green{else}red{/if}">{$pm.Status}</font>]{$pm.Site|upper} - 
									<a href="{$g_SiteUrl[$pm.Site].front}/front/merchant.php?mid={$pm.MerchantId}" target="_blank">{$pm.MerchantName}</a>(<a href="/editor/merchant.php?site={$pm.Site}&merchantid={$pm.MerchantId}&act=editmerchant" target="_blank">{$pm.MerchantId}</a>)({$pm.Grade})<br />
									{/foreach}
								{/if}
							{/foreach}
							
							{if !empty($info.storeHDDRel)}
								{foreach from=$info.storeHDDRel item=storerel}
									{if $storerel.Status == 1}
										[<font color='green'>{$storerel.SiteName}</font>]<a href="{$storerel.MerchantFrontUrl}" target="_blank">{$storerel.MerchantName}</a><br />
									{/if}							
								{/foreach}
							{/if}
							<br />
							{/if}
						{/foreach}
						
						{/if}
						{*
						{if !empty($info.pm)}
						Merchant:<br />
						{foreach from=$info.pm item=pm}
							[<font color="{if $pm.Status == "Active"}green{else}red{/if}">{$pm.Status}</font>]{$pm.Site|upper} - 
							<a href="
							{if $pm.Site eq "csus"}
								http://www.promopro.com
							{elseif $pm.Site eq "csau"}
								http://www.ozdiscount.com
							{elseif $pm.Site eq "csca"}
								http://www.yessaving.ca
							{elseif $pm.Site eq "csde"}
								http://www.allecodes.de
							{elseif $pm.Site eq "csie"}
								http://www.irelandvouchercodes.com
							{elseif $pm.Site eq "csnz"}
								http://www.couponsnapshot.co.nz
							{elseif $pm.Site eq "csuk"}
								http://www.promopro.co.uk
							{/if}
							/front/merchant.php?mid={$pm.MerchantId}" target="_blank">{$pm.MerchantName}</a>(<a href="/editor/merchant.php?site={$pm.Site}&merchantid={$pm.MerchantId}&act=editmerchant" target="_blank">{$pm.MerchantId}</a>)<br />
						{/foreach}
						{/if}
						*}
					</td>
					<td align="center" width="100px"><p class="contry less">{$info.TargetCountryExt}</p><hr style="width:95%"/><p class="contry less">{$info.TargetCountryInt}</p></td>
					<td width="200px">[<span class="external">Ext</span>] {$info.CategoryExt}<hr/>[<span class="internal">Int</span>] {$info.CategoryInt}</td>
					{*<td>
						[<span class="external">Ext</span>]{$info.CommissionExt}<hr >[<span class="internal">Int</span>]{$info.CommissionInt}*}
						{*[<span class="external">Ext</span>]{$info.BonusExt}<br />[<span class="internal">Int</span>]{$info.BonusInt}<hr />
						[<span class="external">Ext</span>]{$info.ContestExt}<br />[<span class="internal">Int</span>]{$info.ContestInt}*}						
					{*</td>					
					<td>{$info.SEMPolicyRemark}</td>
					<td>{$info.UnacceptableWebSitesInt}</td>
					<td>{$info.CouponCodesPolicyInt}</td>*}
					<td>
					{if !empty($info.remarkshort)}
					<span id="l_{$info.ID}" style="display:none;">{$info.Remark} <a style="color:red;" href="javascript:void(0);" onclick="pickUp({$info.ID});">[-]</a></span>
					<span id="s_{$info.ID}" style="display:'';">{$info.remarkshort} <a style="color:red;" href="javascript:void(0);" onclick="expan({$info.ID});">[+]</a></span>
					{else}
					{$info.Remark}
					{/if}
					</td>
					<td align="center" width="60px;">
						<a href="/front/program_edit.php?ID={$info.ID}" target="_blank">Edit</a><br/>
						<a href="/editor/merchant_search.php?filter_status=&type=aff&merchantname=&affiliate={$info.AffId}&MerIDinAff={$info.IdInAff|escape}" target="_blank">Search</a><br/>
						{*<a href="/front/bd_work_log.php?action=add&pid={$info.ID}" target="_blank">Add Work Log</a>*}
						<a href="/front/bd_work_log.php?programid={$info.ID}&prgm_name={$info.Name|escape}" target="_blank">Add Work Log</a>
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