<?php /* Smarty version 2.6.26, created on 2017-11-19 19:07:01
         compiled from b_content_check_log.html */ ?>
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
                        <a href="javascript:void(0)" style="text-decoration:none;">Content Check Log</a>
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
    function tab(){
        $('#example').dataTable({
            "bServerSide": true,
            "bProcessing": true,
	        "scrollX": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
	        "ordering": false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_content_check_log.php",
                "type":'POST',
                "data": {
                    "table":1
                }
            },
            columns: [
                { "data": null,"title":'ContentFeedId',"width": "10%","render":function(data, type, full, meta){
                    return full.ContentFeedId;
                }},
	            { "data": null,"bSortable": false,"title":'AffId',"render":function(data,type,full,meta){
                	return full.AffId;
                }},
	            { "data": null,"bSortable": false,"title":'AffUrl',"render":function(data,type,full,meta){
                	return full.AffUrl;
                }},
                { "data": null,"bSortable": false,"title":'Status',"width": "10%","render":function(data,type,full,meta){
                	return full.Status;
                }},
	            { "data": null,"bSortable": false,"title":'StatusDesc',"width": "10%","render":function(data,type,full,meta){
                	return full.StatusDesc;
                }},
	            { "data": null,"bSortable": false,"title":'Correct',"width": "10%","render":function(data,type,full,meta){
	            	return full.Correct;
                }},
	            { "data": null,"bSortable": false,"title":'AddTime',"render":function(data,type,full,meta){
                	return full.AddTime;
                }}
            ]
        })}
    tab();
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>