var backend_url = "http://csusbackend.megainformationtech.com";
var task_url = "http://task.megainformationtech.com";
//var task_url = "http://tom.task.mega.com/";//TODO DELETE
var url = window.location.href;
var regex = /^http\:\/\/www.(.*)\/(.*)/;
var match = url.match(regex);
var id, type;
var page_uri = "";
if(typeof match != "undefined" && null != match){	
	backend_url = "http://backend."+match[1];
	page_uri = "/" + match[2];
}

var site = "undefined";
var url = window.location.href;
var regex = /^http\:\/\/www.([a-zA-Z0-9]+)\.(.*)\/(.*)/;
var match = url.match(regex);

if(typeof match != "undefined" && null != match){
	if(match[1] == "couponsnapshot"){
		if(match[2] == "com"){
			site = "csus";
		}else if(match[2] == "ca"){
			site = "csca";
		}else if(match[2] == "de"){
			site = "csde";
		}else if(match[2] == "com.au"){
			site = "csau";
		}else if(match[2] == "co.uk"){
			site = "csuk";
		}else if(match[2] == "co.nz"){
			site = "csnz";
		}
	}
	else if(match[1] == "irelandvouchercodes"){
		site = "csie";
	}
	else if(match[1] == "yessaving"){
		site = "csca";
	}
	else if(match[1] == "promopro"){
		if(match[2] == "com"){
			site = "csus";			
		}else if(match[2] == "co.uk"){
			site = "csuk";
		}else if(match[2] == "com/ca"){
			site = "csca";			
		}
	}
	else if(match[1] == "ozdiscount"){
		site = "csau";
	}
	else if(match[1] == "anycodes" || match[1] == "promopro"){
		site = "csus";
	}
	else if(match[1] == "allecodes"){
		site = "csde";
	}
}
function setMktStatus(mid){
	var remark = $("#mkt_remark").val();
	$.getJSON(task_url+"/front/greasemonkey.php?callback=?", {'ajaxTag':'setmktstatus','mid':mid,'site':site, 'remark': remark}, function(data){			
		if(data.res == "success"){
			alert("successed");
			$("#mktstatus").text("Done");
			window.location.reload(); 
		}else{
			alert("error");
		}
	});
//	$.ajax({           
//        async: false,
//        url: task_url+"front/greasemonkey.php?callback=dd",
//        data: {'ajaxTag':'setmktstatus','site':site, 'mid':mid},
//        type: "POST",
//        success: function (data) {               
//        	alert(data);
//        }
//    });
}
if(site != "undefined"){
	if(site == "csus"){
		backend_url = "http://csusbackend.megainformationtech.com";
	}
	if(site == "csca"){
		backend_url = "http://cscabackend.megainformationtech.com";
	}
	if(site == "csau"){
		backend_url = "http://csaubackend.megainformationtech.com";
	}
	if(site == "csie"){
		backend_url = "http://csiebackend.megainformationtech.com";
	}
	if(site == "csnz"){
		backend_url = "http://csnzbackend.megainformationtech.com";
	}
	if(site == "csde"){
		backend_url = "http://csdebackend.megainformationtech.com";
	}
	if(site == "csuk"){
		backend_url = "http://csukbackend.megainformationtech.com";
	}
	if(site == "csfr"){
		backend_url = "http://csfrbackend.megainformationtech.com";
	}
	GM_wait();
}

