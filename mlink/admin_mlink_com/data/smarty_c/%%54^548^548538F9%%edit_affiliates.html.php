<?php /* Smarty version 2.6.26, created on 2017-12-21 02:33:40
         compiled from edit_affiliates.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'in_array', 'edit_affiliates.html', 199, false),array('function', 'html_options', 'edit_affiliates.html', 406, false),)), $this); ?>
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
xmp
{
	  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
}
label
{
	font-weight:100;
}
</style>
<?php if ($this->_tpl_vars['action'] == edit): ?>
<h1 style="text-align:center">Edit Affiliate</h1>
<?php else: ?>
<h1 style="text-align:center">View Affiliate</h1>
<?php endif; ?>
<div class="container" style="margin-top:30px;">
<form action=""  method="post">
<table class="table table-bordered">


   <tbody>
   <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Basic</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red">*</font>Name</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['Name']; ?>
<?php else: ?>  <input  type="text" name="Name" id="Name" class="form-control" value="<?php echo $this->_tpl_vars['arr']['Name']; ?>
"> <?php endif; ?> </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Short Name</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['ShortName']; ?>
<?php else: ?>  <input  type="text" name="ShortName" class="form-control" value="<?php echo $this->_tpl_vars['arr']['ShortName']; ?>
"> <?php endif; ?></td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Current Name</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['CurrentName']; ?>
<?php else: ?>  <input  type="text" name="CurrentName" class="form-control" value="<?php echo $this->_tpl_vars['arr']['CurrentName']; ?>
"> <?php endif; ?></td>
      
      </tr>
       <tr>
           <td style="text-align:right;background-color:#EEE">Account</td>
           <td><input type="text" id = "Account" style="width:500px" class="form-control" value="<?php echo $this->_tpl_vars['arr']['Account']; ?>
" readonly="readonly"></td>
            <input type="hidden" id = "id" value="<?php echo $this->_tpl_vars['id']; ?>
">
       </tr>

       <tr>
           <td style="text-align:right;background-color:#EEE">Password</td>
           <td><div class="form-inline">
               <input type="text" id="Password" style="width:500px" class="form-control" value="<?php echo $this->_tpl_vars['arr']['Password']; ?>
" readonly="readonly">
               <input id="change_password" style="margin-left:15px" type="button" value="修改" class="form-control btn-primary">
           </div>
           </td>
       </tr>
   <tr>
       <td style="text-align:right;background-color:#EEE">Manager</td>
       <td>
           <?php if ($this->_tpl_vars['action'] == view): ?>
           <?php echo $this->_tpl_vars['arr']['Manager']; ?>

           <?php else: ?>
           <select name="Manager"  class="form-control"  >
               <option value="value">To Choose</option>
               <?php $_from = $this->_tpl_vars['managers']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['manager']):
?>
               <option value="<?php echo $this->_tpl_vars['manager']; ?>
" <?php if ($this->_tpl_vars['arr']['Manager'] == $this->_tpl_vars['manager']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['manager']; ?>
</option>
               <?php endforeach; endif; unset($_from); ?>
           </select>
           <?php endif; ?>
       </td>

   </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Importance Rank</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['ImportanceRank']; ?>
<?php else: ?>  <input  type="text" name="ImportanceRank" class="form-control" value="<?php echo $this->_tpl_vars['arr']['ImportanceRank']; ?>
"> <?php endif; ?></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Join Date</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['JoinDate']; ?>
<?php else: ?> <input  type="text" name="JoinDate" class="form-control" value="<?php echo $this->_tpl_vars['arr']['JoinDate']; ?>
" id="datetimepicker"> <?php endif; ?></td>
      </tr>
      
      
      
      
      <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Control</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red">*</font>IsActive</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?>
         <?php echo $this->_tpl_vars['arr']['IsActive']; ?>

         <?php else: ?>
         <select name="IsActive" id="IsActive" class="form-control"  >
                <option value="value" >To Choose</option>
                
              	<option value="YES" <?php if ($this->_tpl_vars['arr']['IsActive'] == YES): ?>selected<?php endif; ?>>YES</option>
              	
                <option value="NO" <?php if ($this->_tpl_vars['arr']['IsActive'] == NO): ?>selected<?php endif; ?>>NO</option>
         </select>
         <?php endif; ?>
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE"><font color="red">*</font>IsInHouse</td>
         <td>   <?php if ($this->_tpl_vars['action'] == view): ?> 
      <?php echo $this->_tpl_vars['arr']['IsInHouse']; ?>

      <?php else: ?>
         <select name="IsInHouse" id="IsInHouse" class="form-control" >
                <option value="value">To Choose</option>
              	<option value="NO" <?php if ($this->_tpl_vars['arr']['IsInHouse'] == NO): ?>selected<?php endif; ?>>NetWork</option>
                <option value="YES" <?php if ($this->_tpl_vars['arr']['IsInHouse'] == YES): ?>selected<?php endif; ?>>InHouse</option>
         </select>
         <?php endif; ?>
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE"><font color="red">*</font>Login Url</td>
         <td> <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['LoginUrl']; ?>
<?php else: ?><input type="text" name="LoginUrl" id="LoginUrl" class="form-control" value="<?php echo $this->_tpl_vars['arr']['LoginUrl']; ?>
"><?php endif; ?></td>
      
      </tr>
      <?php if ($this->_tpl_vars['action'] == view): ?>
      <tr>
         <td style="text-align:right;background-color:#EEE">Level</td>
         <td><xmp><?php echo $this->_tpl_vars['arr']['Level']; ?>
</xmp></td>
      </tr>
      <?php endif; ?>
       <tr>
         <td style="text-align:right;background-color:#EEE">Program Url Template</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><xmp><?php echo $this->_tpl_vars['arr']['ProgramUrlTemplate']; ?>
</xmp><?php else: ?><input type="text" name="ProgramUrlTemplate" class="form-control" value="<?php echo $this->_tpl_vars['arr']['ProgramUrlTemplate']; ?>
"><?php endif; ?></td>
      
      </tr>
      
      
      
      
      
      
      
      
            <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Additional</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red">*</font>Domain</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?><div class="form-inline"><?php echo $this->_tpl_vars['arr']['Domain']; ?>
<input id="domain" style="margin-right:15px ;float:right" type="button" value="GO" class="form-control btn-primary" /></div><?php else: ?><div class="form-inline"><input type="text" name="Domain" id="Domain" style="width:500px" class="form-control" value="<?php echo $this->_tpl_vars['arr']['Domain']; ?>
" placeholder="Start with http:// OR https:// " ><input id="domain" style="margin-left:15px" type="button" value="GO" class="form-control btn-primary" /></div><?php endif; ?>
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Blog</td>
         <td> <?php if ($this->_tpl_vars['action'] == view): ?><div class="form-inline"><?php echo $this->_tpl_vars['arr']['BlogUrl']; ?>
<input id="blog" style="margin-right:15px ;float:right" type="button" value="GO" class="form-control btn-primary" /></div><?php else: ?><div class="form-inline"><input type="text" name="BlogUrl" style="width:500px" class="form-control" placeholder="Start with http://" value="<?php echo $this->_tpl_vars['arr']['BlogUrl']; ?>
"  ><input id="blog" style="margin-left:15px" type="button" value="GO" class="form-control btn-primary" /></div><?php endif; ?>
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Facebook</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><div class="form-inline"><?php echo $this->_tpl_vars['arr']['FacebookUrl']; ?>
<input id="facebook" style="margin-right:15px ;float:right" type="button" value="GO" class="form-control btn-primary" /></div><?php else: ?><div class="form-inline"><input type="text" name="FacebookUrl" style="width:500px" class="form-control" placeholder="Start with http://" value="<?php echo $this->_tpl_vars['arr']['FacebookUrl']; ?>
" ><input id="facebook" style="margin-left:15px" type="button" value="GO" class="form-control btn-primary" /></div><?php endif; ?></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Twitter</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><div class="form-inline"><?php echo $this->_tpl_vars['arr']['TwitterUrl']; ?>
<input id="twitter" style="margin-right:15px ;float:right" type="button" value="GO" class="form-control btn-primary" /></div><?php else: ?><div class="form-inline"><input type="text" name="TwitterUrl" style="width:500px" class="form-control" placeholder="Start with http://" value="<?php echo $this->_tpl_vars['arr']['TwitterUrl']; ?>
" ><input id="twitter" style="margin-left:15px" type="button" value="GO" class="form-control btn-primary" /></div><?php endif; ?></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Country</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['Country']; ?>
<?php else: ?>
         
         
         
         
         
         
         
         
         
         
         
         
         
         
         <table border="0" style="width:100%;">
			<tbody><tr>
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="AR"  <?php if (((is_array($_tmp='AR')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>   >&nbsp;<label>Argentina(AR)</label></td>
														<td><input type="checkbox" name="Country[]" value="AU" <?php if (((is_array($_tmp='AU')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Australia(AU)</label></td>
														<td><input type="checkbox" name="Country[]" value="AT" <?php if (((is_array($_tmp='AT')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Austria</label></td>
														<td><input type="checkbox" name="Country[]" value="BE" <?php if (((is_array($_tmp='BE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Belgium(BE)</label></td>
														
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="CA" <?php if (((is_array($_tmp='CA')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Canada(CA)</label></td>
														<td><input type="checkbox" name="Country[]" value="CZ" <?php if (((is_array($_tmp='CZ')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Czech Republic(CZ)</label></td>
														<td><input type="checkbox" name="Country[]" value="CY" <?php if (((is_array($_tmp='CY')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Cyprus(CY)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="CN" <?php if (((is_array($_tmp='CN')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>China(CN)</label></td>
														</tr><tr>
														
														
														<td><input type="checkbox" name="Country[]" value="CR" <?php if (((is_array($_tmp='CR')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Costa Rica(CR)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="DK" <?php if (((is_array($_tmp='DK')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Denmark(DK)</label></td>
														<td><input type="checkbox" name="Country[]" value="EU" <?php if (((is_array($_tmp='EU')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>European Union(EU)</label></td>
														<td><input type="checkbox" name="Country[]" value="SV" <?php if (((is_array($_tmp='SV')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>El Salvador(SV)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="EE" <?php if (((is_array($_tmp='EE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Estonia(EE)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="FR" <?php if (((is_array($_tmp='FR')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>France(FR)</label></td>
														<td><input type="checkbox" name="Country[]" value="FI" <?php if (((is_array($_tmp='FI')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Finland(FI)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="DE" <?php if (((is_array($_tmp='DE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>German(DE)</label></td>
														
							
														</tr>
														
														<tr>
														<td><input type="checkbox" name="Country[]" value="GI" <?php if (((is_array($_tmp='GI')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Gibraltar(GI)</label></td>
														<td><input type="checkbox" name="Country[]" value="GP" <?php if (((is_array($_tmp='GP')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Guadeloupe(GP)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="GLOBAL" <?php if (((is_array($_tmp='GLOBAL')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>GLOBAL(GLOBAL)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="GR" <?php if (((is_array($_tmp='GR')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Greece(GR)</label></td>
														</tr>
														
														
														<tr>
										
														<td><input type="checkbox" name="Country[]" value="HK" <?php if (((is_array($_tmp='HK')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Hong Kong(HK)</label></td>
														<td><input type="checkbox" name="Country[]" value="IL" <?php if (((is_array($_tmp='IL')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Israel(IL)</label></td>
														<td><input type="checkbox" name="Country[]" value="IN" <?php if (((is_array($_tmp='IN')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>India(IN)</label></td>
														<td><input type="checkbox" name="Country[]" value="ID" <?php if (((is_array($_tmp='ID')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Indonesia(ID)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="IE" <?php if (((is_array($_tmp='IE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Ireland(IE)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="IT" <?php if (((is_array($_tmp='IT')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Italy(IT)</label></td>
														<td><input type="checkbox" name="Country[]" value="JP" <?php if (((is_array($_tmp='JP')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Japan(JP)</label></td>
														<td><input type="checkbox" name="Country[]" value="LV" <?php if (((is_array($_tmp='LV')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Latvia(LV)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="LU" <?php if (((is_array($_tmp='LU')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Luxembourg(LU)</label></td>
										
														<td><input type="checkbox" name="Country[]" value="MX" <?php if (((is_array($_tmp='MX')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Mexico(MX)</label></td>
														<td><input type="checkbox" name="Country[]" value="MY" <?php if (((is_array($_tmp='MY')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Malaysia(MY)</label></td>
														<td><input type="checkbox" name="Country[]" value="MA" <?php if (((is_array($_tmp='MA')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Morocco(MA)</label></td>
														
														
														</tr>
														<tr>
														<td><input type="checkbox" name="Country[]" value="NL" <?php if (((is_array($_tmp='NL')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?> >&nbsp;<label>Netherlands(NL)</label></td>
														<td><input type="checkbox" name="Country[]" value="NO" <?php if (((is_array($_tmp='NO')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Norway(NO)</label></td>
														<td><input type="checkbox" name="Country[]" value="NZ" <?php if (((is_array($_tmp='NZ')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>New Zealand(NZ)</label></td>
														<td><input type="checkbox" name="Country[]" value="PH" <?php if (((is_array($_tmp='PH')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Philippines(PH)</label></td>
														
														</tr>
														<tr>
										
														<td><input type="checkbox" name="Country[]" value="PL" <?php if (((is_array($_tmp='PL')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Poland(PL)</label></td>
														<td><input type="checkbox" name="Country[]" value="PT" <?php if (((is_array($_tmp='PT')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Portugal(PT)</label></td>
														<td><input type="checkbox" name="Country[]" value="QA" <?php if (((is_array($_tmp='QA')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Qatar(QA)</label></td>
														<td><input type="checkbox" name="Country[]" value="RO" <?php if (((is_array($_tmp='RO')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Romania(RO)</label></td>
														
														</tr><tr>
										
														<td><input type="checkbox" name="Country[]" value="ZA" <?php if (((is_array($_tmp='ZA')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>South Africa(ZA)</label></td>
														<td><input type="checkbox" name="Country[]" value="SE" <?php if (((is_array($_tmp='SE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Sweden(SE)</label></td>
														<td><input type="checkbox" name="Country[]" value="SG" <?php if (((is_array($_tmp='SG')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Singapore(SG)</label></td>
														<td><input type="checkbox" name="Country[]" value="ES" <?php if (((is_array($_tmp='ES')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Spain(ES)</label></td>
														</tr><tr>
														<td><input type="checkbox" name="Country[]" value="CH" <?php if (((is_array($_tmp='CH')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Switzerland(CH)</label></td>
														
														<td><input type="checkbox" name="Country[]" value="TW" <?php if (((is_array($_tmp='TW')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Taiwan(TW)</label></td>
										
														<td><input type="checkbox" name="Country[]" value="TH" <?php if (((is_array($_tmp='TH')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Thailand(TH)</label></td>
														<td><input type="checkbox" name="Country[]" value="AE" <?php if (((is_array($_tmp='AE')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>United Arab Emirates(AE)</label></td>
														
														</tr>
														
							<tr>
										<td><input type="checkbox" name="Country[]" value="UK" <?php if (((is_array($_tmp='UK')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>United Kingdom(UK)</label></td>
										<td><input type="checkbox" name="Country[]" value="US" <?php if (((is_array($_tmp='US')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>United States(US)</label></td>
										<td><input type="checkbox" name="Country[]" value="VG" <?php if (((is_array($_tmp='VG')) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['countryArr']) : in_array($_tmp, $this->_tpl_vars['countryArr']))): ?>checked<?php endif; ?>>&nbsp;<label>Virgin Island, British(VG)</label></td>
										
							</tr>
							<tr>
							
							
							</tr>
							<tr>
							
							
							</tr>
			</tbody></table>
         
         
         
         
         
         
         
         
         
         
         
         
         
         <?php endif; ?></td>
      
      </tr>
      
      
      
      
      
      
      
      
                  <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Aff Url Control</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red">*</font>AffiliateUrl Keyword List 1</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?><xmp><?php echo $this->_tpl_vars['arr']['AffiliateUrlKeywords']; ?>
</xmp><?php else: ?><textarea class="form-control" name="AffiliateUrlKeywords" id="AffiliateUrlKeywords" rows="5"   ><?php echo $this->_tpl_vars['arr']['AffiliateUrlKeywords']; ?>
</textarea><?php endif; ?>
         </td>
         
      </tr>
        <tr>
         <td width="20%" style="text-align:right;background-color:#EEE"><font color="red">*</font>AffiliateUrl Keyword List 2</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?><xmp><?php echo $this->_tpl_vars['arr']['AffiliateUrlKeywords2']; ?>
</xmp><?php else: ?><textarea class="form-control" name="AffiliateUrlKeywords2" id="AffiliateUrlKeywords2" rows="5"  ><?php echo $this->_tpl_vars['arr']['AffiliateUrlKeywords2']; ?>
</textarea><?php endif; ?> 
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Support Deep Url</td>
         <td> <?php if ($this->_tpl_vars['action'] == view): ?>
         <?php echo $this->_tpl_vars['arr']['SupportDeepUrl']; ?>

         <?php else: ?>
          <select name="SupportDeepUrl" class="form-control" > 
                <option value="value">To Choose</option>
              	<option value="YES" <?php if ($this->_tpl_vars['arr']['SupportDeepUrl'] == YES): ?>selected<?php endif; ?>>YES</option>
                <option value="NO" <?php if ($this->_tpl_vars['arr']['SupportDeepUrl'] == NO): ?>selected<?php endif; ?>>NO</option>
         </select>
         <?php endif; ?>
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Deep Url Paraname</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['DeepUrlParaName']; ?>
<?php else: ?><input type="text" name="DeepUrlParaName" class="form-control" value="<?php echo $this->_tpl_vars['arr']['DeepUrlParaName']; ?>
"/><?php endif; ?></td>
      
      </tr>
        <tr>
         <td style="text-align:right;background-color:#EEE">Support Sub Tracking</td>
         <td> 
         <?php if ($this->_tpl_vars['action'] == view): ?>
         <?php echo $this->_tpl_vars['arr']['SupportSubTracking']; ?>

         <?php else: ?>
          <select name="SupportSubTracking"  class="form-control"  >
                <option value="value">To Choose</option>
              	<option value="YES" <?php if ($this->_tpl_vars['arr']['SupportSubTracking'] == YES): ?>selected<?php endif; ?>>YES</option>
                <option value="NO" <?php if ($this->_tpl_vars['arr']['SupportSubTracking'] == NO): ?>selected<?php endif; ?>>NO</option>
         </select>
         <?php endif; ?>
         </td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Sub Tracking Setting 1</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['SubTracking']; ?>
<?php else: ?><input type="text" name="SubTracking" class="form-control" value="<?php echo $this->_tpl_vars['arr']['SubTracking']; ?>
"><?php endif; ?></td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Sub Tracking Setting 2</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['SubTracking2']; ?>
<?php else: ?><input type="text" name="SubTracking2" class="form-control" value="<?php echo $this->_tpl_vars['arr']['SubTracking2']; ?>
" ><?php endif; ?></td>
      
      </tr>
      
      
      
                  <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Revenue Control</font></td>
         
         
      </tr>
            <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Revenue Account</td>
         <td>
      <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['RevenueAccount']; ?>
<?php else: ?>  
<select name="RevenueAccount" class="form-control"  >
<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['fin_rev_acc_list'],'selected' => $this->_tpl_vars['arr']['RevenueAccount']), $this);?>

    
         </select>
           <?php endif; ?>     
         </td>
         </tr>
         <tr>


         </tr>
            <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Revenue Received</td>
         <td>
            <select name="RevenueReceived"  class="form-control">
                <option value="0">NO</option>
                <option value="1" <?php if ($this->_tpl_vars['arr']['RevenueReceived']): ?>selected<?php endif; ?>>YES</option>
            </select>  
         </td>
         </tr>
         <tr>


                           <td width="20%" style="text-align:right;background-color:#EEE">Revenue Cycle</td>
         
                  <td> <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['RevenueCycle']; ?>
<?php else: ?><textarea name="RevenueCycle" class="form-control" rows="5" ><?php echo $this->_tpl_vars['arr']['RevenueCycle']; ?>
</textarea>
         <?php endif; ?>
         </td>
      </tr>
        <tr>
                           <td width="20%" style="text-align:right;background-color:#EEE">Revenue Remark</td>
         
                  <td> <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['RevenueRemark']; ?>
<?php else: ?><textarea name="RevenueRemark" class="form-control" rows="5" ><?php echo $this->_tpl_vars['arr']['RevenueRemark']; ?>
</textarea>
         <?php endif; ?>
         </td>
      </tr>
      
      
      
      
      
      
                <tr>
         <td colspan="2" style="text-align:left;font-weight: bold;background-color:#1A3958"><font color="white">Stats</font></td>
         
         
      </tr>
      <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Program Crawled</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?>
          <?php echo $this->_tpl_vars['arr']['ProgramCrawled']; ?>

         <?php else: ?>
<select name="ProgramCrawled" class="form-control"  >
                <option value="value">To Choose</option>
              	<option value="YES" <?php if ($this->_tpl_vars['arr']['ProgramCrawled'] == YES): ?>selected<?php endif; ?>>YES</option>
                <option value="NO" <?php if ($this->_tpl_vars['arr']['ProgramCrawled'] == NO): ?>selected<?php endif; ?>>NO</option>
                <option value="No Need to Crawl" <?php if ($this->_tpl_vars['arr']['ProgramCrawled'] == 'No Need to Crawl'): ?>selected<?php endif; ?>>NO Need To Crawl</option>
                <option value="Request to Crawl" <?php if ($this->_tpl_vars['arr']['ProgramCrawled'] == 'Request to Crawl'): ?>selected<?php endif; ?>>Request To Crawl</option>
                <option value="Can Not Crawl" <?php if ($this->_tpl_vars['arr']['ProgramCrawled'] == 'Can Not Crawl'): ?>selected<?php endif; ?>>Can Not Crawl</option>
         </select>
         <?php endif; ?>         
         </td>
         
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Program Crawl Remark</td>
         <td>           
         <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['ProgramCrawlRemark']; ?>
<?php else: ?><textarea class="form-control" name="ProgramCrawlRemark" rows="5"><?php echo $this->_tpl_vars['arr']['ProgramCrawlRemark']; ?>
</textarea>
		<?php endif; ?> 
         </td>
      
      </tr>
            <tr>
         <td width="20%" style="text-align:right;background-color:#EEE">Stats Report Crawled</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?>
         <?php echo $this->_tpl_vars['arr']['StatsReportCrawled']; ?>

         <?php else: ?>
<select name="StatsReportCrawled" class="form-control" >
                <option value="value">To Choose</option>
              	<option value="YES" <?php if ($this->_tpl_vars['arr']['StatsReportCrawled'] == YES): ?>selected<?php endif; ?>>YES</option>
                <option value="NO" <?php if ($this->_tpl_vars['arr']['StatsReportCrawled'] == NO): ?>selected<?php endif; ?>>NO</option>
                <option value="No Need to Crawl" <?php if ($this->_tpl_vars['arr']['StatsReportCrawled'] == 'No Need to Crawl'): ?>selected<?php endif; ?>>NO Need To Crawl</option>
                <option value="Request to Crawl" <?php if ($this->_tpl_vars['arr']['StatsReportCrawled'] == 'Request to Crawl'): ?>selected<?php endif; ?>>Request To Crawl</option>
                <option value="Can Not Crawl"  <?php if ($this->_tpl_vars['arr']['StatsReportCrawled'] == 'Can Not Crawl'): ?>selected<?php endif; ?>>Can Not Crawl</option>
         </select>  
         <?php endif; ?>       
         </td>
         
      </tr>
         <tr>
         <td style="text-align:right;background-color:#EEE">Stats Report Crawl Remark</td>
         <td>           
          <?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['StatsReportCrawlRemark']; ?>
<?php else: ?><textarea name="StatsReportCrawlRemark" class="form-control" rows="5" ><?php echo $this->_tpl_vars['arr']['StatsReportCrawlRemark']; ?>
</textarea>
<?php endif; ?> 
         </td>
      
      </tr>
      <tr>
         <td style="text-align:right;background-color:#EEE">Stats Affiliate Name</td>
         <td><?php if ($this->_tpl_vars['action'] == view): ?><?php echo $this->_tpl_vars['arr']['StatsAffiliateName']; ?>
<?php else: ?><input name="StatsAffiliateName" type="text" class="form-control" value="<?php echo $this->_tpl_vars['arr']['StatsAffiliateName']; ?>
"   ><?php endif; ?></td>
      
      </tr>
       <tr>
         <td style="text-align:right;background-color:#EEE">Comment</td>
         <td>
         <?php if ($this->_tpl_vars['action'] == view): ?><xmp><?php echo $this->_tpl_vars['arr']['Comment']; ?>
</xmp><?php else: ?><textarea name="Comment" class="form-control" rows="5"><?php echo $this->_tpl_vars['arr']['Comment']; ?>
</textarea>
 <?php endif; ?>
</td>
      
      </tr>
   </tbody>
</table>

<div style="text-align:center;">
<?php if ($this->_tpl_vars['action'] == 'edit'): ?>
<input class="btn btn-primary"  id="sbt" type="button" value="提交修改"/>
<?php endif; ?>
<input class="btn btn-primary"  type="button" id="return" value="返回列表"/>
</div>

</form>
</div>

<script type="text/javascript">

    $('#change_password').click(function(){
       if($(this).val() == "修改"){
           $('#Account').removeAttr("readonly");
           $('#Password').removeAttr("readonly");
           $('#Account').focus();
           $(this).val("确定");
       } else {
           var Account = $('#Account').val();
           var Password = $('#Password').val();
           var AffId = $('#id').val();
           var yn = confirm("确定修改账号信息?\nAccount:"+Account+"\nPassword:"+Password);
           if(yn){
               $.ajax({
                   type:"post",
                   url:"<?php echo @BASE_URL; ?>
/edit_affiliates.php",
                   data:{"Account":Account,"Password":Password,"AffId":AffId},
                   dataType:"json",
                   success: function(req){
                       if(req.succ){
                           $('#Account').attr("readonly","readonly");
                           $('#Password').attr("readonly","readonly");
                           alert("修改成功!");
                           $('#Account').val(req.Account);
                           $('#Password').val(req.Password);
                           $('#change_password').val("修改");
                       } else {
                           alert(req.error);
                           window.location.href=window.location.href;
                       }
                   }
               });
           } else{
               window.location.href=window.location.href;
           }
        }
    });


$("#return").click(function(){
	window.location.href="b_aff_aff.php";
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




//点击Go按钮，跳转到指定页面
$("#domain").click(function(){
	window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent("<?php echo $this->_tpl_vars['arr']['Domain']; ?>
")); 
});
$("#facebook").click(function(){
	window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent("<?php echo $this->_tpl_vars['arr']['FacebookUrl']; ?>
")); 
});
$("#twitter").click(function(){
	window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent("<?php echo $this->_tpl_vars['arr']['TwitterUrl']; ?>
")); 
});
$("#blog").click(function(){
	window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent("<?php echo $this->_tpl_vars['arr']['BlogUrl']; ?>
")); 
});

$("#sbt").click(function(){
    var YN = true;
    if($("#IsActive").val() == 'YES'){
        var notNull = ["Name","Domain","LoginUrl","AffiliateUrlKeywords","AffiliateUrlKeywords2"];
    } else {
        var notNull = ["Name","Domain"];
    }
    $.each(notNull,function(name,value){
       if($('#'+value).val() == ''){
           YN = false;
           var alt = 'Missing ' + value +',Check Please!';
           alert(alt);
           $('#'+value).focus();
           return false;
       }
    });
    if(YN == true){
        $("form:first").submit();
    }
});

</script> 

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>