<?php /* Smarty version 2.6.26, created on 2017-12-07 03:17:48
         compiled from b_home.html */ ?>
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
<div class="ch-container">
    <div class="row">
        <div id="content" class="col-lg-12">
            <!-- content starts -->
            <div>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Home</a>
                    </li>
                    <li>
                        <a href="#">Dashboard</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="box-content" style="text-align: center;">
                <div id="reportrange" style="margin-left:35%;background: #fff;  padding: 5px 10px; border: 1px solid #ccc;width:300px;float: left;">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                    <span></span> <b class="caret"></b>
                </div>
                <div style="float: left;margin-left:10px;">
                    <label class="control-label"for="selectError">Data Type&nbsp;</label>
                    <select id="datatype" data-rel="chosen" class="chosen" style="width: 100px;">
                        <option value="1">Publisher</option>
                        <option value="2">All</option>
                    </select>
                    &nbsp;&nbsp;
                    <input  type="button" class="btn search" style="background-color: #627491;color:white;" value="Search">
                </div>
            </div>
            <input type="hidden" name="startDate" id="startDate">
            <input type="hidden" name="endDate" id="endDate">
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="6 new members.">
                        <div>Total Sales</div>
                        <div id="sumsales"></div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="4 new pro members.">
                        <div>Total Commission</div>
                        <div id="sumcommission"></div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="$34 new sales.">
                        <div>Total Publisher</div>
                        <div id="sumpublisher"></div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="$34 new sales.">
                        <div>Total Clicks -- Real Clicks<span style="margin-left:3px;" class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Total Clicks - Total Robot"></span></div>
                        <div id="sumclick"></div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="$34 new sales.">
                        <div>Total Robot</div>
                        <div id="sumrob"></div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <a data-toggle="tooltip" title="" class="well top-block" href="#" data-original-title="$34 new sales.">
                        <div>Total May Be Robot</div>
                        <div id="sumrobp"></div>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="box col-md-12" style="min-height: 550px;">
                    <div class="box-inner homepage-box">
                        <div class="box-header well">
                            <h2><i class="glyphicon glyphicon-th"></i> Tabs</h2>
                        </div>
                        <div class="box-content">
                            <ul class="nav nav-tabs" id="myTab">
                                <li class="active"><a href="#info">Chart</a></li>
                                <li><a href="#custom">Detail</a></li>
                            </ul>
                            <div id="myTabContent" class="tab-content">
                                <div class="tab-pane active" id="info">
                                    <div id="sc" style="height:300px;margin-top:50px;width: 100%;"></div>
                                    <div id="rob" style="height:300px;margin-top:30px;width: 100%;"></div>
                                    <div id="main" style="height:300px;margin-top: 30px;width:100%;"></div>

                                    <div id="toppub" style="height:300px;margin-top: 30px;margin-bottom: 10px;width:100%;"></div>
                                </div>
                                <div class="tab-pane" id="custom">
                                    <div class="box-inner" style="margin-top: 30px;" >
                                        <div class="box-header well" data-original-title="">
                                            Daily Detail
                                            <span style="float: right;"><a href="javascript:void(0)" class="download" data-id="sc">DownLoad</a></span>
                                        </div>
                                        <div id="tbzone">
                                            <table id="example" class="ui celled table" cellspacing="0" width="100%">
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-inner" style="margin-top: 30px;">
                                        <div class="box-header well" data-original-title="">
                                            Publisher Detail
                                            <span style="float: right;"><a href="javascript:void(0)" class="download" data-id="pub">DownLoad</a></span>
                                        </div>
                                        <div id="tbzone1">
                                            <table id="example1" class="ui celled table" cellspacing="0" width="100%">
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-inner" style="margin-top: 30px;">
                                        <div class="box-header well" data-original-title="">
                                            None Advertiser
                                            <span style="float: right;"><a href="javascript:void(0)" class="download" data-id="na">DownLoad</a></span>
                                        </div>
                                        <div id="tbzone2">
                                            <table id="example2" class="ui celled table" cellspacing="0" width="100%">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="box col-md-12">-->
                    <!--<div class="box-inner">-->
                        <!--<div class="tabs">-->
                            <!--<ul>-->
                                <!--<li><a href="#tabs-1" title="">Tab 1</a></li>-->
                                <!--<li><a href="#tabs-2" title="">Tab 2</a></li>-->
                                <!--<li><a href="#tabs-3" title="">Tab 3</a></li>-->
                            <!--</ul>-->

                            <!--<div id="tabs_container">-->
                                <!--<div id="tabs-1">-->
                                    <!--<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus.</p>-->
                                    <!--<p>Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>-->
                                <!--</div>-->

                                <!--<div id="tabs-2">-->
                                    <!--<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla<a href="http://www.dowebok.com/"></a>, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor.</p>-->
                                <!--</div>-->

                                <!--<div id="tabs-3">-->
                                    <!--<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem.</p>-->
                                    <!--<p>Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales.</p>-->
                                <!--</div>-->
                            <!--</div>-->
                        <!--</div>-->
                        <!--</div>-->
                    <!--</div>-->
            </div>
            <!-- content ends -->
        </div><!--/#content.col-md-0-->
    </div><!--/fluid-row-->
