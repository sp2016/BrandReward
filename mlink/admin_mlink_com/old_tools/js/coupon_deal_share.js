function searchTags(type, id, btnName){
	var catid;
	catid = $("#category option:selected").val();
	var merid;
	merid = $("#merchant").val();
	var couponid;
	couponid = $("#couponid").val();
	var site = $("#site").val();
	var searchKey = $("#" + id).val();
	if(btnName == "Reset"){
		searchKey = "";
	}
	var url = "";
	var selectedTags = "";
	var selectedObjID = "";
	switch(type){
		case "Seasonal":
			selectedObjID = "list8";
			break;
		case "Brands":
			selectedObjID = "list10";
			break;
		case "Products":
			selectedObjID = "list12";
			break;
		case "Category":
			selectedObjID = "list4";
			break;
		case "alltag":
			selectedObjID = "list6";
			break;
		case "Merchant":
			selectedObjID = "list2";
			break;
	}
	objid = document.getElementById(selectedObjID);
	
	for(var i=0; i<objid.options.length; i++)
	{
		if(selectedTags == ""){
			selectedTags = objid.options[i].value;
		}else{
			selectedTags = selectedTags + "," + objid.options[i].value
		}
	}
	searchKey = encodeURIComponent(searchKey);
	url = "/editor/coupon_search.php?action=rsynch-" + type + "&catid=" + catid + "&merid=" + merid + "&couponid=" + couponid + "&site=" + site + "&key=" + searchKey + "&selectedTags=" + selectedTags;
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: url,
		success: function (msg) {
//		alert(msg);
			if(msg != "error"){
				switch(type){
					case "Seasonal":
						$("#list7").html(msg);
						break;
					case "Brands":
						$("#list9").html(msg);
						break;
					case "Products":
						$("#list11").html(msg);
						break;
					case "Category":
						$("#list3").html(msg);
						break;
					case "alltag":
						$("#list5").html(msg);
						break;
					case "Merchant":
						$("#list1").html(msg);
						break;
				}
			}
		},
		error: function (){
			
		}
	});
}

function checkLpUrl(){
	var site = $("#site").val();
	var c_dst_url = $("#c_dst_url").val();
	var merid = $("#merchant").val();
	if((merid == '178' && site == 'csus') || (merid == '1689' && site == 'csuk') || (merid == '110047'  && site == 'csca') || (merid == '10190' && site == 'csde')){
		if(checkAmazonLandingPageUrl(c_dst_url) == true){
			var url = replaceAmazonLandingPageUrl(c_dst_url, merid);
			$("#c_dst_url").val(url);
			alert("Merchant Landing Page URL include 'tag=XXX', it has been removed, please recheck the lpurl.");
			return false;	
		}
	}
	var res = true;
	var verifyArr  = {'lpurl' : c_dst_url};
	
	$.ajax({
		type: "get",
		async: false,
		data: $.param(verifyArr),
		url: "/editor/coupon_search.php?action=rsynch-chk-couponlpurl-submit" + "&site=" + site +  "&merchantid=" + merid,
		success: function (repstring) {	
			var tmp = repstring.split("-||-");
			if (tmp[0] != "true") {
				$("#lpurlflag").val(tmp[0]);
				$("#c_dst_url").val(tmp[1]);
				res = true;
				if(tmp[0] == "confirm"){
					res = "confirm";
				}
			}				
		}
	});
	return res;
}
function checkTitleDesc(){
	var site = $("#site").val();
	var title = $("#title").val();
	/*var couponDeal = $("#normalcouponid").val();
	var desc = $("textarea[name='description_normal']").text();
	if(typeof(couponDeal) == "undefined" || couponDeal == null) {
		desc = $("#description").val();
	}*/
	var desc = $("#description").val();
	var code = $("#couponCode").val();
	var url = $("#c_dst_url").val();
	var merid = $("#merchant").val();
	var res = true;
	var verifyArr  = {'title' : title, 'desc' : desc, 'code':code, 'url':url};
	$.ajax({
		type: "get",
		async: false,
		data: $.param(verifyArr),
		dataType: 'json',
		url: "/editor/coupon_search.php?action=rsynch-chk-coupontitledesc" + "&site=" + site +  "&merchantid=" + merid,
		success: function (data) {
			if (data.flg != "init") {
				var Msg = "";
				if(data.title != "init"){
					Msg = Msg + data.title + "\n";
				}
				if(data.desc != "init"){
					Msg = Msg + data.desc + "\n";
				}
				if(data.code != "init"){
					Msg = Msg + data.code + "\n";
				}
				/*if(data.url != "init"){
					Msg = Msg + data.url + "\n";
				}*/
				var Msg = Msg + "\nClick Cancel to remove the black keyword(s). \n If you still want to use it, click OK to save..";
				if(confirm(Msg)){
					res = true;
				}else{
					res = false;
				}
			/*	if(confirm(Msg)){
					if(data.title != "init"){
						$("#title").val(data.title);
					}
					if(data.desc != "init"){
						$("#description").val(data.desc);
//						$("textarea#description_normal").text(data.desc);
					}
					if(data.code != "init"){
						$("#couponCode").val(data.code);
					}
					if(data.url != "init"){
						$("#c_dst_url").val(data.url);
					}
					res = true;
				}else{
					res = false;
				}*/
			}else{
				res = true;
			}			
		}
	});
	return res;
}

