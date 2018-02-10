$(document).ready(function(e){
	resetStatus();
	//showIsvalid();
});

function addColor(obj) {
    originColor = $(obj).attr("bgColor");
    $(obj).attr("bgColor", "#FFFFBB");
    
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

function markSelectedLinks() {
	var cp_ids;
	var cp_ids_arr = new Array();
	var _flag = false;
	var site = "";
	var iscqc = $("#iscqc").val();
	
	if(!confirm('Sure you want to do this?')){
		return false;
	}
	var obj = arguments[4];
	var ptype = arguments[1];
	if (arguments[0] == 'single') {
		cp_ids = $("#" + arguments[2]).val();	
		if(typeof(arguments[3]) == 'string'){
			site = arguments[3];			
		}
	} else {
		$(".processtatuscheckbox").each(function(){
			if ($(this).attr('checked') == true) {
				_flag = true;
				return;
			}
		});
		
		if (_flag != true) {
			alert('Please select links first!');
			return false;
		}
		$(".processtatuscheckbox").each(function(i){
			if ($(this).attr('checked') == true) {
				if(!cp_ids){
					cp_ids = $(this).attr('value');
				}else{
					cp_ids = cp_ids +","+ $(this).attr('value');
				}	
			}
		});
//		cp_ids = cp_ids_arr.join(',');
	}

	var url = '/editor/coupon_queue_manage.php';
	if(iscqc != null && iscqc != ""){
		url = '/editor/task_cqc_coupon_queue_manage.php';
	}
	var removeobj = $(obj).parent().parent().parent().next().find('.couponlist').eq(arguments[5]-1);
	$.ajax({
    	type: "POST",
    	url: url,
    	data: "action=ajaxprocess&type="+escape(arguments[1])+"&cp_ids="+cp_ids+"&site="+site,
    	success: function(msg){
    		if (msg == 'success') {
    			alert('Operate Successfully');
				window.location.reload();
				/*if($(obj).length>0){
				$(obj).parent().html(site+'('+ptype+')');
				$(removeobj).css('background-color','');
				}else{
    			window.location.reload();
				}*/
    		} else {
    			alert('Operate fail');
    		}
        }
	});
}

sflag = false;
function selectAllLinks() {
	if (sflag == true) {
		sflag = false;
	} else {
		$("." + arguments[0]).each(function(){
			if ($(this).attr("checked") == true) {
				sflag = true;
				return;
			}
		});
	}
	
	if (sflag) {
		$("." + arguments[0]).attr('checked', 'checked');
		$("." + arguments[1]).attr('checked', 'checked');
	} else {
		$("." + arguments[0]).removeAttr('checked');
		$("." + arguments[1]).removeAttr('checked');
	}
	
	
}

function resetStatus() {
	$(".processtatuscheckbox").removeAttr('checked');
	$(".selall").removeAttr('checked');
}

function openUploadFeedWindow() {
	var iWidth = 700;
	var iHeight = 300;
	var iTop = (window.screen.availHeight-30-iHeight)/2; 
	var iLeft = (window.screen.availWidth-10-iWidth)/2;

	window.open('/editor/coupon_queue_manage.php?action=feedupload', 'newwindow', 'height=' + iHeight + ', width=' + iWidth + ', top=' + iTop + ', left=' + iLeft + ', toolbar=no, menubar=no, scrollbars=yes, status=no, resizable=no') ;
}

function ajaxFileUpload() {
	if ($("#affiliate").val() == 0) {
		alert('Select Affiliate first!');
		return false;
	}
	
	$("#loading")
	.ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
	});
	
	$("#msg")
	.ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
	});

	$.ajaxFileUpload
	(
		{
			url:'/ajax/coupon_queue_pending_feed_upload.php?action=feeduploaded&feedaffiliate=' + $("#affiliate").val(),
			secureuri:false,
			fileElementId:'fileToUpload',
			dataType: 'json',
			success: function (data, status)
			{
				if(typeof(data.error) != 'undefined')
				{
					if(data.error != '') alert(data.error);
					else alert(data.msg);
				}
			},
			error: function (data, status, e)
			{
				alert(e);
			}
		}
	)
	
	return false;
}

function markQueueVaild(isvalid, id){
	if (id == '') {
		alert("wrong queue id.");
	}
	
	if(!confirm(isvalid + ' this Queue?')){
		return false;
	}

	$.ajax({
    	type: "POST",
    	url: '/editor/coupon_queue_manage.php',
    	data: "action=ajaxisvalid&isvalid="+isvalid+"&id="+id,
    	success: function(msg){
    		if (msg == 'success') {
    			alert('Operate Successfully');
    			window.location.reload();
    		} else {
    			alert('Operate fail');
    		}
        }
	});
}

function showIsvalid(){
	if($("#competitor").val() == "COMPETITOR"){
		$("#isvalid").show();
	}else{
		$("#isvalid").hide();
	}
}


