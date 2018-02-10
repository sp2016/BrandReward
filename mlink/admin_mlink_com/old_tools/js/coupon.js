function submitCheck()
{
	var AllowCoupon = $("#AllowCoupon_hidden").val();
	var show_block = $("#show_block").val();
	if(AllowCoupon == "NO" && show_block != "coupon_edit" && $("input[name='type']:checked").val() == 1){
		alert("This Merchant doesn't allow coupon!");
		return false;
	}
	var regu = "^[0-9]+$|^[0-9]+\.[0-9]+$";
    var re = new RegExp(regu);
   
	var ischecked_pro_money = $("input[name='pro_detail[money]']").attr('checked');
	if(ischecked_pro_money){
		if(null == $("input[name='pro_con[money]']").val() || $("input[name='pro_con[money]']").val() == ""){
			alert("Please input Promotion Price!");
			$("input[name='pro_con[money]']").focus();
			return false;
		}
		
		if ($("input[name='pro_con[money]']").val().search(re) == - 1) {
			alert("Promotion Price must be a Number!");
			$("input[name='pro_con[money]']").focus();
		 	return false;
		 }
	}
	var ischecked_pro_from = $("input[name='pro_detail[from]']").attr('checked');
	if(ischecked_pro_from){
		if(null == $("input[name='pro_con[from]']").val() || $("input[name='pro_con[from]']").val() == ""){
			alert("Please input Promotion From Price!");
			$("input[name='pro_con[from]']").focus();
			return false;
		}
		
		if ($("input[name='pro_con[from]']").val().search(re) == - 1) {
			alert("Promotion From Price must be a Number!");
			$("input[name='pro_con[from]']").focus();
		 	return false;
		 }
	}
	var ischecked_pro_percent = $("input[name='pro_detail[percent]']").attr('checked');
	if(ischecked_pro_percent){
		if(null == $("input[name='pro_con[percent]']").val() || $("input[name='pro_con[percent]']").val() == ""){
			alert("Please input Promotion Percent!");
			$("input[name='pro_con[percent]']").focus();
			return false;
		}
	}
	if($("input[name='type']:checked").val()==1){
	var couponCodeObj = document.getElementById('couponCode');
	if(couponCodeObj.value.trim().length > 255)
	{
		alert('coupon code should less than 255 characters');
		couponCodeObj.focus();
		return false;
	}
	}
	
	if(checkExpireType() == false){
		return false;
	}
	
	var startDateObj = document.getElementById('couponstartDate');
	if(!checkIsValidDate(startDateObj.value.trim()))
	{
		alert('start date should use this format YYYY-mm-dd');
		startDateObj.focus();
		return false;
	}
	
	var expireDateObj = document.getElementById('expireDate');
	if(!checkIsValidDate(expireDateObj.value.trim()))
	{
		alert('expire date should use this format YYYY-mm-dd');
		expireDateObj.focus();
		return false;
	}

	var startTimeObj = document.getElementById('couponstartDate_time');
	if(!checkIsValidTime(startTimeObj.value.trim()))
	{
		alert('expire time should use this format hh:mm:ss');
		startTimeObj.focus();
		return false;
	}
	
	var expireTimeObj = document.getElementById('expireDate_time');
	if(!checkIsValidTime(expireTimeObj.value.trim()))
	{
		alert('expire time should use this format hh:mm:ss');
		expireTimeObj.focus();
		return false;
	}
	if(checkPromotion() == false){
		return false;
	}
	source = $("#select_source").val();
	newsource = $("#s_source").val();
	if((source == "none" || source == "") && newsource == ""){
		alert("Coupon Source cannot be left blank, please select one.");
		$("#select_source").focus();
		return false
	}
/*
	var affUrl = $("#").val();
	var program = $("#sel_affiliate").val();
	if(affUrl != "" && program == "none"){
		if(!confirm("Affiliate Link URL is null, Continue?")){
			return false;
		}
	}*/
	var s_date=startDateObj.value+" "+startTimeObj.value;
	var e_date=expireDateObj.value+" "+expireTimeObj.value;

	if($("#expire_type").val() == "Fixed"){
		if(expireDateObj.value=="0000-00-00" || e_date=="")
		{
			alert('expire date can not be 0000-00-00');
			startDateObj.focus();
			return false;
		}
		
		//if(((expireDateObj.value!="" || expireDateObj.value!="0000-00-00") && (expireTimeObj.value!="" || expireTimeObj.value!="00:00:00")) && ((startDateObj.value > expireDateObj.value) || (startDateObj.value == expireDateObj.value && startTimeObj.value >= expireTimeObj.value)))
		//if(e_date<s_date && e_date!="0000-00-00 00:00:00" && e_date!="")
		if(e_date<s_date)
		{
			alert('start date should earlier than expire date');
			startDateObj.focus();
			return false;
		}	
		
		var year = parseInt(e_date.substr(0,4)) - parseInt(s_date.substr(0,4));
		var month = parseInt(e_date.substr(5,2)) - parseInt(s_date.substr(5,2));
		var day = parseInt(e_date.substr(8,2)) - parseInt(s_date.substr(8,2));
		if((year > 2) || (year == 2 && month > 0) || (year == 2 && month == 0 && day > 0))
		{
			alert('end date can not more than 2 years');
			expireDateObj.focus();
			return false;
		}
	}
	var urlObj = document.getElementById('c_aff_url');
	if(!urlObj.value.trim().IsStartWithHttp() && urlObj.value.trim() != "")
	{
		alert("Affiliate Link URL must starts with http://");
		urlObj.focus();
		return false;
	}
	
	if($("input[name='type']:checked").val()==1){
	
		if($(".onlinestate").eq(0).attr('checked') == true){
				var urlObj = document.getElementById('c_dst_url');
			if(urlObj.value.trim() == "")
			{
				alert("Merchant Landing Page URL is required");
				urlObj.focus();
				return false;
			}
			if(!urlObj.value.trim().IsStartWithHttp())
			{
				alert("Merchant Landing Page URL must starts with http://");
				urlObj.focus();
				return false;
			}
			
		}
		
		if($(".onlinestate").eq(1).attr('checked') == true){
			if($("#aff_by_url").attr("checked") == true && $("#aff_url_link").val()!=''){
				var urlObj = document.getElementById('aff_url_link');
				if(urlObj.value.trim() == "")
				{
					alert("Merchant Landing Page URL is required");
					urlObj.focus();
					return false;
				}
				if(!urlObj.value.trim().IsStartWithHttp())
				{
					alert("Merchant Landing Page URL must starts with http://");
					urlObj.focus();
					return false;
				}
			}else{
				var urlObj = document.getElementById('aff_url_file');
				if(urlObj.value.trim() == "")
				{
					alert("Please select upload image.");
					urlObj.focus();
					return false;
				}
			}			
		}
	
	}
	
	if($("input[name='type']:checked").val()==2){
	
		if($(".onlinestate").eq(0).attr('checked') == true){
			var urlObj = document.getElementById('c_dst_url');
			if(urlObj.value.trim() == "")
			{
				alert("Merchant Landing Page URL is required");
				urlObj.focus();
				return false;
			}
			if(!urlObj.value.trim().IsStartWithHttp())
			{
				alert("Merchant Landing Page URL must starts with http://");
				urlObj.focus();
				return false;
			}
			
		}
	
	}
	
	/*if($(".onlinestate").eq(1).attr('checked') == true && $("#aff_url_link").val()!=''){	
		if($("input[name='type']:checked").val()==1){
			if($("#aff_by_url").attr("checked") == true){			
				var urlObj = document.getElementById('aff_url_link');
				if(urlObj.value.trim() == "")
				{
					alert("Merchant Landing Page URL is required");
					urlObj.focus();
					return false;
				}
				if(!urlObj.value.trim().IsStartWithHttp())
				{
					alert("Merchant Landing Page URL must starts with http://");
					urlObj.focus();
					return false;
				}
			}else{
				var urlObj = document.getElementById('aff_url_file');
				if(urlObj.value.trim() == "")
				{
					alert("Please select upload image.");
					urlObj.focus();
					return false;
				}
			}
		}
	}else{
		var urlObj = document.getElementById('c_dst_url');
		//alert(urlObj.value);
		if(urlObj.value.trim() == "")
		{
			alert("Merchant Landing Page URL is required");
			urlObj.focus();
			return false;
		}
		if(!urlObj.value.trim().IsStartWithHttp())
		{
			alert("Merchant Landing Page URL must starts with http://");
			urlObj.focus();
			return false;
		}	
	}*/
	
	var merid = $("#merchant").val();
	var c_dst_url = $("#c_dst_url").val();
	var checkdomian = true;
	var site2 = $("#site").val();
	$.ajax({
		type: "post",
		async: false,
			url: "/editor/get_merlanding_url.php?site="+site2+"&action=getdomain&mer_id=" + merid,
			success: function (msg) {
				if(msg=="" || msg == null){
					return;
				}
				var tmp = msg.split("http://");
				 tmp = tmp[1].split("/");
				 tmp = tmp[0].split("?");
				 msg = tmp[0];
				msg = msg.substring(msg.indexOf("."));
				msg = msg.replace(/\//,"");
				if(c_dst_url !=""){
					if(c_dst_url.indexOf(msg) == -1){
						if(!confirm("Landing Page URL Domain and Store URL Domain are Different, Continue?")){
							checkdomian = false;
						}
					}
				}
				
			},
			error: function (){
				
			}
		});	
	if(!checkdomian){
		$("#c_dst_url").focus();
		return false;
	}
//alert('ok1');
	var titleObj = document.getElementById('title');
	if(titleObj.value.trim().length < 1 || titleObj.value.trim().length > 255)
	{
		alert('coupon title can not be empty and should less than 255 characters');
		titleObj.focus();
		return false;
	}

	if(checkStr(titleObj.value.trim()))
	{
		alert('Please check coupon title, some word are not permissioned');
		titleObj.focus();
		return false;
	}

	var descriptionObj = document.getElementById('description');
	if(checkStr(descriptionObj.value.trim()))
	{
		alert('Please check coupon description, some word are not permissioned, keyword : '+descriptionObj.value.trim().match(/(&[a-zA-Z]{2,5};|&#[0-9]{2,5};)/));
		descriptionObj.focus();
		return false;
	}
	
	var merchantObj = document.getElementById('merchant');
	if(merchantObj.value == 0)
	{
		alert('please choose a merchant');
		merchantObj.focus();
		return false;
	}
	
	getselectedmer();
	
	
	var site = $("#site").val();
	//us uk is v4 site, so no about category actions by devin 201410211807
	if(site !="csus" && site !="csuk" && site !="csde"){
		//getSelectedCat();
	}
	
	//us uk is v4 site, so no about category actions by devin 201410211807
	/** if(site!='csus' && site!='csfr'){ */
	if(site!='csus' && site!='csfr' && site !="csuk" && site !="csde"){
		/*var categoryObj = document.getElementById('category');
		if(categoryObj.value == 0 || categoryObj.value == "")
		{
			alert('please choose a category');
			categoryObj.focus();
			return false;
		}*/
	}
	var tagObj = document.getElementById('newtag');
	var objselectedmer = document.getElementById('selectedmer');
	if(objselectedmer.value == "" && tagObj.value == "")
	{
/** cancel all site msg alert by devin 201410211748		
 * if(site!='csus'){
			if(!confirm("You didn't choose any tag. Continue?")){
				tagObj.focus();
				return false;
			}
		}
		*/
	}
	var checkRes = checkTitleDesc();
	if(checkRes == false){
		return false;
	}
	$("#lpurlflag").val("NO");
	var checkRes = checkLpUrl();
	if(checkRes == false){
		return false;
	}
	if(checkRes == "confirm"){
		if(!confirm("LpURL seems like AffUrl, continue to save coupon?")){
	    	return false;
	    }
	}
	var merid = $("#merchant").val();
	var couponcode = $("#couponCode").val();
	var couponid = $("#couponid").val();
	var site = $("#site").val();
	
//	if(couponcode != "" && $("#isDynamicCode").attr("checked") != "true"){
		var aff_url = $("#c_aff_url").val();
		var v_code = false;
//		var appreveCode = false;
		var verifyArr  = {'c_aff_url':aff_url};
		$.ajax({
			type: "get",
			async: false,
			data: $.param(verifyArr),
			url: "/editor/coupon_search.php?action=rsynch-chk-couponcode-submit" + "&couponcode=" + couponcode + "&merchantid=" + merid + "&couponid=" + couponid + "&site=" + site,
			success: function (repstring) {
				if (repstring == "1") {
				    if(!confirm("The code was duplicated, continue to add coupon?")){
				    	document.getElementById('couponCode').focus();
				    	v_code = true;
				    }
//				    else{
//				    	appreveCode = true;
//				    }
				}
				if(repstring == "2"){
					alert("Invalid Affiliate Link URL");
					v_code = true;
				}
			}
		});
		if(v_code){
			return false;
		}
//		if(appreveCode){
//			$("#approvestatus").val("YES");
//		}
//	}
	var AllowNonAffPromo = $("#AllowNonAffPromo_hidden").val();
	var AllowUnaccuratePromo = $("#AllowUnaccuratePromo_hidden").val();
	var show_block = $("#show_block").val();
	if((AllowNonAffPromo == "NO" || AllowUnaccuratePromo == "NO") && show_block != "coupon_edit"){
		if(!confirm("This Merchant doesn't allow NonAffPromo or InaccuratePromo! continue?")){
			return false;
		}
	}
	
	//promation detail
	var pro_check = false;
	for(var i=0;i<$("#PromotionDetail input[type='checkbox']").length;i++){
		if($("#PromotionDetail input[type='checkbox']").eq(i).attr('checked') == true){
			pro_check = true;
		}
	}
	if(pro_check==false){
		alert("Please input Promotion detail");
		return false;
	}
	
	//Check Coupon Policy
	var AllowNonAffCoupon = $("#AllowNonAffCoupon").val();
	var AllowNonAffPromo = $("#AllowNonAffPromo").val();
	var promoType = $("input[name='type']:checked").val();
	var promoSource = $("#select_source").val();
	var promoOnline = $(".onlinestate").eq(0).attr('checked');
	var promoInstore = $(".onlinestate").eq(1).attr('checked');
	if(AllowNonAffCoupon=='NO'&&(promoSource.indexOf("AFFILIATE")==-1&&promoSource.indexOf("Urgent")==-1&&promoSource.indexOf("urgent")==-1&&promoSource.indexOf("Affiliate")==-1)&&promoType==1&&promoOnline==true){
		alert("Please input Affiliate Coupon");
		return false;
	}
	if(AllowNonAffPromo=='NO'&&(promoSource.indexOf("AFFILIATE")==-1&&promoSource.indexOf("Urgent")==-1&&promoSource.indexOf("urgent")==-1&&promoSource.indexOf("Affiliate")==-1)&&promoOnline==true){
		alert("Please input Affiliate Promo");
		return false;
	}
	
	return true;
}

function checkCouponPolicy(){

}

function checkSpace(obj){
	var reSpaceCheck = / {2,}/;
	var val = $(obj).val();
	$(obj).parent().find("#spacetip").remove();
	$isSpage = false;
	if (reSpaceCheck.test(val)){
		$isSpage = true;
		//$(obj).after("<font id='spacetip' color='red'><br>The middle postion has 2+ spaces. </font>");
	}
	var reSpaceCheck = /^ /;
	if (reSpaceCheck.test(val)){
		$isSpage = true;
		//$(obj).after("<font id='spacetip' color='red'><br>The start position has 1 space. </font>");
	}
	var reSpaceCheck = /\s+\./;
	if (reSpaceCheck.test(val)){
		$isSpage = true;
		//$(obj).after("<font id='spacetip' color='red'><br>There has a space before comma or dot. </font>");
	}
	var reSpaceCheck = /\s+,/;
	if (reSpaceCheck.test(val)){
		$isSpage = true;
		//$(obj).after("<font id='spacetip' color='red'><br>There has a space before comma or dot. </font>");
	}
	if($isSpage == true) $(obj).after("<font id='spacetip' color='red'><br>Extra space(s). </font>");
	/*var reSpaceCheck = / $/;
	if (reSpaceCheck.test(val)){
		$(obj).after("<font id='spacetip' color='red'><br>The end position is a space. </font>");
	}*/
}

function checkSameOff(){
	var code = $("#couponCode").val();
	//PromotionDetail
}

function checkStr(str)
{
	var pattern = /&[a-zA-Z]{2,5};|&#[0-9]{2,5};/;
	if(!pattern.test(str)) return false;
	return true;	
}

function checkIsValidTime(dateTimeStr)
{
   //var pattern = /^(\d{2}):(\d{2}):(\d{2})$/;
  // var pattern = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/;
   var pattern = /^(20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/;
   if(!pattern.test(dateTimeStr)) return false;
   return true;
}

function getSelectedCat(){
	var category = "";
	var tag = "";
	var objlist = document.getElementById('categoryRight');
	for(var i=0; i<objlist.options.length; i++)
	{ 
		if(objlist.options[i].value != "")
		{ 
			if(typeof($(objlist.options[i]).attr('parentid')) != "undefined"){
				
				if(tag == ""){
					tag = $(objlist.options[i]).val();
				}else{
					tag = tag + "," + $(objlist.options[i]).val();
				}
			}else{
				if(category == ""){
					category = $(objlist.options[i]).val();
				}else{
					category = category + "," + $(objlist.options[i]).val();
				}
			}
		 }
	}
	var tagOld = $("#selectedmer").val();
	if(tagOld == ""){
		$("#selectedmer").val(tag)
	}else{
		$("#selectedmer").val(tagOld + "," + tag)
	}
	
	$("#category").val(category);
}

function getselectedmer()
{
	var selectedmer = "";
//	var objlist = document.getElementById('list2');
//	for(var i=0; i<objlist.options.length; i++)
//	{ 
//		if(objlist.options[i].value != "")
//		{ 
//			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
//		 }
//	}
	
//	var objlist = document.getElementById('list4');
//	for(var i=0; i<objlist.options.length; i++)
//	{ 
//		if(objlist.options[i].value != "")
//		{ 
//			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
//		 }
//	}
	var objlist = document.getElementById('list6');
	for(var i=0; i<objlist.options.length; i++)
	{ 
		if(objlist.options[i].value != "")
		{ 
			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
		 }
	}
	
	var objlist = document.getElementById('list8');
	for(var i=0; i<objlist.options.length; i++)
	{ 
		if(objlist.options[i].value != "")
		{ 
			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
		 }
	}
	var objlist = document.getElementById('list10');
	for(var i=0; i<objlist.options.length; i++)
	{ 
		if(objlist.options[i].value != "")
		{ 
			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
		 }
	}
//	var objlist = document.getElementById('list12');
//	for(var i=0; i<objlist.options.length; i++)
//	{ 
//		if(objlist.options[i].value != "")
//		{ 
//			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
//		 }
//	}
	 var objselectedmer = document.getElementById('selectedmer');
	 objselectedmer.value = selectedmer;
	 return;
}

function OpenUrl(id){
	var urlObj = document.getElementById(id);
	if(urlObj.value != "" && urlObj.value.trim().IsStartWithHttp()){
		window.open(urlObj.value);
	}else{
		alert("URL must starts with http://");
		urlObj.focus();
		return false;
	}
}

function previewUrl(backend, id){
	var urlObj = document.getElementById(id);
	var merid = $("#merchant").val();
	var site = $("#site").val();
	
	if(urlObj.value != "" && urlObj.value.trim().IsStartWithHttp()){
//		alert(backend + "/editor/rd.php?merid=" + merid + "&url=" + encodeURIComponent(urlObj.value));
		window.open("/front/rd.php?merid=" + merid + "&url=" + encodeURIComponent(urlObj.value) + "&site=" + encodeURIComponent(site));
	}else{
		alert("Affiliate Link URL must starts with http://");
		urlObj.focus();
		return false;
	}
}

function getLandingUrl(id){
	var urlObj = document.getElementById(id);
	if(urlObj.value != "" && urlObj.value.trim().IsStartWithHttp()){
		$("#landingpage_load").show();
		$("#landingpage_button").hide();
		var mid = $("#merchant").val();
		var site = $("#site").val();
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/get_merlanding_url.php?url=" + encodeURIComponent(urlObj.value) + "&site=" + site + "&mer_id=" + mid,
			success: function (msg) {
				if (msg == "error") {
					alert("Generate Url Failed. Please Try again.");
					$("#c_dst_url").val("");
				}else if (msg == "aff_error"){
					alert("Affiliate Link URL is wrong(aff_error).");
					$("#c_dst_url").val("");
				}else if (msg == "404"){
					alert("Affiliate Link URL is wrong(404).");
					$("#c_dst_url").val("");
				}else{					
					$("#c_dst_url").val(msg);
					//$("#"+id).val("");
				}
				$("#landingpage_load").hide();
				$("#landingpage_button").show();
			},
			error: function (){
				$("#landingpage_load").hide();
				$("#landingpage_button").show();
			}
		});
	}else{
		alert("Affiliate Link URL must starts with http://");
		urlObj.focus();
		return false;
	}
}

function getDefaultUrl(id){
	var merid = $("#merchant").val();
	$("#defaultpage_load").show();
	$("#defaultpage_button").hide();
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/get_merlanding_url.php?action=getdefault&mer_id=" + merid + "&site=" + site,
		success: function (msg) {
			if (msg != 2) {
				var temp = msg.split("||");
				if(temp[0] == "error"){
					alert("JS redirection was detected. After you click OK, the Merchant Default URL will be opened in a new window and jump as the final URL. Please copy and paste the final URL into Merchant Landing Page URL box. ");
					window.open(temp[1]);
				}else{
					$("#"+id).val(msg);
				}
			}else{
				$("#"+id).val("");
			}
			$("#defaultpage_load").hide();
			$("#defaultpage_button").show();
		},
		error: function (){
			$("#defaultpage_load").hide();
			$("#defaultpage_button").show();
		}
	});	
}

function getDomainUrl(id){
	var merid = $("#merchant").val();
	$("#domainpage_load").show();
	$("#domainpage_button").hide();
	var site = $("#site").val();
	$.ajax({
		type: "post",
		async: true,
		url: "/editor/get_merlanding_url.php?action=getdomain&mer_id=" + merid + "&site=" + site,
		success: function (msg) {
			if (msg != 2) {
				$("#"+id).val(msg);
			}else{
				$("#"+id).val("");
			}
			$("#domainpage_load").hide();
			$("#domainpage_button").show();
		},
		error: function (){
			$("#domainpage_load").hide();
			$("#domainpage_button").show();
		}
	});	
}

function gencontent()
{
	var codeObj = document.getElementById('couponCode');
	var couponcode = codeObj.value;

	var titleObj = document.getElementById('title');
	if(titleObj.value.trim().length < 1 || titleObj.value.trim().length > 255)
	{
		alert('coupon title can not be empty and should less than 255 characters');
		titleObj.focus();
		return;
	}
	var coupontitle = titleObj.value;

	var merObj = document.getElementById('merchant');
	if(merObj.value == 0)
	{
		alert('please choose a merchant');
		merObj.focus();
		return false;
	}

	//var mername =  merObj.options[merObj.selectedIndex].text.substr(0, merObj.options[merObj.selectedIndex].text.lastIndexOf(' - '));
	//var mername =  document.getElementById('merchant_search').value;
	var mername =  "[MERCHANT NAME]";
	
	var description = "";

	var template_sel=document.getElementById("gencontent_sel").value;

	
	var re = /\[COUPON TITLE\]/g;
	template_sel = template_sel.replace(re, coupontitle);
	var re = /\[COUPON CODE\]/g;
	template_sel = template_sel.replace(re, couponcode);
	
	description = template_sel;
	
	document.getElementById('description').value = description;

	return false;
}

function gencontent1()
{
	var codeObj = document.getElementById('couponCode');
	var couponcode = codeObj.value;

	var titleObj = document.getElementById('title');
	if(titleObj.value.trim().length < 1 || titleObj.value.trim().length > 255)
	{
		alert('coupon title can not be empty and should less than 255 characters');
		titleObj.focus();
		return;
	}
	var coupontitle = titleObj.value;

	var merObj = document.getElementById('merchant');
	if(merObj.value == 0)
	{
		alert('please choose a merchant');
		merObj.focus();
		return false;
	}

	//var mername =  merObj.options[merObj.selectedIndex].text.substr(0, merObj.options[merObj.selectedIndex].text.lastIndexOf(' - '));
	//var mername =  document.getElementById('merchant_search').value;
	var mername =  "[MERCHANT NAME]";
	
	var description = "";

	var template_sel=document.getElementById("gencontent_sel1").value;

	
	var re = /\[COUPON TITLE\]/g;
	template_sel = template_sel.replace(re, coupontitle);
	var re = /\[COUPON CODE\]/g;
	template_sel = template_sel.replace(re, couponcode);
	
	description = template_sel;
	
	document.getElementById('strict').value = description;

	return false;
}

function gencontent2()
{
	var codeObj = document.getElementById('couponCode');
	var couponcode = codeObj.value;
	
	var titleObj = document.getElementById('title');
	if(titleObj.value.trim().length < 1 || titleObj.value.trim().length > 255)
	{
		alert('coupon title can not be empty and should less than 255 characters');
		titleObj.focus();
		return;
	}
	var coupontitle = titleObj.value;
	
	var merObj = document.getElementById('merchant');
	if(merObj.value == 0)
	{
		alert('please choose a merchant');
		merObj.focus();
		return false;
	}
	
	//var mername =  merObj.options[merObj.selectedIndex].text.substr(0, merObj.options[merObj.selectedIndex].text.lastIndexOf(' - '));
	//var mername =  document.getElementById('merchant_search').value;
	var mername =  "[MERCHANT NAME]";
	
	var template_sel = "";
	
//	var template_sel=document.getElementById("gencontent_sel1").value;
	var gencontent_sel_obj = document.getElementById("gencontent_sel1");
	for(var i=0;i<gencontent_sel_obj.options.length;i++)
	{
    	if(gencontent_sel_obj.options[i].selected && gencontent_sel_obj.options[i].value != "")
		{
    		var regstr = gencontent_sel_obj.options[i].text;
//    		var reg =new RegExp(regstr,"g");
//    		if(!reg.test(template_sel)){
//    			template_sel = template_sel + " " +  regstr;
//    		}
    		if(template_sel != ""){
    			template_sel = template_sel + " " +  regstr;
    		}else{
    			template_sel = regstr;
    		}
    		
       }
	}
	
	var re = /\[COUPON TITLE\]/g;
	template_sel = template_sel.replace(re, coupontitle);
	var re = /\[COUPON CODE\]/g;
	template_sel = template_sel.replace(re, couponcode);
	
	description = template_sel;
	
	document.getElementById('strict').value = description;
	
	return false;
}

function new_gencontent()
{
//	$.each($("input[name='desc_tpl_title']:gt(0)").val(), function(i, n){
//		alert(i + ": " + n);
//	});
	//alert($("input[name='desc_tpl_title']:last").val());
	//alert($("#desc_tpl_title:gt(0)").val());
	//alert($("#desc_tpl_title:last").val());

	
}

function DoExpireDateTypeChange(obj, dateobj)
{
	if($("#change_date_info").attr('checked') != true && $("#show_block").val() == "coupon_edit"){
			return;
	}
	
	d = new Date();	
	var thisYear=d.getFullYear();
	var thisMonth=d.getMonth()+1;
	var thisDate=d.getDate();
	if (obj.value == '0'){
		dateobj.value = $("#couponstartDate").val();
		$("#expireDate_time").val("23:59:59");
	}else if (obj.value == 'never')
	{
		dateobj.value = '0000-00-00';
		$("#expireDate_time").val("00:00:00");
	}else if(obj.value == 'thisM'){
		setD = new Date(thisYear,thisMonth,0);
		if(thisMonth)	
		dateobj.value = thisYear+'-'+formatDate(thisMonth)+'-'+setD.getDate();//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == 'nextM'){		
		setD = new Date(thisYear,thisMonth+1,0);
		if(thisMonth>11){
			thisYear+=1;
		}
		dateobj.value = thisYear+'-'+formatDate(setD.getMonth()+1)+'-'+setD.getDate();//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == 'thisY'){
		setD = new Date(thisYear,0,0);
		dateobj.value = thisYear+'-'+formatDate(setD.getMonth()+1)+'-'+setD.getDate();//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == 'twoY'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year+2,month,date);		
		var newmonth = formatDate(setD.getMonth()+1);
		//if(newmonth == 0)newmonth = 12;
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == '3d'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year,month,date+3);
		var newmonth = formatDate(setD.getMonth()+1);		
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == '7d'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year,month,date+7);		
		var newmonth = formatDate(setD.getMonth()+1);		
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == '14d'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year,month,date+14);		
		var newmonth = formatDate(setD.getMonth()+1);		
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == '30d'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year,month,date+30);		
		var newmonth = formatDate(setD.getMonth()+1);		
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}else if(obj.value == '60d'){
		var startDate = $("#couponstartDate").val();
		var year = parseInt(startDate.substr(0,4), 10);
		var month = parseInt(startDate.substr(5,2), 10)-1;
		var date = parseInt(startDate.substr(8,2), 10);

		if (isLeapYear(year) && month == 2 && date == 29){
			date = 28;
		}		
		setD = new Date(year,month,date+60);		
		var newmonth = formatDate(setD.getMonth()+1);		
		dateobj.value = setD.getFullYear()+'-'+newmonth+'-'+formatDate(setD.getDate());//+' 23:59:59';
		$("#expireDate_time").val("23:59:59");
	}
}

