var maxBlockOrder = 0;
var BlockOrderArr = [];

var frontDomainUrl = {
	'csus': 'http://www.promopro.com/',
	'csca' : 'http://www.yessaving.ca/',
	'csuk' : 'http://www.promopro.co.uk/',
	'csde' : 'http://www.allecodes.de/',
	'csie' : 'http://www.irelandvouchercodes.com/',
	'csnz' : 'http://www.couponsnapshot.co.nz/',
	'csau' : 'http://www.ozdiscount.com/'
}


var tagErrMsg = "";
var tagErrMsg2 = "";
var expiredStr = "<span class='expiredstr' style='color:red;'>&nbsp;(Expired)</span>";
var candochange = false;
$(document).ready(function(){
	
	candochange = $("#candochange").val();
	
	$("#tabbar-div span").bind('mouseover',function(){
    	if ($(this).attr('class') == "tab-back") $(this).attr('class', 'tab-hover');
    })
    .bind('mouseout',function(){
    	if ($(this).attr('class') == "tab-hover") $(this).attr('class', 'tab-back');
    })
    .bind('click',function(){
    	switchTab(this);
    });
	
	$("#addblocktypebutton").bind('click',function(){
		addBlockType();
	});
	
	$(".clickcursor").live('click',function(){
		addTopicDetail(this);
	});
	
    $("#save").bind('click',function(){
    	if(candochange){
    		addSaveEvent2();
    	}else{
    		addSaveEvent();
    	}
    	
    });
    
    recalculateBlockOrder();
    
    $(".blockorder").live('click', function(){
    	ajaxEditBlockOrder(this);
    });
    
    $(".removeblock").live('click',function(){
    	removeBlock(this);
    });
    
    $(".remove").live('click',function(){
    	removeTopicDetail(this);
    });
    
    $(".edit").live('click',function(){
    	editTopicDetail(this);
    });
    
    $(".cancel").hide().live('click',function(){
    	cancleTopicDetailEdit(this);
    });
    
    $(".save").hide().live('click',function(){
    	saveEditTopicDetail(this);
    });
    
    $("#public").bind('click',function(){
    	publishTopic();
    });
    
    $("#view").bind('click',function(){
    	previewTopic();
    });
    
    $(".uploadbanner").live('click', function(){
    	ajaxFileUpload(this);
    });
    
    if(candochange){
    	
    	  $("#TagID").live('blur',function(){
    	    	var mtagid = $("#TagID").val();
    	    	if(mtagid == ""){
    	    		tagErrMsg = "";
    	    		return true;
    	    	}
    	    	var regu = "^[0-9]+$";
    	        var re = new RegExp(regu);
    	        if ( !re.test(mtagid) ) {
    	        	tagErrMsg = "Tag must be number!";
    	        	alert(tagErrMsg);
    	        }else{
    	        	var tagstr = 'tagid='+mtagid;
    	            $.ajax({
    	        		type: "POST",
    	        		url: '/editor/special_topic.php?act=ajaxIsSeasonal&site='+$('#site').val(),
    	        		data: tagstr,
    	        		dataType: 'json',
    	        		async: false,
    	        		success: function(res){
    	        			if(res == "0"){
    	        				tagErrMsg = "Tag must be Seasonal TagId! ";
    	        	        	alert(tagErrMsg);
    	        			}else{
    	        				tagErrMsg = "";
    	        			}
    	        	    }
    	        	});
    	        }
    	        
    	    	
    	    });
    	  $(".detail_couponid").live('blur',function(){
    		  var couponid = $(this).val();
    		  if(couponid == ""){
    			 tagErrMsg2 = "";
  	    		return true;
  	    	}
    		  var tagstr = 'couponid='+couponid;
    		  $.ajax({
	        		type: "POST",
	        		url: '/editor/special_topic.php?act=ajaxIsCouponExist&site='+$('#site').val(),
	        		data: tagstr,
	        		dataType: 'json',
	        		async: false,
	        		success: function(res){
	        			if(res == "1"){
	        				tagErrMsg2 = "CouponId didn't exist! ";
	        	        	alert(tagErrMsg2);
//	        	        	$(this).focus();
	        	        	
	        			}
	        			if(res == "2"){
	        				tagErrMsg2 = "CouponId had expired! ";
	        				alert(tagErrMsg2);
//	        	        	$(this).focus();
	        				
	        			}
	        	    }
	        	});
    		  
    	  });
    	  
    }
  
});

