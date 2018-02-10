<?php /* Smarty version 2.6.26, created on 2016-01-12 01:18:10
         compiled from program_edit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strip_tags', 'program_edit.tpl', 68, false),array('modifier', 'nl2br', 'program_edit.tpl', 111, false),array('function', 'html_options', 'program_edit.tpl', 131, false),)), $this); ?>
<html>
<head>
<title><?php echo $this->_tpl_vars['data']['Name']; ?>
 - <?php echo $this->_tpl_vars['data']['affiliatename']; ?>
</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/program.js"></script>
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
<link href="http://bdg.mgsvc.com/admin/css/bootstrap.min.css" rel="stylesheet">
<link href="http://bdg.mgsvc.com/admin/css/front.css" rel="stylesheet">
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
'; ?>

</head>
<body>
<!-- head-top start -->
<div class="navbar head-top">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand hidden-sm" href="http://bdg.mgsvc.com/admin/">MEGAGO</a>
    </div>
    <div>
      <ul class="nav navbar-nav">
        <li>
          <a href="http://bdg.mgsvc.com/admin/b_program.php?affiliate=<?php echo $this->_tpl_vars['data']['affiliatename']; ?>
">Program list</a>
        </li>
        <li>
          <a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId=<?php echo $this->_tpl_vars['data']['ID']; ?>
" target="_blank">Program Domain Tracking Link Config</a>
        </li>
            
      </ul>      
    </div>
  </div>
</div>
<!-- head-top end  -->
<form name="form1" id="form1" method="post" action="">
<h1 style="text-align:center">Edit Program - <?php echo $this->_tpl_vars['data']['Name']; ?>
</h1>
<div class="container" style="margin-top:30px;">
<table class="table table-bordered">
	
	<tr><th colspan="2"><font color="white" style="font-size:14px"><b>Basic Info</b></font> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#TermsConditions">Terms & Conditions</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label" width="20%">Program ID in Affiliate</td>
		<td class="cell_value" width="80%"><?php echo $this->_tpl_vars['data']['IdInAff']; ?>
 <?php if ($this->_tpl_vars['data']['RevenueOrder']): ?><font color='green'>(Rank In Mega: <?php echo $this->_tpl_vars['data']['RevenueOrder']; ?>
)</font><?php endif; ?></td>
	</tr>
	<tr>
		<td class="cell_label">Affiliate</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['affiliatename']; ?>
 (<?php echo $this->_tpl_vars['data']['AffId']; ?>
)</td>
	</tr>
	<tr>
		<td class="cell_label">Name</td>
		<td class="cell_value"><input name="tmp[external][Name]" style="width:450px" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['Name'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
" /></td>
	</tr>
	<tr>
		<td class="cell_label">Homepage</td>
		<td class="cell_value">
			Ext: <?php echo ((is_array($_tmp=$this->_tpl_vars['data']['Homepage'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
 
			<input id="homepage_ext" type="hidden" value="<?php echo $this->_tpl_vars['data']['Homepage']; ?>
">&nbsp;&nbsp;
			<input type="button" value="Open" onclick="OpenURL('homepage_ext');">
			<hr />
			Int: <input id="homepage_int" name="tmp[internal][HomepageInt]" style="width:450px" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['HomepageInt'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
" />&nbsp;&nbsp;			
			<input type="button" value="Open" onclick="OpenURL('homepage_int');">
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Domain</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['prgm_intell']['Domain']; ?>
</td>
	</tr>
	
	<tr>
		<td class="cell_label">Support Other Domains</td>
		<td class="cell_value"><textarea class="form-control" name="RealDomain"><?php echo $this->_tpl_vars['prgm_manual']['RealDomain']; ?>
</textarea></td>
	</tr>
	
	<tr>
		<td class="cell_label">Contacts</td>
		<td class="cell_value">
			Ext: <?php echo ((is_array($_tmp=$this->_tpl_vars['data']['Contacts'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>

			<hr />
			Int: <input name="tmp[internal][ContactsInt]" style="width:450px" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['ContactsInt'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
" />
		</td>
	</tr>
	<tr>
		<td class="cell_label">Description</td>
		<td class="cell_value">
						<iframe src="../front/program_edit.php?ID=<?php echo $this->_tpl_vars['data']['ID']; ?>
&action=desc" style="width:100%;height:300px" class="form-control"></iframe>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Targeting Countries</td>
		<td class="cell_value">
			Ext: <?php echo ((is_array($_tmp=$this->_tpl_vars['data']['TargetCountryExt'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
 | <?php echo $this->_tpl_vars['prgm_intell']['ShippingCountry']; ?>
<hr />
			Int OLD: <?php echo ((is_array($_tmp=$this->_tpl_vars['data']['TargetCountryIntOld'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
 <br />
			Int: 
			<table>
				<tr>
					<td>
						<select id="CountryLeft" ondblclick="optionMove('CountryLeft', 'CountryRight')" onkeydown="if(isEnter(event)) <?php echo '{optionMove(\'CountryLeft\', \'CountryRight\');return false;}'; ?>
" multiple="multiple" size="10" style="width:200px;">
						<?php $_from = $this->_tpl_vars['countryArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['item']):
?>
							<option value="<?php echo $this->_tpl_vars['k']; ?>
"><?php echo $this->_tpl_vars['item']; ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
						</select>
					</td>
					<td>
						<a onclick="optionMove('CountryLeft', 'CountryRight')" href="javascript:void(0);"> Add Selected &rsaquo;</a><br>
						<a onclick="optionMove('CountryRight', 'CountryLeft')" href="javascript:void(0);"> Remove Selected &lsaquo;</a><br>
						<a onclick="optionMoveAll('CountryLeft', 'CountryRight')" href="javascript:void(0);"> Add All &raquo;</a><br>
						<a onclick="optionMoveAll('CountryRight', 'CountryLeft')" href="javascript:void(0);"> Remove All &laquo;</a> 
					</td>
					<td>
						<select name="tmp[external][TargetCountryInt][]" id="CountryRight" ondblclick="optionMove('CountryRight', 'CountryLeft')" onkeydown="if(isEnter(event)) <?php echo '{optionMove(\'CountryRight\', \'CountryLeft\');return false;}'; ?>
" multiple="multiple" size="10" style="width:200px;">
						<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['data']['TargetCountryIntFullNameArr'],'selected' => $this->_tpl_vars['data']['TargetCountryIntArr']), $this);?>

						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
		<tr>
		<td class="cell_label">Rank In Affiliate</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['RankInAff']; ?>
</td>
	</tr>
			<tr>
		<td class="cell_label">Mobile Friendly</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['MobileFriendly']; ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Disallow to Promote</td>
		<td class="cell_value">			
			<?php $_from = $this->_tpl_vars['AllSite']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
				<input type="checkbox" name="tmp[internal][SupportSpread][]" value="<?php echo $this->_tpl_vars['item']; ?>
" id="SupportSpread<?php echo $this->_tpl_vars['item']; ?>
" <?php if (isset ( $this->_tpl_vars['SupportSpread_arr'][$this->_tpl_vars['item']] )): ?>checked<?php endif; ?> /><label for="SupportSpread<?php echo $this->_tpl_vars['item']; ?>
">&nbsp;<?php echo $this->_tpl_vars['item']; ?>
</lable>&nbsp;&nbsp;&nbsp;
			<?php endforeach; endif; unset($_from); ?>
		</td>
	</tr>
	<tr><th colspan="2"><a name="PartnershipStatus"></a><span style="font-size:14px;"><b>Partnership Status</b> | <a href="#">Basic Info</a> | <a href="#TermsConditions">Terms & Conditions</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label">Status In Affiliate</td>
		<td class="cell_value">	
			<?php echo $this->_tpl_vars['data']['StatusInAff']; ?>

					</td>
	</tr>
	<tr>
		<td class="cell_label">Partnership</td>
		<td class="cell_value">
			<?php echo $this->_tpl_vars['data']['Partnership']; ?>
	
					</td>
	</tr>
	<tr>
		<td class="cell_label"><font color="green">Program IsActive In BDG</font></td>
		<td class="cell_value"><?php echo $this->_tpl_vars['prgm_intell']['IsActive']; ?>
</td>
	</tr>
	<tr>
         <td class="cell_label"><font color="green">Program IsActive Manual</font></td>
         <td class="cell_value">
         	<select name="StatusInBdg">
         		<option value="">Default</option>
         		<option value="Active" <?php if ($this->_tpl_vars['prgm_manual']['StatusInBdg'] == 'Active'): ?>selected<?php endif; ?>>Active</option>
         		<option value="Inactive" <?php if ($this->_tpl_vars['prgm_manual']['StatusInBdg'] == 'Inactive'): ?>selected<?php endif; ?>>Inactive</option>
         	</select>
         </td>
	</tr>
	<tr>
		<td class="cell_label">Declined By Mega</td>
		<td class="cell_value">
			<input name="tmp[external][WeDeclined]" type="radio" value="YES" <?php if ($this->_tpl_vars['data']['WeDeclined'] == 'YES'): ?>checked="checked"<?php endif; ?>>YES&nbsp;&nbsp;
			<input name="tmp[external][WeDeclined]" type="radio" value="NO" <?php if ($this->_tpl_vars['data']['WeDeclined'] == 'NO'): ?>checked="checked"<?php endif; ?>>NO&nbsp;&nbsp;
			<input name="tmp[external][WeDeclined]" type="radio" value="NoNeedToApply" <?php if ($this->_tpl_vars['data']['WeDeclined'] == 'NoNeedToApply'): ?>checked="checked"<?php endif; ?>>NoNeedToApply
		</td>
	</tr>
		<tr>
		<td class="cell_label">Apply Date</td>
		<td class="cell_value">
			<input name="tmp[internal][ApplyDate]" type="text" id="ApplyDate" size="20" value="<?php echo $this->_tpl_vars['data']['ApplyDate']; ?>
" onFocus="<?php echo 'WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true,readOnly:true});'; ?>
">
		</td>
	</tr>
		<tr>
		<td class="cell_label">Reapply Status</td>
		<td class="cell_value">					
			<select name="tmp[internal][ReApplyStatus]">
				<option value="UNKNOWN">UNKNOWN</option>						
				<option value="In-Progress" <?php if ($this->_tpl_vars['data']['ReApplyStatus'] == "In-Progress"): ?>selected="selected"<?php endif; ?>>In-Progress</option>
				<option value="Positive" <?php if ($this->_tpl_vars['data']['ReApplyStatus'] == 'Positive'): ?>selected="selected"<?php endif; ?>>Positive</option>
				<option value="Negative" <?php if ($this->_tpl_vars['data']['ReApplyStatus'] == 'Negative'): ?>selected="selected"<?php endif; ?>>Negative</option>				
			</select>
		</td>
	</tr>
	<input name="tmp[internal][ApplyOperator]" type="hidden" value="<?php echo $this->_tpl_vars['user']; ?>
">
	<input name="applydateold" type="hidden" value="<?php echo $this->_tpl_vars['data']['ApplyDate']; ?>
">
	<tr>
		<td class="cell_label">Partnership Change Reason</td>
		<td class="cell_value"><textarea name="tmp[external][PartnershipChangeReason]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['PartnershipChangeReason']; ?>
</textarea></td>
	</tr>
	<tr>
		<td class="cell_label">Cooperate With Coupon Site</td>
		<td class="cell_value">
			<select name="tmp[external][CooperateWithCouponSite]">
				<option value="NO" <?php if ($this->_tpl_vars['data']['CooperateWithCouponSite'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>
				<option value="YES" <?php if ($this->_tpl_vars['data']['CooperateWithCouponSite'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>						
			</select>						
		</td>
	</tr>
		<tr>
		<td class="cell_label">Remark</td>
		<td class="cell_value"><textarea name="tmp[external][Remark]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['Remark']; ?>
</textarea></td>
	</tr>
	
	<tr><th colspan="2"><a name="TermsConditions"></a><span style="font-size:14px;"><b>Terms & Conditions</b> | <a href="#">Basic Info</a> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label">Commission</td>
		<td class="cell_value">
			Ext: <?php echo ((is_array($_tmp=$this->_tpl_vars['data']['CommissionExt'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<hr />
			Int: <textarea name="tmp[internal][CommissionInt]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['CommissionInt']; ?>
</textarea>
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Commission analyzed</td>
		<td class="cell_value">			
			<?php echo $this->_tpl_vars['prgm_intell']['CommissionUsed']; ?>
(<?php if ($this->_tpl_vars['prgm_intell']['CommissionType'] == 'Value'): ?><?php echo $this->_tpl_vars['prgm_intell']['CommissionCurrency']; ?>
<?php else: ?>%<?php endif; ?>)
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Commission Manual</td>
		<td class="cell_value">			
			<div class="form-inline">
         		<div class="form-group">
         			<input type="text" id="f_CommissionUsed"  class="form-control" value="<?php echo $this->_tpl_vars['prgm_manual']['CommissionUsed']; ?>
" name="CommissionUsed" />
         		</div>
         		<div  class="form-group">
         			<select id="CommissionType" class="form-control" name="CommissionType">
         				<option value="Value" <?php if ($this->_tpl_vars['prgm_manual']['CommissionType'] == 'Value'): ?>selected<?php endif; ?>>Value</option>
         				<option value="Percent" <?php if ($this->_tpl_vars['prgm_manual']['CommissionType'] == 'Percent'): ?>selected<?php endif; ?>>Percent</option>
         			</select>
         		</div>
         		<div id="CommissionCurrency" class="form-group">
         			<select class="form-control" name="CommissionCurrency">
         			<option value='' selected>CommissionCurrency</option>
         				<?php $_from = $this->_tpl_vars['currency']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['c']):
?>
         					<option value=<?php echo $this->_tpl_vars['c']; ?>
 <?php if ($this->_tpl_vars['prgm_manual']['CommissionCurrency'] == $this->_tpl_vars['c']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['c']; ?>
</option>
         				<?php endforeach; endif; unset($_from); ?>
         			</select>
         		</div>
         		<div class="form-group">
         			<input type="button" class="btn  btn-primary " value="Clear" onclick="clearComm()">
         		</div>
         	</div>
		</td>
	</tr>
	
		<tr>
		<td class="cell_label">EPC</td>
		<td class="cell_value"><b>EPCDefault: </b><?php echo $this->_tpl_vars['data']['EPCDefault']; ?>
&nbsp;&nbsp;&nbsp;&nbsp;<b>EPC30d: </b><?php echo $this->_tpl_vars['data']['EPC30d']; ?>
&nbsp;&nbsp;&nbsp;&nbsp;<b>EPC90d: </b><?php echo $this->_tpl_vars['data']['EPC90d']; ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Cookie Time</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['CookieTime']; ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Has Pending Offer</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['HasPendingOffer']; ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Number of Occurrences</td>
		<td class="cell_value"><?php echo $this->_tpl_vars['data']['NumberOfOccurrences']; ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Term And Condition</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['TermAndCondition'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<tr>
		<td class="cell_label">Coupon Codes Policy</td>
		<td class="cell_value">
			<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['CouponCodesPolicyExt'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<hr />
			<textarea name="tmp[internal][CouponCodesPolicyInt]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['CouponCodesPolicyInt']; ?>
</textarea>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Sub Affiliate Policy</td>
		<td class="cell_value">
			<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['SubAffPolicyExt'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<hr />
			<textarea name="tmp[internal][SubAffPolicyInt]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['SubAffPolicyInt']; ?>
</textarea>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Complaint</td>
		<td class="cell_value"><textarea name="tmp[external][Complaint]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['Complaint']; ?>
</textarea></td>
	</tr>
	
	<tr>
		<td class="cell_label">Support SEO</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportSEO]">						
				<option value="UNKNOWN" <?php if ($this->_tpl_vars['data']['SupportSEO'] == 'UNKNOWN'): ?>selected="selected"<?php endif; ?>>UNKNOWN</option>
				<option value="YES" <?php if ($this->_tpl_vars['data']['SupportSEO'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>
				<option value="NO" <?php if ($this->_tpl_vars['data']['SupportSEO'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Support SNS</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportSNS]">						
				<option value="UNKNOWN" <?php if ($this->_tpl_vars['data']['SupportSNS'] == 'UNKNOWN'): ?>selected="selected"<?php endif; ?>>UNKNOWN</option>
				<option value="YES" <?php if ($this->_tpl_vars['data']['SupportSNS'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>
				<option value="NO" <?php if ($this->_tpl_vars['data']['SupportSNS'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Support Email</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportEmail]">						
				<option value="UNKNOWN" <?php if ($this->_tpl_vars['data']['SupportEmail'] == 'UNKNOWN'): ?>selected="selected"<?php endif; ?>>UNKNOWN</option>
				<option value="YES" <?php if ($this->_tpl_vars['data']['SupportEmail'] == 'YES'): ?>selected="selected"<?php endif; ?>>YES</option>
				<option value="NO" <?php if ($this->_tpl_vars['data']['SupportEmail'] == 'NO'): ?>selected="selected"<?php endif; ?>>NO</option>				
			</select>
		</td>
	</tr>
	
	<tr><th colspan="2"><a name="SEMPolicy"></a><span style="font-size:14px;"><b>SEM Policy</b> | <a href="#">Basic Info</a> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#TermsConditions">Terms & Conditions</a> </span></th></tr>
	<tr>
		<td class="cell_label">Inquiry Status</td>
		<td class="cell_value">
			<select name="tmp[internal][InquiryStatus]">						
				<option value="Not Inquired" <?php if ($this->_tpl_vars['data']['InquiryStatus'] == 'Not Inquired'): ?>selected="selected"<?php endif; ?>>Not Inquired</option>
				<option value="Inquiring" <?php if ($this->_tpl_vars['data']['InquiryStatus'] == 'Inquiring'): ?>selected="selected"<?php endif; ?>>Inquiring</option>
				<option value="Inquired" <?php if ($this->_tpl_vars['data']['InquiryStatus'] == 'Inquired'): ?>selected="selected"<?php endif; ?>>Inquired</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">SEM Policy Remark</td>
		<td class="cell_value">
			<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['SEMPolicyExt'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<hr />
			<textarea name="tmp[external][SEMPolicyRemark]" rows="7" cols="60"><?php echo $this->_tpl_vars['data']['SEMPolicyRemark']; ?>
</textarea>
		</td>
	</tr>	
	<tr>
		<td class="cell_label">TM Policy</td>
		<td class="cell_value">
			<select name="tmp[internal][TMPolicy]">
				<?php $_from = $this->_tpl_vars['TMArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
				<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['data']['TMPolicy'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">TM Terms Policy</td>
		<td class="cell_value">
			<select name="tmp[internal][TMTermsPolicy]">
				<?php $_from = $this->_tpl_vars['TMArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['info']):
?>
				<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['data']['TMTermsPolicy'] == $this->_tpl_vars['k']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['data']['ProtectedSEMBiddingKeywords']): ?>
	<tr>
		<td class="cell_label">Protected SEM Bidding Keywords</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['ProtectedSEMBiddingKeywords'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['NonCompeteSEMBiddingKeywords']): ?>
	<tr>
		<td class="cell_label">Non-compete SEM Bidding Keywords</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['NonCompeteSEMBiddingKeywords'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['RecommendedSEMBiddingKeywords']): ?>
	<tr>
		<td class="cell_label">Recommended SEM Bidding Keywords</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['RecommendedSEMBiddingKeywords'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['ProhibitedSEMDisplayURLContent']): ?>
	<tr>
		<td class="cell_label">Prohibited SEM Display URL Content</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['ProhibitedSEMDisplayURLContent'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['LimitedUseSEMDisplayURLContent']): ?>
	<tr>
		<td class="cell_label">Limited Use SEM Display URL Content</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['LimitedUseSEMDisplayURLContent'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['ProhibitedSEMAdCopyContent']): ?>
	<tr>
		<td class="cell_label">Prohibited SEM Ad Copy Content</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['ProhibitedSEMAdCopyContent'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['LimitedUseSEMAdCopyContent']): ?>
	<tr>
		<td class="cell_label">Limited Use SEM Ad Copy Content</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['LimitedUseSEMAdCopyContent'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['AuthorizedSearchEngines']): ?>
	<tr>
		<td class="cell_label">Authorized Search Engines</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['AuthorizedSearchEngines'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['data']['SpecialInstructionsForSEM']): ?>
	<tr>
		<td class="cell_label">Special Instructions for SEM</td>
		<td class="cell_value"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['SpecialInstructionsForSEM'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	</tr>
	<?php endif; ?>
	
		<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input name="ID" id="ID" type="hidden" value="<?php echo $this->_tpl_vars['data']['ID']; ?>
">
			<input name="affID" id="affID" type="hidden" value="<?php echo $this->_tpl_vars['data']['AffId']; ?>
">
			<input name="IdInAff" id="IdInAff" type="hidden" value="<?php echo $this->_tpl_vars['data']['IdInAff']; ?>
">
			<input name="action" type="hidden" value="editfinish">
			<button type="button" class="btn_large" onclick="formSubmit('form1');">Save</button>&nbsp;&nbsp;
			<button type="button" onclick="self.close();">Cancel</button>
		</td>
	</tr>
</table>
</div>
</form>
<?php echo '
<script>
function clearComm(){
	$(\'#f_CommissionUsed\').val(\'0.00\');
}
$(\'#CommissionType\').change(function(){
	if($(this).val() == \'Value\'){
		$(\'#CommissionCurrency\').css(\'display\',\'\');
	}else{
		$(\'#CommissionCurrency\').css(\'display\',\'none\');
	}
});

$().ready(function(){
	$("textarea").attr("class","form-control");
});
</script>
'; ?>

</body>
</html>