function OpenUrl(){
	var urlObj = document.getElementById('url');

	window.open(urlObj.value);
}

function OpenCustomDeepUrl(){
	var urlObj = document.getElementById('customurl');
	var strUrl = urlObj.value;
	strUrl.replace('[DEEPURL]', '/');

	window.open(strUrl);
}

function OpenDomainURL(){
	var urlObj = document.getElementById('merchant_original_url');
	window.open("https://edm.megainformationtech.com/rd.php?url=" + encodeURIComponent(urlObj.value));
}

function submitCheck()
{
	var merchanteNameObj = document.getElementById('merchantname');
	if(merchanteNameObj.value.trim().length == 0 ||  merchanteNameObj.value.trim().length > 255 )
	{
		alert('merchant name can not be empty and should less than 255 characters ');
		merchanteNameObj.focus();
		return false;
	}

	if(checkStr(merchanteNameObj.value.trim()))                                                                                                                                                                                                                                                
	{
		alert('Please check merchant title, some word are not permissioned');
		merchanteNameObj.focus();
		return false;
	}	
	
	var urlObj = document.getElementById('merchant_original_url');
	if(!urlObj.value.trim())
	{
		alert("Merchant Domain can not be empty");
		urlObj.focus();
		return false;
	}
	if(!urlObj.value.trim().IsStartWithHttp())
	{
		alert("Merchant Domain must starts with http://");
		urlObj.focus();
		return false;
	}
	if(func_checkmerchantname() == false){
		return false;
	}
	var descObj = document.getElementById('description');
	if(descObj.value.trim())
	{
		var len =descObj.value.replace(/[^\x00-\xff]/g,"**").length;
		if(len > 1000)
		{
			alert('merchant description is too long, should be less than 1000 characters');
			descObj.focus();
			return false;
		}
		if(len < 250)
		{
			alert('merchant description is too short, should be more than 250 characters');
			descObj.focus();
			return false;
		}
	}	 
	if(checkImg(document.getElementById("logo")) == false){
		alert("Failed to add the merchant. Failed to upload the image. Please upload it again and the format should be GIF, JPG, JPEG, PNG or BMP.");
		return false;
	}

	if(checkStr(descObj.value.trim()))
	{
		alert('Please check merchant description, some word are not permissioned');
		descObj.focus();
		return false;
	}

//	var logoObj = document.getElementById('logo');
//	var previewObj = document.getElementById('previewlogo4calcsize');
//	if(logoObj.value && previewObj && !check_image_size(previewObj,200,200,50,50)) return false;
	getselectedmer();
	//getSelectCat();

	return true;
}

/*
function getSelectCat(){
	var obj = document.getElementById("edit_Category");
	var tbox = document.getElementById("selected_mer_cat");
	var tempStr = "";
	for(var i=0; i<tbox.options.length; i++){
		if(i == 0){
			tempStr = tbox.options[i].value
		}else{
			tempStr = tempStr + "||" + tbox.options[i].value;
		}
	}
	obj.value = tempStr;
	
}
*/

function getSelectCat() {
	var CatIds = '';
	
	$("#categoryRight").find("option").each(function(){
		if (typeof $(this).attr("parentid") != 'undefined') CatIds += $(this).val() + "|" + $(this).attr("parentid") + ",";
		else if (typeof $(this).attr("selfid") != 'undefined') CatIds += $(this).val() + "|0,";
	});
	
	if (CatIds != '') $("#edit_Category").val(CatIds);
}

function checkStr(str)
{
	var pattern = /&[a-zA-Z]{2,5};|&#[0-9]{2,5};/;
	if(!pattern.test(str)) return false;
	return true;	
}

function caculateMerDescLength()
{
	var minLen = 250;
	var MaxLen = 1000;
	var PerfectMinLen = 500;
	var PerfectMaxLen = 800;
	var merDescObj = document.getElementById('description');
	var len =merDescObj.value.replace(/[^\x00-\xff]/g,"**").length;
	var DescLenObj = document.getElementById('descriptionlen');
	var str = "Merchant Description Length:";
	str =  str + len + " / "+ MaxLen + " <font color='red'>";

	if(len < minLen) //too short
	{
		str +=  "<b>Too Short!</b>";
	}
	else if(len > MaxLen)//too long
	{
		str +=  "<b>Too Long!</b>";
	}
	else if(len >= PerfectMinLen && len <= PerfectMaxLen)//very good
	{
		str +=  "<b>Perfect!</b>";
	}
	else
	{
		str +=  "<b>Good!</b>";
	}
	DescLenObj.innerHTML = str + "</font>";
	return;
}

