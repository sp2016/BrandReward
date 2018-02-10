function selectSite() {
	$("#merchantsearch").unautocomplete();
	var site = $("#storeSite").find("option:selected").text();
	$("#merchantsearch").autocomplete("/ajax/store.php?ajaxaction=mersearch&site="+site, {
		width: 200,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#merchantsearch").result(function(event, data, formatted) {
		tobj = document.getElementById('MerchantID');
		go_out = false;
		flag = false;
		if (data) {
			for(var j=0; j < tobj.options.length; j++) {
    			if (tobj.options[j].value == data[1]) {
    				go_out = true;
    				break;
    			}
    		}
			$("#merchantsearch").attr("value",'');
			if (go_out) return false;
			var site_mer = data[1].split('-');
			
			$.ajax({
		    	type: "POST",
		    	url: '/ajax/store.php',
		    	data: "ajaxaction=storemerrel&merid=" + site_mer[0] + "&site=" + site_mer[1],
		    	async: false,
		    	success: function(msg){
		    		if (msg == true) {
		    			flag = true;
		    			alert("The merchant - " + data[0] + " - has been associated with another Store!");
		    		}
		        }
			});	
			if (flag) return false;
			var no = new Option();
			no.value = data[1];
			no.text = "[" + site_mer[1] + "]" + data[0];
			var length = tobj.options.length;
			tobj.options[length] = no;
			tobj.options[length].selected = true;
			$("#merchantsearch").attr("value",'');
		}
	});
}

function storeRemoveSearch() {
	$("#storeNameSearch").unautocomplete();
	$("#storeNameSearch").autocomplete("/ajax/store.php?ajaxaction=storesearch", {
		width: 300,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#storeNameSearch").result(function(event, data, formatted) {
		if (data) {
			var store_info = data[1].split('{;}');
			$("#storeNameSearch").attr("value", store_info[0]);
			$("#FromStoreID").attr("value", store_info[1]);
			$("#FromStoreUrl").attr("value", store_info[2]);
			$("#hasmerchant").attr("value", store_info[3]);
			$("#hasprogram").attr("value", store_info[4]);
			
			var info = "<br />Store ID : " + store_info[1] + "<br />Store Name : " + store_info[0] + "<br />Store Url : " + store_info[2] + "<input type='hidden' id='Url' value='" + store_info[2] + "'>&nbsp;&nbsp;<input type='button' value='Open' onclick=\"OpenURL('Url');\">";
			$("#storeDetail").html(info);
		}
	});
}

function storeMergeSearch() {
	$("#fromStoreNameSearch").unautocomplete();
	$("#fromStoreNameSearch").autocomplete("/ajax/store.php?ajaxaction=storesearch", {
		width: 300,
		matchSubset:false,
		selectFirst: false
	});
	$("#toStoreNameSearch").autocomplete("/ajax/store.php?ajaxaction=storesearch", {
		width: 300,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#fromStoreNameSearch").result(function(event, data, formatted) {
		if (data) {
			var from_store_info = data[1].split('{;}');
			$("#fromStoreNameSearch").attr("value", from_store_info[0]);
			$("#FromStoreID").attr("value", from_store_info[1]);
			$("#FromStoreUrl").attr("value", from_store_info[2]);
			$("#hasmerchant").attr("value", from_store_info[3]);
			$("#hasprogram").attr("value", from_store_info[4]);
			
			var frominfo = "<br />Store ID : " + from_store_info[1] + "<br />Store Name : " + from_store_info[0] + "<br />Store Url : " + from_store_info[2] + "<input type='hidden' id='fromUrl' value='" + from_store_info[2] + "'>&nbsp;&nbsp;<input type='button' value='Open' onclick=\"OpenURL('fromUrl');\">";
			$("#fromStoreDetail").html(frominfo);
		}
	});
	$("#toStoreNameSearch").result(function(event, data, formatted) {
		if (data) {
			var to_store_info = data[1].split('{;}');
			$("#toStoreNameSearch").attr("value", to_store_info[0]);
			$("#ToStoreID").attr("value", to_store_info[1]);
			
			var toinfo = "<br />Store ID : " + to_store_info[1] + "<br />Store Name : " + to_store_info[0] + "<br />Store Url : " + to_store_info[2] + "<input type='hidden' id='toUrl' value='" + to_store_info[2] + "'>&nbsp;&nbsp;<input type='button' value='Open' onclick=\"OpenURL('toUrl');\">";
			$("#toStoreDetail").html(toinfo);
		}
	});
}

function formatItem(data) {
	return data[1] + "(" + data[0] + ")" + (data[2] ? " - " +data[2]:"");
}

function programSearch() {
	$("#programsearch").unautocomplete();
	$("#programsearch").autocomplete("/front/program_search.php?ajaxTag=searchProgram", {
		width: 300,
		matchSubset:false,
		selectFirst: false,
		formatItem: formatItem
	});
	
	$("#programsearch").result(function(event, data, formatted) {
		if (data) {
			$("#programsearch").val(data[1]);
			
			var info = '<a href="/front/program_edit.php?ID=' + data[2] + '" target="_blank">Edit Program</a>&nbsp;&nbsp;<a href="/front/program_store_edit.php?ProgramId=' + data[2] + '" target="_blank">Edit P-S Relationship</a>';
			$("p[class='programsearch'] span").html(info)
		}
	});
}

function exportStoreList() {
	var downHref = window.location.search;
	var ajaxUrl;
	if (downHref.substr(0, 1) == '?') {
		downHref = downHref.substr(1);
		ajaxUrl = downHref + "&ajaxaction=downstorelist";
	} else {
		ajaxUrl = "ajaxaction=downstorelist";
	}
	window.open('/ajax/store.php?' + ajaxUrl, 'newwindow', 'height=800, width=1400, top=100, left=100, toolbar=no,menubar=no, scrollbars=yes, status=no');
}