function switchTab(obj) {
	if ($(obj).attr('class') == "tab-front" || $(obj).attr('class') == '' || typeof $(obj).attr('class') == 'undefined' || $(obj).get(0).tagName.toLowerCase() != 'span')
	{
    	return;
    } else {
    	if ($(obj).attr('id') == 'base-tab') {
    		$("#detail-tab").attr('class', 'tab-back');
    		$("#base-tab").attr('class', 'tab-front');
    		$("#detail-table").attr('style', 'display:none;');
    		$("#base-table").attr('style', 'display:table;');
    		$("#detail-operation").attr('style', 'display:none;');
    		$("#base-operation").attr('style', 'display:;');
    	} else if ($(obj).attr('id') == 'detail-tab') {
    		if ($("#ID").val() == '') {
    			alert('Please save base information first!');
        	    return false;
    		}
    		
    		$("#base-tab").attr('class', 'tab-back');
    		$("#detail-tab").attr('class', 'tab-front');
    		$("#base-table").attr('style', 'display:none;');
    		$("#detail-table").attr('style', 'display:table;');
    		$("#base-operation").attr('style', 'display:none;');
    		$("#detail-operation").attr('style', 'display:;');
    	}
    }
}

function recalculateBlockOrder() {
	maxBlockOrder = 0;
	BlockOrderArr = [];
	
	$(".blockorder").each(function(){
    	BlockOrderArr.push(parseInt($(this).text()));
    	if (parseInt($(this).text()) > maxBlockOrder) maxBlockOrder = parseInt($(this).text());
    });
}

function removeBlock(obj) {
	var topicID = $("#ID").val();
	var blockOrder = $(".blockorder", $(obj).parent()).text();
	
	$.ajax({
		type: "POST",
		url: '/editor/special_topic.php',
		data: 'act=delblock&topicid=' + topicID + '&blockorder=' + blockOrder,
		dataType: 'json',
		success: function(res){
			if (res.exec == 'succ') {
				$(obj).parent().parent().parent().remove();
				recalculateBlockOrder();
			} else {
				alert('Operate failed!');
			}
	    }
	});
}

function publishTopic() {
	$.ajax({
		type: "POST",
		url: '/editor/special_topic.php',
		data: 'act=publish&topicid=' + $("#ID").val() + '&starttime=' + encodeURIComponent($("#StartTime").val()),
		dataType: 'json',
		success: function(res){
			if (res.exec == 'succ') {
				alert('Publish successfully!');
			} else {
				alert('Publish failed!');
			}
	    }
	});
}

function previewTopic() {
	var site = $("#site").val();
	var topicid = $("#ID").val();
	topicid = $.trim(topicid);
	if (topicid == '' || topicid == 0) {
		alert('Topic ID is invalid!');
		return false;
	}
	
	var url = frontDomainUrl[site] + 'front/specialtopic.php?stid=' + topicid + '&preview_st=on';
	window.open(url);
}

function addColor(obj) {
    originColor = $(obj).attr("bgColor");
    $(obj).attr("bgColor", "#FBF0E3");
}

function removeColor(obj) {
	$(obj).attr("bgColor", originColor);
}

function ajaxEditBlockOrder(obj) {
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
	
	txt.onblur = function(e)
	{
	    if ($.trim(txt.value).length > 0 && $.trim(txt.value) > 0 && $.trim(txt.value) != $.trim(val)) {
	    	for (var i in BlockOrderArr) {
	    		if (parseInt($.trim(txt.value)) == BlockOrderArr[i]) {
	    			alert('Block Order duplicated!');
	    			obj.innerHTML = org;
	    			return false;
	    		}
	    	}
	    	
	    	recalculateBlockOrder();
	    	
	    	$.ajax({
	        	type: "POST",
	        	url: '/editor/special_topic.php',
	        	data: "act=setblockorder&site=" + $("#site").val() + "&topicid=" + $("#ID").val() + "&blockordernew=" + encodeURIComponent($.trim(txt.value)) + "&blockorderold=" + $.trim(val),
	        	dataType: 'json',
	        	success: function(res){
	        		if (res.exec == 'succ') {
	        			obj.innerHTML = txt.value;
	        		} else {
	        			obj.innerHTML = org;
	        			alert("Please confirm to save the base information!");
	        		}
	        		
	            }
	    	});
	    } else {
	      obj.innerHTML = org;
	    }
	}
	
}

