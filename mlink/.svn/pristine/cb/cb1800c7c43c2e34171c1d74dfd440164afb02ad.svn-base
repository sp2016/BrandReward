<?php /* Smarty version 2.6.26, created on 2015-09-24 22:12:07
         compiled from program_links_list.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'program_links_list.tpl', 59, false),array('modifier', 'escape', 'program_links_list.tpl', 66, false),)), $this); ?>
<html>
<head>
<title>Program Links List</title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<script type="text/javascript" src="/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/program.js"></script>
<script language="JavaScript" src="/js/jquery.autocomplete.js"></script>
<script language="JavaScript" src="/js/program_search.js"></script>
<link type="text/css" rel="stylesheet" href="/css/jquery.autocomplete.css" />
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
</style>
'; ?>

</head>
<body>
<table width="100%" cellspacing="1" cellpadding="2" style="word-break:break-all" bgcolor="#BFE0F7">
	<tr bgcolor="#FFFFFF">
		<td align="center">
			<h1>Program <?php if ($this->_tpl_vars['type'] == 'product'): ?>Products<?php else: ?>Links<?php endif; ?> List</h1>			
		</td>
	</tr>
	<tr>
		<form name="form1" action="" method="get">
		<td>
			<b>Filter</b>			
			Affiliate: <input type="text" id="affiliatename" name="affiliatename" value="<?php echo $this->_tpl_vars['affiliatename']; ?>
" size="30" />&nbsp;<input type="button" value="reset" onclick="resetAff()" />
			<input type="hidden" name="affiliatetype" id="affiliatetype" value="<?php echo $this->_tpl_vars['affid']; ?>
" />&nbsp;&nbsp;
			
			Program: <input type="text" id="program_search" name="name" value="<?php echo $this->_tpl_vars['name']; ?>
" size="30" />&nbsp;&nbsp;
			
			<input type="hidden" name="type" value="<?php echo $this->_tpl_vars['type']; ?>
" />
			<input type="submit" value="Query">
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
					<th width="160px">Program</th>
					<th width="160px">Image</th>
					<th width="200px">Name<hr width="80%" />Desc</th>
					<th width="800px">HtmlCode</th>
					<th width="120px">AddTime<hr width="80%" />EndTime</th>					
				</tr>
				<?php $_from = $this->_tpl_vars['links_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
				<tr bgcolor="<?php echo smarty_function_cycle(array('values' => "#FFFFFF,#EEEEEE"), $this);?>
" onmouseover="addColor(this);" onmouseout="removeColor(this);">
					<td align="center" width="160px">
						<a href="/front/program_edit.php?ID=<?php echo $this->_tpl_vars['prgm_arr'][$this->_tpl_vars['info']['AffMerchantId']]['id']; ?>
" target="_blank"><?php echo $this->_tpl_vars['prgm_arr'][$this->_tpl_vars['info']['AffMerchantId']]['name']; ?>
(<?php echo $this->_tpl_vars['prgm_arr'][$this->_tpl_vars['info']['AffMerchantId']]['idinaff']; ?>
)</a>
					</td>
					<td align="center" width="160px"><?php if ($this->_tpl_vars['info']['LinkImageUrl']): ?><img src="<?php echo $this->_tpl_vars['info']['LinkImageUrl']; ?>
" border=0 width=150 height=150 /><?php endif; ?></td>
					<td width="200px"><?php echo $this->_tpl_vars['info']['LinkName']; ?>
<hr><?php echo $this->_tpl_vars['info']['LinkDesc']; ?>
</td>
					<td width="800px">
						<textarea style="width:100%" rows=3><?php echo ((is_array($_tmp=$this->_tpl_vars['info']['LinkHtmlCode'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
						<?php echo $this->_tpl_vars['info']['LinkHtmlCode']; ?>

						<?php if ($this->_tpl_vars['info']['LinkAffUrl']): ?>
							<br />Affiliate Url: <?php echo $this->_tpl_vars['info']['LinkAffUrl']; ?>

						<?php endif; ?>
											</td>					
					<td><?php echo $this->_tpl_vars['info']['AddTime']; ?>
<hr width="80%" /><?php echo $this->_tpl_vars['info']['LinkEndDate']; ?>
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