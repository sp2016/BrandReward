<html>
<head>
<title>Add Program - {$affiliatename}</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/program.js"></script>
<script language="JavaScript" src="../js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="../js/program_search.js"></script>
<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" />
{literal}
<style type="text/css">
.cell_label{text-align:right;font-weight:bold;background-color:#EEEEEE;border:1px solid #DDDDDD;}
.cell_value{text-align:left;border:1px solid #DDDDDD;}
body,p,a,td,tr,th,table,li,h1,h2,h3{font-family:Tahoma,Helvetica,Arial,Sans-Serif;font-size:12px;line-height:1.5;}
h1{font-size:16px;font-weight:bold;line-height:28px;}
th{align:center;background:#525274;color:#FFFFFF;}
.row_odd td{background-color:#FFFFFF;}
.row_even td{background-color:#EEEEEE;border:1px solid #DDDDDD; }
a:link,a:visited{color:#0080c0;text-decoration:none;}
a:hover,a:active{color:#0080C0;text-decoration:underline;}
hr{margin:3 auto;height:1px;background-color:#999999;border:none;}
.td_value{text-align:left;background-color:#FFFFFF;border:1px solid #DDDDDD;}
.td_label{text-align:right;background-color:#EEEEEE;border:1px solid #DDDDDD;}
.sub{padding-left:10px;}
.btn_large{width:120px;height:40px;font-family:Tahoma,Arial;font-size:16px;} 
</style>

<script type="text/javascript">
function addProgram(){
	if($("#affiliatename").val() == "" || $("#affiliatetype").val() == ""){
		alert("Please select an affiliate.");
	}else if($("#idinaff").val() == "" || $("#name").val() == ""){
		alert("Please input Program ID in Affiliate and Name.");
	}else{
		formSubmit('form1');
	}
}
</script>
{/literal}
</head>
<body>
<form name="form1" id="form1" method="post" action="">
<div style="text-align:center;width:100%;"><h1>Add Program</h1></div>
<table width="100%" cellspacing="1" cellpadding="5" bgcolor="#BFE0F7">
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label" width="200px">Affiliate</td>
		<td class="cell_value">
			<input type="text" id="affiliatename" name="affiliatename" value="{$affiliatename}" size="30" />&nbsp;<input type="button" value="reset" onclick="resetAff()" />
			<input type="hidden" name="affiliatetype" id="affiliatetype" value="{$affid}" />&nbsp;&nbsp;
		</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Program ID in Affiliate</td>
		<td class="cell_value"><input type="text" id="idinaff" name="idinaff" /></td>
	</tr>				
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Name</td>
		<td class="cell_value"><input type="text" id="name" name="name" /></td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Homepage</td>
		<td class="cell_value"><input type="text" name="homepage" style="width:390px"></textarea></td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Contacts</td>
		<td class="cell_value"><textarea name="contacts" rows="7" cols="60"></textarea></td>
	</tr>				
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Targeting Countries</td>
		<td class="cell_value">					
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
						<select name="targetcountryint" id="CountryRight" ondblclick="optionMove('CountryRight', 'CountryLeft')" onkeydown="if(isEnter(event)) {literal}{optionMove('CountryRight', 'CountryLeft');return false;}{/literal}" multiple="multiple" size="10" style="width:200px;">
						{html_options options=$data.TargetCountryIntFullNameArr selected=$data.TargetCountryIntArr}
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Categories</td>
		<td class="cell_value"><textarea name="categories" rows="7" cols="60"></textarea></td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Group Inc</td>
		<td class="cell_value"><input name="GroupInc" style="width:150px" id="Group" type="text" value="" />&nbsp;&nbsp;</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Partnership Status</td>
		<td class="cell_value">
			<select name="partnership">
				<option value="Active" >Active</option>
				<option value="NoPartnership" >NoPartnership</option>							
				<option value="Pending" >Pending</option>
				<option value="Declined" >Declined</option>							
				<option value="Expired" >Expired</option>
				<option value="Removed" >Removed</option>							
			</select>
		</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">TM Policy</td>
		<td class="cell_value">
			<select name="TMPolicy">
				{foreach from=$TMArr item=info key=k}
				<option value="{$k}">{$info}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">TM Terms Policy</td>
		<td class="cell_value">
			<select name="TMTermsPolicy">
				{foreach from=$TMArr item=info key=k}
				<option value="{$k}">{$info}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr bgcolor="{cycle values="#FFFFFF,#EEEEEE"}" onmouseover="addColor(this);" onmouseout="removeColor(this);">
		<td class="cell_label">Commission</td>
		<td class="cell_value">
			<textarea name="CommissionInt" rows="7" cols="60"></textarea>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">						
			<input name="action" type="hidden" value="doadd">
			<button type="button" class="btn_large" onclick="addProgram();">Save</button>
			<button type="button" onclick="self.close();">Cancel</button>
		</td>
	</tr>
</table>

</form>
</body>
</html>