function addSaveEvent() {
//	alert(maxBlockOrder);
//	alert('-----');
//	for (var i in BlockOrderArr) {
//		alert(BlockOrderArr[i]);
//	}
	
	//return false;
	if ($.trim($("#Name").val()) == '') {
		alert("Please input Name!");
		return false;
	}
	
	if ($.trim($("#Title").val()) == '') {
		alert("Please input Title!");
		return false;
	}
	
	if(candochange){
		
		if(tagErrMsg != ""){
			alert(tagErrMsg);
			return false;
		}
		
		if ($.trim($("#StartTime").val()) == '') {
			alert("Please input StartTime!");
			return false;
		}
		
		if ($.trim($("#ExpireTime").val()) == '') {
			alert("Please input ExpireTime!");
			return false;
		}
		var startarr = $.trim($("#StartTime").val()).split("-");
		var expirearr = $.trim($("#ExpireTime").val()).split("-");
		var mydate = new Date();
		var myear = mydate.getFullYear();
		if(myear != startarr[0] || myear != expirearr[0] ){
			if(!confirm("The Start/Expire Date is not up to date, Continue or not?")){
				return false;
			}
		}
		
	}
	
	
	var saveFieldStr = 'act=save&site=' + $("#site").val() + '&ID=' + $("#ID").val();
	$("#form1 input").each(function(){
		if ($(this).attr('name') != '')
			saveFieldStr += '&' + $(this).attr('name') + '=' + encodeURIComponent($.trim($(this).val()));
	});
	$("#form1 textarea").each(function(){
		if ($(this).attr('name') != '') 
			saveFieldStr += '&' + $(this).attr('name') + '=' + encodeURIComponent($.trim($(this).val()));
	});
	
	$.ajax({
		type: "POST",
		url: '/editor/special_topic.php',
		data: saveFieldStr,
		dataType: 'json',
		async: false,
		success: function(res){
			if (res.exec == 'fail') {
				alert('Operate failed!');
			}else if (res.exec == 'fail2' && candochange) {
				alert('Operate failed: Tag must be Seasonal TagId! ');
			} else {
				$("#ID").val(res.topicid);
				alert('Operate successfully!');
			}
	    }
	});
}
function addSaveEvent2() {
//	alert(maxBlockOrder);
//	alert('-----');
//	for (var i in BlockOrderArr) {
//		alert(BlockOrderArr[i]);
//	}
	
	//return false;
	if ($.trim($("#Name").val()) == '') {
		alert("Please input Name!");
		return false;
	}
	
	if ($.trim($("#Title").val()) == '') {
		alert("Please input Title!");
		return false;
	}
	
	if(tagErrMsg != ""){
		alert(tagErrMsg);
		return false;
	}
	
	if ($.trim($("#StartTime").val()) == '') {
		alert("Please input StartTime!");
		return false;
	}
	
	if ($.trim($("#ExpireTime").val()) == '') {
		alert("Please input ExpireTime!");
		return false;
	}
	var startarr = $.trim($("#StartTime").val()).split("-");
	var expirearr = $.trim($("#ExpireTime").val()).split("-");
	var mydate = new Date();
	var myear = mydate.getFullYear();
	if(myear != startarr[0] || myear != expirearr[0] ){
		if(!confirm("The Start/Expire Date is not up to date, Continue or not?")){
			return false;
		}
	}
	
	var saveFieldStr = 'act=save&site=' + $("#site").val() + '&ID=' + $("#ID").val();
	$("#form1 input").each(function(){
		if ($(this).attr('name') != '')
			saveFieldStr += '&' + $(this).attr('name') + '=' + encodeURIComponent($.trim($(this).val()));
	});
	$("#form1 textarea").each(function(){
		if ($(this).attr('name') != '') 
			saveFieldStr += '&' + $(this).attr('name') + '=' + encodeURIComponent($.trim($(this).val()));
	});
	
	queryStr = saveFieldStr;
	
	$(".uploadbanner", $('#tdbackimg')).trigger('click');
}

function removeTopicDetail(obj) {
	var currentTrObj = $(obj).parent().parent(); 
	
	$.ajax({
		type: "POST",
		url: '/editor/special_topic.php',
		data: "act=deltopicdetail&site=" + $("#site").val() + "&topicdetailid=" + currentTrObj.attr('topicdetailid'),
		dataType: 'json',
		success: function(res){
			//if (res.exec == 'succ') currentTrObj.remove();
	    }
	});
	
	currentTrObj.remove();
}

