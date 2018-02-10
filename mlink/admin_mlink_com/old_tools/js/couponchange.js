function saveRemark(site){
	var remark = $("#changeComment").val();
	remark=encodeURIComponent(remark); 
	var couponid = $("#couponid").val();
	var couponaddinfoid = $("#couponaddinfoid").val();
	var verifyArr  = {'remark':remark, 'couponaddinfoid':couponaddinfoid, 'couponid':couponid};
	var url = "/editor/coupon_search.php?ajaxTag=changeComment" + "&site=" + site;
//	alert(url);
	$.ajax({
		type: "post",
		async: true,
		data:$.param(verifyArr),
		url: url,
		success: function (msg) {
			if(msg == "success"){
				alert("Succeed to save it.");
			}else{
				alert("Saved error(" + msg + ").");
			}
		},
		error: function (){
			alert("Error");
		}
	});	
}