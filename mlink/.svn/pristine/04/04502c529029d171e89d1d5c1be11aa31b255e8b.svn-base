function addColor(obj) {
    originColor = $(obj).attr("bgColor");
    $(obj).attr("bgColor", "#FFFFBB");
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

iswaitforcheck = false;
resolvemark = '';
closemark = '';
sleepday = 0;
function show_msg() {
	$("#msgbg").css("display","block");
	$("#msgdiv").css("display","block");
}

function close_msgbox() {
	iswaitforcheck = false;
	resolvemark = '';
	closemark = '';
	sleepday = '';
	
	if (arguments.length > 0) {
		if (arguments[1] == 'resolve') {
			if ($("#waitforcheck").attr("checked") == true) iswaitforcheck = true;
			resolvemark = $("#resolvemark").val();
			ajaxChangeStatus(arguments[0], arguments[1]);
		} else if (arguments[1] == 'close') {
			closemark = $("#closemark").val();
			sleepday = $("#sleepday").val();
			sleepday = trim(sleepday);
			sleepday = Math.abs(sleepday);
			if (sleepday == '') sleepday = 0;
            
			ajaxChangeStatus(arguments[0], arguments[1]);
		}
	}
	$("#msgbg").css("display","none");
	$("#msgdiv").css("display","none");
}

function appendLayer2AjaxChangeStatus(issueid, process) {
	var process = process.replace(/^\s*|\s*$/g, "");
	var str = '';
	$("#messageLayer").html('');
	
	if (process == "resolve") {
		str = '<div id="msgbg" style="display:none;"></div><div id="msgdiv" style="display:none"><h2>信息提示</h2><div id="msgbox"><table><tr><td><textarea id="resolvemark" rows="4" cols="30"></textarea><br /><input name="waitforcheck" id="waitforcheck" type="checkbox" value="WAITFORCHECK"> Let machine check automatically.</td></tr><tr><td><input class="btn" type="button" value="确 定" onclick="close_msgbox(\''+issueid+'\', \''+process+'\');"><input class="btn1" type="button" value="取 消" onclick="close_msgbox();"></td></tr></table></div></div>';
	} else if (process == "close") {
		str = '<div id="msgbg" style="display:none;"></div><div id="msgdiv" style="display:none"><h2>信息提示</h2><div id="msgbox"><table><tr><td><textarea id="closemark" rows="4" cols="30"></textarea><br />Do NOT alert issue for this LP in <input id="sleepday" type="text" size="3" value="0"> days.</td></tr><tr><td><input class="btn" type="button" value="确 定" onclick="close_msgbox(\''+issueid+'\', \''+process+'\');"><input class="btn1" type="button" value="取 消" onclick="close_msgbox();"></td></tr></table></div></div>';
	} else {
		ajaxChangeStatus(issueid, process);
		return;
	}
	
	if (str != '') {
		$("#messageLayer").html(str);
		show_msg();
	}
}

function ajaxChangeStatus(issueid, process) {
	var queryStr = '&issueid='+issueid+'&process='+process;
	
	if (process == 'resolve') {
		queryStr += '&resolvemark=' + encodeURIComponent(resolvemark);
		if (iswaitforcheck === true) queryStr += '&iswaitforcheck=1';
	} else if (process == 'close') {
		queryStr += '&closemark=' + encodeURIComponent(closemark) + '&sleepday=' + sleepday;
	}
	
	$.ajax({
    	type: "POST",
    	url: '/front/issue_landing_page_list.php',
    	data: "action=ajaxchangestatus"+queryStr,
    	dataType: 'json',
    	success: function(res){
    		if (res.exec == 'succ') {
    			window.location.reload();
    		} else {
    			alert(res.msg);
    			window.location.reload();
    		}
        }
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

function ajaxEdit(obj, act, id) {
	var tag = obj.firstChild.tagName;
	
	if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
	{
	    return;
	}
	
	var org = obj.innerHTML;
	var isIE = window.ActiveXObject ? true : false;
	var val = isIE ? obj.innerText : obj.textContent;

	var txt = document.createElement("INPUT");
	txt.value = (val == 'N/A') ? '' : val;
	txt.style.width = (obj.offsetWidth + 12) + "px" ;
	
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
	    if (trim(txt.value).length > 0)
	    {
	    	$.ajax({
	        	type: "POST",
	        	url: '/front/issue_landing_page_list.php',
	        	data: "action=" + act + "&val=" + encodeURIComponent(trim(txt.value)) + "&issueid="+id,
	        	dataType: 'json',
	        	success: function(res){
	        		obj.innerHTML = (res.exec == 'succ') ? res.content : org;
	            }
	    	});
	    }
	    else {
	      obj.innerHTML = org;
	    }
	}
}

