<?php /* Smarty version 2.6.26, created on 2017-12-08 01:35:33
         compiled from b_store.html */ ?>
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
    .t1{
        min-width: 80px;
    }
</style>
<div class="ch-container" style="margin-top: 20px;">
    <div class="row">
        <div class="col-sm-2 col-lg-2">
            <div class="sidebar-nav" style="background-color:#f6f6f6;">
                <a href="javascript:void(0)" class="sh"><i style="margin-top: 10px;color:#627491;margin-left: 10px;font-size: 18px;" class="glyphicon glyphicon-search"></i></a>
                <input style="width: 95%;margin-left: 2.5%;margin-right: 2.5%;margin-top: 15px;" type="text" class="form-control dtpicker from" placeholder="From">
                <input style="width: 95%;margin-left: 2.5%;margin-right: 2.5%;margin-top: 5px;" type="text" class="form-control dtpicker to" placeholder="To">
                <input style="width: 95%;margin-left: 2.5%;margin-right: 2.5%;margin-top: 15px;" type="text" class="form-control Advertiser" placeholder="Advertiser">
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Choose a Country</label>
                    <select id="country" class="chosen1 chosen-select" multiple="multiple" data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <?php $_from = $this->_tpl_vars['countryArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['c']):
?>
                        <option value="<?php echo $this->_tpl_vars['c']; ?>
"><?php echo $this->_tpl_vars['k']; ?>
</option>
                        <?php endforeach; endif; unset($_from); ?>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Current Status</label>
                    <select id="status" class="chosen2"  data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <option value="Content">Content Only</option>
                        <option value="Promotion">Promotion Only</option>
                        <option value="All">Content & Promotion</option>
                        <option value="Mixed">Mixed</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">PPC Status</label>
                    <select id="ppc" class="chosen2"  data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <option value="PPCAllowed">PPCAllowed</option>
                        <option value="Mixed">Mixed</option>
                        <option value="NotAllow">NotAllow</option>
                        <option value="UNKNOWN">UNKNOWN</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Category Status</label>
                    <select id="catestu" class="chosen2"  data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <option value="YES">Yes</option>
                        <option value="NO">No</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Logo Status</label>
                    <select id="logo" class="chosen2"  data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <option value="1">Multiple Logo</option>
                        <option value="2">NULL logo</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Advertiser Name Status</label>
                    <select id="aname" class="chosen2"  data-rel="chosen" style="width: 96%;">
                        <option value="">All</option>
                        <option value="1">YES</option>
                        <option value="2">NO</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;" for="selectError">Network</label>
                    <select id="networkid" class="chosen2" data-rel="chosen" multiple="multiple" style="width: 96%;">
                        <option value="">ALL</option>
                        <?php $_from = $this->_tpl_vars['affname']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['aff']):
?>
                        <option value="<?php echo $this->_tpl_vars['aff']['ID']; ?>
" class="aff"><?php echo $this->_tpl_vars['aff']['Name']; ?>
</option>
                        <?php endforeach; endif; unset($_from); ?>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;">Category</label>
                    <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['cate']):
?>
                    <br />
                    <input id="cate_<?php echo $this->_tpl_vars['k']; ?>
" data-id="<?php echo $this->_tpl_vars['k']; ?>
" type="checkbox" class="search_cate" style="margin: 0;" />
                    <label for="cate_<?php echo $this->_tpl_vars['k']; ?>
" style="margin-left:3px;font-weight: 100;font-size: 14px;"><?php echo $this->_tpl_vars['cate']; ?>
</label>
                    <?php endforeach; endif; unset($_from); ?>
                </div>


                <!--add coupon policy -->
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;">coupon policy</label>
                    <?php $_from = $this->_tpl_vars['coupon_policy_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cate']):
?>
                    <br />
                    <input id="coupon_policy_<?php echo $this->_tpl_vars['cate']; ?>
" data-id="<?php echo $this->_tpl_vars['cate']; ?>
" type="checkbox" class="search_coupon_policy" style="margin: 0;" />
                    <label for="coupon_policy_<?php echo $this->_tpl_vars['cate']; ?>
" style="margin-left:3px;font-weight: 100;font-size: 14px;"><?php echo $this->_tpl_vars['cate']; ?>
</label>
                    <?php endforeach; endif; unset($_from); ?>
                </div>
                <!--end add-->


                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;">DataType</label>
                    <select id="datatype" class="chosen2" data-rel="chosen" style="width: 96%;">
                        <option value="1">Publisher</option>
                        <option value="2">ALL</option>
                    </select>
                </div>
                <div style="margin-top: 10px;margin-left: 2.5%;margin-right: 2.5%;" class="controls">
                    <label class="control-label" style="margin-left: 3px;">Cooperation Status</label>
                    <select id="cooperation" class="chosen2" data-rel="chosen" style="width: 96%;">
                        <option value="">ALL</option>
                        <option value="1">Yes</option>
                        <option value="2">No</option>
                    </select>
                </div>
                <input  type="button" class="btn search" style="width: 95%;margin-left:2.5%;margin-top:10px;margin-bottom:10px;background-color: #627491;color:white;" value="Search">
                <input  type="button" class="btn  csv" style="width: 95%;margin-left:2.5%;margin-bottom:10px;background-color: #286090;color:white;" value="Download Csv">
            </div>
        </div>


        <div id="content" class="col-lg-10 col-sm-10">
            <!-- content starts -->
            <div>
                <ul class="breadcrumb">
                    <li>
                        <a href="<?php echo @BASE_URL; ?>
