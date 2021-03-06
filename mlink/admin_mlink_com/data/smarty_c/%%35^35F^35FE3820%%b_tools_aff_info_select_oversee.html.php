<?php /* Smarty version 2.6.26, created on 2018-01-18 18:20:31
         compiled from b_tools_aff_info_select_oversee.html */ ?>
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

    #example{
        font-size: 16px;
    }
    #example td{
        padding: 6px;
    }
    .typeDetailBtn{
        border: 0;
        background: #ffffff;
        font-size: 18px;
        padding: 5px 10px;
    }
    #TypeFieldsDetailContent h4{
        padding: 20px 0 6px 0;
        border-bottom: 1px solid lightgrey;
    }

    #TypeFieldsDetailContent li{
        width: 30%;
        background: #f7f7f7;
        float: left;
        display: block;
        margin: 4px 1%;
        padding: 5px 1.5%;
        border-radius: 6%;
        font-size: 14px;
    }
    #TypeFieldsDetailContent li span{
        display: block;
        float: right;
        width: 10%;
        margin-left: 4%;
    }
    #TypeFieldsDetailContent li div{
        display: block;
        float: left;
        overflow: hidden;
        width: 86%;
    }

    #example span{
        padding-left: 20px;
    }

</style>
<div class="ch-container" style="margin-top: 20px;">
    <div class="row">
        <div id="content" class="col-lg-12 col-sm-12">
            <div class="box col-md-12" style="padding-left:0;padding-right:0;">
                <div class="panel panel-default">
                    <div class="panel-heading" style="text-align: center"><h1>Network Crawl Info select Show</h1></div>
                    <div class="panel-body" style="padding: 12px 15px">
                        <form id="form_content_search">
                            <div class="row">
                                <div class=" form-inline">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;<label for="NetworkID"><b>Network</b></label>
                                            <input id="NetworkID" name="NetworkName" class="form-control" style="width:264px">
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;<label for="HaveKeyword"><b>HaveKeyword</b></label>
                                            <input id="HaveKeyword" name="HaveKeyword" class="form-control" style="width:100px">
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;<label for="NoKeyword"><b>NoKeyword</b></label>
                                            <input id="NoKeyword" name="NoKeyword" class="form-control" style="width:100px">
                                        </div>
                                        <div class="form-group" style="margin-left: 30px;">
                                            <a href="javascript:void(0);" onclick="displaySearch()" style="padding: 7px 15px;background: #ddd;color: #111;border-radius:4px;text-decoration: none">Search</a>
                                        </div>
                                        <div class="form-group" style="margin-right: 30px; float: right">
                                            <a class="btn btn-info" href="<?php echo @BASE_URL; ?>
/b_tools_aff_info_select_search.php"> DetailSearch </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
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
    </div>
</div>