function DoRemindDateTypeChange(obj)
{
	var remindDateType = $("#check_date").val();
	if(remindDateType>0){
		var days = parseInt(remindDateType, 10);
		d = new Date();	
		d.setDate(d.getDate()+ days);
		var thisYear=d.getFullYear();
		var thisMonth=d.getMonth()+1;
		
		obj.value = thisYear+'-'+thisMonth+'-'+formatDate(d.getDate());
	}else{
		obj.value = '0000-00-00';
	}
	
}

function isLeapYear(year){
	if((year %4==0 && year %100!=0) || (year %400==0)) return true;
	else return false;
}

function formatDate(date){
	if(date<10){
		date="0"+date;	
	}
	return date;
}

var reloadtag = function  ()
{
//	if(hideTag == 1){
//		return;
//	}
	getTags();

//	$("#Submit").hide();
//	$("#Submit").attr("disabled","ture");
//	$("#Reset").attr("disabled","ture");
	var catid;
	catid = $("#category option:selected").val();
	var merid;
	merid = $("#merchant").val();
	var couponid;
	couponid = $("#couponid").val();
//	var strwaiting = "<br><img src='/image/loading.gif'> Loading...<br><br>";
//	$("#tag_sel_html").html(strwaiting);
	var site = $("#site").val();
	$.ajax({
		type: "post",
//		data:{title:$("#title").val(),description:$("#description").val(),couponCode:$("#couponCode").val()},
		asynchronous: true,
		url: "/editor/coupon_search.php?action=rsynch-tag" + "&catid=" + catid + "&merid=" + merid + "&couponid=" + couponid + "&site=" + site,
		success: function (repstring) {
			if (repstring.length > 0) {
				$(".rsynch-tag").remove();
			    $("#tag_sel_html").after(repstring);
				if(hideTag==1) $(".rsynch-tag").hide();
//			    reloadalltag();
//			    $("#tag_sel_html").html("");
			}

//			if($("#tag_all_sel_html").html().length > 100)
//			{
//				$("#Submit").removeAttr("disabled");
//				$("#Reset").removeAttr("disabled");
//				$("#Submit").show();
//			}
		}
	});
}

