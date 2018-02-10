$(document).ready(function(){
	if($("#setting_type").val() == "AFFILIATE"){
		$("#scode_tr").remove();
	}
	loadlist();
});
function changeScope(obj){
	if($(obj).val()=='GLOBAL'){
		$("#merchantid").val('');
		$(".tdmid").hide();
	}
	if($(obj).val()=='MERCHANT'){
		$(".tdmid").show();
		if($("#site").val() == 'all'){
			$("#site option").attr('selected','');
			$("#site option").eq(1).attr('selected','selected');
		}
	}
}
function changeSettingType(e){
	$('#merchantedit_form').submit();
}
	
function addnewprogramid(ot){
	var aff_maxlinenum = $("#aff_maxlinenum").val();
	aff_maxlinenum =parseInt(aff_maxlinenum) + 1;
	
	var type = $("#setting_type").val();
	var scope = $("#scope").val();
	var trStr = "";
	trStr = "<tr id=tr_" + aff_maxlinenum + " align='left' class='csl_oldline' style='background-color:#BFE484'>";
	switch(type){
		case "AFFILIATE":
			$affiliate_sel = $("#affiliate_sel").html();
			trStr = trStr + "<td><select name='objid_" + aff_maxlinenum + "' id='objid_" + aff_maxlinenum + "'>" + $affiliate_sel + "</select></td>";
			trStr = trStr + "<td><input style='width:300px;' type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' />&nbsp;&nbsp;Title<input id='title_"  + aff_maxlinenum + "' type='checkbox' value='1' name='title_" + aff_maxlinenum + "'>&nbsp;&nbsp;URL<input id='url_"  + aff_maxlinenum + "' type='checkbox' value='1' name='url_" + aff_maxlinenum + "'>&nbsp;&nbsp;Description<input id='desc_"  + aff_maxlinenum + "' type='checkbox' value='1' name='desc_" + aff_maxlinenum + "'></td>";
			trStr = trStr + "<td><select name='status_" + aff_maxlinenum + "' id='status_" + aff_maxlinenum + "' ><Option value='ACTIVE'>Active</option><Option value='INACTIVE'>Inactive</option></select></td>";
			trStr = trStr + "<td align='center'>";
			trStr = trStr + "<input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/>"; 
			trStr = trStr + "</td>";
			break;
		case "MERCHANT":
			
			switch(scope){
				case "GLOBAL":
					trStr = trStr + "<td></td>";
					trStr = trStr + "<td><input style='width:300px;' type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' />&nbsp;&nbsp;Title<input id='title_"  + aff_maxlinenum + "' type='checkbox' value='1' name='title_" + aff_maxlinenum + "'>&nbsp;&nbsp;AffUrl<input id='url_"  + aff_maxlinenum + "' type='checkbox' value='1' name='url_" + aff_maxlinenum + "'>&nbsp;&nbsp;Description<input id='desc_"  + aff_maxlinenum + "' type='checkbox' value='1' name='desc_" + aff_maxlinenum + "'>&nbsp;&nbsp;Coupon Code<input id='code_"  + aff_maxlinenum + "' type='checkbox' value='1' name='code_" + aff_maxlinenum + "'>&nbsp;&nbsp;LpUrl<input id='lpurl_"  + aff_maxlinenum + "' type='checkbox' value='1' name='lpurl_" + aff_maxlinenum + "'>&nbsp;&nbsp;CPQ Description<input id='cpqdes_"  + aff_maxlinenum + "' type='checkbox' value='1' name='cpqdes_" + aff_maxlinenum + "'></td>";
					trStr = trStr + "<td><select name='status_" + aff_maxlinenum + "' id='status_" + aff_maxlinenum + "' ><Option value='ACTIVE'>Active</option><Option value='INACTIVE'>Inactive</option></select></td>";
					trStr = trStr + "<td>&nbsp;</td><td>&nbsp;</td><td><textarea id='remark_" + aff_maxlinenum + "' name='remark_" + aff_maxlinenum + "'></textarea></td>";
					trStr = trStr + "<td><input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/></td>"; 
					break;
				case "MERCHANT":
					var merchantid = $("#merchantid").val();
					trStr = trStr + "<td></td>";
					trStr = trStr + "<td><input type='text' name='objid_" + aff_maxlinenum + "' id='objid_" + aff_maxlinenum + "' value='" + merchantid + "'/><span id='objid_span_" + aff_maxlinenum + "'></span></td>";
					trStr = trStr + "<td><input style='width:300px;' type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' />&nbsp;&nbsp;Title<input id='title_"  + aff_maxlinenum + "' type='checkbox' value='1' name='title_" + aff_maxlinenum + "'>&nbsp;&nbsp;URL<input id='url_"  + aff_maxlinenum + "' type='checkbox' value='1' name='url_" + aff_maxlinenum + "'>&nbsp;&nbsp;Description<input id='desc_"  + aff_maxlinenum + "' type='checkbox' value='1' name='desc_" + aff_maxlinenum + "'>&nbsp;&nbsp;Coupon Code<input id='code_"  + aff_maxlinenum + "' type='checkbox' value='1' name='code_" + aff_maxlinenum + "'></td>";
					trStr = trStr + "<td><select name='status_" + aff_maxlinenum + "' id='status_" + aff_maxlinenum + "' ><Option value='ACTIVE'>Active</option><Option value='INACTIVE'>Inactive</option></select></td>";
					trStr = trStr + "<td>&nbsp;</td><td>&nbsp;</td><td><textarea id='remark_" + aff_maxlinenum + "' name='remark_" + aff_maxlinenum + "'></textarea></td>";
					trStr = trStr + "<td><input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/></td>"; 
					break;
			}
			break;
		case "REVIEW":
			switch(scope){
				case "GLOBAL":
					trStr = trStr + "<td><input style='width:300px;' type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' /></td>";
					trStr = trStr + "<td><select name='status_" + aff_maxlinenum + "' id='status_" + aff_maxlinenum + "' ><Option value='ACTIVE'>Active</option><Option value='INACTIVE'>Inactive</option></select></td>";
					trStr = trStr + "<td>&nbsp;</td><td>&nbsp;</td><td><textarea id='remark_" + aff_maxlinenum + "' name='remark_" + aff_maxlinenum + "'></textarea></td>";
					trStr = trStr + "<td><input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/></td>"; 
					break;
				case "MERCHANT":
					trStr = trStr + "<td></td>";
					trStr = trStr + "<td><input type='text' name='objid_" + aff_maxlinenum + "' id='objid_" + aff_maxlinenum + "'/><span id='objid_span_" + aff_maxlinenum + "'></span></td>";
					trStr = trStr + "<td><input style='width:300px;' type='text' name='keyword_" + aff_maxlinenum + "' id='keyword_" + aff_maxlinenum +  "' /></td>";
					trStr = trStr + "<td><select name='status_" + aff_maxlinenum + "' id='status_" + aff_maxlinenum + "' ><Option value='ACTIVE'>Active</option><Option value='INACTIVE'>Inactive</option></select></td>";
					trStr = trStr + "<td>&nbsp;</td><td>&nbsp;</td><td><textarea id='remark_" + aff_maxlinenum + "' name='remark_" + aff_maxlinenum + "'></textarea></td>";
					trStr = trStr + "<td><input type='button' name='desc"+aff_maxlinenum+"' id='desc"+aff_maxlinenum+"' onclick='removeAff(\"" + aff_maxlinenum + "\");' value='Remove'/></td>"; 
					break;
			}
			break;
	}
	trStr = trStr +"</tr>";
	if(ot == 1){
	    $("#merchantaffiateid_tr").prepend(trStr);
	}else{
	    $("#merchantaffiateid_tr").append(trStr);
    }
    $("#aff_maxlinenum").val(aff_maxlinenum);
    $("#objid_" + aff_maxlinenum).live("click", function(){
    	$(".ac_results").remove();
    	var objName = $("#objid_" + aff_maxlinenum).val();
    	var site = $("#site").val();
    	$("#objid_" + aff_maxlinenum).autocomplete(
    		'/editor/placement_search.php?act=merchantName&q=' + objName + "&site=" + site,
    		{
    			scrollHeight: 320,
    			max: 3000,
    			formatItem: formatItem,
    			formatResult: formatResult,
    			autoFill: false
    	});
   });
   
    $("#objid_" + aff_maxlinenum).result(function(event, row, formatted){
    	$(".ac_results").remove();
    	$("#objid_span_" + aff_maxlinenum).text(row[1]);
    	alert(row[1]);
    	$("#objid_" + aff_maxlinenum).val(row[0]);
    });
}

	function formatItem(row) {
		
		return row[1] + "(" + row[0] + ")";
	}
	function formatResult(row) {
		return row[0];
	}
	function deleteKey(id){
		if(!confirm("Delete this keyword?")){
			return false;
		}
		var verifyArr  = {'id':id};
		$.ajax({
			type: "post",
			async: false,
			url: "/editor/blacklist_list.php?&act=deletekey",
			data: $.param(verifyArr),
			success: function (data) {			
				if(data != "success"){ 
					alert('Error(' + data + ')');
				}else{
					loadlist();
				}
		}
		});
	}
	function changeStatus(id, status){
		if(!confirm("Change Status?")){
			return false;
		}
		var verifyArr  = {'id':id, 'status':status};
		$.ajax({
			type: "post",
			async: false,
			url: "/editor/blacklist_list.php?&act=changestatus",
			data: $.param(verifyArr),
			success: function (data) {			
			if(data != "success"){ 
				alert('Error(' + data + ')');
			}else{
				loadlist();
			}
		}
		});
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
		var url = domain + "/search-" + encodeURI($("#object_type").val()) + "-coupons-deals.html";
		$("#fronturl").html("<a style='color:black;' href='" + url + "' target='_blank'>" + URL + "</a>");
	}
	function clearSearchKeyword(e){
		$("#confirmkeyword").val("");
	}
	function addnewsletter(){
		$("#newsletterflg").val("newsletter");
		addnewprogramid(2);
	}
	
	function submitForm(){
		if(checkFormInput() == false){
			return false;
		}
		if(!confirm("Save ?")){
			return false;
		}
		$("#act").val("save");
		$('#merchantedit_form').ajaxSubmit(function(data) {
				$("#act").val("");
					if(data != "success"){ 
						alert('Error(' + data + ')');
					}else{
						alert("Success");
						loadlist();
					}
				});
		return false;
	}
	
	function checkFormInput(){
		var page_name = $("#setting_type").val();
		var placement_tag_type = $("#scope").val();
		var maxLine = parseInt($("#aff_maxlinenum").val());
		var oldLine = parseInt($("#aff_oldlinecount").val());
		for(var i = oldLine + 1; i<= maxLine; i++){
			if($("#title_" + i).length > 0){
				switch(page_name){
					case "AFFILIATE":
						if($("#title_" + i).attr("checked")!=true && $("#url_" + i).attr("checked")!=true && $("#desc_" + i).attr("checked")!=true){
							alert("Please select at least one checkbox.");
							return false;
						}
						break;
					case "MERCHANT":
						if(placement_tag_type == "MERCHANT"){
							if($("#title_" + i).attr("checked")!=true && $("#url_" + i).attr("checked")!=true && $("#desc_" + i).attr("checked")!=true && $("#code_" + i).attr("checked")!=true){
								alert("Please select at least one checkbox.");
								return false;
							}
						}
						if(placement_tag_type == "GLOBAL"){
							if($("#lpurl_" + i).attr("checked")!=true  && $("#title_" + i).attr("checked")!=true && $("#url_" + i).attr("checked")!=true && $("#desc_" + i).attr("checked")!=true && $("#code_" + i).attr("checked")!=true && $("#cpqdes_" + i).attr("checked")!=true ){
								alert("Please select at least one checkbox.");
								return false;
							}
						}
						break;
					case "REVIEW":
						break;
				}
			}
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
	}

	function loadlist(){
		var setting_type = $("#setting_type").val();
		var scope = $("#scope").val();
		var page = $("#page").val();
		var perpage = $("#perpage").val();
		var site = $("#site").val();
		var status = $("#status").val();
		var merchantid = $("#merchantid").val();
		var keywords_search = $("#keywords_search").val();
		var keywords = $("#keywords").val();
		var verifyArr  = {'site':site, 'status':status, 'merchantid':merchantid, 'keywords':keywords};
		var url = "/editor/blacklist_list.php?act=loadlist" + "&setting_type=" + setting_type + "&scope=" + scope + "&page=" + page + "&perpage=" + perpage + "&keywords_search=" + encodeURI(keywords_search);
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
				$("#merchantaffiateid_tr").html(tempArr[1]);
		}		
		});
	}
	function updateRemark(id){
		var htmlStr = "<textarea id=\""+id+"\"></textarea><input type=\"button\" onclick=\"saveRemark('"+id+"')\" value=\"Save\"><input type=\"button\" onclick=\"closeRemark('"+id+"')\" value=\"Close\">";
		$("#span_remark_" + id).html(htmlStr);
	}
	
	function closeRemark(id){
		var remark = $("#input_remark_"+id).val();
		var htmlStr = remark + "<br/><input type=\"button\" value=\"Update\" onclick=\"updateRemark('"+id+"')\">";
		$("#span_remark_" + id).html(htmlStr);
	}
	
	function saveRemark(id){
		var remark = $("#" + id).val();
		var verifyArr  = {'id':id, 'remark':remark};
		var url = "/editor/blacklist_list.php?act=saveremark";
		$.ajax({
			type: "post",		
			url: url,
			data: $.param(verifyArr),
			success: function (resString) {
				if(resString == "success"){
					alert("Success");
					loadlist();
				}
			}		
		});
	}
	function pageJump(page){
		$("#page").val(page);
		loadlist();
	}

	function selectAllCheckbox(){
		var check_all = $("#check_all").attr("checked");
        $("#merchantaffiateid_tr :checkbox").attr("checked", check_all);  
	}

	function removeAff(aff_maxlinenum){
		$("#tr_" + aff_maxlinenum).remove();
	}