function cancleTopicDetailEdit(obj) {
	var currentTrObj = $(obj).parent().parent();
	var blockType = currentTrObj.attr('blocktype');
	
	$(obj).hide();
	$(".save", $(obj).parent()).hide();
	$(".remove", $(obj).parent()).show();
	$(".edit", $(obj).parent()).show();
	if (blockType == 'BANNER') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html($("input[attrname='Title1']", $(this)).val());
//				if ($(this).attr('field') == 'ImgUrl') $(this).html('');
				if ($(this).attr('field') == 'ImgUrl'){
					var insertimgstr = $(".hideurl", $(this).parent()).val();
					if(insertimgstr != ""){
						insertimgstr = "<a target='_blank' href='"+ insertimgstr +"'>view image</a>"
					}
					$(this).html(insertimgstr);
				}
				if ($(this).attr('field') == 'Description') $(this).html($("textarea[attrname='Description']", $(this)).val());
				if ($(this).attr('field') == 'Order') $(this).html($("input[attrname='Order']", $(this)).val());
			}
		});
	} else if (blockType == 'COUPONROTATE') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html($("input[attrname='Title1']", $(this)).val());
				if ($(this).attr('field') == 'Title2') $(this).html($("input[attrname='Title2']", $(this)).val());
				if ($(this).attr('field') == 'MerchantName') $(this).html($("input[attrname='MerchantName']", $(this)).val());
				if ($(this).attr('field') == 'CouponID') $(this).html($("input[attrname='CouponID']", $(this)).val());
				if ($(this).attr('field') == 'Order') $(this).html($("input[attrname='Order']", $(this)).val());
			}
		});
	} else if (blockType == 'STORE') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html($("input[attrname='Title1']", $(this)).val());
				if ($(this).attr('field') == 'Title2') $(this).html($("input[attrname='Title2']", $(this)).val());
				if ($(this).attr('field') == 'MerchantName') $(this).html($("input[attrname='MerchantName']", $(this)).val());
				if ($(this).attr('field') == 'Description') {
					var currentTextareaId = $("textarea[attrname='" + $(this).attr('field') + "']", $(this)).attr('id');
					$(this).html(CKEDITOR.instances[currentTextareaId].getData());
				}
				if ($(this).attr('field') == 'Order') $(this).html($("input[attrname='Order']", $(this)).val());
			}
		});
	} else if (blockType == 'COUPONLIST') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html($("input[attrname='Title1']", $(this)).val());
				if ($(this).attr('field') == 'Title2') $(this).html($("input[attrname='Title2']", $(this)).val());
				if ($(this).attr('field') == 'TagID') $(this).html($("input[attrname='TagID']", $(this)).val());
				if ($(this).attr('field') == 'Count') $(this).html($("input[attrname='Count']", $(this)).val());
				if ($(this).attr('field') == 'Order') $(this).html($("input[attrname='Order']", $(this)).val());
			}
		});
	} else if (blockType == 'DEALGRID') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html($("input[attrname='Title1']", $(this)).val());
				if ($(this).attr('field') == 'Title2') $(this).html($("input[attrname='Title2']", $(this)).val());
				if ($(this).attr('field') == 'TagID') $(this).html($("input[attrname='TagID']", $(this)).val());
				if ($(this).attr('field') == 'Count') $(this).html($("input[attrname='Count']", $(this)).val());
				if ($(this).attr('field') == 'Order') $(this).html($("input[attrname='Order']", $(this)).val());
			}
		});
	} else if (blockType == 'COUPONLIST_FEATUREDCOUPON') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'CouponID'){
					if(candochange){
						var insertcouponid= $(".hidecouponid", $(this).parent()).val();
						if(typeof(insertcouponid) == 'undefined'){
							insertcouponid = "";
						}
						
						var hideexpiredstr = $('.hideexpiredstr',currentTrObj).val();
						if(typeof(hideexpiredstr) == 'undefined'){
							hideexpiredstr = "";
						}
						$(this).html(insertcouponid + hideexpiredstr);
					}else{
						$(this).html($("input[attrname='CouponID']", $(this)).val());
					}
				}
				
				if ($(this).attr('field') == 'Order'){
					if(candochange){
						var insertorder= $(".hideorder", $(this).parent()).val();
						if(typeof(insertorder) == 'undefined'){
							insertorder = "";
						}
						$(this).html(insertorder);
					}else{
						$(this).html($("input[attrname='Order']", $(this)).val());
					}
					
				}
				
				
				if(candochange){
					if ($(this).attr('field') == 'ImgUrl'){
						var insertimgstr = $(".hideurl", $(this).parent()).val();
						if(insertimgstr != ""){
							insertimgstr = "<a target='_blank' href='"+ insertimgstr +"'>view image</a>"
						}
						$(this).html(insertimgstr);
					}
					
					
					if ($(this).attr('field') == 'Category'){
						var cateoldval = $('.hidecateid',currentTrObj).val();
						//change select to input by devin 201410221322 from owen start
//						var cateidval = $(".categories option[value='" + cateoldval + "']", $(this)).text();
						var cateidval = cateoldval;
						//change select to input by devin 201410221322 from owen  end
						if(cateidval == "NONE"){
							cateidval = "";
						}
						$(this).html(cateidval);
					}
				}
				
			}
		});
	}
}