//var deleted_seasonal_arr = [];
//var selected_seasonal_arr = [];

var regselected_seasonal_arr = [];
var regselected_all_arr = [];
var regselected_mertags_arr = [];
var regselected_othertags_arr = [];
var serach_keyword_other = "";
var serach_keyword_mer = "";
var reloadtag_change = function  (from, to, regselected_arr)
{
	var strobj = $("#couponCode").val()+" "+$("#title").val()+" "+$("#description").val();
	var fbox = document.getElementById(from);
	var tbox = document.getElementById(to);
	var site = $("#site").val();
	if(fbox.options.length > 0){
		for(var i=0;i<fbox.options.length;i++)
		{
			if(array_search(regselected_all_arr,fbox.options[i].value)){
				continue;
			}
			
			//de site, 14792:Gutschein would not be auto tag
			if(site == "csde" && fbox.options[i].value == '14792'){
				continue;
			}
			
			//if from data has alia,then check alia name
			var alianame = $("option",$("#"+from)).eq(i).attr("alia");
			if(alianame == "undefined"){
				alianame = "";
			}
			if(fbox.options[i].value != "")
			{
				var opstr = fbox.options[i].text;
				var regobj = new RegExp("\\b"+fbox.options[i].text+"\\b","gi");
				
				if(strobj.match(regobj) != null){
//					console.debug(opstr+">1>"+strobj.match(regobj));
//					fbox.options[i].selected = true;
					regselected_arr.push(fbox.options[i].value);
					regselected_all_arr.push(fbox.options[i].value);
					
					var newoption = new Option();
					newoption.value = fbox.options[i].value;
					newoption.text = fbox.options[i].text;
					tbox.options[tbox.options.length] = newoption;
					fbox.options[i].value = "";
					fbox.options[i].text = "";
					$("option","#"+from).eq(i).remove();
					if(alianame != ""){
						var indx = tbox.options.length -1;
						indx = indx < 1 ? 0 : indx;
						$("option","#"+to).eq(indx).attr('alia', alianame);
					}
					
				}else if(alianame != ""){
					regobj = new RegExp("\\b"+alianame+"\\b","gi");
					if(strobj.match(regobj) != null){
//						console.debug(alianame+">"+i+">>2>"+alianame.match(regobj));
						fbox.options[i].selected = true;
						regselected_arr.push(fbox.options[i].value);
						regselected_all_arr.push(fbox.options[i].value);
						
						var newoption2 = new Option();
						newoption2.value = fbox.options[i].value;
						newoption2.text = fbox.options[i].text;
						tbox.options[tbox.options.length] = newoption2;
						fbox.options[i].value = "";
						fbox.options[i].text = "";
						var indx = tbox.options.length -1;
						indx = indx < 1 ? 0 : indx;
						$("option","#"+to).eq(indx).attr('alia', alianame);
						$("option","#"+from).eq(i).remove();
					}

					
				}
				
				
	       }
		}
		BumpUp(fbox);
		
	}

	if(tbox.options.length > 0){
		for(var m=0;m<tbox.options.length;m++)
		{
			
			if(tbox.options[m].value != "" && array_search(regselected_arr,tbox.options[m].value))
			{
				
				var opstr = tbox.options[m].text;
				
				var regobj = new RegExp("\\b"+tbox.options[m].text+"\\b","gim");
				var alianame2 = $("option",$("#"+to)).eq(m).attr("alia");
				var regobj2 = new RegExp("\\b"+alianame2+"\\b","gi");
//				alert(opstr+">"+strobj.match(regobj));
				if(alianame2 == "undefined"){
					alianame2 = "";
				}
				
				var isdel = false;
				
				if(strobj.match(regobj) == null && ((strobj.match(regobj2) == null && alianame2 != "") || alianame2=="") ){
					isdel = true;
				}

				if(isdel && tbox.options.length > 0){
					tbox.options[m].selected = true;
					regselected_arr.splice(array_search(regselected_arr,tbox.options[m].value),1);
					regselected_all_arr.splice(array_search(regselected_all_arr,tbox.options[m].value),1);
					
					var newoption = new Option();
					newoption.value = tbox.options[m].value;
					newoption.text = tbox.options[m].text;
					
					fbox.options[fbox.options.length] = newoption;
					tbox.options[m].value = "";
					tbox.options[m].text = "";
					$("option","#"+to).eq(m).remove();
					if(alianame2 != ""){
						var indx2 = fbox.options.length -1;
						indx2 = indx2 < 1 ? 0 : indx2;
//						$("option","#"+from).eq(indx2).attr('alia', alianame2);
					}
				}
				
	       }
		}
		BumpUp(tbox);
		
	}
	
}

