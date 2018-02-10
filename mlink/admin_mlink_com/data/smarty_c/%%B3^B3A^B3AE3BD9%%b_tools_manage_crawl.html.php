<?php /* Smarty version 2.6.26, created on 2017-12-03 23:49:54
         compiled from b_tools_manage_crawl.html */ ?>
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

<div>
    <div class="container-fluid" style="margin-top:30px;">
        <div style="text-align:center;margin-bottom:30px;">
            <h1>Manager - Crawl</h1>
        </select>
        </div>
        <div class="row" style="padding:20px 0;">

            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">在线运行数：<?php echo $this->_tpl_vars['scriptSum']; ?>

                    </div>
                    <div class="panel-body">
                        <table class="table table-striped" id="table_aff">
                            <thead>
                            <tr>
                                <th>AffName</th>
                                <th>ProgramCrawlStatus<br/>状态<br/>当前脚本状态</th>
                                <th>LinkCrawlStatus<br/>状态<br/>当前脚本状态</th>
                                <th>FeedCrawlStatus<br/>状态<br/>当前脚本状态</th>
                                <th style="float: right">Operation</th>
                            </tr>
                            </thead>
                            <?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['data']):
?>
                            <tr>
                                <td><?php echo $this->_tpl_vars['data']['Name']; ?>
</td>
                                <td>
                                    <?php if ($this->_tpl_vars['data']['ProgramCrawlStatus'] == 'Yes'): ?>
                                                                            开启<br/>
                                      <?php if ($this->_tpl_vars['data']['scriptGetprogram'] > 0): ?>【<span style="color:red">脚本运行中...</span>】<?php else: ?>【脚本已结束】<?php endif; ?>
                                    <?php else: ?>
                                      <span style="color:#FF6347">未开启</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($this->_tpl_vars['data']['LinkCrawlStatus'] == 'Yes'): ?>
                                                                            开启<br/>
                                      <?php if ($this->_tpl_vars['data']['scriptGetlinks'] > 0): ?>【<span style="color:red">脚本运行中...</span>>】<?php else: ?>【脚本已结束】<?php endif; ?>                                       
                                    <?php else: ?>
                                        <span style="color:#FF6347">未开启</span>                                      
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($this->_tpl_vars['data']['FeedCrawlStatus'] == 'Yes'): ?>
                                                                            开启<br/>
                                      <?php if ($this->_tpl_vars['data']['scriptGetfeed'] > 0): ?>【<span style="color:red">脚本运行中...</span>】<?php else: ?>【脚本已结束】<?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#FF6347">未开启</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!--<?php if ($this->_tpl_vars['data']['ProgramCrawlStatus'] == 'Yes'): ?>
                                        <?php if ($this->_tpl_vars['data']['scriptGetprogram'] > 0): ?>
                                            <span style="float:right" class="null_style_by_js" data-type="2" data-crawl="getprogram" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">EndProgramScript</a></span><br/>                                        
                                        <?php else: ?>
                                            <span style="float:right" class="null_style_by_js" data-type="1" data-crawl="getprogram" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">StartProgramScript</a></span><br/>                                         
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($this->_tpl_vars['data']['LinkCrawlStatus'] == 'Yes'): ?>
                                        <?php if ($this->_tpl_vars['data']['scriptGetlinks'] > 0): ?>
                                            <span style="float:right" class="null_style_by_js" data-type="2" data-crawl="getallpagelinks" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">EndLinksScript</a></span><br/>                                      
                                        <?php else: ?>
                                            <span style="float:right" class="null_style_by_js" data-type="1" data-crawl="getallpagelinks" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">StartLinksScript</a></span><br/>                                        
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($this->_tpl_vars['data']['FeedCrawlStatus'] == 'Yes'): ?>
                                        <?php if ($this->_tpl_vars['data']['scriptGetfeed'] > 0): ?>
                                            <span style="float:right" class="null_style_by_js" data-type="2" data-crawl="getallfeeds" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">EndFeedsScript</a></span><br/>                                     
                                        <?php else: ?>
                                            <span style="float:right" class="null_style_by_js" data-type="1" data-crawl="getallfeeds" data-affid="<?php echo $this->_tpl_vars['key']; ?>
"><a href="javascript:void(0);" target="_blank">StartFeedsScript</a></span><br/>                                          
                                        <?php endif; ?>
                                    <?php endif; ?>-->
                                </td>
                            </tr>
                            <?php endforeach; endif; unset($_from); ?>


                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>

<script type="text/javascript">    

$(".null_style_by_js").click(function(){
	
	var type  = $(this).data('type');
	var crawl = $(this).data('crawl');
	var affid = $(this).data('affid');
	console.log(type+'=='+crawl+'=='+affid);
	
    $.ajax({
	         type:"post",
	         dataType:"json",
	         url:"<?php echo @BASE_URL; ?>
/b_tools_manage_crawl.php",
	         data:'affid='+affid+'&crawl='+crawl+'&type='+type,
	         success: function(req){
	           
	        	  console.log(req);
	        	 
	         }
	      });
})    
</script>	      
 
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>