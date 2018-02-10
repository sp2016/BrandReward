
$().ready(function() {
	function log(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
		return row[1] + "(" + row[0] + ")" + " "+(row[2]?row[2]:"");
	}
	function formatResult(row) {
		return row[1];
	}
	
	//merchant
	$("#merchant_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=merchantName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: addfun
	});
	
	
	
	/*$("#merchant_search").bind("change", function(){
		
	});*/
	
	/*$("#merchant_search").blur(function(){
		$("#merchant").val($("#merchant_search").val());		
	});*/
	
	/*$("#merchant").blur(function(){	
		var verifyArr  = {'ajaxTag':'getMerchantTips'};
		$.ajax({type: "POST",
			url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantTips&q="+$("#merchant").val(),
			data: $.param(verifyArr),
			success: function(msg){
				$("#merchant_tips").html(msg);
			}					   
		});							   
	});*/
	
		//alert("111");	
	
	var temp_mer_text=$("#merchant_search").val();

	function addfun(){	
		if(temp_mer_text!=$("#merchant_search").val()){			
			temp_mer_text=$("#merchant_search").val();
			var verifyArr  = {'ajaxTag':'merchantName'};		
			$.ajax({type: "POST",
				url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=bundlecoupon&q="+encodeURIComponent($("#merchant_search").val()),
				data: $.param(verifyArr),
				success: function(msg){
					$("#coupon_bundle").html(msg);
					if(msg){
						$("#bundle_merchant_div").css("display","");
						$("#couponbundle").html("");
					}else{
						$("#bundle_merchant_div").css("display","none");
					}
				}
			});	
			$.ajax({type: "POST",
				url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantIdByName&q="+encodeURIComponent($("#merchant_search").val()),
				data: $.param(verifyArr),
				success: function(msg){
					$("#merchant").val(msg);	
					BlackKeyWords();
					$("#merchant_name_auto").html("Selected Merchant: "+temp_mer_text+"("+msg+")");
					$.ajax({type: "POST",
						url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantDeepUrl&q="+escape(msg),
						data: $.param(verifyArr),
						success: function(url){
							if(url != ''){
								$("#deepurl").html(url);
								//$("#deepurl_div").show();
								$(".non_deep").hide();
								$(".is_deep").show();
							}else{
								$("#deepurl").html("");
								//$("#deepurl_div").hide();
								$(".is_deep").hide();
								$(".non_deep").show();
							}
							try {
								if (typeof(eval("checkLindShare")) == "function") {
									checkLindShare();
								}
							} catch (e) {
								
							}
							$.ajax({type: "POST",
								url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getcanrecommendcoupon&q="+escape($("#merchant").val()),
								data: $.param(verifyArr),
								success: function(msg){
									
									if( msg == "YES"){
										$(".c_canrecommendcoupon").show();
									}else{
										$(".c_canrecommendcoupon").hide();
									}
								}					   
							});	
						}	
						
					});
					reloadtag();
					/*if (typeof(eval("loadAffiliate")) == "function") {
						
						loadAffiliate();
					}*/
					checkMerchantAff();
				}					   
										   
			});
			$("#merchant_tips").html("<img src='/image/loading.gif' />");
			$.ajax({type: "POST",
				url: "/editor/search.php?ajaxTag=getMerchantTips&site="+escape($("#site").val())+"&q="+encodeURIComponent($("#merchant_search").val()),
				data: $.param(verifyArr),
				success: function(msg){
					$("#c_dst_url_hidden").val("");
					$("#merchant_tips").html(msg);
				}					   
			});	
		}
	}
	
	$("#merchant_list_search").autocomplete('/editor/search.php?site='+escape($("#site").val())+'&ajaxTag=mmcMerName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: sel_mer
	});
	function sel_mer(){
		$.ajax({type: "POST",
			url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantIdByName&q="+encodeURIComponent($("#merchant_list_search").val()),			
			success: function(msg){
				$("#merchant").val(msg);
				$("#merchant_id_span").html("MID:" + msg);
				//$("form:first").submit();
			}					   
		});	
		$.ajax({type: "POST",
			url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getGrade&q="+encodeURIComponent($("#merchant_list_search").val()),			
			success: function(msg){
				$("#merchantGrade").text("Grade:"+msg);
			}					   
		});	
	}
	
	//$("#merchant_search").change(function(){addfun();});

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
});


function merchant(figure){
	$("#merchant_list_search_"+figure).autocomplete('/editor/search.php?site='+escape($("#Site_"+figure).attr("value"))+'&ajaxTag=mmcMerName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true
	}).result(function(event, row, formatted) {
        $("#merchant_"+figure).val(row[0]);
		getmerchantinfo(figure);
    });
}
function formatItem(row) {
	return row[1] + "(" + row[0] + ")" + " "+(row[2]?row[2]:"");
}
function formatResult(row) {
	return row[1];
}
function getmerchantinfo(figure){
	var merchant = $("#merchant_"+figure).attr("value");
	if(!merchant){
		alert("Please select a merchant first.");
		return false;
	}
	var groupID = $("#GroupID").attr("value");
	var type = $("#type").attr("value");
	var site = $("#Site_"+figure).attr("value");

	jQuery.ajax({
		type : "get",
		url : 'SiteMerchantGroup.php',
		dataType : 'json',
		data : 'action=Merchant&mid=' + merchant +'&GroupID='+groupID+'&type='+type+'&site='+site,
		success : function(msg) {
			if(!$("#groupName_"+figure).attr('value')){
				$("#groupName_"+figure).attr('value',msg.groupname);
			}
			$("#name_"+figure).attr("value",msg.Name);
			$("#url_"+figure).attr("value",msg.OriginalUrl);
		}
	});
}