//when the list has not the tag,but the tag int table,because the list limit 100.so research then put in the other box. by devin
function research_tag(from, to,regselected_arr,actionname){
//	if(hideTag==1){
//		return;
//	}
	
	if(actionname == "" || null == actionname){
		actionname = "rsynch-search_othertag";
	}
	var serach_keyword = "";
	if(to=="list10"){
		serach_keyword = serach_keyword_mer;
	}
	if(to=="list6"){
		serach_keyword = serach_keyword_other;
	}

	var strobj = $("#couponCode").val()+" "+$("#title").val()+" "+$("#description").val();
	if(strobj == serach_keyword){
		return;
	}
	if(strobj != serach_keyword){
		serach_keyword = strobj;
	}
	
	if(to=="list10"){
		serach_keyword_mer = serach_keyword;
	}
	
	if(to=="list6"){
		serach_keyword_other = serach_keyword;
	}
	
	
	var merid = $("#merchant").val();
	var couponid = $("#couponid").val();
	var fbox = document.getElementById(from);
	var tbox = document.getElementById(to);
	var site = $("#site").val();	
	$.ajax({
    	type: "GET",
    	url: 'coupon_search.php?action='+actionname+'&site='+site+'&keyword='+encodeURIComponent(strobj)+"&merid="+merid+"&couponid="+couponid,
    	dataType: 'json',
    	async: false,
    	success: function(data){
    		if(null == data){
    			return;
    		}
    		var search_arr = [];	
	    	for(var i=0; i< data.length; i++){
	    		search_arr[i] =  data[i].id;
	    		if(!array_search(regselected_all_arr,data[i].id)){
		    		var newoption2 = new Option();
					newoption2.value = data[i].id;
					newoption2.text = data[i].name;
					tbox.options[tbox.options.length] = newoption2;
					var indx = tbox.options.length -1;
					indx = indx < 1 ? 0 : indx;
					$("option","#"+to).eq(indx).attr('alia', data[i].alias);
					regselected_arr.push(data[i].id);
					regselected_all_arr.push(data[i].id);
					if(hideTag==1) $(".rsynch-tag").hide();
		    	}
//	    		alert(data.length);
//	    		alert(data[0].name);
	    	}
    		
        }
	});
}