function editTopicDetail(obj) {
	var currentTrObj = $(obj).parent().parent();
	var blockType = currentTrObj.attr('blocktype');
	
	$(obj).hide();
	$(".remove", $(obj).parent()).hide();
	$(".save", $(obj).parent()).show();
	$(".cancel", $(obj).parent()).show();
	if (blockType == 'BANNER') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html('<input attrname="Title1" type="text" size="30" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'ImgUrl') {
					$(this).html('<input attrname="ImgUrl" id="ImgUrl_' + new Date().getTime() + '" name="ImgUrl_' + new Date().getTime() + '" type="file"><input type="button" class="uploadbanner" style="display:none;">');
				}
				if ($(this).attr('field') == 'Description') $(this).html('<textarea attrname="Description" cols="40" rows="3">' + $(this).html() + '</textarea>');
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
		});
	} else if (blockType == 'COUPONROTATE') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html('<input attrname="Title1" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Title2') $(this).html('<input attrname="Title2" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'MerchantName') {
					$(this).html('<input attrid="merchantid" id="' + new Date().getTime() + '" type="hidden" size="10" value="' + $(this).parent().attr('merchantid') + '"><input attrname="MerchantName" id="search' + new Date().getTime() + '" type="text" size="25" value="' + $(this).html() + '">');
					iniMerchantSearch(this);
				}
				if ($(this).attr('field') == 'CouponID') $(this).html('<input attrname="CouponID" type="text" size="10" value="' + $(this).html() + '">&nbsp;<button onclick="checkCouponidBelonged(this);">Open</button>');
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
		});
	} else if (blockType == 'STORE') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html('<input attrname="Title1" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Title2') $(this).html('<input attrname="Title2" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'MerchantName') {
					$(this).html('<input attrid="merchantid" id="' + new Date().getTime() + '" type="hidden" size="10" value="' + $(this).parent().attr('merchantid') + '"><input attrname="MerchantName" id="search' + new Date().getTime() + '" type="text" size="25" value="' + $(this).html() + '">');
					iniMerchantSearch(this);
				}
				if ($(this).attr('field') == 'Description') {
					$(this).html('<textarea attrname="Description" id="ckedescription' + new Date().getTime() + '" cols="40" rows="3">' + $(this).html() + '</textarea>');
					iniCkeditor(this);
				}
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
		});
	} else if (blockType == 'COUPONLIST') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html('<input attrname="Title1" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Title2') $(this).html('<input attrname="Title2" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'TagID') $(this).html('<input attrname="TagID" type="text" size="10" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Count') $(this).html('<input attrname="Count" type="text" size="10" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
		});
	} else if (blockType == 'DEALGRID') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'Title1') $(this).html('<input attrname="Title1" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Title2') $(this).html('<input attrname="Title2" type="text" size="15" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'TagID') $(this).html('<input attrname="TagID" type="text" size="10" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Count') $(this).html('<input attrname="Count" type="text" size="10" value="' + $(this).html() + '">');
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
		});
	} else if (blockType == 'COUPONLIST_FEATUREDCOUPON') {
		$("td", currentTrObj).each(function(){
			if (typeof $(this).attr('field') != 'undefined') {
				if ($(this).attr('field') == 'CouponID'){
					if(candochange){
						$(".expiredstr",this).remove();
					}
					
					$(this).html('<input class="detail_couponid" attrname="CouponID" type="text" size="10" value="' + $(this).html() + '">&nbsp;<button onclick="checkCouponidBelonged(this);">Open</button>');
				}
				
				if ($(this).attr('field') == 'Order') $(this).html('<input attrname="Order" type="text" size="5" value="' + $(this).html() + '">');
			}
			
			if(candochange){
				if ($(this).attr('field') == 'ImgUrl') {
					$(this).html('<input attrname="ImgUrl" id="ImgUrl_' + new Date().getTime() + '" name="ImgUrl_' + new Date().getTime() + '" type="file"><input type="button" class="uploadbanner" style="display:none;">');
				}
				
				if ($(this).attr('field') == 'Category'){
					//change select to input by devin from owwen xu 20141022
					var oldval = $(".hidecateid", currentTrObj).val();
					$(this).html($("#categorytree").html());
					//$("select option[value='"+ oldval +"']", $(this)).attr('selected',true);
					$("input",$(this)).val(oldval);
					//change select to input by devin from owwen xu 20141022 end
				}
				
			}
			
			
		});
	}
}

var queryStr = '';
function saveEditTopicDetail(obj) {
	var currentTrObj = $(obj).parent().parent();
	var blockType = currentTrObj.attr('blocktype');
	var topicdetailid = currentTrObj.attr('topicdetailid');
	var blockorder = $(".blockorder", currentTrObj.parent().parent().parent()).text();
	queryStr = 'site=' + $("#site").val() + '&topicid=' + $("#ID").val() + '&blocktype=' + blockType + "&blockorder=" + blockorder;
	if (topicdetailid > 0) queryStr += '&act=updatedetail' + '&topicdetailid=' + topicdetailid;
	else queryStr += '&act=insertdetail' + '&topicdetailid=';
	
	$("td", currentTrObj).each(function(){
		if (typeof $(this).attr('field') != 'undefined') {
			if ($(this).attr('field') == 'ImgUrl') {
				queryStr +='&' + $(this).attr('field') + '=' + encodeURIComponent($("input[attrname='" + $(this).attr('field') + "']", $(this)).attr('name'));
			} else if ($(this).attr('field') == 'Description') {
				if ($(this).parent().attr('blocktype') == 'STORE') {
					var currentTextareaId = $("textarea[attrname='" + $(this).attr('field') + "']", $(this)).attr('id');
					queryStr +='&' + $(this).attr('field') + '=' + encodeURIComponent(CKEDITOR.instances[currentTextareaId].getData());
				} else {
					queryStr +='&' + $(this).attr('field') + '=' + encodeURIComponent($("textarea[attrname='" + $(this).attr('field') + "']", $(this)).val());
				}
			} else if ($(this).attr('field') == 'MerchantName') {
				if ($.trim($("input[attrname='MerchantName']", $(this)).val()) == '') {
					$("input[attrid='merchantid']", $(this)).val('');
					queryStr +='&MerchantID=';
				}
				else queryStr +='&MerchantID=' + encodeURIComponent($("input[attrid='merchantid']", $(this)).val());
			} else if($(this).attr('field') == 'Category' && candochange){
				queryStr +='&' + $(this).attr('field') + '=' + encodeURIComponent($(".categories", $(this)).val());
				$(".hidecateid", currentTrObj).val($(".categories", $(this)).val());
			} else{
				queryStr +='&' + $(this).attr('field') + '=' + encodeURIComponent($("input[attrname='" + $(this).attr('field') + "']", $(this)).val());
				if(candochange){
					if( $(this).attr('field') == 'CouponID'){
						$(".hidecouponid", currentTrObj).val($(".detail_couponid", $(this)).val());
					}
					if( $(this).attr('field') == 'Order'){
						$(".hideorder", currentTrObj).val($(".detail_order", $(this)).val());
					}
					
				}
			}
		}
	});
	
	//alert(queryStr);
	//return false;
	
	if (blockType == 'BANNER' || (blockType == 'COUPONLIST_FEATUREDCOUPON' && candochange )) {
		$(".uploadbanner", currentTrObj).trigger('click');
	} else {
		$.ajax({
			type: "POST",
			url: '/editor/special_topic.php',
			data: queryStr,
			dataType: 'json',
			success: function(res){
				if (res.exec == 'succ') {
					$(".save", $(obj).parent()).hide();
					$(".edit", $(obj).parent()).show();
					$(".cancel", $(obj).parent()).trigger('click');
					
					if (res.topicdetailid) $(obj).parent().parent().attr('topicdetailid', res.topicdetailid);
				}
		    }
		});
	}
}

