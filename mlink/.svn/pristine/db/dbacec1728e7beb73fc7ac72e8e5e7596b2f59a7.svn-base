<html>
<head>
<title>P-S Relationship Change Log</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/jquery.js"></script>
<script language="JavaScript" src="/js/My97DatePicker/WdatePicker.js"></script>
{literal}
<style type="text/css">
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;color:#000000;}
h1{font-size:16px;font-weight:bold;line-height:2.2;}
th{align:center;background:#346283;color:#FFFFFF;font-weight:bold;}
a:link,a:visited{color:#0080C0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:0 auto;height:1px;background-color:#999999;border:none;}
#poplayer{display:none;position:absolute;z-index:1337;opacity:1;background-color:#E8E8FF;padding:10px;}
.button1,.button2{padding:3px 12px; border-width:1px; border-style:solid; line-height:15px; cursor:pointer; overflow:visible;font-family:Tahoma;} 
.button1{background-color:#425f99; border-color:#5A7EC6 #2C3E5F #2C3E5F #5A7EC6; color:white;font-weight:bold;}
.button2{background-color:#ECE9D8; border-color:#eeeeee #bbbbbb #bbbbbb #eeeeee; color:#333333;}
</style>
<script>
function addColor(obj) {
		originColor = $(obj).attr("bgColor");
		$(obj).attr("bgColor", "#FFFFBB");
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

function closeLayer() {
	$("#poplayer").hide();
}

var comments = '';
function generateLayerElements(id, position) {
	comments = '';
	var elements = '';
	$("#poplayer").html('');

	if (position == 'center') {
		var parentTop = 110;
		var parentLeft = 280;
		$("#poplayer").css({display: 'block', left: parentLeft, top: parentTop});
	} else if (position == 'attach') {
		var parentTop = $("#td_" + id).offset().top;
		var parentRight = $(window).width() - $("#td_" + id).offset().left;
		$("#poplayer").css({display: 'block', right: parentRight, top: parentTop});
	}
	
    var tmpComments = '';
    if ($.trim(id) != '') tmpComments = $("#reason_" + id).text();
    
	elements += '<textarea id="comment" rows="5" cols="30">' + tmpComments + '</textarea>';
	elements += '<div style="padding:10px 0 0 0">';

	if (position == 'attach') {
		elements +='<input class="button1" type="button" value="Save" onclick="setPMLogDone(\'' + id + '\', 1)">&nbsp;&nbsp;<input class="button2" type="button" value="Cancel" onclick="setPMLogDone(\'' + id + '\', 2)">';
	} else if (position == 'center') {
		elements +='<input class="button1" type="button" value="Save" onclick="setSelectedPMLogDone(1)">&nbsp;&nbsp;<input class="button2" type="button" value="Cancel" onclick="setSelectedPMLogDone(2)">';;
	}
	
	elements += '</div>';
	
	return elements;
	
}

function setPMLogDone(id, values) {
	if (typeof values == 'undefined') {
		var layerhtml = generateLayerElements(id, 'attach');
		$("#poplayer").html(layerhtml);
		return;
	} else if (values == 1) {
		comments = $("#comment").val();
	} else if (values == 2) {
		comments = '';
		closeLayer();
		return false;
	}
    
	$.ajax({
		type: "POST",
		url: '/front/program_sotre_relationship_change_log.php',
		data: "action=ajaxsetdone&ID="+id+"&reason="+encodeURIComponent(comments),
		success: function(msg){
			if (msg == 'success') {
				alert('Operate Successfully');
				window.location.reload();
				window.opener.location.reload();
			} else {
				alert('Operate failed');
			}
		}
	});
}

function setSelectedPMLogDone(layerflag){
	var PM_log_id = "";
	$.each($("input[name^='n_id_']"),function(i,n){		
		if($(this).attr('checked')){
			if(PM_log_id == ""){
				PM_log_id += $(this).val();
			}else{
				PM_log_id += "," + $(this).val();
			}
		}
	});
	
	if(PM_log_id == ""){
		alert("Please select one item at least.");
		return false;
	}else{
		if (typeof layerflag == 'undefined') {
			var layerhtml = generateLayerElements(' ', 'center');
			$("#poplayer").html(layerhtml);
			return;
		} else if (layerflag == 1) {
			comments = $("#comment").val();
		} else if (layerflag == 2) {
			comments = '';
			closeLayer();
			return false;
		}
		
		$.ajax({
			type: "POST",
			url: '/front/program_sotre_relationship_change_log.php',
			data: "action=ajaxsetselecteddone&ID=" + PM_log_id + "&reason="+encodeURIComponent(comments),
			success: function(msg){
				if (msg == 'success') {
					alert('Operate Successfully');
					window.location.reload();
					window.opener.location.reload();
				} else {
					alert('Operate failed');
				}
			}
		});
	}
}

sflag = false;
function selectAllItems() {
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
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF"><td align="center"><h1>P-S Relationship Change Log</h1></td></tr>
	<tr>
		<form name="form1" action="" method="get">
		<td>
			<b>Filter</b> 
			Program ID: <input name="programid" type="text" value="{$programid|escape}">&nbsp;&nbsp;
			Program Name: <input name="programname" type="text" value="{$programname|escape}">&nbsp;&nbsp;
			Store ID: <input name="storeid" type="text" value="{$storeid|escape}">&nbsp;&nbsp;
			Store Name: <input name="storename" type="text" value="{$storename|escape}">&nbsp;&nbsp;
			Store Domain: <input name="storedomain" type="text" value="{$storedomain|escape}">&nbsp;&nbsp;
			&nbsp;&nbsp;Status: 
			<select name="status" id="status">
				{foreach from=$statusAll item=info key=k}
				<option value="{$k}" {if $status eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>&nbsp;&nbsp;
			Creator: 
			<select name="creator" id="creator" >
				{html_options options=$allEditor selected=$creator}
			</select>&nbsp;&nbsp;
			Operator: 
			<select name="operator" id="operator" >
				{html_options options=$allEditor selected=$operator}
			</select>&nbsp;&nbsp;
			Type: 
			<select name="type" id="type" >
				{html_options options=$logTypeArr selected=$type}
			</select>&nbsp;&nbsp;
			Operator Time: 
			<input id="operatortime_from" name="operatortime_from" value="{$operatortime_from}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'operatortime_from'})"{/literal} type="text"/>
			~ <input id="operatortime_to" name="operatortime_to" value="{$operatortime_to}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'operatortime_to'})"{/literal} type="text"  />&nbsp;&nbsp;
			Add Time: 
			<input id="addtime_from" name="addtime_from" value="{$addtime_from}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'addtime_from'})"{/literal} type="text"/>
			~ <input id="addtime_to" name="addtime_to" value="{$addtime_to}" class="Wdate" style="width:120px;" {literal}onclick="WdatePicker({el:'addtime_to'})"{/literal} type="text"  />
			&nbsp;&nbsp;
			Program Order From:
			<input id="order_from" name="order_from" value="{$order_from}" style="width:50px;" type="text"/>
			~ <input id="order_to" name="order_to" value="{$order_to}" style="width:50px;" type="text"/>
			&nbsp;&nbsp;
			<b>Order by</b>:
			<select name="order" id="order" >
				{html_options options=$orderArr selected=$order}
			</select>&nbsp;&nbsp;
			<b>Sorting By</b>:
			<select name="sort" id="sort" >
				{html_options options=$sortArr selected=$sort}
			</select>&nbsp;&nbsp;
			<input type="checkbox" value="1" name="showorder" {if $showorder != ''}checked="checked"{/if} /> Show Order Logs&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Query">
		</td>
		</form>
	</tr>
	<tr>
		<td>
			<div style="width:50%;float:left">
				<input id="sel_top" class="selall" type="checkbox" onclick="selectAllItems('selall', 'processtatuscheckbox');"><label for="sel_top"> Select All </label>&nbsp;&nbsp;<input type="button" value="Set Selected Items Done" onclick="setSelectedPMLogDone()" />
			</div>
			<div style="width:50%;float:left;">{$pagebar}</div>
		</td>
	</tr>
	<tr>
		<td>
			<table  width="100%" cellspacing="1" cellpadding="2" style="word-break:normal">
				<tr>
					<th width="10px;"></th>
					<th width="30px;">ID<hr width="90%" />Order</th>
					<th width="200px;">[Affiliate]Program</th>
					<th width="150px;">Store</th>
					<th >Value From<hr width="80%" />Value To</th>
					<th width="70px;">Add Time<hr width="90%" />Oper Time</th>
					<th width="80px;">Creator<hr width="60px" />Operator</th>
					<th width="150px;">Remark</th>
					<th width="50px;">Status</th>
					<th width="80px;">Action</th>
				</tr>
				{foreach from=$data item=info}
				<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center">{if $info.Status neq 'DONE'}<input class="processtatuscheckbox" type="checkbox" name="n_id_{$info.ID}" value="{$info.ID}" />{/if}</td>
					<td align="center">{$info.ID}<hr width="90%" />{$info.ProgramOrder}</td>
					<td align="center"><a href="program_edit.php?ID={$info.ProgramId}" target="_blank">{$info.ProgramName|escape}</a><br>[{$info.Affiliate|escape}]</td>
					<td align="center">
						<a href="store_edit_bd.php?id={$info.StoreId}" target="_blank">{$info.StoreName|escape}</a><br/>
						<a href="/front/program_sotre_relationship_change_log.php?storeid={$info.StoreId}&status=NEW" target="_blank"><span style="color:#5D0303;">Same Store's P-S Change</span></a>
					</td>
					<td align="left">{$info.ValueFrom|nl2br}<hr width="98%" />{$info.ValueTo|nl2br}</td>
					<td align="center">{$info.AddTime}{if $info.Operator}<hr />{$info.OperateTime}{/if}</td>
					<td align="center">{$info.Creator}{if $info.Operator}<hr />{$info.Operator}{/if}</td>
					<td align="left">{$info.Reason}</td>
					<td align="center">{$info.Status}</td>
					<td align="center" id="td_{$info.ID}">
						{if $info.Status neq 'DONE'}<a href="javascript:void(0);" onclick="setPMLogDone('{$info.ID}');">Set Done</a>{/if}<br/>
					</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
	<tr>
	<td>
		<div style="width:50%;float:left">&nbsp;
		{*<input id="sel_buttom" class="selall" type="checkbox" onclick="selectAllItems('selall', 'processtatuscheckbox');"><label for="sel_buttom"> Select All </label>&nbsp;&nbsp;
		<input type="button" value="Set Selected Items Done" onclick="setSelectedPMLogDone()" />*}
		</div>
		<div style="width:50%;float:left;">{$pagebar1}</div>
	</td>
	</tr>
</table>
<div id="poplayer"></div>
</body>
</html>