function array_search(array,str){
    if(typeof array !== 'object'){
            return false;
    }else{
            var found = [];
            for(var i in array){
                    if(array[i]==str){
                            found.push(i);
                    }
            }
            var num = found.length;
            if(num==0) return false;
            if(num==1) return found[0];
            return found;
    }
}


var reloadtagSubCategory = function  ()
{
	var catid;
	catid = $("#category option:selected").val();
	var merid;
	merid = $("#merchant").val();
	var couponid;
	couponid = $("#couponid").val();
	
	var site = $("#site").val();
//	alert("/editor/coupon_search.php?action=rsynch-sub-category" + "&catid=" + catid + "&merid=" + merid + "&couponid=" + couponid + "&site=" + site);
	$.ajax({
		type: "post",
		asynchronous: true,
		url: "/editor/coupon_search.php?action=rsynch-sub-category" + "&catid=" + catid + "&merid=" + merid + "&couponid=" + couponid + "&site=" + site,
		success: function (repstring) {
		
			if (repstring.length > 0) {
				$(".rsynch-tag-category").remove();
			    $("#tag_sel_html_category").after(repstring);
			    addEventOnCategory();
//			    undisplayAllCategory();
			}
		}
	});
}


var reloadalltag = function  ()
{
//	if(hideTag==1){
//		return;
//	}
	var couponid;
	couponid = $("#couponid").val();
	var site = $("#site").val();
//	alert("/editor/coupon_search.php?action=rsynch-alltag" + "&couponid=" + couponid + "&site=" + site);
	$.ajax({
		type: "post",
		asynchronous: true,
		url: "/editor/coupon_search.php?action=rsynch-alltag" + "&couponid=" + couponid + "&site=" + site,
		success: function (repstring) {
			if (repstring.length > 0) {
			    $("#tag_all_sel_html").after(repstring);
				if(hideTag==1) $(".rsynch-tag").hide();
			}
		}
	});
	$("#tag_all_sel_html").html('');

}

var func_selectsitewide = function()
{
	var sitewideselstr = $("#sitewide option:selected").val();
	if(sitewideselstr == 1)
	{
		if(confirm("Do you want to select all brands and tags under the merchant ?"))
		{
//			option_moveall('list1', 'list2');
			option_moveall('list9', 'list10');
		}
	}
}

var func_checkcouponcode = function ()
{
	$("#btn_checkcouponcode").attr("disabled","ture");
	var merid;
	merid = $("#merchant").val();
	var couponcode;
	couponcode = $("#couponCode").val();
	var couponid;
	couponid = $("#couponid").val();
	var merchantSearch = $("#merchant_search").val();
	if(!couponcode)
	{
		alert("There is no coupon code");
		$("#btn_checkcouponcode").removeAttr("disabled");
		return;
	}
	
	var site = $("#site").val();
	$("#checkcouponcode").html("<img src='/image/loading.gif'> Checking...");
	$.ajax({
		type: "get",
		asynchronous: true,
		url: "/editor/coupon_search.php?action=rsynch-chk-couponcode" + "&couponcode=" + encodeURIComponent(couponcode)+ "&merchantSearch=" + encodeURIComponent(merchantSearch) + "&merchantid=" + merid + "&couponid=" + couponid+ "&site=" + site ,
		success: function (repstring) {
			if (repstring.length > 0) {
			    $("#checkcouponcode").html(repstring);
			}
			$("#btn_checkcouponcode").removeAttr("disabled");
		}
	});
}

$(document).ready(function(){

	$("input[type=text]").keyup(function(){
		checkSpace(this);
	});
	$("textarea").keyup(function(){
		checkSpace(this);
	});

	$("#merchant").val($("#merchant_id").val());
	loadtips();
//	reloadalltag();
	
		
//		multipleAddOnclickOnSearch($("#site").val());
	reloadtag();
	/*if (typeof(eval("loadAffiliate")) == "function") {
		loadAffiliate();
	}*/
	reloadtagSubCategory();
	changeExpireType();
	expireOnClick();
	checkMerchantAff();
	try {
		if (typeof(eval("checkLindShare")) == "function") {
			checkLindShare();
		}
	} catch (e) {
		
	}
//	checkLindShare();
	$("#merchant_search").change(function(){reloadtag();checkMerchantAff();});
	$("#category").change(function(){
	reloadtagSubCategory();
	});
	$("#sitewide").change(function(){func_selectsitewide()});
	$("#btn_checkcouponcode").click(function(){func_checkcouponcode()});
	
	printable_sel();
	$(".onlinestate").click(function(){
		printable_sel();
	});
	$("input[name='type']").click(function(){
		printable_sel();
	});
	
	$("#title").mouseout(function(){
		do_tag_change();
	});

	$("#description").mouseout(function(){
		do_tag_change();
	});
	
	$("#couponCode").mouseout(function(){
		do_tag_change();
	});
	$("#title").change(function(){
		do_tag_change();
	});
	
	$("#description").change(function(){
		do_tag_change();
	});
	
	$("#couponCode").change(function(){
		do_tag_change();
	});
	
//	$("#title").blur(function(){
//		do_tag_change();
//	});
//	
//	$("#description").blur(function(){
//		do_tag_change();
//	});
//	
//	$("#couponCode").blur(function(){
//		do_tag_change();
//	});
	

	if($("#isDynamicCode").attr("dycode") == 1){
		$("#couponCode").attr("readonly","readonly");
		$("#isDynamicCode").attr("checked","checked");
	}
	
		if(window.navigator.userAgent.indexOf("MSIE") >= 1){
			set_preview_logo()
			$("#logo").change(function(){ 
					set_preview_logo();
				});
		}else{
			$("#logo").change(function(){
				handleFiles(this.files);
			});
		}
	if(hideTag==1){
		$(".tagbox").hide();
	}
	//$(".cursorp").css('cursor','pointer');
	$(".autocheck").click(function(){
		//$(this).find('input[type="checkbox"]').click();
		//$(this).find('input').eq(0).click();
		//alert($(this).find('input[type="checkbox"]').attr('checked'));
		/*if($(this).find('input[type="checkbox"]').attr('checked')==true){
			$(this).find('input[type="checkbox"]').attr('checked',false);
		}else{
			$(this).find('input[type="checkbox"]').attr('checked',true);
		}*/
		//$(this).find('input[type="checkbox"]').click();
	});
});

function do_tag_change(){
//	if(hideTag==1){
//		return;
//	}
	reloadtag_change("list7","list8",regselected_seasonal_arr);
	reloadtag_change("list9","list10",regselected_mertags_arr);
//	research_tag("list9","list10",regselected_mertags_arr,"rsynch-search_merchantag");
	reloadtag_change("list5","list6",regselected_othertags_arr);
//	research_tag("list5","list6",regselected_othertags_arr);
}