function getMid(){
	var url = arguments[0] || window.location.href;
	
	if(site == "csca"){
		//var regex = /^http\:\/\/(.*)\/m([0-9]+)-(.*)-online-coupons-codes\.html(.*)/;	//m110046-Doubleday-Canada-online-coupons-codes.html
		//var regex = /^http\:\/\/(.*)\/store\/(.*)\.html(.*)/;		//http://www.yessaving.ca/store/raileurope.html		
		var regex = /^http\:\/\/(.*)\/merchant-(.*)-(coupons|vouchers)-deals-([0-9]+)\.html(.*)/;		//http://www.promopro.com/ca/merchant-WeatherTech-coupons-deals-102082.html
		var match = url.match(regex);		
		
	}else if(site == "csde"){
		var regex = /^http\:\/\/(.*)\/(.*)-Gutscheine-Deals-([0-9]+)\.html(.*)/;	//Kaufmann-Bitdefender-Gutscheine-Angebote-9146.html
		var match = url.match(regex);
	}else{
		//var regex = /^http\:\/\/(.*)\.couponsnapshot.com\/merchant-(.*)-coupons-deals-([0-9]+)\.html(.*)/;	
		var regex = /^http\:\/\/(.*)\/merchant-(.*)-(coupons|vouchers)-deals-([0-9]+)\.html(.*)/;	
		var match = url.match(regex);
	}
	
	if(site == "csca"){
		var regex_tag = /^http\:\/\/(.*)\/(discount|seasonal|product|brand|subcategory)\/([a-zA-Z0-9\.]+)\.html(.*)/;
		var match_tag = url.match(regex_tag);
		
	}else if(site == "csde"){	
		var regex_tag = /^http\:\/\/(.*)\/etikettierte-Online-Gutscheine-mit-(.*)-([0-9]+)\.html(.*)/;	//http://www.allecodes.de/etikettierte-Online-Gutscheine-mit-Accessiores-16285.html
		var match_tag = url.match(regex_tag);
		
	}else{
		var regex_tag = /^http\:\/\/(.*)\/online-(coupons|vouchers)-tagged-with-(.*)-([0-9]+)\.html(.*)/;
		var match_tag = url.match(regex_tag);
	}
	
	var regex_coupon_comment = /^http\:\/\/(.*)\/(.*)-(coupon|voucher)-comments-([0-9]+)\.html(.*)/;
	var match_coupon_comment = url.match(regex_coupon_comment);	
	
	if(typeof match != "undefined" && null != match){
		//host = match[1];
		if(site == "csca"){			
			var id = match[4];
			
		}else if(site == "csde"){
			var id = match[3];				
		}else{
			var id = match[4];
		}
		return "/mer/"+id;
	}else if(typeof match_tag != "undefined" && null != match_tag){
		if(site == "csca"){
			var id = match_tag[3];
		}else if(site == "csde"){
			var id = match_tag[3];
		}else{
			var id = match_tag[4];
		}
		return "/tag/"+id;
	}else if(typeof match_coupon_comment != "undefined" && null != match_coupon_comment){		
		var id = match_coupon_comment[4];
		return "/coupon/"+id;
	}else{
		return "f";
	}
}

function getIdType(){
	var id_str = getMid();
	if(id_str == "f"){
		var url = window.location.href;
		var regex = /^http\:\/\/(.*)\/(.*)\.html(.*)/;
		var match = url.match(regex);
		
		if(typeof match != "undefined" && null != match){
			var from_obj = "/"+match[2]+".html";		
			/*$.ajax({           
	            async: false,
	            url: task_url+"/front/greasemonkey.php?callback=?",
	            data: {'ajaxTag':'getPageRedir','url':from_obj,'site':site},            
	            success: function (data) {               
	            	id = data.ToObjId;
	    			type = data.ToObjType;
	    			if(type == "MERCHANT"){
	    				type = "mer";
	    			}else if(type == "TAG"){
	    				type = "tag";
	    			}
	            }
	        });*/
			
			$.getJSON(task_url+"/front/greasemonkey.php?callback=?", {'ajaxTag':'getPageRedir','url':from_obj,'site':site}, function(data){			
				id = data.ToObjId;
				type = data.ToObjType;
				if(type == "MERCHANT"){
					type = "mer";
				}else if(type == "TAG"){
					type = "tag";
				}
			});
		}else{
			id = null;
		}
	}else{
		id_str = id_str.split("/");
		type = id_str[1];
		id = id_str[2];
	}
	
}

function getQuickLinks(){
	getIdType();
	checkId();
}

function checkId(){
	//alert(id);
	if(typeof id == 'undefined'){		
		window.setTimeout(checkId,100);
	}else{		
		showQuickBlock(id, type);
	}
}
	
