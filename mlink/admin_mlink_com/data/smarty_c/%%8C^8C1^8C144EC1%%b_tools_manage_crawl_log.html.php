<?php /* Smarty version 2.6.26, created on 2017-12-04 19:39:46
         compiled from b_tools_manage_crawl_log.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'ucfirst', 'b_tools_manage_crawl_log.html', 111, false),)), $this); ?>
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
            <h1>Crawl - Log</h1>
        </select>
        </div>
        <div class="row" style="padding:20px 0;">

            <div class="col-lg-12">
                
                <div class="panel panel-default">
                    <div class="panel-heading"><a href="b_tools_manage_crawl.php" target="_blank">当前脚本状态</a></div>
                    <div class="panel-body">
                        <form id="form_content_search">
                            <div class="row">
                                <div class=" form-inline">
                                    <div class="col-lg-12 ">
                                        <div class="form-group dpm" style="position:relative;">
                                           <input type="text" name="date" class="form-control datepicker" placeholder="Run Time" value="<?php echo $this->_tpl_vars['search']['date']; ?>
"> 
                                        </div>
                                        <div class="form-group">
                                            &nbsp;AffName
                                            <select name="affid" class="form-control" style="width:120px">
                                                <option value="">All</option>
                                                <?php $_from = $this->_tpl_vars['affiList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['lk'] => $this->_tpl_vars['lc']):
?>
                                                <option value="<?php echo $this->_tpl_vars['lk']; ?>
" <?php if (isset ( $this->_tpl_vars['search']['affid'] ) && $this->_tpl_vars['search']['affid'] == $this->_tpl_vars['lk']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['lc']['Name']; ?>
</option>
                                                <?php endforeach; endif; unset($_from); ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            &nbsp;CrawlType
                                            <select name="method" class="form-control" style="width:120px">
                                                <option value="">All</option>
                                                <option value="getprogram" <?php if (isset ( $this->_tpl_vars['search']['method'] ) && $this->_tpl_vars['search']['method'] == 'getprogram'): ?> selected="selected"<?php endif; ?>>getprogram</option>
                                                <option value="getallpagelinks" <?php if (isset ( $this->_tpl_vars['search']['method'] ) && $this->_tpl_vars['search']['method'] == 'getallpagelinks'): ?> selected="selected"<?php endif; ?>>getallpagelinks</option>
                                                <option value="getallfeeds" <?php if (isset ( $this->_tpl_vars['search']['method'] ) && $this->_tpl_vars['search']['method'] == 'getallfeeds'): ?> selected="selected"<?php endif; ?>>getallfeeds</option>
                                                <option value="transactionCrawl" <?php if (isset ( $this->_tpl_vars['search']['method'] ) && $this->_tpl_vars['search']['method'] == 'transactionCrawl'): ?> selected="selected"<?php endif; ?>>transactionCrawl</option> 
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            &nbsp;Status
                                            <select name="status" class="form-control" style="width:120px">
                                                <option value="">All</option>
                                                <option value="finish" <?php if (isset ( $this->_tpl_vars['search']['status'] ) && $this->_tpl_vars['search']['status'] == 'finish'): ?> selected="selected"<?php endif; ?>>Finish</option>
                                                <option value="doing" <?php if (isset ( $this->_tpl_vars['search']['status'] ) && $this->_tpl_vars['search']['status'] == 'doing'): ?> selected="selected"<?php endif; ?>>Doing</option>
                                                <option value="error" <?php if (isset ( $this->_tpl_vars['search']['status'] ) && $this->_tpl_vars['search']['status'] == 'error'): ?> selected="selected"<?php endif; ?>>Error</option>
                                            </select>
                                        </div>
                                       <!--   <div class="from_group">
                                        	&nbsp;MK/BR
                                        	<select name="platform" class="form-control" style="width:120px">
                                        		<option value="">All</option>
                                        		<option value="MK" <?php if (isset ( $this->_tpl_vars['search']['platform'] ) && $this->_tpl_vars['search']['platform'] == MK): ?> selected="selected"<?php endif; ?>>MK</option>
                                        		<option value="BR" <?php if (isset ( $this->_tpl_vars['search']['platform'] ) && $this->_tpl_vars['search']['platform'] == BR): ?> selected="selected"<?php endif; ?>>BR</option>
                                        	</select>
                                        </div>-->
                                        
                                        <div class="form-group" style="margin-left: 10px;">
                                            <input type="submit" class="btn b-primary" value="Search">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-striped" id="table_aff">
                            <thead>
                            <tr>
                                <th>AffName</th>
                                <th>Platform</th>
                                <th>Type</th>
                                <th>Starttime</th>
                                <th>Endtime</th>
                                <th>Logfile</th>
                                <th>Status</th>
                                <?php if ($this->_tpl_vars['search']['method'] == 'getprogram'): ?>
                                <th>New</th>
                                <th>NotFound</th>
                                <th>PartnershipOn</th>
                                <th>PartnershipOff</th>
                                <th>StoreOff</th>
                                <?php elseif ($this->_tpl_vars['search']['method'] == 'transactionCrawl'): ?>
                                <th>Unknown</th>
                                <th></th>
                                <th></th>
                                <?php else: ?>
                                <th>Total</th>
                                <th>New</th>
                                <th>ToInactive</th>
                                <?php endif; ?>
                                
                                
                            </tr>
                            </thead>
                            <?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?>
                            <tr>
                                <td><?php echo $this->_tpl_vars['affiList'][$this->_tpl_vars['value']['affid']]['Name']; ?>
</td>
                                <td><?php echo $this->_tpl_vars['value']['platform']; ?>
</td>
                                <td><?php echo $this->_tpl_vars['value']['method']; ?>
</td>
                                <td><?php echo $this->_tpl_vars['value']['startTime']; ?>
</td>
                                <td><?php echo $this->_tpl_vars['value']['endTime']; ?>
</td>
                                <td><?php echo $this->_tpl_vars['value']['logfile']; ?>
</td>
                                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['value']['status'])) ? $this->_run_mod_handler('ucfirst', true, $_tmp) : ucfirst($_tmp)); ?>
</td>
                                <?php if ($this->_tpl_vars['value']['analyze_flag'] == '0'): ?>
                                <td></td>
                                <td></td>
                                <td></td>
                                <?php else: ?>
                                 <?php if ($this->_tpl_vars['value']['method'] == 'getprogram'): ?>
                                   <td><a href="javascript:void(0);" onclick="cate_operate(1,<?php echo $this->_tpl_vars['value']['id']; ?>
)"><?php echo $this->_tpl_vars['value']['new']; ?>
</a></td>
                                   <td><a href="javascript:void(0);" onclick="cate_operate(3,<?php echo $this->_tpl_vars['value']['id']; ?>
)"><?php echo $this->_tpl_vars['value']['notfound']; ?>
</a></td>
                                   <td><a href="javascript:void(0);" onclick="cate_operate(2,<?php echo $this->_tpl_vars['value']['id']; ?>
)"><?php echo $this->_tpl_vars['value']['update']; ?>
</a></td>
                                   <td><a href="javascript:void(0);" onclick="cate_operate(4,<?php echo $this->_tpl_vars['value']['id']; ?>
)"><?php echo $this->_tpl_vars['value']['toInactive']; ?>
</a></td>
                                   <td><?php echo $this->_tpl_vars['value']['storeOffcount']; ?>
</td>
                                 <?php elseif ($this->_tpl_vars['search']['method'] == 'transactionCrawl'): ?>
                                   <td><?php echo $this->_tpl_vars['value']['total']; ?>
</td>
                                   <td></td>
                                   <td></td>
                                 <?php else: ?>
                                   <td><?php echo $this->_tpl_vars['value']['total']; ?>
</td>
                                   <td><?php echo $this->_tpl_vars['value']['new']; ?>
</td>
                                   <td><?php echo $this->_tpl_vars['value']['toInactive']; ?>
</td>
                                 <?php endif; ?>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; endif; unset($_from); ?>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
 
<div class="ui fullscreen modal">
    <i class="close icon"></i>
    <div class="header label"></div>
    <div class="content modelval"  style="overflow:auto;height:100%;">
    </div>
</div>

<script type="text/javascript">    
function cate_operate(type,id){
	 
	$('.modelval').html('');
    $('.label').html('List');
    $.ajax({
        type: "post",
        url: "b_tools_manage_crawl_log.php",
        data: "id="+id+'&type='+type,
        async: false,
        success: function (html) {
            $('.modelval').append(html);
        }
    });
    $('.fullscreen').modal('show');
}
   
</script>	      
 
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>