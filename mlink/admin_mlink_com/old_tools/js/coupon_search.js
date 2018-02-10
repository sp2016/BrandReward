
$().ready(function() {
	function log(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
//		rowStr = rowStr.toString();
//		var row  =  rowStr.split("|");
		return row[1] + "(" + row[0] + ")" + " "+row[2];
	}
	function formatResult(row) {
//		rowStr = rowStr.toString();
//		var row  =  rowStr.split("|");
		return row[1];
	}
	var site = $("#site").val();
	
	//merchant
	$("#merchant_search").autocomplete('/editor/coupon_search.php?ajaxTag=merchantName&sitename=' + site, {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: addfun
	});
	
	var temp_mer_text=$("#merchant_search").val();

	function addfun(){	
		var site = $("#site").val();
		if(temp_mer_text!=$("#merchant_search").val()){			
			temp_mer_text=$("#merchant_search").val();
			var verifyArr  = {'ajaxTag':'merchantName'};		
			$.ajax({type: "POST",
				url: "/editor/coupon_search.php?ajaxTag=bundlecoupon&q="+encodeURIComponent($("#merchant_search").val()) + "&sitename=" + site,
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
				url: "/editor/coupon_search.php?ajaxTag=getMerchantIdByName&q="+encodeURIComponent($("#merchant_search").val()) + "&sitename=" + site,
				data: $.param(verifyArr),
				success: function(msg){
					$("#merchant").val(msg);	
					BlackKeyWords();
					$("#merchant_name_auto").html("Selected Merchant: "+temp_mer_text+"("+msg+")");
					$.ajax({type: "POST",
						url: "/editor/coupon_search.php?ajaxTag=getMerchantDeepUrl&q="+escape(msg)  + "&sitename=" + site,
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
								url: "/editor/coupon_search.php?ajaxTag=getcanrecommendcoupon&q="+escape($("#merchant").val())  + "&sitename=" + site,
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
				}					   
										   
			});
			$("#merchant_tips").html("<img src='/image/loading.gif' />");
			var site = $("#site").val();
			$.ajax({type: "POST",
				url: "/editor/coupon_search.php?ajaxTag=getMerchantTips&q="+encodeURIComponent($("#merchant_search").val())  + "&sitename=" + site,
				data: $.param(verifyArr),
				success: function(msg){
				 	$("#c_dst_url_hidden").val("");
					$("#merchant_tips").html(msg);
					if (typeof(eval("checkMerchantAff")) == "function") {
						checkMerchantAff();
					}
				}					   
			});	
		
		}
	}
	
	$("#merchant_list_search").autocomplete('/editor/coupon_search.php?ajaxTag=merchantName'  + "&sitename=" + site, {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: sel_mer
	});
	
	function sel_mer(){
		$.ajax({type: "POST",
			url: "/editor/coupon_search.php?ajaxTag=getMerchantIdByName&q="+escape($("#merchant_list_search").val())  + "&sitename=" + site,			
			success: function(msg){				
				$("#merchant").val(msg);
				$("form:first").submit();
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