function addBlockType() {
	var blockType = $("#addblocktype").val();
	if (blockType == '') {
		alert('Please select block type!');
		return false;
	}
	
	var appendBlockHtml = '<tr blocktype="' + blockType + '">';
	
	if (blockType == 'BANNER') {
		appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+ Banner</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
		appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Image Title</th><th style="font-size:12px;width:200px;">Image Url</th><th style="font-size:12px;width:600px;">Image Description</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table></td>';
	} else if (blockType == 'COUPONROTATE') {
		appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+ Coupon Rotate</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
		appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Title1</th><th style="font-size:12px;width:200px;">Title2</th><th style="font-size:12px;width:230px;">Merchant name</th><th style="font-size:12px;width:250px;">Coupon id</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table></td>';
	} else if (blockType == 'STORE') {
		appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+ Store</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
		appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Title1</th><th style="font-size:12px;width:200px;">Title2</th><th style="font-size:12px;width:230px;">Merchant name</th><th style="font-size:12px;width:250px;">Description</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table></td>';
	} else if (blockType == 'COUPONLIST') {
		if(candochange){
			appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+ Coupon List</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
			appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Title1</th><th style="font-size:12px;width:200px;">Title2</th><th style="font-size:12px;width:230px;">Tag</th><th style="font-size:12px;width:100px;">Count</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table><table cellspacing="1" cellpadding="4" align="center" style="border-collapse:collapse;border:1px solid #888888;margin-top:5px;"><tr bgcolor="#CBFA8E"><td colspan=4 style="font-size: 14px;font-weight: bold;" align="center">This Coupon List Featured Coupon </td><td align="center"><button onclick="addTopicDetail(this,\'COUPONLIST_FEATUREDCOUPON\')">Add</button></td></tr><tr><th width="250px">Coupon id</th><th width="100px">Order</th><th width="100px">Image</th><th width="100px">Category</th><th>Action</th></tr></table></td>';
		}else{
			appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+ Coupon List</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
			appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Title1</th><th style="font-size:12px;width:200px;">Title2</th><th style="font-size:12px;width:230px;">Tag</th><th style="font-size:12px;width:100px;">Count</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table><table cellspacing="1" cellpadding="4" align="center" style="border-collapse:collapse;border:1px solid #888888;margin-top:5px;"><tr bgcolor="#CBFA8E"><td colspan=2 style="font-size: 14px;font-weight: bold;" align="center">This Coupon List Featured Coupon </td><td align="center"><button onclick="addTopicDetail(this,\'COUPONLIST_FEATUREDCOUPON\')">Add</button></td></tr><tr><th width="250px">Coupon id</th><th width="100px">Order</th><th>Action</th></tr></table></td>';
		}
	} else if (blockType == 'DEALGRID') {
		appendBlockHtml += '<td colspan="2"><p class="addblock"><span class="clickcursor">+Deal Grid</span><span class="removeblock"> X </span><span class="blockorder">' + (parseInt(maxBlockOrder) + 1) + '</span></p>';
		appendBlockHtml += '<table align="center" cellpadding="4" cellspacing="1"><tbody><tr><th style="font-size:12px;width:200px;">Title1</th><th style="font-size:12px;width:200px;">Title2</th><th style="font-size:12px;width:230px;">Tag</th><th style="font-size:12px;width:100px;">Count</th><th style="font-size:12px;width:100px;">Order</th><th>Action</th></tr></tbody></table></td>';
	}
	appendBlockHtml += '</tr>';
	
	$("#detail-table").append(appendBlockHtml);
	recalculateBlockOrder();
}

