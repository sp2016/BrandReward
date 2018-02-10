<html>
<head>
<title>Check Program Domain Links</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>


<link href="http://bdg.mgsvc.com/admin/css/bootstrap.min.css" rel="stylesheet">
<link href="http://bdg.mgsvc.com/admin/css/front.css" rel="stylesheet">

<script src="http://bdg.mgsvc.com/admin/js/jquery.min.js"></script>
<script src="http://bdg.mgsvc.com/admin/js/bootstrap.min.js"></script>
<script src="http://bdg.mgsvc.com/admin/js/bootstrap-datetimepicker.js"></script>

<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="../js/program_search.js"></script>
<script type="text/javascript" src="../js/program.js"></script>

<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
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
<script>
function previewLPUrl(id){
	var tplObj = $("#affurl"+id).val();
	var urlObj = $("#homepage"+id).val();

	if(tplObj){
		window.open("store_rd.php?url=" + encodeURIComponent(urlObj) + "&tpl=" + encodeURIComponent(tplObj));
	}else{
		window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent(urlObj));
	}
}

function previewDeepUrl(id){
	var tplObj = $("#urltpl"+id).val();
	var urlObj = $("#homepage"+id).val();

	if(tplObj){
		window.open("store_rd.php?url=" + encodeURIComponent(urlObj) + "&tpl=" + encodeURIComponent(tplObj));
	}
}

function done(id){
	var url =  "check_program_domain_links.php?action=done";
    var remark = $('#remark'+id).val();
	var verifyArr = {'id':id,'remark':remark};
	$.ajax({type: "POST",
		url: url,
		data: $.param(verifyArr),
		success: function(msg){
			console.info(msg);			
			if(msg == "success"){
				$("#tr" + id).hide();				
			}else{
				alert(msg);
			}
		}					   
	});
}

function ignored(id){
	var url =  "check_program_domain_links.php?action=ignored";
    var remark = $('#remark'+id).val();
	var verifyArr = {'id':id,'remark':remark};
	$.ajax({type: "POST",
		url: url,
		data: $.param(verifyArr),
		success: function(msg){
			console.info(msg);			
			if(msg == "success"){
				$("#tr" + id).hide();				
			}else{
				alert(msg);
			}
		}					   
	});
}

function assignElsa(id){
	var remark = $("#remark" + id).val(); 
	var url =  "check_program_domain_links.php?action=assign";	
	var verifyArr = {'id':id, 'remark':remark};
	$.ajax({type: "POST",
		url: url,
		data: $.param(verifyArr),
		success: function(msg){
			console.info(msg);			
			if(msg == "success"){
				$("#tr" + id).hide();				
			}else{
				alert(msg);
			}
		}					   
	});
}

</script>
{/literal}
</head>
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

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Program<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_program.php" target="_blank">Program - List</a>
            </li>
          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Domain<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_merchants.php" target="_blank">Domain - List</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_outlog.php" target="_blank">OutLog</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_chk_jump_mer.php" target="_blank">Check Jump Mer</a>
            </li>
          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Affiliate<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_aff_aff.php" target="_blank">Affiliate List</a>
            </li>

          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Stats<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_performance.php?type=daily" target="_blank">Performance - Daily</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_performance.php?type=sites" target="_blank">Performance - Sites</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_performance_program.php" target="_blank">Performance - Program</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_performance.php?type=merchants" target="_blank">Performance - Merchant</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_transaction.php">Transaction</a>
            </li>

            <li>
              <a href="http://bdg.mgsvc.com/admin/b_affiliate.php" target="_blank">Affiliate Daily</a>
            </li>
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_aff_ov.php" target="_blank">Affiliate OverView</a>
            </li>
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_history_affiliate.php" target="_blank">History Affiliate</a>
            </li>
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_history_program.php" target="_blank">History Program</a>
            </li>
           <li>
              <a href="http://bdg.mgsvc.com/admin/b_history_domain.php" target="_blank">History Domain</a>
            </li>

          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Tools<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_tools_upload_transaction.php" target="_blank">Upload Transaction</a>
            </li>
            <li>
              <a href="http://bdg.mgsvc.com/admin/b_tools_currency.php" target="_blank">Currency</a>
            </li>
          </ul>
        </li>

		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
            Tickets<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
			<li>
              <a href="http://bdg.mgsvc.com/admin/old_tools/front/check_program_domain_links.php" target="_blank">Check Program Domain Links</a>
            </li>
            
            
			</ul>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="http://bdg.mgsvc.com/admin/b_admin.php">admin</a></li>
        <li><a href="http://bdg.mgsvc.com/admin/b_account.php">account</a></li>
      </ul>
    </div>
  </div>