/b_home.php">Home</a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" style="text-decoration:none;">Advertisers</a>
                    </li>
                </ul>
            </div>
            <div id="append">

            </div>

            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Total Advertiser</div>
                    <div class="nulls"  id="total"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Total Sales</div>
                    <div class="nulls" id="sales"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Total Commsion</div>
                    <div class="nulls"  id="revenues"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Total Clicks</div>
                    <div class="nulls"  id="totals"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Real Clicks<span style="margin-left:3px;" class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Total Clicks - Robot"></span></div>
                    <div class="nulls"  id="click"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>Robot</div>
                    <div class="nulls"  id="rob"></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-6" style="padding-left: 0;">
                <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                    <div>May Be Robot</div>
                    <div class="nulls"  id="robp"></div>
                </a>
            </div>
            <div class="box col-md-12" style="padding-left:0;padding-right:0;">
                <div class="box-inner">
                    <div class="box-header well" data-original-title="">
                    </div>
                    <div id="tbzone">
                        <table id="example" class="ui celled table" cellspacing="0" width="100%">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="category" style="display: none">
    <button type="button"  class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="Category" >Category&nbsp;<b class="caret"></b></button>
    <ul class="multiselect-container dropdown-menu" onclick="event.stopPropagation();" style="overflow:scroll;height: 600px;width: 300px;">
        <li>
            <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0 0 0 0;"><span class="label label-info" onclick="select_all('<?php echo $this->_tpl_vars['second_id']; ?>
')">Select All</span></a>
            <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0 0 0 0;"><span class="label label-info" onclick="deselect_all('<?php echo $this->_tpl_vars['second_id']; ?>
')">Deselect All</span></a>
            <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0 0 0 0;"><span class="label label-info" onclick="confirm('<?php echo $this->_tpl_vars['second_id']; ?>
')">Confirm</span></a>
        </li>
        <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['first_id'] => $this->_tpl_vars['sub_category']):
?>
        <li>
            <label class="pri_cat multiselect-group" style="margin: 5px 0 0 5px" onclick="operate_sub_cate('<?php echo $this->_tpl_vars['second_id']; ?>
','<?php echo $this->_tpl_vars['standard_first_id']; ?>
')"><?php echo $this->_tpl_vars['sub_category']['Main']; ?>
</label>
            <ul class="multiselect-container">
                <?php $_from = $this->_tpl_vars['sub_category']['Sub']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['second_id'] => $this->_tpl_vars['second_name']):
?>
                <li style="margin: 0;">
                    <a style="text-decoration:none">
                        <label class="checkbox" style="margin: 0;">
                            <input type="checkbox" data-id="<?php echo $this->_tpl_vars['second_id']; ?>
" class="pri_cate_<?php echo $this->_tpl_vars['second_id']; ?>
_<?php echo $this->_tpl_vars['first_id']; ?>
 pri_cate_<?php echo $this->_tpl_vars['second_id']; ?>
"
                            <?php $_from = $this->_tpl_vars['category_relation'][$this->_tpl_vars['second_id']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['current_cate']):
?>
                            <?php if ($this->_tpl_vars['current_cate'] == $this->_tpl_vars['second_id']): ?>checked
                            <?php endif; ?>
                            <?php endforeach; endif; unset($_from); ?>
                            >
                            <?php echo $this->_tpl_vars['second_name']; ?>

                        </label>
                    </a>
                </li>
                <?php endforeach; endif; unset($_from); ?>
            </ul>
        </li>
        <?php endforeach; endif; unset($_from); ?>
    </ul>
</div>
<input type="hidden" id="sid">
<input type="hidden" id="index">
<input type="hidden" id="sname">
<!-- Modal -->
<div class="ui mod fullscreen modal" style="min-height: 90%;">
    <i class="close icon"></i>
    <div class="header label"></div>
    <div class="content modelval"  style="overflow:auto;height:100%;">
    </div>
</div>
<div class="ui fullscreen modal" id="rstore_div" style="min-height: 90%;min-width:90%;">
    <i class="close icon"></i>
    <div class="header rlable"></div>
    <div class="content">
        <div class="box col-md-12">
            <div class="box-inner" style="height: 120px;">
                <div class="box-header well" data-original-title="">Search</div>
                <form id="rfrom">
                    <div style="margin-top: 10px;margin-left: 10px;float: left" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Choose a Country</label>&nbsp;
                        <select name="country" class="chosen3"  data-rel="chosen">
                            <option value="">All</option>
                            <?php $_from = $this->_tpl_vars['countryArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['c']):
?>
                            <option value="<?php echo $this->_tpl_vars['c']; ?>
"><?php echo $this->_tpl_vars['k']; ?>
</option>
                            <?php endforeach; endif; unset($_from); ?>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Language</label>&nbsp;
                        <select  name="language" class="chosen3"  data-rel="chosen" style="width: 100px;">
                            <option value="">All</option>
                            <option value="en">EN</option>
                            <option value="fr">FR</option>
                            <option value="de">DE</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Current Status</label>&nbsp;
                        <select id="status1" name="status" class="chosen3"  data-rel="chosen" style="width:150px;">
                            <option value="">All</option>
                            <option value="Content ">Content Only</option>
                            <option value="Promotion">Promotion Only</option>
                            <option value="All">Content & Promotion</option>
                            <option value="Mixed">Mixed</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Source</label>
                        <select  name="source" class="chosen3"  data-rel="chosen" style="width:100px;">
                            <option value="">All</option>
                            <option value="site">site</option>
                            <option value="email">email</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Type</label>
                        <select  class="chosen3" name="type" data-rel="chosen" style="width:100px;">
                            <option value="">All</option>
                            <option value="Coupon">Coupon</option>
                            <option value="Promotion">Deal</option>
                            <option value="Product">Product</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;" class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Promotions Status</label>&nbsp;
                        <select id="" class="chosen2" name="pstatus"  data-rel="chosen" style="width: 96%;">
                            <option value="">All</option>
                            <option value="1">Ongoing</option>
                            <option value="2">Not started</option>
                            <option value="3">End</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;margin-left: 15px;float: left;"class="controls">
                        <label class="control-label" style="margin-left: 3px;" for="selectError">Title/Desc</label>&nbsp;
                        <input  name="keywords" style="width: 200px;" type="text" class="form-control title" placeholder="Title/Desc">
                    </div>
                    <div style="margin-top: 34px;margin-left: 10px;float: left;" class="controls">
                        <div class="btn-group">
                            <label class="control-label" style="margin-left: 3px;" for="selectError">&nbsp;</label>
                            <button type="button"   class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="Category" >Category&nbsp;<b class="caret"></b></button>
                            <ul class="multiselect-container dropdown-menu" onclick="event.stopPropagation();" style="overflow:scroll;height: 600px;">
                                <li style="margin-left: 10px;margin-top: 5px;">
                                    <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-info" onclick="select_opt('all')">Select All</span></a>
                                    <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-warning" onclick="select_opt('none')">Deselect All</span></a>
                                    <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-info" onclick="select_opt('confirm')">Confirm</span></a>
                                </li>
                                <li style="margin-top:10px;"></li>
                                <input type="hidden"  name="categories" class="categories">
                                <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['cate']):
?>
                                <li><a href="javascript:void(0);" style="margin-left: 10px;"><label class="checkbox" style="margin-bottom: 0px;margin-top: 0px;">
                                    <input type="checkbox" class="category" id="<?php echo $this->_tpl_vars['id']; ?>
" value="<?php echo $this->_tpl_vars['id']; ?>
"><?php echo $this->_tpl_vars['cate']; ?>
</label></a></li>
                                <?php endforeach; endif; unset($_from); ?>
                            </ul>
                        </div>
                    </div>
                    <div style="margin-top:33px;margin-left: 15px;float: left;"class="controls">
                        <input  type="button" class="btn rsearch" style="background-color: #627491;color:white;" value="Search">
                    </div>
                </form>
            </div>
        </div>
        <div class="box col-md-12">
            <div class="box-inner">
                <div class="box-header well" data-original-title="">
                </div>
                <div id="tbzone1">
                    <table id="rstore" class="ui celled table" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ui  modal" id="catemodel" style="min-height: 500px;min-width: 1000px;">
    <div class="header label">Category of <font id="catename"></font></div>

    <div class="content" id="mod" style="overflow:auto;height:100%;padding-top:5px;">
        <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['cate']):
