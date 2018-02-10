<?php /* Smarty version 2.6.26, created on 2015-11-05 19:21:10
         compiled from program_list.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'program_list.tpl', 67, false),array('function', 'cycle', 'program_list.tpl', 168, false),array('modifier', 'escape', 'program_list.tpl', 170, false),array('modifier', 'upper', 'program_list.tpl', 198, false),)), $this); ?>
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
<?php echo '
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
'; ?>

</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF">
		<td align="center">
			<h1>Program List</h1>
			<?php if ($this->_tpl_vars['isRemind']): ?>
			<font color="red">Has Remind</font>
			<?php endif; ?>			
			[<a href="../admin/old_tools/front/program_add.php" target="_blank">Add Program</a>]
		</td>
	</tr>
	<tr>
		<form name="form1" action="" method="get">
		<td>
			<b>Filter</b>			
			Affiliate: <input type="text" id="affiliatename" name="affiliatename" value="<?php echo $this->_tpl_vars['affiliatename']; ?>
" size="30" />&nbsp;<input type="button" value="reset" onclick="resetAff()" />
			<input type="hidden" name="affiliatetype" id="affiliatetype" value="<?php echo $this->_tpl_vars['affiliatetype']; ?>
" />&nbsp;&nbsp;
						
			Program: <input type="text" id="program_search" name="name" value="<?php echo $this->_tpl_vars['name']; ?>
" size="30" />&nbsp;&nbsp;
			
			Site: <select name="site" id="site">
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['siteArr'],'selected' => $this->_tpl_vars['site']), $this);?>

			</select>&nbsp;&nbsp;
			Merchant: <input type="text" id="merchantname" name="merchantname" value="<?php echo $this->_tpl_vars['merchantname']; ?>
" size="30" />
			<input type="hidden" name="merchantid" id="merchantid" value="<?php echo $this->_tpl_vars['merchantid']; ?>
" />&nbsp;&nbsp;
			
			Partnership Create Date: 
			<input name="createdatestart" type="text" id="createdatestart" size="20" value="<?php echo $this->_tpl_vars['createdatestart']; ?>
" onFocus="<?php echo 'WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true,readOnly:true});'; ?>
"> 
			 ~ 
			<input name="createdatend" type="text" id="createdatend" size="20" value="<?php echo $this->_tpl_vars['createdatend']; ?>
" onFocus="<?php echo 'WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true,readOnly:true});'; ?>
">&nbsp;&nbsp;
			
			Program Add Date: 
			<input name="addtimestart" type="text" id="addtimestart" size="20" value="<?php echo $this->_tpl_vars['addtimestart']; ?>
" onFocus="<?php echo 'WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true,readOnly:true});'; ?>
"> 
			 ~ 
			<input name="addtimeend" type="text" id="addtimeend" size="20" value="<?php echo $this->_tpl_vars['addtimeend']; ?>
" onFocus="<?php echo 'WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true,readOnly:true});'; ?>
">&nbsp;&nbsp;
			
			
			<?php if ($this->_tpl_vars['isRemind']): ?><input name='expireremind' type="checkbox" value="1" <?php if ($this->_tpl_vars['expireremind'] == 1): ?>checked="checked"<?php endif; ?> id="expireremind" onclick="this.form.submit();"><font color="red">Has Remind</font><?php endif; ?>
			&nbsp;&nbsp;&nbsp;&nbsp;			
			
			</br>
			
			Country: <select name="country" id="country">
			<?php $_from = $this->_tpl_vars['countryAllArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
			<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['country'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>&nbsp;&nbsp;
			Partnership: <select name="partnership" id="partnership">
			<?php $_from = $this->_tpl_vars['partnerShipAllArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
				<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['partnership'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>&nbsp;&nbsp;
			Declined by Mega: <select name="wedeclined" id="wedeclined">
				<option value="All" <?php if ($this->_tpl_vars['wedeclined'] == 'All'): ?>selected="selected"<?php endif; ?>>All</option>
				<option value="YES" <?php if ($this->_tpl_vars['wedeclined'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>
				<option value="NO" <?php if ($this->_tpl_vars['wedeclined'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>
				<option value="NoNeedToApply" <?php if ($this->_tpl_vars['wedeclined'] == 'NoNeedToApply'): ?>selected="selected"<?php endif; ?>>NoNeedToApply</option>
			</select>&nbsp;&nbsp;
			Status In Affiliate: <select name="statusinaff" id="statusinaff">
			<?php $_from = $this->_tpl_vars['statusInAffiliateAllArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
				<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['statusinaff'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>&nbsp;&nbsp;
			
			P-M Relationship: <select name="hasPM">
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['pmArr'],'selected' => $this->_tpl_vars['hasPM']), $this);?>

			</select>&nbsp;&nbsp;
			
			P-S Relationship: <select name="hasPS">
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['psArr'],'selected' => $this->_tpl_vars['hasPS']), $this);?>

			</select>&nbsp;&nbsp;
			
			Cooperate With Coupon Site: <select name="hasCoop">
				<option value="All" <?php if ($this->_tpl_vars['hasCoop'] == 'All'): ?>selected="selected"<?php endif; ?>>All</option>
				<option value="YES" <?php if ($this->_tpl_vars['hasCoop'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>
				<option value="NO" <?php if ($this->_tpl_vars['hasCoop'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>
			</select>&nbsp;&nbsp;

			Group Inc: <select name="group">
				<option value="All">All</option>
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['groupArr'],'selected' => $this->_tpl_vars['group']), $this);?>

			</select>&nbsp;&nbsp;
			
			Mobile Friendly: <select name="mobilefriendly">				
				<?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['mobilefriendlyArr'],'output' => $this->_tpl_vars['mobilefriendlyArr'],'selected' => $this->_tpl_vars['mobilefriendly']), $this);?>

			</select>&nbsp;&nbsp;
			
			<b>Sort</b>
			Order By: <select name="order" id="order">
			<?php $_from = $this->_tpl_vars['orderbyArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
			<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['order'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>&nbsp;&nbsp;
			
			<input type="submit" class="submit" value="Query">
			<br/>
			<input type="hidden" name="down" id="down" />
			<input type="button" value="Down" onclick="downCsv()" />
		</td>
		</form>
	</tr>
	<tr>
		<td align="right"><?php echo $this->_tpl_vars['pagebar']; ?>
</td>
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
								
																				<th width="150px">Remark</th>
					<th width="80px">Action</th>
				</tr>
				<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
				<tr bgcolor="<?php echo smarty_function_cycle(array('values' => "#FFFFFF,#EEEEEE"), $this);?>
" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center" width="100px;">
						<a href="<?php echo $this->_tpl_vars['info']['go_Homepage']; ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</a><?php if ($this->_tpl_vars['info']['isremind']): ?> <font color='red'>*</font><?php endif; ?><?php if ($this->_tpl_vars['info']['RevenueOrder']): ?><font color='green'>(<?php echo $this->_tpl_vars['info']['RevenueOrder']; ?>
)</font><?php endif; ?>
						<hr width="95%"/><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['affiliatename'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

					</td>
					<td align="center" width="80px;"><?php if ($this->_tpl_vars['info']['idInAffUrl']): ?><a href="<?php echo $this->_tpl_vars['info']['idInAffUrl']; ?>
" target="_blank"><?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['IdInAff'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
<?php if ($this->_tpl_vars['info']['idInAffUrl']): ?></a><?php endif; ?><hr width="95%"/>Rank: <span style="font-weight:bold;"><?php echo $this->_tpl_vars['info']['RankInAff']; ?>
</span></td>
					<td align="center">
						<span style="font-weight:bold;"><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['StatusInAff'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</span><br /><span style="color:#FF0000;"><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['StatusInAffRemark'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</span><hr width="95%"/>
						<span style="font-weight:bold;"><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['Partnership'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</span><br /><span style="color:#FF0000;"><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['PartnershipChangeReason'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
<?php if ($this->_tpl_vars['info']['WeDeclined'] == 'YES'): ?>(We Declined)<?php elseif ($this->_tpl_vars['info']['WeDeclined'] == 'NoNeedToApply'): ?>(NoNeedToApply)<?php endif; ?></span>
					</td>
					<td>
						<?php if (! empty ( $this->_tpl_vars['info']['ps'] )): ?>						
						<?php $_from = $this->_tpl_vars['info']['ps']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ps']):
?>
							Store: <?php if ($this->_tpl_vars['s_default'][$this->_tpl_vars['ps']['StoreId']]['SEM'] == 'YES'): ?><font color='red'>Has SEM</font><?php endif; ?><br />
							[<font color="<?php if ($this->_tpl_vars['ps']['Status'] == 'Active'): ?>green<?php else: ?>red<?php endif; ?>"><?php echo $this->_tpl_vars['ps']['Status']; ?>
</font>]<a href="/front/store_edit_bd.php?id=<?php echo $this->_tpl_vars['ps']['StoreId']; ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['ps']['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
(<?php echo $this->_tpl_vars['ps']['StoreId']; ?>
)</a> 
							<?php if ($this->_tpl_vars['s_default'][$this->_tpl_vars['ps']['StoreId']]['AffiliateDefaultUrl']): ?>
								<?php $this->assign('pid', $this->_tpl_vars['s_default'][$this->_tpl_vars['ps']['StoreId']]['AffiliateDefaultUrl']); ?>
								Default Url : <?php echo ((is_array($_tmp=$this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['AffName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 - <a href="/front/program_edit.php?ID=<?php echo $this->_tpl_vars['pid']; ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</a>(<?php echo ((is_array($_tmp=$this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['IdInAff'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
);
							<?php endif; ?>
							<?php if ($this->_tpl_vars['s_default'][$this->_tpl_vars['ps']['StoreId']]['DeepUrlTemplate']): ?>
								<?php $this->assign('pid', $this->_tpl_vars['s_default'][$this->_tpl_vars['ps']['StoreId']]['DeepUrlTemplate']); ?>
								Deep Url Template : <?php echo ((is_array($_tmp=$this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['AffName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 - <a href="/front/program_edit.php?ID=<?php echo $this->_tpl_vars['pid']; ?>
" target="_blank"><?php echo $this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['Name']; ?>
</a>(<?php echo ((is_array($_tmp=$this->_tpl_vars['p_default'][$this->_tpl_vars['pid']]['IdInAff'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
)								
							<?php endif; ?>
							<br />
							<?php if (! empty ( $this->_tpl_vars['info']['sm'] )): ?>
							<hr>
							Merchant: <a href="/front/store_edit_bd.php?id=<?php echo $this->_tpl_vars['ps']['StoreId']; ?>
#rel_info" target="_blank">S-M Rank</a><br />
							<?php $_from = $this->_tpl_vars['info']['sm']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['spm']):
?>								
								<?php if ($this->_tpl_vars['key'] == $this->_tpl_vars['ps']['StoreId']): ?>
									<?php $_from = $this->_tpl_vars['spm']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pm']):
?>
									[<font color="<?php if ($this->_tpl_vars['pm']['Status'] == 'Active'): ?>green<?php else: ?>red<?php endif; ?>"><?php echo $this->_tpl_vars['pm']['Status']; ?>
</font>]<?php echo ((is_array($_tmp=$this->_tpl_vars['pm']['Site'])) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
 - 
									<a href="<?php echo $this->_tpl_vars['g_SiteUrl'][$this->_tpl_vars['pm']['Site']]['front']; ?>
/front/merchant.php?mid=<?php echo $this->_tpl_vars['pm']['MerchantId']; ?>
" target="_blank"><?php echo $this->_tpl_vars['pm']['MerchantName']; ?>
</a>(<a href="/editor/merchant.php?site=<?php echo $this->_tpl_vars['pm']['Site']; ?>
&merchantid=<?php echo $this->_tpl_vars['pm']['MerchantId']; ?>
&act=editmerchant" target="_blank"><?php echo $this->_tpl_vars['pm']['MerchantId']; ?>
</a>)(<?php echo $this->_tpl_vars['pm']['Grade']; ?>
)<br />
									<?php endforeach; endif; unset($_from); ?>
								<?php endif; ?>
							<?php endforeach; endif; unset($_from); ?>
							
							<?php if (! empty ( $this->_tpl_vars['info']['storeHDDRel'] )): ?>
								<?php $_from = $this->_tpl_vars['info']['storeHDDRel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['storerel']):
?>
									<?php if ($this->_tpl_vars['storerel']['Status'] == 1): ?>
										[<font color='green'><?php echo $this->_tpl_vars['storerel']['SiteName']; ?>
</font>]<a href="<?php echo $this->_tpl_vars['storerel']['MerchantFrontUrl']; ?>
" target="_blank"><?php echo $this->_tpl_vars['storerel']['MerchantName']; ?>
</a><br />
									<?php endif; ?>							
								<?php endforeach; endif; unset($_from); ?>
							<?php endif; ?>
							<br />
							<?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
						
						<?php endif; ?>
											</td>
					<td align="center" width="100px"><p class="contry less"><?php echo $this->_tpl_vars['info']['TargetCountryExt']; ?>
</p><hr style="width:95%"/><p class="contry less"><?php echo $this->_tpl_vars['info']['TargetCountryInt']; ?>
</p></td>
					<td width="200px">[<span class="external">Ext</span>] <?php echo $this->_tpl_vars['info']['CategoryExt']; ?>
<hr/>[<span class="internal">Int</span>] <?php echo $this->_tpl_vars['info']['CategoryInt']; ?>
</td>
																	
										<td>
					<?php if (! empty ( $this->_tpl_vars['info']['remarkshort'] )): ?>
					<span id="l_<?php echo $this->_tpl_vars['info']['ID']; ?>
" style="display:none;"><?php echo $this->_tpl_vars['info']['Remark']; ?>
 <a style="color:red;" href="javascript:void(0);" onclick="pickUp(<?php echo $this->_tpl_vars['info']['ID']; ?>
);">[-]</a></span>
					<span id="s_<?php echo $this->_tpl_vars['info']['ID']; ?>
" style="display:'';"><?php echo $this->_tpl_vars['info']['remarkshort']; ?>
 <a style="color:red;" href="javascript:void(0);" onclick="expan(<?php echo $this->_tpl_vars['info']['ID']; ?>
);">[+]</a></span>
					<?php else: ?>
					<?php echo $this->_tpl_vars['info']['Remark']; ?>

					<?php endif; ?>
					</td>
					<td align="center" width="60px;">
						<a href="/front/program_edit.php?ID=<?php echo $this->_tpl_vars['info']['ID']; ?>
" target="_blank">Edit</a><br/>
						<a href="/editor/merchant_search.php?filter_status=&type=aff&merchantname=&affiliate=<?php echo $this->_tpl_vars['info']['AffId']; ?>
&MerIDinAff=<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['IdInAff'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" target="_blank">Search</a><br/>
												<a href="/front/bd_work_log.php?programid=<?php echo $this->_tpl_vars['info']['ID']; ?>
&prgm_name=<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" target="_blank">Add Work Log</a>
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
</body>
</html>