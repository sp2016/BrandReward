<html>
<head>
<title>{$data.Name} - {$data.affiliatename}</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/program.js"></script>
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
<link href="http://bdg.mgsvc.com/admin/css/bootstrap.min.css" rel="stylesheet">
<link href="http://bdg.mgsvc.com/admin/css/front.css" rel="stylesheet">
{literal}
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
{/literal}
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
          <a href="http://bdg.mgsvc.com/admin/b_program.php?affiliate={$data.affiliatename}">Program list</a>
        </li>
        <li>
          <a href="http://bdg.mgsvc.com/admin/old_tools/front/program_store_edit.php?ProgramId={$data.ID}" target="_blank">Program Domain Tracking Link Config</a>
        </li>
            
      </ul>      
    </div>
  </div>
</div>
<!-- head-top end  -->
<form name="form1" id="form1" method="post" action="">
<h1 style="text-align:center">Edit Program - {$data.Name}</h1>
<div class="container" style="margin-top:30px;">
<table class="table table-bordered">
	
	<tr><th colspan="2"><font color="white" style="font-size:14px"><b>Basic Info</b></font> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#TermsConditions">Terms & Conditions</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label" width="20%">Program ID in Affiliate</td>
		<td class="cell_value" width="80%">{$data.IdInAff} {if $data.RevenueOrder}<font color='green'>(Rank In Mega: {$data.RevenueOrder})</font>{/if}</td>
	</tr>
	<tr>
		<td class="cell_label">Affiliate</td>
		<td class="cell_value">{$data.affiliatename} ({$data.AffId})</td>
	</tr>
	<tr>
		<td class="cell_label">Name</td>
		<td class="cell_value"><input name="tmp[external][Name]" style="width:450px" type="text" value="{$data.Name|strip_tags}" /></td>
	</tr>
	<tr>
		<td class="cell_label">Homepage</td>
		<td class="cell_value">
			Ext: {$data.Homepage|strip_tags} 
			<input id="homepage_ext" type="hidden" value="{$data.Homepage}">&nbsp;&nbsp;
			<input type="button" value="Open" onclick="OpenURL('homepage_ext');">
			<hr />
			Int: <input id="homepage_int" name="tmp[internal][HomepageInt]" style="width:450px" type="text" value="{$data.HomepageInt|strip_tags}" />&nbsp;&nbsp;			
			<input type="button" value="Open" onclick="OpenURL('homepage_int');">
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Domain</td>
		<td class="cell_value">{$prgm_intell.Domain}</td>
	</tr>
	
	<tr>
		<td class="cell_label">Support Other Domains</td>
		<td class="cell_value"><textarea class="form-control" name="RealDomain">{$prgm_manual.RealDomain}</textarea></td>
	</tr>
	
	<tr>
		<td class="cell_label">Contacts</td>
		<td class="cell_value">
			Ext: {$data.Contacts|strip_tags}
			<hr />
			Int: <input name="tmp[internal][ContactsInt]" style="width:450px" type="text" value="{$data.ContactsInt|strip_tags}" />
		</td>
	</tr>
	<tr>
		<td class="cell_label">Description</td>
		<td class="cell_value">
			<iframe src="../front/program_edit.php?ID={$data.ID}&action=desc" style="width:100%;height:300px" class="form-control"></iframe>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Targeting Countries</td>
		<td class="cell_value">
			Ext: {$data.TargetCountryExt|nl2br} | {$prgm_intell.ShippingCountry}<hr />
			Int OLD: {$data.TargetCountryIntOld|nl2br} <br />
			Int: 
			<table>
				<tr>
					<td>
						<select id="CountryLeft" ondblclick="optionMove('CountryLeft', 'CountryRight')" onkeydown="if(isEnter(event)) {literal}{optionMove('CountryLeft', 'CountryRight');return false;}{/literal}" multiple="multiple" size="10" style="width:200px;">
						{foreach from=$countryArr item=item key=k}
							<option value="{$k}">{$item}</option>
						{/foreach}
						</select>
					</td>
					<td>
						<a onclick="optionMove('CountryLeft', 'CountryRight')" href="javascript:void(0);"> Add Selected &rsaquo;</a><br>
						<a onclick="optionMove('CountryRight', 'CountryLeft')" href="javascript:void(0);"> Remove Selected &lsaquo;</a><br>
						<a onclick="optionMoveAll('CountryLeft', 'CountryRight')" href="javascript:void(0);"> Add All &raquo;</a><br>
						<a onclick="optionMoveAll('CountryRight', 'CountryLeft')" href="javascript:void(0);"> Remove All &laquo;</a> 
					</td>
					<td>
						<select name="tmp[external][TargetCountryInt][]" id="CountryRight" ondblclick="optionMove('CountryRight', 'CountryLeft')" onkeydown="if(isEnter(event)) {literal}{optionMove('CountryRight', 'CountryLeft');return false;}{/literal}" multiple="multiple" size="10" style="width:200px;">
						{html_options options=$data.TargetCountryIntFullNameArr selected=$data.TargetCountryIntArr}
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Rank In Affiliate</td>
		<td class="cell_value">{$data.RankInAff}</td>
	</tr>
	<tr>
		<td class="cell_label">Mobile Friendly</td>
		<td class="cell_value">{$data.MobileFriendly}</td>
	</tr>
	<tr>
		<td class="cell_label">Disallow to Promote</td>
		<td class="cell_value">			
			{foreach from=$AllSite item=item}
				<input type="checkbox" name="tmp[internal][SupportSpread][]" value="{$item}" id="SupportSpread{$item}" {if isset($SupportSpread_arr[$item])}checked{/if} /><label for="SupportSpread{$item}">&nbsp;{$item}</lable>&nbsp;&nbsp;&nbsp;
			{/foreach}
		</td>
	</tr>
	<tr><th colspan="2"><a name="PartnershipStatus"></a><span style="font-size:14px;"><b>Partnership Status</b> | <a href="#">Basic Info</a> | <a href="#TermsConditions">Terms & Conditions</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label">Status In Affiliate</td>
		<td class="cell_value">	
			{$data.StatusInAff}
		</td>
	</tr>
	<tr>
		<td class="cell_label">Partnership</td>
		<td class="cell_value">
			{$data.Partnership}
		</td>
	</tr>
	<tr>
		<td class="cell_label"><font color="green">Program IsActive In BDG</font></td>
		<td class="cell_value">{$prgm_intell.IsActive}</td>
	</tr>
	<tr>
         <td class="cell_label"><font color="green">Program IsActive Manual</font></td>
         <td class="cell_value">
         	<select name="StatusInBdg">
         		<option value="">Default</option>
         		<option value="Active" {if $prgm_manual.StatusInBdg == 'Active'}selected{/if}>Active</option>
         		<option value="Inactive" {if $prgm_manual.StatusInBdg == 'Inactive'}selected{/if}>Inactive</option>
         	</select>
         </td>
	</tr>
	<tr>
		<td class="cell_label">Declined By Mega</td>
		<td class="cell_value">
			<input name="tmp[external][WeDeclined]" type="radio" value="YES" {if $data.WeDeclined eq 'YES'}checked="checked"{/if}>YES&nbsp;&nbsp;
			<input name="tmp[external][WeDeclined]" type="radio" value="NO" {if $data.WeDeclined eq 'NO'}checked="checked"{/if}>NO&nbsp;&nbsp;
			<input name="tmp[external][WeDeclined]" type="radio" value="NoNeedToApply" {if $data.WeDeclined eq 'NoNeedToApply'}checked="checked"{/if}>NoNeedToApply
		</td>
	</tr>
	{*<tr>
		<td class="cell_label">Partnership Create Date</td>
		<td class="cell_value">
			<input name="tmp[external][CreateDate]" type="text" id="CreateDate" size="20" value="{$data.CreateDate}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}">
		</td>
	</tr>
	<tr>
		<td class="cell_label">Partnership Drop Date</td>
		<td class="cell_value">{$data.DropDate}</td>
	</tr>*}
	<tr>
		<td class="cell_label">Apply Date</td>
		<td class="cell_value">
			<input name="tmp[internal][ApplyDate]" type="text" id="ApplyDate" size="20" value="{$data.ApplyDate}" onFocus="{literal}WdatePicker({startDate:'%y-%M-01 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true});{/literal}">
		</td>
	</tr>
	{*<tr>
		<td class="cell_label">Apply Operator</td>
		<td class="cell_value">{$data.ApplyOperator}</td>
	</tr>*}
	<tr>
		<td class="cell_label">Reapply Status</td>
		<td class="cell_value">					
			<select name="tmp[internal][ReApplyStatus]">
				<option value="UNKNOWN">UNKNOWN</option>						
				<option value="In-Progress" {if $data.ReApplyStatus eq "In-Progress"}selected="selected"{/if}>In-Progress</option>
				<option value="Positive" {if $data.ReApplyStatus eq "Positive"}selected="selected"{/if}>Positive</option>
				<option value="Negative" {if $data.ReApplyStatus eq "Negative"}selected="selected"{/if}>Negative</option>				
			</select>
		</td>
	</tr>
	<input name="tmp[internal][ApplyOperator]" type="hidden" value="{$user}">
	<input name="applydateold" type="hidden" value="{$data.ApplyDate}">
	<tr>
		<td class="cell_label">Partnership Change Reason</td>
		<td class="cell_value"><textarea name="tmp[external][PartnershipChangeReason]" rows="7" cols="60">{$data.PartnershipChangeReason}</textarea></td>
	</tr>
	<tr>
		<td class="cell_label">Cooperate With Coupon Site</td>
		<td class="cell_value">
			<select name="tmp[external][CooperateWithCouponSite]">
				<option value="NO" {if $data.CooperateWithCouponSite eq "NO"}selected="selected"{/if}>NO</option>
				<option value="YES" {if $data.CooperateWithCouponSite eq "YES"}selected="selected"{/if}>YES</option>						
			</select>						
		</td>
	</tr>
	<tr>
		<td class="cell_label">Remark</td>
		<td class="cell_value"><textarea name="tmp[external][Remark]" rows="7" cols="60">{$data.Remark}</textarea></td>
	</tr>
	
	<tr><th colspan="2"><a name="TermsConditions"></a><span style="font-size:14px;"><b>Terms & Conditions</b> | <a href="#">Basic Info</a> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#SEMPolicy">SEM Policy</a> </span></th></tr>
	<tr>
		<td class="cell_label">Commission</td>
		<td class="cell_value">
			<iframe src="../front/program_edit.php?ID={$data.ID}&action=commissionExt" style="width:100%;height:300px" class="form-control"></iframe>
			Int: <textarea name="tmp[internal][CommissionInt]" rows="7" cols="60">{$data.CommissionInt}</textarea>
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Commission analyzed</td>
		<td class="cell_value">			
			{$prgm_intell.CommissionUsed}({if $prgm_intell.CommissionType == 'Value'}{$prgm_intell.CommissionCurrency}{else}%{/if})
		</td>
	</tr>
	
	<tr>
		<td class="cell_label">Commission Manual</td>
		<td class="cell_value">			
			<div class="form-inline">
         		<div class="form-group">
         			<input type="text" id="f_CommissionUsed"  class="form-control" value="{$prgm_manual.CommissionUsed}" name="CommissionUsed" />
         		</div>
         		<div  class="form-group">
         			<select id="CommissionType" class="form-control" name="CommissionType">
         				<option value="Value" {if $prgm_manual.CommissionType == 'Value'}selected{/if}>Value</option>
         				<option value="Percent" {if $prgm_manual.CommissionType == 'Percent'}selected{/if}>Percent</option>
         			</select>
         		</div>
         		<div id="CommissionCurrency" class="form-group">
         			<select class="form-control" name="CommissionCurrency">
         			<option value='' selected>CommissionCurrency</option>
         				{foreach from=$currency item=c}
         					<option value={$c} {if $prgm_manual.CommissionCurrency == $c}selected{/if}>{$c}</option>
         				{/foreach}
         			</select>
         		</div>
         		<div class="form-group">
         			<input type="button" class="btn  btn-primary " value="Clear" onclick="clearComm()">
         		</div>
         	</div>
		</td>
	</tr>
	
	{*<tr>
		<td class="cell_label">Bonus</td>
		<td class="cell_value">
			{$data.BonusExt|nl2br}<hr />
			<textarea name="tmp[internal][BonusInt]" rows="7" cols="60">{$data.BonusInt}</textarea>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Contest</td>
		<td class="cell_value">
			{$data.ContestExt|nl2br}<hr />
			<textarea name="tmp[internal][ContestInt]" rows="7" cols="60">{$data.ContestInt}</textarea>
		</td>
	</tr>*}
	<tr>
		<td class="cell_label">EPC</td>
		<td class="cell_value"><b>EPCDefault: </b>{$data.EPCDefault}&nbsp;&nbsp;&nbsp;&nbsp;<b>EPC30d: </b>{$data.EPC30d}&nbsp;&nbsp;&nbsp;&nbsp;<b>EPC90d: </b>{$data.EPC90d}</td>
	</tr>
	<tr>
		<td class="cell_label">Cookie Time</td>
		<td class="cell_value">{$data.CookieTime}</td>
	</tr>
	<tr>
		<td class="cell_label">Has Pending Offer</td>
		<td class="cell_value">{$data.HasPendingOffer}</td>
	</tr>
	<tr>
		<td class="cell_label">Number of Occurrences</td>
		<td class="cell_value">{$data.NumberOfOccurrences}</td>
	</tr>
	<tr>
		<td class="cell_label">Term And Condition</td>
		<td class="cell_value">{$data.TermAndCondition|nl2br}</td>
	</tr>
	<tr>
		<td class="cell_label">Coupon Codes Policy</td>
		<td class="cell_value">
			{$data.CouponCodesPolicyExt|nl2br}<hr />
			<textarea name="tmp[internal][CouponCodesPolicyInt]" rows="7" cols="60">{$data.CouponCodesPolicyInt}</textarea>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Sub Affiliate Policy</td>
		<td class="cell_value">
			{$data.SubAffPolicyExt|nl2br}<hr />
			<textarea name="tmp[internal][SubAffPolicyInt]" rows="7" cols="60">{$data.SubAffPolicyInt}</textarea>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Complaint</td>
		<td class="cell_value"><textarea name="tmp[external][Complaint]" rows="7" cols="60">{$data.Complaint}</textarea></td>
	</tr>
	
	<tr>
		<td class="cell_label">Support SEO</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportSEO]">						
				<option value="UNKNOWN" {if $data.SupportSEO eq "UNKNOWN"}selected="selected"{/if}>UNKNOWN</option>
				<option value="YES" {if $data.SupportSEO eq "YES"}selected="selected"{/if}>YES</option>
				<option value="NO" {if $data.SupportSEO eq "NO"}selected="selected"{/if}>NO</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Support SNS</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportSNS]">						
				<option value="UNKNOWN" {if $data.SupportSNS eq "UNKNOWN"}selected="selected"{/if}>UNKNOWN</option>
				<option value="YES" {if $data.SupportSNS eq "YES"}selected="selected"{/if}>YES</option>
				<option value="NO" {if $data.SupportSNS eq "NO"}selected="selected"{/if}>NO</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">Support Email</td>
		<td class="cell_value">			
			<select name="tmp[internal][SupportEmail]">						
				<option value="UNKNOWN" {if $data.SupportEmail eq "UNKNOWN"}selected="selected"{/if}>UNKNOWN</option>
				<option value="YES" {if $data.SupportEmail eq "YES"}selected="selected"{/if}>YES</option>
				<option value="NO" {if $data.SupportEmail eq "NO"}selected="selected"{/if}>NO</option>				
			</select>
		</td>
	</tr>
	
	<tr><th colspan="2"><a name="SEMPolicy"></a><span style="font-size:14px;"><b>SEM Policy</b> | <a href="#">Basic Info</a> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#TermsConditions">Terms & Conditions</a> </span></th></tr>
	<tr>
		<td class="cell_label">Inquiry Status</td>
		<td class="cell_value">
			<select name="tmp[internal][InquiryStatus]">						
				<option value="Not Inquired" {if $data.InquiryStatus eq "Not Inquired"}selected="selected"{/if}>Not Inquired</option>
				<option value="Inquiring" {if $data.InquiryStatus eq "Inquiring"}selected="selected"{/if}>Inquiring</option>
				<option value="Inquired" {if $data.InquiryStatus eq "Inquired"}selected="selected"{/if}>Inquired</option>				
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">SEM Policy Remark</td>
		<td class="cell_value">
			{$data.SEMPolicyExt|nl2br}<hr />
			<textarea name="tmp[external][SEMPolicyRemark]" rows="7" cols="60">{$data.SEMPolicyRemark}</textarea>
		</td>
	</tr>	
	<tr>
		<td class="cell_label">TM Policy</td>
		<td class="cell_value">
			<select name="tmp[internal][TMPolicy]">
				{foreach from=$TMArr item=info key=k}
				<option value="{$k}" {if $data.TMPolicy eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="cell_label">TM Terms Policy</td>
		<td class="cell_value">
			<select name="tmp[internal][TMTermsPolicy]">
				{foreach from=$TMArr item=info key=k}
				<option value="{$k}" {if $data.TMTermsPolicy eq $k}selected="selected"{/if}>{$info}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	{if $data.ProtectedSEMBiddingKeywords}
	<tr>
		<td class="cell_label">Protected SEM Bidding Keywords</td>
		<td class="cell_value">{$data.ProtectedSEMBiddingKeywords|nl2br}</td>
	</tr>
	{/if}
	{if $data.NonCompeteSEMBiddingKeywords}
	<tr>
		<td class="cell_label">Non-compete SEM Bidding Keywords</td>
		<td class="cell_value">{$data.NonCompeteSEMBiddingKeywords|nl2br}</td>
	</tr>
	{/if}
	{if $data.RecommendedSEMBiddingKeywords}
	<tr>
		<td class="cell_label">Recommended SEM Bidding Keywords</td>
		<td class="cell_value">{$data.RecommendedSEMBiddingKeywords|nl2br}</td>
	</tr>
	{/if}
	{if $data.ProhibitedSEMDisplayURLContent}
	<tr>
		<td class="cell_label">Prohibited SEM Display URL Content</td>
		<td class="cell_value">{$data.ProhibitedSEMDisplayURLContent|nl2br}</td>
	</tr>
	{/if}
	{if $data.LimitedUseSEMDisplayURLContent}
	<tr>
		<td class="cell_label">Limited Use SEM Display URL Content</td>
		<td class="cell_value">{$data.LimitedUseSEMDisplayURLContent|nl2br}</td>
	</tr>
	{/if}
	{if $data.ProhibitedSEMAdCopyContent}
	<tr>
		<td class="cell_label">Prohibited SEM Ad Copy Content</td>
		<td class="cell_value">{$data.ProhibitedSEMAdCopyContent|nl2br}</td>
	</tr>
	{/if}
	{if $data.LimitedUseSEMAdCopyContent}
	<tr>
		<td class="cell_label">Limited Use SEM Ad Copy Content</td>
		<td class="cell_value">{$data.LimitedUseSEMAdCopyContent|nl2br}</td>
	</tr>
	{/if}
	{if $data.AuthorizedSearchEngines}
	<tr>
		<td class="cell_label">Authorized Search Engines</td>
		<td class="cell_value">{$data.AuthorizedSearchEngines|nl2br}</td>
	</tr>
	{/if}
	{if $data.SpecialInstructionsForSEM}
	<tr>
		<td class="cell_label">Special Instructions for SEM</td>
		<td class="cell_value">{$data.SpecialInstructionsForSEM|nl2br}</td>
	</tr>
	{/if}
	
	{*
	<tr><td colspan="2"><a name="AdditionalInfo"></a><span style="font-size:14px;"><b>Additional Info</b> | <a href="#">Basic Info</a> | <a href="#PartnershipStatus">Partnership Status</a> | <a href="#TermsConditions">Terms & Conditions</a> | <a href="#SEMPolicy">SEM Policy</a></span></td></tr>
	<tr>
		<td class="cell_label">Merchant List</td>
		<td class="cell_value">
			{if !empty($data.merchantlist)}
			<table cellspacing="1" cellpadding="4">
				<tr>
					<td width="50px" style="font-weight:bold">Site</td>
					<!-- <td width="100px" style="font-weight:bold">Merchant ID</td> -->
					<td width="150px" style="font-weight:bold">Merchant</td>
					<td width="150px" style="font-weight:bold">Deep URL Template</td>
					<td width="150px" style="font-weight:bold">Aff URL</td>
					<td width="50px" style="font-weight:bold">Order</td>               
					<td style="font-weight:bold">Action</td>
				</tr>
				{foreach from=$data.merchantlist item=item key=key}
				<tr>
					<td>{$item.Site}</td>
					<!-- <td>{$item.MerchantId}</td> -->
					<td>({$item.MerchantId}){$item.MerchantName}</td>
					<td>{$item.deep_url_tpl}</td>
					<td>{$item.aff_url}</td>
					<td>{$item.orderbynum}</td>
					<td>
						<a href="{$item.merchantPage}" target="_blank">Merchant Page</a><br />
						<a href="{$item.merchantEdit}" target="_blank">Merchant Edit</a><br />
					</td>
				</tr>
			{/foreach}
			</table>
			{/if}
		</td>
	</tr>	
	<tr>
		<td class="cell_label">Remind</td>
		<td class="cell_value">
			{if !empty($data.remind)}
			<table cellspacing="1" cellpadding="4">
				<tr>
					<td width="100px" style="font-weight:bold">Operator</td>
					<td width="100px" style="font-weight:bold">Remind Date</td>
					<td width="400px" style="font-weight:bold">Message</td>
				</tr>
				{foreach from=$data.remind item=item key=key}
				<tr {if $item.RemindDate eq $smarty.now|date_format:"%Y-%m-%d"}bgcolor="#66FF66"{/if}>
					<td>{$item.Operator}</td>
					<td>{$item.RemindDate}</td>
					<td>{$item.Message}</td>
				</tr>
				{/foreach}
				<tr><td colspan="3">&nbsp;</td></tr>
			</table>
			{/if}
			<table cellspacing="1" cellpadding="4">
				<tr>
					<td style="font-weight:bold;">Remind Date</td>
					<td><input name="remind[RemindDate]" id="remindate" type="text" onFocus="{literal}WdatePicker({readOnly:true});{/literal}"></td>
				</tr>
				<tr>
					<td style="font-weight:bold;">Message</td>
					<td><textarea name="remind[Message]" rows="5" cols="50"></textarea></td>
				</tr>
			</table>
		</td>
	</tr>
	*}
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input name="ID" id="ID" type="hidden" value="{$data.ID}">
			<input name="affID" id="affID" type="hidden" value="{$data.AffId}">
			<input name="IdInAff" id="IdInAff" type="hidden" value="{$data.IdInAff}">
			<input name="action" type="hidden" value="editfinish">
			<button type="button" class="btn_large" onclick="formSubmit('form1');">Save</button>&nbsp;&nbsp;
			<button type="button" onclick="self.close();">Cancel</button>
		</td>
	</tr>
</table>
</div>
</form>
{literal}
<script>
function clearComm(){
	$('#f_CommissionUsed').val('0.00');
}
$('#CommissionType').change(function(){
	if($(this).val() == 'Value'){
		$('#CommissionCurrency').css('display','');
	}else{
		$('#CommissionCurrency').css('display','none');
	}
});

$().ready(function(){
	$("textarea").attr("class","form-control");
});
</script>
{/literal}
</body>
</html>