function addColor(obj) {
    originColor = $(obj).attr("bgColor");
    $(obj).attr("bgColor", "#FFFFBB");
    
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

function chooseAllShipCountry(obj) {
	if ($(obj).attr("checked") == true) {
		$("input[name^='SupportedShippingCountry']").attr("checked", true);
	} else {
		$("input[name^='SupportedShippingCountry']").attr("checked", false);
	}
}

function isHiddenInput(obj) {
	var selVal = $(obj).val();
	var selText = $(obj).find("option:selected").text();
	if (selVal == 'Other') {
		$("#CouponTitleOther").attr("style", "display:''");
    } else {
    	$("#CouponTitleOther").attr("style", "display:none");
    }
}

function checkFirstDomain(url, storeid) {
	var res = false;
	$.ajax({
    	type: "POST",
    	url: '/ajax/store.php',
    	data: "ajaxaction=checkdomain&storeurl="+url + "&storeid=" + storeid,
    	dataType: 'json',
    	async: false,
    	success: function(data){
    		if (data.flag == 'fail') {
    			var msg = "The follows have the same domain:\r\n";
    			for (var i = 0; i < data.msg.length; i++) {
    				msg += data.msg[i].Url + "\r\n";
    			}
				msg += "Are you sure you want to add this store?";
    			scrollTo(0,0);
    			if(confirm(msg)) res = true;
    			else res = false;
    		} else res = true;
        }
	});
	return res;
}

function checkStoreUrl() {
	$("#checkstoreurl").html('');
	
	var url = $("#Url").val();
	url = url.replace(/(^\s*)|(\s*$)/g, '');
	var ID = $("#ID").val();
	
	var pattern = /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	if (url == '' || pattern.test(url) == false) return false;
	var res = false;
	
	
	
	if (ID == undefined) {
		ID = '';
		var ajaxaction = 'addurl';
		var originalstoreurl = '';
	} else {
		var ajaxaction = 'editurl';
		var originalstoreurl = $("#originalStoreUrl").val();
	}
	
	$.ajax({
    	type: "POST",
    	url: '/ajax/store.php',
    	data: "ajaxaction="+ajaxaction+"&storeurl="+url+"&originalstoreurl="+originalstoreurl,
    	async: false,
    	success: function(msg){
    		if (msg == 'success') {
    			$("#checkstoreurl").html('Congratulations! The Url is available');
    			if (checkFirstDomain(url, ID)) res = true;
    			else res = false;
    		} else {
    			res = false;
    			$("#checkstoreurl").html('Sorry!  The Url exists!');
    		}
        }
	});
	
	return res;
}

function checkStoreName() {
	$("#checkstorename").html('');
	
	var url = $("#Url").val();
	url = url.replace(/(^\s*)|(\s*$)/g, '');
	
	var pattern = /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	if (url == '' || pattern.test(url) == false) return false;
	
	var name = $("#Name").val();
	name = name.replace(/(^\s*)|(\s*$)/g, '');
	var ID = $("#ID").val();
	
	var res = false;
	
	
	if (ID == undefined) {
		ID = '';
		var ajaxaction = 'addurl';
	} else {
		var ajaxaction = 'editurl';
	}
	
	$.ajax({
    	type: "POST",
    	url: '/ajax/store.php',
    	data: "ajaxaction="+ajaxaction+"&storename="+name+"&storeid="+ID,
    	async: false,
    	success: function(msg){
    		if (msg == 'success') {
    			$("#checkstorename").html('Congratulations! The Store Name is available');
    			if (checkFirstDomain(url, ID)) res = true;
    			else res = false;
    		} else {
    			res = false;
    			$("#checkstorename").html('Sorry!  The Store Name exists!');
    		}
        }
	});
	
	return res;
}

function deleteStoreRel(obj,id) {
	if (confirm('Confirm delete?')) {
		var storeID = $("#storeID"+id).val();
		var siteName = $("#siteName"+id).val();
		var merchantID = $("#merchantID"+id).val();
		
		$.ajax({
	    	type: "POST",
	    	url: '/ajax/store.php',
	    	data: "ajaxaction=delstorerel&siteName="+siteName+"&merchantID="+merchantID+"&storeID="+storeID,
	    	success: function(msg){
	    		if (msg == 'success') {
	    			$(obj).parent().parent().remove();
	    		} else {
	    			alert('Delete fail!');
	    		}
	        }
		});	
	}
}

function deleteStoreCompetitorRel(obj,id) {
	if (confirm('Confirm delete?')) {
		var storeID = $("#ID").val();
		var competitorID = $("#competitorID"+id).val();
		var cpturl = encodeURIComponent($("#competitorUrl"+id).val());
		
		$.ajax({
	    	type: "POST",
	    	url: '/ajax/store.php',
	    	data: "ajaxaction=delstorecompetitorrel&competitorID="+competitorID+"&storeID="+storeID+"&cpturl="+cpturl,
	    	success: function(msg){
	    		if (msg == 'success') {
	    			$(obj).parent().parent().remove();
	    		} else {
	    			alert('Delete fail!');
	    		}
	        }
		});	
	}
}

function deleteRelUrl(obj, id) {
	if (confirm('Confirm delete?')) {
		$.ajax({
	    	type: "POST",
	    	url: '/ajax/store.php',
	    	data: "ajaxaction=delrelurl&ID="+id,
	    	success: function(msg){
	    		if (msg == 'success') {
	    			$(obj).parent().parent().remove();
	    		} else {
	    			alert('Delete fail!');
	    		}
	        }
		});	
	}
}

function optionMove(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
		var go_out = false;
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
    		for(var j=0; j < tbox.options.length; j++) {
    			if (tbox.options[j].value == fbox.options[i].value) {
    				go_out = true;
    				break;
    			}
    		}
    		if(go_out) {
    			fbox.options[i].value = "";
    			fbox.options[i].text = "";
    			continue;
    		}
    		
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			tbox.options[tbox.options.length - 1].selected = true;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function optionMoveAll(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
		var go_out = false;
		if(fbox.options[i].value != "")
		{ 
			for(var j=0; j < tbox.options.length; j++) {
    			if (tbox.options[j].value == fbox.options[i].value) {
    				go_out = true;
    				break;
    			}
    		}
			if(go_out) {
    			fbox.options[i].value = "";
    			fbox.options[i].text = "";
    			continue;
    		}
    		
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			tbox.options[tbox.options.length - 1].selected = true;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
		} 
	} 
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function BumpUp(box)
{ 
	for(var i=0; i<box.options.length; i++)
	{
		if(box.options[i].value == "")
		{
			for(var j=i;j<box.options.length-1;j++)
			{
				box.options[j].value = box.options[j+1].value;
				box.options[j].text = box.options[j+1].text;
			}
			var ln = i;
			break;
		}
	}
	
	if(ln < box.options.length)
	{ 
		box.options.length -= 1;
		BumpUp(box);
	}
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

function isEnter(ev)
{
	var code = ev.which? ev.which : ev.keyCode;
	if(code == 13) return true;
	return false;
}

function removeMerchant()
{
	fobj = document.getElementById('MerchantID');
	for(var i=0; i < fobj.options.length; i++)
	{
    	if(fobj.options[i].selected && fobj.options[i].value != "")
		{
    		fobj.removeChild(fobj.options[i]);
    		i--;
       }
	}
}

function removeMerchantAll()
{
	fobj = document.getElementById('MerchantID');
	for(var i=0;i < fobj.options.length;)
	{
		if(fobj.options[i].value != "")
		{ 
			fobj.removeChild(fobj.options[i]);
		} 
	} 
}

function defaultSel() {
	$("#categoryRight").find("option[selfid]").each(function(){$(this).attr("selected","")});
	$("#categoryRight").find("option[parentid]").each(function(){$(this).attr("selected","selected")});
	$("#MerchantID").children().each(function(){$(this).attr("selected","selected")});
}

function operationOpen(url) {
	window.open(url, 'newwindow', 'height=800, width=1400, top=100, left=100, toolbar=no,menubar=no, scrollbars=yes, status=no') ;
}

function formSubmit(formid) {
	var rownum = $("#storecompreltable tr").length;
	var rownumlast = rownum -1;
	var lastrobj = $("#storecompreltable tr:eq(" + rownumlast + ")");
	var lastinputobj = lastrobj.find("INPUT[class='newadd']");
	var lastselobj = lastrobj.find("select[class='newadd']");
	var classname = lastinputobj.attr("class");
	if (classname == 'newadd') {
		var res = checkLastTablerow(lastselobj, lastinputobj);
		if (res == false) {
			$("#neaddmsg").html("The last row, competitor and Url are not empty or invalid");
			return false;
		} else {
			$("#neaddmsg").html("");
		}
	}
	
	
	var rownum1 = $("#storerelurltable tr").length;
	var rownumlast1 = rownum1 -1;
	var lastrobj1 = $("#storerelurltable tr:eq(" + rownumlast1 + ")");
	var lastinputobj1 = lastrobj1.find("INPUT[class='newaddnameinput']");
	var lastinputobj2 = lastrobj1.find("INPUT[class='newaddurlinput']");
	var classname1 = lastinputobj1.attr("class");
	if (classname1 == 'newaddnameinput') {
		var res1 = checkLastTablerow1(lastinputobj1, lastinputobj2);
		if (res1 == false) {
			$("#relurlmsg").html("The last row, Name and Url are required! or Url is invalid");
			return false;
		} else {
			$("#relurlmsg").html("");
		}
	}
	
	if ($("#etype").val() == 'FULL' || $("#etype").val() == 'ADD') {
		if ($("#Url").val() != '') {
			if (!checkStoreName()) {
				scrollTo(0,0);
				return false;
			}
		}
	}
    
	if ($("#MerchantID option").size() > 0 && $("#PStable tr").size() > 1) {
		var psflag = true;
		var orderArr = new Array();
		$("#PStable tr[programid]").each(function(){
			var programid = $(this).attr('programid');
			var order = $("#order_" + programid).val();
			var affurldefault = $("#affurldefault_" + programid).val();
			if ($.trim(order) == '' || $.trim(order) == 0 || $.trim(affurldefault) == '') {
				psflag = false;
				alert("Order and Affiliate Default URL are not empty or zero");
				return false;
			}
			
			for (i in orderArr) {
				if (orderArr[i] == order) {
					psflag = false;
					alert("Order Duplicated");
					return false;
				}
			}
			orderArr.push(order);
		});
		
		if (!psflag) return false;
	}
	
	defaultSel();
	
	if($("#pagename").val() == "storeadd"){
		checkAffUrl(formid);
		return false;
	}else{
		$("#"+formid).submit();
		return true;
	}
}

function checkAffUrl(formid){
	var res = true;
	var action = $("#action").val();
	$("#action").val("checkaffurl");
	 $('#' + formid).ajaxSubmit({
	        url:"/front/store_add.php?action=checkaffurl",
	        success: function(data) {
		 		$("#action").val(action);
			 	if(data == "success"){
			 		$("#"+formid).submit();
			 	}else{
			 		alert(data);
			 	}
	        }
	    });
	$("#action").val(action);
	return false;
}

function removeStoreformSubmit(formid, storenameid, storeurlid) {
	if ($("#hasmerchant").val() == 1) {
		alert("There are merchant(s) associated with this store. Please delete those association first!");
		return false;
	}
	if ($("#hasprogram").val() == 1) {
		alert("There are program(s) associated with this store. Please delete those association first!");
		return false;
	}
	if ($("#form1").validationEngine('validate')) {
		if(confirm('Please confirm to delete store: ' + $("#"+storenameid).val() + '(' + $("#"+storeurlid).val() + ')?')) {
			$("#"+formid).submit();
		}
	}
}

function mergeStoreformSubmit(formid, frstorenameid, frstoreurlid, tostorenameid, tostoreurlid) {
	if ($("#hasmerchant").val() == 1) {
		alert("There are merchant(s) associated with this store.");
		return false;
	}
	if ($("#hasprogram").val() == 1) {
		alert("There are program(s) associated with this store.");
		return false;
	}
	
	if ($("#form1").validationEngine('validate')) {
		if ($("#FromStoreID").val() == $("#ToStoreID").val()) {
			alert('From Store and To Store are duplicate!');
			return false;
		}
		if(confirm('Please confirm to merge store: ' + $("#"+frstorenameid).val() + '(' + $("#"+frstoreurlid).val() + ') TO ' + $("#"+tostorenameid).val() + '(' + $("#"+tostoreurlid).val() + ')?')) {
			$("#"+formid).submit();
		}
	}
}

function get_ssl_rd_url(url){
	return "https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent(url);
}

function OpenURL(url){
	var url = $("#"+url).val();
	url = url.replace(/(^\s*)|(\s*$)/g, '');
	if (url != '') {
		url = get_ssl_rd_url(url);
		window.open(url);
	}
}

//Get competitors
var competitorsOptions = "<option value=''>--select--</option>";
function getCompetitors() {
	$.ajax({
		type: "POST",
		url: '/ajax/store.php',
		data: "ajaxaction=getcompetitors",
		dataType: 'json',
		async: false,
		success: function(msg){
			if (msg != 'fail') {
				for (var i = 0; i < msg.length; i++) {
					competitorsOptions += "<option value='" + msg[i].ID + "'>" + msg[i].Name + "</option>";
				}
			}
	    }
	});
}
getCompetitors();

var addCompetitorRowFlag = true;
function addStoreCompetitorRel() {
	if (!addCompetitorRowFlag) {
		$("#neaddmsg").html("The last row, competitor and Url are not empty or invalid");
		return false;
	} else {
		$("#neaddmsg").html("");
	}
	
	var rownum = $("#storecompreltable tr").length;
	var rownumlast = rownum -1;
	var lastrobj = $("#storecompreltable tr:eq(" + rownumlast + ")");
	var lastinputobj = lastrobj.find("INPUT[class='newadd']");
	var lastselobj = lastrobj.find("select[class='newadd']");
	var classname = lastinputobj.attr("class");
	if (classname == 'newadd') {
		var res = checkLastTablerow(lastselobj, lastinputobj);
		if (res == false) {
			$("#neaddmsg").html("The last row, competitor and Url are not empty or invalid");
			return false;
		} else {
			$("#neaddmsg").html("");
		}
	}
	var dateObj = new Date();
	var milliSecond = dateObj.getTime();
	
	var urltext = "<input type='text' size=\"45\" class='newadd' id='i_" + milliSecond + "' name='competitorrelurl[]' readonly='readonly' onblur=\"checkUrlToCompetitorDomain($(this).val(),$('#s_" + milliSecond +"').find('option:selected').text())\"/>";
  var competitorsel = "<select class='newadd' id='s_" + milliSecond + "' name='competitorid[]' onchange=\"if ($(this).val() != '') {$('#i_" + milliSecond +"').removeAttr('readonly');}else {$('#i_" + milliSecond +"').val('');$('#i_" + milliSecond +"').attr('readonly','readonly');}\">" + competitorsOptions + "</select>";
  var purpose = "<select name='purpose[]'><option valule='ForTask'>ForTask</option><option valule='ForDeal'>ForDeal</option><option valule='ForTaskAndDeal'>ForTaskAndDeal</option></select>";
  var action = "<input type='button' value='Remove' onclick='removeRow(this);'>";
    
  var row = "<tr><td>" + competitorsel + "</td><td>" + urltext + "</td><td align='center'>" + purpose + "</td><td>" + action + "</td></tr>";
  $("#storecompreltable").append(row);
}

function removeRow(obj) {
	$("#neaddmsg").html("");
	$(obj).parent().parent().remove();
}

function removeRow1(obj) {
	$("#relurlmsg").html("");
	$(obj).parent().parent().remove();
}


function addRelUrl() {
	var rownum = $("#storerelurltable tr").length;
	var rownumlast = rownum -1;
	var lastrobj = $("#storerelurltable tr:eq(" + rownumlast + ")");
	var lastinputobj = lastrobj.find("INPUT[class='newaddnameinput']");
	var lastinputobj1 = lastrobj.find("INPUT[class='newaddurlinput']");
	var classname = lastinputobj.attr("class");
	if (classname == 'newaddnameinput') {
		var res = checkLastTablerow1(lastinputobj, lastinputobj1);
		if (res == false) {
			$("#relurlmsg").html("The last row, Name and Url are required! or Url is invalid");
			return false;
		} else {
			$("#relurlmsg").html("");
		}
	}
	var dateObj = new Date();
	var milliSecond = dateObj.getTime();
	
	var nametext = "<input type='text' class='newaddnameinput' id='n_" + milliSecond + "' name='relurlname[]' size='25px'/>";
	var urltext = "<input type='text' class='newaddurlinput' id='u_" + milliSecond + "' name='relurl[]' size='50px'/>";
    var action = "<input type='button' value='Remove' onclick='removeRow1(this);'>";
    
    var row = "<tr><td>" + nametext + "</td><td>" + urltext + "</td><td>" + action + "</td></tr>";
    $("#storerelurltable").append(row);
}


function checkUrlToCompetitorDomain(url, domain) {
	var _url = url.replace(/^\s*|\s*$/g, "");
	var _domain = domain.replace(/^\s*|\s*$/g, "");
	
	$.ajax({
		type: "POST",
		url: '/ajax/store.php',
		data: "ajaxaction=checkurldomaincompetitors&url=" + encodeURIComponent(_url) + "&domain=" + _domain,
		dataType: 'json',
		async: false,
		success: function(res){
			if (res.flag == 'fail') {
				addCompetitorRowFlag = false;
				alert(res.msg);
			} else {
				addCompetitorRowFlag = true;
				$("#neaddmsg").html("");
			}
	    }
	});
}

function checkLastTablerow(sel, inp) {
	var pattern = /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	
	var selval = sel.val();
	var urlval = inp.val();
    if (selval == '') return false;
    if (urlval == '' || pattern.test(urlval) == false) return false;
    
	return true;
}

function checkLastTablerow1(inp, inp1) {
	var pattern = /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	
	var nameval = inp.val();
	nameval = nameval.replace(/^\s*|\s*$/g, "");
	
	var urlval = inp1.val();
	urlval = urlval.replace(/^\s*|\s*$/g, "");
	
    if (nameval == '') return false;
    if (urlval == '' || pattern.test(urlval) == false) return false;
    
	return true;
}

function addEventOnCategory() {
	$("#categoryLeft").unbind('click', toggleDisplay);
	$("#categoryLeft").bind('click', toggleDisplay);
	eval("undisplayAllCategory();");
}

function toggleDisplay() {
    $("#categoryLeft").find("option:selected[selfid]").each(function(){
	    $("#categoryLeft").find("option[parentid=" + $(this).attr("selfid") + "]").each(function(){
	    	var _this = this;
	    	$(this).parent().each(function(){
	  	        if (!$.nodeName(this, 'SPAN')) {
	  	    	    $(_this).wrap("<span style='display:none'></span>");
	  		    } else {
	  			    $(_this).unwrap("<span style='display:none'></span>");
	  		    }
	  	    });
	    });
    });
}

function displayAllCategory() {
	$("#categoryLeft").find("option[parentid]").each(function(){
		var _this = this;
		$(this).parent().each(function(){
	        if ($.nodeName(this, 'SPAN')) {
	        	$(_this).unwrap("<span style='display:none'></span>");
		    }
	    });
	});
}

function undisplayAllCategory() {
	$("#categoryLeft").find("option[parentid]").each(function(){
		var _this = this;
		$(this).parent().each(function(){
	        if (!$.nodeName(this, 'SPAN')) {
	        	$(_this).wrap("<span style='display:none'></span>");
		    }
	    });
	});
}

function move(from, to){
    var fobj = $("#"+from);
    var tobj = $("#"+to);
    
    fobj.find("option:selected[parentid]").each(function(){
       var currentFPobj = fobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var childrenlen = fobj.find("option[parentid=" + $(this).attr("parentid") + "]").size();
       var toparentoptionlen = currentTPobj.size();
       
       if (toparentoptionlen == 0) {
           tobj.append("<option selfid=" + currentFPobj.attr("selfid") + " style='font-weight:bold;'>" + currentFPobj.html() + "</option>");
           var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       }
       
       currentTPobj.after("<option parentid=" + $(this).attr("parentid") + " value=" + $(this).val() + ">" + $(this).html() + "</option>");
       $(this).remove();
       if (childrenlen == 1) currentFPobj.remove();
    });
}

function moveAll(from, to){
    var fobj = $("#"+from);
    var tobj = $("#"+to);
    
    fobj.find("option[parentid]").each(function(){
       var currentFPobj = fobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var childrenlen = fobj.find("option[parentid=" + $(this).attr("parentid") + "]").size();
       var toparentoptionlen = currentTPobj.size();
       
       if (toparentoptionlen == 0) {
           tobj.append("<option selfid=" + currentFPobj.attr("selfid") + " style='font-weight:bold;'>" + currentFPobj.html() + "</option>");
           var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       }
       
       currentTPobj.after("<option parentid=" + $(this).attr("parentid") + " value=" + $(this).val() + ">" + $(this).html() + "</option>");
       $(this).remove();
       if (childrenlen == 1) currentFPobj.remove();
    });
    addEventOnCategory();
}

function moveContainFirstLevel(from, to, callbackflag, isright){
    var fobj = $("#"+from);
    var tobj = $("#"+to);
    
    fobj.find("option:selected").each(function(){
    	if (callbackflag == 1) {
    		moveContainFirstLevel(from, to, 3, isright);
    	} else if (callbackflag == 2 && (typeof $(this).attr('selfid') != 'undefined')) {
    		fobj.find("option[parentid=" + $(this).attr("selfid") + "]").attr("selected","selected");
    		$(this).removeAttr('selected');
    		moveContainFirstLevel(from, to, 3, isright);
    	} else {
    		if (typeof $(this).attr('selfid') != 'undefined') {
    			var currentFPobj = fobj.find("option[selfid=" + $(this).attr("selfid") + "]");
    			var currentTPobj = tobj.find("option[selfid=" + $(this).attr("selfid") + "]");
                var childrenlen = fobj.find("option[parentid=" + $(this).attr("selfid") + "]").size();
                var toparentoptionlen = currentTPobj.size();
                
                if (typeof isright == 'undefined') {
                	if (typeof currentTPobj.attr('selfid') == 'undefined') {
                    	tobj.append($(this).clone(true));
                    }
                } else {
                	if ((typeof currentTPobj.attr('selfid') == 'undefined') && (childrenlen == 0)) {
                    	tobj.append($(this).clone(true));
                    }
                }
                
                if (childrenlen == 0) currentFPobj.remove();
    		} else {
    			var currentFPobj = fobj.find("option[selfid=" + $(this).attr("parentid") + "]");
                var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
                var childrenlen = fobj.find("option[parentid=" + $(this).attr("parentid") + "]").size();
                var toparentoptionlen = currentTPobj.size();
                
                if (toparentoptionlen == 0) {
    	            tobj.append("<option selfid=" + currentFPobj.attr("selfid") + " value=" + currentFPobj.attr("selfid") + " style='font-weight:bold;'>" + currentFPobj.html() + "</option>");
    	            var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
    	        }
    	        
    	        currentTPobj.after("<option class=\"sub\" parentid=" + $(this).attr("parentid") + " value=" + $(this).val() + ">" + $(this).html() + "</option>");
    	        $(this).remove();
    	        if ((typeof isright == 'undefined') && childrenlen == 1) currentFPobj.remove();
    		}  
    	}
     });
    //addEventOnCategory();
}

function moveAllContainFirstLevel(from, to){
    var fobj = $("#"+from);
    var tobj = $("#"+to);
    
    fobj.find("option[parentid]").each(function(){
       var currentFPobj = fobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       var childrenlen = fobj.find("option[parentid=" + $(this).attr("parentid") + "]").size();
       var toparentoptionlen = currentTPobj.size();
       
       if (toparentoptionlen == 0) {
           tobj.append("<option selfid=" + currentFPobj.attr("selfid") + " value=" + currentFPobj.attr("selfid") + " style='font-weight:bold;'>" + currentFPobj.html() + "</option>");
           var currentTPobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
       }
       
       currentTPobj.after("<option class=\"sub\" parentid=" + $(this).attr("parentid") + " value=" + $(this).val() + ">" + $(this).html() + "</option>");
       
       $(this).remove();
       if (childrenlen == 1) currentFPobj.remove();
    });
    
    fobj.find("option[selfid]").each(function(){
    	var flag = false;
    	var selfid = $(this).attr('selfid');
    	
    	tobj.find("option[selfid]").each(function(){
    		if (selfid == $(this).attr('selfid')) {
    			flag = true;
    			return false;
    		}
    	});
    	
    	if (!flag) {
    		tobj.append("<option selfid=" + selfid + " value=" + selfid + " style='font-weight:bold;'>" + $(this).html() + "</option>");
    	}
    	
    	$(this).remove();
    	
    });
    
    addEventOnCategory();
}

function reloadCategory() {
	var fobj = $("#categoryRight");
    var tobj = $("#categoryLeft");
    
    tobj.find("option[parentid]").each(function(){
    	var flag = false;
    	var val = $(this).val();
    	var tparentoptobj = tobj.find("option[selfid=" + val + "]");
    	var childrenlen = tobj.find("option[parentid=" + val + "]").size();
    	fobj.find("option[parentid]").each(function(){
    		if (val == $(this).val()) {
    			flag = true;
				return false;
    		}
    	});
    	
    	if (flag) {
			$(this).remove();
			if (childrenlen == 1) tparentoptobj.remove();
		}
    });
}

function reloadCategory1() {
	//return true if right is empty
	if($("#categoryRight option").length == 0) return true;
	
	var fobj = $("#categoryRight");
    var tobj = $("#categoryLeft");
    
    tobj.find("option[parentid]").each(function(){
    	var flag = false;
    	var val = $(this).val();
    	
    	fobj.find("option[parentid]").each(function(){
    		if (val == $(this).val()) {
    			flag = true;
				return false;
    		}
    	});
    	
    	if (flag) {
			var childrenlen = tobj.find("option[parentid=" + $(this).attr("parentid") + "]").size();
			$(this).remove();
			if (childrenlen == 1)
			{
				var tparentoptobj = tobj.find("option[selfid=" + $(this).attr("parentid") + "]");
				tparentoptobj.remove();
			}
		}
    });
    
    tobj.find("option[selfid]").each(function(){
    	var _flag = false;
    	var selfid = $(this).attr('selfid');
    	
    	fobj.find("option[selfid]").each(function(){
    		if (selfid == $(this).attr('selfid') && fobj.find("option[parentid=" + selfid + "]").size() == 0) {
    			_flag = true;
    			return false;
    		}
    	});
    	
    	if (_flag) $(this).remove();
    });
}

function fixEvent(e){
    var evt = (typeof e == "undefined") ? window.event : e;
    return evt;
}

function srcElement(e)
{
	if (typeof e == "undefined") e = window.event;
    var src = document.all ? e.srcElement : e.target;
    
    return src;
}

function trim(text)
{
	if (typeof(text) == "string")
    {
		return text.replace(/^\s*|\s*$/g, "");
    }
    else
    {
    	return text;
    }
}

function ajaxEdit(obj, act, field, ids) {
	var tag = obj.firstChild.tagName;
	var affid = arguments[4];	
	
	if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
	{
	    return;
	}
	
	var org = trim(obj.innerHTML);
	var isIE = window.ActiveXObject ? true : false;
	var val = isIE ? obj.innerText : obj.textContent;
    
	var txt = document.createElement("INPUT");
	txt.value = (val == 'N/A') ? '' : val;
	txt.style.width = (txt.value != '') ? (obj.offsetWidth + 12) + "px" : "400px";
	
	obj.innerHTML = "";
	obj.appendChild(txt);
	txt.focus();
	
	txt.onkeypress = function(e)
	{
	    var evt = fixEvent(e);
	    var obj = srcElement(e);

	    if (evt.keyCode == 13)
	    {
	      obj.blur();

	      return false;
	    }

	    if (evt.keyCode == 27)
	    {
	      obj.parentNode.innerHTML = org;
	    }
	}
	
	txt.onblur = function(e)
	{
		//POR
		var checkPOR = false;
		if(affid == 29 && act =="editps"){			
			var re = /\/0(?=\/|$)/g;
			r = txt.value.match(re);
			if(r != null && r.length > 1){
				checkPOR = true;
			}else{
				txt.value =txt.value.replace(re, "/[SUBTRACKING]");    
			}			
		}
		
		if(checkPOR){
			if(!confirm("Notice: Url has more than one /0 parameter, on this condition the 0 part won't be replaced by [SUBTRACKING]. Are you sure ?")){
				return false;
			}
		}
		
	    if (trim(txt.value).length > 0 && trim(txt.value) != val)
	    {
	    	$.ajax({
	        	type: "POST",
	        	url: '/ajax/store.php',
	        	data: "ajaxaction=" + act + "&field=" + field + "&val=" + encodeURIComponent(trim(txt.value)) + "&ids=" + ids,
	        	dataType: 'json',
	        	success: function(res){
	        		if (res.exec == 'succ') {
	        			obj.innerHTML = res.content;
	        		} else {
	        			obj.innerHTML = org;
	        			if (trim(res.msg) != '') alert(res.msg);
	        		}
	            }
	    	});
	    }
	    else {
	      obj.innerHTML = org;
	    }
	}
}

var locatedCountrys = new Array();
locatedCountrys[''] = 'All';
locatedCountrys['GLOBAL'] = 'GLOBAL(GLOBAL)';
locatedCountrys['EU'] = 'European Union(EU)';
locatedCountrys['AR'] = 'Argentina(AR)';
locatedCountrys['AU'] = 'Australia(AU)';
locatedCountrys['AT'] = 'Austria';
locatedCountrys['BE'] = 'Belgium(BE)';
locatedCountrys['CA'] = 'Canada(CA)';
locatedCountrys['CH'] = 'Switzerland(CH)';
locatedCountrys['CN'] = 'China(CN)';
locatedCountrys['CR'] = 'Costa Rica(CR)';
locatedCountrys['CY'] = 'Cyprus(CY)';
locatedCountrys['CZ'] = 'Czech Republic(CZ)';
locatedCountrys['DK'] = 'Denmark(DK)';
locatedCountrys['SV'] = 'El Salvador(SV)';
locatedCountrys['EE'] = 'Estonia(EE)';
locatedCountrys['FI'] = 'Finland(FI)';
locatedCountrys['FR'] = 'France(FR)';
locatedCountrys['DE'] = 'German(DE)';
locatedCountrys['GI'] = 'Gibraltar(GI)';
locatedCountrys['GP'] = 'Guadeloupe(GP)';
locatedCountrys['GR'] = 'Greece(GR)';
locatedCountrys['HK'] = 'Hong Kong(HK)';
locatedCountrys['IN'] = 'India(IN)';
locatedCountrys['ID'] = 'Indonesia(ID)';
locatedCountrys['IE'] = 'Ireland(IE)';
locatedCountrys['IL'] = 'Israel(IL)';
locatedCountrys['IT'] = 'Italy(IT)';
locatedCountrys['JP'] = 'Japan(JP)';
locatedCountrys['LV'] = 'Latvia(LV)';
locatedCountrys['LU'] = 'Luxembourg(LU)';
locatedCountrys['MA'] = 'Morocco(MA)';
locatedCountrys['MX'] = 'Mexico(MX)';
locatedCountrys['MY'] = 'Malaysia(MY)';
locatedCountrys['NL'] = 'Netherlands(NL)';
locatedCountrys['NO'] = 'Norway(NO)';
locatedCountrys['NZ'] = 'New Zealand(NZ)';
locatedCountrys['PH'] = 'Philippines(PH)';
locatedCountrys['PL'] = 'Poland(PL)';
locatedCountrys['PT'] = 'Portugal(PT)';
locatedCountrys['QA'] = 'Qatar(QA)';
locatedCountrys['RO'] = 'Romania(RO)';
locatedCountrys['ZA'] = 'South Africa(ZA)';
locatedCountrys['SE'] = 'Sweden(SE)';
locatedCountrys['SG'] = 'Singapore(SG)';
locatedCountrys['ES'] = 'Spain(ES)';
locatedCountrys['TW'] = 'Taiwan(TW)';
locatedCountrys['TH'] = 'Thailand(TH)';
locatedCountrys['AE'] = 'United Arab Emirates(AE)';
locatedCountrys['UK'] = 'United Kingdom(UK)';
locatedCountrys['US'] = 'United States(US)';
locatedCountrys['VG'] = 'Virgin Island, British(VG)';

function addMultiEvents(classname, eventname) {
	if (classname == 'partitioncheckbox') {
		var locatedCountrySel = '<select name="locatedcountry" id="locatedcountry">';
		for (var i in locatedCountrys) {
			locatedCountrySel += '<option value="' + i + '">' + locatedCountrys[i] + '</option>';
		}
		locatedCountrySel += '</select>';
		
		var operation = 'Name: <input id="storename" type="text" value="">&nbsp;&nbsp;Located Country: ' + locatedCountrySel + '&nbsp;&nbsp;Url: <input id="storeurl" type="text" value="http://" size="45">&nbsp;&nbsp;<input type="button" value="Partition" onclick="submitPartition(\'' + classname + '\');">';
		
		$("." + classname).bind(eventname, function(){
			if ($("." + classname + ":checked").size() > 0 && $("." + classname + ":checked").size() < $("." + classname).size()) {
				$("#operation").html(operation);
			} else {
				$("#operation").html('');
			}
		});
	} else if (classname == 'initializecheckbox') {
		var operation = '<input type="button" value="Initialize" onclick="submitInitialize(\'' + classname + '\');">';
		
		$("." + classname).bind(eventname, function(){
			if ($(this).attr("checked")) {
				originBgColor = $(this).parent().parent().attr("bgcolor");
				$(this).parent().parent().attr("bgcolor", "#D28EFF");
				
				$("td[name='order']", $(this).parent().parent()).bind('click',function(){
					ajaxEditInitializeField(this, 'order', classname);
				});
				$("td[name='affdefaulturl']", $(this).parent().parent()).bind('click',function(){ajaxEditInitializeField(this, 'affdefaulturl');});
				$("td[name='deepurl']", $(this).parent().parent()).bind('click',function(){ajaxEditInitializeField(this, 'deepurl');});
			} else {
				$(this).parent().parent().attr("bgcolor", originBgColor);
				
				$("td[name='order']", $(this).parent().parent()).unbind('click');
				$("td[name='affdefaulturl']", $(this).parent().parent()).unbind('click');
				$("td[name='deepurl']", $(this).parent().parent()).unbind('click');
			} 
			
			if ($("." + classname + ":checked").size() > 0) {
				$("#operation").html(operation);
			} else {
				$("#operation").html('');
			}
		});
	}
	
}

function submitPartition(classname) {
	var storename = $.trim($("#storename").val());
	var storeurl = $.trim($("#storeurl").val());
	var locatedcountry = $.trim($("#locatedcountry").val());
	
	var pattern = /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	
	if (storename == '' || storeurl == '' || pattern.test(storeurl) == false) {
		alert('Please enter Name and Url, or Url is invalid');
		return false;
	}
	
	var storemerrelarr = new Array();
	$("." + classname + ":checked").each(function(){
		storemerrelarr.push($(this).val());
	});
	if (storemerrelarr.length == 0) {
		alert("Please select items!");
		return false;
	}
	
	var storemerrel = storemerrelarr.join(',');
	
	$.ajax({
		type: "POST",
		url: '/ajax/store.php',
		data: "ajaxaction=partition&storename=" + encodeURIComponent(storename) + "&storeurl=" + encodeURIComponent(storeurl) + "&storeid=" + $("#storeid").val() + "&storemerrel=" + storemerrel + "&locatedcountry=" + locatedcountry,
		dataType: 'json',
		success: function(res){
			if (res.exec == 'succ') {
    			var succLinks = '<a href="/front/store_edit_bd.php?id=' + res.newstoreid + '" target="_blank">Store Edit(BD)</a><a href="/front/store_special_edit.php?editType=initialize&storeId=' + res.newstoreid + '" target="_blank" style="padding-left:20px;">Store Initialize</a>';
    			$("." + classname).attr("checked", false).attr("disabled", true);
    			$("#operation").html(succLinks);
    		} else {
    			if (trim(res.msg) != '') alert(res.msg);
    		}
	    }
	});
}

function submitInitialize(classname) {
	var newRelChecked = new Array();
	var delRel = new Array();
	
	var processFields = ['site', 'merchantid', 'programid', 'affid', 'status', 'order', 'affdefaulturl', 'deepurl', 'affmerid', 'status'];
	
	var iniGoFlag = true;
	$("." + classname).each(function(){
		var dataTmp = new Array();
		for (i in processFields) {
			var _key = processFields[i];
			/*if ($(this).attr("checked") && _key == 'order' && $(this).parent().parent().attr(_key) == 0) {
				iniGoFlag = false;
				return false;
			}
			if ($(this).attr("checked") && _key == 'affdefaulturl' && $(this).parent().parent().attr('status') == 'Active' && trim($(this).parent().parent().attr(_key)) == '') {
				iniGoFlag = false;
				return false;
			}*/
			dataTmp.push(_key + "|" + encodeURIComponent($(this).parent().parent().attr(_key)));
		}
		
		if ($(this).attr("checked")) newRelChecked.push(dataTmp.join(';'));
		else delRel.push(dataTmp.join(';'));
	});
	
	if (!iniGoFlag) {
		alert("Order is invalid or Affiliate Default URL is empty");
		return false;
	}
	
	if (newRelChecked.length == 0) {
		alert("Please select items!");
		return false;
	}
	//alert('df');return false;
	var newRelCheckedStr = newRelChecked.join(',');
	var delRelStr = delRel.join(',');
	
	$.ajax({
		type: "POST",
		url: '/ajax/store.php',
		data: "ajaxaction=initialize&storeid=" + $("#storeid").val() + "&newrelchecked=" + newRelCheckedStr + "&delrel=" + delRelStr,
		dataType: 'json',
		success: function(res){
			if (res.exec == 'succ') {
    			//var succLinks = '<a href="/front/store_edit_full.php?id=' + $("#storeid").val() + '" target="_blank">Store Edit</a>';
    			//$("." + classname).attr("checked", false).attr("disabled", true);
    			//$("#operation").html(succLinks);
				alert("Initialized successfully");
				window.opener.location.reload();
				self.close();
    		} else {
    			if (trim(res.msg) != '') alert(res.msg);
    		}
	    }
	});
	
}

function ajaxEditInitializeField(obj, field, classname) {
	var tag = obj.firstChild.tagName;
	
	if (typeof(tag) != "undefined" && tag.toLowerCase() == "input") return;
	
	var org = obj.innerHTML;
	var isIE = window.ActiveXObject ? true : false;
	var val = isIE ? obj.innerText : obj.textContent;

	var txt = document.createElement("INPUT");
	txt.value = (val == 'N/A') ? '' : val;
	txt.style.width = (obj.offsetWidth + 12) + "px" ;
	
	obj.innerHTML = "";
	obj.appendChild(txt);
	txt.focus();
	
	txt.onkeypress = function(e) {
	    var evt = fixEvent(e);
	    var obj = srcElement(e);

	    if (evt.keyCode == 13) {
	      obj.blur();
	      
	      return false;
	    }

	    if (evt.keyCode == 27) {
	    	obj.parentNode.innerHTML = org;
	    }
	}
	
	txt.onblur = function(e) {
	    if (trim(txt.value) != trim(val)) {
	    	if (field == 'order') {
	    		var orderDuplicateFlag = false;
	    		$("." + classname + ":checked").each(function(){
	    			if ($(this).parent().parent().attr('order') == trim(txt.value)) {
	    				orderDuplicateFlag = true;
	    				alert('Order Duplicated!');
	    				obj.innerHTML = org;
	    				return false;
	    			}
	    		});
	    		
	    		if (!orderDuplicateFlag) {
	    			obj.innerHTML = trim(txt.value);
			    	$(obj).parent().attr(field, trim(txt.value));
	    		}
	    	} /*else if (field == 'affdefaulturl') {
	    		if ($(obj).parent().attr("status") =='Active' && trim(txt.value) == '') {
	    			alert('Not empty');
	    			obj.innerHTML = org;
	    		} else {
	    			obj.innerHTML = trim(txt.value);
			    	$(obj).parent().attr(field, trim(txt.value));
	    		}
	    	}*/ else {
	    		obj.innerHTML = trim(txt.value);
		    	$(obj).parent().attr(field, trim(txt.value));
	    	}
	    } else {
	    	obj.innerHTML = org;
	    	//if ($(obj).parent().attr("status") =='Active' && field == 'affdefaulturl' && trim(txt.value) == '') alert('Not empty');
	        
	    }
	}
}

function searchProgram() {
	$("#programsearch").unautocomplete();
	$("#programsearch").autocomplete("/ajax/store.php?ajaxaction=programsearch", {
		width: 200,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#programsearch").result(function(event, data, formatted) {
		var programStr = '';
		if ($("#PStable tr[programid=" + data[1] + "]").size() > 0 /*|| $("#PStable tr[affid=" + data[2] + "]").size() > 0*/) {
			$("#programsearch").attr("value",'');
			alert("Program duplicated");
			return false;
		}
		
		programStr += '<tr programid="' + data[1] + '" affid="' + data[2] + '">';
		programStr += '<td>' + data[0] + '<input name="psinfo[' + data[1] + '][idinaff]" value="' + data[3] + '" type="hidden"></td>';
		programStr += '<td>' + data[1] + '(' + data[3] + ')<input name="psinfo[' + data[1] + '][programid]" value="' + data[1] + '" type="hidden"></td>';
		programStr += '<td>' + data[4] + '(' + data[2] + ')<input name="psinfo[' + data[1] + '][affid]" value="' + data[2] + '" type="hidden"></td>';
		programStr += '<td><input name="psinfo[' + data[1] + '][order]" type="text" size="5" id="order_' + data[1] + '"></td>';
		programStr += '<td><input name="psinfo[' + data[1] + '][affurldefault]" type="text" size="40" id="affurldefault_' + data[1] + '"></td>';
		programStr += '<td><input name="psinfo[' + data[1] + '][deepurltemplate]" type="text" size="40"></td>';
		programStr += '<td><input type="button" value="Remove" onclick="$(this).parent().parent().remove();"></td>';
		programStr += '</tr>';
		
		$("#programsearch").attr("value",'');
		$("#PStable").append(programStr);
		
	});
}

function UpdateScreenSnapshot(id) {
	$("#ssmsg_td1").html('<img src="/image/loading_red.gif">');
	$("#ssmsg_td2").html('Please wait, the screen snapshot request is in process.');
    $.colorbox({
        inline:true, 
        opacity:0.5, 
        width:"600px", 
        height:"230px",
        overlayClose:false, 
        speed:350, 
        href:"#ssmsg"
    });
	$.ajax({
    	type: "POST",
    	url: '/front/store_thumb.php',
    	data: "action=thumbRequest&storeid="+id,
    	success: function(msg){
            json = eval("("+msg+")");
            if( json.status == true){
            	$("#ssmsg_td1").html('<img src="/image/loading_red.gif">');
            	$("#ssmsg_td2").html('The request is submit successfully and the screen snapshot is being got back.');
				GetScreenSnapshot(id);
            }else{
            	$("#ssmsg_td1").html('<img src="/image/no.jpg">');
            	$("#ssmsg_td2").html('Error: ' + json.msg);
            }
            $.colorbox({
		        inline:true, 
		        opacity:0.5, 
		        width:"600px", 
			    height:"230px", 
		        overlayClose:false, 
		        speed:350, 
		        href:"#ssmsg"
		    });
        }
	});
}
function GetScreenSnapshot(id) {
	$.ajax({
    	type: "POST",
    	url: '/front/store_thumb.php',
    	data: "action=getThumb&storeid="+id,
    	success: function(msg){
            json = eval("("+msg+")");
            if( json.status == true){
            	$("#ssmsg_td1").html('');
				$("#ssmsg_td2").html('');
            	$("#ssmsg_tb").after('<table width="99%" id="ssmsg_tb1" border="0" align="center"><tr><td align="center"><img src="/image/thumb/'+id+'.png?v='+Math.random()+'"></td></tr><tr><td><br/>&nbsp;</td></tr><tr><td align="center"><input type="button" value="Synchronize To Frontend" onclick="SynchronizeToFrontend('+id+')"></td></tr></table>');
            	src = $("#ScreenSnapshot").attr("src");
				$("#ScreenSnapshot").attr("src", src+"?v="+Math.random());
            	$.colorbox({
			        inline:true, 
			        opacity:0.5, 
			        width:"600px", 
			        height:"400px", 
			        overlayClose:false, 
			        speed:350, 
			        href:"#ssmsg",
			        onClosed:function(){
		                $("#ssmsg_tb1").remove();
		            } 
			    });
            }else{
            	$("#ssmsg_td1").html('<img src="/image/no.jpg">');
            	$("#ssmsg_td2").html('Error: ' + json.msg);
            	$.colorbox({
			        inline:true, 
			        opacity:0.5, 
			        width:"600px", 
			        height:"230px", 
			        overlayClose:false, 
			        speed:350, 
			        href:"#ssmsg" 
			    });
            }

        }
	});
}
function SynchronizeToFrontend(id) {
	$("#ssmsg_td1").html('<img src="/image/loading_red.gif">');
	$("#ssmsg_td2").html('Please wait, the screen snapshot is being synchronized to frontend.');
    $.colorbox({
        inline:true, 
        opacity:0.5, 
        width:"600px", 
        height:"230px",
        overlayClose:false, 
        speed:350, 
        href:"#ssmsg"
    });
	$.ajax({
    	type: "POST",
    	url: '/front/store_thumb.php',
    	data: "action=merchantThumb&storeid="+id,
    	success: function(msg){
            json = eval("("+msg+")");
            if( json.status == true){
            	$("#ssmsg_td1").html('<img src="/image/yes.jpg">');
            	$("#ssmsg_td2").html('The screen snapshot is synchronized to frontend successfully.');
            }else{
            	$("#ssmsg_td1").html('<img src="/image/no.jpg">');
            	$("#ssmsg_td2").html('Error: ' + json.msg);
            }
        	$.colorbox({
		        inline:true, 
		        opacity:0.5, 
		        width:"600px",
		        height:"230px", 
		        overlayClose:false, 
		        speed:350, 
		        href:"#ssmsg"
		    });
        }
	});
}
