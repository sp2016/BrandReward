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
	$('#form_tran_search').submit();
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
	$('#form_tran_search').submit();
});