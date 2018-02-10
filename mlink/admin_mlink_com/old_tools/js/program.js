function addColor(obj) {
    originColor = $(obj).attr("bgColor");
    $(obj).attr("bgColor", "#FFFFBB");
    
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

function expan(id) {
	$("#s_"+id).attr("style", "display:none;");
	$("#l_"+id).attr("style", "display:'';");
}

function pickUp(id) {
	$("#l_"+id).attr("style", "display:none;");
	$("#s_"+id).attr("style", "display:'';");
	
}

function get_ssl_rd_url(url){
	return "https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent(url);
}

function OpenURL(url){
	var url = $("#"+url).val();
	url = url.replace(/(^\s*)|(\s*$)/g, '');
	if (url != '') {
		url = get_ssl_rd_url(url);
		window.open(url);
	}
}

function optionMove(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
		var go_out = false;
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
    		for(var j=0; j < tbox.options.length; j++) {
    			if (tbox.options[j].value == fbox.options[i].value) {
    				go_out = true;
    				break;
    			}
    		}
    		if(go_out) {
    			fbox.options[i].value = "";
    			fbox.options[i].text = "";
    			continue;
    		}
    		
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			tbox.options[tbox.options.length - 1].selected = true;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function BumpUp(box)
{ 
	for(var i=0; i<box.options.length; i++)
	{
		if(box.options[i].value == "")
		{
			for(var j=i;j<box.options.length-1;j++)
			{
				box.options[j].value = box.options[j+1].value;
				box.options[j].text = box.options[j+1].text;
			}
			var ln = i;
			break;
		}
	}
	
	if(ln < box.options.length)
	{ 
		box.options.length -= 1;
		BumpUp(box);
	}
}

function SortD(box)
{
	var temp_opts = new Array();
	var temp = new Object();
	for(var i=0; i<box.options.length; i++)
	{
		temp_opts[i] = box.options[i];
	}

	for(var x=0; x<temp_opts.length-1; x++)
	{
		for(var y=(x+1); y<temp_opts.length; y++)
		{
			if(temp_opts[x].text > temp_opts[y].text)
			{
				temp = temp_opts[x].text;
				temp_opts[x].text = temp_opts[y].text;
				temp_opts[y].text = temp;
				temp = temp_opts[x].value;
				temp_opts[x].value = temp_opts[y].value;
				temp_opts[y].value = temp;
			}
		}
	}

	for(var i=0; i<box.options.length; i++)
	{
		box.options[i].value = temp_opts[i].value;
		box.options[i].text = temp_opts[i].text;
	}
}

function isEnter(ev)
{
	var code = ev.which? ev.which : ev.keyCode;
	if(code == 13) return true;
	return false;
}

function optionMoveAll(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
		var go_out = false;
		if(fbox.options[i].value != "")
		{ 
			for(var j=0; j < tbox.options.length; j++) {
    			if (tbox.options[j].value == fbox.options[i].value) {
    				go_out = true;
    				break;
    			}
    		}
			if(go_out) {
    			fbox.options[i].value = "";
    			fbox.options[i].text = "";
    			continue;
    		}
    		
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			tbox.options[tbox.options.length - 1].selected = true;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
		} 
	} 
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function defaultSel() {
	$("#CountryRight").children().each(function(){$(this).attr("selected","selected")});
}

function formSubmit(formid) {
	defaultSel();
	$("#"+formid).submit();
}

function searchProgramSearch() {
	$("#programsearch").unautocomplete();
	$("#programsearch").autocomplete("../front/program_notice_list.php?action=ajaxsearchprogram", {
		width: 200,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#programsearch").result(function(event, data, formatted) {
		if (data) {
			$("#programsearch").attr("value", data[0]);
			$("#programid").attr("value", data[1]);
		}
	});
}

function setNoticeDone() {
	$.ajax({
		type: "POST",
		url: '../front/program_notice_list.php',
		data: "action=ajaxsetdone&ID="+arguments[0],
		success: function(msg){
			if (msg == 'success') {
				alert('Operate Successfully');
				window.location.reload();
			} else {
				alert('Operate failed');
			}
		}
	});
}

function setSelectedNoticeDone(){
	var notice_id = "";
	$.each($("input[name^='n_id_']"),function(i,n){		
		if($(this).attr('checked')){
			if(notice_id == ""){
				notice_id += $(this).val();
			}else{
				notice_id += "," + $(this).val();
			}
		}
	});
	
	if(notice_id == ""){
		alert("Please select one notice at least.");
		return false;
	}else{
		$.ajax({
			type: "POST",
			url: '../front/program_notice_list.php',
			data: "action=ajaxsetselecteddone&ID="+notice_id,
			success: function(msg){
				if (msg == 'success') {
					alert('Operate Successfully');
					window.location.reload();
				} else {
					alert('Operate failed');
				}
			}
		});
	}
}

function selectAllNotice(){	
	if($("#sel_all").attr("checked") == true){
		$("input[name^='n_id_']").attr("checked","checked");
	}else{		
		$("input[name^='n_id_']").attr("checked","");
	}
}

function formatResult(row) {
	return row[0];
}
function formatItem(row) {
	return row[0];
}

$().ready(function() {
	$("#Group").autocomplete('../front/program_search.php?ajaxTag=searchGroup', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true
	});
});
