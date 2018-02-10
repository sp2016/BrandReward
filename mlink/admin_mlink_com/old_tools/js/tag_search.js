
$().ready(function() {
	autoMerchantComplete();
	autoTagComplete();
});

function log(event, data, formatted) {
	$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
}

function formatItem(row) {
	return row[1] + "(" + row[0] + ")" + " "+(row[2]?row[2]:"");
}
function formatResult(row) {
	return row[1];
}

function autoMerchantComplete() {
	$("#merchant_list_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=mmcMerName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: mer_sel_mer
	});
}

function mer_sel_mer(){
	$.ajax({type: "POST",
		url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantIdByName&q="+encodeURIComponent($("#merchant_list_search").val()),			
		success: function(msg){				
			$("#merchant").val(msg);
			$("#merchant_id_span").html("MID:" + msg);
			//$("form:first").submit();
		}					   
	});	
}

function autoTagComplete() {
	$("#tag_list_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=tmcTagName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: false,
		addfun: sel_mer
	});
}

function sel_mer(){
	$.ajax({type: "POST",
		url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getTagIdByName&q="+encodeURIComponent($("#tag_list_search").val()),			
		success: function(msg){				
			$("#tag").val(msg);
			$("#tag_id_span").html("TID:" + msg);
			//$("form:first").submit();
		}					   
	});	
}