function addTopicDetail(obj) {
	if(arguments[1] != undefined){
		var parentTrObj = $(obj).parent().parent().parent().parent().parent();
        var blockType = arguments[1];
	} else {
		var parentTrObj = $(obj).parent().parent().parent();
		var blockType = parentTrObj.attr('blocktype');
	}

	if(blockType == 'COUPONLIST' && typeof(parentTrObj.find("tr[blocktype='COUPONLIST']").attr('topicdetailid')) != 'undefined'){
		alert('Just one tag could be added for coupon list.');
		return false;
	}
	
	var topicDetailHtml = '<tr blocktype="' + blockType + '" topicdetailid="">';
	
	if (blockType != 'COUPONLIST_FEATUREDCOUPON') {
		topicDetailHtml += '<td align="center" field="Title1"><input attrname="Title1" size="30" value="" type="text"></td>';
	}
	if (blockType == 'BANNER') {
		topicDetailHtml += '<td align="center" field="ImgUrl"><input attrname="ImgUrl" id="ImgUrl_' + new Date().getTime() + '" name="ImgUrl_' + new Date().getTime() + '" type="file"><input type="button" class="uploadbanner" style="display:none;"></td>';
		topicDetailHtml += '<td align="center" field="Description"><textarea attrname="Description" cols="40" rows="3"></textarea></td>';
	} else if (blockType == 'COUPONROTATE') {
		topicDetailHtml += '<td align="center" field="Title2"><input attrname="Title2" size="15" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="MerchantName"><input attrid="merchantid" id="' + new Date().getTime() + '" size="10" value="" type="hidden"><input attrname="MerchantName" id="search' + new Date().getTime() + '" size="25" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="CouponID"><input attrname="CouponID" size="10" value="" type="text">&nbsp;<button onclick="checkCouponidBelonged(this);">Open</button></td>';
	} else if (blockType == 'STORE') {
		if ($("table tr", parentTrObj).size() > 1 && typeof $("table tr:last", parentTrObj).attr('topicdetailid') == 'undefined') {
			alert('Save the current row and the add!');
			return false;
		}
		topicDetailHtml += '<td align="center" field="Title2"><input attrname="Title2" size="15" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="MerchantName"><input attrid="merchantid" id="' + new Date().getTime() + '" size="10" value="" type="hidden"><input attrname="MerchantName" id="search' + new Date().getTime() + '" size="25" value="" type="text"></td>';
		topicDetailHtml += '<td align="left" field="Description"><textarea attrname="Description" id="ckedescription' + new Date().getTime() + '" cols="40" rows="3"></textarea></td>';
	} else if (blockType == 'COUPONLIST') {
		topicDetailHtml += '<td align="center" field="Title2"><input attrname="Title2" size="15" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="TagID"><input attrname="TagID" size="10" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="Count"><input attrname="Count" size="10" value="" type="text"></td>';
	} else if (blockType == 'DEALGRID') {
		topicDetailHtml += '<td align="center" field="Title2"><input attrname="Title2" size="15" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="TagID"><input attrname="TagID" size="10" value="" type="text"></td>';
		topicDetailHtml += '<td align="center" field="Count"><input attrname="Count" size="10" value="" type="text"></td>';
	} else if (blockType == 'COUPONLIST_FEATUREDCOUPON') {
		topicDetailHtml += '<td align="center" field="CouponID"><input class="detail_couponid" attrname="CouponID" size="10" value="" type="text">&nbsp;<button onclick="checkCouponidBelonged(this);">Open</button></td>';
	}
	
	topicDetailHtml += '<td align="center" field="Order"><input attrname="Order" class="detail_order" size="5" value="" type="text"></td>';
	if (blockType == 'COUPONLIST_FEATUREDCOUPON' && candochange) {
		var selectstr = $("#categorytree").html();
		topicDetailHtml += '<td align="center" field="ImgUrl"><input id="ImgUrl_' + new Date().getTime() + '" name="ImgUrl_' + new Date().getTime() + '" attrname="ImgUrl" size="10" type="file"><input type="button" class="uploadbanner" style="display:none;"></td>';
		topicDetailHtml += '<td align="center" field="Category">' + selectstr + '</td>';
	}
	
	topicDetailHtml += '<td align="center"><button style="display: none;" class="edit">Edit</button>&nbsp;<button style="display: inline-block;" class="save">Save</button>&nbsp;<button class="remove">Remove</button>&nbsp;<button style="display: none;" class="cancel">Cancel</button>';
	if (blockType == 'COUPONLIST_FEATUREDCOUPON' && candochange) {
		topicDetailHtml += '<input class="hideurl" type="hidden" value="">';
		topicDetailHtml += '<input class="hidecateid" type="hidden" value="">';
		topicDetailHtml += '<input class="hideexpiredstr" type="hidden" value="">';
		topicDetailHtml += '<input class="hidecouponid" type="hidden" value="">';
		topicDetailHtml += '<input class="hideorder" type="hidden" value="">';
	}else if (blockType == 'BANNER') {
		topicDetailHtml += '<input class="hideurl" type="hidden" value="">';
	}
	topicDetailHtml += '</td>';
	topicDetailHtml += '</tr>'

	if (blockType == 'COUPONLIST_FEATUREDCOUPON') {
		$("table:last", parentTrObj).append(topicDetailHtml);
	} else{
		$("table:first", parentTrObj).append(topicDetailHtml);
	}
	if (blockType == 'COUPONROTATE') {
		iniMerchantSearch($("table tr:last", parentTrObj));
	} else if (blockType == 'STORE') {
		iniMerchantSearch($("table tr:last", parentTrObj));
		iniCkeditor($("table tr:last", parentTrObj));
	}
}