function showQuickBlock(id, type){	
	var width = '350px';
	var height = '';
	var button_text = "HIDE";
	var gm_content = "block";
	//alert($.cookie("greasemonkey_size"));
//	if($.cookie("greasemonkey_size") == "small"){		
//		width="32px";
//		height="16px";
//		gm_content="none";
//		button_text="show";		
//	}
	

	var forcerefresh = "forcerefresh=1";
	var findstr = /forcerefresh/i;
	var r = url.search(findstr);
	if(r != "-1"){
		forcerefresh = "";
	}else{		
		r = url.search(/\?/);
		if(r == "-1" && site == "csca"){
			forcerefresh = "?"+forcerefresh;				
		}else{
			forcerefresh = "&"+forcerefresh;
		}		
	}
	if (type == "mer" && id != null){
		$.getJSON(task_url+"/front/greasemonkey.php?callback=?", {'ajaxTag':'merchantName','mid':id,'site':site}, function(data){
			//alert("JSON data: " + data.IsActive);		
			var priority = data.Priority;
			if(priority == 1){
				priority = "Very Important (Top 20)";
			}else if(priority == 2){
				priority = "Important (Top 15%)";
			}else if(priority == 3){
				priority = "Normal (Top 15% - 50%)";
			}else if(priority == 4){
				priority = "Low (Under Top 50%)";
			}else if(priority == 5){
				priority = "Do NOT Update";
			}else{
				priority = "Unassigned";
			}
			
			var reportissue = "";
			var content_task_cfg = "";
			if(site != "undefined"){
				reportissue = "<a href='http://task.megainformationtech.com/front/merchant_issue_list.php?act=addnew&site="+site+"&mid="+data.Id+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Report Issue</a> <br/>";
				//content_task_cfg = "<a href='http://task.megainformationtech.com/front/merchant_task_config.php?site="+site+"&merchantid="+id+"&showconfig=2&merconfig=config' target='_blank' style='color:green;font-size:12px'>Content Task Cfg</a> ";
			}
			
			var add_deal = "";
//			if(site == "csus"){
				add_deal = "<a href='http://task.megainformationtech.com/editor/deal.php?action=adddeal&merchant="+data.Id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Add Deal</a>"; 
//			}
				
			var b_alias = "";
			if(data.alias.length > 0){
				b_alias = "<span>Alias: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.alias+"</span><br>";
			}
			
			var b_tips = "";
			//if(data.Tip.length > 0){
				b_tips = "<span>Editorial Tips: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC;display:block'>"+data.Tip+"</span>";
			//}
			
			var b_aff = "";			
			if(typeof(data.DefaultAffiliate) != "undefined" && data.DefaultAffiliate.length > 0){
				b_aff = "<span>Default Url with Aff: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.DefaultAffiliate+"</span><br>";
			}
			if(typeof(data.DeepUrlTemplateAffiliate) != "undefined" && data.DeepUrlTemplateAffiliate.length > 0){
				b_aff += "<span>Deep Url Template with Aff: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.DeepUrlTemplateAffiliate+"</span><br>";
			}
			
			var is_fake = "";			
			if(typeof(data.isFake_Default) != "undefined" && data.isFake_Default.length > 0){
				is_fake = "<span>P-S DefaultAFFURLProgram: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.isFake_Default+"</span><br>";
			}
			if(typeof(data.isFake_Deep) != "undefined" && data.isFake_Deep.length > 0){
				is_fake += "<span>P-S DefaultAFFURLTPLProgram: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.isFake_Deep+"</span><br>";
			}
			var mktCheck = "<br/><span id='mktstatus'>" + data.mktStatus + "&nbsp;&nbsp;&nbsp;&nbsp;Reason:<textarea id='mkt_remark'>" + data.mktRemark + "</textarea><br/><a href='javascript:setMktStatus(\"" + data.Id + "\");'>MKT Done</a>" + "</span>";
			$("body").before("<link href='"+task_url+"/css/greasemonkey.css' rel='stylesheet' type='text/css' />"+
					"<div id='divFixed'  style='position: fixed; bottom: 50px; width: 42px;height:42px;  right:1px; margin-right:15px; z-index: 999999'   style='background:#ECF5FF;text-align: center;'>" +
					"<a href='#top' target='_self'><img alt='' src='"+task_url+"/image/return.jpg'></a></div>"+
					"<div id='gm_div' class='drsElement'  style='width:"+width+";height:"+height+";padding:3px;margin:0px;border:groove 1px #CCCCCC;background:#FAFAFA;z-index:2147483647;position:fixed;display:block;overflow:hidden;font-family:Tahoma,Arial,Helvetica,sans-serif;font-size:14px;float:left;overflow-y:visible;'>" +
					"<div id='gm_content' style='display:"+gm_content+";'><a id='GM_close' href='#' style='float:right;color:green;font-size:12px' onclick='doCloseGM();'>CLOSE</a> <a id='GM_fix' href='#' onclick='fixGM();' style='float:right;padding-right:6px;color:green;font-size:12px'>FIXED</a> " +
					"<a href='"+url+forcerefresh+"' style='color:#325D9A;font-family:Tahoma,Arial'>Force Refresh Current Page</a> <br/>" +
					b_alias + //Alias
					"<span>Importance Rank: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.Rank+" (Grade "+data.Grade+")</span><br>" +
					"<span>Store Traffic: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.StoreTraffic + "</span><br>" +
					"<span>Allow External Deal: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.AllowExtDeal+"</span><br>" +
					"<span>Last Full Check Time: </span><span style='padding-left:10px;padding-right:20px;color:#3399CC'>"+data.LastFullCheckTime+"</span><br>" +
					"<span>Last Update Time: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.LastUpdateTime+"</span><br>" +
					"<span>7 Days Stats: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>Impr "+data.Imps7d+", Clk "+data.Clks7d+", CTR "+data.Ctr+"%</span><br>" +
					"<span>Has SEM Campaign: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.HasSEM+"</span><br>" +
					"<br>" +
					"<span>Has Affiliate: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.HasAffiliate+"</span><br>" +
					"<span>Affiliate Program: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.AffPro+"</span><br>" +
					b_aff + //Default Url with Aff
					"<span>Support Deep URL: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC;'>"+data.customurl+"</span><br>" +
					is_fake +
					"<br>" +
					"<span>Quick Links: </span><p style='padding-left:20px;padding-right:20px;color:#3399CC;font-size:12px;'>" +
					"<a href='http://task.megainformationtech.com/editor/merchant.php?action=editmerchant&merchantid="+data.Id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Edit Base Info</a> " +
					"<a href='http://task.megainformationtech.com/front/merchant_config_mkt_edit.php?merchantid="+data.Id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Edit MKT Info</a> " +
					"<a href='http://task.megainformationtech.com/front/merchant_config_content.php?merchantid="+data.Id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Edit Ctrl Info</a> " + "<br>" +
					"<a href='http://task.megainformationtech.com/front/store_edit_content.php?id="+data.StoreID+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Edit Store for Content</a> " +			
					"<a href='http://task.megainformationtech.com/front/store_edit_bd.php?id="+data.StoreID+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Edit Store for BD</a> " +
					"<br>" +					
					"<a href='http://task.megainformationtech.com/editor/coupon_list.php?showmerlist=-1&action=listcoupon&merchant="+data.Id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon List</a> " +
					"<a href='http://task.megainformationtech.com/editor/coupon.php?action=addcoupon&merchant="+data.Id+"&site="+site+"&showmerlist=-1' target='_blank' style='color:green;font-family:Tahoma,Arial'>Add Coupon</a> " +
					reportissue + 
//					add_deal +
					"<br>" +
					
					//"<a href='"+backend_url+"/editor/mer_email_rule_edit.php?merchant="+data.Id+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Email Dispatch</a> " +
					"<a href='"+backend_url+"/editor/review_approval.php?type=INITIAL&mid="+data.Id+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Mer Review Approval</a> " +					
					//"<a href='"+backend_url+"/editor/aff_links.php?showmerlist=-1&merchant="+data.Id+"&promo=all&proc=pending&ignzeromerid=all&showunknownexpire=yes&lastno=-1&perpage=25&affservice=-1' target='_blank' style='color:green;font-family:Tahoma,Arial'>Pending Links</a> " +
					//"<a href='"+backend_url+"/editor/aff_links.php?merchant="+data.Id+"&promo=coupon&proc=pending&ignzeromerid=yes&showunknownexpire=yes&lastno=-1&perpage=25&affservice=-1' target='_blank' style='color:green;font-family:Tahoma,Arial'>Pending Coupons</a> " +
					
					"<a href='"+backend_url+"/editor/review_coupon.php?type=INITIAL&mid="+data.Id+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon Review Approval</a> <br/>" +
					"<br/>" + 
			
					"<a href='http://reporting.megainformationtech.com/site_lp_daily.php?site="+ site +"&lp_phrase=" + encodeURI(page_uri) + "&sort=Date&order=DESC&createreport=Generate+Report' target='_blank' style='color:green;font-family:Tahoma,Arial'>LP Daily</a> " +
					"<a href='http://reporting.megainformationtech.com/site_lp_detail.php?site="+ site +"&lp=" + encodeURI(page_uri) + "&sort=Commission&order=DESC&createreport=Generate+Report' target='_blank' style='color:green;font-family:Tahoma,Arial'>LP Transactions</a> " +
					"<a href='http://reporting.megainformationtech.com/site_lp_src.php?p=1&site="+ site +"&lp=" + encodeURI(page_uri) + " target='_blank' style='color:green;font-family:Tahoma,Arial' target='_blank'>LP Traffic Source</a> <br/>" +

					"<a href='http://reporting.megainformationtech.com/site_page.php?sortcol=impr&sortord=1&si=" + site + "&pt=merchant&pv=" + data.Id + "&di=pa' target='_blank' style='color:green;font-family:Tahoma,Arial'>Placement Report</a> " +
					"<a href='http://reporting.megainformationtech.com/site_coupon.php?sortcol=impr&sortord=1&si="+ site +"&mi="+ data.id +"&fromTo_cp=&di=ci' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon Report</a> " +
					"<a href='http://reporting.megainformationtech.com/site_clkarea.php?sortcol=clk&sortord=1&si=" + site + "&pt=merchant&pv=" + data.Id + "' target='_blank' style='color:green;font-family:Tahoma,Arial'>CA Report</a> " +
					"<br>" +
					"</p>" +
					"<span>Editor: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.Editor+"</span><br>" +
					b_tips +
					//mktCheck + 
					//content_task_cfg +
					"</div>"+
					"<div id='GM_hide' style='float:right;cursor:pointer;background:#23487E;color:#FFFFFF' onclick='resizeGM()'>"+button_text+"</span>" +
					"</div>");			
		});
	
	}else if (type == "tag" && id != null){
			$.getJSON(task_url+"/front/greasemonkey.php?callback=?", {'ajaxTag':'tag','urlName':id,'site':site}, function(data){				
				showTagBlock(data.Id,width,height,gm_content,forcerefresh,button_text, data);
			});
		
	}else if (type == "coupon" && id != null){
		$.getJSON(backend_url+"/editor/greasemonkey.php?callback=?", {'ajaxTag':'couponComment','cid':id}, function(data){
			var mid = data.mid;
			$("body").before("<div id='gm_div' style='width:"+width+";height:"+height+";padding:3px;margin:0px;border:groove 1px #CCCCCC;background:#FAFAFA;z-index:999999;position:fixed;display:block;overflow:hidden;font-family:Tahoma,Arial,Helvetica,sans-serif;font-size:14px;float:left'>" +
					"<div id='gm_content' style='display:"+gm_content+";'><a id='GM_close' href='#' style='float:right;color:green;font-size:12px'>CLOSE</a> <a id='GM_fix' href='#' style='float:right;padding-right:6px;color:green;font-size:12px'>FIXED</a> " +
					"<span>Quick Links: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC;font-size:12px;'>" +
					"<a href='http://task.megainformationtech.com/editor/coupon.php?action=editcoupon&showmerlist=-1&couponid="+id+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon Edit</a> " +
					"<a href='http://task.megainformationtech.com/editor/coupon_list.php?merchant="+mid+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon List</a> " +					
					"<a href='http://task.megainformationtech.com/editor/coupon.php?action=addcoupon&merchant="+mid+"&site="+site+"&showmerlist=-1' target='_blank' style='color:green;font-family:Tahoma,Arial'>Add Coupon</a> " +
					"<a href='"+backend_url+"/editor/review_coupon.php?type=INITIAL&mid="+mid+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon Comment Approval</a>" +
					"<br>" +
					"<a href='"+url+forcerefresh+"' style='color:#325D9A;font-family:Tahoma,Arial'>Force Refresh Current Page</a> " +
					"</span></div>" +
					"<div id='GM_hide' style='float:right;cursor:pointer;background:#23487E;color:#FFFFFF'>"+button_text+"</span>" +
					"</div>");			
		});
	}
	
	getGMHeight();	
	//alert($.cookie("greasemonkey_size"));
	if($.cookie("greasemonkey_size") == "small"){
		setTimeout(function() {			
			$("#gm_div").css({"width": "42px", "height": "16px"});
			$("#gm_content").hide();
			$("#GM_hide").text("SHOW");
		}, 1000);		
	}
}

