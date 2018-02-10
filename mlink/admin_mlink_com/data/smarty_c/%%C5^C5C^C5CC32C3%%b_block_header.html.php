<?php /* Smarty version 2.6.26, created on 2017-11-19 19:04:50
         compiled from b_block_header.html */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- <link rel="icon" href="../../favicon.ico"> -->
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

  <title><?php if ($this->_tpl_vars['title']): ?><?php echo $this->_tpl_vars['title']; ?>
<?php else: ?>BrandReward<?php endif; ?></title>

  <!-- Bootstrap core CSS -->
  <?php $_from = $this->_tpl_vars['sys_header']['css']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['css']):
?>
    <link href="<?php echo $this->_tpl_vars['css']; ?>
" rel="stylesheet">
  <?php endforeach; endif; unset($_from); ?>
  
  <?php $_from = $this->_tpl_vars['sys_header']['js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['js']):
?>
      <script src="<?php echo $this->_tpl_vars['js']; ?>
"></script>
  <?php endforeach; endif; unset($_from); ?>

</head>
<body>