function printable_sel(){
	if($(".onlinestate").eq(0).attr('checked')==false && $(".onlinestate").eq(1).attr('checked')==false){
		$(".onlinestate").eq(0).attr('checked','checked');
		alert('Scope of Application need!');
		return false;
	}
	if($("input[name='type']:checked").val()==2){
		$("#aff_printable").hide();
		change_title(2);
	}else{
		change_title(1);
	}
	if($(".onlinestate").eq(1).attr('checked')==true){
		if($("input[name='type']:checked").val()==1) $('#aff_printable').show();
	}else{
		$('#aff_printable').hide();
	}
	if($(".onlinestate").eq(0).attr('checked')==true){
		$('#aff_normal').show();
	}else{
		$('#aff_normal').hide();
	}
}

function change_title(type){
	var typename = new Array();
	typename[1] = 'Coupon';
	typename[2] = 'Deal';
	var title = $("title").html();
	if(title.indexOf('Edit')>-1){
		var act = 'Edit';
	}
	if(title.indexOf('Add')>-1){
		var act = 'Add';
	}
	var othertype = type==1?2:1;
	$("title").html(act+' '+typename[type]);
	$("h1").eq(0).html($("h1").eq(0).html().replace(act+' '+typename[othertype],act+' '+typename[type]));
	for(var i=0;i<$("td.td_label").length;i++){
		$("td.td_label").eq(i).html($("td.td_label").eq(i).html().replace(typename[othertype],typename[type]));
	}
	if(type==1) $("#couponCode").parent().parent().show();
	if(type==2){
		$("#couponCode").parent().parent().hide();
		$("#couponCode").val("");
	}
}

function getTags(){
//	if(hideTag==1){
//		return;
//	}
	var merchantId = $("#merchant").val();
	var site = $("#site").val();
	$.ajax({
		type: "post",
		asynchronous: true,
		url: "/editor/coupon_search.php?action=rsynch-getMerchantRelatedTags" + "&merchantId=" + merchantId  + "&site=" + site ,
		success: function (tags) {
			$("#MerchantRelatedTags").html(tags);
			$("#MerchantRelatedTags_backup").html(tags);
			if(hideTag==1) $(".rsynch-tag").hide();
		}
	});
}

function loadtips(){	
	var verifyArr  = {'ajaxTag':'merchantName'};
	var site = $("#site").val();
	if(site == ""){
		return false;
	}
	$.ajax({type: "POST",
		url: "/editor/coupon_search.php?ajaxTag=getMerchantTips&q="+encodeURIComponent($("#merchant_search").val()) + "&site=" + site ,
		data: $.param(verifyArr),
		success: function(msg){
			
			$("#merchant_tips").html(msg);
		}					   
	});
	BlackKeyWords();
}

function BlackKeyWords(){
	var site = $("#site").val();
	if(site == ""){
		return false;
	}
	
	$.ajax({type: "POST",
		url: "/editor/coupon_search.php?ajaxTag=BlackKeyWords&q="+escape($("#merchant").val()) + "&site=" + site ,
		success: function(msg){
			if(msg == 'YES'){
				var a = "/editor/blacklist_list.php?scope=MERCHANT&merchantid="+$("#merchant").val()+"&setting_type=MERCHANT&site="+site;
				$("#BlackKeyWords").attr('href',a);
				$("#BlackKeyWords").show();  
			}
		}					   
	});
}

function checkMerchantAff(){
	var site = $("#site").val();
	$.ajax({type: "POST",
		url: "/editor/coupon_search.php?ajaxTag=checkMerchantAff&q="+escape($("#merchant").val()) + "&site=" + site ,		
		success: function(msg){
			if($("#deepurl").html() == ""){
				$(".is_deep").hide();
				$(".non_deep").show();
			}else{		
				$(".non_deep").hide();
				$(".is_deep").show();
			}
			if(msg == 1){
				$("#non_aff").hide();
				$("#is_aff").show();
				$(".non_aff").hide();
			}else{
				$("#is_aff").hide();
				$("#non_aff").show();
				$(".non_aff").show();
				$(".is_deep").hide();
				$(".non_deep").hide();
				
				//for cpq add 
				if($("#cpq_affurl").val() != "" && $("#non_aff").css("display") != "none" && $("#c_dst_url").val() == ""){
					$("#c_dst_url").val($("#cpq_affurl").val());
				}
			}
		}					   
	});
}

function merchantResTagsDispCtrlShow(){
	var ctl = $("#tagShowHide").val();
	if(ctl == "show"){
		$("#MerchantResTagsDispCtrl").show();
		$("#tagShowHide").val("hide");
		$("#historyTagsShow").hide();
//		$("#historyTagsShow").html("Less");
		$("#MerchantResTagsDispCtrl").after('<a id="historyTagsShowMore" href="#" onclick="return merchantResTagsDispCtrlShow();">Less</a>');
	}else{
		$("#MerchantResTagsDispCtrl").hide();
		$("#tagShowHide").val("show");
		$("#historyTagsShow").show();
		$("#historyTagsShowMore").remove();
//		$("#historyTagsShow").html("More");
	}
	return false;
}

function addHistoryTags(tag,count){
	tag_temp=$("#newtag").val();
	if(tag_temp!=""){
		tag_temp+=",";
	}
	$("#newtag").val(tag_temp+tag);
	$("#histtory_tag_"+count).css("display","none");
}

function addAllHistoryTags(){
	var tag_temp=$("#newtag_temp").html();	
	$("#newtag").val(tag_temp);
	$("#MerchantRelatedTags").css("display","none");
	return false;
}