function showTagBlock(tid,width,height,gm_content,forcerefresh,button_text, data){
	var b_alias = "";
	if(data.Alias.length > 0){
		b_alias = "<span>Alias: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.Alias+"</span><br>";
	}
	var b_tips = "";
	if(data.EditorTips.length > 0){
		b_tips = "<span>Editorial Tips: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC;display:block'>"+data.EditorTips+"</span>";
	}
	var mktCheck = "<span>" + data.mktStatus + "</span>";
	
	$("body").before("<div id='gm_div' style='width:"+width+";height:"+height+";padding:3px;margin:0px;border:groove 1px #CCCCCC;background:#FAFAFA;z-index:999999;position:fixed;display:block;overflow:hidden;font-family:Tahoma,Arial,Helvetica,sans-serif;font-size:14px;float:left'>" +
			"<div id='gm_content' style='display:"+gm_content+"'><a id='GM_close' href='#' style='float:right;color:green;font-size:12px'>CLOSE</a> <a id='GM_fix' href='#' style='float:right;padding-right:6px;color:green;font-size:12px'>FIXED</a> " +
			"<a href='"+url+forcerefresh+"' style='color:#325D9A;font-family:Tahoma,Arial'>Force Refresh Current Page</a> <br/>" + 
			b_alias + //Alias
			"<span>Tag Type: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.TagTypeName+"</span><br>" +
			"<span>Importance Rank: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.Rank+" (Grade "+data.Grade+")</span><br>" +
			"<span>Last Full Check Time: </span><span style='padding-left:10px;padding-right:20px;color:#3399CC'>"+data.LastCheckTime+"</span><br>" +
			"<span>Last Update Time: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.LastUpdateTime+"</span><br>" +
			"<span>7 Days Stats: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>Impr "+data.Imps7d+", Clk "+data.Clks7d+", CTR "+data.Ctr+"%</span><br>" +
			"<span>Quick Links: TagId("+tid+")</span>" + 
			"<br>" +
			"<p style='padding-left:20px;padding-right:20px;color:#3399CC;font-size:12px;'>" +
			" <a href='http://task.megainformationtech.com/editor/tag.php?site="+site+"&action=edittag&tagid="+tid+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Tag Edit</a> " +
			" <a href='http://task.megainformationtech.com/editor/coupon_list.php?showmerlist=-1&action=listcoupon&tag_id="+tid+"&site="+site+"' target='_blank' style='color:green;font-family:Tahoma,Arial'>Coupon List</a>" +
			" <a href='" + backend_url +"/editor/review_tag.php?type=INITIAL&m_start=a&tid=" + tid + "' target='_blank' style='color:green;font-family:Tahoma,Arial'>Tag Review Approval</a> <br/>" +
			" <a href='http://reporting.megainformationtech.com/site_lp_daily.php?site="+ site +"&lp_phrase=" + encodeURI(page_uri) + "&sort=Date&order=DESC&createreport=Generate+Report' target='_blank' style='color:green;font-family:Tahoma,Arial'>LP Daily</a>" +
			" <a href='http://reporting.megainformationtech.com/site_lp_detail.php?site="+ site +"&lp=" + encodeURI(page_uri) + "&sort=Commission&order=DESC&createreport=Generate+Report' target='_blank' style='color:green;font-family:Tahoma,Arial'>LP Transactions</a>" +
			" <a href='http://reporting.megainformationtech.com/site_lp_src.php?p=1&site="+ site +"&lp=" + encodeURI(page_uri) + " target='_blank' style='color:green;font-family:Tahoma,Arial' target='_blank'>LP Traffic Source</a>" +
			" <a href='http://reporting.megainformationtech.com/site_page.php?sortcol=impr&sortord=1&si=" + site + "&pt=tag&pv=" + data.Id + "&di=pa' target='_blank' style='color:green;font-family:Tahoma,Arial'>Placement Report</a>" +
			" <a href='http://reporting.megainformationtech.com/site_clkarea.php?sortcol=clk&sortord=1&si=" + site + "&pt=tag&pv=" + data.Id + "' target='_blank' style='color:green;font-family:Tahoma,Arial'>CA Report</a> " +
			"</p>" +
			"<br>" +
			"<span>Editor: </span><span style='padding-left:20px;padding-right:20px;color:#3399CC'>"+data.Editor+"</span><br>" +
			b_tips + 
			//mktCheck +
			"</div>" +	
			
			"<div id='GM_hide' style='float:right;cursor:pointer;background:#23487E;color:#FFFFFF' onclick='resizeGM()'>"+button_text+"</span>" +
			"</div>");
}

