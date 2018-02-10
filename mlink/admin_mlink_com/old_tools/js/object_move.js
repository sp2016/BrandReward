
$().ready(function() {
	function log(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
//		rowStr = rowStr.toString();
//		var row  =  rowStr.split("|");
		return row[1] + "(" + row[0] + ")" + " "+row[2];
	}
	function formatResult(row) {
//		rowStr = rowStr.toString();
//		var row  =  rowStr.split("|");
		return row[1];
	}
	$("#merchant_search").live("click", function(){
		
		var site = $("#site").val();
		var objType = $("#objtype").val();
		$(".ac_results").remove();
		var ajaxTag = "merchantName";
		if(objType == "tag"){
			ajaxTag = "tagName";
		}
		if(objType == "merchant"){
			ajaxTag = "merchantName";
		}
//		alert('/editor/coupon_search.php?ajaxTag=' + ajaxTag + '&sitename=' + site + "&objtype=" + objType);
		$("#merchant_search").autocomplete('/editor/coupon_search.php?ajaxTag=' + ajaxTag + '&sitename=' + site + "&objtype=" + objType, {
			scrollHeight: 320,
			max: 3000,
			formatItem: formatItem,
			formatResult: formatResult,
			autoFill: true,
			addfun: addfun
		});
	});
	//merchant
	$("#merchant_search").result(function(event,row,formatted){
		var objType = $("#objtype").val();
		if(objType == "tag"){
			$("#merchant").val(row[0]);
			$("#merchant_name_auto").text("Selected Tag (" + row[0] + ")");
		}
	});
	
	var temp_mer_text=$("#merchant_search").val();

	function addfun(rows){	
		var site = $("#site").val();
		var verifyArr  = {'ajaxTag':'merchantName'};		
		var objType = $("#objtype").val();
		if(objType == "tag"){
//			alert(rows);
		}else if(objType == "merchant"){
			$.ajax({type: "POST",
				url: "/editor/coupon_search.php?ajaxTag=getMerchantIdByName&q="+escape($("#merchant_search").val()) + "&sitename=" + site+ "&objtype=" + objType,
				data: $.param(verifyArr),
				success: function(msg){
					$("#merchant").val(msg);					
					$("#merchant_name_auto").html("Selected Merchant: "+temp_mer_text+"("+msg+")");
					$.ajax({type: "POST",
						url: "/editor/coupon_search.php?ajaxTag=getMerchantDeepUrl&q="+escape(msg)  + "&sitename=" + site + "&objtype=" + objType,
						data: $.param(verifyArr),
						success: function(url){
							if(url != ''){
								$("#deepurl").html(url);
								//$("#deepurl_div").show();
								$(".non_deep").hide();
								$(".is_deep").show();
							}else{
								$("#deepurl").html("");
								//$("#deepurl_div").hide();
								$(".is_deep").hide();
								$(".non_deep").show();
							}
						}	
					});
					$("#merchant_tips").html("<img src='/image/loading.gif' />");
					$.ajax({type: "POST",
						url: "/editor/coupon_search.php?ajaxTag=getMerchantTips&q="+escape($("#merchant_search").val())  + "&sitename=" + site + "&objtype=" + objType,
						data: $.param(verifyArr),
						success: function(msg){
							$("#merchant_tips").html(msg);
						}					   
					});	
				}					   
										   
			});
		}
		
	}
});


function submitCheck(){
	var comment = $("#comment").val();
	if(comment == ""){
		alert("Please input Comment.");
		return false;
	}
	if($("#merchant_search").val() == ""){
		alert("Please select merchant from the list.");
		return false;
	}
	var site = $("#site").val();	
	var mid = $("#merchant").val();
	var objtype = $("#objtype").val();
	var verifyArr  = {'objtype':objtype, 'comment':comment};	
	
	$.ajax({type: "POST",
		url: "/editor/object_move.php?action=checkobj&mid=" + mid  + "&site=" + site + "&objtype=" + objtype,
		data: $.param(verifyArr),
		success: function(msg){
			if(msg == "success"){
				if(confirm("Do you realy want move " + objtype + " to urus?")){
				    $.colorbox({href:"#Download", inline:true, opacity:0.5, width:"500px",height:"200px",title: "Do not close the window. " + objtype +  " moving...... ",scrolling:false,overlayClose:false});
				    $("#cboxClose").hide();
					$.ajax({type: "POST",
						url: "/editor/object_move.php?action=move&mid=" + mid  + "&site=" + site + "&objtype=" + objtype,
						data: $.param(verifyArr),
						success: function(msg){
							$.colorbox.close();
							if(msg == "success"){
								alert(objtype + " Moved");
							}else{
								alert("Msg:" + msg);
							}
						}					   
					});
				}
			}else{
				$.colorbox.close();
				if(objtype == "tag"){
					alert("Tag not exist,Please reselect.\n" );
				}else{
					alert("Merchant not exist,Please reselect.\n" );
				}
			}
		}					   
	});

}

function changeSite(site){
	$(".ac_results").remove();
	$("#merchant_tips").html("");
	$("#merchant").val("");
	$("#merchant_search").val("");
}

function clearMerchantInfo(){
	$("#merchant_name_auto").html("");
	$("#merchant_tips").html("");
	$("#merchant").val("");
	$("#merchant_id").val("");
}

function changeObjType(){
	var objType = $("#objtype").val();
	$("#merchant_search").val(""); 
	$("#merchant").val("");
	$("#merchant_id").val("");
	$("#merchant_tips").html("");
	$("#merchant_name_auto").text("");
	if(objType == "tag"){
		$("#objTag").text("Tag");
	}
	if(objType == "merchant"){
		$("#objTag").text("Merchant");
	}
}