</div>
<script type="text/javascript">
    $(".chosen").chosen();
    $('.download').on('click',function(){
        var startDate = $("#startDate").val();
        var endDate = $("#endDate").val();
        var datatype = $('#datatype').val();
        var type = $(this).data('id');
        window.location.href = 'index.php?&type='+type+'&act=download&startDate='+startDate+'&endDate='+endDate+'&datatype='+datatype;
    })
    var start = moment().subtract(30, 'days');
    var end = moment().subtract(1, 'days');
    $("#startDate").val(start.format("YYYY-MM-DD"));
    $("#endDate").val(end.format("YYYY-MM-DD"));
    $('#myTab a:first').tab('show');
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    function tab(){
        $('#example').DataTable({
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "index.php",
                "type":'POST',
                data:{"startDate":$("#startDate").val(),"endDate":$("#endDate").val(),"table":"1","datatype":$('#datatype').val(),'type':'sc'},
            },
            "aaSorting":[
                [1, "desc"],
            ],
            columns:[
                { "data": "date","sClass":"t1","title":'Date',"bSortable": false },
                { "data": "sales","title":"Sales"},
                { "data": "commission","title":"Commission","bSortable": false},
                { "data": "order","title":"Order","bSortable": false},
                { "data": "clicks","title":"Clicks","bSortable": false},
                { "data": "rob","title":"Robot","bSortable": false},
                { "data": "robp","title":"May Be Robot","bSortable": false},
                { "data": "epc","title":"EPC","bSortable": false},
                { "data": "rate","title":'<a href="javascript:void(0)" title="(commission/sales*100)">Commission.Rate</a>',"bSortable": false},
            ]
        })
        $('#example1').DataTable({
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "index.php",
                "type":'POST',
                data:{"startDate":$("#startDate").val(),"endDate":$("#endDate").val(),"table":"1","datatype":$('#datatype').val(),'type':'pub'},
            },
            "aaSorting":[
                [1, "desc"],
            ],
            columns:[
                { "data": "name","sClass":"t1","title":'Publisher',"bSortable": false },
                { "data": "sales","title":"Sales"},
                { "data": "commission","title":"Commission","bSortable": false},
                { "data": "order","title":"Order","bSortable": false},
                { "data": "clicks","title":"Clicks","bSortable": false},
                { "data": "rob","title":"Robot","bSortable": false},
                { "data": "robp","title":"May Be Robot","bSortable": false},
                { "data": "epc","title":"EPC","bSortable": false},
                { "data": "rate","title":'<a href="javascript:void(0)" title="(commission/sales*100)">Commission.Rate</a>',"bSortable": false},
            ]
        })
        $('#example2').DataTable({
            "bServerSide": true,
            "bProcessing": true,
            "iDisplayLength": 20,
            "bPaginage":true,
            "aLengthMenu": [10, 20, 50, 100],
            'bFilter': false,
            'pagingType':'full_numbers',
            "ajax": {
                "url": "index.php",
                "type":'POST',
                data:{"startDate":$("#startDate").val(),"endDate":$("#endDate").val(),"table":"1","datatype":$('#datatype').val(),'type':'na'},
            },
            "aaSorting":[
                [1, "desc"],
            ],
            columns:[
                { "data": "name","sClass":"t1","title":'Advertiser',"bSortable": false },
                { "data": "clicks","title":"Clicks","bSortable": false},

            ]
        })
    }
    function changeDate(start, end) {
        $("#startDate").val(start.format("YYYY-MM-DD"));
        $("#endDate").val(end.format("YYYY-MM-DD"));
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    $('#reportrange').daterangepicker({
        "alwaysShowCalendars": true,
        "startDate": start,
        "endDate": end,
        "maxDate": moment().subtract(1, 'days'),
        "ranges": {
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(7, 'days'), moment().subtract(1, 'days')],
            'Last 30 Days': [moment().subtract(30, 'days'), moment().subtract(1, 'days')],
            'This Week': [moment().startOf('week'), moment().subtract(1, 'days')],
            'Last Week': [moment().startOf('week').subtract(7, 'days'), moment().startOf('week').subtract(1, 'days')],
            'This Month': [moment().startOf('month'), moment().subtract(1, 'days')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    },changeDate);
    changeDate(start, end);
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: 'Top 10 Advertiser',
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['Advertiser']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            show: true,
            feature: {
                magicType: {type: ['line', 'bar']},
                saveAsImage: {}
            }
        }
    };
    myChart.setOption(option);

    var toppub = echarts.init(document.getElementById('toppub'));
    option5 = {
        title: {
            text: 'Top 10 Publisher',
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['Advertiser']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            show: true,
            feature: {
                magicType: {type: ['line', 'bar']},
                saveAsImage: {}
            }
        }
    };
    toppub.setOption(option5);

    var sc = echarts.init(document.getElementById('sc'));
    option1 = {
        title: {
            text: 'Sales & Commission',
        },
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
            data:['Sales','Commission']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            show : true,
            feature : {
                mark : {show: true},
                dataView : {show: true, readOnly: false},
                magicType: {show: true, type: ['line', 'bar']},
                restore : {show: true},
                saveAsImage : {show: true}
            }
        }
    };
    sc.setOption(option1);

    var rob = echarts.init(document.getElementById('rob'));
    option3 = {
        title: {
            text: 'Click & Robot',
        },
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
            data:['Click','Robot','May Be Robot']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            show : true,
            feature : {
                mark : {show: true},
                dataView : {show: true, readOnly: false},
                magicType: {show: true, type: ['line', 'bar']},
                restore : {show: true},
                saveAsImage : {show: true}
            }
        }
    };
    function getchart(){
        $.ajax({
            type: "post",
            url: "index.php",
            data:{"startDate":$("#startDate").val(),"endDate":$("#endDate").val(),"changeMark":"1","datatype":$('#datatype').val()},
            async: false,
            success: function (res){
                var res = ($.parseJSON(res));
                var total = res.total;
                $.each(total,function(i,val){
                    $('#'+i).html(val);
                })
                myChart.setOption({
                    xAxis: {
                        type: 'value'
                    },
                    yAxis: {
                        type: 'category',
                        data:res.newname
                    },
                    series: [
                        {
                            name: 'Commission',
                            type: 'bar',
                            itemStyle:{
                                normal:{
                                    color:'#6E8097'
                                }
                            },
                            data:res.newrevenues,
                        }
                    ]
                });
                toppub.setOption({
                    xAxis: {
                        type: 'value'
                    },
                    yAxis: {
                        type: 'category',
                        data:res.pub_name
                    },
                    series: [
                        {
                            name: 'Commission',
                            type: 'bar',
                            label: {
                            },
                            itemStyle:{
                                normal:{
                                    color:'#6E8097'
                                }
                            },
                            data:res.pub_revenues,
                        }
                    ]
                });
                sc.setOption({
                    legend: {
                        data:['Sales','Commission'],
                        selected: {
                            'Sales': false,
                            'Commission': true
                        }
                    },
                    xAxis : [
                        {
                            type : 'category',
                            data:res.cday
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
                            axisLabel: {
                                formatter: '${value}'
                            }
                        }
                    ],
                    series : [
                        {
                            name:'Sales',
                            type:'bar',
                            data:res.Sales,
                            markLine : {
                                data : [
                                    {type :'average'}
                                ]
                            },
                            itemStyle:{
                                normal:{
                                    color:'#6E8097'
                                }
                            },
                        },
                        {
                            name:'Commission',
                            type:'bar',
                            data:res.Commission,
                            markLine : {
                                data : [
                                    {type :'average'}
                                ]
                            },
                            itemStyle:{
                                normal:{
                                    color:'#97BBCD'
                                }
                            },
                        }
                    ]
                });
                rob.setOption({
                    legend: {
                        data:['Click','Robot','May Be Robot']
                    },
                    xAxis : [
                        {
                            type : 'category',
                            data:res.cday
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        {
                            name:'Click',
                            type:'bar',
                            areaStyle: {normal: {}},
                            data:res.click,
                            itemStyle:{
                                normal:{
                                    color:'#6E8097'
                                }
                            },
                        },
                        {
                            name:'Robot',
                            type:'bar',
                            areaStyle: {normal: {}},
                            data:res.rob,
                            itemStyle:{
                                normal:{
                                    color:'#C6CED2'
                                }
                            },
                        },
                        {
                            name:'May Be Robot',
                            type:'bar',
                            areaStyle: {normal: {}},
                            data:res.robp,
                            itemStyle:{
                                normal:{
                                    color:'#48555D'
                                }
                            },
                        }
                    ]
                });
            }
        });
    }
    getchart();
    tab();
    rob.setOption(option3);
    $('.search').on('click',function(){
        $('#tbzone').html('<table id="example" class="ui celled table" cellspacing="0" width="100%"></table>');
        $('#tbzone1').html('<table id="example1" class="ui celled table" cellspacing="0" width="100%"></table>');
        $('#tbzone2').html('<table id="example2" class="ui celled table" cellspacing="0" width="100%"></table>');
        tab();
        getchart();
    })

</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "b_block_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>