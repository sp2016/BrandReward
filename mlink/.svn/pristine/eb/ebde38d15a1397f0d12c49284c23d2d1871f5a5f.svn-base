function ajaxPost(verifyArr, url, callback){
	$.ajax({
		type: "post",
		async: false,
		data: $.param(verifyArr),
		url: url,
		success: function (resString) {
			callback(resString);
		}
	});
}

function ajaxGet(action, verifyArr, url, callback){
	$.ajax({
		type: "get",
		async: false,
		data: $.param(verifyArr),
		url: url,
		success: function (resString) {
			callback(resString);
		}
	});
}