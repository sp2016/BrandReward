
$().ready(function() {
	function log(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
		return row[1] + "(" + row[0] + ")" + " "+(row[2]?row[2]:"");
	}
	function formatResult(row) {
		return row[1];
	}
	
	$("#tag_list_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=tmcTagName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: sel_mer
	});
	
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
	
	$("#merchant_list_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=mmcMerName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: mer_sel_mer
	});
	changeType();
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
	
});

function changeType(e){
	var type = $("#type").val();
	if(type == "TAG"){
		$("#span_tag").show();
		$("#span_merchant").hide();
	}
	if(type == "MERCHANT"){
		$("#span_tag").hide();
		$("#span_merchant").show();
	}
}


function doneTask(mid){
	if(!confirm("Have you realy checked all reference and alerts?")){
		return false;
	}
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=donetask&mid=" + mid + "&site=" + site,
		success: function (msg) {
			if(msg == "success"){
				alert("Operate Success");
				location.reload();
			}else{
				alert(msg);
			}
		},
		error: function (){
			alert("Operate Error!");
		}
	});	
	return true;
}
function recheckTask(mid){
	if(!confirm("Re-Check all alerts for this merchant?")){
		return false;
	}
	var site = $("#site").val();

	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=rechecktask&mid=" + mid + "&site=" + site,
		success: function (msg) {
		if(msg == "success"){
			alert("Operate Success");
			location.reload();
		}else{
			alert(msg);
		}
	},
	error: function (){
		alert("Operate Error!");
	}
	});	
	return true;
}


function showHideDiv(mid, merName){
	var speed = 500;

	  var offset = $("#pos_" + mid).offset();
	
      var pos = offset.top;
      
    	var scrollHeight = document.documentElement.clientHeight;
//    	if(pos + 550 > scrollHeight){
//        	if(pos > 550){
//    			pos = 	pos - 280 - 20;
//        	}
//       }
    	
      $("#divPop").css({ top: pos + "px", left: offset.left + 30});
      var tips =  $("#tips_hide_" + mid).text();
     
      $("#tips_text").val(tips);
      $("#merchant_name_tips").text(merName);
      
      $("#divPop").show();
      return false;

}


function hidDiv(id){
	$("#" + id).hide();
}

function editTaskUpdateCycle(mid){
	$("#div_editTaskUpdateCycle_" + mid).show();
	$("#link_TaskUpdateCycle_" + mid).hide();
}
function cancelTaskUpdateCycle(mid){
	$("#div_editTaskUpdateCycle_" + mid).hide();
	$("#link_TaskUpdateCycle_" + mid).show();
}
function editMinPromotionCount(mid){
	$("#div_editMinPromotionCount_" + mid).show();
	$("#link_MinPromotionCount_" + mid).hide();
}
function cancelMinPromotionCount(mid){
	$("#div_editMinPromotionCount_" + mid).hide();
	$("#link_MinPromotionCount_" + mid).show();
}

function saveTaskUpdateCycle(mid){
	var inputValue= $("#edit_TaskUpdateCycle_" + mid).val();
	var inputNum = parseInt(inputValue);
	if(isNaN(inputNum)){
		alert("Please input int.");
		return false;
	}
	if(inputNum <= 0 || inputNum > 60){
		alert("Plese input an int (1-60)");
		return false;
	}

	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=updatetaskupdatecycle&mid=" + mid + "&site=" + site + "&value=" + inputNum,
		success: function (msg) {
			if(msg == "success"){
				alert("TaskUpdateCycle Saved!");
				$("#span_TaskUpdateCycle_" + mid).text(inputNum + " Days");
				$("#div_editTaskUpdateCycle_" + mid).hide();
				$("#link_TaskUpdateCycle_" + mid).show();

			}else{
				alert(msg);
			}
		},
		error: function (){
			alert("Operate Error!");
		}
	});	
}

function saveMinPromotionCount(mid){
	var inputValue= $("#edit_MinPromotionCount_" + mid).val();
	var inputNum = parseInt(inputValue);
	if(isNaN(inputNum)){
		alert("Please input int.");
		return false;
	}
	if(inputNum <= 0 || inputNum > 60){
		alert("Plese input an int (1-60)");
		return false;
	}
	
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=updateminpromotioncount&mid=" + mid + "&site=" + site + "&value=" + inputNum,
		success: function (msg) {
		if(msg == "success"){
			alert("MinPromotionCount Saved!");
			$("#span_MinPromotionCount_" + mid).text(inputNum);
			$("#div_editMinPromotionCount_" + mid).hide();
			$("#link_MinPromotionCount_" + mid).show();
			
		}else{
			alert(msg);
		}
	},
	error: function (){
		alert("Operate Error!");
	}
	});	
}


function showHistory(mid, action){
	
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?mid=" + mid + "&site=" + site + "&action=" + action,
		success: function (msg) {
		if(msg != "error"){
			  var offset = $("#" + action + "_" + mid).offset();
		      var pos = offset.top;
		      
		      $("#divPop1").css({ top: pos + "px", left: offset.left + 30});
		     
		      $("#table_history").html(msg);
		      $("#divPop1").show();
		}else{
			alert("Get history error!");
		}
	},
	error: function (){
		alert("Operate Error!");
	}
	});	
}


function changeAlert(){
	if($("#alert").attr("checked") == true){
		$("#alerthide").val("YES");
	}else{
		$("#alerthide").val("NO");
	}
}


function clearAutoDiv(){
//	$(".ac_results").remove();
	$("#merchant").val("");
	$("#merchant_list_search").val("");
	$("#form1").submit();
}

function clearSpan(){
	$("#merchant_id_span").html("");
}