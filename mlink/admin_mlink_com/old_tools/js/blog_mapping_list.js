
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
	$("#objValue").live("click", function(){
		var site = $("#site").val();
		var objType = $("#type").val();
		$(".ac_results").remove();
		var ajaxTag = "merchantName";
		if(objType == "TAG"){
			ajaxTag = "tagName";
		}
		if(objType == "MERCHANT"){
			ajaxTag = "merchantName";
		}
		if(objType == "blogTag"){
			ajaxTag = "blogTag";
		}
//		alert('/editor/coupon_search.php?ajaxTag=' + ajaxTag + '&sitename=' + site + "&objtype=" + objType);
		$("#objValue").autocomplete('/editor/blog_search.php?ajaxTag=' + ajaxTag + '&sitename=' + site + "&objtype=" + objType, {
			scrollHeight: 320,
			max: 3000,
			formatItem: formatItem,
			formatResult: formatResult,
			autoFill: false
		});
	});
	
	$("#objValue").result(function(event,row,formatted){
		var objType = $("#type").val();
		$("#objID").val(row[0]);
		$("#obj_value_auto").text("Selected " + objType + " (" + row[0] + ")");
	});

	$("#objValue_add").live("click", function(){
		var site = $("#site_add").val();
		var objType = $("#type_add").val();
		$(".ac_results").remove();
		var ajaxTag = "merchantName";
		if(objType == "TAG"){
			ajaxTag = "tagName";
		}
		if(objType == "MERCHANT"){
			ajaxTag = "merchantName";
		}
		$("#objValue_add").autocomplete('/editor/blog_search.php?ajaxTag=' + ajaxTag + '&sitename=' + site + "&objtype=" + objType, {
			scrollHeight: 320,
			max: 3000,
			formatItem: formatItem,
			formatResult: formatResult,
			autoFill: false
		});
	});
	$("#objValue_add").result(function(event,row,formatted){
		var objType = $("#type_add").val();
		$("#objID_add").val(row[0]);
		$("#obj_value_auto_add").text("Selected " + objType + " (" + row[0] + ")");
	});
});

function changeSite(site){
	$(".ac_results").remove();
	$("#objID").val("");
	$("#objValue").val("");
}

function clearMerchantInfo(){
	$("#obj_value_auto").html("");
	$("#objID").val("");
}

function changeObjType(){
	$("#objValue").val(""); 
	$("#objID").val("");
	$("#obj_value_auto").text("");
	var objType = $("#type").val();

	if(objType == 'ALL'){
		$("#objValue").addClass("ndisplay"); 
		$("#category").addClass("ndisplay"); 
		$("#objTag").addClass("ndisplay"); 
	}else{
		$("#objTag").removeClass("ndisplay"); 
		if(objType == 'CATEGORY'){
			$("#objValue").addClass("ndisplay"); 
			$("#category").removeClass("ndisplay"); 
			$("#objTag").html("Category: ");
		}else{
			if(objType == 'TAG'){
				$("#objTag").html("Tag: ");
			}else{
				$("#objTag").html("Merchant: ");
			}
			$("#objValue").removeClass("ndisplay"); 
			$("#category").addClass("ndisplay"); 
		}
	}
}

function changeSite_add(site){
	$(".ac_results").remove();
	$("#objID_add").val("");
	$("#objValue_add").val("");
}

function clearMerchantInfo_add(){
	$("#obj_value_auto_add").html("");
	$("#objID_add").val("");
}

function changeObjType_add(){
	$("#objValue_add").val(""); 
	$("#objID_add").val("");
	$("#obj_value_auto_add").text("");
	var objType = $("#type_add").val();

	if(objType == 'ALL'){
		$("#objValue_add").addClass("ndisplay"); 
		$("#category_add").addClass("ndisplay"); 
		$("#objTag_add").addClass("ndisplay"); 
	}else{
		$("#objTag_add").removeClass("ndisplay"); 
		if(objType == 'CATEGORY'){
			$("#objValue_add").addClass("ndisplay"); 
			$("#category_add").removeClass("ndisplay"); 
			$("#objTag_add").html("Category: ");
		}else{
			if(objType == 'TAG'){
				$("#objTag_add").html("Tag: ");
			}else{
				$("#objTag_add").html("Merchant: ");
			}
			$("#objValue_add").removeClass("ndisplay"); 
			$("#category_add").addClass("ndisplay"); 
		}
	}
}

function deleteMapping(bmid,site){
	var answer = confirm("Continue To Delete This Mapping?");
	if (answer){
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/blog_mapping_list.php?action=deleteBlogMapping&bmid=" + bmid + "&site=" + site,
			success: function (msg) {
				if(msg == "success"){
					alert("Delete This Mapping Successfully!");
					location.reload();
				}else{
					alert(msg);
				}
			},
			error: function (){
				alert("Operate Error!");
			}
		});
	}
}