function getGMHeight(){
	var div_height = $("#gm_div").height();
	if(div_height == null || div_height == "undefined"){
		window.setTimeout(getGMHeight,100);
	}else{
		if(div_height > 700){
			div_height = 700;			
			$("#gm_div").css("height","700px");
			$("#gm_div").css("overflow-x","hidden");			
		}
		GM_DIV_H = div_height;
	}	
}

function closeGM(){
	$("#GM_close").bind("click", function(){
		doCloseGM();
	});
}

function doCloseGM(){
	$("#gm_div").remove();
}


function hideGM(){
	$("#GM_hide").bind("click", function(){
		resizeGM();
	});	
}
function resizeGM(){
	if (GM_DIV_H == null || GM_DIV_H =="undefined"){		
		GM_DIV_H = $("#gm_div").height();
	}
	if($("#GM_hide").text() == "HIDE"){
		$("#gm_div").animate({ 
			width: "42px",
			height: "16px"
		}, 450);
		$("#gm_content").hide("fast",function(){
			$("#GM_hide").text("SHOW");
		});
		$.cookie("greasemonkey_size","small");
	}else{
		$("#gm_div").animate({ 
			width: "350px",
			height: GM_DIV_H
		}, 450);
		$("#gm_content").show("fast",function(){
			$("#GM_hide").text("HIDE");
		});
		$.cookie("greasemonkey_size","show");
	}
}
function fixChangeGM(){
	$("#GM_fix").bind("click", function(){
		fixGM();
	});
}

