<?php /* Smarty version 2.6.26, created on 2018-01-18 18:18:10
         compiled from b_tools_aff_info_select_search.html */ ?>
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
        background: lightgray;
        border-radius: 5px;
        padding: 4px 10px;
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


</style>
<div class="ch-container" style="margin-top: 20px;">
    <div class="row">
        <div id="content" class="col-lg-12 col-sm-12">
            <div class="box col-md-12" style="padding-left:0;padding-right:0;">
                <div class="panel panel-default">
                    <div class="panel-heading" style="text-align: center"><h1>Network Crawl Info select Show</h1></div>
                    <div class="panel-body">
                        <form id="form_content_search">
                            <div class="row">
                                <div class=" form-inline">
                                    <div class="col-lg-12 ">
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;Network
                                            <input id="NetworkID" name="NetworkName" class="form-control" style="width:264px">
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;Type
                                            <select name="Type" class="form-control" style="width:120px">
                                                <option value="">All</option>
                                                <?php $_from = $this->_tpl_vars['crawlMethods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['lc']):
?>
                                                <option value="<?php echo $this->_tpl_vars['lc']; ?>
"><?php echo $this->_tpl_vars['lc']; ?>
</option>
                                                <?php endforeach; endif; unset($_from); ?>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;DataSourceType
                                            <select name="DataSourceType" class="form-control" style="width:70px">
                                                <option value="">All</option>
                                                <option value="API">API</option>
                                                <option value="Page">Page</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;UseStatus
                                            <select name="useStatus" class="form-control" style="width:70px">
                                                <option value=0>All</option>
                                                <option value=-1>NO</option>
                                                <option value=1>YES</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-left: 10px;">
                                            &nbsp;keyword
                                            <input name="keyword" class="form-control" style="width:80px">
                                        </div>
                                        <div class="form-group" style="margin-left: 30px;">
                                            <a href="javascript:void(0);" onclick="displaySearch()" style="padding: 7px 15px;background: #ddd;color: #111;border-radius:4px;text-decoration: none">Search</a>
                                        </div>
                                        <div class="form-group" style="margin-right: 30px; float: right">
                                            <a class="btn btn-info" href="<?php echo @BASE_URL; ?>
/b_tools_aff_info_select_oversee.php"> Oversee </a>
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

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-top: 100px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel" style="text-align: center"></h2>
            </div>
            <div id="preUpdate"  class="modal-body"></div>
            <div class="modal-footer" style="text-align: center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitBtn"></button>
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
                    var html_tip = '<ul class="dropdown-menu" style="margin-left: 85px;margin-top: -1px;width: 265px;ov">';
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
        $('#NetworkID').parent().find('ul').remove('');
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

    /*********************************************************
     * 修改及添加操作的入口，根据不同option的type值执行不同的操作
     * @param Id
     * @param optionType 1：打开更新弹窗
     *                   2：提交更新操作
     *                   3：打开添加弹窗
     *                   4：提交添加操作
     *********************************************************/
    function displayUpdate(Id,optionType) {

        if (!parseInt(Id)){
            return false;
        }

        switch (optionType) {
            case 1:
                var divStr = '<form id="aff_info_id_'+ Id +'" style="text-align: center" class="form-horizontal">';
                var data = {
                    "table": 1,
                    "params": JSON.stringify({'ID' : Id})
                };
                var result = doAjax(data);
                result  =JSON.parse(result);
                var data = result.data[0];
                for (item in data){
                    if (item == 'ID' || item == 'AddTime'){
                        continue;
                    }
                    divStr += '<div class="form-group"><label for="'+ item +'" class="col-sm-3 control-label">'+ item +'</label><div class="col-sm-7">' ;
                    if (item == 'NetworkID' || item == 'NetworkName'){
                        divStr += '<input type="text" class="form-control" name="'+ item +'" id="'+ item +'" value="'+ data[item] +'" readonly>';
                    }
                    if (item == 'Field' || item == 'BrField'){
                        divStr += '<input type="text" class="form-control" name="'+ item +'" id="'+ item +'" value="'+ data[item] +'">';
                    }
                    if (item == 'Type'){
                        divStr += '<select class="form-control" name="Type" id="Type">'+
                                      '<option value="Program" '+ (data[item] == 'Program' ? 'selected="selected"': '') +' >Program</option>'+
                                      '<option value="Link" '+ (data[item] == 'Link' ? 'selected="selected"': '') +'>Link</option>'+
                                      '<option value="Couponfeed" '+ (data[item] == 'Couponfeed' ? 'selected="selected"': '') +'>Couponfeed</option>'+
                                      '<option value="Product" '+ (data[item] == 'Product' ? 'selected="selected"': '') +'>Product</option>'+
                                      '<option value="Transaction" '+ (data[item] == 'Transaction' ? 'selected="selected"': '') +'>Transaction</option>'+
                                  '</select>';
                    }
                    if (item == 'DataSourceType'){
                        divStr += '<select class="form-control" name="DataSourceType" id="DataSourceType">'+
                                       '<option value="API" '+ (data[item] == 'API' ? 'selected="selected"': '') +' >API</option>'+
                                       '<option value="Page" '+ (data[item] == 'Page' ? 'selected="selected"': '') +'>Page</option>'+
                                  '</select>';
                    }
                    divStr += '</div><span class="col-sm-2"></span></div>';
                };
                divStr += '</form>';
                $('#myModalLabel').html('Update Select Info');
                $('#preUpdate').html(divStr);
                $('#submitBtn').html('<span onclick="displayUpdate('+ Id + ', 2' +')">Submit</span>');
                $('#myModal').modal('show');
                break;
            case 2:
                var params = {};
                var formArr = $('#aff_info_id_' + Id).serializeArray();
                $.each(formArr, function() {
                    params[this.name] = this.value;
                });
                params = JSON.stringify(params);

                console.log(params);
                var data = {
                    "updateId": Id,
                    "params": params
                };
                var result = doAjax(data);
                result = JSON.parse(result);

                if (result.code == 1){
                    $('#myModal').modal('hide');
                    displaySearch();
                }else {
                    alert(result.msg);
                }
                break;

            case 3:
            case 4:
            default :
                return false;
        }

    }

    function displayDetailFields(netWorkName, Type){
        $('#TypeFieldsDetailLabel').html(netWorkName + ' &nbsp;&nbsp;' + Type + ' &nbsp;&nbsp;Fields &nbsp;&nbsp;Detial');

        var htmlStr = '';
        var apiStr = '';
        var pageStr = '';
        var params = {'NetworkName': netWorkName, 'Type': Type}
        params = JSON.stringify(params);
        params = {'table': 2, 'params': params};

        var result = doAjax(params);
        if (result.indexOf('"data"') > 0){
            result = JSON.parse(result);
            result = result.data;

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
        }else {
            return false;
        }

        $('#TypeFieldsDetailContent').html(htmlStr);
        $('#TypeFieldsDetail').modal('show');
    }

    function doAjax(data){
        var result = '';
        $.ajax({
            type:"post",
            url:"b_tools_aff_info_select_search.php",
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
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            "ordering": false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "b_tools_aff_info_select_search.php",
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
                { "data": null,"bSortable": false,"title":'Type',"width": "10%","render":function(data,type,full,meta){
                    return '<button class="typeDetailBtn" title="Click to see all fields about '+ full.Type +' from '+ full.NetworkName +'" onclick="displayDetailFields(\''+ full.NetworkName + '\',\''+ full.Type +'\')">'
                    + full.Type + '</button>';

                }},
                { "data": null,"bSortable": false,"title":'Field',"width": "10%","render":function(data,type,full,meta){
                    return full.Field;
                }},
                { "data": null,"bSortable": false,"title":'Use Status',"width": "10%","render":function(data,type,full,meta){
                    if (full.BrField){
                        return '<span class="glyphicon glyphicon-ok" style="color: green; font-size: 20px">';
                    }else {
                        return '<span class="glyphicon glyphicon-remove" style="color: red; font-size: 20px">';
                    }
                }},
                { "data": null,"bSortable": false,"title":'Action',"width": "10%","render":function(data,type,full,meta){
                    return '<button class="btn btn-info" onclick="displayUpdate(' + full.ID + ', 1' + ')"> update </button>';
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