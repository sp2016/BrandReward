<?php /* Smarty version 2.6.26, created on 2017-12-21 02:25:38
         compiled from b_aff_aff.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'b_aff_aff.html', 75, false),array('modifier', 'number_format', 'b_aff_aff.html', 140, false),)), $this); ?>
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
<style type="text/css"> 
.table>tbody>tr>td,.table>tbody>tr>th,.table>tfoot>tr>td,.table>tfoot>tr>th,.table>thead>tr>td,.table>thead>tr>th
{
 vertical-align:middle;
}
th{
  text-align: left;
}
hr
{
  position:absolute;
  float:left;
  margin-top: 0px;
  margin-bottom: 0px;
  width:150px;
  border:1px solid;
}
xmp
{
	  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
}
</style>
<div>
  <div style="margin-top:30px; width:100%">
    <div style="text-align:center;margin-bottom:30px;"><h1><?php echo $this->_tpl_vars['title']; ?>
</h1></div>
    <div class="row" style="padding:20px 0;">

      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Search</div>
          <div class="panel-body" >
            <form id="sfrom">
              <input type="hidden" name="p" value="1" />
            <div class="col-lg-12 form-inline">
              <div class="form-group ">
                Name:<input type="text" name="name" class="form-control tip" id="name" value="<?php echo $this->_tpl_vars['search']['name']; ?>
" placeholder="name">
                Domain:<input type="text" name="domain" class="form-control" id="domain" value="<?php echo $this->_tpl_vars['search']['domain']; ?>
" placeholder="domain">
                Transaction Crawled:
                <select name="statsReportCrawled" id="statsReportCrawled" class="form-control">
                  <option value="">ALL</option>
                  <option value="YES">YES</option>
                  <option value="NO" <?php if ($this->_tpl_vars['search']['statsReportCrawled'] == 'NO'): ?>selected<?php endif; ?>>NO</option>
                  <option value="Can Not Crawl" <?php if ($this->_tpl_vars['search']['statsReportCrawled'] == 'Can Not Crawl'): ?>selected<?php endif; ?>>Can Not Crawl</option>
                  <option value="No Need to Crawl" <?php if ($this->_tpl_vars['search']['statsReportCrawled'] == 'No Need to Crawl'): ?>selected<?php endif; ?>>No Need to Crawl</option>
                  <option value="Request to Crawl" <?php if ($this->_tpl_vars['search']['statsReportCrawled'] == 'Request to Crawl'): ?>selected<?php endif; ?>>Request to Crawl</option>
                </select> 

                ProgramCrawled:
                <select name="programCrawled" id="programCrawled" class="form-control">
                  <option value="">ALL</option>
                  <option value="YES" <?php if ($this->_tpl_vars['search']['programCrawled'] == 'YES'): ?>selected<?php endif; ?>>YES</option>
                  <option value="NO" <?php if ($this->_tpl_vars['search']['programCrawled'] == 'NO'): ?>selected<?php endif; ?>>NO</option>
                  <option value="Can Not Crawl" <?php if ($this->_tpl_vars['search']['programCrawled'] == 'Can Not Crawl'): ?>selected<?php endif; ?>>Can Not Crawl</option>
                  <option value="No Need to Crawl" <?php if ($this->_tpl_vars['search']['programCrawled'] == 'No Need to Crawl'): ?>selected<?php endif; ?>>No Need to Crawl</option>
                  <option value="Request to Crawl" <?php if ($this->_tpl_vars['search']['programCrawled'] == 'Request to Crawl'): ?>selected<?php endif; ?>>Request to Crawl</option>
                </select> 

                IsActive:
                <select name="isActive" id="isActive" class="form-control">
                  <option value="">ALL</option>		
                  <option value="YES" <?php if ($this->_tpl_vars['search']['isActive'] == 'YES'): ?>selected<?php endif; ?>>YES</option>
                  <option value="NO" <?php if ($this->_tpl_vars['search']['isActive'] == 'NO'): ?>selected<?php endif; ?>>NO</option>
                </select>
                Revenue Received:
                <select name="received" id="received" class="form-control">
                    <option value="">ALL</option>
                    <option value="YES" <?php if ($this->_tpl_vars['search']['received'] == 'YES'): ?>selected<?php endif; ?>>YES</option>
                    <option value="NO" <?php if ($this->_tpl_vars['search']['received'] == 'NO'): ?>selected<?php endif; ?>>NO</option>
                </select>
                Revenue Account:
                <select name="revenueAccount" id="isActive" class="form-control">
                <option value="">ALL</option>
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['fin_rev_acc_list'],'selected' => $this->_tpl_vars['search']['revenueAccount']), $this);?>
       
                </select>

                Type:
                <select name="isInHouse" id="isInHouse" class="form-control">
                  <option value="">ALL</option>
                  <option value="YES" <?php if ($this->_tpl_vars['search']['isInHouse'] == 'YES'): ?>selected<?php endif; ?>>InHouse</option>
                  <option value="NO" <?php if ($this->_tpl_vars['search']['isInHouse'] == 'NO'): ?>selected<?php endif; ?>>NetWork</option>
                </select> 
              </div>
              <div class="form-group " style="margin-top:20px">
                Level:
                <select name="level" id="level" class="form-control">
                  <option value="">ALL</option>
                  <option value="TIER1" <?php if ($this->_tpl_vars['search']['level'] == 'TIER1'): ?>selected<?php endif; ?>>TIER1</option>
                  <option value="TIER2" <?php if ($this->_tpl_vars['search']['level'] == 'TIER2'): ?>selected<?php endif; ?>>TIER2</option>
                </select> 
                <input type="hidden" name="limit" id="limit2" value="<?php echo $this->_tpl_vars['search']['limit']; ?>
" />
                <button type="submit" class="btn  btn-primary">Search</button>
              </div>
            </div>
            
          </form>
          </div>
        </div>
      </div>
      
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Result</div>
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>Name(Short)</th>
                  <th>Domain
                  <hr/><br/>
                  Account
                  <hr/><br/>
                  Password
                  </th>
                  <th>Type</th>
                  <th>Level</th>
                  <th>Revenue</br>Account</th>
                  <th>Revenue</br>Received</th>
                  <th>Stats</br>Report</br>Crawled</th>
                  <th>Program</br>Crawled</th>
                  <th>30 Days</br>Commission</th>
                  <th>30~60 Days</br>Commission</th>
                  <th>operation</th>
                   </tr>
              </thead>
              <?php $_from = $this->_tpl_vars['AffList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['aff']):
?>
              <?php $this->assign('affid', $this->_tpl_vars['aff']['Id']); ?>
                <tr>
                  <td><?php echo $this->_tpl_vars['aff']['Id']; ?>
</td>
                  <td><?php echo $this->_tpl_vars['aff']['Name']; ?>
  (<?php echo $this->_tpl_vars['aff']['ShortName']; ?>
)</td>
                  <td style="word-break:break-all;">[D]<a href="<?php echo $this->_tpl_vars['aff']['Domain']; ?>
" target="domain"><?php echo $this->_tpl_vars['aff']['Domain']; ?>
</a><br/>[A]<?php echo $this->_tpl_vars['aff']['Account']; ?>
<br/>[P]<?php echo $this->_tpl_vars['aff']['Password']; ?>
</td>
                  <td><?php if ($this->_tpl_vars['aff']['IsInHouse'] == 'YES'): ?><font color="orange">InHouse</font><?php else: ?><font color="blue">NetWork</font><?php endif; ?></td>
                  <td><?php echo $this->_tpl_vars['aff']['Level']; ?>
</td>
                  <td><?php echo $this->_tpl_vars['fin_rev_acc_list'][$this->_tpl_vars['aff']['RevenueAccount']]; ?>
</td>
                  <td><?php if ($this->_tpl_vars['aff']['RevenueReceived']): ?><b style="color:green;">YES</b><?php else: ?><b style="color:red;">NO</b><?php endif; ?></td>
                  <td><?php if ($this->_tpl_vars['aff']['StatsReportCrawled'] == 'YES'): ?><b style="color:green;">YES</b><?php else: ?><b style="color:red;">NO</b><?php endif; ?></td>
                  <td><?php if ($this->_tpl_vars['aff']['ProgramCrawled'] == 'YES'): ?><b style="color:green;">YES</b><?php else: ?><b style="color:red;">NO</b><?php endif; ?></td>
                  <td><?php if ($this->_tpl_vars['affComm30'][$this->_tpl_vars['affid']]): ?><b <?php if (! $this->_tpl_vars['affComm60'][$this->_tpl_vars['affid']] || $this->_tpl_vars['affComm60'][$this->_tpl_vars['affid']] < $this->_tpl_vars['affComm30'][$this->_tpl_vars['affid']]): ?>style="color:green;"<?php else: ?>style="color:red;"<?php endif; ?>>$<?php echo ((is_array($_tmp=$this->_tpl_vars['affComm30'][$this->_tpl_vars['affid']])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2', '.', ',') : smarty_modifier_number_format($_tmp, '2', '.', ',')); ?>
<?php else: ?><b>$0<?php endif; ?></b></td>
                  <td><b>$<?php if ($this->_tpl_vars['affComm60'][$this->_tpl_vars['affid']]): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['affComm60'][$this->_tpl_vars['affid']])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2', '.', ',') : smarty_modifier_number_format($_tmp, '2', '.', ',')); ?>
<?php else: ?>0<?php endif; ?></b></td>
                  <td>
                  [<a id="edit_<?php echo $this->_tpl_vars['aff']['Id']; ?>
" href="edit_affiliates.php?action=edit&id=<?php echo $this->_tpl_vars['aff']['Id']; ?>
" style="cursor: pointer"/>Edit</a>]<br/>
                  [<a id="view_<?php echo $this->_tpl_vars['aff']['Id']; ?>
" href="edit_affiliates.php?action=view&id=<?php echo $this->_tpl_vars['aff']['Id']; ?>
"  style="cursor: pointer"/>View</a>]
                  </td>
                </tr>
              <?php endforeach; endif; unset($_from); ?>
            </table>
       
        </div>
      </div>

     <div style="width:100%;">
           <div style="float:left"><?php echo $this->_tpl_vars['pageHtml']; ?>
</div>
               <div  class="form-inline" style="padding: 20px;float:left;margin-left:5px;">
         <input type="text" class="form-control" id="skip" value="">

         <input type="button" class="form-control" id="submit_skip" value="跳转"> Show:
                   <select name="limit" id="limit" class="form-control">
                       <option value="20">20</option>
                       <option value="10" <?php if ($this->_tpl_vars['search']['limit'] == '10'): ?>selected<?php endif; ?>>10</option>
                       <option value="20" <?php if ($this->_tpl_vars['search']['limit'] == '20'): ?>selected<?php endif; ?>>20</option>
                       <option value="50" <?php if ($this->_tpl_vars['search']['limit'] == '50'): ?>selected<?php endif; ?>>50</option>
                       <option value="100" <?php if ($this->_tpl_vars['search']['limit'] == '100'): ?>selected<?php endif; ?>>100</option>
                   </select>entries
      </div>
            </div>
    </div>
  </div>
</div>

<script type="text/javascript">

$("#submit_skip").click(function(){
	var skip=$('#skip').val();
	window.location.href="b_aff_aff.php?&p="+skip;
});

$('#change_password').keyup(function(){                  //接触save_change按钮的禁用
	var password = $('#change_password').val();
	if(password.length>=8){
		$("#save_change").removeAttr("disabled");
	}
	
});

//tip搜索菜单自动提示功能

(function(){
	  $('.tip').keyup(function(){//keyup是jquery函数，表示松开键盘
	    var ipt = this;
	    var keywords = $(this).val();
	    var url = '<?php echo @BASE_URL; ?>
/process.php';
	    var ajaxdata = 'act=tip_wf&keywords='+keywords;

	    if(!keywords.match(/[^\s]{1,}/)){//排除空白符，即空白符不算字符
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

	function load_tip(obj){
	  $(obj).css('display','block');  //jquery的css()函数，相当于display:block
	  $(obj).find('a').click(function(){
		 //console.info($($(obj).parent().find("input")[0]));
		 
		 $($(obj).parent().find("input")[0]).val($(this).html());	    
		  $(obj).remove();
	  });
	}
    $('#limit').on('change',function () {
        var limit = $(this).val();
        if (limit == undefined ) {
            limit = 20;
        }
       $('#limit2').val(limit);
        $("#sfrom").submit();
    });

	//点击add按钮，跳转到新页面
	$("#add").click(function(){
		window.open("add_affiliates.php");
	});

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>