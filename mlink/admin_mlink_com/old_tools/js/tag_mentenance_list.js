function doneTask(tid){
	if(!confirm("Have you realy checked all reference and alerts?")){
		return false;
	}
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/tag_maintenance_list.php?action=donetask&tid=" + tid + "&site=" + site,
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
function recheckTask(tid){
	if(!confirm("Re-Check all alerts for this tag?")){
		return false;
	}
	var site = $("#site").val();

	$.ajax({
		type: "post",
		async: true,
		url: "/editor/tag_maintenance_list.php?action=rechecktask&tid=" + tid + "&site=" + site,
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


function showHideDiv(tid, merName){
	var speed = 500;

	  var offset = $("#pos_" + tid).offset();
	
      var pos = offset.top;
      
    	var scrollHeight = document.documentElement.clientHeight;
//    	if(pos + 550 > scrollHeight){
//        	if(pos > 550){
//    			pos = 	pos - 280 - 20;
//        	}
//       }
    	
      $("#divPop").css({ top: pos + "px", left: offset.left - 460});
      var tips =  $("#tips_hide_" + tid).html();
     
      $("#tips_text").html(tips);
      $("#tag_name_tips").text(merName);
      
      $("#divPop").show();
      return false;

}

function showHideDivRelatedMerchant(tid, tagName){
	var speed = 500;

	  var offset = $("#rm_pos_" + tid).offset();
	
      var pos = offset.top;
      
    	var scrollHeight = document.documentElement.clientHeight;
//    	if(pos + 550 > scrollHeight){
//        	if(pos > 550){
//    			pos = 	pos - 280 - 20;
//        	}
//       }
    	
      $("#divPop").css({ top: pos + "px", left: offset.left - 460});
      var tips =  $("#rm_tips_hide_" + tid).html();
     
      $("#tips_text").html(tips);
      $("#tag_name_tips").text(tagName);
      
      $("#divPop").show();
      return false;

}

function showHideDivRelatedCategory(tid, tagName){
	var speed = 500;

	  var offset = $("#rc_pos_" + tid).offset();
	
      var pos = offset.top;
      
    	var scrollHeight = document.documentElement.clientHeight;
//    	if(pos + 550 > scrollHeight){
//        	if(pos > 550){
//    			pos = 	pos - 280 - 20;
//        	}
//       }
    	
      $("#divPop").css({ top: pos + "px", left: offset.left - 460});
      var tips =  $("#rc_tips_hide_" + tid).html();
     
      $("#tips_text").html(tips);
      $("#tag_name_tips").text(tagName);
      
      $("#divPop").show();
      return false;

}

function hidDiv(id){
	$("#" + id).hide();
}

function editTaskUpdateCycle(tid){
	$("#div_editTaskUpdateCycle_" + tid).show();
	$("#link_TaskUpdateCycle_" + tid).hide();
}
function cancelTaskUpdateCycle(tid){
	$("#div_editTaskUpdateCycle_" + tid).hide();
	$("#link_TaskUpdateCycle_" + tid).show();
}
function editMinPromotionCount(tid){
	$("#div_editMinPromotionCount_" + tid).show();
	$("#link_MinPromotionCount_" + tid).hide();
}
function cancelMinPromotionCount(tid){
	$("#div_editMinPromotionCount_" + tid).hide();
	$("#link_MinPromotionCount_" + tid).show();
}

function saveTaskUpdateCycle(tid){
	var inputValue= $("#edit_TaskUpdateCycle_" + tid).val();
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
		url: "/editor/tag_maintenance_list.php?action=updatetaskupdatecycle&tid=" + tid + "&site=" + site + "&value=" + inputNum,
		success: function (msg) {
			if(msg == "success"){
				alert("TaskUpdateCycle Saved!");
				$("#span_TaskUpdateCycle_" + tid).text(inputNum + " Days");
				$("#div_editTaskUpdateCycle_" + tid).hide();
				$("#link_TaskUpdateCycle_" + tid).show();

			}else{
				alert(msg);
			}
		},
		error: function (){
			alert("Operate Error!");
		}
	});	
}

function saveMinPromotionCount(tid){
	var inputValue= $("#edit_MinPromotionCount_" + tid).val();
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
		url: "/editor/tag_maintenance_list.php?action=updateminpromotioncount&tid=" + tid + "&site=" + site + "&value=" + inputNum,
		success: function (msg) {
		if(msg == "success"){
			alert("MinPromotionCount Saved!");
			$("#span_MinPromotionCount_" + tid).text(inputNum);
			$("#div_editMinPromotionCount_" + tid).hide();
			$("#link_MinPromotionCount_" + tid).show();
			
		}else{
			alert(msg);
		}
	},
	error: function (){
		alert("Operate Error!");
	}
	});	
}


function showHistory(tid, action){
	
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/tag_maintenance_list.php?tid=" + tid + "&site=" + site + "&action=" + action,
		success: function (msg) {
		if(msg != "error"){
			  var offset = $("#" + action + "_" + tid).offset();
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
	$("#tag").val("");
	$("#tag_list_search").val("");
	$("#form1").submit();
}

function clearSpan(){
	$("#tag_id_span").html("");
	$("#tag").val("");
}