function addCouponbundleList(fboxid){
	fbox = document.getElementById(fboxid);	
	for(var i=0;i<fbox.options.length;i++)
	{
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
    		document.getElementById("couponbundle_list").value+=","+fbox.options[i].value;
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
	update_couponbundle_list();
}

function update_couponbundle_list()
{
	var objSel = document.getElementById('couponbundle');
	var arr_id = new Array();
	for(var i=0;i<objSel.options.length;i++)
	{
		var v = objSel.options[i].value;
		if(!v) continue;
		arr_id[arr_id.length] = v;
	}
	var list_val = document.getElementById('couponbundle_list');
	list_val.value = arr_id.join(",");
	if(list_val.value!=""){
		list_val.value = ","+list_val.value+",";
	}
	SortD(couponbundle);
	SortD(merchantbundle);
}

function SortD(box)
{
	var temp_opts = new Array();
	var temp = new Object();
	for(var i=0; i<box.options.length; i++)
	{
		temp_opts[i] = box.options[i];
	}

	for(var x=0; x<temp_opts.length-1; x++)
	{
		for(var y=(x+1); y<temp_opts.length; y++)
		{
			if(temp_opts[x].text > temp_opts[y].text)
			{
				temp = temp_opts[x].text;
				temp_opts[x].text = temp_opts[y].text;
				temp_opts[y].text = temp;
				temp = temp_opts[x].value;
				temp_opts[x].value = temp_opts[y].value;
				temp_opts[y].value = temp;
			}
		}
	}

	for(var i=0; i<box.options.length; i++)
	{
		box.options[i].value = temp_opts[i].value;
		box.options[i].text = temp_opts[i].text;
	}
}

function toggle(div){
	$('#'+div).toggle();
}

function filterTagLetter(){	
	if(hideTag==1){
		return;
	}
	
	var startwith = $("#tagfilter").val();
	var verifyArr  = {'ajaxTag':'getTagsByLetter'};
	
	var temp_html = $("#all_tags_filter").html();
	$("#all_tags_filter").hide();
	$("#all_tags_filter_loading").show();
	var site = $("#site").val();
	$.ajax({
		type: "post",		
		url: "/editor/coupon_search.php?ajaxTag=getTagsByLetter" + "&startwith=" + startwith + "&site=" + site ,
		data: $.param(verifyArr),
		success: function (tags) {			
			$("#list5").html(tags);
			$("#all_tags_filter_loading").hide();
			$("#all_tags_filter").show();	
			if(hideTag==1) $(".rsynch-tag").hide();
		}		
	});	
}

function filterPromotionDetail(){
	var pro_detail=$("#pro_detail").val();
	if(pro_detail == "percent"){
		$("#pro_off").show();
		$("#money_type").hide();
	}else if(pro_detail == "money"){		
		$("#pro_off").show();
		$("#money_type").show();
	}else{
		$("#pro_off").hide();
		$("#money_type").hide();
	}
}

function resetTags(){
	$("#MerchantRelatedTags").html($("#MerchantRelatedTags_backup").html());
	$("#MerchantRelatedTags").show();
	$("#newtag").val("");
	return false;
}

function calendartimeset(id,n){
	$("#"+id).val(n);
}

function checkDefaultTime(){	
	if($("#expireDate").val() != "0000-00-00"){
		$("#expireDate_time").val("23:59:59");	
	}else{
		$("#expireDate_time").val("00:00:00");	
	}
}
function toggleRecommend(e){
	if(e.checked){
		document.getElementById("Recommend_div").style.display = "block";
	}else{
		document.getElementById("Recommend_div").style.display = "none";
	}
	
}



function set_preview_logo()
{
	var obj = document.getElementById("logo");
	if(! obj.value) return;
	
	$("#previewlogo4calcsize").remove();
	$("#image_url").after('<img id="previewlogo4calcsize" style="position: absolute;left: -9999px;" src=""/>');

    obj.select();
    var imgsrc=document.selection.createRange().text;
    var previewlogo=document.getElementById("preview");
    previewlogo.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgsrc;
    
	$("#previewlogo4calcsize").attr("src",imgsrc);
	$("#previewlogo").attr("src",imgsrc);
    $("#preview").show();
}

function displayImage(container, dataURL) {
	var img = document.createElement('img');
	img.src = dataURL;
	img.style.width="200px";
	img.style.height="50px";
	
	//container.appendChild(img);
	container.empty();
	container.append(img);
}

function handleFiles(files) {
	for ( var i = 0; i < files.length; i++) {
		var file = files[i];
		var imageType = /image.*/;
		if (!file.type.match(imageType)) {
			continue;
		}
		var reader = new FileReader();
		reader.onload = function(e) {
			displayImage($('#preview'), e.target.result);
			$("#previewlogo4calcsize").remove();
			$("#image_url").after('<img id="previewlogo4calcsize" style="position: absolute;left: -9999px;" src=""/>');
			$("#previewlogo4calcsize").attr("src",e.target.result);
			$("#previewlogo").attr("src",e.target.result);
			$("#preview").show();
		}
		reader.readAsDataURL(file);
	}
}  


function changeExpireType(){
	var expireType = $("#expire_type").val();
	if(expireType == "Fixed"){
		$("#expire_date_tr").show();
		$("#start_date_tr").show();
		$("#check_date_tr").hide();
	}else if(expireType == "Unknown"){
		$("#expire_date_tr").hide();
		$("#start_date_tr").show();
		$("#check_date_tr").show();
		if($("#change_date_info").attr('checked') == true){
			$("#check_date").val("14");
		}
	}else if(expireType == "Never"){
		$("#expire_date_tr").hide();
		$("#start_date_tr").show();
		$("#check_date_tr").hide();
	}else{
		$("#expire_date_tr").hide();
		$("#start_date_tr").hide();
		$("#check_date_tr").hide();
	}
}

function checkExpireType(){
	var expireType = $("#expire_type").val();
	if(expireType == "Fixed"){
		return true;
	}else if(expireType == "Unknown"){
		if(confirm("Please confirm coupon Expire Date 'Unknown'?")){
			return true;
		}else{
			$("#expire_type").focus();
			return false;
		}
	}else if(expireType == "Never"){
		if(confirm("Please confirm coupon Expire Date 'Never Expire'?")){
			return true;
		}else{
			$("#expire_type").focus();
			return false;
		}
	}else{
		return true;
	}
}

function checkExpireReadable(){
	if($("#change_date_info").attr('checked') == true){
		$("#couponstartDate_picker").unbind("click");
		$("#couponstartDate_time_picker").unbind("click");
		$("#expireDate_picker").unbind("click");
		$("#expireDate_time_picker").unbind("click");
		
		$("#couponstartDate").attr("readonly", false);
		$("#couponstartDate_time").attr("readonly", false);
		$("#expireDate").attr("readonly", false);
		$("#expireDate_time").attr("readonly", false);
		$("#expire_type").attr("disabled", false);
		$("#check_date").attr("disabled", false);
		$("#couponstartDate_picker").bind("click", function(){WdatePicker({el:'couponstartDate'})});
		$("#couponstartDate_time_picker").bind("click", function(){WdatePicker({el:'couponstartDate_time',dateFmt:'HH:mm:ss'})});
		$("#expireDate_picker").bind("click", function(){WdatePicker({el:'expireDate'})});
		$("#expireDate_time_picker").bind("click", function(){WdatePicker({el:'expireDate_time',dateFmt:'HH:mm:ss'})});
		$("#remindDate_picker").bind("click", function(){WdatePicker({el:'remindDate'})});
	}else{
		$("#couponstartDate_picker").unbind("click");
		$("#couponstartDate_time_picker").unbind("click");
		$("#expireDate_picker").unbind("click");
		$("#expireDate_time_picker").unbind("click");
		
		$("#couponstartDate").attr("readonly", true);
		$("#couponstartDate_time").attr("readonly", true);
		$("#expireDate").attr("readonly", true);
		$("#expireDate_time").attr("readonly", true);
		$("#expire_type").attr("disabled", true);
		$("#check_date").attr("disabled", true);
	}
	
}
function expireOnClick(){
	var action = $("#show_block").val();
	
	if(action != "coupon_edit"){
		//add
		$("#couponstartDate_picker").bind("click", function(){WdatePicker({el:'couponstartDate'})});
		$("#couponstartDate_time_picker").bind("click", function(){WdatePicker({el:'couponstartDate_time',dateFmt:'HH:mm:ss'})});
		$("#expireDate_picker").bind("click", function(){WdatePicker({el:'expireDate'})});
		$("#expireDate_time_picker").bind("click", function(){WdatePicker({el:'expireDate_time',dateFmt:'HH:mm:ss'})});
		$("#remindDate_picker").bind("click", function(){WdatePicker({el:'remindDate'})});
		$("#couponstartDate").attr("readonly", false);
		$("#couponstartDate_time").attr("readonly", false);
		$("#expireDate").attr("readonly", false);
		$("#expireDate_time").attr("readonly", false);
		$("#expire_type").attr("disabled", false);
		$("#check_date").attr("disabled", false);
	}else{
		//edit
		$("#couponstartDate").attr("readonly", true);
		$("#couponstartDate_time").attr("readonly", true);
		$("#expireDate").attr("readonly", true);
		$("#expireDate_time").attr("readonly", true);
		$("#expire_type").attr("disabled", true);
		$("#check_date").attr("disabled", true);

	}

}

function checkLindShare(){
	var merchantid = $("#merchant").val();
	var site = $("#site").val();
	var verifyArr = new Array();
//	alert("/editor/coupon_search.php?ajaxTag=checkLindShare" + "&merchantid=" + merchantid + "&site=" + site );
	$.ajax({
		type: "post",		
		url: "/editor/coupon_search.php?ajaxTag=checkLindShare" + "&merchantid=" + merchantid + "&site=" + site ,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "ISLS" || msg == "NODEEPURL"){
				$("#c_dst_url").unbind("blur");
				$("#c_dst_url").bind("blur", checkAffUrl);
				if(msg == "ISLS"){
					$("#ls_generate_url_span").show();
				}
			}else{
				
				$("#c_dst_url").unbind("blur");
				$("#ls_generate_url_span").hide();
			}
		}		
	});
}

function checkAffUrl(){
	var aff_url = $("#c_aff_url").val();
	aff_url = aff_url.trim();
	var dst = $("#c_dst_url").val();
	if(dst.trim() == ""){
//		alert("Please input Landing Page URL first.");
		return false;
	}
//	if(aff_url == ""){
//		if(confirm("Do you want system to generate AFF URL?")){
//			$("#c_dst_url_hidden").val(newDestUrl);
//			$("#url_load").remove();
//			$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
//			generateLSUrl();
//			return false;
//		}
//	}else{
		if($("#couponid").val() != "0" && $("#couponid").val() != ""){
			oldUrl = getOldUrl();
		}
//	}
}
function getOldUrl(){
	var couponid = $("#couponid").val();
	var site = $("#site").val();
	
	
	
	var verifyArr = new Array();
	$("#url_load").remove();
	$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
	
	var couponid = $("#couponid").val();
	var site = $("#site").val();
	var oldDestUrl = $("#c_dst_url_hidden").val();
	var newDestUrl = $("#c_dst_url").val();
	var c_aff_url = $("#c_aff_url").val();
	if(oldDestUrl.trim() == newDestUrl.trim()){
		$("#url_load").remove();
		return false;
	}else{
		 $("#c_dst_url_hidden").val(newDestUrl);
		 var msg = "";
		 if(c_aff_url.trim() == ""){
			 msg = "Do you want system to re-generate AFF URL?";
		 }else{
			 msg = "System detected you have changed the LP URL, do you want system to re-generate AFF URL?";
		 }
		
		if(confirm(msg)){
			$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
			generateLSUrl();
		}
		
	}
	$("#url_load").remove();
	
//	alert("/editor/coupon_search.php?ajaxTag=checkLindShare" + "&merchantid=" + merchantid + "&site=" + site );
	/*
	$.ajax({
		type: "post",		
		url: "/editor/coupon_search.php?ajaxTag=getOldUrl" + "&couponid=" + couponid + "&site=" + site ,
		data: $.param(verifyArr),
		success: function (msg) {
			
//			var newUrl = $("#c_aff_url").val();
			var newUrl = $("#c_dst_url").val();
			if(newUrl.trim() == msg.trim()){
				$("#url_load").remove();
				return false;
			}else{
				if(confirm("System detected you have changed the LP URL, do you want system to re-generate AFF URL?")){
					$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
					generateLSUrl();
					
				}
			}
			$("#url_load").remove();
		}		
	});
	*/
}