</div>
<!-- head-top end  -->
<h1 style="text-align:center">Check Program Domain Links</h1>
<div style="margin-top:30px;">
<table class="table table-bordered">
	<tr>
		<form name="form1" action="" method="get">
		<td>
			Type: <select name="type">
				{html_options options=$typeArr selected=$type}
				
			</select>&nbsp;&nbsp;
		
			Editor: <select name="editor">
				{html_options options=$editorArr selected=$editor}
				
			</select>&nbsp;&nbsp;
			
			Affiliate: 	<input type="text" id="affiliatename" name="aff_name" value="{$aff_name}" size="30" />&nbsp;&nbsp;
						<input type="hidden" id="affiliatetype" name="aff_id" value="{$aff_id}" />						
			Program: 	<input type="text" id="program" name="program_name" value="{$program_name}" size="30" />&nbsp;&nbsp;
						<input type="hidden" id="programid" name="program_id" value="{$program_id}" size="30" />
						
			Status: <select name="status">
				{html_options options=$statusArr selected=$status}				
			</select>&nbsp;&nbsp;
						
			<input type="checkbox" name="important" value="1" {if $important == 1}checked{/if} /> only Importtant&nbsp;&nbsp;
			
			{*Add Date: 
			<input name="addtimestart" type="text" id="addtimestart" size="20" value="{$addtimestart}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}"> 
			 ~ 
			<input name="addtimeend" type="text" id="addtimeend" size="20" value="{$addtimeend}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}">&nbsp;&nbsp;
			*}
			
			<input type="submit" class="submit" value="Query">
			
		</td>
		</form>
	</tr>
	<tr>
		<td align="right">{$pagebar}</td>
	</tr>
	<tr>
		<td>
			<table class="table-bordered" style="word-break:break-all" width="100%">
				<tr height="36px">
					<th width="20%" align="center">Program</th>
					<th width="15%" align="center">Domain</th>
					<th width="45%" align="center">Type</th>	
					<th width="20%" align="center">Operation</th>					
				</tr>
				{foreach from=$data item=info}
				<tr id="tr{$info.id}" bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td>
						<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_edit.php?ID={$info.programid}" target="_blank">{$p_info[$info.programid].name} ({$p_info[$info.programid].idinaff})</a>
						<br /> - {$p_info[$info.programid].aff_name}
					</td>
					<td>						
						{if $info.errortype == 4}
							{foreach from=$p_domain[$info.programid] item=domains}
								{$domains}<br />
							{/foreach}
						{else}
							{$d_info[$info.domainid].domain}
						{/if}
						
					</td>
					<td>
						{if $info.errortype == 11}
							{$typeArr[1]}:
						{else}
							{$typeArr[$info.errortype]}:
						{/if}
						<br />
						{if $info.errortype == 1 || $info.errortype == 11}
							{*if $info.errortype == 1*}				
								{$info.AffDefaultUrl}
								<br />
								<input type='hidden' id="affurl{$info.id}" value="{$info.AffDefaultUrl}" />								
								<input type='button' value='Preview Links' onclick="javacript: return previewLPUrl('{$info.id}')" />
								<br />							
							{*/if*}
							
							{if $info.DeepUrlTpl}					
								{$info.DeepUrlTpl}
								<br />
								<input type='hidden' id="urltpl{$info.id}" value="{$info.DeepUrlTpl}" />							
								<input type='button' value='Preview Deep Tpl Links' onclick="javacript: return previewDeepUrl('{$info.id}')" />
							{/if}
							
							<input type='hidden' id="homepage{$info.id}" value="http://{$d_info[$info.domainid].domain}" />
							
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId={$info.programid}&DomainId={$info.domainid}" target="_blank">quick edit link</a>
						{elseif $info.errortype == 2 || $info.errortype == 3}
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId={$info.programid}" target="_blank">quick edit link</a>
						{elseif $info.errortype == 4}
							New Domain: <font color="green">{$info.errorvalue}</font> <br />
							Old Homepage: {$p_info[$info.programid].homepage}							
							<input type='hidden' id="homepage{$info.id}" value="{$p_info[$info.programid].homepage}" />
							<input type='button' value='Preview Links' onclick="javacript: return previewLPUrl('{$info.id}')" />
							
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_edit.php?ID={$info.programid}" target="_blank">quick edit link</a>
						{/if}
					</td>					
					<td align="left" width="60px;">
                        {if $status=="Done"||$status=="Ignored"}
                            {$status} by {$info.editor}
                        {else}
                            <a href="javascript:done('{$info.id}')">[done]</a> |||
                            <a href="javascript:ignored('{$info.id}')">[ignored]</a>
                        {/if}
						<hr />						
						{if $info.remark != '' || $editor == "elsahou" || $status=="Done" || $status=="Ignored"}
							 {$info.remark}
						{else}
							<textarea rows="2" id="remark{$info.id}" style="width:100%"></textarea>
							<br />
							<a href="javascript:assignElsa('{$info.id}')" style="float:right">[assign to Elsa]</a>
						{/if}
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
</div>
</body>
</html>