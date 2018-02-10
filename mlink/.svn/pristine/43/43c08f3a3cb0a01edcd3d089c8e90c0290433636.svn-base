	function addnewprogramid(){
		if(checkObjId() == false){
			return false;
		}
		var _len = $("#merchantaffiateid_tr tr").length;
		var aff_newlinecount = $("#aff_newlinecount").val();
		aff_newlinecount =parseInt(aff_newlinecount) + 1;
		$("#aff_newlinecount").val(aff_newlinecount);
		var aff_maxlinenum = $("#aff_maxlinenum").val();
		var placement_tag_type = $("#placement_tag_type").val();
		aff_maxlinenum =parseInt(aff_maxlinenum) + 1;
		
		$("#aff_maxlinenum").val(aff_maxlinenum);
		var trStr = "";
		trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
		trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
		trStr = trStr + "<td><input type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' /></td>";
		trStr = trStr + "<td><input style='width:150px;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>";
		trStr = trStr + "ID: <input type='text' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/> <a href='javascript:vodi(0)' onclick='getCoupons(\"" + aff_maxlinenum + "\")'></a></td>";
		trStr = trStr + "<td><input type='text' style='width:280px;' id='lpurl_" + aff_maxlinenum + "' name='lpurl_" + aff_maxlinenum + "' value=''></td>";
		trStr = trStr + "<td><input type='text' style='width:280px;' id='affurl_" + aff_maxlinenum + "' name='affurl_" + aff_maxlinenum + "' value=''></td>";
		trStr = trStr + "<td><input type='text' style='width:80px;' id='coupon_count_" + aff_maxlinenum + "' name='coupon_count_" + aff_maxlinenum + "' value=''></td>";
		trStr = trStr + "<td align='center'>";
		trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
		trStr = trStr + "</td>";
		trStr = trStr +"</tr>";
	    $("#merchantaffiateid_tr").append(trStr);
	    $("#affname_newline_" + aff_maxlinenum).live("click", function(){
	    	var affname = $("#affname_newline_" + aff_maxlinenum).val();
	    	var placement_tag_type = $("#placement_tag_type").val();
	    	var site = $("#site").val();
		    	$("#affname_newline_" + aff_maxlinenum).autocomplete(
		    		'/editor/placement_search.php?act=merchantName&q=' + affname + "&site=" + site,
		    		{
		    			scrollHeight: 320,
		    			max: 3000,
		    			formatItem: formatItem,
		    			formatResult: formatResult,
		    			autoFill: false
		    		});
	    	
	   });
	  
	    $("#affname_newline_" + aff_maxlinenum).result(function(event, row, formatted){
	    	$("#affname_newline_" + aff_maxlinenum).val(row[1]);
	    	$("#hide_affname_newline_" + aff_maxlinenum).val(row[0]);
	    	
	    	if(row[3] == "InHouse" || row[0] == '11'){
	    		$("#howto_href_" + aff_maxlinenum).attr("href","#");
				$("#program_newline_" + aff_maxlinenum).attr("disabled",true);
				$("#program_newline_" + aff_maxlinenum).css({"background-color":"#B0B2B4"});
	        }else{
	        	$("#program_newline_" + aff_maxlinenum).attr("disabled",false);
	        	$("#howto_href_" + aff_maxlinenum).attr("href",row[2]);
				$("#program_newline_" + aff_maxlinenum).css({"background-color":"#FFFFFF"});
	        }
	    		    	
	    	if($("#page_name").val().trim() != "merchant"){
				$(".existlines").each(function(){
					if($(this).val() == row[0]){
						alert(row[1] + " is exist! Please reselect.");
						$("#hide_affname_newline_" + aff_maxlinenum).val("");
						$("#affname_newline_" + aff_maxlinenum).val("");
					}
				});
	
		    	var num = $("#aff_maxlinenum").val();
		        for(var i = 1; i <= num; i++){
					var affid = $("#hide_affname_newline_" + i).val();
					var id = $(this).attr("id");
					var idTmpArr = id.split("_");
					id = idTmpArr[2];
					if(id != i){
						if(row[0] == affid){
							alert(row[1] + " is exist! Please reselect.");
							$("#hide_affname_newline_" + aff_maxlinenum).val("");
							$("#affname_newline_" + aff_maxlinenum).val("");
						}
					}
		        }
	    	}
	    });
	}

	function removeAff(aff_maxlinenum){
		
		$("#tr_" + aff_maxlinenum).remove();
		var aff_newlinecount = $("#aff_newlinecount").val();
		aff_newlinecount =parseInt(aff_newlinecount) - 1;
		$("#aff_newlinecount").val(aff_newlinecount);
	}

	function checkObjId(){
		var object_type = $("#object_type").val();
		var page_name = $("#page_name").val();
		if(page_name == "tag" || page_name == "merchant" || page_name == "category"){
			if(object_type == "" || object_type == "0"){
				if(  page_name == "category"){
					alert("please select a category");
				}else{
					
					alert("please select a " + page_name + " .");
				}
				return false;
			}
			if(isNaN(parseInt(object_type)) == true){
				alert(page_name + " id error.");
				return false;
			}
		}
		if(page_name == "search"){
			if($("#confirmkeyword").val() != "confirm"){
				alert("Please confirm a keyword.");
				return false;
			}
		}
		return true;
	}
	function formatItem(row) {
		
		return row[1] + "(" + row[0] + ")";
	}
	function formatResult(row) {
		return row[0];
	}

	function openWinTestUrl(url){
		url = $("#url").val();
		openWin(url);
	}
	
	function merchantEditSave(){
		var urlObj = document.getElementById('url');
		if(!urlObj.value.trim())
		{
			alert("Url can not be empty.");
			urlObj.focus();
			return false;
		}
	
		var inputValue= $("#mer_taskupdatecycle").val();
		var inputNum = parseInt(inputValue);
		if(isNaN(inputNum)){
			alert("Task Update Cycle must be a number(1-60).");
			return false;
		}
		if(inputNum <= 0 || inputNum > 60){
			alert("Task Update Cycle must be a number(1-60).");
			return false;
		}
		
		if(!urlObj.value.trim().IsStartWithHttp())
		{
			alert("Url must starts with http://.");
			urlObj.focus();
			return false;
		}
		$('#merchantedit_form').submit();
		return false;
		
	}
	

	$(document).ready(function(){
		$("#object_type").result(function(event,row,formatted){
			var page = $("#page_name").val() ;
			if(page == "merchant"){
				$("#tagname_span").html("<a href='" + $("#front_url").val() + "/front/merchant.php?mid=" + row[0] + "&forcerefresh=true' target='_blank' style='color:black;'>" + row[1] + "(" + row[0] + ")</a>");
			}else if(page == "tag"){
				$("#tagname_span").html("<a href='" + $("#front_url").val() + "/front/tag.php?tagid=" + row[0] + "&forcerefresh=true' target='_blank' style='color:black;'>" + row[1] + "(" + row[0] + ")</a>");	
			}
			
			$("#check_all").attr("checked", false);
			loadlist();
		});
		initPlacementType();
		loadlist();
	
		bindTagAuto("object_type");
//		placementTagType(document.getElementById("placement_tag_type"));
	});
	function initPlacementType(){
		var page_name = $("#page_name").val();
		var placement_tag_type = $("#placement_tag_type").val();
		switch(page_name){
			case "tag":
				$("#placement_tag_type").val("FeaturedMerchantLogos");
				$("#act").val("tagpage_merchant_save");
				break;
			case "merchant":
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("merchantpage_coupon_save");
				break;
			
		}
	}
	
	function clearSearchKeyword(e){
		$("#confirmkeyword").val("");
	}
	function placementTagType(e){
		$("#message").html("");
		$("#object_type").val("");
		$("#tagname_span").html("");
		var page = $("#page_name").val();
		if(page == "merchant"){
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("merchantpage_coupon_save");
				
			}
		}
		if(page == "tag"){
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("tagpage_merchant_save");
				$("#head2").text("Merchant ID");
			}
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("tagpage_coupondeal_save");
				$("#head2").text("Coupon ID");
			}
