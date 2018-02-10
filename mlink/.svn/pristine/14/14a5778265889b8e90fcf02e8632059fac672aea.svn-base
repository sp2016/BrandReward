<?php /* Smarty version 2.6.26, created on 2015-12-10 06:41:18
         compiled from check_program_domain_links.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'check_program_domain_links.tpl', 256, false),array('function', 'cycle', 'check_program_domain_links.tpl', 300, false),)), $this); ?>
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
<?php echo '
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
	var verifyArr = {\'id\':id};
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
	var verifyArr = {\'id\':id};
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
	var verifyArr = {\'id\':id, \'remark\':remark};
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
'; ?>

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
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['typeArr'],'selected' => $this->_tpl_vars['type']), $this);?>

				
			</select>&nbsp;&nbsp;
		
			Editor: <select name="editor">
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['editorArr'],'selected' => $this->_tpl_vars['editor']), $this);?>

				
			</select>&nbsp;&nbsp;
			
			Affiliate: 	<input type="text" id="affiliatename" name="aff_name" value="<?php echo $this->_tpl_vars['aff_name']; ?>
" size="30" />&nbsp;&nbsp;
						<input type="hidden" id="affiliatetype" name="aff_id" value="<?php echo $this->_tpl_vars['aff_id']; ?>
" />						
			Program: 	<input type="text" id="program" name="program_name" value="<?php echo $this->_tpl_vars['program_name']; ?>
" size="30" />&nbsp;&nbsp;
						<input type="hidden" id="programid" name="program_id" value="<?php echo $this->_tpl_vars['program_id']; ?>
" size="30" />
						
			Status: <select name="status">
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['statusArr'],'selected' => $this->_tpl_vars['status']), $this);?>
				
			</select>&nbsp;&nbsp;
						
			<input type="checkbox" name="important" value="1" <?php if ($this->_tpl_vars['important'] == 1): ?>checked<?php endif; ?> /> only Importtant&nbsp;&nbsp;
			
						
			<input type="submit" class="submit" value="Query">
			
		</td>
		</form>
	</tr>
	<tr>
		<td align="right"><?php echo $this->_tpl_vars['pagebar']; ?>
</td>
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
				<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
				<tr id="tr<?php echo $this->_tpl_vars['info']['id']; ?>
" bgcolor="<?php echo smarty_function_cycle(array('values' => "#FFFFFF,#EEEEEE"), $this);?>
" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td>
						<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_edit.php?ID=<?php echo $this->_tpl_vars['info']['programid']; ?>
" target="_blank"><?php echo $this->_tpl_vars['p_info'][$this->_tpl_vars['info']['programid']]['name']; ?>
 (<?php echo $this->_tpl_vars['p_info'][$this->_tpl_vars['info']['programid']]['idinaff']; ?>
)</a>
						<br /> - <?php echo $this->_tpl_vars['p_info'][$this->_tpl_vars['info']['programid']]['aff_name']; ?>

					</td>
					<td>						
						<?php if ($this->_tpl_vars['info']['errortype'] == 4): ?>
							<?php $_from = $this->_tpl_vars['p_domain'][$this->_tpl_vars['info']['programid']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['domains']):
?>
								<?php echo $this->_tpl_vars['domains']; ?>
<br />
							<?php endforeach; endif; unset($_from); ?>
						<?php else: ?>
							<?php echo $this->_tpl_vars['d_info'][$this->_tpl_vars['info']['domainid']]['domain']; ?>

						<?php endif; ?>
						
					</td>
					<td>
						<?php if ($this->_tpl_vars['info']['errortype'] == 11): ?>
							<?php echo $this->_tpl_vars['typeArr'][1]; ?>
:
						<?php else: ?>
							<?php echo $this->_tpl_vars['typeArr'][$this->_tpl_vars['info']['errortype']]; ?>
:
						<?php endif; ?>
						<br />
						<?php if ($this->_tpl_vars['info']['errortype'] == 1 || $this->_tpl_vars['info']['errortype'] == 11): ?>
											
								<?php echo $this->_tpl_vars['info']['AffDefaultUrl']; ?>

								<br />
								<input type='hidden' id="affurl<?php echo $this->_tpl_vars['info']['id']; ?>
" value="<?php echo $this->_tpl_vars['info']['AffDefaultUrl']; ?>
" />								
								<input type='button' value='Preview Links' onclick="javacript: return previewLPUrl('<?php echo $this->_tpl_vars['info']['id']; ?>
')" />
								<br />							
														
							<?php if ($this->_tpl_vars['info']['DeepUrlTpl']): ?>					
								<?php echo $this->_tpl_vars['info']['DeepUrlTpl']; ?>

								<br />
								<input type='hidden' id="urltpl<?php echo $this->_tpl_vars['info']['id']; ?>
" value="<?php echo $this->_tpl_vars['info']['DeepUrlTpl']; ?>
" />							
								<input type='button' value='Preview Deep Tpl Links' onclick="javacript: return previewDeepUrl('<?php echo $this->_tpl_vars['info']['id']; ?>
')" />
							<?php endif; ?>
							
							<input type='hidden' id="homepage<?php echo $this->_tpl_vars['info']['id']; ?>
" value="http://<?php echo $this->_tpl_vars['d_info'][$this->_tpl_vars['info']['domainid']]['domain']; ?>
" />
							
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId=<?php echo $this->_tpl_vars['info']['programid']; ?>
&DomainId=<?php echo $this->_tpl_vars['info']['domainid']; ?>
" target="_blank">quick edit link</a>
						<?php elseif ($this->_tpl_vars['info']['errortype'] == 2 || $this->_tpl_vars['info']['errortype'] == 3): ?>
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId=<?php echo $this->_tpl_vars['info']['programid']; ?>
" target="_blank">quick edit link</a>
						<?php elseif ($this->_tpl_vars['info']['errortype'] == 4): ?>
							New Domain: <font color="green"><?php echo $this->_tpl_vars['info']['errorvalue']; ?>
</font> <br />
							Old Homepage: <?php echo $this->_tpl_vars['p_info'][$this->_tpl_vars['info']['programid']]['homepage']; ?>
							
							<input type='hidden' id="homepage<?php echo $this->_tpl_vars['info']['id']; ?>
" value="<?php echo $this->_tpl_vars['p_info'][$this->_tpl_vars['info']['programid']]['homepage']; ?>
" />
							<input type='button' value='Preview Links' onclick="javacript: return previewLPUrl('<?php echo $this->_tpl_vars['info']['id']; ?>
')" />
							
							<a href="http://bdg.mgsvc.com/admin/old_tools/front/program_edit.php?ID=<?php echo $this->_tpl_vars['info']['programid']; ?>
" target="_blank">quick edit link</a>
						<?php endif; ?>
					</td>					
					<td align="left" width="60px;">
						<a href="javascript:done('<?php echo $this->_tpl_vars['info']['id']; ?>
')">[done]</a> ||| 
						<a href="javascript:ignored('<?php echo $this->_tpl_vars['info']['id']; ?>
')">[ignored]</a>
						<hr />						
						<?php if ($this->_tpl_vars['info']['remark'] != '' || $this->_tpl_vars['editor'] == 'elsahou'): ?>
							 <?php echo $this->_tpl_vars['info']['remark']; ?>

						<?php else: ?>
							<textarea rows="2" id="remark<?php echo $this->_tpl_vars['info']['id']; ?>
" style="width:100%"></textarea>
							<br />
							<a href="javascript:assignElsa('<?php echo $this->_tpl_vars['info']['id']; ?>
')" style="float:right">[assign to Elsa]</a>
						<?php endif; ?>
					</td>					
				</tr>
				<?php endforeach; endif; unset($_from); ?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $this->_tpl_vars['pagebar1']; ?>
</td>
	</tr>
</table>
</div>
</body>
</html>