function getselectedmer()
{
	var selectedmer = "";
//	var objlist = document.getElementById('list2');
//	
//	for(var i=0; i<objlist.options.length; i++)
//	{ 
//		if(objlist.options[i].value != "")
//		{ 
//			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
//		 }
//	}
	
	/*var objlist = document.getElementById('list4');
	for(var i=0; i<objlist.options.length; i++)
	{ 
		if(objlist.options[i].value != "")
		{ 
			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
		 }
	}*/
	
//	var objlist = document.getElementById('list6');
//	for(var i=0; i<objlist.options.length; i++)
//	{ 
//		if(objlist.options[i].value != "")
//		{ 
//			selectedmer = selectedmer ? selectedmer + ',' + objlist.options[i].value : objlist.options[i].value;
//		 }
//	}
	
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
	
	 var objselectedmer = document.getElementById('selectedmer');
	 objselectedmer.value = selectedmer;
	 return;
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

$(document).ready(function(){
	if(window.navigator.userAgent.indexOf("MSIE") >= 1){
		set_preview_logo()
		$("#logo").change(function(){ 
				set_preview_logo();
	//			handleFiles(this.files); 
			});
	}else{
		$("#logo").change(function(){
			handleFiles(this.files);
		});
	}
//	set_preview_logo()
	$("#logo").change(function(){set_preview_logo();});
});

function filterTagLetter(){		
	var startwith = $("#tagfilter").val();
	var verifyArr  = {'ajaxTag':'getTagsByLetter'};
	
	var temp_html = $("#all_tags_filter").html();
	$("#all_tags_filter").hide();
	$("#all_tags_filter_loading").show();
	var site = $("#site").val();
	$.ajax({
		type: "post",		
		url: "/editor/search.php?ajaxTag=getTagsByLetter" + "&startwith=" + startwith + "&site=" +  site,
		data: $.param(verifyArr),
		success: function (tags) {				
			$("#list1").html(tags);
			$("#all_tags_filter_loading").hide();
			$("#all_tags_filter").show();	
		}		
	});	
}

function addBundle(){
	var bundlename = $("#bundlename").val();
	if(bundlename=="add_new"){
		$("#newbundlename_div").show();
	}else{
		$("#newbundlename").val("");
		$("#newbundlename_div").hide();
	}
}

function CtoH(obj)
{
	
	var str = obj.value;
	var result = "";
	var alertchars = "";
	
	for (var i = 0; i < str.length; i++)
	{
		
		var charcode = str.charCodeAt(i);
		alert( charcode);
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
		alert("there are some invalid chars: " + alertchars);
	}
}
	
function CtoH2(obj)
{
	var str = obj.value;
	var result = "";
	var alertchars = "";
	var div_result = "";
	
	for (var i = 0; i < str.length; i++)
	{
		var charcode = str.charCodeAt(i);
		if(charcode == 8211)
		{
			result += String.fromCharCode(charcode);	// –
			div_result += String.fromCharCode(charcode);
		}
		else if(charcode == 8222)
		{
			result += String.fromCharCode(charcode);	//„
			div_result += String.fromCharCode(charcode);
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

function option_remove_mer(fboxid,tboxid)
{
	tbox = document.getElementById(tboxid)
	fbox = document.getElementById(fboxid)
	var p = new Array(400);
	var f = new Array(400);
	
	for(var i=0; i<tbox.options.length; i++)
	{
		var pid = tbox.options[i].value;
		var sid = "";
		var temp = pid.split("_");
		
		if(temp[0] == "P" && tbox.options[i].selected){
			var x = "" + temp[1];
			p[x] = true;
		}
	}
	
	var j = 0;
	var length = tbox.options.length;
	for(var i=length -1 ; i >=0 ; i--)
	{
		
		var pid = tbox.options[i].value;
		var spid = "";
		var ppid = "";
		var temp = pid.split("_");
		if(temp[0] == "S"){
			spid = temp[1];
		
		}
		
    	if((tbox.options[i].selected || p[spid] == true) && tbox.options[i].value != "")
		{
			
			for(var j=0; j<fbox.options.length; j++){
				var tempid = fbox.options[j].value;
				var tempArr = tempid.split("_");
				var tmpId = tempArr[1];
				if(spid == tmpId){
					var no = new Option();
					no.value = tbox.options[i].value;
					no.text = tbox.options[i].text;
					
					fbox.options.add(no, j +1);
					break;
				}
				
			}
			tbox.options.remove(i);
       }
	}

	checkToMerList(tboxid);
}

function displayImage(container, dataURL) {
	var img = document.createElement('img');
	img.src = dataURL;
	//img.style.width="200px";
	//img.style.height="50px";
	
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

function ajaxGetFilterTags(url, type, kwid, toselid, fromselid, site) {
	var toseltagids = '';
	var url = '';
	var kw = $.trim($("#" + kwid).val());
	
	if ($.trim(url).length) url = $.trim(url);
	else url = '/editor/merchant.php';
	
	$("#" + toselid).children().each(function(){
		toseltagids += $(this).val() + ',';
	});
	toseltagids = $.trim(toseltagids);
	toseltagidslen = toseltagids.length;
	
	if (toseltagidslen) {
		toseltagids = toseltagids.substr(0,toseltagidslen - 1);
	}
	
	$("#" + fromselid).children().remove();
	
	$.ajax({
		type: "POST",
		url: url,
		data: "action=ajaxgetags&site=" + site + "&type=" + $.trim(type) + "&kw=" + encodeURIComponent(kw) + "&exceptids=" + toseltagids,
		dataType: 'json',
		success: function(res){
			if (res.status == 'succ') {
				var optionstr = '';
				for (var i = 0; i < res.data.length; i++) {
					optionstr += "<option value='" + res.data[i].tagid + "'>" + res.data[i].tagname + "</option>";
				}
				
				if (optionstr != '') $("#" + fromselid).append(optionstr);
			}
	    }
	});
}

function multipleAddOnclickOnSearch(site) {
	$("#brandsearchbtn").bind('click.SearchBrand', function(){
		ajaxGetFilterTags('','brand','brandsearch','list4','list3',site);
	}).trigger('click.SearchBrand');
	
    $("#brandsearchreset").bind('click', function(){
    	$("#brandsearch").val('');
    	$("#brandsearchbtn").trigger('click.SearchBrand');
    });
     
    $("#brandsearch").bind('keydown', function(event){
    	if (event.keyCode == 13) {
    		ajaxGetFilterTags('','brand','brandsearch','list4','list3',site);
    		return false;
    	}
    });
    
    
    $("#alltagsearchbtn").bind('click.SearchAlltag', function(){
		ajaxGetFilterTags('','alltag','alltagsearch','list2','list1',site);
	}).trigger('click.SearchAlltag');
    
    $("#alltagsearchreset").bind('click', function(){
    	$("#alltagsearch").val('');
    	$("#alltagsearchbtn").trigger('click.SearchAlltag');
    });
    
    $("#alltagsearch").bind('keydown', function(event){
    	if (event.keyCode == 13) {
    		ajaxGetFilterTags('','alltag','alltagsearch','list2','list1',site);
    		return false;
    	}
    });
    
    
    $("#productsearchbtn").bind('click.SearchProduct', function(){
		ajaxGetFilterTags('','product','productsearch','list6','list5',site);
	}).trigger('click.SearchProduct');
	
    $("#productsearchreset").bind('click', function(){
    	$("#productsearch").val('');
    	$("#productsearchbtn").trigger('click.SearchProduct');
    });
    
    $("#productsearch").bind('keydown', function(event){
    	if (event.keyCode == 13) {
    		ajaxGetFilterTags('','product','productsearch','list6','list5',site);
    		return false;
    	}
    });
    
    $("#seasonalsearchbtn").bind('click.SearchProduct', function(){
		ajaxGetFilterTags('','seasonal','seasonalsearch','list8','list7',site);
	}).trigger('click.SearchProduct');
	
    $("#seasonalsearchreset").bind('click', function(){
    	$("#seasonalsearch").val('');
    	$("#seasonalsearchbtn").trigger('click.SearchProduct');
    });
    
    $("#seasonalsearch").bind('keydown', function(event){
    	if (event.keyCode == 13) {
    		ajaxGetFilterTags('','seasonal','seasonalsearch','list8','list7',site);
    		return false;
    	}
    });
    
    
    $("#relatedsearchbtn").bind('click.SearchRelated', function(){
//		ajaxGetFilterTags('','related','relatedsearch','list10','list9',site);
		ajaxGetFilterTags('','alltag','relatedsearch','list10','list9',site);
	}).trigger('click.SearchRelated');
	
    $("#relatedsearchreset").bind('click', function(){
    	$("#relatedsearch").val('');
    	$("#relatedsearchbtn").trigger('click.SearchRelated');
    });
     
    $("#relatedsearch").bind('keydown', function(event){
    	if (event.keyCode == 13) {
    		ajaxGetFilterTags('','related','relatedsearch','list10','list9',site);
    		return false;
    	}
    });
}
// /.\.jpg|.\.gif|.\.jpeg|.\.png|.\.bmp/i;
function checkImg(obj) {
	if(obj.value == ""){
		return true;
	}
	var imgname = obj.value.substring(obj.value.lastIndexOf("."), obj.value.length);
	imgname = imgname.toLowerCase();
	if ((imgname != ".jpg") && (imgname != ".gif") && (imgname != ".jpeg") && (imgname != ".png") && (imgname != ".bmp") && (imgname != ".svg")) {
		return false;
	}
	return true;
}