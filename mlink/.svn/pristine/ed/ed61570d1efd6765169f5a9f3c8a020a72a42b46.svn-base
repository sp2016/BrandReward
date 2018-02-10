<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />-->
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<title>Program Domain Tracking Link Config</title>
<script language="JavaScript" src="../js/jquery.js"></script>
<script language="JavaScript" src="../js/calendar_fix.js"></script>
<script language="JavaScript" src="../js/html_common.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="../js/jquery.form.js"></script>
<link href="http://bdg.mgsvc.com/admin/css/bootstrap.min.css" rel="stylesheet">
<link href="http://bdg.mgsvc.com/admin/css/front.css" rel="stylesheet">
</head>
{literal}
<style type="text/css">
.cell_label{text-align:right;font-weight:bold;background-color:#EEEEEE;border:1px solid #DDDDDD;}
.cell_value{text-align:left;border:1px solid #DDDDDD;}
td,th{font-size:14px;line-height:1.5;}

th{text-align:left;background:#1A3958;color:#FFFFFF;}
.row_odd td{background-color:#FFFFFF;}
.row_even td{background-color:#EEEEEE;border:1px solid #DDDDDD; }
a:link,a:visited{text-decoration:none;}
a:hover,a:active{text-decoration:underline;}
hr{margin:3 auto;height:1px;background-color:#999999;border:none;}
.td_value{text-align:left;background-color:#FFFFFF;border:1px solid #DDDDDD;}
.td_label{text-align:right;background-color:#EEEEEE;border:1px solid #DDDDDD;}
.sub{padding-left:10px;}
.btn_large{width:120px;height:40px;font-family:Tahoma,Arial;font-size:16px;} 
</style>
{/literal}

<body>
<!-- head-top start -->
<div class="navbar head-top">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand hidden-sm" href="http://bdg.mgsvc.com/admin">MEGAGO</a>
    </div>

    <div>
      <ul class="nav navbar-nav">
        <li class="active">
          <a href="http://bdg.mgsvc.com/admin/b_home.php">Home</a>
        </li>

       
      </ul>
     
    </div>
  </div>
</div>
<!-- head-top end  -->
<form action="program_store_edit.php" method="post" id="merchantedit_form">
<h1 style="text-align:center">Program Domain Tracking Link Config</h1>
<div class="container" style="margin-top:30px;width:1350px">
	<table class="table table-bordered" width="1300px">
		<tr>			
			<td class="td_value" width="500px" onmouseover="this.style.backgroundColor='#FBF0E3';" onmouseout="this.style.backgroundColor='#FFFFFF'">
				<table class="table table-bordered" style="word-break:break-all">
					<tr style="background-color:#FFFFFF">
						<td width="180px">Program Name:</td>
						<td width="320px"><a href="/admin/b_program_edit.php?id={$ProgramId}" target="_blank">{$prgm_info.pname}</a></td>
					</tr>
					<tr style="background-color:#FFFFFF">
						<td>Affiliate:</td>
						<td>{$prgm_info.aname} ({$prgm_info.AffId})</td>
					</tr>
					<tr style="background-color:#FFFFFF">
						<td>Program ID in Aff:</td>
						<td>{$prgm_info.IdInAff}</td>
					</tr>					
					<tr style="background-color:#FFFFFF">
						<td>Support Deep-URL:</td>
						<td style="color:{if $prgm_info.SupportDeepUrl eq "No"}red{else}green{/if}">{$prgm_info.SupportDeepUrl}</td>
					</tr>
					<tr style="background-color:#FFFFFF">
						<td>IsActive In BDG:</td>
						<td style="color:{if $prgm_info.IsActive eq "Inactive"}red{else}green{/if}">{$prgm_info.IsActive}</td>
					</tr>
					<tr style="background-color:#FFFFFF">
						<td>Program Homepage:</td>
						<td>{$prgm_info.Homepage}</td>
					</tr>	
					{if $prgm_info.AffDefaultUrl}
					<tr style="background-color:#FFFFFF">
						<td style="color:green">Affiliate Default URL:</td>
						<td>{$prgm_info.AffDefaultUrl}</td>
					</tr>	
					{/if}					
				</table>
			</td>
			<td class="td_value" width="830px" onmouseover="this.style.backgroundColor='#FBF0E3';" onmouseout="this.style.backgroundColor='#FFFFFF'">
				<table class="table table-bordered">
					<tr><td>
					Encode Url: 
					<input type="text" id="encodeurl" style="width:600px" />
					<input type="button" value="Encode URL" onclick="javascript:encodeUrl()" />
					<div id="encodeurl_div" style="display:none">
						<textarea id="encodeurl_txt" rows=2 style="width:700px"></textarea>
					</div>
					<br />
					{if $deepurltpl}
					<p style="background-color:#fffacd;border:2px solid #DDDDDD; font-size:16px">
					Remark: {$deepurltpl.Remark|replace:"\n":"<br>"}
					</p>
					{if $prgm_info.AffId eq 1}
						<a href="http://bcg.mgsvr.com/editor/cj_merchant.php?keywords={$prgm_info.pname|escape:"url"}" target="_blank">Get Program Links</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;						
						<a href="http://bcg.mgsvr.com/editor/cj_product.php?cjid={$prgm_info.IdInAff}&keywords=" target="_blank">Get Program Products</a><br /><br />
					{else}				
						<a href="../front/program_links_list.php?affiliatetype={$prgm_info.AffId}&pid={$ProgramId}" target="_blank">Get Program Links</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="../front/program_links_list.php?affiliatetype={$prgm_info.AffId}&pid={$ProgramId}&type=product" target="_blank">Get Program Products</a><br /><br />					
					{/if}
					
					<input type="hidden" id="idinaff" value="{$prgm_info.IdInAff}" />
					<input type="hidden" value="{$prgm_info.Homepage}" id="p_homepage" />
					<input type="hidden" value="{$deepurltpl.DefaultUrl}" id="suggestdefaulturl" />
					
					
					{if $deepurltpl.DefaultUrl neq ""}
					<div id="suggestdefaulturl_div" style="display:none">
						Suggest Default Url:<br />
						<textarea id="suggestdefaulturl_txt" rows=2 style="width:800px"></textarea>
					</div>
					<br />
					{/if}
					
					
					{if $deepurltpl.SupportDeepUrlTpl eq "YES"}
						{if $prgm_info.AffId eq 32 || $prgm_info.AffId eq 223 || $prgm_info.AffId eq 191 || $prgm_info.AffId eq 10}
							Deep-URL Template:
							
						{else}
							Deep-URL Template: {$deepurltpl.DeepUrlTpl}
							<br />
					
							{if $prgm_info.AffId eq 2 || $prgm_info.AffId eq 4}
								Input Landing Page Url: 
							{else}
								Input Affiliate Default Url: 
							{/if}
							<br />
							<textarea id="defaulturl" rows=2 style="width:800px"></textarea> <br />
							<input type="hidden" value="{$deepurltpl.DeepUrlTpl}" id="tpl" />
							
							{if $prgm_info.AffId eq 2}
							<input type="button" value="Generate From LS API" onclick="GenerateFromLsApi()" />
							{/if}
							<input type="button" value="Make Deep-URL" onclick="javascript:makeDeepurl()" />						
							<br />
						{/if}
						<br />
						<div id="deepurltpl" style="display:none">
							<textarea id="deepurltpl_txt" rows=3 style="width:900px"></textarea>
						</div>
					{/if}
					
					{/if}
					</td></tr>
				</table>				
			</td>
		</tr>
		<tr>			
			<td class="td_value" colspan=20 onmouseover="this.style.backgroundColor='#FBF0E3';" onmouseout="this.style.backgroundColor='#FFFFFF'">
				
				<table class="table table-bordered" id="merchantaffiateid_tr">
					<tr>						
						<th style="width:150px;">Domain (ID)</th>
						{*<th style="width:150px;">Uri</th>*}
						{*<th style="width:150px;">Special URI</th>*}				
						<th>Affiliate Default Url</th>
						<th>Deep-URL Template</th>
						{*<th style="width:90px;">Order</th>*}				
						<th style="width:90px;">Status</th>
						<th style="width:90px;">Is Fake</th>
						{*<th style="width:120px;">Source</th>*}	
						<th style="width:120px;">Action</th>
					</tr>
					{foreach from=$rel_arr item=item}{counter assign="index"}
						<tr {if $DomainId == $item.did}style="background-color:gold"{else}style="background-color:#FFFFFF"{/if}>
							<td>
								<span class="class_hidden_{$index}" id="span_store_newline_{$index}">{$item.domain} ({$item.did}) &nbsp; <a href="http://bdg.mgsvc.com/admin/b_dpl.php?id={$item.did}" target="_blank">detail</a></span>
								<span id="span_store_href_{$index}"></span>
								<span class="class_{$index}" style="display:none;">
									<input type="text" id="store_newline_{$index}" name="store_newline_{$index}" value="{$item.domain}" style="width:130px;" disabled="disabled" />								
									
									<input type="hidden" id="id_{$index}" name="id_{$index}" value="{$item.ID}" />
									<input type="hidden" id="old_store_name_{$index}" value="{$item.domain}" />
									<input type="hidden" id="old_store_id_{$index}" name="old_store_id_{$index}" value="{$item.did}" />
									<input type="hidden" id="hide_store_id_{$index}" name="hide_store_id_{$index}" value="{$item.did}" />
								</span>								 
							</td>
							{*<td>
								<span class="class_hidden_{$index}" id="span_uri_newline_{$index}">{$item.Uri|escape}</span>
								<span class="class_{$index}" style="display:none;">
									<textarea id="uri_newline_{$index}" type="text" name="uri_newline_{$index}" style="width:95%;" rows="3">{$item.Uri|escape}</textarea>
								</span>
							</td>*}
							{*<td>
								<span class="class_hidden_{$index}" id="span_domain_newline_{$index}">{$item.Domain|escape}</span>
								<span class="class_{$index}" style="display:none;">
									<input type="text" id="hide_store_domain_{$index}" name="hide_store_domain_{$index}" value="{$item.Domain|escape}" disabled="disabled" />
								</span>
							</td>*}
							<td>
								<span class="class_hidden_{$index}" id="span_defaultaffurl_newline_{$index}">{$item.AffDefaultUrl|escape}</span>
								<span class="class_{$index}" style="display:none;">
									<textarea id="defaultaffurl_newline_{$index}" type="text" name="defaultaffurl_newline_{$index}" style="width:95%;" rows="7">{$item.AffDefaultUrl|escape}</textarea>
								</span>
							</td>
							<td>
								<span class="class_hidden_{$index}" id="span_deepurltemplate_newline_{$index}">{$item.DeepUrlTpl|escape}</span>
								<span class="class_{$index}" style="display:none;">
									<textarea id="deepurltemplate_newline_{$index}" name="deepurltemplate_newline_{$index}" style="width:95%;" rows="3">{$item.DeepUrlTpl|escape}</textarea>
									<br />
									Test Landing Page URL: <textarea type="text" id="lp_url_{$index}" value="" style="width:95%;" rows="3"></textarea>					
									<input type='button' value='Preview Deep URL' onclick="javacript: return previewLPUrl('lp_url_{$index}','deepurltemplate_newline_{$index}')" />
								</span>							
							</td>
							{*<td>
								<span class="class_hidden_{$index}">{$item.Order}</span>							
							</td>*}
							<td>
								<span class="class_hidden_{$index}" id="span_status_{$index}">{$item.Status}</span>
								<input type="hidden" id="status_old_{$index}" name="status_old_{$index}" value="{$item.Status}" >
								<span class="class_{$index}" style="display:none;">
									<select id="status_{$index}" name="status_{$index}" onchange="showRemark('status_{$index}', 'status_old_{$index}', 'remark_div_{$index}')"> 
										<option value='Inactive' {if $item.Status == "Inactive"}selected{/if}>Inactive</option>
										<option value='Active' {if $item.Status == "Active"}selected{/if}>Active</option>
									</select>
									
								</span>
								<div id="remark_div_{$index}" style="display: none; right: 96px; top: 202.917px;">
									Remark:<textarea cols="30" rows="5" id="remark_{$index}" name="remark_{$index}"></textarea>
								</div>
							</td>
							<td>
								<span class="class_hidden_{$index}" id="span_isfake_{$index}">{$item.IsFake}</span>
								<input type="hidden" id="isfake_old_{$index}" name="isfake_old_{$index}" value="{$item.IsFake}" >
								<span class="class_{$index}" style="display:none;">
									<select id="isfake_{$index}" name="isfake_{$index}"> 
										<option value='NO' {if $item.IsFake == "NO"}selected{/if}>NO</option>
										<option value='YES' {if $item.IsFake == "YES"}selected{/if}>YES</option>
									</select>								
								</span>							
							</td>
							{*<td>
								<span class="class_hidden_{$index}" id="span_source_{$index}">{$item.Source}</span>								
								<input type="hidden" id="source_old_{$index}" name="source_old_{$index}" value="{$item.Source}" >
								<span class="class_{$index}" style="display:none;">
									<select id="source_{$index}" name="source_{$index}">
										<option value=''></option>
										{html_options values=$source_sel output=$source_sel selected=$item.Source}
									</select>
								</span>
							</td>*}
							<td>
								<span class="class_hidden_{$index}">
									
									<input type="button" value="Edit" onclick="editLine(this, '{$index}');"/>
									
									
									<div id="delremark_div_{$index}" style="display: none; right: 96px; top: 202.917px;">
										Remark:<textarea cols="30" rows="5" id="delremark_{$index}" name="delremark_{$index}"></textarea>
									</div>
								</span>
								<span class="class_{$index}" style="display:none;">
									<input type="button" value="Save" onclick="SaveLine('{$index}');" style="width:50px;"/>
									<input type="button" value="Cancel" onclick="CancelLine(this, '{$index}');" style="width:60px;"/>
								</span>
							</td>
						</tr>
						{foreach from=$item.merchantlist item=mer}
						<tr>
							<td></td>
							<td colspan="20">
								Site: {$mer.Site} | Merchant Id: {$mer.MerchantId} | Merchant Name: {$mer.MerchantName}							
							</td>
						</tr>
						{/foreach}
					{/foreach}
				</table>
				{*&nbsp;&nbsp;<input type="button" value="Add Related Domain" onclick="addnewprogramid();"/>*}
				<input type="hidden" id="aff_oldlinecount" name="aff_oldlinecount" value="{$rel_arr|@count}" />	  
				<input type="hidden" id="aff_maxlinenum" name="aff_maxlinenum" value="{$rel_arr|@count}" />	<br/>
				<input type="hidden" id="programid" name="programid" value="{$ProgramId}" />
				<input type="hidden" id="affid" value="{$prgm_info.AffId}" />
				<div id="source_sel" style="display:none">{html_options values=$source_sel output=$source_sel selected=$edit_ps_source}</div>
				<div style="width:100%;background-color:#ddd;">
				Hint for <b>Deep-URL Template</b>:<br/>
				1. Leave blank Or Starts with http://<br/>
				2. Totally 8 Macro Variables supported:<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[PURE_DEEPURL]</b> will be replaced by <span style="color:blue;">real URL</span>.<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[DEEPURL]</b> will be replaced by <span style="color:blue;">real URL</span> with <span style="color:red;">1 time URL-Encoded</span>.<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[DOUBLE_ENCODE_DEEPURL]</b> will be replaced by <span style="color:blue;">real URL</span> with <span style="color:red;">2 time URL-Encoded</span>.<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[URI]</b> will be replaced by <span style="color:blue;">real URL</span> <span style="color:red;">without domain</span>. ex. http://www.zanui.com.au<b>/Carlo-3-Seater-Brown-17058.html</b><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[ENCODE_URI]</b> will be replaced by <span style="color:blue;">URI</span> with <span style="color:red;">1 time URL-Encoded</span>.<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[DOUBLE_ENCODE_URI]</b> will be replaced by <span style="color:blue;">URI</span> with <span style="color:red;">2 time URL-Encoded</span>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;<b>[SUBTRACKING]</b> will be replaced by <span style="color:blue;">real Sub-Tracking value</span> defined in Affiliate. This is used for some affiliate having special URL re-write rule.<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<b>[?|&]</b>: Some In-House Affiliates' Deep-URL Template is like <span style="color:blue;">'[DEEPURL]?affid=abc'</span>. If you are unsure which one to use - <b>'?'</b> or <b>'&'</b>, please use <b>[?|&]</b>. System will replace <b>'[?|&]'</b> automatically when necessary. If you use <b>'?'</b> or <b>'&'</b> specifically, the system will <b>NOT</b> replace it automatically.<br/>
				</div>
			</td>
		</tr>

		
	</table>
</div>	
</form>

{literal}
<script language="JavaScript">
	function addnewprogramid(){		
		var aff_maxlinenum = $("#aff_maxlinenum").val();
		aff_maxlinenum = parseInt(aff_maxlinenum) + 1;
		$("#aff_maxlinenum").val(aff_maxlinenum);

		$("#merchantaffiateid_tr").append(
			"<tr id=tr_" + aff_maxlinenum + " align='left' style='background-color:#BFE484'>"
			+ "<td><input type='text' id='store_newline_" + aff_maxlinenum + "' name='store_newline_" + aff_maxlinenum +  "' style='width:130px;'/></td>"			
			+ "<td><input type='text' id='defaultaffurl_newline_" + aff_maxlinenum + "' name='defaultaffurl_newline_" + aff_maxlinenum + "' style='width:95%;'/></td>"
			+ "<td><input type='text' id='deepurltemplate_newline_" + aff_maxlinenum + "' name='deepurltemplate_newline_" + aff_maxlinenum + "' style='width:95%;'/></td>"
			+ "<td><select id='status_" + aff_maxlinenum + "' name='status_" + aff_maxlinenum + "'><option value='Active'>Active</option><option value='Inactive'>Inactive</option></select></td>"
			+ "<td><select id='isfake_" + aff_maxlinenum + "' name='isfake_" + aff_maxlinenum + "'><option value='NO'>NO</option><option value='YES'>YES</option></select></td>"
			+ "<td align='center'>"
			+ "<input type='button' name='desc" + aff_maxlinenum + "' id='desc" + aff_maxlinenum + "' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"
			+ "<input type='hidden' id='hide_store_id_" + aff_maxlinenum + "' name='hide_store_id_" + aff_maxlinenum + "' />"				
			+ "</td>"
			+ "</tr>");

		$("#store_newline_" + aff_maxlinenum).autocomplete('../front/program_search.php?ajaxTag=searchStore', {
			scrollHeight: 320,
			max: 3000,
			formatItem: formatItem,
			formatResult: formatPrgmResult,
			extraReceiveData : new Array("hide_store_id_" + aff_maxlinenum),
			autoFill: false
		});		
	}

	function removeAff(aff_maxlinenum){		
		$("#tr_" + aff_maxlinenum).remove();		
	}

	function save(){
		var store_str = "";
		var store_id = 0;
		var is_check = true;
		$.each($("input[id^='hide_store_id_']"), function(i, n){
			store_id = $(n).val();
			if(store_id > 0){
				if(store_str.indexOf("-"+store_id+"-") >=0){
					alert("Please check Store, the same store is not allowed.");
					is_check = false;
					return false;
				}else{
					store_str += "-"+$(n).val()+"-";
				}
			}
		});
		var oldMax = parseInt($("#aff_oldlinecount").val(), 10);
		var i = 0;
		for(i = 1; i<= oldMax; i++){
			var remark = $("#remark_" + i).val().trim();
			if(!checkValue(i) && remark == "" && $("#status_old_" + i).val() == "Active"){
				alert("Status changed,Please input remark.");
				$("#remark_div_" + i).show();
				$("#remark_" + i).focus();
				return false;
			}

			if($("#aff_chks_" + i).attr("checked") == true && $("#delremark_" + i).val().trim() == ""){
				alert("Remove elationship, Please input remark.");
				$("#delremark_div_" + i).show();
				$("#delremark_" + i).focus();
				return false;
			}

			if(!$("#defaultaffurl_newline_" + i).val().IsStartWithHttp() && $("#defaultaffurl_newline_" + i).val().trim() != ""){
				//alert("Affiliate Default Url must starts with http://");
				if(!confirm("Please confirm that Affiliate Default Url not start with http://"))
				{
					$("#defaultaffurl_newline_" + i).focus();
					return false;
				}
				
			}

			if(!$("#deepurltemplate_newline_" + i).val().IsStartWithHttp() && $("#deepurltemplate_newline_" + i).val().trim() != ""){
				if(!confirm("Please confirm that Deep-URL Template not start with http://"))
				{
					$("#deepurltemplate_newline_" + i).focus();
					return false;
				}
//				alert("Deep-URL Template must starts with http://");
			}
		}

		//POR
		var checkPOR = false;
		if($("#affid").val() == 29){
			var s_max = $("#aff_maxlinenum").val();			
			var re = /\/0(?=\/|$)/g;
			
			for(i = 1; i<= s_max; i++){
				var storeid = $("#hide_store_id_" + i).val();
				if(store_id > 0){
					//alert(store_id);
					var deepurl  = $("#deepurltemplate_newline_" + i).val();
					var defaultaffurl = $("#defaultaffurl_newline_" + i).val();
					
					r = deepurl.match(re);
					if(r != null && r.length > 1){
						checkPOR = true;
					}
					r = defaultaffurl.match(re);				
					if(r != null && r.length > 1){
						checkPOR = true;
					}
				}
			}
		}

		if(checkPOR){
			if(!confirm("Notice: Url has more than one /0 parameter, on this condition the 0 part won't be replaced by [SUBTRACKING]. Are you sure ?")){
				return false;
			}
		}
		
		if(is_check){
			$('#merchantedit_form').submit();
		}
		return false;
	}	

	function SaveLine(index){
		var status = $("#status_" + index).val();                //当前status
		var storeid = $("#hide_store_id_" + index).val();		 //did
		var oldstoreid = $("#old_store_id_" + index).val();		 //did
		var programid = $("#programid").val();	
		var uri = $("#uri_newline_" + index).val()
		var deepurl  = $("#deepurltemplate_newline_" + index).val();
		var defaultaffurl = $("#defaultaffurl_newline_" + index).val();
		var id = $("#id_" + index).val();	
		var remark = $("#remark_" + index).val();
		var isfake = $("#isfake_" + index).val();
		var source = $("#source_" + index).val();
		if(!checkValue(index) && $("#remark_" + index).val().trim() == "" && status == "Inactive"){  //checkValue函数用于判断status是否改变
			$("#remark_div_" + index).show();
			alert("Status changed,Please input remark.");
			$("#remark_" + index).focus();
			return false;
		}

		if(!defaultaffurl.IsStartWithHttp() && defaultaffurl.trim() != "")
		{
			//alert("Affiliate Default Url must starts with http://");
			if(!confirm("Please confirm that Affiliate Default Url not start with http://"))
			{
				$("#defaultaffurl_newline_" + index).focus();
				return false;
			}
		}
		if(!deepurl.IsStartWithHttp() && deepurl.trim() != "")
		{
			//alert("Deep-URL Template must starts with http://");
			if(!confirm("Please confirm that Deep-URL Template Url not start with http://"))
			{
				$("#deepurltemplate_newline_" + index).focus();
				return false;
			}
		}

		//POR
		var checkPOR = false;
		if($("#affid").val() == 29){
			var re = /\/0(?=\/|$)/g;
			r = deepurl.match(re);
			if(r != null && r.length > 1){
				checkPOR = true;
			}else{
				deepurl = deepurl.replace(re, "/[SUBTRACKING]");    
			}
			r = defaultaffurl.match(re);				
			if(r != null && r.length > 1){
				checkPOR = true;
			}else{
				defaultaffurl = defaultaffurl.replace(re, "/[SUBTRACKING]");    
			}
		}

		if(checkPOR){
			if(!confirm("Notice: Url has more than one /0 parameter, on this condition the 0 part won't be replaced by [SUBTRACKING]. Are you sure ?")){
				return false;
			}
		}
		
		$("#remark_div_" + index).hide();
		var url =  "../front/program_store_edit.php?action=saveLine";	
		var verifyArr = {'id':id, 'uri':uri, 'storeid':storeid, 'oldstoreid':oldstoreid, 'deepurl':deepurl, 'defaultaffurl':defaultaffurl, 'status':status, 'programid':programid, 'remark':remark, 'isfake':isfake, 'source':source};
		$.ajax({type: "POST",
			url: url,
			data: $.param(verifyArr),                 //param()是jquery函数，用于序列化一个数组或者对象
			success: function(msg){
				console.info(msg);
				if(msg == "pmcheckerror"){
					alert("Program Merchant Relationship is incorrect, please fix it first.");
					window.open("../front/program_store_correct_edit.php?storeid=" + storeid);
					return false;
				}
				if(msg == "success"){
					$("#span_status_" + index).text(status);
					$("#span_isfake_" + index).text(isfake);
					$("#span_source_" + index).text(source);
					//$("#span_domain_newline_" + index).text($("#hide_store_domain_" +index).val().trim());
					$("#span_store_newline_" + index).text($("#store_newline_" +index).val().trim() + " (" + storeid + ")");
					$("#store_newline_" + index).val($("#old_store_name_" +index).val().trim());
					$("#old_store_id_" + index).val(storeid);
					$("#hide_store_id_" + index).val(storeid);
					$("#status_old_" + index).val(status);
					$("#isfake_old_" + index).val(isfake);
					$("#source_old_" + index).val(source);
					$("#span_uri_newline_" + index).text(uri);
					$("#remark_" + index).val("");
					//$("#span_deepurltemplate_newline_" + index).text($("#deepurltemplate_newline_" + index).val());
					//$("#span_defaultaffurl_newline_" + index).text($("#defaultaffurl_newline_" + index).val());
					$("#span_deepurltemplate_newline_" + index).text(deepurl);
					$("#span_defaultaffurl_newline_" + index).text(defaultaffurl);
					
					$(".class_hidden_" + index).show();
					$(".class_" + index).hide();
					$("#aff_chks_" + index).show();
					$("#span_store_href_" + index).html('&nbsp;<a target="_blank" href="../front/store_edit_bd.php?id=' + $("#hide_store_id_" + index).val() + '">Edit</a>');
				}else{
					alert(msg);
				}
			}					   
		});
		
	}
	
	
	function CancelLine(e, index){

		
		$(".class_hidden_" + index).show();
		$(".class_" + index).hide();
		$("#aff_chks_" + index).show();
		
		$("#store_newline_" + index).val($("#old_store_name_" +index).val().trim());
		//$("#hide_store_domain_" + index).val($("#span_domain_newline_" +index).text().trim());
		$("#hide_store_id_" + index).val($("#old_store_id_" +index).val().trim());	
		
		$("#deepurltemplate_newline_" + index).val($("#span_deepurltemplate_newline_" +index).text().trim());
		$("#defaultaffurl_newline_" + index).val($("#span_defaultaffurl_newline_" +index).text().trim());
		
		$("#remark_div_" + index).hide();
		$(e).parent().parent().parent().children("td").each(function (){$(this).css('background-color', '#FFFFFF')});
	}
	
	
	function editLine(e, index){	
		$(".class_hidden_" + index).hide();
		$(".class_" + index).show();
		$("#aff_chks_" + index).hide();
		if($("#status_" + index).val()== "Inactive" && $("#status_old_" + index).val()== "Active"){
			$("#remark_div_" + index).show();
		}
		$(e).parent().parent().parent().children("td").each(function (){$(this).css('background-color', '#FD9797')});
	}
	
	function activeAutocomplete(){
		var num = $("#aff_oldlinecount").val();	
		for(i=0; i<num; i++){
			$("#store_newline_" + i).autocomplete('/front/program_search.php?ajaxTag=searchStore', {
				scrollHeight: 320,
				max: 3000,
				formatItem: formatItem,
				formatResult: formatPrgmResult,
				extraReceiveData : new Array("hide_store_id_" + i),
				autoFill: false
			});
		}
	}
	
	function formatItem(row) {
		return row[1] + "(" + row[0] + ")";
	}
	
	function formatResult(row) {
		return row[0];
	}
	
	function formatPrgmResult(row){
		return row[1] + "|||" + row[0] + "|||" + row[2];
	}
	
	$(document).ready(function(){
		activeAutocomplete();
	});

	function showRemark(statusId, statusOldId, remarkDivId){
		
		if($("#" +statusId).val() != $("#" + statusOldId).val() && $("#" +statusId).val() == "Inactive"){
			$("#" + remarkDivId).show();
		}else{
			$("#" + remarkDivId).hide();
		}
	}

	function showDelRemark(id){		
		if($("#aff_chks_" + id).attr("checked") == true){
			$("#delremark_div_" + id).show();
		}else{
			$("#delremark_div_" + id).hide();
		}
	}

	function checkValue(id){
		if($("#status_old_" +id).val() != $("#status_" + id).val()){
			return false;
		}
		return true;
	}

	function previewLPUrl(url, tpl){
		var urlObj = document.getElementById(url);
		var tplObj = document.getElementById(tpl);

		var re = /\[PURE_DEEPURL\]/;
		
		if(urlObj.value != "" && urlObj.value.trim().IsStartWithHttp() && tplObj.value != "" && (tplObj.value.trim().IsStartWithHttp() || tplObj.value.search(re) != "-1")){
			window.open("../front/store_rd.php?url=" + encodeURIComponent(urlObj.value) + "&tpl=" + encodeURIComponent(tplObj.value));
		}else{
			alert("Landing Page URL & Deep-URL Template must starts with http://");
			urlObj.focus();
			return false;
		}
	}

	function makeDeepurl(){
	{/literal}
		{if $prgm_info.AffId eq 10}		
			makeDeepurlAW();
		{elseif $prgm_info.AffId eq 7}
			makeDeepurlSAS();
		{elseif $prgm_info.AffId eq 46}
			makeDeepurlclixGalore();
		{elseif $prgm_info.AffId eq 52 || $prgm_info.AffId eq 65}
			makeDeepurlTradeTracker();
		{elseif $prgm_info.AffId eq 13 || $prgm_info.AffId eq 14 || $prgm_info.AffId eq 18  || $prgm_info.AffId eq 34}
			makeDeepurlWebgains();
		{elseif $prgm_info.AffId eq 2 || $prgm_info.AffId eq 4}
			makeDeepurlLS();
		{elseif $prgm_info.AffId eq 5 || $prgm_info.AffId eq 27 || $prgm_info.AffId eq 35  || $prgm_info.AffId eq 133 || $prgm_info.AffId eq 415}
			makeDeepurlTradeDoubler();
		{else}
			makeDeepurlNoraml();
		{/if}
		formatDeepurl();
	{literal}
	}

	function formatDeepurl(){
		var tpl = $("#deepurltpl_txt").val();
		var re = /\s+/;
		tpl = tpl.replace(re, "");
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlNoraml(){
		var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();
		var re = /\[DEFAULTURL\]\+/;
		tpl = tpl.replace(re, defaulturl);
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlSAS(){
		var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();

		var re = /\bb=.*\bu=/i;
		var r = defaulturl.search(re);
		if(r > 0){
			var re = /\bu=[^&]+/i;
			var u = defaulturl.match(re);
			defaulturl = defaulturl.replace(re, "#u#");

			var re = /\bb=[^&]+/i;
			var b = defaulturl.match(re);
			defaulturl = defaulturl.replace(re, "#b#");

			defaulturl = defaulturl.replace("#u#", b);
			defaulturl = defaulturl.replace("#b#", u);
		}

		var re = /&afftrack=.*/i;
		/*var r = defaulturl.search(re);
		if(r < 1){
			defaulturl += "&afftrack=";
		}*/
		defaulturl = defaulturl.replace(re, "");
		

		var re = /&urllink=.*/i;
		/*var r = defaulturl.search(re);
		if(r < 1){
			defaulturl += "&urllink=";
		}*/
		defaulturl = defaulturl.replace(re, "");

		//var re = /\burllink=.*/i;		
		//defaulturl = defaulturl.replace(re, "urllink=");

		defaulturl += "&afftrack=&urllink=";

		var re = /\[DEFAULTURL\]\+/;
		tpl = tpl.replace(re, defaulturl);
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlclixGalore(){
		var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();

		var re = /&lp=[^&]+/i;
		var pos = defaulturl.match(re);
		//pos = pos.toString();		
		
		defaulturl = defaulturl.replace(re, "");

		var re = /\[DEFAULTURL\]\+/;
		tpl = tpl.replace(re, defaulturl);

		if(pos != null){
			tpl += pos;
		}
		
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlAW(){
		/*var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();

		re = /\bmid=/i;
		defaulturl = defaulturl.replace(re, "awinmid=");
		re = /\bid=/i;
		defaulturl = defaulturl.replace(re, "awinaffid=");

		re = /\[DEFAULTURL\]\+/;
		tpl = tpl.replace(re, defaulturl);
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);*/

		var idinaff = $("#idinaff").val();
		var tpl = "http://www.awin1.com/cread.php?awinmid="+idinaff+"&awinaffid=80151&clickref=&p=[DEEPURL]";
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlWebgains(){
		var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();
		var idinaff = $("#idinaff").val();

		var re = /\bwglinkid=\d+/i;
		var r = defaulturl.search(re);

		if(r > 0){
			re = /\bwglinkid=\d+&/i;
			defaulturl = defaulturl.replace(re, "");
			defaulturl += "&wgprogramid=" + idinaff;
		}

		re = /\[DEFAULTURL\]\+/;
		tpl = tpl.replace(re, defaulturl);
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlLS(){
		var idinaff = $("#idinaff").val();
		var re = /_\d+/i;
		idinaff = idinaff.replace(re, "");		
		var tpl = "http://click.linksynergy.com/deeplink?id=AeuDahFBnDk&mid="+idinaff+"&murl=[DEEPURL]";
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlTradeDoubler(){
		var defaulturl = $("#defaulturl").val();
		var tpl = "";

		var re = /[()]/i;
		var r = defaulturl.search(re);

		if(r > 0){
			makeDeepurlNoraml();
		}else{			
			tpl = defaulturl + "&url=[DEEPURL]";
			$("#deepurltpl").show();
			$("#deepurltpl_txt").val(tpl);
		}
	}

	function makeDeepurlTradeTracker(){
		var defaulturl = $("#defaulturl").val();
		var tpl = $("#tpl").val();

		var re = /tc.tradetracker.net/i;
		var r = defaulturl.search(re);

		if(r > 0){
			makeDeepurlNoraml();
		}else{
			var re = /(\?tt=[^&]+)/i;
			var pos = defaulturl.match(re);
			
			defaulturl = defaulturl.replace(re, "$1" + "_[SUBTRACKING]");

			var re = /_\d+_/;
			defaulturl = defaulturl.replace(re, "_12_");

			var re = /(_\[SUBTRACKING\])\1/g;
			defaulturl = defaulturl.replace(re, "$1");

			var re = /__/g;
			defaulturl = defaulturl.replace(re, "_");
	
			var re = /\[DEFAULTURL\]\+/;
			tpl = tpl.replace(re, defaulturl);
			$("#deepurltpl").show();
			$("#deepurltpl_txt").val(tpl);
		}
	}

	function makeDeepurlAG(){		
		var idinaff = $("#idinaff").val();
		var tpl = "https://secure.avangate.com/affiliate.php?ACCOUNT="+idinaff+"&AFFILIATE=9792&PATH=[DEEPURL]";
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlSkimlinks(){		
		var tpl = "http://go.redirectingat.com?id=7438X662619&xcust=[SUBTRACKING]&xs=1&url=[DEEPURL]";
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDeepurlViglink(){
		var tpl = "http://redirect.viglink.com?key=cad6cf4a614403969204fb78b3f0b467&u=[DEEPURL]";
		$("#deepurltpl").show();
		$("#deepurltpl_txt").val(tpl);
	}

	function makeDefaultUrl(){
		var idinaff = $("#idinaff").val();
		var defaulturl = $("#suggestdefaulturl").val();
		var homepage = $("#p_homepage").val();

		if(defaulturl.length > 0){
			re = /\[IDINAFF\]/g;
			defaulturl = defaulturl.replace(re, idinaff);

			re = /\[DEEPHOMEPAGE\]/g;
			homepage = encodeURIComponent(homepage);
			defaulturl = defaulturl.replace(re, homepage);
			
			$("#suggestdefaulturl_div").show();
			$("#suggestdefaulturl_txt").val(defaulturl);
		}
	}

	function encodeUrl(){		
		var encodeurl = $("#encodeurl").val();
		
		encodeurl = encodeURIComponent(encodeurl);		
		
		$("#encodeurl_div").show();
		$("#encodeurl_txt").val(encodeurl);		
	}

	function GenerateFromLsApi(){
		var idinaff = $("#idinaff").val();
		var dsturl = $("#defaulturl").val();
		if(dsturl.trim() == ""){
			alert("Please input Landing Page URL first.");
			return false;
		}
		var dsturl = encodeURIComponent(dsturl);
		dsturl = dsturl.replace("&", "%26");
		$("#url_load").remove();
		$("#deepurltpl").after('<img id="url_load" style="" src="../image/loading.gif">');

		var verifyArr = new Array();
		var url = "../ajax/deepurl_ls.php?am=" + idinaff + "&du=" + dsturl ; 
//		alert(url);
		$.ajax({
			type: "get",
			url: url,
			data: $.param(verifyArr),
			success: function (msg) {
				msg = JSON.parse(msg);
				if(msg.status == "true"){
					$("#deepurltpl").show();

					var re = /&RD_PARM1=([^&]+)/i;				
					var deepurltpl = msg.url.replace(re, "&RD_PARM1=[DEEPURL]");
					
					$("#deepurltpl_txt").val(deepurltpl);
					//alert("Generate AFF URL successfully; please preview AFF URL to make sure it works well.");					
				}else{					
					alert(msg.message);
				}
				$("#url_load").remove();
			},
			error: function () {
				alert("Generate AFF URL failed.");
				$("#url_load").remove();
			}	
		});
	}
	
	$().ready(function(){
	{/literal}
		makeDefaultUrl();
		
		{if $prgm_info.AffId eq 191}
			makeDeepurlViglink();
		{elseif $prgm_info.AffId eq 223}
			makeDeepurlSkimlinks();		
		{elseif $prgm_info.AffId eq 10}
			makeDeepurlAW();
		{elseif $prgm_info.AffId eq 32}		
			makeDeepurlAG();
		{/if}
	{literal}
	})
	
</script>
{/literal}
</body>
</html>
