<?php /* Smarty version 2.6.26, created on 2015-12-10 05:04:45
         compiled from domain_default_change_log.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'domain_default_change_log.tpl', 62, false),array('modifier', 'replace', 'domain_default_change_log.tpl', 118, false),array('modifier', 'escape', 'domain_default_change_log.tpl', 160, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />-->
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<script language="JavaScript" src="../js/jquery.js"></script>
<script language="JavaScript" src="../js/calendar_fix.js"></script>
<script language="JavaScript" src="../js/html_common.js"></script>
<script language="JavaScript" src="../js/program_search.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="../js/jquery.form.js"></script>
<link href="http://bdg.mgsvc.com/admin/css/bootstrap.min.css" rel="stylesheet">
<link href="http://bdg.mgsvc.com/admin/css/front.css" rel="stylesheet">
</head>
<?php echo '
<style type="text/css">
.cell_label{text-align:right;font-weight:bold;background-color:#EEEEEE;border:1px solid #DDDDDD;}
.cell_value{text-align:left;border:1px solid #DDDDDD;}
td,th{font-size:14px;line-height:1.5;}
table{word-break:break-all}
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
<script language=\'javascript\'>
function edit(id, status){
	//var remark = $(\'#remark\'+id).val();
	var url =  \'http://bdg.mgsvc.com/admin/old_tools/front/domain_default_change_log.php\';
	var verifyArr = {\'id\':id, \'status\':status, \'action\':\'edit\'};
	$.ajax({type: \'get\',
		url: url,
		data: $.param(verifyArr),
		success: function(msg){
					if(msg.trim() == \'1\'){
						$(\'#tr_\'+id).remove();
					}else{
						alert(msg);
					}
				 }
	});
}
</script>
'; ?>

<div class="container" style="margin-top:30px;width:100%;">
	<div class='col-lg' style='width:98%;'>
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<?php echo $this->_tpl_vars['title']; ?>

			</div>
			<div class='panel-heading'>
			<form id="s">
				Site: <select name='site'>
							<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['site_filter_Arr'],'selected' => $this->_tpl_vars['site']), $this);?>

				 		</select>
				&nbsp;&nbsp;
				Domain: <input type="text" id="domain_search" name="domain" value="<?php echo $this->_tpl_vars['domain']; ?>
" size="30" />
						<input type="hidden" id="domainid" name="domainid" value="<?php echo $this->_tpl_vars['domainid']; ?>
"/>
				&nbsp;&nbsp; 
				
				<input type="checkbox" name="rank" id="rank" value=1 <?php if ($this->_tpl_vars['rank']): ?>checked<?php endif; ?> /><label for="rank">Rank</label>
				
				&nbsp;&nbsp; 
				
				<input type="submit" value="Search" />
			</form>
			</div>
			<div class='panel-body'>
				<table class='table table-striped'>
					<tr>						
						<th width="18%">Domain</th>
						<th width="35%">Program From</th>
						<th width="35%">Program to</th>
						<th width="12%">Change Time</th>
					</tr>
					<?php $_from = $this->_tpl_vars['change_log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
					<tr id="tr_<?php echo $this->_tpl_vars['item']['id']; ?>
">						
						<td>
							<a href="http://bdg.mgsvc.com/admin//b_dpl.php?id=<?php echo $this->_tpl_vars['item']['did']; ?>
&site=<?php echo $this->_tpl_vars['item']['site']; ?>
" target="_blank"><?php echo $this->_tpl_vars['item']['domain']; ?>
(<?php echo $this->_tpl_vars['item']['did']; ?>
)</a>
							<br />
							<b>Site:</b>[<font color="blue"><?php echo $this->_tpl_vars['item']['site']; ?>
</font>];
							<b>Rank:</b><font color="green">(<?php echo $this->_tpl_vars['item']['rank']; ?>
)</font>
							<br />
							<?php if ($this->_tpl_vars['item']['clicks1M'] > 0): ?>
							<b>revenue 1M:</b> <?php echo $this->_tpl_vars['item']['revenue1M']; ?>
 <br />
							<b>clicks 1M:</b> <?php echo $this->_tpl_vars['item']['clicks1M']; ?>
 <br />
							<b>orders 1M:</b> <?php echo $this->_tpl_vars['item']['orders1M']; ?>
 <br />							
							<?php endif; ?>
						</td>
						<td>
							<?php if ($this->_tpl_vars['item']['programfrom'] > 0): ?>
								<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['aff_name']; ?>
 - <?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['name']; ?>
 <a href="http://bdg.mgsvc.com/admin/b_program_edit.php?id=<?php echo $this->_tpl_vars['item']['programfrom']; ?>
" target="_blank">(<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['idinaff']; ?>
)</a> <br />
								<b>Rank:</b><font color="green">(<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['revenueOrder']; ?>
)</font>;
								<b>Status:</b>
								<?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['isactive'] == 'Active'): ?>
									<font color='green'><?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['isactive']; ?>
</font>
								<?php else: ?>
									<font color='red'><?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['isactive']; ?>
</font>
								<?php endif; ?><br />
								<font color='green'><?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['commissiontype'] == 'Percent'): ?>
									<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['commissionused']; ?>
 %
								<?php else: ?>
									<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['commissioncurrency']; ?>
 <?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['commissionused']; ?>

								<?php endif; ?></font>
								<?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['commissionincentive'] == 1): ?>
									<font color='pink'>Incentive</font>
								<?php endif; ?>
								<br /><b>Domain:</b><br />
																<?php echo ((is_array($_tmp=$this->_tpl_vars['p_domains'][$this->_tpl_vars['item']['programfrom']]['domains'])) ? $this->_run_mod_handler('replace', true, $_tmp, ",", "<br />") : smarty_modifier_replace($_tmp, ",", "<br />")); ?>

								<br /><b>country:</b><br />
								<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programfrom']]['shippingcountry']; ?>

							<?php else: ?>
								None Aff
							<?php endif; ?>
						</td>
						<td>
							<?php if ($this->_tpl_vars['item']['programto'] > 0): ?>
								<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['aff_name']; ?>
 - <?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['name']; ?>
 <a href="http://bdg.mgsvc.com/admin/b_program_edit.php?id=<?php echo $this->_tpl_vars['item']['programto']; ?>
" target="_blank">(<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['idinaff']; ?>
)</a> <br />
								<b>Rank:</b><font color="green">(<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['revenueOrder']; ?>
)</font>
								<b>Status:</b>
								<?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['isactive'] == 'Active'): ?>
									<font color='green'><?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['isactive']; ?>
</font>
								<?php else: ?>
									<font color='red'><?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['isactive']; ?>
</font>
								<?php endif; ?><br />
								<font color='green'><?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['commissiontype'] == 'Percent'): ?>
									<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['commissionused']; ?>
 %
								<?php else: ?>
									<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['commissioncurrency']; ?>
 <?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['commissionused']; ?>

								<?php endif; ?></font>
								<?php if ($this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['commissionincentive'] == 1): ?>
									<font color='pink'>Incentive</font>
								<?php endif; ?>
								<br /><b>Domain:</b><br />
																<?php echo ((is_array($_tmp=$this->_tpl_vars['p_domains'][$this->_tpl_vars['item']['programto']]['domains'])) ? $this->_run_mod_handler('replace', true, $_tmp, ",", "<br />") : smarty_modifier_replace($_tmp, ",", "<br />")); ?>

								<br /><b>country:</b><br />
								<?php echo $this->_tpl_vars['prgm_intell'][$this->_tpl_vars['item']['programto']]['shippingcountry']; ?>

							<?php else: ?>
								None Aff
							<?php endif; ?>	
						</td>
						<td>
							<?php echo $this->_tpl_vars['item']['changetime']; ?>

							<br />
							<?php if ($this->_tpl_vars['item']['status'] == 'New'): ?>
																<input type='button' value='Positive' onclick="edit(<?php echo $this->_tpl_vars['item']['id']; ?>
, 'Positive')" />
								<input type='button' value='Negative' onclick="edit(<?php echo $this->_tpl_vars['item']['id']; ?>
, 'Negative')" />
							<?php else: ?>
								<?php echo ((is_array($_tmp=$this->_tpl_vars['item']['status'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; endif; unset($_from); ?>
				</table>
								
				
			</div>
		</div>
	</div>
</div>

</body>
</html>