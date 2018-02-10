function doneTask(obj,mid){
	if(!confirm("Have you realy checked all reference and alerts?")){
		return false;
	}
	var site = $("#site").val();
	$("body").css({cursor:"wait"});
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=donetask&mid=" + mid + "&site=" + site,
		success: function (msg) { 
			if(msg != "error"){
				alert_btn = $(obj).parent().parent().find('td').eq(5).find('input');
				for(var i=0;i<alert_btn.length;i++){
					alert_btn.eq(i).click();
				}
				alert("Operate Success");
				//location.reload();
				$("#lastchecktime_" + mid).text(msg);
			}else{
				alert(msg);
			}
			$("body").css({cursor:"default"});
		},
		error: function (){
			alert("Operate Error!");
			$("body").css({cursor:"default"});
		}
	});	
	return true;
}
function doneAlert(obj, id, alertType){
	/*if(!confirm("Have you realy checked " + alertType + " alerts?")){
		return false;
	}*/
	var site = $("#site").val();
	$("body").css({cursor:"wait"});
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/merchant_maintenance_list.php?action=donealert&id=" + id + "&site=" + site + "&field=" + alertType,
		success: function (msg) {
		if(msg == "success"){
			$(obj).prev().remove();
			$(obj).remove();
			//alert("Operate Success");
			//$("#"+alertType+"_"+id).css("display","none");
			//location.reload();
//			$("#lastchecktime_" + mid).text(msg);
		}else{
			alert(msg);
		}
		$("body").css({cursor:"default"});
	},
	error: function (){
		alert("Operate Error!");
		$("body").css({cursor:"default"});
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
      var tips =  $("#tips_hide_" + mid).text();
     
      $("#tips_text").val(tips);
      $("#merchant_name_tips").text(merName);
      
      $.colorbox({
	    inline:true, 
	    opacity:0.5, 
	    overlayClose:false, 
	    speed:350, 
	    href:"#divPop", 
      });
      return false;

}
function PromotionsHis(site, merid){
	url = "http://reporting.megainformationtech.com/dataapi/pa.php?si="+site+"&id="+merid+"&tp=mer";
	$.colorbox({
        iframe:true,
        scrolling:false,
        opacity:0.5, 
    	innerWidth:"800px",
        innerHeight:"220px",
        overlayClose:false,
        href:url, 
    });
}
function PromotionsOnOff(site, merid){
	url = "http://reporting.megainformationtech.com/dataapi/pa_frame.php?si="+site+"&id="+merid+"&tp=mer";
	$.colorbox({
        iframe:true,
        scrolling:false,
        opacity:0.5, 
    	innerWidth:"800px",
        innerHeight:"260px",
        overlayClose:false,
        href:url, 
    });
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
		      $("#table_history").html(msg);
		      $.colorbox({
			    inline:true, 
			    opacity:0.5, 
			    overlayClose:false, 
			    speed:350, 
			    href:"#divPop1", 
		      });
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

function checkcolleagues(){
	var promotioncount = $("#promotioncount").val().trim();
	var duedate = $("#duedate").val().trim();
	if((promotioncount != "" && promotioncount != "ALL") && duedate == "" ){
		alert("You had set promotion count.\n Promotion count and due date must be set at the same time.");
		return false;
	}
/*	if((promotioncount == "" || promotioncount == "ALL") && duedate != ""){
		alert("You had set due date.\n Promotion count and due date must be set at the same time.");
		return false;
	}*/
	return true;
}


function showcouponduetime(id){
	$("#" + id).show();
}

function alertHistory(mid){
	alert(mid);
}
