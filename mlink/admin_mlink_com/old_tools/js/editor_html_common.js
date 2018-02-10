var merListSource = null;
/*
onload=function (){
	merListSource = document.getElementById("mer_category_list").innerHTML;
	checkFromTo('mer_category_list', 'selected_mer_cat');
}
*/
function CtoH(obj)
{
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
			obj.focus();
			alert("there are some invalid chars: " + alertchars);
			
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

function CtoH2(obj, str)
{
//	var str = obj.value;
	var result = "";
	var alertchars = "";
	var div_result = "";
	
	str = str.replace(/&ldquo;/g, String.fromCharCode(8220));
	str = str.replace(/&rdquo;/g, String.fromCharCode(8221));
	str = str.replace(/&lsquo;/g, String.fromCharCode(8216));
	str = str.replace(/&rsquo;/g, String.fromCharCode(8217));
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
//		obj.value = result;
//		alert(obj.value);
		CKEDITOR.instances.description.setData(result);
		alert(CKEDITOR.instances.description.getData());
		if(alertchars != "")
		{
			obj.focus();
			alert("there are some invalid chars: " + alertchars);
			
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

function CtoH3(obj, str)
{
	var alertchars = "";
	var div_result = "";

	str = str.replace(/&ldquo;/g, String.fromCharCode(8220));
	str = str.replace(/&rdquo;/g, String.fromCharCode(8221));
	str = str.replace(/&lsquo;/g, String.fromCharCode(8216));
	str = str.replace(/&rsquo;/g, String.fromCharCode(8217));
	for (var i = 0; i < str.length; i++)
	{
		var charcode = str.charCodeAt(i);
		if((charcode == 12288)||(charcode == 12290)||(charcode == 8216)||(charcode == 8217)||(charcode == 8220)||(charcode == 8221)||(charcode == 8212)||(charcode == 65509)||(charcode == 8361)||(charcode == 8364)||(charcode > 65280 && charcode < 65375)||(charcode > 255))
		{
			if(alertchars) alertchars += "," + String.fromCharCode(charcode) + "(" + charcode + ")";
			else alertchars = String.fromCharCode(charcode) + "(" + charcode + ")";
			
			div_result += "<span style='color:white;font-weight:900;background:#3297FD;padding-left:5px;padding-right:5px'>"+String.fromCharCode(charcode)+"</span>";
		}
		else
		{
			div_result += String.fromCharCode(charcode);
		}
	}
	
	var div = "#ctoh"+$(obj).attr("id");	
	if(alertchars != "")
	{
		obj.focus();
		alert("there are some invalid chars: " + alertchars);
		
		var div = "#ctoh"+$(obj).attr("id");
		if($("div").index($(div)) == -1){
			$("#message_pos").after("<div id='ctoh"+$(obj).attr("id")+"' active='y' style='background:#FFFFCC;border:1px solid;border-color:#999;font-size:14px;color:#000;font-family:Courier;'></div>");
		}
		$(div).css({"width":$(obj).outerWidth()});
		$(div).html(div_result);
		$(div).show();
		location.hash="message_pos";
		return false;
	}else{
		$(div).hide();
	}

	return true;
}
function CtoHDeal(obj)
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

function CtoHDeal1(obj)
{
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
			window.setTimeout(function(){location.hash="message_pos";;},0);
//			obj.focus();
//			
			var div = "#ctoh"+$(obj).attr("id");
			if($("div").index($(div)) == -1){
				$("#message_pos").after("<div id='ctoh"+$(obj).attr("id")+"' active='y' style='background:#FFFFCC;border:1px solid;border-color:#999;font-size:14px;color:#000;font-family:Courier;'></div>");
			}
			alert($("#message_pos"));
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
function checkIsValidDate(dateStr)
{
   var pattern = /^((\d{4})|(\d{2}))-(\d{2})-(\d{2})$/;
   if(!pattern.test(dateStr)) return false;
   return true;
}

function checkIsValidDateTime(dateTimeStr)
{
   var pattern = /^((\d{4})|(\d{2}))-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/;
   if(!pattern.test(dateTimeStr)) return false;
   return true;
}

function checkIsInteger(str)
{
   if(/^(\-?)(\d+)$/.test(str)) return true;
   return false;
}

String.prototype.trim = function()
{
   return this.replace(/(^[\s]*)|([\s]*$)/g,"");
}

String.prototype.IsStartWithHttp = function()
{ 
	if(this.trim() == '') return true;
	var pattern = /^https{0,1}:\/\/.+$/i;
	if(pattern.exec(this)) return true;
	return false;
}



function option_move(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function checkToMerList(tboxid){
	
	tbox = document.getElementById(tboxid);
	
	var j = 0;
	var psCnt = 0;
	var length = tbox.options.length - 1;
	for(var i = length; i >= 0; i--){
		
		var id = tbox.options[i].value;
		var idArr = id.split("_");
		
		if(idArr[0] == "P"){
			if(psCnt == 0){
				tbox.options.remove(i);
			}
			psCnt = 0;
		}else{
			psCnt++;
		}
	}
}

function option_removeall_mer(fboxid,tboxid)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].value != "")
		{
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);

	document.getElementById(tboxid).innerHTML = merListSource;
}



function option_remove(fboxid,tboxid)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
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



function option_removeall(fboxid,tboxid)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].value != "")
		{
			fbox.options[i].value = "";
			fbox.options[i].text = "";
       }
	}
	BumpUp(fbox);
}

function option_copy(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
			var no = new Option();
			no.value = fbox.options[i].value;
			no.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = no;
			fbox.options[i].selected = false;
       } 
	}
	if(sortitems) SortD(tbox);
}

