<?php /* Smarty version 2.6.26, created on 2017-12-01 00:22:27
         compiled from add_affiliates.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'add_affiliates.html', 348, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_banner.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<style type="text/css">
.table>tbody>tr>td,.table>tbody>tr>th,.table>tfoot>tr>td,.table>tfoot>tr>th,.table>thead>tr>td,.table>thead>tr>th
{
 vertical-align:middle;
}
label
{
	font-weight:100;
}
</style>
<h1 style="text-align:center">Add Affiliate</h1>
<div class="container" style="margin-top:30px;">
<form action="b_aff_aff.php" method="post">
<table class="table table-bordered">


   <tbody>
   <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Basic</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>Name</td>
         <td><input id="name" type="text" name="Name" class="form-control"  ><div id="hide_name" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div></td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Short Name</td>
         <td><input type="text" name="ShortName" class="form-control" ></td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Importance Rank</td>
         <td><input type="text" name="ImportanceRank" class="form-control" ></td>
      
      </tr>
       <tr>
           <td style="text-align:right;background-color:#EEE">Account</td>
           <td><input type="text" name="Account" class="form-control" ></td>

       </tr>
       <tr>
           <td style="text-align:right;background-color:#EEE">Password</td>
           <td><input type="text" name="Password" class="form-control" ></td>

       </tr>
        <tr>
         <td style="text-align:right;background-color:#EEE">Join Date</td>
         <td>
 <input type="text"  name="JoinDate" class="form-control" value="<?php echo $this->_tpl_vars['timeNow']; ?>
" id="datetimepicker">
    
    </td>
      
      </tr>
      
      
      
      
      <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Control</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>IsActive</td>
         <td>
         <select id="isActive" name="IsActive" class="form-control">
                <option value="">To Choose</option>
              	<option value="YES">YES</option>
                <option value="NO">NO</option>
         </select>
         <div id="hide_isActive" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div>
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>IsInHouse</td>
         <td>   <select id="isInHouse" name="IsInHouse" class="form-control">
                <option value="">To Choose</option>
              	<option value="NO">NetWork</option>
                <option value="YES">InHouse</option>
         </select>
         <div id="hide_isInHouse" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div>
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Login Url</td>
         <td><input type="text" name="LoginUrl" class="form-control"></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Program Url Template</td>
         <td><input type="text" name="ProgramUrlTemplate" class="form-control" ></td>
      
      </tr>
      
      
      
      
      
      
      
      
            <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Additional</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>Domain</td>
         <td>
         <input type="text" id="domain" name="Domain" class="form-control" placeholder="Start with http:// OR https://">
         <div id="hide_domain" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div>
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Blog</td>
         <td>  <input type="text" name="BlogUrl" class="form-control" placeholder="Start with http://">
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Facebook</td>
         <td><input type="text" name="FacebookUrl" class="form-control" placeholder="Start with http://"></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Twitter</td>
         <td><input type="text" name="TwitterUrl" class="form-control" placeholder="Start with http://"></td>
      
      </tr>
      
      
      
       <tr>
         <td style="text-align:right;background-color:#EEE">Country</td>
         <td>
         
         
         
         
         
           
         <table border="0" style="width:100%;">
			<tbody><tr>
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="AR">&nbsp;<label>Argentina(AR)</label></td>
														<td><input type="checkbox" name="Country[]" value="AU">&nbsp;<label>Australia(AU)</label></td>
														<td><input type="checkbox" name="Country[]" value="AT">&nbsp;<label>Austria</label></td>
														<td><input type="checkbox" name="Country[]" value="BE">&nbsp;<label>Belgium(BE)</label></td>
														
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="CA">&nbsp;<label>Canada(CA)</label></td>
														<td><input type="checkbox" name="Country[]" value="CZ">&nbsp;<label>Czech Republic(CZ)</label></td>
														<td><input type="checkbox" name="Country[]" value="CY">&nbsp;<label>Cyprus(CY)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="CN">&nbsp;<label>China(CN)</label></td>
														</tr><tr>
														
														
														<td><input type="checkbox" name="Country[]" value="CR">&nbsp;<label>Costa Rica(CR)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="DK">&nbsp;<label>Denmark(DK)</label></td>
														<td><input type="checkbox" name="Country[]" value="EU">&nbsp;<label>European Union(EU)</label></td>
														<td><input type="checkbox" name="Country[]" value="SV">&nbsp;<label>El Salvador(SV)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="EE">&nbsp;<label>Estonia(EE)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="FR">&nbsp;<label>France(FR)</label></td>
														<td><input type="checkbox" name="Country[]" value="FI">&nbsp;<label>Finland(FI)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="DE">&nbsp;<label>German(DE)</label></td>
														
							
														</tr>
														
														<tr>
														<td><input type="checkbox" name="Country[]" value="GI">&nbsp;<label>Gibraltar(GI)</label></td>
														<td><input type="checkbox" name="Country[]" value="GP">&nbsp;<label>Guadeloupe(GP)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="GLOBAL">&nbsp;<label>GLOBAL(GLOBAL)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="GR">&nbsp;<label>Greece(GR)</label></td>
														</tr>
														
														
														<tr>
										
														<td><input type="checkbox" name="Country[]" value="HK">&nbsp;<label>Hong Kong(HK)</label></td>
														<td><input type="checkbox" name="Country[]" value="IL">&nbsp;<label>Israel(IL)</label></td>
														<td><input type="checkbox" name="Country[]" value="IN">&nbsp;<label>India(IN)</label></td>
														<td><input type="checkbox" name="Country[]" value="ID">&nbsp;<label>Indonesia(ID)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="IE">&nbsp;<label>Ireland(IE)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="IT">&nbsp;<label>Italy(IT)</label></td>
														<td><input type="checkbox" name="Country[]" value="JP">&nbsp;<label>Japan(JP)</label></td>
														<td><input type="checkbox" name="Country[]" value="LV">&nbsp;<label>Latvia(LV)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="LU">&nbsp;<label>Luxembourg(LU)</label></td>
										
														<td><input type="checkbox" name="Country[]" value="MX">&nbsp;<label>Mexico(MX)</label></td>
														<td><input type="checkbox" name="Country[]" value="MY">&nbsp;<label>Malaysia(MY)</label></td>
														<td><input type="checkbox" name="Country[]" value="MA">&nbsp;<label>Morocco(MA)</label></td>
														
														
														</tr>
														<tr>
														<td><input type="checkbox" name="Country[]" value="NL">&nbsp;<label>Netherlands(NL)</label></td>
														<td><input type="checkbox" name="Country[]" value="NO">&nbsp;<label>Norway(NO)</label></td>
														<td><input type="checkbox" name="Country[]" value="NZ">&nbsp;<label>New Zealand(NZ)</label></td>
														<td><input type="checkbox" name="Country[]" value="PH">&nbsp;<label>Philippines(PH)</label></td>
														
														</tr>
														<tr>
										
														<td><input type="checkbox" name="Country[]" value="PL">&nbsp;<label>Poland(PL)</label></td>
														<td><input type="checkbox" name="Country[]" value="PT">&nbsp;<label>Portugal(PT)</label></td>
														<td><input type="checkbox" name="Country[]" value="QA">&nbsp;<label>Qatar(QA)</label></td>
														<td><input type="checkbox" name="Country[]" value="RO">&nbsp;<label>Romania(RO)</label></td>
														
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="ZA">&nbsp;<label>South Africa(ZA)</label></td>
														<td><input type="checkbox" name="Country[]" value="SE">&nbsp;<label>Sweden(SE)</label></td>
														<td><input type="checkbox" name="Country[]" value="SG">&nbsp;<label>Singapore(SG)</label></td>
														<td><input type="checkbox" name="Country[]" value="ES">&nbsp;<label>Spain(ES)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="CH">&nbsp;<label>Switzerland(CH)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="TW">&nbsp;<label>Taiwan(TW)</label></td>
										
														<td><input type="checkbox" name="Country[]" value="TH">&nbsp;<label>Thailand(TH)</label></td>
														<td><input type="checkbox" name="Country[]" value="AE">&nbsp;<label>United Arab Emirates(AE)</label></td>
														
														</tr>
														
							<tr>
										<td><input type="checkbox" name="Country[]" value="UK">&nbsp;<label>United Kingdom(UK)</label></td>
										<td><input type="checkbox" name="Country[]" value="US">&nbsp;<label>United States(US)</label></td>
										<td><input type="checkbox" name="Country[]" value="VG">&nbsp;<label>Virgin Island, British(VG)</label></td>
										
							</tr>
							<tr>
							
							
							</tr>
							<tr>
							
							
							</tr>
			</tbody></table>
         
         
         
         
         
         
         
         
         
         </td>
      
      </tr>
      
      
      
      
                  <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Aff Url Control</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>AffiliateUrl Keyword List 1</td>
         <td>
         <textarea class="form-control" id="AffiliateUrlKeywords" name="AffiliateUrlKeywords" rows="5" ></textarea>
         <div id="hide_AffiliateUrlKeywords" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div>
         </td>
         
      </tr>
        <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red" style="font-size:20px;">*</font>AffiliateUrl Keyword List 2</td>
         <td>
         <textarea class="form-control" id="AffiliateUrlKeywords2" name="AffiliateUrlKeywords2" rows="5" ></textarea>
         <div id="hide_AffiliateUrlKeywords2" style="display:none"><font color="red" style="font-size:15px;">*cannot be null</font></div>
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Support Deep Url</td>
         <td>  <select name="SupportDeepUrl" class="form-control">
                <option value="">To Choose</option>
              	<option value="YES">YES</option>
                <option value="NO">NO</option>
         </select>
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Deep Url Paraname</td>
         <td><input type="text" name="DeepUrlParaName" class="form-control" ></td>
      
      </tr>
        <tr>
         <td style="text-align:right;background-color:#EEE">Support Sub Tracking</td>
         <td>  <select name="SupportSubTracking"  class="form-control">
                <option value="">To Choose</option>
              	<option value="YES">YES</option>
                <option value="NO">NO</option>
         </select>
         </td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Sub Tracking Setting 1</td>
         <td><input type="text" name="SubTracking" class="form-control" ></td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Sub Tracking Setting 2</td>
         <td><input type="text" name="SubTracking2" class="form-control" ></td>
      
      </tr>
      
      
      
                <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Revenue Control</font></td>
         
         
      </tr>
      
                  <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Revenue Account</td>
         <td>
        
<select name="RevenueAccount" class="form-control"  >
    <option  value="" >To Choose</option>

<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['fin_rev_acc_list'],'selected' => $this->_tpl_vars['search']['revenueAccount']), $this);?>



         </select>
           
         </td>
         </tr>
         <tr>
                           <td width="20%" style="text-align:right;background-color:#EEE">Revenue Cycle</td>
         
                  <td> <textarea name="RevenueCycle" class="form-control" rows="5" ></textarea>
        
         </td>
      </tr>
        <tr>
                           <td width="20%" style="text-align:right;background-color:#EEE">Revenue Remark</td>
         
                  <td><textarea name="RevenueRemark" class="form-control" rows="5" ></textarea>
         
         </td>
      </tr>
      
      
      
      
      
                <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Stats</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Program Crawled</td>
         <td>
<select name="ProgramCrawled" class="form-control">
                <option value="">To Choose</option>
              	<option value="YES">YES</option>
                <option value="NO">NO</option>
                <option value="No Need to Crawl">No Need to Crawl</option>
                <option value="Request to Crawl">Request to Crawl</option>
                <option value="Can Not Crawl">Can Not Crawl</option>
         </select>         
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Program Crawl Remark</td>
         <td>           
         <textarea class="form-control" name="ProgramCrawlRemark" rows="5" ></textarea>

         </td>
      
      </tr>
            <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Stats Report Crawled</td>
         <td>
<select name="StatsReportCrawled" class="form-control">
                <option value="">To Choose</option>
              	<option value="YES">YES</option>
                <option value="NO">NO</option>
                <option value="No Need to Crawl">No Need to Crawl</option>
                <option value="Request to Crawl">Request to Crawl</option>
                <option value="Can Not Crawl">Can Not Crawl</option>
         </select>         
         </td>
         
      </tr>
         <tr>
         <td style="text-align:right;background-color:#EEE">Stats Report Crawl Remark</td>
         <td>           
         <textarea name="StatsReportCrawlRemark" class="form-control" rows="5" ></textarea>

         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Stats Affiliate Name</td>
         <td><input name="StatsAffiliateName" type="text" class="form-control" ></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Comment</td>
         <td>
         <textarea name="Comment" class="form-control" rows="5" ></textarea>

</td>
      
      </tr>
   </tbody>
</table>
<div style="text-align:center;">
<input class="btn btn-primary" id="sub" type="submit" value="提交"/>
<input type="button" id="return" class="btn btn-primary" value="返回"/>

</div>
</form>
</div>
<script type="text/javascript">
$("#return").click(function(){

	window.location.href="b_aff_aff.php";
	
});
$("#sub").click(function(){
	if($("#name").val()==""){
		$("#hide_name").show();
		$("#sub").attr("type","button");
	}
	if($("#name").val()!==""){
		$("#hide_name").hide();
		$("#sub").attr("type","submit");
	}
	
	
	if($("#isActive").val()==""){
		$("#hide_isActive").show();
		$("#sub").attr("type","button");
	}
	if($("#isActive").val()!==""){
		$("#hide_isActive").hide();
		$("#sub").attr("type","submit");
	}
	
	
	if($("#isInHouse").val()==""){
		$("#hide_isInHouse").show();
		$("#sub").attr("type","button");
	}
	if($("#isInHouse").val()!==""){
		$("#hide_isInHouse").hide();
		$("#sub").attr("type","submit");
	}
	
	
	if($("#domain").val()==""){
		$("#hide_domain").show();
		$("#sub").attr("type","button");
	}
	if($("#domain").val()!==""){
		$("#hide_domain").hide();
		$("#sub").attr("type","submit");
	}        
	
	
	
	
	if($("#AffiliateUrlKeywords").val()==""){
		$("#hide_AffiliateUrlKeywords").show();
		$("#sub").attr("type","button");
	}
	if($("#AffiliateUrlKeywords").val()!==""){
		$("#hide_AffiliateUrlKeywords").hide();
		$("#sub").attr("type","submit");
	} 
	
	
	
	
	
	
	if($("#AffiliateUrlKeywords2").val()==""){
		$("#hide_AffiliateUrlKeywords2").show();
		$("#sub").attr("type","button");
	}
	if($("#AffiliateUrlKeywords2").val()!==""){
		$("#hide_AffiliateUrlKeywords2").hide();
		$("#sub").attr("type","submit");
	} 
	

});



//join date的时间插件

      $('#datetimepicker').datetimepicker({  
        format: 'yyyy-mm-dd hh:ii:ss',  
        language: 'en',  
        pickDate: true,  
        pickTime: true,  
        hourStep: 1,  
        minuteStep: 1,  
        secondStep: 15,  
        inputMask: true,  
      });  
   
</script>

