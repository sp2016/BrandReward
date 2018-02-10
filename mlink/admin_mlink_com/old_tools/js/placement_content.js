function CtoH(obj)
{
	alert(obj.value);
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
		}
	}
	obj.value = result;
	if(alertchars != "")
	{
		obj.focus();
		alert("There are some invalid chars: " + alertchars);
	}
}
function checkC(obj)
{
	if($("#site").val() == "csde"){
		return false;
	}
	var str = obj.value;
	var result = "";
	var alertchars = "";
	var div_result = "";
	
	for (var i = 0; i < str.length; i++)
	{
		var charcode = str.charCodeAt(i);
		if(charcode == 12288)
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
	
	function addnewprogramid(){
		if(checkObjId() == false){
			return false;
		}
		var reason_select_option = "<option selected='selected' value=''>No Reason</option><option value='Merchant Required'>Merchant Required</option><option value='Other'>Other</option>";
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
		trStr = trStr + "<td><input style='width:250px;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>";
		if(placement_tag_type == "FeaturedMerchantLogos" || placement_tag_type == "FeaturedTopTags" || placement_tag_type == "FeaturedStores" || placement_tag_type == "HotStores" || placement_tag_type == "FeaturedCouponsBackfill" || placement_tag_type == "FeaturedDealsBackfill"){
			trStr = trStr + "ID: <input type='text' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/></td>";
		}else{
			trStr = trStr + "<input type='hidden' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/></td>";
		}
		if(placement_tag_type == "FeaturedCouponsBackfill" || placement_tag_type == "FeaturedDealsBackfill"){
			trStr = trStr + "<td><input type='text' style='width:80px;' id='coupon_count_" + aff_maxlinenum + "' name='coupon_count_" + aff_maxlinenum + "' value=''></td>";
		}
		trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
		trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''>  <img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
		trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''>  <img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
		trStr = trStr + "<td align='center'>";
		trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
		trStr = trStr + "</td>";
		trStr = trStr +"</tr>";
		
		if(placement_tag_type == "DisplayCategorys"){
			var PlacementHtml = $("#category").html();
			trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
			trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
			trStr = trStr + "<td><select style='width:250px;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class'>" + PlacementHtml + "</select></td>";
			trStr = trStr + "<td><input type='text' name='dispcnt_newline_" + aff_maxlinenum + "' id='dispcnt_newline_" + aff_maxlinenum +  "' value='4'/></td>";
			trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''><img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''><img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td align='center'>";
			trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
			trStr = trStr + "</td>";
			trStr = trStr +"</tr>";
		}
		if(placement_tag_type == "MoreMenu" || placement_tag_type == "MoreMenuCoupon" || placement_tag_type == "MoreMenuDeal" || placement_tag_type == "MoreMenuLearnShare" || placement_tag_type == "Topic"){
			trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
			trStr = trStr + "<td align='center'><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' style='width:100px'/></td>";
			trStr = trStr + "<td><input style='width:98%;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class' value='' onblur='checkC(this)'/>";
			trStr = trStr + "<input type='hidden' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/></td>";
			trStr = trStr + "<td><input style='width:97%;' type='text' name='url_newline_" + aff_maxlinenum + "' id='url_newline_" + aff_maxlinenum + "' class='aff_class' value=''/><input type='checkbox' id='nofollow_" + aff_maxlinenum + "' name='nofollow_" + aff_maxlinenum + "' value='nofollow'>nofollow";
			trStr = trStr + "<td align='center'><select name='newwindow_newline_" + aff_maxlinenum + "' id='newwindow_newline_" + aff_maxlinenum + "' ><Option value='NO'>NO</option><Option value='YES'>YES</option></select>";
			trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''><img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''><img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td align='center'>";
			trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
			trStr = trStr + "</td>";
			trStr = trStr +"</tr>";
		}
		if(placement_tag_type == "RotateBanner" || placement_tag_type == "RightBanner" || placement_tag_type == "SeasonalBlock"){
			var isNesletter = $("#newsletterflg").val();
			if(isNesletter != "newsletter"){
				trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
				trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
				trStr = trStr + "<td><input style='width:95%;' type='file' name='pic_newline_" + aff_maxlinenum + "' id='pic_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>&nbsp;&nbsp</td>";
				trStr = trStr + "<td><input style='width:95%;' type='text' name='alt_newline_" + aff_maxlinenum + "' id='alt_newline_" + aff_maxlinenum + "' class='aff_class' value='' onblur='checkC(this)'/>&nbsp;&nbsp</td>";
				trStr = trStr + "<td><input style='width:95%;' type='text' name='url_newline_" + aff_maxlinenum + "' id='url_newline_" + aff_maxlinenum + "' class='aff_class' value=''/><br /	><input type='checkbox' id='nofollow_" + aff_maxlinenum + "' name='nofollow_" + aff_maxlinenum + "' value='nofollow'/>nofollow&nbsp;&nbsp";
				if(placement_tag_type == "RightBanner" && $("#site").val()== 'csca'){
					trStr = trStr + "<input type='checkbox' id='indexnotshow_" + aff_maxlinenum + "' name='indexnotshow_" + aff_maxlinenum + "' value='indexnotshow'>indexnotshow";
				}
				trStr = trStr + "</td>";
				trStr = trStr + "<td><select name='newwindow_newline_" + aff_maxlinenum + "' id='newwindow_newline_" + aff_maxlinenum + "' ><Option value='NO'>NO</option><Option value='YES'>YES</option></select></td>";
				trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
				trStr = trStr + "<td 'width:100px;'><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''><img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
				trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''><img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
				trStr = trStr + "<td align='center'>";
				trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
				trStr = trStr + "</td>";
				if(placement_tag_type == "RotateBanner"){
					trStr = trStr + "<td><input type='text' name='fix_pos_" + aff_maxlinenum + "' id='fix_pos_" + aff_maxlinenum +  "' style='width:50px;' /></td>";
				}
				trStr = trStr +"</tr>";
			}else{
				trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
				trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
				trStr = trStr + "<td></td>";
				trStr = trStr + "<td><input style='width:95%;' type='text' name='alt_newline_" + aff_maxlinenum + "' id='alt_newline_" + aff_maxlinenum + "' class='aff_class' value='newsletter' readonly/>&nbsp;&nbsp</td>";
				trStr = trStr + "<td><input style='width:95%;' type='hidden' name='url_newline_" + aff_maxlinenum + "' id='url_newline_" + aff_maxlinenum + "' class='aff_class' value='' readonly/>&nbsp;&nbsp</td>";
				trStr = trStr + "<td></td>";
				trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
				trStr = trStr + "<td 'width:100px;'><input type='hidden' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''></td>";
				trStr = trStr + "<td><input type='hidden' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''></td>";
				trStr = trStr + "<td align='center'>";
				trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
				trStr = trStr + "</td>";
				if(placement_tag_type == "RotateBanner"){
					trStr = trStr + "<td><input type='text' name='fix_pos_" + aff_maxlinenum + "' id='fix_pos_" + aff_maxlinenum +  "' style='width:50px;' /></td>";
				}
				trStr = trStr +"</tr>";
				$("#newsletterflg").val("");
			}
			
		}
		if(placement_tag_type == "HotSearches"){
			trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
			trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
			trStr = trStr + "<td><input style='width:250px;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>";
			trStr = trStr + "<input type='hidden' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/></td>";
			trStr = trStr + "<td><input style='width:95%;' type='text' name='url_newline_" + aff_maxlinenum + "' id='url_newline_" + aff_maxlinenum + "' class='aff_class' value=''/><input type='checkbox' id='nofollow_" + aff_maxlinenum + "' name='nofollow_" + aff_maxlinenum + "' value='nofollow'>nofollow";
//			trStr = trStr + "<td><select name='newwindow_newline_" + aff_maxlinenum + "' id='newwindow_newline_" + aff_maxlinenum + "' ><Option value='YES'>YES</option><Option value='NO'>NO</option></select>";
			trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
			trStr = trStr + "<td 'width:100px;'><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''><img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''><img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td align='center'>";
			trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
			trStr = trStr + "</td>";
			trStr = trStr +"</tr>";
		}
		if(placement_tag_type == "FooterKeywordLinks"){
			trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
			trStr = trStr + "<td><input type='text' name='order_newline_" + aff_maxlinenum + "' id='order_newline_" + aff_maxlinenum +  "' /></td>";
			trStr = trStr + "<td><input style='width:250px;' type='text' name='affname_newline_" + aff_maxlinenum + "' id='affname_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>";
			trStr = trStr + "<input type='hidden' style='background-color:#BFE484;' name='hide_affname_newline_" + aff_maxlinenum + "' id='hide_affname_newline_" + aff_maxlinenum + "' class='hide_aff_class' readonly value=''/></td>";
			trStr = trStr + "<td><input style='width:95%;' type='text' name='text_newline_" + aff_maxlinenum + "' id='text_newline_" + aff_maxlinenum + "' class='aff_class' value=''/>&nbsp;&nbsp";
			trStr = trStr + "<td><input type='text' style='width:250px;' name='url_newline_" + aff_maxlinenum + "' id='url_newline_" + aff_maxlinenum + "'/><input type='checkbox' id='nofollow_" + aff_maxlinenum + "' name='nofollow_" + aff_maxlinenum + "' value='nofollow'>nofollow";
			trStr = trStr + "<td style='display:none;'><input type='text' style='width:50px' name='urlid_newline_" + aff_maxlinenum + "' id='urlid_newline_" + aff_maxlinenum + "'/>";
			trStr = trStr + "<td><select name='reason_" + aff_maxlinenum + "' id='reason_" + aff_maxlinenum + "'>" + reason_select_option + "</select></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='startdate_" + aff_maxlinenum + "' name='startdate_" + aff_maxlinenum + "' value=''><img id='couponstartDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td><input type='text' class='expiredate_c' style='width:80px;' id='expiredate_" + aff_maxlinenum + "' name='expiredate_" + aff_maxlinenum + "' value=''><img id='couponexpireDate_picker_" + aff_maxlinenum + "' style='vertical-align:middle' src='/js/My97DatePicker/skin/datePicker.gif'/></td>";
			trStr = trStr + "<td align='center'>";
			trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
			trStr = trStr + "</td>";
			trStr = trStr +"</tr>";
		}
		
	    $("#merchantaffiateid_tr").append(trStr);
		$("#couponstartDate_picker_" + aff_maxlinenum).bind("click", function(){WdatePicker({el:'startdate_' + aff_maxlinenum})});
		$("#couponexpireDate_picker_" + aff_maxlinenum).bind("click", function(){WdatePicker({el:'expiredate_' + aff_maxlinenum})});
	    $("#affname_newline_" + aff_maxlinenum).live("click", function(){
		    
	    	var affname = $("#affname_newline_" + aff_maxlinenum).val();
	    	var placement_tag_type = $("#placement_tag_type").val();
	    	var site = $("#site").val();
	    	if( placement_tag_type == "FeaturedMerchantLogos" || placement_tag_type == "FeaturedStores" || placement_tag_type == "HotStores" || placement_tag_type == "FeaturedCouponsBackfill" || placement_tag_type == "FeaturedDealsBackfill" || placement_tag_type == "RelatedMerchant"){
		    	$("#affname_newline_" + aff_maxlinenum).autocomplete(
		    		'/editor/placement_search.php?act=merchantName&q=' + affname + "&site=" + site,
		    		{
		    			scrollHeight: 320,
		    			max: 3000,
		    			formatItem: formatItem,
		    			formatResult: formatResult,
		    			autoFill: false
		    		});
	    	}
	    	if( placement_tag_type == "FeaturedTopTags" || placement_tag_type == "DefaultRelatedTags" || placement_tag_type == "RelatedTag" ){
	    		$("#affname_newline_" + aff_maxlinenum).autocomplete(
	    				'/editor/placement_search.php?act=getTag&q=' + affname + "&site=" + site,
	    				{
	    					scrollHeight: 320,
	    					max: 3000,
	    					formatItem: formatItem,
	    					formatResult: formatResult,
	    					autoFill: false
	    				});
	    	}
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
		$("#act").val("default_page_save");
		switch(page_name){
			case "tag":
				$("#placement_tag_type").val("FeaturedMerchantLogos");
				$("#act").val("tagpage_merchant_save");
				break;
			case "merchant":
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("merchantpage_coupon_save");
				break;
			case "deal":
				$("#placement_tag_type").val("DisplayCategorys");
				$("#act").val("dealpage_category_save");
				break;
			case "coupon":
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("couponpage_coupon_save");
				break;
			case "exclusive":
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("exclusivepage_coupon_save");
				break;
			case "category":
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("category_page_coupon_save");
				break;
			case "homepage":
				$("#placement_tag_type").val("FeaturedCoupons");
				$("#act").val("homepage_FeaturedCoupons_save");
				break;
			case "sitewide":
				$("#placement_tag_type").val("MoreMenu");
				$("#act").val("all_MoreMenu_save");
				break;
			case "banner":
				$("#placement_tag_type").val("RotateBanner");
				break;
			case "search":
				$("#confirmkeyword").val("");
				$("#placement_tag_type").val("FeaturedCouponsDeals");
				$("#act").val("searchpage_coupondeal_save");
				break;
		}
	}
	
	function confirmKeyword(e, domain){
		var keyword = $("#object_type").val();
		var confirm = e.value
		if(keyword == ""){
			alert("Please input a keyword.");
			return false;
		}
		$("#confirmkeyword").val("confirm");
		loadlist();
		var site = $("#site").val();
		var url = domain + "/search-" + encodeURI($("#object_type").val()) + "-coupons-deals.html";
		if(site == "csde"){
			url = domain + "/" + encodeURI($("#object_type").val()) + "-Gutscheine" + "-Rabatte-suchen.html";
		}
		if(site == "csie" || site == "csau" || site == "csuk"){
			url = domain + "/search-" + encodeURI($("#object_type").val()) + "-vouchers-deals.html";
		}
		$("#fronturl").html("<a style='color:black;' href='" + url + "' target='_blank'>" + url + "</a>");
	}
	function clearSearchKeyword(e){
		$("#confirmkeyword").val("");
	}
	function addnewsletter(){
		$("#newsletterflg").val("newsletter");
		addnewprogramid();
	}
	function placementTagType(e){
		$("#addnewsletterbtn").remove();
		$("#message").html("");
		$("#object_type").val("");
		$("#tagname_span").html("");
		$("#act").val("default_page_save");
		var page = $("#page_name").val();
		if(page == "merchant"){
			if(e.value == "FeaturedCouponsDeals"){
				$("#head2").text("Coupon ID");
				$("#act").val("merchantpage_coupon_save");
			}
			if(e.value == "RelatedMerchant"){
				$("#head2").text("Merchant ID");
				$("#act").val("merchantpage_related_merchant");
			}
			
			if(e.value == "RelatedTag"){
				$("#head2").text("Tag ID");
				$("#act").val("merchantpage_related_tag");
			}
		}
		else if(page == "category"){
			$("#object_type").val("0");
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("category_page_coupon_save");
				$("#head2").text("Coupon ID");
			}
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("category_page_merchant_save");
				$("#head2").text("Merchant ID");
			}
			if(e.value == "FeaturedTopTags"){
				$("#act").val("category_page_tag_save");
				$("#head2").text("Tag ID");
			}
//			loadlist();
		}
		else if(page == "coupon"){
			$("#object_type").val("0");
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("coupon_page_coupon_save");
				$("#head2").text("Coupon ID");
			}
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("coupon_page_merchant_save");
				$("#head2").text("Merchant ID");
			}
//			loadlist();
		}
		else if(page == "deal"){
			$("#object_type").val("0");
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("deal_page_coupon_save");
				$("#head2").text("Coupon ID");
				$("#head6").parent().remove();
			}
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("deal_page_merchant_save");
				$("#head2").text("Merchant ID");
				$("#head6").parent().remove();
			}
			if(e.value == "DisplayCategorys"){
				$("#act").val("dealpage_category_save");
				$("#head2").text("Category ID");
				$("#head2").parent().after("<th width=\"140px\"><span id=\"head6\">Disp Deal Cnt</span></th>");
				
			}
