<html>
<head>
<title>Invalid Link Report</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/store.js"></script>
<script type="text/javascript" src="../js/imgchange.js"></script>
<link href="../css/colorbox.css" rel="stylesheet">
<script type="text/javascript" src="../js/jquery.colorbox.js"></script>
<script type="text/javascript" src="../js/jquery.form.js" language="javascript"></script>
<script language="JavaScript" src="../js/My97DatePicker/WdatePicker.js"></script>
<link rel="stylesheet" rev="stylesheet" href="../css/base.css" type="text/css" media="all" />
{literal}
<style type="text/css">
th{align:center;background:#B7036A;color:#FFFFFF;font-weight:bold;}
</style>
<script>
$(function() {
	$(".view_detail").colorbox({href:"#detail", inline:true,opacity:0.5,  width:"680px",height:"338px",title:"Creative Details",scrolling:false,onOpen:function(){$("#detail_div").html($(this).attr("title"));	}});
	$('#contentform').ajaxForm({
		url:"affiliate_invalid_link.php?action=content",
		beforeSubmit: function(a,f,o) {
			o.dataType = "html";
			var Comment = $("#Comment").val();
			var id = $("#content_id").val();
			if(Comment.length < 1){
				alert("Please input the Comment.");
				return false;
			}
		},
		success: function(data) {
			if(data== "success"){
				alert("Set to Content successfully!");
				location.reload();
			}else{
				alert(data);
			}
		}
	});
});
function setStutus(id,status)
{
	if(!confirm("Set This Recond "+status+"?")) return false;
	var ajax_url = "/front/affiliate_invalid_link.php?action=stutus&status="+status+"&id=" + id;
	$.get(ajax_url,function(msg){
		if(msg!= "success")
		{
			alert("set "+status+" failed.");
		}
		else
		{
			alert("Set to "+status+" successfully!");
			location.reload();
		}
	});
}
function batchSetStutus(id,status)
{	
	var ajax_url = "/front/affiliate_invalid_link.php?action=stutus&status="+status+"&id=" + id;
	return $.get(ajax_url,function(msg){
		if(msg!= "success")
		{
			alert("set "+status+" failed.");
		}
	});
}
function markSelectedLinks() {
	var _flag = false;
	var ajax_url = "";
	var status=arguments[1];
	var succes_num=0;
	var lost_num=0;

		$(".processtatuscheckbox").each(function(){
			if ($(this).attr('checked') == true) {
				_flag = true;
				return;
			}
		});
		
		if (_flag != true) {
			alert('Please select links first!');
			return false;
		}
		if(!confirm("assign all checked link to "+status+"?")) return false;
		$(".processtatuscheckbox").each(function(i){
			if ($(this).attr('checked') == true) {
				batchSetStutus($(this).val(),status);
			}
		});
		alert("assign all checked link to "+status+" complete.");
		location.reload();
}

sflag = false;
function selectAllLinks() {
	if (sflag == true) {
		sflag = false;
	} else {
		$("." + arguments[0]).each(function(){
			if ($(this).attr("checked") == true) {
				sflag = true;
				return;
			}
		});
	}
	
	if (sflag) {
		$("." + arguments[0]).attr('checked', 'checked');
		$("." + arguments[1]).attr('checked', 'checked');
	} else {
		$("." + arguments[0]).removeAttr('checked');
		$("." + arguments[1]).removeAttr('checked');
	}
	
	
}
</script>
{/literal}
</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#FAD8FA">
	<tr bgcolor="#FFFFFF"><td align="center"><h1>Invalid Link Report</h1></td></tr>
	<tr>
		<form name="form1" action="" method="get">
	  <td >
			<b>Filter</b> 
			Status: <select name="status" id="status">
		    {foreach from=$status_all_arr item=info key=k}
					<option value="{$k}" {if $status eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>&nbsp;&nbsp;
			Affiliate: <select name="affiliate" id="affiliate">
		    {foreach from=$aff_all_arr item=info key=k}
					<option value="{$k}" {if $affiliate eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>&nbsp;&nbsp;
			CSSites: <select name="CSSites" id="CSSites">
		    {foreach from=$CSSites_arr item=info key=k}
					<option value="{$k}" {if $CSSites eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>&nbsp;&nbsp;
			<b>Add Date:</b>
					<input id="addtime_from" name="addtime_from" value="{$addtime_from}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'addtime_from',dateFmt:'yyyy-MM-dd HH:mm:ss',startDate:'%y-%M-01 00:00:00'})"{/literal} type="text"/>
					~ <input id="addtime_to" name="addtime_to" value="{$addtime_to}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'addtime_to',dateFmt:'yyyy-MM-dd HH:mm:ss',startDate:'%y-%M-01 00:00:00'})"{/literal} type="text"  />
					&nbsp;&nbsp;
			<b>Keyword:</b>
					<input id="keyword" name="keyword" value="{$keyword}" style="width:150px;" type="text"/>
					&nbsp;&nbsp;
			<b>Sort</b>
			Order By: <select name="order" id="order" onchange='this.form.submit();'>
				{foreach from=$ordey_arr item=info key=k}
					<option value="{$k}" {if $order eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>
			&nbsp;&nbsp;<input type="submit" value="Query">
	  </td>
		</form>
	</tr>
	<tr>
		<td>
			<div style="width:50%;float:left">
				<input id="sel_top" class="selall" type="checkbox" onclick="selectAllLinks('selall', 'processtatuscheckbox');"><label for="sel_top"> Select All Links </label>&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="button" onclick="markSelectedLinks('batch', 'DONE');">Mark Selected Links as Done</button>
				<button type="button" onclick="markSelectedLinks('batch', 'IGNORED');">Mark Selected Links as Ignored</button>
			</div>
			<div style="width:50%;float:left;">{$pagebar}</div>
		</td>
	<tr> 
		<td>
			<table  width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all">
				<tr>
					<th width="10px"></th>
					<th width="70px">Add Time</th>
					<th width="150px">Affiliate<hr style="width:90%">Program</th>
					<!-- <th width="150px">Program<hr style="width:90%">Affiliation Status</th> -->
					<th width="70px">Link ID</th>
					<th width="350px">Referral</th>
					<th width="60px">Occurred</th>
					<th width="200px">Creative Type<hr style="width:90%">Reason</th>
					<th width="250px">CSSites | Merchant Name<hr style="width:90%">Merchant Landing Page</th>
					<th width="120px">CS Merchant</th>
					<th width="60px">Status</th>	
					<th width="100px">Action</th>
				</tr>
				{foreach from=$data item=info}
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);" >
					<td align="center"><input class="processtatuscheckbox" id="c_{$info.ID}" type="checkbox" value="{$info.ID}" /></td>
					<td align="center">{$info.AddTime}</td>
					<td>({$info.affiliate}){$aff_all_arr[$info.affiliate]}<hr style="width:95%">({$info.ProgramID}){$info.ProgramName}[{$info.AffiliationStatus}]</td>
					<!-- <td>({$info.ProgramID}){$info.ProgramName}<hr style="width:95%">{$info.AffiliationStatus}</td> -->
					<td align="center">{$info.LinkID}{if $info.Details}<hr style="width:95%"><a href="#" class="view_detail" title="{$info.Details|escape}">Details</a>{/if}</td>
					<td>{if $info.ReferralUrl}<a href="{$info.ReferralUrl}" target="_blank">{$info.ReferralUrl}</a>{/if}</td>
					<td align="center">{if $info.OccuredDate && $info.OccuredDate neq "0000-00-00 00:00:00"}{$info.OccuredDate}{/if}<hr>{$info.Clicks}</td>	
					<td>{$info.CreativeType}<hr>{$info.Reason}</td>
					<td>{$info.CSSites} | {$info.MerchantName}<hr style="width:95%">{$info.MerLandingPage}</td>
					<td style="word-break:normal">{if $info.CsMerchantSite}[{$info.CsMerchantSite|upper}] {/if}{if $info.CsMerchantId}<a href="{$info.CsMerchantPage}" target="_blank">{$info.CsMerchantName}</a><br/>(<a href="/editor/coupon_list.php?merchant={$info.CsMerchantId}&site={$info.CsMerchantSite|lower}" target="_blank">Coupon List</a>){/if}
					</td>
					<td align="center">{$info.Status}</td>
					<td align="center">
						{if $info.Status eq "NEW"}[<a href="#" onclick="setStutus('{$info.ID}','IGNORED');return false;">Set Ignored</a>]<br />{/if}						
						{if $info.Status eq "NEW"}[<a href="#" onclick="setStutus('{$info.ID}','DONE');return false;">Set to Done</a>]<br />{/if}						
					</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
	<tr>
	<td>
		<div style="width:50%;float:left">
			<input id="sel_top" class="selall" type="checkbox" onclick="selectAllLinks('selall', 'processtatuscheckbox');"><label for="sel_top"> Select All Links </label>&nbsp;&nbsp;&nbsp;&nbsp;
			<button type="button" onclick="markSelectedLinks('batch', 'DONE');">Mark Selected Links as Done</button>
			<button type="button" onclick="markSelectedLinks('batch', 'IGNORED');">Mark Selected Links as Ignored</button>
		</div>
		<div style="width:50%;float:left;">{$pagebar1}</div>
	</td>
  </tr>
</table>
<!-- View Detail -->
<div style='display:none'>
	<div id='detail' class="detail">
	<table width="99%"  border="0" cellpadding="5" cellspacing="1" bgcolor="#E8E8FF" align="center" style="border:1px solid #DDDDDD">
		<tr bgcolor="#FFF2C1" style="height:40px;">
			<td class="td_value">
			<div id="detail_div"></div>
			</td>
		</tr>
	</table>
	</div>
</div>
<!-- View Detail -->
</body>
</html>