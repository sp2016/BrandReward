$($('#form_tran_search input:button')[0]).click(function(){
	var mydate = new Date();
	var y = mydate.getFullYear();
	var m = parseInt(mydate.getMonth()) + 1;
	var d = mydate.getDate();
	if(m < 10)
	m = '0'+m;
	if(d < 10)
	d = '0'+d;

	var today = y+'-'+m+'-'+d;

	var mydate = new Date();
	mydate.setDate(mydate.getDate() - 7);
	var y = mydate.getFullYear();
	var m = parseInt(mydate.getMonth()) + 1;
	var d = mydate.getDate();
	if(m < 10)
	m = '0'+m;
	if(d < 10)
	d = '0'+d;

	var lastday = y+'-'+m+'-'+d;

	$('#tran_from').val(lastday);
	$('#tran_to').val(today);
});

$($('#form_tran_search input:button')[1]).click(function(){
	var mydate = new Date();
	var y = mydate.getFullYear();
	var m = parseInt(mydate.getMonth()) + 1;
	var d = mydate.getDate();
	if(m < 10)
	m = '0'+m;
	if(d < 10)
	d = '0'+d;

	var today = y+'-'+m+'-'+d;

	var mydate = new Date();
	mydate.setDate(mydate.getDate() - 30);
	var y = mydate.getFullYear();
	var m = parseInt(mydate.getMonth()) + 1;
	var d = mydate.getDate();
	if(m < 10)
	m = '0'+m;
	if(d < 10)
	d = '0'+d;

	var lastday = y+'-'+m+'-'+d;

	$('#tran_from').val(lastday);
	$('#tran_to').val(today);
});


//展开
$($('.open-close').click(function(){
    var id = $(this).attr('id');
    var list = $('.File_'+id);
    list.each(function(){
        if($(this).attr('state') == 'open'){
            $(this).css('display','none');
            $(this).attr('state','close');
        } else {
            $(this).css('display','');
            $(this).attr('state','open');
        }
    });
}));

//页面跳转
$($('.go').click(function(){
    var url = window.location.href;
    set_param('p',$('#size').val());
}));



function set_param(param,value){
    var query = location.search.substring(1);
    var p = new RegExp("(^|&"+param+")=[^&]*");
    if(p.test(query)){
        query = query.replace(p,"$1="+value);
        location.search = '?'+query;
    }else{
        if(query == ''){
            location.search = '?'+param+'='+value;
        }else{
            location.search = '?'+query+'&'+param+'='+value;
        }
    }
}

function load_tip(obj){
    $(obj).css('display','block');
    $(obj).find('a').click(function(){
        $(obj).prev().val($(this).text());
        $(obj).remove();
    });
}