function generateLSUrl(){
	
	var couponid = $("#couponid").val();
	var merchantid = $("#merchant").val();
	var url = encodeURIComponent($("#c_aff_url").val());
	var dsturl = encodeURIComponent($("#c_dst_url").val());
	url = url.replace("&", "%26");
	dsturl = dsturl.replace("&", "%26");
	
	var site = $("#site").val();
	var verifyArr = new Array();
	var url =  "/editor/coupon_search.php?ajaxTag=genreateLSUrl" + "&couponid=" + couponid + "&dsturl=" + dsturl + "&merchantid=" + merchantid + "&url=" + url + "&site=" + site ;
//	alert(url);
	$.ajax({
		type: "post",		
		url: url,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "error"){
				alert("Generate AFF URL failed.");
			}else{
				$("#c_aff_url").val(msg);
				alert("Generate AFF URL successfully; please preview AFF URL to make sure it works well.");
			}
			$("#url_load").remove();
		}		
	});
}


function GenerateFromDeepUrlTpl(){
	
	var couponid = $("#couponid").val();
	var merchantid = $("#merchant").val();
	var url = encodeURIComponent($("#c_aff_url").val());
	var dst = $("#c_dst_url").val();
	if(dst.trim() == ""){
		alert("Please input Landing Page URL first.");
		return false;
	}
	var dsturl = encodeURIComponent($("#c_dst_url").val());
	url = url.replace("&", "%26");
	dsturl = dsturl.replace("&", "%26");
	$("#url_load").remove();
	$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
	var site = $("#site").val();
	var verifyArr = new Array();
//	alert( "/editor/coupon_search.php?ajaxTag=genreateLSdeepUrl" + "&couponid=" + couponid + "&dsturl=" + dsturl + "&merchantid=" + merchantid + "&url=" + url + "&site=" + site);
	$.ajax({
		type: "post",		
		url: "/editor/coupon_search.php?ajaxTag=genreateLSdeepUrl" + "&couponid=" + couponid + "&dsturl=" + dsturl + "&merchantid=" + merchantid + "&url=" + url + "&site=" + site ,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "error"){
				alert("Generate AFF URL failed.");
			}else{
				$("#c_aff_url").val(msg);
				alert("Generate AFF URL successfully; please preview AFF URL to make sure it works well.");
			}
			$("#url_load").remove();
		}		
	});
}
function GenerateFromLsApi(){
	
	var couponid = $("#couponid").val();
	var merchantid = $("#merchant").val();
	var url = encodeURIComponent($("#c_aff_url").val());
	var dst = $("#c_dst_url").val();
	if(dst.trim() == ""){
		alert("Please input Landing Page URL first.");
		return false;
	}
	var dsturl = encodeURIComponent($("#c_dst_url").val());
	url = url.replace("&", "%26");
	dsturl = dsturl.replace("&", "%26");
	$("#url_load").remove();
	$("#c_aff_url").after('<img id="url_load" style="" src="/image/loading.gif">');
	var site = $("#site").val();
	var verifyArr = new Array();
	var url = "/editor/coupon_search.php?ajaxTag=genreateLSApi" + "&couponid=" + couponid + "&dsturl=" + dsturl + "&merchantid=" + merchantid + "&url=" + url + "&site=" + site ; 
//	alert(url);
	$.ajax({
		type: "post",		
		url: url,
		data: $.param(verifyArr),
		success: function (msg) {
			if(msg == "error"){
				alert("Generate AFF URL failed.");
			}else{
				$("#c_aff_url").val(msg);
				alert("Generate AFF URL successfully; please preview AFF URL to make sure it works well.");
			}
			$("#url_load").remove();
		}		
	});
}

function showImputNew(showid, hideid){
	$("#" + showid).show();
	$("#" + hideid).hide();
	$("#s_source").val("");
	
}
/*
function CtoH(obj)
{
	var str = obj.value;
	var result = "";
	var alertchars = "";
	
	for (var i = 0; i < str.length; i++)
	{
		var charcode = str.charCodeAt(i);
		if(charcode == 12288) result += String.fromCharCode(32); //space
		else if(charcode == 12290) result += String.fromCharCode(46); //period
		else if(charcode == 8216 || charcode == 8217 ) result += String.fromCharCode(39); //single quotation marks
		else if(charcode == 8220 || charcode == 8221) result += String.fromCharCode(34); //double quotation marks
		else if(charcode == 8212) result += String.fromCharCode(45); //dash
		else if(charcode == 65509) result += String.fromCharCode(165); //&#165; &yen; yen 
		else if(charcode == 8361 || charcode == 8364) result += String.fromCharCode(charcode);
		else if(charcode > 65280 && charcode < 65375) result += String.fromCharCode(charcode-65248);
		else
		{
			result += String.fromCharCode(charcode);
			if(charcode > 255)
			{
				if(alertchars) alertchars += "," + String.fromCharCode(charcode) + "(" + charcode + ")";
				else alertchars = String.fromCharCode(charcode) + "(" + charcode + ")";
			}
		}
	}
	obj.value = result;
	if(alertchars != "")
	{
		obj.focus();
		alert("there are some invalid chars: " + alertchars);
		return false;
	}
	return true;
}
*/
function CtoH2(obj)
{
	if($("#site").val() == "csde" || $("#site").val() == "csfr"){
		return false;
	}
	var str = obj.value;
	var result = "";
	var alertchars = "";
	var div_result = "";
	
	for (var i = 0; i < str.length; i++)
	{
		var charcode = str.charCodeAt(i);
		if(charcode == 8211)
		{
			result += String.fromCharCode(45);	//–
			div_result += String.fromCharCode(45);
		}
		else if(charcode == 711)
		{
			result += String.fromCharCode(711);	//ˇ
			div_result += String.fromCharCode(711);
		}
		else if(charcode == 8776)
		{
			result += String.fromCharCode(8776);	//≈
			div_result += String.fromCharCode(8776);
		}
		else if(charcode == 12288)
		{
			result += String.fromCharCode(32);	//space
			div_result += String.fromCharCode(32);
		}
		else if(charcode == 12290)
		{
			result += String.fromCharCode(46);	//period
			div_result += String.fromCharCode(46);
		}
		else if(charcode == 8216 || charcode == 8217 )
		{
			result += String.fromCharCode(39);	//single quotation marks
			div_result += String.fromCharCode(39);
		}
		else if(charcode == 8220 || charcode == 8221)
		{
			result += String.fromCharCode(34);	//double quotation marks
			div_result += String.fromCharCode(34);
		}
		else if(charcode == 8212)
		{
			result += String.fromCharCode(45);  //dash
			div_result += String.fromCharCode(45);
		}
		else if(charcode == 65509)
		{
			result += String.fromCharCode(165);  //&#165; &yen; yen 
			div_result += String.fromCharCode(165);
		}
		else if(charcode == 8361 || charcode == 8364)
		{
			result += String.fromCharCode(charcode); 
			div_result += String.fromCharCode(charcode);
		}
		else if(charcode > 65280 && charcode < 65375){
			result += String.fromCharCode(charcode-65248); 
			div_result += String.fromCharCode(charcode-65248);
		}
		else
		{
			result += String.fromCharCode(charcode);
			if(charcode > 255)
			{
				if(alertchars) alertchars += "," + String.fromCharCode(charcode) + "(" + charcode + ")";
				else alertchars = String.fromCharCode(charcode) + "(" + charcode + ")";
				
				div_result += "<span style='color:white;font-weight:900;background:#3297FD;padding-left:5px;padding-right:5px'>"+String.fromCharCode(charcode)+"</span>";
			}else{
				div_result += String.fromCharCode(charcode);
			}
		}
	}
	
	var div = "#ctoh"+$(obj).attr("id");	
	//if($(div).css("display") != "block"){	
		obj.value = result;
		if(alertchars != "")
		{
			alert("there are some invalid chars: " + alertchars);
			window.setTimeout(function(){obj.focus();},0);
//			obj.focus();
//			
			var div = "#ctoh"+$(obj).attr("id");
			if($("div").index($(div)) == -1){
				$(obj).after("<div id='ctoh"+$(obj).attr("id")+"' active='y' style='background:#FFFFCC;border:1px solid;border-color:#999;font-size:14px;color:#000;font-family:Courier;'></div>");
			}
			$(div).css({"width":$(obj).outerWidth()});
			$(div).html(div_result);
			$(div).show();
			return false;
			//$(obj).before("<div id='' onclick='$(this).hide()' style='width:"+$(obj).outerWidth()+";height:"+$(obj).outerHeight()+";background:#ffff00;position:absolute'>"+result+"</div>");
		}else{
			$(div).hide();
		}
	//}
	return true;
}

function loadAffiliate(){
	 var merchant = $("#merchant").val();
	 var site = $("#site").val();
	 var verifyArr = new Array();
	 var couponid = $("#couponid").val();
	 var url = "/editor/coupon_search.php?ajaxTag=getaffiliate&site=" + site + "&merchantid=" + merchant + "&couponid=" + couponid;;
	 $.ajax({
			type: "post",		
			url: url,
			data: $.param(verifyArr),
			success: function (msg) {
				$("#sel_affiliate").html(msg);
			}		
		});
}