?>
        <div class="col-xs-6 col-md-3" style="margin-top:10px;height: 30px;padding-left:0px;padding-right:0px;">
            <input id="fate_<?php echo $this->_tpl_vars['k']; ?>
" data-id="<?php echo $this->_tpl_vars['k']; ?>
" type="checkbox">&nbsp;<?php echo $this->_tpl_vars['cate']; ?>

        </div>
        <?php endforeach; endif; unset($_from); ?>
        <div style="float: left;width: 100%;margin-top: 10px;text-align: center;">

            <input  type="button" class="btn all" val="0" style="width: 100px;background-color: #5bbfde;color:white;" value="Select All">
            &nbsp;
            <input  type="button" class="btn upcate" val="0" style="width: 100px;background-color: #627491;color:white;" value="Confirm">
        </div>
    </div>
</div>

<div class="ui  modal" id="couponPolicyModel" style="min-height: 500px;min-width: 1000px;">
    <div class="header label">Coupon policy <font id="couponPolicyName"></font></div>

    <div class="content" id="couponPolicyMod" style="overflow:auto;height:100%;padding-top:5px;">
        <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['cate']):
?>
        <div class="col-xs-6 col-md-3" style="margin-top:10px;height: 30px;padding-left:0px;padding-right:0px;">
            <input id="cp_<?php echo $this->_tpl_vars['k']; ?>
" data-id="<?php echo $this->_tpl_vars['k']; ?>
" type="checkbox">&nbsp;<?php echo $this->_tpl_vars['cate']; ?>

        </div>
        <?php endforeach; endif; unset($_from); ?>
        <div style="float: left;width: 100%;margin-top: 10px;text-align: center;">

            <input  type="button" class="btn all" val="0" style="width: 100px;background-color: #5bbfde;color:white;" value="Select All">
            &nbsp;
            <input  type="button" class="btn upcate" val="0" style="width: 100px;background-color: #627491;color:white;" value="Confirm">
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document" style="width: 70%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearAjaxHtml()"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->_tpl_vars['LANG']['backend']['b_merchant']['a5']; ?>
</h4>
            </div>
            <div class="modal-body" id="mb">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearAjaxHtml()"><?php echo $this->_tpl_vars['LANG']['backend']['b_merchant']['a6']; ?>
</button>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="custom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document" style="width: 70%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearAjaxHtml()"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->_tpl_vars['LANG']['backend']['b_merchant']['a5']; ?>
</h4>
            </div>
            <div class="modal-body" id="mb">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearAjaxHtml()"><?php echo $this->_tpl_vars['LANG']['backend']['b_merchant']['a6']; ?>
</button>

            </div>
        </div>
    </div>
</div>
<div class="modal ui" id="newContentDiv" tabindex="-1" role="dialog"  aria-hidden="true" style="max-height: 630px;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title ptitle"></h3>
        </div>
        <div class="modal-body store_add_coupon">
        </div>
    </div>