function fixGM(){
	if($("#GM_fix").text() == "FIX"){
		$("#gm_div").css("position","fixed");
		$("#GM_fix").text("FIXED");
	}else{
		$("#gm_div").css("position","absolute");
		$("#GM_fix").text("FIX");
	}	
}
function removeCover(){
	$("div[id^='divcover']").remove();
}

function couponQuickTools(){
	getQuickLinks();    
    closeGM();    
    fixChangeGM();
    hideGM();
}

function GM_wait(){
	if(typeof jQuery == 'undefined'){
		window.setTimeout(GM_wait,100);
	}else{		
		couponQuickTools();
	}	
}
function drag(){
	if(typeof addEvent!='function'){var addEvent=function(o,t,f,l){var d='addEventListener',n='on'+t,rO=o,rT=t,rF=f,rL=l;if(o[d]&&!l)return o[d](t,f,false);if(!o._evts)o._evts={};if(!o._evts[t]){o._evts[t]=o[n]?{b:o[n]}:{};o[n]=new Function('e','var r=true,o=this,a=o._evts["'+t+'"],i;for(i in a){o._f=a[i];r=o._f(e||window.event)!=false&&r;o._f=null}return r');if(t!='unload')addEvent(window,'unload',function(){removeEvent(rO,rT,rF,rL)})}if(!f._i)f._i=addEvent._i++;o._evts[t][f._i]=f};addEvent._i=1;var removeEvent=function(o,t,f,l){var d='removeEventListener';if(o[d]&&!l)return o[d](t,f,false);if(o._evts&&o._evts[t]&&f._i)delete o._evts[t][f._i]}}function cancelEvent(e,c){e.returnValue=false;if(e.preventDefault)e.preventDefault();if(c){e.cancelBubble=true;if(e.stopPropagation)e.stopPropagation()}};function DragResize(myName,config){var props={myName:myName,enabled:true,handles:['tl','tm','tr','ml','mr','bl','bm','br'],isElement:null,isHandle:null,element:null,handle:null,minWidth:10,minHeight:10,minLeft:0,maxLeft:9999,minTop:0,maxTop:9999,zIndex:9999998,mouseX:0,mouseY:0,lastMouseX:0,lastMouseY:0,mOffX:0,mOffY:0,elmX:0,elmY:0,elmW:0,elmH:0,allowBlur:true,ondragfocus:null,ondragstart:null,ondragmove:null,ondragend:null,ondragblur:null};for(var p in props)this[p]=(typeof config[p]=='undefined')?props[p]:config[p]};DragResize.prototype.apply=function(node){var obj=this;addEvent(node,'mousedown',function(e){obj.mouseDown(e)});addEvent(node,'mousemove',function(e){obj.mouseMove(e)});addEvent(node,'mouseup',function(e){obj.mouseUp(e)})};DragResize.prototype.select=function(newElement){with(this){if(!document.getElementById||!enabled)return;if(newElement&&(newElement!=element)&&enabled){element=newElement;element.style.zIndex=++zIndex;if(this.resizeHandleSet)this.resizeHandleSet(element,true);elmX=parseInt(element.style.left);elmY=parseInt(element.style.top);elmW=element.offsetWidth;elmH=element.offsetHeight;if(ondragfocus)this.ondragfocus()}}};DragResize.prototype.deselect=function(delHandles){with(this){if(!document.getElementById||!enabled)return;if(delHandles){if(ondragblur)this.ondragblur();if(this.resizeHandleSet)this.resizeHandleSet(element,false);element=null}handle=null;mOffX=0;mOffY=0}};DragResize.prototype.mouseDown=function(e){with(this){if(!document.getElementById||!enabled)return true;var elm=e.target||e.srcElement,newElement=null,newHandle=null,hRE=new RegExp(myName+'-([trmbl]{2})','');while(elm){if(elm.className){if(!newHandle&&(hRE.test(elm.className)||isHandle(elm)))newHandle=elm;if(isElement(elm)){newElement=elm;break}}elm=elm.parentNode}if(element&&(element!=newElement)&&allowBlur)deselect(true);if(newElement&&(!element||(newElement==element))){if(newHandle)cancelEvent(e);select(newElement,newHandle);handle=newHandle;if(handle&&ondragstart)this.ondragstart(hRE.test(handle.className))}}};DragResize.prototype.mouseMove=function(e){with(this){if(!document.getElementById||!enabled)return true;mouseX=e.pageX||e.clientX+document.documentElement.scrollLeft;mouseY=e.pageY||e.clientY+document.documentElement.scrollTop;var diffX=mouseX-lastMouseX+mOffX;var diffY=mouseY-lastMouseY+mOffY;mOffX=mOffY=0;lastMouseX=mouseX;lastMouseY=mouseY;if(!handle)return true;var isResize=false;if(this.resizeHandleDrag&&this.resizeHandleDrag(diffX,diffY)){isResize=true}else{var dX=diffX,dY=diffY;if(elmX+dX<minLeft)mOffX=(dX-(diffX=minLeft-elmX));else if(elmX+elmW+dX>maxLeft)mOffX=(dX-(diffX=maxLeft-elmX-elmW));if(elmY+dY<minTop)mOffY=(dY-(diffY=minTop-elmY));else if(elmY+elmH+dY>maxTop)mOffY=(dY-(diffY=maxTop-elmY-elmH));elmX+=diffX;elmY+=diffY}with(element.style){left=elmX+'px';width=elmW+'px';top=elmY+'px';height=elmH+'px'}if(window.opera&&document.documentElement){var oDF=document.getElementById('op-drag-fix');if(!oDF){var oDF=document.createElement('input');oDF.id='op-drag-fix';oDF.style.display='none';document.body.appendChild(oDF)}oDF.focus()}if(ondragmove)this.ondragmove(isResize);cancelEvent(e)}};DragResize.prototype.mouseUp=function(e){with(this){if(!document.getElementById||!enabled)return;var hRE=new RegExp(myName+'-([trmbl]{2})','');if(handle&&ondragend)this.ondragend(hRE.test(handle.className));deselect(false)}};DragResize.prototype.resizeHandleSet=function(elm,show){with(this){if(!elm._handle_tr){for(var h=0;h<handles.length;h++){var hDiv=document.createElement('div');hDiv.className=myName+' '+myName+'-'+handles[h];elm['_handle_'+handles[h]]=elm.appendChild(hDiv)}}for(var h=0;h<handles.length;h++){elm['_handle_'+handles[h]].style.visibility=show?'inherit':'hidden'}}};DragResize.prototype.resizeHandleDrag=function(diffX,diffY){with(this){var hClass=handle&&handle.className&&handle.className.match(new RegExp(myName+'-([tmblr]{2})'))?RegExp.$1:'';var dY=diffY,dX=diffX,processed=false;if(hClass.indexOf('t')>=0){rs=1;if(elmH-dY<minHeight)mOffY=(dY-(diffY=elmH-minHeight));else if(elmY+dY<minTop)mOffY=(dY-(diffY=minTop-elmY));elmY+=diffY;elmH-=diffY;processed=true}if(hClass.indexOf('b')>=0){rs=1;if(elmH+dY<minHeight)mOffY=(dY-(diffY=minHeight-elmH));else if(elmY+elmH+dY>maxTop)mOffY=(dY-(diffY=maxTop-elmY-elmH));elmH+=diffY;processed=true}if(hClass.indexOf('l')>=0){rs=1;if(elmW-dX<minWidth)mOffX=(dX-(diffX=elmW-minWidth));else if(elmX+dX<minLeft)mOffX=(dX-(diffX=minLeft-elmX));elmX+=diffX;elmW-=diffX;processed=true}if(hClass.indexOf('r')>=0){rs=1;if(elmW+dX<minWidth)mOffX=(dX-(diffX=minWidth-elmW));else if(elmX+elmW+dX>maxLeft)mOffX=(dX-(diffX=maxLeft-elmX-elmW));elmW+=diffX;processed=true}return processed}};

	var dragresize = new DragResize('dragresize',
			{ minWidth: 50, minHeight: 50, minLeft: 20, minTop: 20, maxLeft: 600, maxTop: 600 });


			dragresize.isElement = function(elm)
			{
			if (elm.className && elm.className.indexOf('drsElement') > -1) return true;
			};
			dragresize.isHandle = function(elm)
			{
			if (elm.className && elm.className.indexOf('drsMoveHandle') > -1) return true;
			};

			dragresize.ondragfocus = function() { };
			dragresize.ondragstart = function(isResize) { };
			dragresize.ondragmove = function(isResize) { };
			dragresize.ondragend = function(isResize) { };
			dragresize.ondragblur = function() { };

			dragresize.apply(document);
}

window.onload=function(){
	drag();
}