<div class="modal fade" id="TypeFieldsDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-top: 50px;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="TypeFieldsDetailLabel" style="text-align: center;margin: 5px auto"></h2>
            </div>
            <div id="TypeFieldsDetailContent"></div>
            <div class="modal-footer" style="border: 0px">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function(){
        $('#NetworkID').keyup(function(){
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
                    var html_tip = '<ul class="dropdown-menu" style="margin-left: 85px;margin-top: -1px;width: 265px;">';
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
        $(obj).css('display','block');
        $(obj).find('a').click(function(){
            $(obj).prev().val($(this).text());
            $(obj).remove();
        });
    }

    function displaySearch(){
        $('#NetworkID').parent().find('ul').remove();
        var params = {};
        var formArr = $('#form_content_search').serializeArray();
        $.each(formArr, function() {
            params[this.name] = this.value;
        });
        params = JSON.stringify(params);
        console.log(params);
        $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
        tab(params);
    }

    function displayDetailFields(netWorkId, netWorkName, Type){
        $('#TypeFieldsDetailLabel').html(netWorkName + ' &nbsp;&nbsp;' + Type + ' &nbsp;&nbsp;Fields &nbsp;&nbsp;Detail');

        var htmlStr = '';
        var apiStr = '';
        var pageStr = '';
        var params = {'NetworkID': netWorkId, 'Type': Type}
        params = JSON.stringify(params);
        params = {'table': 2, 'params': params};

        var result = doAjax(params);
        result = JSON.parse(result);

        for (var item in result){
            var data = result[item];
            var useStstus = data.BrField ? '<span class="glyphicon glyphicon-ok" style="color: green; font-size: 18px">' : '<span style="color: red; font-size: 18px">';
            var title = data.Field.length > 28 ? ' title="'+ data.Field +'"' : '';

            if (data.DataSourceType == 'API'){
                apiStr += '<li'+ title +'><div>'+ data.Field +'</div>'+ useStstus +'</li>';
            }else {
                pageStr += '<li'+ title +'><div>'+ data.Field +'</div>'+ useStstus +'</li>';
            }
        }

        htmlStr +=  '<div style="width: 92%; margin: auto 4%">' +
            '   <div id="fieldsFromAPI" class="row">' +
            '       <h4>The fields from API</h4>'+
            '       <ul>'+apiStr+'</ul>' +
            '   </div>' +
            '   <div id="fieldsFromPage" class="row">' +
            '       <h4>The fields from Page</h4>'+
            '       <ul>'+pageStr+'</ul>' +
            '   </div>' +
            '</div>';


        $('#TypeFieldsDetailContent').html(htmlStr);
        $('#TypeFieldsDetail').modal('show');
    }

    function doAjax(data){
        var result = '';
        $.ajax({
            type:"post",
            url:"b_tools_aff_info_select_oversee.php",
            data:data,
            async:false,
            success: function(res){
                result = res;
            }
        });
        return result;
    }

    function tab(searchData){
        $('#example').dataTable({
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 50,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            "ordering": false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_tools_aff_info_select_oversee.php",
                "type":'POST',
                "data": {
                    "table": 1,
                    "params": searchData
                }
            },
            columns: [
                { "data": null,"bSortable": false,"title":'Network',"width": "10%","render":function(data,type,full,meta){
                    return full.NetworkName + ' (' + full.NetworkID+ ')';
                }},
                { "data": null,"bSortable": false,"title":'Program',"width": "10%","render":function(data,type,full,meta){
                    if (full.Program != undefined){
                        return '<button class="typeDetailBtn" title="Click to see all fields about Program from ' + full.NetworkName + '" onclick="displayDetailFields('+full.NetworkID +",'"+ full.NetworkName + '\',\'Program\')">Detail <span class="glyphicon glyphicon-ok" style="color: green;"> </button>';
                    }else {
                        return '<div class="typeDetailBtn">Detail <span class="glyphicon glyphicon-remove" style="color: red;"> </span>';
                    }
                }},
                { "data": null,"bSortable": false,"title":'Link',"width": "10%","render":function(data,type,full,meta){
                    if (full.Link != undefined){
                        return '<button class="typeDetailBtn" title="Click to see all fields about Link from ' + full.NetworkName + '" onclick="displayDetailFields('+full.NetworkID +",'"+ full.NetworkName + '\',\'Link\')">Detail <span class="glyphicon glyphicon-ok" style="color: green;"> </button>';
                    }else {
                        return '<div class="typeDetailBtn">Detail <span class="glyphicon glyphicon-remove" style="color: red;"> </span>';
                    }
                }},
                { "data": null,"bSortable": false,"title":'Promotion',"width": "10%","render":function(data,type,full,meta){
                    if (full.Couponfeed != undefined){
                        return '<button class="typeDetailBtn" title="Click to see all fields about Couponfeed from ' + full.NetworkName + '" onclick="displayDetailFields('+full.NetworkID +",'"+ full.NetworkName + '\',\'Couponfeed\')">Detail <span class="glyphicon glyphicon-ok" style="color: green;"> </button>';
                    }else {
                        return '<div class="typeDetailBtn">Detail <span class="glyphicon glyphicon-remove" style="color: red;"> </span>';
                    }
                }},
                { "data": null,"bSortable": false,"title":'Product',"width": "10%","render":function(data,type,full,meta){
                    if (full.Product != undefined){
                        return '<button class="typeDetailBtn" title="Click to see all fields about Product from ' + full.NetworkName + '" onclick="displayDetailFields('+full.NetworkID +",'"+ full.NetworkName + '\',\'Product\')">Detail <span class="glyphicon glyphicon-ok" style="color: green;"> </button>';
                    }else {
                        return '<div class="typeDetailBtn">Detail <span class="glyphicon glyphicon-remove" style="color: red;"> </span>';
                    }
                }},
                { "data": null,"bSortable": false,"title":'Transaction',"width": "10%","render":function(data,type,full,meta){
                    if (full.Transaction != undefined){
                        return '<button class="typeDetailBtn" title="Click to see all fields about Transaction from ' + full.NetworkName + '" onclick="displayDetailFields('+full.NetworkID +",'"+ full.NetworkName + '\',\'Transaction\')">Detail <span class="glyphicon glyphicon-ok" style="color: green;"> </button>';
                    }else {
                        return '<div class="typeDetailBtn">Detail <span class="glyphicon glyphicon-remove" style="color: red;"> </span>';
                    }
                }}
            ]
        });
    }
    tab(null);

</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>