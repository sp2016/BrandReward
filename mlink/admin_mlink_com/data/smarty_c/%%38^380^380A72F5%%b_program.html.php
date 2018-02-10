<?php /* Smarty version 2.6.26, created on 2018-01-09 03:21:02
         compiled from b_program.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'b_program.html', 149, false),)), $this); ?>
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
table{word-break:break-all}
.form_search .form-inline{margin-bottom:15px;}
.form_search .form-inline .form-group{margin-right:15px;}
hr
{
  position:absolute;
  float:left;
  margin-top: 0px;
  margin-bottom: 0px;
  width:150px;
  border:1px solid;
}
</style>
<div class="container" style="width: 100%">
  <div class="" style="margin-top:30px;">
    <div style="text-align:center;margin-bottom:30px;"><h1><?php echo $this->_tpl_vars['title']; ?>
</h1><br /></div>
    <div class="row" style="padding:20px 0;">

      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Search</div>
          <div class="panel-body" >
            <form id="form_tran_search" class="form_search">

              <input type="hidden" name="p" value="1" />

              <div class="form-inline">
                <div class="form-group" style="margin-right: 0px;">
                  <div class="checkbox">
                    Affiliate:
                  </div>
                  <input type="text" name="affiliate" id="f_affiliate" class="form-control tip_affiliate" value="<?php echo $this->_tpl_vars['search']['affiliate']; ?>
" placeholder="Affiliate Name" />
                </div>


                <div class="form-group" style="margin-right: 0px;">
                  <div class="checkbox">
                    Program:
                  </div>
                  <input type="text" name="program" class="form-control tip_program" value="<?php echo $this->_tpl_vars['search']['program']; ?>
" placeholder="Program Name" />
                </div>


                <div class="form-group"  style="margin-right: 0px;">
                  <div class="checkbox">
                    Domain:
                  </div>
                  <input type="text" name="domain" class="form-control tip_domain" value="<?php echo $this->_tpl_vars['search']['domain']; ?>
" placeholder="Domain Name">
                </div>


                <div class="form-group" style="margin-right: 0px;">
                  <div class="checkbox">
                    Partnership:
                  </div>
                  <select name="partnership" class="form-control">
                    <option value="">--ALL--</option>
                    <option value="Active" <?php if ($this->_tpl_vars['search']['partnership'] == 'Active'): ?>selected<?php endif; ?>>Active</option>
                    <option value="Declined" <?php if ($this->_tpl_vars['search']['partnership'] == 'Declined'): ?>selected<?php endif; ?>>Declined</option>
                    <option value="NoPartnership" <?php if ($this->_tpl_vars['search']['partnership'] == 'NoPartnership'): ?>selected<?php endif; ?>>NoPartnership</option>
                    <option value="Pending" <?php if ($this->_tpl_vars['search']['partnership'] == 'Pending'): ?>selected<?php endif; ?>>Pending</option>
                    <option value="Expired" <?php if ($this->_tpl_vars['search']['partnership'] == 'Expired'): ?>selected<?php endif; ?>>Expired</option>
                    <option value="Removed" <?php if ($this->_tpl_vars['search']['partnership'] == 'Removed'): ?>selected<?php endif; ?>>Removed</option>
                  </select>
                </div>
                <div class="form-group" style="margin-right: 0px;">
                  <div class="checkbox">
                    Status In Affiliate:
                  </div>
                  <select name="statusinaff" class="form-control">
                    <option value="">--ALL--</option>
                    <option value="Active" <?php if ($this->_tpl_vars['search']['statusinaff'] == 'Active'): ?>selected<?php endif; ?>>Active</option>
                    <option value="TempOffline" <?php if ($this->_tpl_vars['search']['statusinaff'] == 'TempOffline'): ?>selected<?php endif; ?>>TempOffline</option>
                    <option value="Offline" <?php if ($this->_tpl_vars['search']['statusinaff'] == 'Offline'): ?>selected<?php endif; ?>>Offline</option>
                  </select>
                </div>
                <div class="form-group" style="margin-right: 0px;">
                  &nbsp;
                  <input type="hidden" value="<?php echo $this->_tpl_vars['search']['categories']; ?>
" name="categories" class="categories">
                  <div class="btn-group">
                    <button type="button"  class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="Category" >Category&nbsp;<b class="caret"></b></button>
                    <ul class="multiselect-container dropdown-menu" onclick="event.stopPropagation();" style="overflow:scroll;height: 600px;">
                      <li>
                        <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-info" onclick="select_opt('all')">Select All</span></a>
                        <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-warning" onclick="select_opt('none')">Deselect All</span></a>
                        <a href="javascript:void(0);" style="display: inline;margin:0 auto; padding:0;"><span class="label label-info" onclick="select_opt('confirm')">Confirm</span></a>
                      </li>
                      <?php $_from = $this->_tpl_vars['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['cate']):
?>
                      <li><a href="javascript:void(0);"><label class="checkbox"><input type="checkbox"
                        <?php if ($this->_tpl_vars['sel_cate']): ?>
                        <?php $_from = $this->_tpl_vars['sel_cate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sc']):
?>
                        <?php if ($this->_tpl_vars['sc'] == $this->_tpl_vars['cate']): ?> checked <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                        <?php endif; ?>
                        class="category" id="<?php echo $this->_tpl_vars['cate']; ?>
" value="<?php echo $this->_tpl_vars['cate']; ?>
"> <?php echo $this->_tpl_vars['cate']; ?>
</label></a></li>
                      <?php endforeach; endif; unset($_from); ?>
                    </ul>
                  </div>
                </div>&nbsp;
                <div class="form-group" style="margin-right: 0px;">
                  <div class="checkbox">
                    Country :
                  </div>
                  <select name="country" class="form-control" style="width: 300px;">

                    <option value="">All</option>
                    <?php $_from = $this->_tpl_vars['countryArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['c']):
?>
                    <option value="<?php echo $this->_tpl_vars['c']; ?>
"><?php echo $this->_tpl_vars['k']; ?>
</option>
                    <?php endforeach; endif; unset($_from); ?>
                  </select>
                </div>&nbsp;
                <div class="form-group">
                  <input type="submit" class="btn  btn-primary " value="Search">
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">List</div>
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>

                  <th style="width:15%">Program(ProgramID)<hr><br>IdInAff</th>
                  <th style="width:10%">Affiliate<a href="#" style="color:black" class="tooltip-test" data-toggle="tooltip"
   title="Id Of Affiliate">(AffId)</a></th>
                  <th style="width:12%">Partnership<hr><br>Status In Affiliate</th>
                  <th style="width:13%">Country</th>
                  <th style="width:15%">Category</th>
                  <th style="width:15%">Commission<hr><br>CommissionTxt
                  <th style="width:15%">Domain</th>
                </tr>
              </thead>
              <?php $_from = $this->_tpl_vars['programList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['p']):
?>
              <?php $this->assign('AffId', $this->_tpl_vars['p']['AffId']); ?>
              <?php $this->assign('PID', $this->_tpl_vars['p']['ID']); ?>
                <tr>
                  <td><?php echo $this->_tpl_vars['p']['Name']; ?>
(<b><?php echo $this->_tpl_vars['p']['ID']; ?>
</b>)<hr><br><?php echo $this->_tpl_vars['p']['IdInAff']; ?>
</td>
                  <td><?php echo $this->_tpl_vars['affList'][$this->_tpl_vars['AffId']]['Name']; ?>
(<?php echo $this->_tpl_vars['p']['AffId']; ?>
)</td>
                  <td><?php echo $this->_tpl_vars['p']['Partnership']; ?>
<hr><br><?php echo $this->_tpl_vars['p']['StatusInAff']; ?>
<hr></td>
                  <td><?php echo ((is_array($_tmp=@$this->_tpl_vars['p']['ShippingCountry'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Global') : smarty_modifier_default($_tmp, 'Global')); ?>
</td>
                  <td><div style="overflow: auto; height: 60px;width: 100%"><?php echo ((is_array($_tmp=@$this->_tpl_vars['p']['CategoryExt'])) ? $this->_run_mod_handler('default', true, $_tmp, '-') : smarty_modifier_default($_tmp, '-')); ?>
</div></td>
                  <td>
                    <?php if ($this->_tpl_vars['p']['CommissionType'] == 'Value'): ?><?php echo $this->_tpl_vars['p']['CommissionCurrency']; ?>
<?php endif; ?>
                    <?php echo $this->_tpl_vars['p']['CommissionUsed']; ?>

                    <?php if ($this->_tpl_vars['p']['CommissionType'] == 'Percent'): ?>%<?php endif; ?>
                      <hr><br>
                      <a href="javascript:void(0)" class="viewComm">View CommissionTxt</a>
                      <div style="display:none;"><?php echo $this->_tpl_vars['p']['CommissionExt']; ?>
</div>
                  </td>
                  <td>
                    <?php if ($this->_tpl_vars['p']['Domain']): ?><?php echo $this->_tpl_vars['p']['Domain']; ?>

                    <?php elseif ($this->_tpl_vars['p']['Homepage']): ?>Homepage: <?php echo $this->_tpl_vars['p']['Homepage']; ?>

                    <?php else: ?> -
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; endif; unset($_from); ?>
            </table>
            <div class="form-inline">
            <div class="form-group" style="position:relative;">
           <?php echo $this->_tpl_vars['pageHtml']; ?>

           </div>
           <div class="form-group" style="position:relative;">
           Page Size: <input  id="size" class="form-control" name="page_size" placeholder="<?php if ($this->_tpl_vars['search']['page_size']): ?><?php echo $this->_tpl_vars['search']['page_size']; ?>
<?php endif; ?>" >
            </div>
            <div class="form-group" style="position:relative;">
            <input  type="button" class="btn btn-primary btn-sm go" name="page_size" value="Go">
            </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="dialog-commissiontxt" tabindex="-1" role="dialog"  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h2 class="modal-title" id="exampleModalLabel">CommissionTxt</h2>
      </div>
      <div class="modal-body">

      </div>
      </div>
    </div>
  </div>
</div>

<script>
$('.viewComm').click(function(){
  var commission = $(this).next().html();
  $('#dialog-commissiontxt .modal-body').html(commission);
  $('#dialog-commissiontxt').modal();
});



(function(){
  $('.tip_affiliate').keyup(function(){
    var ipt = this;
    var keywords = $(this).val();
    var url = '<?php echo @BASE_URL; ?>
/process.php';
    var ajaxdata = 'act=tip_affiliate&keywords='+keywords;

    if(!keywords.match(/[^\s]{1,}/)){
      return;
    }

    $.ajax({
      type:"post",
      url:"<?php echo @BASE_URL; ?>
/process.php",
      data:ajaxdata,
      success: function(req){
        var html_tip = '<ul class="dropdown-menu" >';
        var arr = req.split('|');
        for(var i in arr){
          html_tip = html_tip+'<li><a href="javascript:void(0);">'+arr[i]+'</a></li>';
        }
        var html_tip = html_tip+'</ul>';


        if($(ipt).parent().find('ul')){
          $(ipt).parent().find('ul').remove();
        }

        $(ipt).parent().append(html_tip);

        load_tip($(ipt).parent().find('ul'));
      }
    });


  });

  $('.tip_program').keyup(function(){
    var ipt = this;
    var keywords = $(this).val();
    var url = '<?php echo @BASE_URL; ?>
/process.php';
    var ajaxdata = 'act=tip_program&keywords='+keywords+'&affname='+$('#f_affiliate').val();
    if(!keywords.match(/[^\s]{3,}/)){
      return;
    }

    $.ajax({
      type:"post",
      url:"<?php echo @BASE_URL; ?>
/process.php",
      data:ajaxdata,
      success: function(req){
        var html_tip = '<ul class="dropdown-menu" >';
        var arr = req.split('|');
        for(var i in arr){
          html_tip = html_tip+'<li><a href="javascript:void(0);">'+arr[i]+'</a></li>';
        }
        var html_tip = html_tip+'</ul>';


        if($(ipt).parent().find('ul')){
          $(ipt).parent().find('ul').remove();
        }

        $(ipt).parent().append(html_tip);

        load_tip($(ipt).parent().find('ul'));
      }
    });
  });


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
  $('.tip_domain').keyup(function(){
    var ipt = this;
    var keywords = $(this).val();
    var url = '<?php echo @BASE_URL; ?>
/process.php';
    var ajaxdata = 'act=tip_domain&keywords='+keywords;

    if(!keywords.match(/[^\s]{3,}/)){
      return;
    }

    $.ajax({
      type:"post",
      url:"<?php echo @BASE_URL; ?>
/process.php",
      data:ajaxdata,
      success: function(req){
        var html_tip = '<ul class="dropdown-menu" >';
        var arr = req.split('|');
        for(var i in arr){
          html_tip = html_tip+'<li><a href="javascript:void(0);">'+arr[i]+'</a></li>';
        }
        var html_tip = html_tip+'</ul>';


        if($(ipt).parent().find('ul')){
          $(ipt).parent().find('ul').remove();
        }

        $(ipt).parent().append(html_tip);

        load_tip($(ipt).parent().find('ul'));
      }
    });
  });

})();
function select_opt(val)
{
  switch (val)
  {
    case 'all':
      $('.category').prop('checked',true);
      break;
    case 'none':
      $('.category').prop('checked',false);
      break;
    case 'confirm':
      $('.btn-group').removeClass('open');
      break;
    default:
      break;
  }
  get_category();
}
function load_tip(obj){
  console.info(obj);
  $(obj).css('display','block');
  $(obj).find('a').click(function(){
    $(obj).prev().val($(this).text());
    $(obj).remove();
  });
}
$(function () { $("[data-toggle='tooltip']").tooltip(); });
//调节当前页目录条数
$('.go').click(function(){

	var url_now = window.location.href;
	if(url_now.indexOf('&') != -1){
		var url_new = url_now+'&page_size='+$('#size').val();
	}else{
		var url_new = url_now+'?page_size='+$('#size').val();
	}

	window.location.href = url_new;
});
</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>