function removeSource(couponsourceId){
	var site = $("#site").val();
	var verifyArr = new Array();
	if(!confirm("If you delete this source, other coupons for the same merchant and having the same code may lose this source too. Continue?")){
		return false;
	}
	var url = "/editor/coupon_search.php?ajaxTag=deletecouponsource&action=deletecouponsource" + "&couponsourceid=" + couponsourceId + "&sitename=" + site ; 
	$.ajax({
		type: "post",		
		url: url,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "success"){
				alert("Delete Source Success.");
				$("#couponsource_" + couponsourceId).remove();
			}else{
				alert(msg);
			}
		}		
	});
}

function addSourceToCoupon(couponid){
	var select_source = $("#select_source").val().trim();
	var s_source = $("#s_source").val().trim();
	var sourceid = $("#sourceid").val().trim();
	var source = s_source;
	if(select_source != '' && select_source != "none"){
		source = select_source;
	}
	if(source == "" && sourceid == ""){
		alert("Please input Source.");
		return;
	}
	if(source == "none"){
		alert("Please input Source.");
		return;
	}
	var site = $("#site").val();
	var verifyArr = new Array();
	var url = "/editor/coupon_search.php?action=addcouponsource" + "&couponid=" + couponid + "&couponsourceid=" + sourceid + "&couponsource=" + source + "&sitename=" + site ; 
	$.ajax({
		type: "post",		
		url: url,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "success"){
				if(confirm("Add Source Success.Close window?")){
					window.close()
				}
			}else{
				alert(msg);
			}
		}		
	});
}

function checkPromotion(){
	var off = $("#pro_off").val();
	var intOff = parseInt(off);
	if($("#pro_detail").val() == "percent"){
		if(intOff >= 100){
			alert("Promotion detail must be less than 100%");
			return false;
		}
	}
	return true;
}

function setverifytime(couponid){
	var site = $("#site").val();
	var verifyArr = {'id' : couponid, 'site':site, 'sitename':site};
	var url = "/editor/coupon_search.php?action=setverifytime"
	ajaxPost(verifyArr, url, callbackFunc);
}
function callbackFunc(strRes){
	if(strRes != 'success'){
		alert(strRes);
	}else{
		alert("Verify success");
	}
}
function checkAmazonLandingPageUrl(url){
	var idx = url.indexOf("tag=");
	if(idx >= 0){
		return true;
	}
	return false;
}
function replaceAmazonLandingPageUrl(url, mid){
	if(url == ''){
		return "";
	}
	if(mid != '178' && mid != '110047' && mid != '1689' && mid != '10190'){
		return url;
	}
	var reg=new RegExp("[?&]tag=[^&]*","gmi");
	var rep=url.replace(reg, "");
	return rep; 
}
function autoSelTagSitewide(){
	if($("#PromotionDetail input[name='pro_detail\[site_wide\]']").attr("checked") == true){
		option_moveall('list9','list10');
	}
}
$(function(){
	$("#PromotionDetail input[type='checkbox']").click(function(){
		var name = $(this).val();
		if($(this).attr('checked')){
			if(name=='other'){
				$("#PromotionDetail input[type='checkbox']").attr('checked','');
				$(this).attr('checked','checked');
			}else{
				$("#PromotionDetail input[value='other']").attr('checked','');
			}
			if(name=='site_wide'){
				autoSelTagSitewide();
			}
		}
	});
});