//			loadlist();
		}
		else if(page == "exclusive"){
			$("#object_type").val("0");
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("exclusive_page_coupon_save");
				$("#head2").text("Coupon ID");
			}
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("exclusive_page_merchant_save");
				$("#head2").text("Merchant ID");
			}
//			loadlist();
		}
		else if(page == "tag"){
			if(e.value == "FeaturedMerchantLogos"){
				$("#act").val("tagpage_merchant_save");
				$("#head2").text("Merchant ID");
			}
			if(e.value == "FeaturedCouponsDeals"){
				$("#act").val("tagpage_coupondeal_save");
				$("#head2").text("Coupon ID");
			}
			if(e.value == "RelatedTag"){
				$("#head2").text("Tag ID");
				$("#act").val("tagpage_related_tag");
			}
//			loadlist();
		}		
		else if(page == "search"){
			if(e.value == "FeaturedCouponsDeals"){
				$("#head2").text("Coupon ID");
				$("#act").val("searchpage_coupondeal_save");
			}else if(e.value == "FeaturedMerchantLogos"){
				$("#head2").text("Merchant ID");
				$("#act").val("searchpage_merchant_save");
			}
		}
		else if(page == "sitewide"){
			
			if(e.value == "MoreMenu"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Text</span></th><th><span id="head2">URL|Nofollow</span></th><th width="60px"><span id="head2">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_MoreMenu_save");
			}else if(e.value == "MoreMenuCoupon"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Text</span></th><th width="25%"><span id="head2">URL|Nofollow</span></th><th width="60px"><span id="head2">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_MoreMenuCoupon_save");
			}else if(e.value == "MoreMenuDeal"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Text</span></th><th width="25%"><span id="head2">URL|Nofollow</span></th><th width="60px"><span id="head2">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_MoreMenuDeal_save");
			}else if(e.value == "MoreMenuLearnShare"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Text</span></th><th width="25%"><span id="head2">URL|Nofollow</span></th><th width="60px"><span id="head2">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_MoreMenuLearnShare_save");
			}else if(e.value == "FooterKeywordLinks"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Keyword Text</span></th><th width="300px">Title</th><th>URL|Nofollow</th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_FooterKeywordLinks_save");
			}else if(e.value == "HotSearches"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Keyword Text</span></th><th >Destination URL|Nofollow</th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_HotSearches_save");
			}else if(e.value == "DefaultRelatedTags"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th ><span id="head2">Tag ID</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("DefaultRelatedTags_save");
			}else if(e.value == "Topic"){
				$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="285px"><span id="head2">Text</span></th><th width="25%"><span id="head2">URL|Nofollow</span></th><th width="60px"><span id="head2">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
				$("#act").val("all_MoreMenu_save");
			}
		}
		else if(page == "banner"){
			
			switch(e.value){
				case "RotateBanner":
					$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="260px"><span id="head2">Picture</span></th><th width="260px"><span id="head3">Alt</span></th><th><span id="head4">URL|Nofollow</span></th><th width="60px"><span id="head5">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head6">Start Date</span></th><th width="120px"><span id="head7">Expire Date</span></th><th width="100px"><span id="head8">Action &nbsp;&nbsp;<input id="check_all" type="checkbox" value="" name="check_all" onclick="selectAllCheckbox();"/></span></th><th width="100px"><span id="head11">Fix Position</span></th>');
					$("#act").val("default_page_save");
					break;
				case "RightBanner":
					$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="260px"><span id="head2">Picture</span></th><th width="260px"><span id="head3">Alt</span></th><th><span id="head4">URL|Nofollow|indexnotshow</span></th><th width="60px"><span id="head5">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head6">Start Date</span></th><th width="120px"><span id="head7">Expire Date</span></th><th width="100px"><span id="head8">Action &nbsp;&nbsp;<input id="check_all" type="checkbox" value="" name="check_all" onclick="selectAllCheckbox();"/></span></th>');
					$("#addnewbtn").after('<input type="button" value="Add Newsletter" id="addnewsletterbtn" onclick="addnewsletter();"/>');
					$("#act").val("default_page_save");
					break;
			}
		}
		else if(page == "homepage"){
			switch(e.value){
				case "FeaturedCoupons":
					$("#head2").text("Coupon ID");
					$("#act").val("homepage_FeaturedCoupons_save");
					$("#head6").parent().remove();
					break;
				case "FeaturedDeals":
					$("#head2").text("Coupon ID");
					$("#act").val("homepage_FeaturedDeals_save");
					$("#head6").parent().remove();
					break;
				case "FeaturedStores":
					$("#head2").text("Merchant ID");
					$("#act").val("homepage_FeaturedStores_save");
					$("#head6").parent().remove();
					break;
				case "FeaturedTopTags":
					$("#head2").text("Tag ID");
					$("#act").val("homepage_FeaturedTopTags_save");
					$("#head6").parent().remove();
					break;
				case "FeaturedCouponsBackfill":
					$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th><span id="head2">Merchant ID</span></th><th width="100px"><span id="head6">Promotion Count</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
					$("#act").val("homepage_FeaturedCouponsBackfill_save");
					break;
				case "FeaturedDealsBackfill":
					$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th><span id="head2">Merchant ID</span></th><th width="100px"><span id="head6">Promotion Count</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head4">Start Date</span></th><th width="120px"><span id="head4">Expire Date</span></th><th width="100px"><span id="head5">Action &nbsp;&nbsp;<input type="checkbox" onclick="selectAllCheckbox();" name="check_all" value="" id="check_all"></span></th>');
					$("#act").val("homepage_FeaturedDealsBackfill_save");
					break;
				case "RotateBanner":
					//TODO
					break;
				case "HotStores":
					$("#head2").text("Merchant ID");
					$("#act").val("homepage_HotStores_save");
					$("#head6").parent().remove();
					break;
				case "SeasonalBlock":
					$("#head_list").html('<th width="100px"><span id="head1">Display Order</span></th><th width="260px"><span id="head2">Picture</span></th><th width="260px"><span id="head3">Alt</span></th><th><span id="head4">Topic ID</span></th><th width="60px"><span id="head5">New Window</span></th><th width="130px"><span id="head10">Reason</span></th><th width="120px"><span id="head6">Start Date</span></th><th width="120px"><span id="head7">Expire Date</span></th><th width="100px"><span id="head8">Action &nbsp;&nbsp;<input id="check_all" type="checkbox" value="" name="check_all" onclick="selectAllCheckbox();"/></span></th>');					
					$("#act").val("default_page_save");
					break;
			}
//			loadlist();
		}else{
			switch(e.value){
				case "FeaturedMerchantLogos":
					$("#head2").text("Merchant ID");
					break;
				case "FeaturedCouponsDeals":
					$("#head2").text("Coupon ID");
					break;
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
		$(".expiredate_c").each(function(){
			var date = $(this).val();
			if(date == "0000-00-00" || date == ""){
				$(this).css({ color:"black"});
			}else{
				var dateObj = new Date(date);
				if(dateObj == "Invalid Date"){
					$(this).css({ color:"red"});
					$(this).focus();
					flag = false;
				}else{
					$(this).css({ color:"black"});
				}
			}
		});
		if(flag == false){
			alert("Invalid Date");
			return false;
		}
		if(!confirm("Save ?")){
			return false;
		}
		var act = $("#act").val().trim();
		var postStr  = "";
		if(act == "homepage_FeaturedStores_save" || act == "homepage_HotStores_save"){
			var aff_oldlinecount = $("#aff_oldlinecount").val();
			var aff_maxlinenum = $("#aff_maxlinenum").val();
			var merchantid = $("#merchantid").val();
			var act = $("#act").val();
			var site = $("#site").val();
			var page_name = $("#page_name").val();
			var placement_tag_type = $("#placement_tag_type").val();
			var page_position = $("#page_position").val();
			postStr = "aff_oldlinecount=" + aff_oldlinecount + "||aff_maxlinenum=" + aff_maxlinenum;
			postStr = postStr + "||merchantid=" + merchantid + "||act=" + act;
			postStr = postStr + "||site=" + site + "||page_name=" + page_name;
			postStr = postStr + "||placement_tag_type=" + placement_tag_type + "||page_position=" + page_position;
			var i = 0;
			for(i = 1; i <= aff_maxlinenum; i++){
				
				if($("#hide_affname_newline_" + i).val() != ""){
					if($("#aff_chks_" + i).attr("checked") == true){
						postStr = postStr + "||aff_chks_" + i + "=" + $("#aff_chks_" + i).val();
					}
					postStr = postStr + "||order_newline_" + i + "=" + $("#order_newline_" + i).val();
					postStr = postStr + "||hide_affname_newline_" + i + "=" + $("#hide_affname_newline_" + i).val();
					postStr = postStr + "||affname_newline_" + i + "=" + $("#affname_newline_" + i).val();
					postStr = postStr + "||reason_" + i + "=" + $("#reason_" + i).val();
					postStr = postStr + "||startdate_" + i + "=" + $("#startdate_" + i).val();
					postStr = postStr + "||expiredate_" + i + "=" + $("#expiredate_" + i).val();
					postStr = postStr + "||oldflag_" + i + "=" + $("#oldflag_" + i).val();
//					postStr = postStr + "||aff_chks_" + i + "=" + $("#aff_chks_" + i).val();
				}
			}
			var verifyArr  = {'placement_values':postStr};
			$.ajax({
				type: "post",
				async: false,
				url: "/editor/placement_content.php?site=" + site + "&act=act",
				data: $.param(verifyArr),
				success: function (data) {			
				if(data != "success"){ 
					alert('Error(' + data + ')');
				}else{
					alert("Succeed to save.");
					object_type = $("#object_type").val();
					var page = $("#page_name").val();
					var placement_tag_type = $("#placement_tag_type").val();
					if(page == "tag" || page == "merchant"){
						switch(placement_tag_type){
							case "FeaturedMerchantLogos":
								loadTagMerchant(object_type);
								break;
							case "FeaturedCouponsDeals":
								LoadTagCouponDeal(object_type);
								break;
							default:
								LoadRelated(object_type);
								break;
						}
					}else if(page == "category"){
						LoadCategoryList(object_type);
					}
					else if(page == "homepage"){
						LoadHomepageList(object_type);
					}
					else if(page == "search"){
						LoadSearchpageList(object_type);
					}
					else if(page == "sitewide"){
						LoadAllList(object_type);
					}
					else if(page == "deal"){
						LoadDealList(object_type);
					}
					else if(page == "coupon"){
						LoadCouponList(object_type);
					}
					else if(page == "exclusive"){
						LoadExclusiveList(object_type);
					}else{
						LoadDefaultList(object_type);
					}
					return false;
				}		
				}
			});
			return false;
		}
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
							switch(placement_tag_type){
								case "FeaturedMerchantLogos":
									loadTagMerchant(object_type);
									break;
								case "FeaturedCouponsDeals":
									LoadTagCouponDeal(object_type);
									break;
								default:
									LoadRelated(object_type);
									break;
							}
						}
						else if(page == "category"){
							LoadCategoryList(object_type);
						}
						else if(page == "homepage"){
							//LoadHomepageList(object_type);
							switch(placement_tag_type){
								case "SeasonalBlock":
									LoadDefaultList(object_type);
									break;
								default :
									LoadHomepageList(object_type);
									break;
							}
						}
						else if(page == "search"){
							LoadSearchpageList(object_type);
						}
						else if(page == "sitewide"){
							LoadAllList(object_type);
						}
						else if(page == "deal"){
							LoadDealList(object_type);
						}
						else if(page == "coupon"){
							LoadCouponList(object_type);
						}
						else if(page == "exclusive"){
							LoadExclusiveList(object_type);
						}else{
							LoadDefaultList(object_type);
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
			case "coupon":
			case "exclusive":
			case "category":
				return checkCategory();
				break;
			case "homepage":
				return checkHomepage();
				break;
			case "sitewide":
				return checkSitewide();
				break;
			case "search":
				return checkSearch();
				break;
			case "deal":
				return checkDeal();
				break;
			case "banner":
				return checkBanner();
				break;
		}
	}
	function checkDeal(){
		var maxLine = parseInt($("#aff_maxlinenum").val());
		var i =1;
		for(var i = 1; i <= maxLine; i++){
			for(var j = 2; j <= maxLine; j++){
				if(i != j){
					if($("#affname_newline_" + i).val() == $("#affname_newline_" + j).val()){
						alert("Category Duplicate.");
						$("#affname_newline_" + i).focus();
						return false;
					}
					if($("#affname_newline_" + i).val() == "0"){
						alert("Please select category.");
						$("#affname_newline_" + i).focus();
						return false;
					}
				}
			}
		}
	return true;
}
	function changeCategory(e){
		if(e.value != "0"){
			if(e.value == "-1"){
				$("#tagname_span").html("<a href='" + $("#front_url").val() + "/front/category.php?type=new&forcerefresh=true' target='_blank' style='color:black;'>" + $("#object_type").find("option:selected").text() + "</a>");
			}else if(e.value == "-2"){
				$("#tagname_span").html("<a href='" + $("#front_url").val() + "/front/category.php?type=popular&forcerefresh=true' target='_blank' style='color:black;'>" + $("#object_type").find("option:selected").text() + "</a>");
			}else{
				$("#tagname_span").html("<a href='" + $("#front_url").val() + "/front/category.php?cateid=" + e.value + "&forcerefresh=true' target='_blank' style='color:black;'>" + $("#object_type").find("option:selected").text() + "</a>");
			}
		}else{
			$("#tagname_span").html("");
		}
		
		loadlist();
	}
	
	function checkSearch(){
		var placement_tag_type = $("#placement_tag_type").val();
		if(placement_tag_type == "FeaturedCouponsDeals" ){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				var line = $("#affname_newline_" + i).val();
				if(typeof(line) != "undefined"){
					var lineInt = parseInt(line, 10);
					if(isNaN(lineInt) == true){
						alert("Please input number");
						$("#affname_newline_" + i).focus();
						return false;
					}
				}
			}
		}
		return true;
	}
	function checkSitewide(){
		var placement_tag_type = $("#placement_tag_type").val();
		if(placement_tag_type == "HotSearches" || placement_tag_type == "FooterKeywordLinks" || placement_tag_type == "MoreMenu"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				var line = $("#affname_newline_" + i).val();
				var line1 = $("#url_newline_" + i).val();
				
				if(line == ""){
					alert("Please input a keyword.");
					$("#affname_newline_" + i).focus();
					return false;
				}
				if(line1 == ""){
					alert("Please input URL.");
					$("#url_newline_" + i).focus();
					return false;
				}
			}
		}
		return true;
	}
	function checkBanner(){
		var placement_tag_type = $("#placement_tag_type").val();
		if(placement_tag_type == "RotateBanner" || placement_tag_type == "RightBanner"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var oldLine = parseInt($("#aff_oldlinecount").val());
			var i = oldLine;
			for(i = 1; i<= maxLine; i++){
				var line = $("#alt_newline_" + i).val();
				var line1 = $("#url_newline_" + i).val();
				var pic = $("#pic_newline_" + i).val();
				if(i > oldLine && pic == ""){
					alert("Please upload a pic.");
					$("#pic_newline_" + i).focus();
					return false;
				}
				var line3 = $("#alt_newline_" + i).val();
				if(line3 == "newsletter"){
					continue;
				}
				if(line == ""){
					alert("Please input alt.");
					$("#affname_newline_" + i).focus();
					return false;
				}
				if(line1 == ""){
					alert("Please input URL.");
					$("#url_newline_" + i).focus();
					return false;
				}
			}
		}
		return true;
	}
	
	function checkHomepage(){
		var placement_tag_type = $("#placement_tag_type").val();
		if(placement_tag_type == "FeaturedCoupons" || placement_tag_type == "FeaturedDeals" ||  placement_tag_type == "FeaturedCouponsBackfill" ||  placement_tag_type == "FeaturedDealsBackfill"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				if(placement_tag_type == "FeaturedCouponsBackfill" ||  placement_tag_type == "FeaturedDealsBackfill"){
					
					var line = $("#hide_affname_newline_" + i).val();
					if(typeof(line) != "undefined"){
						var lineInt = parseInt(line, 10);
						if(isNaN(lineInt) == true){
							alert("Please input number");
							$("#affname_newline_" + i).focus();
							return false;
						}
					}
					if(typeof($("#coupon_count_" + i).val()) != "undefined"){
						var couponCount = parseInt($("#coupon_count_" + i).val(), 10);
						if(isNaN(couponCount) == true){
							alert("Please input number");
							$("#coupon_count_" + i).focus();
							return false;
						}
					}
				}else{
					var line = $("#affname_newline_" + i).val();
					if(typeof(line) != "undefined"){
						var lineInt = parseInt(line, 10);
						if(isNaN(lineInt) == true){
							alert("Please input number");
							$("#affname_newline_" + i).focus();
							return false;
						}
					}
				}
			}
		}
		return true;
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
	function checkCategory(){
	
		if($("#placement_tag_type").val() == "FeaturedCouponsDeals"){
			var maxLine = parseInt($("#aff_maxlinenum").val());
			var i =1;
			for(i = 1; i<= maxLine; i++){
				var line = $("#affname_newline_" + i).val();
				
				if(typeof(line) != "undefined"){
					var lineInt = parseInt(line, 10);
					if(isNaN(lineInt) == true){
						alert("Please input number");
						$("#affname_newline_" + i).focus();
						return false;
					}
				}
			}
		}
		return true;
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
		
		var setting_type = $("#setting_type").val();
		var page_name = $("#page_name").val();
		var object_type = $("#object_type").val();
		var placement_tag_type = $("#placement_tag_type").val();
		if(setting_type == "placement"){
			switch(page_name){
				case "tag":
					switch(placement_tag_type){
						case "FeaturedMerchantLogos":
							loadTagMerchant(object_type);
							break;
						case "FeaturedCouponsDeals":
							LoadTagCouponDeal(object_type);
							break;
						case "RelatedTag":
							LoadRelated(object_type);
							break;
					}
					break;
				case "merchant":
					switch(placement_tag_type){
						case "FeaturedCouponsDeals":
							LoadTagCouponDeal(object_type);
							break;
						case "RelatedMerchant":
							LoadRelated(object_type);
							break;
						case "RelatedTag":
							LoadRelated(object_type);
							break;
					}
					break;
				case "category":
					LoadCategoryList(object_type);
					break;
				case "coupon":
					LoadCouponList(object_type);
					break;
				case "exclusive":
					LoadExclusiveList(object_type);
					break;
				case "homepage":
					//LoadHomepageList(object_type);
					switch(placement_tag_type){
						case "SeasonalBlock":
							LoadDefaultList(object_type);
							break;
						default :
							LoadHomepageList(object_type);
							break;
					}
					break;
				case "search":
					LoadSearchpageList(object_type);
					break;
				case "sitewide":
					LoadAllList(object_type);
					break;
				case "deal":
					LoadDealList(object_type);
					break;
				default:
					LoadDefaultList(object_type);
			}
		}
	}
	
	function selectAllCheckbox(){
		var check_all = $("#check_all").attr("checked");
        $("#merchantaffiateid_tr :checkbox").attr("checked", check_all);  
	}
	
	function LoadDefaultList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var placement_tag_type = $("#placement_tag_type").val();
		var pageType = $("#page_name").val();
		var objTypeId = $("#object_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=loaddefaultlist" + "&site=" + site + "&placementtype=" + placement_tag_type + "&pagetype=" + pageType + "&objtypeid=" + objTypeId + "&p_r=" + placementReason + "&p_s=" + placementStatus;
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
	function LoadAllList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getAllList" + "&site=" + site + "&q=" + placement_tag_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
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
			if(tempArr[0] > 7 && placement_tag_type == 'HotSearches'){
				$("#message").text("Only 7 keywords can be shown on the front page while you have set " + tempArr[0] + " keywords here.");
			}
		}		
		});	
	}
	function LoadSearchpageList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		
		var placement_tag_type = $("#placement_tag_type").val();
		var object_type = $("#object_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getSearchpageList" + "&site=" + site + "&q=" + placement_tag_type + "&objecttype=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
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
	function LoadHomepageList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getHomepageList" + "&site=" + site + "&q=" + placement_tag_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
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
	function LoadCategoryList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var object_type = $("#object_type").val();
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getCategoryList" + "&site=" + site + "&q=" + placement_tag_type + "&cid=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;

		var pageType = $("#page_name").val();
//		alert("/editor/placement_search.php?act=getTagMerchantList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type);
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
	function LoadExclusiveList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var object_type = $("#object_type").val();
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getExclusiveList" + "&site=" + site + "&q=" + placement_tag_type + "&cid=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		
		var pageType = $("#page_name").val();
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
	function LoadCouponList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var object_type = $("#object_type").val();
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getCouponList" + "&site=" + site + "&q=" + placement_tag_type + "&cid=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
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
	function LoadDealList(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var object_type = $("#object_type").val();
		var placement_tag_type = $("#placement_tag_type").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getDealList" + "&site=" + site + "&q=" + placement_tag_type + "&cid=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
//		alert("/editor/placement_search.php?act=getTagMerchantList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type);
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
	function clearTrAndData(){
		$(".csl_oldline").remove();
		$("#aff_oldlinecount").val("0");
		$("#aff_maxlinenum").val("0");
	}
	function loadTagMerchant(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "/editor/placement_search.php?act=getTagMerchantList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
		var pageType = $("#page_name").val();
		switch(pageType){
			case "merchant":
				url = "/editor/placement_search.php?act=merchantName" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
				break;
		}
//		alert("/editor/placement_search.php?act=getTagMerchantList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type);
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
	function LoadTagCouponDeal(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var page_name = $("#page_name").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var url = "";
		switch(page_name){
			case "tag":
				url = "/editor/placement_search.php?act=getTagcouponDealList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
				break;
			case "merchant":
				url = "/editor/placement_search.php?act=getMerchantcouponDealList" + "&merchantId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
				break;
		}
		
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
	
	function LoadRelated(object_type){
		clearTrAndData();
		var site = $("#site").val();
		var verifyArr = new Array();
		var page_name = $("#page_name").val();
		var placementReason = $("#placement_reason").val();
		var placementStatus = $("#placement_status").val();
		var placement_tag_type = $("#placement_tag_type").val();
		var url = "";
		switch(page_name){
			case "tag":
				url = "/editor/placement_search.php?act=getRelatedTagList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
				break;
			case "merchant":
				if("RelatedTag" == placement_tag_type){
					url = "/editor/placement_search.php?act=getRelatedTagList" + "&tagId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus+"&page_name=merchant";
				}else{
					url = "/editor/placement_search.php?act=getRelatedMerchantList" + "&merchantId=" + object_type + "&site=" + site + "&q=" + object_type + "&p_r=" + placementReason + "&p_s=" + placementStatus;
				}
				
				break;
		}
		
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
//			$("#object_lable").text("Tag ID");
		}else if(e.value == 'merchant'){
			$("#act").val("merchant");
			$("#merchantedit_form").submit();
//			$("#object_lable").text("Merchant ID");
		}else if(e.value == 'category'){
			$("#act").val("category");
			$("#merchantedit_form").submit();
//			$("#object_lable").text("Category ID");
		}else if(e.value == 'homepage'){
			$("#act").val("homepage");
			$("#merchantedit_form").submit();
		}else if(e.value == 'search'){
			$("#act").val("search");
			$("#merchantedit_form").submit();
		}else if(e.value == 'sitewide'){
			$("#act").val("sitewide");
			$("#merchantedit_form").submit();
		}else if(e.value == 'deal'){
			$("#act").val("deal");
			$("#merchantedit_form").submit();
		}else if(e.value == 'coupon'){
			$("#act").val("coupon");
			$("#merchantedit_form").submit();
		}else if(e.value == 'exclusive'){
			$("#act").val("exclusive");
			$("#merchantedit_form").submit();
		}else if(e.value == 'banner'){
			$("#act").val("banner");
			$("#merchantedit_form").submit();
		}else{
			$("#act").val("default");
			$("#merchantedit_form").submit();
		}
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
	function bindCategoryAuto(id){
		$("#" + id).unbind("live");
		$("#" + id).unbind("click");
    	$(".ac_results").remove();

		$("#" + id).live("click", function(){
			var affname = $("#" + id).val();
			$("#" + id).autocomplete(
				'/editor/placement_search.php?act=categoryName&q=' + affname,
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
	