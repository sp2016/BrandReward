<?php /* Smarty version 2.6.26, created on 2018-01-30 03:34:47
         compiled from b_tools_key_msg_lose_alert.html */ ?>
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
<style>
    .s{
        max-width: 30%;
        word-wrap:break-word;word-break:break-all;
    }
</style>
<div class="ch-container" style="margin-top: 20px;">
    <div class="row">
        <div id="content" class="col-lg-12 col-sm-12">
            <div class="box col-md-12" style="padding-left:0;padding-right:0;">
                <div class="panel panel-default">
                    <div class="panel-heading" style="text-align: center"><h1>Program Crawl Info Missing Alert</h1></div>
                    <div class="panel-body">
                        <form id="form_content_search">
                            <div class="row">
                                <div class=" form-inline">
                                    <div class="col-lg-12 ">
                                        <div class="form-group dpm" style="position:relative;margin-left: 10px;">
                                            <input type="text" name="date" class="form-control dtpicker" placeholder="Run Time" value="<?php echo $this->_tpl_vars['search']['date']; ?>
">
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
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
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;KeyField
                                            <select name="field" class="form-control" style="width:120px">
                                                <option value="">All</option>
                                                <?php $_from = $this->_tpl_vars['fieldsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['lc']):
?>
                                                <option value="<?php echo $this->_tpl_vars['lc']; ?>
" <?php if (isset ( $this->_tpl_vars['search']['field'] ) && $this->_tpl_vars['search']['method'] == $this->_tpl_vars['lc']): ?>selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['lc']; ?>
</option>
                                                <?php endforeach; endif; unset($_from); ?>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;ProgramID
                                            <input name="pid" class="form-control" style="width:80px">
                                        </div>
                                        <div class="form-group" style="margin-left: 30px;">
                                            <a href="javascript:void(0);" onclick="logSearch()" style="padding: 7px 15px;background: #ddd;color: #111;border-radius:4px;text-decoration: none">搜索</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>


                    <div id="modelval" style="overflow:auto;height:100%;">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <table class="table table-striped" id="table_aff" style="table-layout:fixed;">
                                    <thead>
                                        <tr>
                                            <th>Network</th>
                                            <th>Field</th>
                                            <th>Missing</th>
                                            <th>ProgramId</th>
                                        </tr>
                                    </thead>

                                    <tbody style="text-align: center;">
                                        <?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['value']):
?>
                                        <tr>
                                            <td><?php echo $this->_tpl_vars['affiList'][$this->_tpl_vars['value']['affid']]['Name']; ?>
(<?php echo $this->_tpl_vars['value']['affid']; ?>
)</td>
                                            <td><?php echo $this->_tpl_vars['value']['field']; ?>
</td>
                                            <td><?php echo $this->_tpl_vars['value']['count']; ?>
</td>
                                            <td style="table-layout: fixed;overflow:auto;"><?php echo $this->_tpl_vars['value']['pidDetail']; ?>
</td>
                                        </tr>
                                        <?php endforeach; endif; unset($_from); ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(".dtpicker").datetimepicker({
        minView: "month",//设置只显示到月份
        format : "yyyy-mm-dd",//日期格式
        autoclose:true,//选中关闭
        todayBtn: true//今日按钮
    });
    function logSearch(){
        var params = $('#form_content_search').serialize('');
        console.log(params);
        $('#modelval').html('');
        $.ajax({
            type: "post",
            url: "b_tools_key_msg_lose_alert.php",
            data: params,
            async: false,
            success: function (html) {
                $('#modelval').html(html);
            }
        });
    }
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>