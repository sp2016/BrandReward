<?php /* Smarty version 2.6.26, created on 2017-12-10 21:25:52
         compiled from b_merchant_domains1.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'in_array', 'b_merchant_domains1.html', 32, false),array('modifier', 'default', 'b_merchant_domains1.html', 38, false),array('modifier', 'number_format', 'b_merchant_domains1.html', 58, false),)), $this); ?>
<?php $this->assign('ppc', $this->_tpl_vars['store']['PPC']); ?>
<div class="row" >

    <div class="col-lg-12">
        <div class="panel panel-default" id="store_info">
            <form  id="form_store"  enctype="multipart/form-data">
            <input type="hidden" name="storeid" id="sid" value="<?php echo $this->_tpl_vars['sid']; ?>
">
            <input type="hidden" name="act" value="save">
            <div class="panel-heading"><h4>Advertiser Info</h4></div>
            <div class="panel-body">
                <table class="table table-bordered" style="text-align: center;">
                    <tr>
                        <th>Logo</th>
                        <th>Network</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Support Site</th>
                        <th>PPC Status</th>
                    </tr>
                    <tr>
                        <td rowspan="3">
                        <img src="http://www.brandreward.com/img/adv_logo/<?php echo $this->_tpl_vars['store']['LogoName']; ?>
" style="height: 110px; width: 240px;"><br />
                        <div data-type="edit" style="display:none;"><input type="file" name="logo" id="f_user_certificate" value="上传图片"/></div>
                        </td>
                        <td rowspan="3">
                            <?php echo $this->_tpl_vars['affname']; ?>

                        </td>
                        <td rowspan="3">
                        <p style="margin-bottom: 0px;" id="store_category"><?php $_from = $this->_tpl_vars['store']['category_id_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cid']):
?><?php echo $this->_tpl_vars['category'][$this->_tpl_vars['cid']]; ?>
<br /><?php endforeach; endif; unset($_from); ?></p>
                        <div data-type="edit" style="display:none;overflow: scroll; height: 250px;">
                            <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cid'] => $this->_tpl_vars['cname']):
?>
                                <input id="cate_<?php echo $this->_tpl_vars['cid']; ?>
" name="categoryid[]" value="<?php echo $this->_tpl_vars['cid']; ?>
" type="checkbox" style="margin: 0;" <?php if (((is_array($_tmp=$this->_tpl_vars['cid'])) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['store']['category_id_list']) : in_array($_tmp, $this->_tpl_vars['store']['category_id_list']))): ?>checked<?php endif; ?>>
                                <label for="cate_<?php echo $this->_tpl_vars['cid']; ?>
" style="margin-left:3px;font-weight: 100;font-size: 14px;"><?php echo $this->_tpl_vars['cname']; ?>
</label><br />
                            <?php endforeach; endif; unset($_from); ?>
                        </div>
                        </td>
                        <td>
                        <p style="margin-bottom: 0px;" id="store_name"><?php echo ((is_array($_tmp=@$this->_tpl_vars['store']['NameOptimized'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['store']['storeName']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['store']['storeName'])); ?>
</p>
                        <div data-type="edit" style="display:none;"><input class="form-control" type="text" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['store']['NameOptimized'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['store']['storeName']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['store']['storeName'])); ?>
" name="NameOptimized"></div>
                        </td>
                        <td><?php echo $this->_tpl_vars['store']['SupportType']; ?>
</td>
                        <td><p style="margin-bottom: 0px;" id="store_ppc"><?php echo $this->_tpl_vars['ppc_option'][$this->_tpl_vars['ppc']]; ?>
</p>
                        <div data-type="edit" style="display:none;">
                            <select class="form-control" name="ppc">
                                <?php $_from = $this->_tpl_vars['ppc_option']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['vl'] => $this->_tpl_vars['op']):
?>
                                    <option value="<?php echo $this->_tpl_vars['vl']; ?>
" <?php if ($this->_tpl_vars['vl'] == $this->_tpl_vars['ppc_option'][$this->_tpl_vars['ppc']]): ?>selected<?php endif; ?> ><?php echo $this->_tpl_vars['op']; ?>
</option>
                                <?php endforeach; endif; unset($_from); ?>
                            </select>
                        </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Clicks</th>
                        <th>Sales</th>
                        <th>Commission</th>
                    </tr>
                    <tr>
                        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['store']['clicks'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '0', '.', ',') : smarty_modifier_number_format($_tmp, '0', '.', ',')); ?>
</td>
                        <td>$<?php echo ((is_array($_tmp=$this->_tpl_vars['store']['sales'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2', '.', ',') : smarty_modifier_number_format($_tmp, '2', '.', ',')); ?>
</td>
                        <td>$<?php echo ((is_array($_tmp=$this->_tpl_vars['store']['commission'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2', '.', ',') : smarty_modifier_number_format($_tmp, '2', '.', ',')); ?>
</td>
                    </tr>
                </table>
                <table class="table table-bordered">
                    <tr>
                        <th>Description</th>
                    </tr>
                    <tr>
                        <td><p id="store_desc"><?php echo $this->_tpl_vars['store']['Description']; ?>
</p><div style="display: none;" data-type="edit"><textarea name="desc" cols="150" rows="5"><?php echo $this->_tpl_vars['store']['Description']; ?>
</textarea></div></td>
                    </tr>



                    <tr>
                        <th>Coupon Policy</th>
                    </tr>
                    <tr>
                        <td>
                            <strong>Exclusive Code : </strong>
                            <span id="Exclusive_Code"><?php echo $this->_tpl_vars['store']['Exclusive_Code']; ?>
</span>&nbsp;&nbsp;
                            <span style="display: none;" data-type="edit">
                                <input type="radio" id="Exclusive_Code_yes" name="Exclusive_Code" cols="150" rows="5" value="1" <?php if ($this->_tpl_vars['store']['Exclusive_Code'] == 'YES'): ?>checked="checked"<?php endif; ?> ><label for="Exclusive_Code_yes"> &nbsp;YES&nbsp;&nbsp;</label>
                                <input type="radio" id="Exclusive_Code_no" name="Exclusive_Code" cols="150" rows="5" value="0" <?php if ($this->_tpl_vars['store']['Exclusive_Code'] == 'NO'): ?>checked="checked"<?php endif; ?> ><label for="Exclusive_Code_no"> &nbsp;NO</label>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>CPA Increase : </strong>
                            <span id="CPA_Increase"><?php echo $this->_tpl_vars['store']['CPA_Increase']; ?>
</span>&nbsp;&nbsp;
                            <span style="display: none;" data-type="edit">
                                <input type="radio" id="CPA_Increase_yes" name="CPA_Increase" cols="150" rows="5" value="1" <?php if ($this->_tpl_vars['store']['CPA_Increase'] == 'YES'): ?>checked="checked"<?php endif; ?> ><label for="CPA_Increase_yes"> &nbsp;YES&nbsp;&nbsp;</label>
                                <input type="radio" id="CPA_Increase_no" name="CPA_Increase" cols="150" rows="5" value="0" <?php if ($this->_tpl_vars['store']['CPA_Increase'] == 'NO'): ?>checked="checked"<?php endif; ?> > <label for="CPA_Increase_no"> &nbsp;NO</label>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Allow Inaccurate Promo : </strong>
                            <span id="Allow_Inaccurate_Promo"><?php echo $this->_tpl_vars['store']['Allow_Inaccurate_Promo']; ?>
</span>&nbsp;&nbsp;
                            <span style="display: none;" data-type="edit">
                                <input type="radio" id="Allow_Inaccurate_Promo_yes" name="Allow_Inaccurate_Promo" cols="150" rows="5" value="1" <?php if ($this->_tpl_vars['store']['Allow_Inaccurate_Promo'] == 'YES'): ?>checked="checked"<?php endif; ?> > <label for="Allow_Inaccurate_Promo_yes"> &nbsp;YES&nbsp;&nbsp;</label>
                                <input type="radio" id="Allow_Inaccurate_Promo_no" name="Allow_Inaccurate_Promo" cols="150" rows="5" value="0" <?php if ($this->_tpl_vars['store']['Allow_Inaccurate_Promo'] == 'NO'): ?>checked="checked"<?php endif; ?> > <label for="Allow_Inaccurate_Promo_no"> &nbsp;NO</label>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Allow to Change Promotion Title/Description : </strong>
                            <span id="Allow_to_Change_Promotion_TitleOrDescription"><?php echo $this->_tpl_vars['store']['Allow_to_Change_Promotion_TitleOrDescription']; ?>
</span>&nbsp;&nbsp;
                            <span style="display: none;" data-type="edit">
                                <input type="radio" id="Allow_to_Change_Promotion_TitleOrDescription_yes" name="Allow_to_Change_Promotion_TitleOrDescription" cols="150" rows="5" value="1" <?php if ($this->_tpl_vars['store']['Allow_to_Change_Promotion_TitleOrDescription'] == 'YES'): ?>checked="checked"<?php endif; ?> > <label for="Allow_to_Change_Promotion_TitleOrDescription_yes"> &nbsp;YES&nbsp;&nbsp;</label>
                                <input type="radio" id="Allow_to_Change_Promotion_TitleOrDescription_no" name="Allow_to_Change_Promotion_TitleOrDescription" cols="150" rows="5" value="0" <?php if ($this->_tpl_vars['store']['Allow_to_Change_Promotion_TitleOrDescription'] == 'NO'): ?>checked="checked"<?php endif; ?> > <label for="Allow_to_Change_Promotion_TitleOrDescription_no"> &nbsp;NO</label>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Promo Code has been blacklisted : </strong>
                            <span id="Promo_Code_has_been_blacklisted"><?php echo $this->_tpl_vars['store']['Promo_Code_has_been_blacklisted']; ?>
</span>
                            <div style="display: none;" data-type="edit">
                                <textarea name="Promo_Code_has_been_blacklisted" cols="150" rows="2"><?php echo $this->_tpl_vars['store']['Word_has_been_blacklisted']; ?>
</textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Word has been blacklisted : </strong>
                            <span id="Word_has_been_blacklisted"><?php echo $this->_tpl_vars['store']['Word_has_been_blacklisted']; ?>
</span>
                            <div style="display: none;" data-type="edit">
                                <textarea name="Word_has_been_blacklisted" cols="150" rows="2"><?php echo $this->_tpl_vars['store']['Word_has_been_blacklisted']; ?>
</textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Others : </strong>
                            <span id="Coupon_Policy_Others"><?php echo $this->_tpl_vars['store']['Coupon_Policy_Others']; ?>
</span>
                            <div style="display: none;" data-type="edit">
                                <textarea name="Coupon_Policy_Others" cols="150" rows="2"><?php echo $this->_tpl_vars['store']['Coupon_Policy_Others']; ?>
</textarea>
                            </div>
                        </td>
                    </tr>




                </table>
                <a class="btn btn-info" onclick="update(this)">Update</a>
                <a class="btn btn-info" onclick="save(this)" style="display: none;">Save</a>
            </div>
            </form>
        </div>
    </div>

    <?php if ($this->_tpl_vars['store']['SupportType'] != 'Mixed'): ?>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Advertiser: <?php echo $this->_tpl_vars['search']['name']; ?>
</h4></div>
            <div class="panel-body" style="text-align: center;">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Region</th>
                        <th>Network</th>
                        <th>Name In Network</th>
                        <th>Commission Value</th>
                        <th>PPC Term</th>
                    </tr>
                    </thead>
                    <?php $_from = $this->_tpl_vars['domains']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['domain']):
?>
                    <?php $_from = $this->_tpl_vars['domain']['Outbound']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['out']):
?>
                    <tr class="store">
                        <td><?php echo $this->_tpl_vars['out']['1']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['out']['0']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['domain']['AffName']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['domain']['Name']; ?>
</td>
                        <td>
                            <?php if ($this->_tpl_vars['domain']['commission'] == '0'): ?>Other
                            <?php else: ?>
                            <?php echo $this->_tpl_vars['domain']['commission']; ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->_tpl_vars['domain']['TermAndCondition'] != ''): ?>
                                <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php elseif ($this->_tpl_vars['domain']['ntext'] != ''): ?>
                                <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="showtab1<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" style="display: none;">
                        <td colspan="6">
                            <textarea class="text<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" style="width: 90%;height:300px;margin-top:10px;">
                                <?php echo $this->_tpl_vars['domain']['TermAndCondition']; ?>

                            </textarea>
                            <button style="float:left;margin-top: 10px;margin-left: 47%;margin-bottom: 10px;" sid="<?php echo $this->_tpl_vars['sid']; ?>
"  id="<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" class="btn subtc  btn-primary">Submit</button>
                        </td>
                    </tr>
                    <tr class="showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
 " style="display: none;">
                        <td colspan="6">
                            <?php if ($this->_tpl_vars['domain']['ntext'] != ''): ?>
                                <?php echo $this->_tpl_vars['domain']['ntext']; ?>

                            <?php else: ?>
                                <?php echo $this->_tpl_vars['domain']['TermAndCondition']; ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php endforeach; endif; unset($_from); ?>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Advertiser: <?php echo $this->_tpl_vars['search']['name']; ?>
(Promotion  Publisher Visible)</h4></div>
            <div class="panel-body" style="text-align: center;">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Region</th>
                        <th>Network</th>
                        <th>Name In Network</th>
                        <th>Commission Value</th>
                        <th>PPC Term</th>
                    </tr>
                    </thead>
                    <?php $_from = $this->_tpl_vars['domains']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['domain']):
?>
                    <?php if ($this->_tpl_vars['domain']['SupportType'] == 'All'): ?>
                    <?php $_from = $this->_tpl_vars['domain']['Outbound']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['out']):
?>
                    <tr class="store">
                        <td><?php echo $this->_tpl_vars['out']['1']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['out']['0']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['domain']['AffName']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['domain']['Name']; ?>
</td>
                        <td>
                            <?php if ($this->_tpl_vars['domain']['commission'] == '0'): ?>Other
                            <?php else: ?>
                            <?php echo $this->_tpl_vars['domain']['commission']; ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->_tpl_vars['domain']['TermAndCondition'] != ''): ?>
                            <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php elseif ($this->_tpl_vars['domain']['ntext'] != ''): ?>
                            <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="showtab1<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" style="display: none;">
                        <td colspan="6">
                            <textarea class="text<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" style="width: 90%;height:300px;margin-top:10px;">
                                <?php echo $this->_tpl_vars['domain']['TermAndCondition']; ?>

                            </textarea>
                            <button style="float:left;margin-top: 10px;margin-left: 47%;margin-bottom: 10px;" sid="<?php echo $this->_tpl_vars['sid']; ?>
"  id="<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
" class="btn subtc  btn-primary">Submit</button>
                        </td>
                    </tr>
                    <tr class="showtab<?php echo $this->_tpl_vars['domain']['ProgramId']; ?>
 " style="display: none;">
                        <td colspan="6">
                            <?php if ($this->_tpl_vars['domain']['ntext'] != ''): ?>
                            <?php echo $this->_tpl_vars['domain']['ntext']; ?>

                            <?php else: ?>
                            <?php echo $this->_tpl_vars['domain']['TermAndCondition']; ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Advertiser: <?php echo $this->_tpl_vars['search']['name']; ?>
(Content Publisher Visible)</h4></div>
            <div class="panel-body" style="text-align: center;">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Region</th>
                        <th>Network</th>
                        <th>Name In Network</th>
                        <th>Commission Value</th>
                        <th>PPC Term</th>
                    </tr>
                    </thead>
                    <?php $_from = $this->_tpl_vars['programs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['program']):
?>
                    <tr class="store">
                        <td><?php echo $this->_tpl_vars['program']['ccode']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['program']['region']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['program']['domain']['AffName']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['program']['domain']['Name']; ?>
</td>
                        <td>
                            <?php if ($this->_tpl_vars['program']['domain']['commission'] == '0'): ?>Other
                            <?php else: ?>
                            <?php echo $this->_tpl_vars['program']['domain']['commission']; ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->_tpl_vars['program']['domain']['TermAndCondition'] != ''): ?>
                            <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php elseif ($this->_tpl_vars['program']['domain']['ntext'] != ''): ?>
                            <button onclick='show(this)' id=".showtab<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
">Show Terms</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="showtab1<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
" style="display: none;">
                        <td colspan="6">
                            <textarea class="text<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
" style="width: 90%;height:300px;margin-top:10px;">
                                <?php echo $this->_tpl_vars['program']['domain']['TermAndCondition']; ?>

                            </textarea>
                            <button style="float:left;margin-top: 10px;margin-left: 47%;margin-bottom: 10px;" sid="<?php echo $this->_tpl_vars['sid']; ?>
"  id="<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
" class="btn subtc  btn-primary">Submit</button>
                        </td>
                    </tr>
                    <tr class="showtab<?php echo $this->_tpl_vars['program']['domain']['ProgramId']; ?>
 " style="display: none;">
                        <td colspan="6">
                            <?php if ($this->_tpl_vars['program']['domain']['ntext'] != ''): ?>
                            <?php echo $this->_tpl_vars['program']['domain']['ntext']; ?>

                            <?php else: ?>
                            <?php echo $this->_tpl_vars['program']['domain']['TermAndCondition']; ?>

                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endforeach; endif; unset($_from); ?>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
    function show(obj){
        var id = $(obj).attr('id');
        if($(obj).html() == 'Show Terms'){
            $(obj).html('Pick Up');
            $(id).css('display','');
        }else{
            $(obj).html('Show Terms');
            $(id).css('display','none');
        }
    }
    function update(obj){
        $(obj).next().css('display','');
        $(obj).css('display','none');
        $('div[data-type="edit"]').css('display','');
        $('span[data-type="edit"]').css('display','');
        if($('div[data-type="edit"]').prev().is('p')){
            $('div[data-type="edit"]').prev().css('display','none');
            $('span[data-type="edit"]').prev().css('display','none');
        }
    }

    $('input:file').change(function(){
      var iptfile = this;
      oFReader = new FileReader()
      oFReader.readAsDataURL(iptfile.files[0]);

      oFReader.onload = function (oFREvent) {
        $(iptfile).parent().prev().prev().attr('src',oFREvent.target.result);
      };

    });

    function save(obj){
        $(obj).prev().css('display','');
        $(obj).css('display','none');
        var formData = new FormData($( "#form_store" )[0]);
        $.ajax({
            url: '<?php echo @BASE_URL; ?>
/b_merchants_domains.php',
            type: 'POST',
            cache: false,
            data: formData,
            processData: false,
            contentType: false
        }).done(function(res) {
            var cat_str = '';
            $('input[name="categoryid[]"]:checked').each(function(el,obj){
                cat_str+=$(obj).next().html();
                cat_str+="<br />";
            });
            var name = $('input[name="NameOptimized"]').val();
            var ppc = $('select[name="ppc"] option:selected').html();
            var desc = $('textarea[name="desc"]').val();

            $('#store_category').html(cat_str);
            $('#store_name').html(name);
            $('#store_ppc').html(ppc);
            $('#store_desc').html(desc);
            
            $('div[data-type="edit"]').css('display','none');
            $('span[data-type="edit"]').css('display','none');
            if($('div[data-type="edit"]').prev().is('p')){
                $('div[data-type="edit"]').prev().css('display','');
                $('span[data-type="edit"]').prev().css('display','');
            }




            var result = JSON.parse(res);

            var Exclusive_Code = (result.Exclusive_Code == 1) ? 'YES' : 'NO';
            $('#Exclusive_Code').html(Exclusive_Code);
            var Allow_Inaccurate_Promo = (result.Allow_Inaccurate_Promo == 1) ? 'YES' : 'NO';
            $('#Allow_Inaccurate_Promo').html(Allow_Inaccurate_Promo);
            var CPA_Increase = (result.CPA_Increase == 1) ? 'YES' : 'NO';
            $('#CPA_Increase').html(CPA_Increase);
            var Allow_to_Change_Promotion_TitleOrDescription = (result.Allow_to_Change_Promotion_TitleOrDescription == 1) ? 'YES' : 'NO';
            $('#Allow_to_Change_Promotion_TitleOrDescription').html(Allow_to_Change_Promotion_TitleOrDescription);
            $('#Word_has_been_blacklisted').html(result.Word_has_been_blacklisted);
            $('#Promo_Code_has_been_blacklisted').html(result.Promo_Code_has_been_blacklisted);
            $('#Coupon_Policy_Others').html(result.Coupon_Policy_Others);




        }).fail(function(res) {}); 
    }
</script>