function option_add_merlist(fboxid,tboxid)
{
	var flag = arguments[2];
	
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	var tboxArr = new Array();
	var movId = null;
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].selected && fbox.options[i].value != "")
		{
    		movId = i;
    		var sId = fbox.options[i].value;
    		var valuetext = fbox.options[i].text;
    		var pId = "";
    		var temp = sId.split("_");
    		if(temp[0] == "P"){
    			continue;
    		}
    		pId = "P_" + temp[1] + "_0";
    		var tt = "";
    		pi = getOption(fbox, pId);
    		
    		si = getOption(tbox, pId);
    		
    		if(si === false){
    			var no = new Option();
    			no.value = pId;
    			no.text = fbox.options[pi].text;
    			no.style.color   =   "green"; 
    			no.style.fontSize   =   "16px"; 
    			no.style.fontWeight   =   "bold"; 
    			tbox.options[tbox.options.length] = no;
    			if(si == false && temp[0] != "P"){
	    			no = new Option();
	    			no.value = fbox.options[i].value;
	    			no.text = fbox.options[i].text;
	    			tbox.options[tbox.options.length] = no;
    			}
    		}else{
    			
    			ssi = getOption(tbox, sId);
    			
    			if(ssi === false){
	    			no = new Option();
	    			no.value = fbox.options[i].value;
	    			no.text = fbox.options[i].text;
	    			
	    			tbox.options.add(no, si +1);
    			}
    		}
    		
    		if(flag !== true){
    			i--;
//    			fbox.options.remove(movId);
    		}
    		
    		checkFromTo(fboxid,tboxid);

		}
	}
	if(flag !== true){
//		fbox.options.remove(movId);
	}

}


function checkFromTo(fboxid,tboxid)
{
	var deleteArr = new Array();
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<tbox.options.length; i++)
	{
		for(var j=0; j<fbox.options.length; j++){
			var fId = fbox.options[j].value;
			var tId = tbox.options[i].value;
			var temp = fId.split("_");
    		if(temp[0] == "P"){
    			continue;
    		}
			if(tId == fId){
//				alert(tId + " : " + fId + " : " + j);
				fbox.options.remove(j);
			}
		}
	}
	
}
function getOption(selectObj, option){
	for(var i=0; i<selectObj.options.length; i++)
	{
		if(selectObj.options[i].value == option){
			return i;
		}
	}
	return false;
}

function checkSubCount(selectObj, optionValue){
	for(var i=0; i<selectObj.options.length; i++)
	{
		if(selectObj.options[i].value == option){
			return i;
		}
	}
	return false;
}

function option_copyall(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0; i<fbox.options.length; i++)
	{
    	if(fbox.options[i].value != "")
		{
			var no = new Option();
			no.value = fbox.options[i].value;
			no.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = no;
			fbox.options[i].selected = false;
       } 
	}
	if(sortitems) SortD(tbox);
}

function option_moveall(fboxid,tboxid,sortitems)
{
	fbox = document.getElementById(fboxid);
	tbox = document.getElementById(tboxid)
	for(var i=0;i<fbox.options.length;i++)
	{
		if(fbox.options[i].value != "")
		{ 
			var newoption = new Option();
			newoption.value = fbox.options[i].value;
			newoption.text = fbox.options[i].text;
			tbox.options[tbox.options.length] = newoption;
			fbox.options[i].value = "";
			fbox.options[i].text = "";
		} 
	} 
	BumpUp(fbox);
	if(sortitems) SortD(tbox);
}

function option_swap(objSel,index1,index2)
{
	var newoption = new Option();
	newoption.value = objSel.options[index1].value;
	newoption.text = objSel.options[index1].text;

	objSel.options[index1].value = objSel.options[index2].value;
	objSel.options[index1].text = objSel.options[index2].text;
	
	objSel.options[index2].value = newoption.value;
	objSel.options[index2].text = newoption.text;
}

function option_move_up(objSel)
{
	for(var i=0;i<objSel.options.length;i++)
	{
		if(objSel.options[i].selected)
		{
			if(i == 0) return;//cant move up the first item
			option_swap(objSel,i,i-1);
			objSel.options[i].selected = false;
			objSel.options[i-1].selected = true;
		}
	}
}

function option_move_down(objSel)
{
	if(objSel.options.length == 0) return;
	for(var i=objSel.options.length-1;i>=0;i--)
	{
		if(objSel.options[i].selected)
		{
			if(i == objSel.options.length-1) return;//cant move up the first item
			option_swap(objSel,i,i+1);
			objSel.options[i+1].selected = true;
			objSel.options[i].selected = false;
		}
	}
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

function check_image_size(obj,allow_min_width,allow_max_width,allow_min_height,allow_max_height)
{
	var image_src = obj.src;
	var check_status = false;

	if(allow_min_width || allow_max_width || allow_min_height || allow_max_height)
	{
		check_status = true;
		if(obj.readyState)
		{
			if(obj.readyState != "complete") check_status = false;
		}
		else if(!obj.complete) check_status = false;

		if(check_status == false)
		{
			alert("image is not ready");
			return false;
		}
	}
	
	var image_file_size = obj.fileSize;
	var image_width = obj.width;
	var image_height = obj.height;

	//alert(image_file_size + ":" + image_width + ":" + image_height);

	if(allow_min_width)
	{
		if(image_width < allow_min_width)
		{
			alert("image width is too small.");
			return false
		}
	}
	
	if(allow_max_width)
	{
		if(image_width > allow_max_width)
		{
			alert("image width is too lager.");
			return false
		}
	}

	if(allow_min_height)
	{
		if(image_height > allow_min_height)
		{
			alert("image height is too lager.");
			return false
		}
	}

	if(allow_max_height)
	{
		if(image_height > allow_max_height)
		{
			alert("image height is too lager.");
			return false
		}
	}

	return true;
}
