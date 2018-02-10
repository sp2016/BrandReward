<?php /* Smarty version 2.6.26, created on 2016-01-13 23:57:32
         compiled from domain_noaff_config.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'domain_noaff_config.tpl', 279, false),array('function', 'html_options', 'domain_noaff_config.tpl', 311, false),)), $this); ?>
<html>
<head>
<title>Domain NoAff Redirect Config</title>
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
hr{margin:3px auto;height:1px;background-color:#999999;border:none;}
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
            
            <li>
              <a href="http://bdg.mgsvc.com/admin/old_tools/front/domain_noaff_config.php" target="_blank">Domain NoAff Redirect Config</a>
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
<h1 style="text-align:center">Domain NoAff Redirect Config</h1>
<div style="margin-top:30px;">
<table class="table table-bordered">
	<tr>
		<form name="form1" action="" method="get">
		<td>
						<input type="button" value="add" onclick="showBlock('add_rule');" />
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
					<th width="30%" align="center">Domain</th>
					<th width="10%" align="center">Type</th>
					<th width="10%" align="center">Status</th>	
					<th width="20%" align="center">Last Update Time</th>
					<th width="30%" align="center">Operation</th>					
				</tr>
				<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
				<tr id="tr<?php echo $this->_tpl_vars['info']['id']; ?>
" bgcolor="<?php echo smarty_function_cycle(array('values' => "#FFFFFF,#EEEEEE"), $this);?>
" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td>
						<?php echo $this->_tpl_vars['info']['domain']; ?>
 - <?php echo $this->_tpl_vars['info']['domainid']; ?>

					</td>
					<td>						
						<?php echo $this->_tpl_vars['info']['redirecttype']; ?>

					</td>
					<td>
						<?php echo $this->_tpl_vars['info']['status']; ?>

					</td>
					<td>
						<?php echo $this->_tpl_vars['info']['lastupdatetime']; ?>

					</td>					
					<td align="left" width="60px;">
						<a href="javascript:done('<?php echo $this->_tpl_vars['info']['id']; ?>
')">[done]</a> ||| 
						<a href="javascript:ignored('<?php echo $this->_tpl_vars['info']['id']; ?>
')">[ignored]</a>						
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
<div id="add_rule">
	<table>
		<input type="text" id="domain_search" name="config_domain" />
		<input type="hidden" id="domain_search" name="config_domain" />
		<select name="config_type">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['typeArr']), $this);?>

		</select>
		<input type="button" onclick="checkAddRule();" />
	</table>
</div>
</body>
</html>