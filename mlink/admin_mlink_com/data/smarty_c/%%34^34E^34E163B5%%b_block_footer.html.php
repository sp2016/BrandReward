<?php /* Smarty version 2.6.26, created on 2017-11-19 19:04:50
         compiled from b_block_footer.html */ ?>
	<div class="footer">
	  <div class="container">
	    <ul class="list-inline">
	      <li><a href="#">ADVERTISERS</a></li>
	      <li>|</li>
	      <li><a href="#">SUPPORT</a></li>
	      <li>|</li>
	      <li><a href="#">DEVELOPER CENTER</a></li>
	      <li>|</li>
	      <li><a href="#">REFERRAL PROGRAM</a></li>
	      <li>|</li>
	      <li><a href="#">PRIVACY & POLICIES</a></li>
	      <li>|</li>
	      <li><a href="#">CAREERS</a></li>
	    </ul>
	  </div>
	</div>
</script>
<?php $_from = $this->_tpl_vars['sys_footer']['js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['js']):
?>
      <script src="<?php echo $this->_tpl_vars['js']; ?>
">
<?php endforeach; endif; unset($_from); ?>
</script>
</body>
</html>