//			loadlist();
		}
		if(page == "merchant"){
			if(e.value == "FeaturedCouponsDeals"){
				$("#head2").text("Coupon ID");
				$("#act").val("merchantpage_coupon_save");
			}
		}
		
	
		loadlist();
		clearTrAndData();
	}	
	
	function DateNow(){  
		var d, s = "";
		d = new Date();
		s += (d.getMonth() + 1) + "-";
		s += d.getDate() + "-";   
		s += d.getYear();
		return(s);
	}
	function submitFormTag(){
		if(checkFormInput() == false){
			return false;
		}
		var now = DateNow();
		var i = 1;
		var flag = true;
		
		if(!confirm("Save ?")){
			return false;
		}
		var act = $("#act").val().trim();
		var postStr  = "";
//		alert($("#act").val());
		$('#merchantedit_form').ajaxSubmit(function(data) {
					if(data != "success"){ 
						alert('Error(' + data + ')');
					}else{
						alert("Succeed to save.");
						object_type = $("#object_type").val();
						var page = $("#page_name").val();
						var placement_tag_type = $("#placement_tag_type").val();
						if(page == "tag" || page == "merchant"){
							loadTagMerchant(object_type);
						}
						return false;
					}
				});
		return false;
	}
	
	function checkFormInput(){
		var page_name = $("#page_name").val();
		var placement_tag_type = $("#placement_tag_type").val();
		switch(page_name){
			case "merchant":
				return checkMerchantCoupon();
				break;
			case "tag":
				return checkTag();
				break;
		}
	}

	
	function checkTag(){
		object_type = $("#object_type").val();
		if(object_type == ""){
			alert("Please select Tag ID.");
			return false;
		}
		if(isNaN(parseInt(object_type, 10)) == true){
			alert("Tag ID error!");
			return false;
		}
		if($("#placement_tag_type").val() == "FeaturedCouponsDeals"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				var line = $("#affname_newline_" + i).val();
				if(typeof(line) != "undefined"){
					var lineInt = parseInt(line, 10);
					if(isNaN(lineInt) == true){
						alert("Please input number.");
						$("#affname_newline_" + i).focus();
						return false;
					}
				}
			}
		}
		return true;
	}

	function checkTagMerchant(){
		var object_type = $("#object_type").val();
		var aff_maxlinenum = $("#aff_maxlinenum").val();
		var maxlinenum = parseInt(aff_maxlinenum, 10);
		var oldlinecount = $("#aff_oldlinecount").val();
		var oldLine = parseInt(oldlinecount, 10);
		if(object_type == ""){
			alert("Please input Tag ID!");
			return false;
		}
		var i = 1;
		for(i = 1; i <= maxlinenum; i++){
			if($("#order_newline_" + i).length > 0){
				var order = $("#order_newline_" + i).val();
				var orderFloat = parseFloat(order);
				if(isNaN(orderFloat)){
					alert("Order error(Line " + i + ")");
					return false;
				}
				if(i > oldLine){
					var hide_affname_newline = $("#hide_affname_newline_" + i).val();
					if(hide_affname_newline == ""){
						alert("Please reselect merchant form the input (Line " + i + ")");
						return false;
					}
				}
				var dateStr = $("#expiredate_" + i).val();
				if(!CheckData(dateStr)){
					alert("Date error (line " + i + ")");
					return false;
				}
			}else{
				continue;
			}
		}
	}
	
	function CheckData(cform){
		if (cform=="")
		{
			return false;
		} 
	    if (!formatTime(cform))
	    {
	    	return false;
	    } 
	    return true;
	}
	
	function formatTime(str)
	{
	  var   r   =   str.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);     
	  if(r==null) return   false;     
	  var  d=  new  Date(r[1],   r[3]-1,   r[4]);     
	  return  (d.getFullYear()==r[1]&&(d.getMonth()+1)==r[3]&&d.getDate()==r[4]);  
	}
	

	function bindTagAuto(id){
		$("#" + id).unbind("live");
		$("#" + id).unbind("click");
		$("#" + id).live("click", function(){
	    $(".ac_results").remove();
    	var affname = $("#" + id).val();
    	var type = $("#page_name").val();
    	var action = "";
    	if(type == "merchant"){
    		action = "merchantName";
    	}else if(type == "tag"){
    		action = "getTag";
    	}else if(type == "category"){
    		action = "categoryName";
    		return true;
    	}
    	var site = $("#site").val();
    	
//    	$("#" + id).live("click", function(){
	    	$("#" + id).autocomplete(
	    		'/editor/placement_search.php?act=' + action +'&q=' + affname + "&site=" + site,
	    		{
	    			scrollHeight: 320,
	    			max: 3000,
	    			formatItem: formatItem,
	    			formatResult: formatResult,
	    			autoFill: false
	    		});
		   });
//		});
	}

	function loadlist(){
		loadTagMerchant();
	}
	
	function selectAllCheckbox(){
		var check_all = $("#check_all").attr("checked");
        $("#merchantaffiateid_tr :checkbox").attr("checked", check_all);  
	}
	
	function clearTrAndData(){
		$(".csl_oldline").remove();
		$("#aff_oldlinecount").val("0");
		$("#aff_maxlinenum").val("0");
	}
	function loadTagMerchant(object_type){
		clearTrAndData();
		var pageName = $("#page_name").val();
		var site = $("#site").val();
		var objId = $("#object_type").val();
		var verifyArr = new Array();
		var url = "/editor/manual_department_store.php?act=getTagMerchantList" + "&objid=" + objId + "&site=" + site + "&pagename=" + pageName;
		
//		alert(url);
		$.ajax({
			type: "post",		
			url: url,
			data: $.param(verifyArr),
			success: function (tagmerchanttr) {		
				if(tagmerchanttr == "" || tagmerchanttr == "error"){
					return false;
				}
				var tempArr = tagmerchanttr.split("||||");
				$("#aff_oldlinecount").val(tempArr[0]);
				$("#aff_maxlinenum").val(tempArr[0]);
				$("#head_list").after(tempArr[1]);
			}		
		});	
	}
	
	
	function changePageName(e){
		$(".ac_results").remove();
		
		if(e.value == 'tag'){
			$("#act").val("tag");
			$("#merchantedit_form").submit();
		}else if(e.value == 'merchant'){
			$("#act").val("merchant");
			$("#merchantedit_form").submit();
		}
	}
	function changeSite(e){
		$(".ac_results").remove();
		if($("#page_name").val() == "tag"){
			$("#act").val("tag");
		}
		if($("#page_name").val() == "merchant"){
			$("#act").val("merchant");
		}
		$("#merchantedit_form").submit();
	}

	function loadPlacementType(){
		var settingtype = $("#setting_type").val();
		var pageType = $("#page_name").val();
		var site = $("#site").val();
		var verifyArr = new Array();
		$.ajax({
			type: "post",		
			url: "/editor/placement_search.php?act=getPlacementType" + "&settingtype=" + settingtype + "&site=" + site + "&q=" + pageType,
			data: $.param(verifyArr),
			success: function (tagmerchanttr) {		
				$("#placement_tag_type").parent().html(tagmerchanttr);
			}
		});	
	}
	function bindMerchantAuto(id){
		$("#" + id).unbind("live");
		$("#" + id).unbind("click");
		
		$("#" + id).live("click", function(){
    	var affname = $("#" + id).val();
    	$(".ac_results").remove();
    	$("#" + id).autocomplete(
    		'/editor/placement_search.php?act=merchantName&q=' + affname,
    		{
    			scrollHeight: 320,
    			max: 3000,
    			formatItem: formatItem,
    			formatResult: formatResult,
    			autoFill: false
    		});
	   });
	}
	
	function changeSettingType(e, url){
		if(e.value == "adsconfig"){
			 window.location.href=url; 
		}
	}
	function checkMerchantCoupon(){
		object_type = $("#object_type").val();
		if(object_type == ""){
			alert("Please select Merchant ID.");
			return false;
		}
		if(isNaN(parseInt(object_type, 10)) == true){
			alert("Merchant ID error!");
			return false;
		}
		if($("#placement_tag_type").val() == "FeaturedCouponsDeals"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				var line = $("#affname_newline_" + i).val();
				
				if(typeof(line) != "undefined"){
					var lineInt = parseInt(line, 10);
					if(isNaN(lineInt) == true){
						alert("Please input number.");
						$("#affname_newline_" + i).focus();
						return false;
					}
				}
			}
		}
		return true;
	}
	
	function getCoupons(index){
		var pageName = $("#page_name").val();
		var site = $("#site").val();
		var objId = $("#object_type").val();
		var verifyArr = new Array();
		var merchantid = $("#hide_affname_newline_" + index).val();
		$("#merchantid").val(merchantid)
		var url = "/editor/manual_department_store.php?act=getCoupons" + "&objid=" + objId + "&site=" + site + "&pagename=" + pageName + "&merchantid=" + merchantid;
		$.ajax({
			type: "post",		
			url: url,
			data: $.param(verifyArr),
			success: function (tagmerchanttr) {		
				if(tagmerchanttr != "error"){
					showHideDiv(index);
					$("#table_coupons").html(tagmerchanttr);
				}
			}		
		});
	}
	function showHideDiv(index){
		var speed = 500;
		  var offset = $("#hide_affname_newline_" + index).offset();
	      var pos = offset.top;
	      var scrollHeight = document.documentElement.clientHeight;
	      $("#divPop1").css({ top: pos + "px", left: offset.left + 30});
	      $("#divPop1").show();
	      return false;
	}

	function hidDiv(id){
		$("#" + id).hide();
	}
	
	function saveMerchantCoupons(){
		var pageName = $("#page_name").val();
		var site = $("#site").val();
		var objId = $("#object_type").val();
		var merchantid = $("#merchantid").val()
		var strid = "";
		
		$(".processtatuscheckbox:checked").each(function() {
                var id = $(this).attr("id");
                strid+=id+",";
		})
		var verifyArr =  {'couponids':strid};
		var url = "/editor/manual_department_store.php?act=saveMerchantCoupons" + "&objid=" + objId + "&site=" + site + "&pagename=" + pageName + "&merchantid=" + merchantid;
		$.ajax({
			type: "post",		
			url: url,
			data: $.param(verifyArr),
			success: function (tagmerchanttr) {		
				if(tagmerchanttr != "error"){
					alert(tagmerchanttr);
					$("#divPop1").hide();
				}
			}		
		});
	}