</div>
<div class="modal ui" id="store_separation" tabindex="-1" role="dialog"  aria-hidden="true" style="max-height: 650px;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title">Store Separation</h3>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2" style="text-align:center;">
                        <form id="fnull1">
                            <table style="width: 100%;border-collapse: separate;border-spacing: 0 10px;" class="s1">
                                <tr>
                                    <td style="text-align: right">Store Name:</td>
                                    <td colspan="2"><input id="newname" placeholder="Please input the Store Name"  type="text" class="form-control CouponCode"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Domain:</td>
                                    <td colspan="2">
                                        <select class="form-control language chosen2" id="sdomain" multiple="true" data-rel="chosen" class="chosen-select">

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" ><button type="button" id="separationinfo" class="btn btn-primary btn-lg btn-block" style="margin: auto;text-align: center;width: 60%">submit</button></td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery.zclip.min.js"></script>
<script>
    $(".dtpicker").datetimepicker({
        minView: "month",//设置只显示到月份
        format : "yyyy-mm-dd",//日期格式
        autoclose:true,//选中关闭
        todayBtn: true//今日按钮
    });
    $(".dtpicker1").datetimepicker({
        format : "yyyy-mm-dd  hh:ii",//日期格式
        todayBtn: true//今日按钮
    });
    $('#sdomain').on('change',function(){
        alert(12);
    })
    function get_category(){
        var cate='';
        var cc =$("input[class='category']:checked");
        for(var i=0;i<cc.length;i++){
            if(cc[i].checked){
                cate += cc[i].value+',';
            }
        }
        cate=cate.substring(0,cate.length-1);
        $('.categories').val(cate);
    }
    $(".category").change(function () {
        get_category();
    });
    function tab(){
        var category = '';
        $('.search_cate').each(function(){
            if($(this).is(":checked")){
                category+=$(this).data('id')+',';
            }
        });
        category=category.substr(0,category.length-1);


        var coupon_policy = '';
        $('.search_coupon_policy').each(function(){
            if($(this).is(":checked")){
                coupon_policy+=$(this).data('id')+',';
            }
        });
        coupon_policy=coupon_policy.substr(0,coupon_policy.length-1);


        var status = $('#status').val();
        var ppc = $('#ppc').val();
        var country = $('#country').val();
        var advertiser = $('.Advertiser').val();
        var logo = $('#logo').val();
        var catestu = $('#catestu').val();
        var aname = $('#aname').val();
        var networkid = $('#networkid').val();
        var datatype = $('#datatype').val();
        var stime = $('.from').val();
        var etime = $('.to').val();
        var cooperation = $('#cooperation').val();
        var s = $('#example').DataTable({
            "fnDrawCallback": function (data) {
                $('.nulls').html('');
                if(data.json.store != '0'){
                    var json = $.parseJSON(data.json.store);
                    var html = '';
                    $.each(json,function(i,itme){
                        var str = '';
                        var name = itme['storeName'];
                        var s = (itme['Store']);
                        $.each(s,function(i,itme){
                            str+= "<a href='javascript:void(0)' onclick=showModal1('"+itme["StoreId"]+"','"+itme["StoreName"]+"')>"+itme["StoreName"]+"<br>";
                        })
                        html+='<tr class="odd"><td>'+name+'</td><td><button class="a" type="button" class="btn btn-default" data-container="body" data-toggle="popover" data-placement="right" data-content="'+str+'" data-trigger="foucs" data-html="true" title="" data-original-title="Recommend Advertiser"><span class="glyphicon glyphicon-link"></span></button></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    })
                    $('#example tr:last').after(html);
                    $(function () { $("[data-toggle='popover']").popover();})
                }
                var clicks = data.json.click;
                var revenues = data.json.revenues;
                var total = data.json.recordsFiltered;
                var sales = data.json.sales;
                var rob = data.json.rob;
                var robp = data.json.robp;
                var totals = data.json.total;
                var dcount = data.json.dcount
                $('#total').html(total+' / '+dcount);
                $('#click').html(clicks);
                $('#revenues').html(revenues);
                $('#sales').html(sales);
                $('#rob').html(rob);
                $('#robp').html(robp);
                $('#totals').html(totals);
                $('.filer_input').on('click',function(){
                    $("#sid").val($(this).data('id'));
                    $("#sname").val($(this).data('name'));
                    $('#index').val($(this).parent().parent().parent().parent().parent().index());
                })
                $('.filer_input').filer({
                    uploadFile:{
                        url: "upload.php",
                        type: 'POST',
                        enctype: 'multipart/form-data',
                        beforeSend: function(){},
                        success: function(data, el){
                            var sid = $('#sid').val();
                            var index = $('#index').val();
                            var dat = {'sid':sid,'imgname':data}
                            $.ajax({
                                type: "post",
                                url: "b_store.php",
                                data:dat,
                                async: false,
                                success: function (res) {
                                    if(res == 1){
                                        var img  = 'https://www.brandreward.com/img/adv_logo/'+data;
                                        $('.imgval').eq(index).find('img').attr('src',img);
                                        //alert($('.imgval').eq(index).html());
                                        alert('Succee');
                                    }else if(res == 2){
                                        var name = $('#sname').val();
                                        var img  = 'https://www.brandreward.com/img/adv_logo/'+data;
                                        var html = '<div style="border: 1px solid #D5D5D5;background-color:white;height: 110px;width : 240px;text-align: center;margin:auto;"><div style="width: 95%;text-align: center;margin:auto;"><img class="img-responsive center-block imgs"  style="margin-top:10px;height: 90px;" src="'+img+'" alt="picture" /></div></div><div style="border: 1px solid #D5D5D5;background-color:#D5D5D5;height: 25px;width : 240px;text-align: center;margin:auto;"><div style="font-weight: bold; text-align: center;margin-top:2px;"><span class="newname'+sid+'">'+name+'<span></div></div>';
                                        $('.imgval').eq(index).html(html);
                                    }
                                }
                            });
                        },
                        error: function(el){
                            var parent = el.find(".jFiler-jProgressBar").parent();
                            el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                                $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");
                            });
                        },
                        statusCode: null,
                        onProgress: null,
                        onComplete: null
                    }
                });
            },
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_store.php",
                "type":'POST',
                "data": {
                    "table":1,
                    "category":category,

                    "coupon_policy":coupon_policy,

                    "country":country,
                    "advertiser":advertiser,
                    "status":status,
                    "ppc":ppc,
                    "logo":logo,
                    "catestu":catestu,
                    "aname":aname,
                    "networkid":networkid,
                    "datatype":datatype,
                    "stime":stime,
                    "etime":etime,
                    "cooperation":cooperation
                }
            },
            "aaSorting": [
                [1, "desc"],
            ],
            columns: [
                { "data": "storeName","sClass":"t1","title":'Advertiser',"render":function(data, type, full, meta){
                    var name = full.storeName;
                    if(full.LogoName != '' && full.LogoName != null)
                    {
                        var logo = full.LogoName;
                        var t = /,/;
                        if(t.test(logo)){
                            logo = logo.split(',');
                            logo = logo[0];
                        }
                        if(full.LogoStatus == 2){
                            var img  = 'https://www.brandreward.com/img/logo_program/'+logo;
                        }else{
                            var img  = 'https://www.brandreward.com/img/adv_logo/'+logo;
                        }
                        return '<a class="imgval" style="cursor:pointer" name='+name+' id="'+full.StoreId+'" rate="0" onclick="showModal(this)"><div style="border: 1px solid #D5D5D5;background-color:white;height: 110px;width: 240px;text-align: center;margin:auto;"><div style="width: 95%;text-align: center;margin:auto;"><img class="img-responsive center-block imgs"  style="margin-top:10px;height: 90px;" src="'+img+'" alt="picture" /></div></div><div style="border: 1px solid #D5D5D5;background-color:#D5D5D5;height: 25px;width:240px;text-align: center;margin:auto;"><div  style="font-weight: bold; text-align: center;margin-top:2px;"><span class="newname'+full.StoreId+'">'+name+'</span></div></div></a>';

                    }else{
                        return '<a class="imgval" style="cursor:pointer" name='+name+' id="'+full.StoreId+'" onclick="showModal(this)"><span class="newname'+full.StoreId+'">'+name+'</span></a>';
                    }
                },"bSortable": false },
                { "data": "clicks","title":"Clicks","asSorting": [ "desc", "asc", "desc" ]},
                { "data": "rob","title":"Robot","bSortable": false},
                { "data": "robp","title":"May Be Robot","bSortable": false},
                { "data": "sales","title":"Sales","asSorting": [ "desc", "asc", "desc" ]},
                { "data":"commission","title":"Commission<br/>Commission Rate","asSorting": [ "desc", "asc", "desc" ],"render":function(data,type,full,meta){
                    if(full.NameOptimized != '' && full.NameOptimized != null){
                        var name = full.NameOptimized;
                    }else{
                        var name = full.storeName;
                    }
                    return full.commission+'<br/><br/>'+full.rate;
                }},
                { "data":"epc","title":"Epc","bSortable": false},
                { "data": "StoreCount","title":"Promotions","bSortable": false,"render":function(data,type,full,meta){
                    if(full.NameOptimized != '' && full.NameOptimized != null){
                        var name = full.NameOptimized;
                    }else{
                        var name = full.storeName;
                    }
                    return '<a style="cursor:pointer" name='+name+' id="'+full.StoreId+'" onclick="show2(this)">'+full.StoreCount+'</a>';
                }},
                { "data": null,"title":'<a href="javascript:void(0)" title="P1:Content\nP2:Promotion\nP3:Both">Current Status</a><br>Cooperation Status',"render":function(data, type, full, meta){
                    if(full.SupportType == 'All'){
                        var val = "Content & Promotion";
                    }else{
                        var val = full.SupportType;
                    }
                    return '<a style="cursor:pointer" name='+ full.storeName +' id="'+full.StoreId+'" onclick="showSupportType(this)">'+val+'</a></br></br>'+full.StoreAffSupport;

                },"bSortable": false },
                { "data": null,"title":'<a href="javascript:void(0)" title="P1:PPCAllowed\nP2:Mixed\nP3:NotAllow">PPC Status</a>',"bSortable": false,"render":function(data,type,full,meta){
                	//return full.PPCStatus;
                	return '<a style="cursor:pointer" name='+ full.storeName +' id="'+full.StoreId+'" onclick="showSupportType(this)">'+full.PPCStatus;+'</a>';
                }},
                { "data": null,"title":"Update Logo","bSortable": false,"render":function(data,type,full,meta){
                    var logo = full.LogoName;
                    var  name = full.storeName;
                    var t = /,/;
                    if(t.test(logo)){
                        var loname = logo.split(',');
                        var lo = '';
                        for (var i=0;i<loname.length ;i++ )
                        {
                            lo+=loname[i]+',';
                        }
                        lo = lo.substring(0,lo.length-1);
                        var val = '<div class="logoshow"><button class="btn btn-info" val="'+lo+'" name="'+name+'" status="'+full.LogoStatus+'" id="' + full.StoreId +'" onclick="logo_operate(this)">Choose Logo</button></div>';
                    }else if(logo == ''){
                        var val = '<div class="logoshow"><form  action="upload.php" method="post" enctype="multipart/form-data"><input type="file" name="files[]" class="filer_input" multiple="multiple" data-name="'+name+'" data-id="'+full.StoreId+'"></form></div>';
                    }else{
                        var val = '<div class="logoshow"><form  action="upload.php" method="post" enctype="multipart/form-data"><input type="file" name="files[]" class="filer_input" multiple="multiple" data-name="'+name+'" data-id="'+full.StoreId+'"></form></div>';
                    }
                    return val;
                }},
                { "data": null,"title":"Operation","sWidth":'110px',"bSortable": false,"render":function(data,type,full,meta){
                    var val = '<div class="dropdown">'+
                            '<button type="button" class="btn dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown">Operations'+
                            '<span class="caret"></span>'+
                            '</button>'+
                            '<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">'+
                            '<li role="presentation">'+
                            '<a role="menuitem" tabindex="-1" href="javascript:void(0)" sid="'+full.StoreId+'"  val="1" onclick="store_operation(this);return false;">Update Category</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                            '<a role="menuitem" tabindex="-1" href="javascript:void(0)" sid="'+full.StoreId+'" val ="2" onclick="store_operation(this);return false;">Update Name</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                            '<a role="menuitem" tabindex="-1" href="javascript:void(0)" sid="'+full.StoreId+'" val ="4" onclick="store_operation(this);return false;">Add Promotion</a>'+
                            '</li>'+
                            '</ul>'+
                            '</div>&nbsp;&nbsp;&nbsp;'+
                            '<div class="adiv'+full.StoreId+'" style="width:95%;display:none;">'+
                            '<input style="width:82%;height: 20px;" type="text" class="form-control aname'+full.StoreId+'"></div>'+
                            '<input type="hidden"  cid="'+full.CategoryId+'" sid="'+full.StoreId+'" sname="'+full.storeName+'" class="opstore'+full.StoreId+'">';
                    return val;
                }}
            ]
        })


    }
    function store_operation(ths){
        var c = '.opstore'+$(ths).attr('sid');
        var id = $(c).attr('sid');
        var cid = $(c).attr('cid');
        var name = $(c).attr('sname');
        if($(ths).attr('val') == 1){
            cate_operate(cid,id,name);
        }else if($(ths).attr('val') == 2){
            var cls1 = '.shidden'+id;
            var cls2 = '.adiv'+id;
            $(cls1).css('display','none');
            $(cls2).css('display','');
            update_aname(name,id);
        }else if($(ths).attr('val')== 4){
            $('.ptitle').html('Add Promotion Of '+name);
            var data = {addp:1,"sid":id}
            $.ajax({
                type: "post",
                url: "b_store.php",
                data: data,
                async: false,
                success: function (res){
                    if(res != ''){
                        $('.store_add_coupon').html(res);

                    }else{
                        $('.store_add_coupon').html('');
                    }
                }
            });
            $('#newContentDiv').modal('show');
        }
    }

    tab();
    function store_custom(){
        $('#custom').modal('show');
    }
    $('.chosen1').chosen({width:'100%;'});
    $('.chosen2').chosen({width:'100%;'});
    //  $('.catemodel').modal('show');
    $('.all').on('click',function(){
        if($(this).attr('val') == 0){
            $('#mod :checkbox').prop("checked",true);
            $(this).attr('val',1);
            // alert($(this).attr('val'));
        }else{
            $('#mod :checkbox').prop("checked",false);
            $(this).attr('val',0);
        }
    })
    function show2(ths){
        $('#rfrom')[0].reset();
        $('.chosen3').chosen({width:'100%;'});
        var id = $(ths).attr('id');
        var name = $(ths).attr('name');
        $('.rsearch').attr('val',id);
        table(id);
        $('.rlable').html('Content Feed Of '+name);
        $('#rstore_div').modal('show');
    }
    function table(id){
        var data = $('#rfrom').serializeArray();
        var s = $('#rstore').DataTable({
            "fnDrawCallback": function (data) {
                var total = data.json.recordsFiltered;
                $('#total').html(total);
                $('.copydata').zclip({
                    path: "js/ZeroClipboard.swf",
                    copy:function(){
                        return $(this).data('val');
                    }
                });
                $('.delete_content').bind('click',function(){
                    if(confirm('DO you really want to delete the content feed in ' + $(this).data('name') + '?')){
                        var id = $(this).data('id');
                        var index = $(this).parent().parent().index();
                        var data = {'id':$(this).data('id'),'delete_content':1}
                        $.ajax({
                            type:"post",
                            url:"b_content_new.php",
                            data:data,
                            async:false,
                            success: function(res){
                                if(res == 1){
                                    $('tr').eq(index).remove();
                                    alert('Success');
                                }else {
                                    alert('Error');
                                }
                            }
                        });
                    }
                    else
                    {
                        alert('Operation cancelled!');
                    }
                });
            },
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
             destroy:true,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_content_new.php",
                "type":'POST',
                "data": {
                    "table":1,
                    "data":JSON.stringify(data),
                    "id":id
                }
            },
            "aaSorting": [
                [6, "desc"],
            ],
            columns: [
                { "data":null,"title":"Network","bSortable": false,"render":function(data,type,full,meta){
                    if(full.aname != '' && full.aname != null){
                        var name = full.aname;
                    }else{
                        var name = '';
                    }null
                    return name;
                }},
                { "data": "Title","title":"Title","bSortable": false},
                { "data":null,"title":"Description","bSortable": false,"render":function(data,type,full,meta){
                    if(full.Desc != '' && full.Desc != null){
                        var desc = full.Desc;
                    }else{
                        var desc = '';
                    }
                    return desc;
                }},
                { "data": 'CouponCode',"title":'Coupon Code',"bSortable": false },
                { "data": 'StartDate',"sClass":"t1","title":"Start Date","asSorting": [ "desc", "asc", "desc" ]},
                { "data": 'EndDate',"sClass":"t1","title":"End Date","asSorting": [ "desc", "asc", "desc" ]},
                { "data": 'AddTime',"sClass":"t1","title":"AddTime","asSorting": [ "desc", "asc", "desc" ]},
                { "data": null,"title":"Operation","bSortable": false,"render":function(data,type,full,meta){
                    var url = full.LinkUrl;
                    var html = '<div style="position: relative;"><input type="button"  data-val="'+url+'" class="btn btn-info copydata"     value="Copy Link"/></div>';
                    return html;
                }}
            ]
        })
    }
    function update_aname(name,id){
        var c = '.aname'+id;
        $(c).on('keydown',function(){
            var val = $(this).val();
            var cls1 = '.shidden'+id;
            var cls2 = '.adiv'+id;
            if(event.keyCode == 27){
                $(cls1).css('display','');
                $(''+cls1+' option:first').prop("selected", 'selected');
                $(cls2).css('display','none');
            }
            if(event.keyCode == 13){
                var data = {id:id,val:val,'atype':1,'oldval':name}
                if(val == ''){
                    alert('Not Empty');
                    return false;
                }
                $.ajax({
                    type: "post",
                    url: "b_store.php",
                    data: data,
                    async: false,
                    success: function (res){
                        if(res == 1){
                            alert('Succee');
                            var nc = ".newname"+id;
                            $(nc).html(val);
                            $(cls1).css('display','');
                            $(''+cls1+' option:first').prop("selected", 'selected');
                            $(cls2).css('display','none');
                        }else{
                            alert('Error');
                        }
                    }
                });
            }
        });
    }
    function logo_operate(ths){
        $('#index').val($(ths).parent().parent().parent().index());
        var status = $(ths).attr('status');
        var id = $(ths).attr('id');
        $("#sid").val(id);
        var val = $(ths).attr('val');
        var name = $(ths).attr('name');
        $('.modelval').html('');
        $('.label').html('Logos Of '+name);
        var loname = val.split(',');
        var html = '';
        for (var i=0;i<loname.length ;i++ )
        {
            if(status == 2){
                var img  = 'https://www.brandreward.com/img/logo_program/'+loname[i];
            }else{
                var img  = 'https://www.brandreward.com/img/adv_logo/'+loname[i];
            }
            html+='<a href="javascript:void(0)" onclick="uplogo(this)" status="'+status+'" id="'+id+'" val="'+loname[i]+'"><div style="margin-top:10px;margin-left:15px;float: left;"><div style="border: 1px solid #D5D5D5;background-color:white;height: 110px;width:250px;text-align: center;margin:auto;"><div style="width: 95%;text-align: center;margin:auto;"><img class="img-responsive center-block imgs"  style="margin-top:10px;height: 90px;" src="'+img+'" alt="picture" /></div></div><div style="border: 1px solid #D5D5D5;background-color:#D5D5D5;height: 25px;width: 250px;text-align: center;margin:auto;"><div class="newname" style="font-weight: bold; text-align: center;margin-top:2px;"><span class="newname">'+name+'</span></div></div></div></a>';
        }
        $('.modelval').append(html);
        $('.mod').modal('show');
    }
    function showModal1(storeId,name){
        $('.modelval').html('');
        $('.label').html('Domains Of  '+name);
        $.ajax({
            type: "post",
            url: "b_merchants_domains.php",
            data: "id="+storeId+'&name='+name+"&type=1",
            async: false,
            success: function (html) {
                $('.modelval').append(html);
            }
        });
        $('.mod').modal('show');
    }
    function clearAjaxHtml(){
        $('#mb').html('');
        $('#mb1').html('');
    }
    $(".Advertiser").keydown(function() {
        if (event.keyCode == "13") {//keyCode=13是回车键
            $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
            tab();
        }
    });
    function uplogo(ths){
        var id = $(ths).attr('id');
        var val = $(ths).attr('val');
        var status = $(ths).attr('status');
        if(confirm("DO you really want to change this logo?"))
        {
            var data = {"id":id,"val":val,"uplogo":'1'};
            $.ajax({
                type: "post",
                url: "b_store.php",
                data:data,
                async: false,
                success: function (res) {
                    if(res == 1){
                        $('.mod').modal('hide');
                        if(status == 2){
                            var img  = 'https://www.brandreward.com/img/logo_program/'+val;
                        }else{
                            var img  = 'https://www.brandreward.com/img/adv_logo/'+val;
                        }
                        var index = $('#index').val();
                        var id = $('#sid').val();
                        $('img').eq(index).attr('src',img);
                        var cls = 'filer_input'+id;
                        $('.logoshow').eq(index).html('<form  action="upload.php" method="post" enctype="multipart/form-data"><input type="file" name="files[]" class="'+cls+'" multiple="multiple" data-id="'+id+'"></form>');
                        $('.filer_input'+id).filer({
                            uploadFile:{
                                url: "upload.php",
                                type: 'POST',
                                enctype: 'multipart/form-data',
                                beforeSend: function(){},
                                success: function(data, el){
                                    var sid = $('#sid').val();
                                    var index = $('#index').val();
                                    var dat = {'sid':sid,'imgname':data}
                                    $.ajax({
                                        type: "post",
                                        url: "b_store.php",
                                        data:dat,
                                        async: false,
                                        success: function (res) {
                                            if(res == 1){
                                                var img  = 'https://www.brandreward.com/img/adv_logo/'+data;
                                                $('img').eq(index).attr('src',img);
                                                alert('Succee');
                                            }else{
                                                alert('Error');
                                            }
                                        }
                                    });
//                                var parent = el.find(".jFiler-jProgressBar").parent();
//                                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
//                                    $("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");
//                                });
                                },
                                error: function(el){
                                    var parent = el.find(".jFiler-jProgressBar").parent();
                                    el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                                        $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");
                                    });
                                },
                                statusCode: null,
                                onProgress: null,
                                onComplete: null
                            }
                        });
                        $('.filer_input'+id).on('click',function(){
                            $("#sid").val($(this).data('id'));
                            $("#sname").val($(this).data('name'));
                            $('#index').val($(this).parent().parent().parent().parent().parent().index());
                        })
                        alert('Succee');
                    }
                },
                error:function(){
                    alert('Failed!');
                }
            });
        }
    }

    function changes(t){
        var id = t.id;
        var old_val = $('#'+id).data('old-val');
        if(confirm("DO you really want to change support type?"))
        {
            var storeId = $(t).attr('val');
            var supportType = t.value;
            var data = {UpdateSupport:1,storeId:storeId,supportType:supportType,oldVal:old_val};
            $.ajax({
                type: "post",
                url: "b_store.php",
                data:data,
                async: false,
                success: function (res) {
                    if(res == 1){
                        $('#'+id).data('old-val',supportType);
                        alert('Success!');
                    }else {
                        alert('Failed!');
                    }
                },
                error:function(){
                    alert('Failed!');
                }
            });
        }
        // console.log();
    }
    function changeppc(t){
        var id = t.id;
        var old_val = $('#'+id).data('old-val');
        if(confirm("DO you really want to change PPC status?"))
        {
            var storeId = $(t).attr('val');
            var supportType = t.value;
            var data = {UpdatePPC:1,storeId:storeId,ppc:supportType,oldVal:old_val};
            $.ajax({
                type: "post",
                url: "b_store.php",
                data:data,
                async: false,
                success: function (res) {
                    if(res == 1){
                        $('#'+id).data('old-val',supportType);
                        alert('Success!');
                    }else {
                        alert('Failed!');
                    }
                },
                error:function(){
                    alert('Failed!');
                }
            });
        }
        // console.log();
    }
    function cate_operate(cid,sid,sname){
        var cateid = cid;
        var id = sid;
        var name = sname;
        $('#catename').html(name);
        $('#mod :checkbox').prop("checked", false);
        var arr = cateid.split(',');
        for(var i=0;i<arr.length;i++){
            $('#fate_'+arr[i]).prop('checked','true');
        }
        $('.upcate').attr('val',id);
        $('#catemodel').modal('show');
    }
    $('.upcate').on('click',function(){
        var id = $(this).attr('val');
        var index = $(this).attr('index');
        var cate = '';
        $('#mod :checkbox').each(function(i){
            if($('#mod :checkbox').eq(i).is(':checked')){
                cate+=$('#mod :checkbox').eq(i).attr('data-id')+',';
            }
        })
        if(cate == ''){
            alert('Please select');
            return false;
        }else {
            var data={'act':'UpdateCategory','cate':cate,'id':id}
            $.ajax({
                type: "post",
                url: "process.php",
                data:data,
                async: false,
                success: function (res) {
                    if(res == 1){
                        alert('Succee');
                        var c = ".opstore"+id;
                        $(c).attr('cid',''+cate.substring(0,cate.length-1)+'');
                        $('.res option:first').prop("selected", 'selected');
                        $('#catemodel').modal('hide');
                    }else{
                        alert('Error');
                    }
                },
                error:function(){
                    alert('Failed!');
                }
            });
        }
    })
    $('.search').bind("click",function(){
        $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
        tab();
    })
    $('.rsearch').bind("click",function(){
        var id = $(this).attr('val');
        $('#tbzone1').html('<table id="rstore" class="ui celled table" cellspacing="0" width="100%"></table>');
        table(id);
    })
    $('.sh').bind("click",function(){
        $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
        tab();
    })
    $(".checkmin").bind("click",function(){
        var id = $(this).data('id');
        if($(this).is(':checked')){
            $('.catesub'+id).prop('checked',true);
        }else{
            $('.catesub'+id).prop('checked',false);
        }
    });
    $(".cate_btn").bind("click",function(){
        var id = this.id;
        var cls = '.catesub'+id;
        var ckm = '.checkmin'+id;
        var chk = 0;
        $(cls).each(function(i){
            if($(this).is(":checked")){
                chk++;
            }
        });
        if(chk == 0){
            $(ckm).prop('checked',false);
        }else{
            $(ckm).prop('checked',true);
        }
    });
    function showModal(t){
        $('.modelval').html('');
        var name = $(t).attr('name');
        var rate = $(t).attr('rate');
        var storeId = $(t).attr('id');
        var data = {'id':storeId,'name':name,'type':1,'rate':rate};
        $('.label').html('Domains Of '+name);
        $.ajax({
            type: "post",
            url: "b_merchants_domains.php",
            data: data,
            async: false,
            success: function (html) {
                $('.modelval').append(html);
            },
            error:function(){
                alert('Failed!');
            }
        });
        $('.mod').modal('show');
    }

    function showSupportType(t){
        $('.modelval').html('');
        var name = $(t).attr('name');
        var storeId = $(t).attr('id');
        var data = {'id':storeId,'name':name};
        $('.label').html('Support type of '+name);
        $.ajax({
            type: "post",
            url: "b_merchants_support_type.php",
            data: data,
            async: false,
            success: function (html) {
                $('.modelval').append(html);
            },
            error:function(){
                alert('Failed!');
            }
        });
        $('.mod').modal('show');
    }

    $('.csv').bind("click",function(){
        var category = '';
        $('.search_cate').each(function(i){
            if($(this).is(":checked")){
                category+=$(this).data('id')+',';
            }
        });
        category=category.substr(0,category.length-1);
        var ppc = $('#ppc').val();
        var country = $('#country').val();
        var advertiser = $('.Advertiser').val();
        var status = $('#status').val();
        var catestu = $('#catestu').val();
        var cooperation = $('#cooperation').val();
        var aname = $('#aname').val();
        var networkid = $('#networkid').val();
        var datatype = $('#datatype').val();
        var stime = $('.from').val();
        var etime = $('.to').val();
        window.location.href = 'process.php?act=AdvertiserCsv&cooperation='+cooperation+'&stime='+stime+'&etime='+etime+'&datatype='+datatype+'&aname='+aname+'&catestu='+catestu+'&advertiser='+advertiser+'&ppc='+ppc+'&status='+status+'&country='+country+'&category='+category+'&networkid='+networkid;
    })
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>