function checkCouponidBelonged(obj){
	var site = $("#site").val();
	var couponid = $("input[attrname='CouponID']", $(obj).parent()).val();
	couponid = $.trim(couponid);
	if (couponid == '' || couponid == 0) {
		alert('Coupon ID is empty!');
		return false;
	}
	
	var url = frontDomainUrl[site] + 'front/coupondetail.php?couponid=' + couponid;
	window.open(url);
	
}

function iniMerchantSearch(obj) {
	var merIdentify = $("input[attrid='merchantid']", $(obj)).attr('id');
	var merSearchIdentify = $("input[attrname='MerchantName']", $(obj)).attr('id');
	
	$("#" + merSearchIdentify).unautocomplete();
	var site = $("#site").val();
	
	$("#" + merSearchIdentify).autocomplete("/editor/special_topic.php?act=ajaxmersearch&site="+site, {
		width: 200,
		matchSubset:false,
		selectFirst: false
	});
	
	$("#" + merSearchIdentify).result(function(event, data, formatted) {
		if (data) {
			$("#" + merSearchIdentify).attr("value", data[1]);
			$("#" + merIdentify).attr("value", data[2]);
		}
	});
}

function iniCkeditor(obj) {
	var ckeDescriptionIdentify = $("textarea[attrname='Description']", $(obj)).attr('id');
	
	CKEDITOR.replace(ckeDescriptionIdentify,
			{
				language : 'en',
				width : '350px',
				height: '100px',
				toolbar :
				[
					['Source'],['BulletedList'],['Link'],['FontSize'],['TextColor'],['Bold']
				]
			});
}

function ajaxFileUpload(obj) {
	var currentTdObj = $(obj).parent();
	var currentFileElementId = $("input[attrname='ImgUrl']", currentTdObj).attr('id');
//	alert(currentFileElementId);
	$.ajaxFileUpload
	(
		{
			url : '/editor/special_topic.php?' + queryStr,
			secureuri : false,
			fileElementId : currentFileElementId,
			dataType: 'json',
			async: false,
			success: function (data, status){
				if(candochange){
					
					if(currentFileElementId == 'BackImg'){
//						alert(data.topicid);
						if (data.exec == 'fail') {
							alert('Operate failed!');
						}else if (data.exec == 'fail2') {
							alert('Operate failed: Tag must be Seasonal TagId! ');
						} else {
							$("#ID").val(data.topicid);
							alert('Operate successfully!');
							location.reload();
						}
					}else{
						if(data.exec == 'fail_c' ){
							alert(data.msg);
							return;
						}
						if (data.topicdetailid) $(obj).parent().parent().attr('topicdetailid', data.topicdetailid);
						$(".save", $(obj).parent().parent()).hide();
						$(".edit", $(obj).parent().parent()).show();
//						alert(data.ImgUrl);
						if(data.ImgUrl != ""){
							$(".hideurl", $(obj).parent().parent()).val(data.ImgUrl);
						}
						
						if(data.Order != ""){
							$(".hideorder", $(obj).parent().parent()).val(data.Order);
						}
						
						if(data.ExpiredStr != "" && typeof(data.ExpiredStr) !="undefined"){
							$(".hideexpiredstr", $(obj).parent().parent()).val(expiredStr);
						}else{
							$(".hideexpiredstr", $(obj).parent().parent()).val("");
						}
						
						$(".cancel", $(obj).parent().parent()).trigger('click');
						
					}
				}else{
					if (data.topicdetailid) $(obj).parent().parent().attr('topicdetailid', data.topicdetailid);
					
					$(".save", $(obj).parent().parent()).hide();
					$(".edit", $(obj).parent().parent()).show();
					if(data.ImgUrl != ""){
						$(".hideurl", $(obj).parent().parent()).val(data.ImgUrl);
					}
					$(".cancel", $(obj).parent().parent()).trigger('click');
				}
				
			},
			error: function (data, status, e){}
		}
	)
	
	return false;
}

