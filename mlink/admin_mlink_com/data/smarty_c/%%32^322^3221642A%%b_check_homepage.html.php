<?php /* Smarty version 2.6.26, created on 2017-12-03 23:51:33
         compiled from b_check_homepage.html */ ?>
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
            <div>
                <ul class="breadcrumb">
                    <li>
                        <a href="<?php echo @BASE_URL; ?>
/b_home.php">Home</a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" style="text-decoration:none;">Check Homepage</a>
                    </li>
                </ul>
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

<script type="text/javascript">
    $('#network').chosen();

    function tab(){
        $('#example').dataTable({
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
	        "ordering": false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_check_homepage.php",
                "type":'POST',
                "data": {
                    "table":1,
                }
            },
            columns: [
                { "data": null,"title":'ProgramID',"width": "10%","render":function(data, type, full, meta){
                    return full.PID;
                }},
	            { "data": null,"bSortable": false,"title":'ErrorType',"width": "10%","render":function(data,type,full,meta){
                	return '<a href="'+ full.Old + '">' + full.Old + '</a>';
                }},
                { "data": null,"bSortable": false,"sClass":'s',"title":'UrlOrTpl',"width": "20%","render":function(data,type,full,meta){
                	return '<a target="_blank" href="'+ full.New + '">' + full.New + '</a>';
                }},
	            { "data": null,"bSortable": false,"title":'Checked',"width": "10%","render":function(data,type,full,meta){
                    return '<button class="btn btn-info"'+(full.Checked=='YES'?'disabled="disabled"':"") +' onclick="check(' + full.ID + ')">' + (full.Checked=='NO'?'YES':'NO') +'</button>';
                }}
            ]
        })}
    tab();
    function check(id) {
//    	if(confirm("Are you sure it's checked?"))
//        {
        	$.ajax({
                type:"post",
                url:"process.php",
                data:'act=homepage_check&id='+id,
                success: function(res){
                	console.log(res);
                    if(res){
//                        alert('Success');
                    }
                    else{
                        alert('Error');
                    }
                }
            });
//        }
    }

    $('.search').bind("click",function(){
        $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
        tab();
    })
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>