/*	function mutiExpiredPromotion(site){
		var coupon_id_list='';
		$.each($(':checkbox'),function(i,n){
			if($(this).attr('checked') && $(this).val() != 'checkall'){
				coupon_id_list += $(this).val()+',';
			}
		});
		if(coupon_id_list != ''){
			if(confirm('Please confirm to make selected promotions expired?')){
				$.ajax({type: "GET",
					url: "/editor/search.php?site="+ site +"&ajaxTag=mutiExpiredPromotionCheck&q="+coupon_id_list,
					dataType: "json",
					success: function(chkmsg){
						if(chkmsg['hasunkown']=='yes' && confirm('the promotions you selected include \'Unknown\' and \'Never\' type ones, ids:'+chkmsg['ids']+'. \nClick \'Ok\' to make ALL selected promotions expired and click \'Cancel\' to make \'Fixed\' only promotions expired.')){
							var processall = 'yes';
						} else {
							var processall = 'no';
						}
						$.ajax({type: "GET",
							url: "/editor/search.php?site="+ site +"&ajaxTag=mutiExpiredPromotion&processall="+processall+"&q="+coupon_id_list,
							dataType: "json",
							success: function(msg){
								alert(msg['affect']+' selected promotions have been expired.');
								if(parseInt(msg['affect'])>0){
									document.location.reload();
								}
							}					   
						});
					}					   
				});
			}
		} else {
			alert('Please select at least one promotions.');
		}
	}
	
	
	function mutiDelayRemindDate(site){
		var coupon_id_list='';
		$.each($(':checkbox'),function(i,n){		
			if($(this).attr('checked') && $(this).val() != 'checkall'){
				coupon_id_list += $(this).val()+',';
			}
		});
		if(coupon_id_list != ''){
			if(confirm('Please confirm to delay the RemindDate of selected promotions?')){
				$.ajax({type: "GET",
					url: "/editor/search.php?site="+ site +"&ajaxTag=mutiDelayCheckDate&q="+coupon_id_list,
					dataType: "json",
					success: function(msg){
						alert(msg['affect']+' selected promotions RemindDate have been delayed.');
						if(parseInt(msg['affect'])>0){
							document.location.reload();
						}
					}					   
				});
			}
		} else {
			alert('Please select at least one promotions.');
		}

	}
	
	
	function domultiEdit(){
		$("#testarea").html("<img src='/image/loading.gif' />");
		
		var coupon_id_list=$("#coupon_id_list").val();
		
		var num=$("#numM").val();
		var temp_query="";		
		for(var i=0;i<num;i++){
			coupon_id=$("#coupon_id_"+i).html();
			isactive=$("#edit_isactive_"+i).val();
			type=$("#edit_type_"+i).val();
			//[DE SITE]-------encodeURIComponent instead escape
			couponcode=escape($("#edit_couponcode_"+i).val());
			startdate=$("#edit_startdate_"+i).val();
			expiration=$("#edit_expiration_"+i).val();
			title_temp=escape($("#edit_title_"+i).val());
			desc_temp=escape($("#edit_desc_"+i).val());
			
			var edit_pro_detail = $("#edit_pro_detail_" + i).val();
			var edit_money_type = $("#edit_money_type_" + i).val();
			var edit_pro_off = $("#edit_pro_off_" + i).val();
			var coupontype = $("#edit_coupontype_" + i).val();
		
			if(edit_pro_detail == "percent" || edit_pro_detail == "money"){
				if(!checkFloat(edit_pro_off)){
					alert("Promotion OFF must be a number.");
					$("#edit_pro_off_" + i).focus();
					return false;
				}
			}
			//euro
			var re = /%u20AC/g;
			title = title_temp.replace(re, "%80");
			title = title.replace(/\+/g, "%2B");
			desc = desc_temp.replace(re, "%80");
			desc = desc.replace(/\+/g, "%2B");

			starttime=$("#edit_starttime_"+i).val();
			endtime=$("#edit_endtime_"+i).val();
			
			if(!checkIsValidDate($("#edit_startdate_"+i).val().trim()))
			{
				alert('start date should use this format YYYY-mm-dd');
				$("#edit_startdate_"+i).focus();
				$("#testarea").html("");
				return false;
			}			
			if(!checkIsValidDate($("#edit_expiration_"+i).val().trim()))
			{
				alert('expire date should use this format YYYY-mm-dd');
				$("#edit_expiration_"+i).focus();
				$("#testarea").html("");
				return false;
			}
			if(!checkIsValidTime($("#edit_starttime_"+i).val().trim()))
			{
				alert('start time should use this format hh:mm:ss');
				$("#edit_starttime_"+i).focus();
				$("#testarea").html("");
				return false;
			}
			if(!checkIsValidTime($("#edit_endtime_"+i).val().trim()))
			{
				alert('expire time should use this format hh:mm:ss');
				$("#edit_endtime_"+i).focus();
				$("#testarea").html("");
				return false;
			}			
			
			startdate=startdate+" "+starttime;
			expiration=expiration+" "+endtime;

			var year = parseInt(expiration.substr(0,4)) - parseInt(startdate.substr(0,4));
			var month = parseInt(expiration.substr(5,2)) - parseInt(startdate.substr(5,2));
			var day = parseInt(expiration.substr(8,2)) - parseInt(startdate.substr(8,2));
			if((year > 2) || (year == 2 && month > 0) || (year == 2 && month == 0 && day > 0))
			{
				alert('end date can not more than 2 years');
				$("#edit_expiration_"+i).focus();
				$("#testarea").html("");
				return false;
			}

			if(expiration == "" || expiration == "0000-00-00"){
				alert('expire date can not be 0000-00-00');
				$("#edit_expiration_"+i).focus();
				$("#testarea").html("");
				return false;
			}
	
			if(expiration<startdate && expiration != "0000-00-00" && expiration != "0000-00-00 00:00:00"){
				alert('start date should earlier than expire date');
				$("#edit_expiration_"+i).focus();
				$("#testarea").html("");
				return false;
			}
			
			temp_query +="num_"+i+"="+i+"&coupon_id_"+i+"="+coupon_id+"&isactive_"+i+"="+isactive+"&type_"+i+"="+type+"&couponcode_"
			+i+"="+couponcode+"&startdate_"+i+"="+startdate+"&expiration_"+i+"="+expiration+"&title_"+i+"="+title+"&desc_"+i+"="+desc+"&"
			+ "edit_pro_detail_" + i + "=" + edit_pro_detail 
			+ "&edit_money_type_" + i + "=" + edit_money_type 
			+ "&edit_pro_off_" + i + "=" + edit_pro_off
			+ "&coupontype_" + i + "=" + coupontype + "&";
		}
		site = $("#site").val();
		temp_query +="count="+num;
//		alert("/editor/search.php?site="+ site +"&ajaxTag=multiEdit&q="+escape($("#coupon_id_list").val()) + "&" +  temp_query);
		$.ajax({type: "POST",
			url: "/editor/search.php?site="+ site +"&ajaxTag=multiEdit&q="+escape($("#coupon_id_list").val()),
			data: temp_query,
			success: function(msg){
//				alert(msg);
				var s = msg.split("||zy||");
				var ss = new Array();				
				for(var i=0; i<s.length; i++){
					var tt=s[i];
					
					ss = tt.split("//zy//");
					if(ss.length == 0){
						continue;
					}

					var temp_couponid=ss[0];

					var oldType = $("#coupontypehid_" + temp_couponid).val();
					var newType = ss[12];
					
					$("#isactive_"+temp_couponid).html(ss[1]);
					$("#couponcode_"+temp_couponid).html(ss[3]);
					$("#startdate_"+temp_couponid).html(ss[4]);
					$("#expiration_"+temp_couponid).html(ss[5]);
					$("#status_"+temp_couponid).html(ss[6]);

					$("#title_"+temp_couponid).html(ss[7]);
					$("#desc_"+temp_couponid).html(ss[8]);
					
					$("#pro_detail_"+ temp_couponid ).val(ss[9]);
					$("#money_type_" + temp_couponid).val(ss[10]);
					$("#pro_off_" + temp_couponid).val(ss[11]);
					$("#type_" + temp_couponid).html(couponTypeArr[ss[12]]);
					$("#coupontypehid_" + temp_couponid).val(ss[12]);
					var detailStr = "";
					if(ss[9] == "percent"){
						detailStr = ss[11] + "% OFF"
					}else if(ss[9] == "money"){
						var money = ss[10];
						detailStr = arr_money[money] + ss[11] + " OFF"
					}else{
						detailStr = arr_pro[ss[9]];
					}
					$("#pdetail_" + temp_couponid).html(detailStr);
				}				
								
				$("#sy_edit_button").insertAfter("#coupon_id_list");				
				for(var i=0;i<num;i++){
					var coupon_id=$("#edit_coupon_id_"+i).attr("cid");
					$("#tr_"+coupon_id).show();
				}
				$("tr").remove(".addmo");
				$("#multieidt_button").show();
				$("#multicancel_button").hide();
				$("#multisave_button").hide();
				$(":checkbox").css('visibility','visible');
				$("#edit_pro_detail_" + i).remove();
				$("#edit_money_type_" + i).remove();
				$("#edit_pro_off_" + i).remove();
				$("#edit_coupontype_" + i).remove();
				
				$("#testarea").html("");
			}					   
		});
	}
	
	
	function multiEdit(){
		var coupon_id_list='';
		var insert_id="listmark";
		var num=0;
		$.each($(':checkbox'),function(i,n){		
			if($(this).attr('checked')){			
				coupon_id=$(this).val();
				var pro_off;
				var money_type;
				var pro_detail;
				pro_off = $("#pro_off_" + coupon_id).val();
				money_type = $("#money_type_" + coupon_id).val();
				pro_detail = $("#pro_detail_" + coupon_id).val();
				var couponTypeHid = $("#coupontypehid_" + coupon_id).val();

				if(coupon_id!='checkall'){
					$("#tr_"+coupon_id).hide();
					var	temp_html=$("#editarea").html();
					var	startdate=$("#startdate_"+coupon_id).html();
					var	expiration=$("#expiration_"+coupon_id).html();
					var	type=$("#type_"+coupon_id).html();
					var isactive=$("#isactive_"+coupon_id).html();
					var couponcode=$("#couponcode_"+coupon_id).html();
					var starttime=$("#starttime_"+coupon_id).val();
					var endtime=$("#endtime_"+coupon_id).val();
					
					var sel="";
					if(isactive=="NO"){
						sel="selected";
					}
					var promoStr = promoHtml(pro_off, money_type, pro_detail, num);
					var couponType = couponTypeHtml(couponTypeHid, num);

					
					var typeoption="";
					var typeoptionarray=new Array('Coupon','Printable coupon','Deal','Product Deal','Exclusive Coupon');
					for(var j=0;j<5;j++){						
						if(type==typeoptionarray[j]){
							typesel="selected";
						}else{
							typesel="";
						}
						typeoption+="<option value='"+(j+1)+"' "+typesel+">"+typeoptionarray[j]+"</option>";
					}
					
					var coupontype="<select id='edit_type_"+num+"' style='font-size:12px;width:100px'>"+typeoption+"</select>";
//										+"<option value='1'>Coupon</option>"
//										+"<option value='5'>Exclusive Coupon</option>"
//										+"<option value='2'>Printable coupon</option>"
//										+"<option value='3'>Deal</option>"
//										+"<option value='4'>Product Deal</option>"
//										+"</select>";					
					$("<tr id='edit_tr_"+i+"' class='addmo' bgcolor='#CBF5DC'>"
						+"<td width='2%'></td>"
						+"<td width='5%' id='edit_coupon_id_"+num+"' cid='"+coupon_id+"' onmouseover=\"sy_edit_button('edit_isactive_','"+num+"')\" onmouseout='sy_edit_button_hide()'><span id='coupon_id_"+num+"'>"+ coupon_id +"</span><br />"+ $("#status_"+coupon_id).html() +"<br /> <select id='edit_isactive_"+num+"'><option value='1'>YES</option><option value='0' "+sel+">NO</option></select></td>"

						//by ike 20111231 +"<td width='8%' onmouseover=\"sy_edit_button('edit_type_','"+num+"')\" onmouseout='sy_edit_button_hide()' align='center'>"+ coupontype +"</td>"
						+ "<td width='8%'><input type=\"hidden\" id=\"edit_type_" + num + "\" value=\"" + type + "\">&nbsp;</td>"
						+ "<td width='8%' onmouseover=\"sy_edit_button('edit_couponcode_','"+num+"')\" onmouseout='sy_edit_button_hide()' align='center'>" + couponType + "<br/><hr style='width:95%;'/><br/>" + promoStr + "<hr style='width:95%;'/><br/><input id='edit_couponcode_"+num+"' type='text' value='"+ couponcode +"' style='width:100px' /></td>"
						+ "<td width='16%' onmouseover=\"sy_edit_button('edit_title_','"+num+"')\" onmouseout='sy_edit_button_hide()'><textarea id='edit_title_"+num+"' style='width:220px' rows='4'>"+ $("#title_"+coupon_id).html() +"</textarea></td>"
						+ "<td width='22%' onmouseover=\"sy_edit_button('edit_desc_','"+num+"')\" onmouseout='sy_edit_button_hide()'><textarea id='edit_desc_"+num+"' style='width:280px' rows='4'>"+ $("#desc_"+coupon_id).html() +"</textarea></td>"						
						+ "<td width='8%'>"+ $("#category_"+coupon_id).html() +"</td>"
						+ "<td width='8%'>"+ $("#stats_"+coupon_id).html() +"</td>"
						+ "<td width='6%' onmouseover=\"sy_edit_button('edit_startdate_','"+num+"')\" onmouseout='sy_edit_button_hide()'><input id='edit_startdate_"+num+"' type='text' value='"+ startdate +"' style='width:90px' class=\"Wdate\" onclick=\"WdatePicker({el:'edit_startdate_"+num+"'})\" /><br><input id='edit_starttime_"+num+"' type='text' value='"+ starttime +"' style='width:90px' /><br>"
						+ "</td>"
						+ "<td width='8%' onmouseover=\"sy_edit_button('edit_expiration_','"+num+"')\" onmouseout='sy_edit_button_hide()'><input id='edit_expiration_"+num+"' type='text' value='"+ expiration +"' style='width:90px;display:none;' class=\"Wdate\" onclick=\"WdatePicker({el:'edit_expiration_"+num+"'})\" /><br><input id='edit_endtime_"+num+"' type='text' value='"+ endtime +"' style='width:90px; display:none;' /><br>"
						+ "</td>"
						+ "<td width='5%'>"+ $("#editor_"+coupon_id).html() +"</td>"
						+ "<td width='5%'>"+ $("#image_"+coupon_id).html() +"</td>"
						//+"<td width='4%'>"+ $("#status_"+coupon_id).html() +"</td>"
						//+"<td width='6%' onmouseover=\"sy_edit_button('edit_isactive_','"+num+"')\" onmouseout='sy_edit_button_hide()'><select id='edit_isactive_"+num+"'><option value='1'>YES</option><option value='0' "+sel+">NO</option></select></td>"
						+"<td width='6%'>"+ $("#merchant_"+coupon_id).html() +"</td>"
						//+"</tr>").insertAfter("#"+insert_id);
						+"</tr>").insertAfter("#tr_"+coupon_id);
					
					insert_id="edit_tr_"+i;
					num++;
					coupon_id_list+= coupon_id+',';
				}
			}
		});
		$("#numM").val(num);
		if(num>0){
			$("#multieidt_button").hide();
			$("#multicancel_button").show();
			$("#multisave_button").show();
			$(":checkbox").css('visibility','hidden');	
		}
		$("#coupon_id_list").val(coupon_id_list);
	}	

	function cancelmultiEdit(){
		$.each($(':checkbox'),function(i,n){
			$(this).css('visibility','visible');
			if($(this).attr('checked')){
				coupon_id=$(this).val();
				$("#tr_"+coupon_id).show();
			}
		});
		$("#sy_edit_button").insertAfter("#coupon_id_list");
		$("tr").remove(".addmo");
		$("#multieidt_button").show();
		$("#multicancel_button").hide();
		$("#multisave_button").hide();
	}
*/

	$().ready(function() {
		$.ajax({type: "POST",
			url: "/editor/search.php?site="+escape($("#site").val())+"&ajaxTag=getGrade&q="+encodeURIComponent($("#merchant_list_search").val()),			
			success: function(msg){
				$("#merchantGrade").text("Grade:"+msg);
			}					   
		});	
	});
	function showtop(scwEle,xx){
		var id=xx;		
		//alert("offsettop"+scwEle.offsetTop+"------"+id+"----------scrollTop"+scwEle.scrollTop);
		alert("offsettop"+$("#"+id).offset().top+"postop"+$("#"+id).position().top+"scrollTop"+$("#"+id).scrollTop());
	}

	function docalendar(a, id, addfun){
		//alert(id);
		//alert($("#"+id).offset().top);
		addfun=addfun;
		calendar(a, a, $("#"+id).offset().top, addfun);
	}

	function checkBackgroundColor(){
		$(':checkbox').click(function(){
			$.each($(':checkbox'),function(i,n){
				coupon_id=$(this).val();			
				if($(this).attr('checked')){				
					$("#tr_"+coupon_id).css('background-color','#CBF5DC');
				}else{
					$("#tr_"+coupon_id).css('background-color','#ffffff');
				}
			});
		});	
	}

	function filterPromotionDetail(couponid){
		var pro_detail=$("#edit_pro_detail_" + couponid).val();
		if(pro_detail == "percent"){
			$("#edit_pro_off_" + couponid).show();
			$("#edit_money_type_" + couponid).hide();
		}else if(pro_detail == "money"){		
			$("#edit_pro_off_" + couponid).show();
			$("#edit_money_type_" + couponid).show();
		}else{
			$("#edit_pro_off_" + couponid).hide();
			$("#edit_money_type_" + couponid).hide();
		}
	}
	
	function promoHtml(pro_off, money_type, pro_detail, coupon_id){
		var moneystyle = "none;";
		var proffstyle = "none;";
		switch(pro_detail){
			case "percent":
				proffstyle = "";
				break;
			case "money":
				proffstyle = "";
				moneystyle = "";			
				break;
			default:
				break;
		}
		var resStr = '<div style="text-align:left"><select id="edit_pro_detail_' + coupon_id + '" onchange="javascript:filterPromotionDetail(\'' + coupon_id + '\')">';
		for(var id in arr_pro){
			if(id == 'bogo'){
				continue;
			}
			if(id == pro_detail){
				resStr = resStr + '<option value="' + id + '" selected>' + arr_pro[id] + '</option>';
			}else{
				resStr = resStr + '<option value="' + id + '">' + arr_pro[id] + '</option>';
			}
		}
		resStr = resStr + "</select><br/>";
		resStr = resStr + '<select id="edit_money_type_' + coupon_id + '" style="display:' + moneystyle + '">';
		for(var idm in arr_money){
			if(idm == money_type){
				resStr = resStr + '<option value="' + idm + '" selected>' + arr_money[idm] + '</option>';
			}else{
				resStr = resStr + '<option value="' + idm + '">' + arr_money[idm] + '</option>';
			}
		}
		resStr = resStr + "</select><br/>";
		resStr = resStr + '<input id="edit_pro_off_' + coupon_id + '" type="text" maxlength="10" style="width: 50px;display:' + proffstyle + '" value="' + pro_off + '" name="pro_off"><br/></div><br/>';
		return resStr;
	}
	function couponTypeHtml(type, coupon_id){
		var resStr = '<div style="text-align:left"><select id="edit_coupontype_' + coupon_id + '">';
		for(var id in couponTypeArr){
			if(id == type){
				resStr = resStr + '<option value="' + id + '" selected>' + couponTypeArr[id] + '</option>';
			}else{
				resStr = resStr + '<option value="' + id + '">' + couponTypeArr[id] + '</option>';
			}
		}
		resStr = resStr + "</select><br/>";
		return resStr;
	}

	
	function sy_edit_button(element,num){
		var temp_val=$("#"+element+num).val();
		//$("#sy_edit_button").attr("ell",element);
		//$("#sy_edit_button").attr("ellnum",num);
		//$("#sy_edit_button").attr("ellval",temp_val);
		$("#ell").val(element);
		$("#ellnum").val(num);
		$("#ellval").val(temp_val);
		
		if(element=='edit_startdate_'){
			//$("#sy_edit_button").attr("ellval_stime",$("#edit_starttime_"+num).val());
			$("#ellval_stime").val($("#edit_starttime_"+num).val());
		}
		if(element=='edit_expiration_'){
			//$("#sy_edit_button").attr("ellval_etime",$("#edit_endtime_"+num).val());
			$("#ellval_etime").val($("#edit_endtime_"+num).val());
		}
		
		var notIe = -[1,]; 
		if(-[1,]){//if not ie
			$("#sy_edit_button").css("left",$("#"+element+num).offset().left+$("#"+element+num).width());
			$("#sy_edit_button").css("top",$("#"+element+num).offset().top);
		}
		$("#sy_edit_button").insertAfter("#"+element+num);
		$("#sy_edit_button").show();
	}

	function sy_edit_button_hide(){	
		$("#sy_edit_button").hide();		
	}	
	
	function editvalue(){			
		//var numM=$("#sy_edit_button").attr("numM");	
		//var val=$("#sy_edit_button").attr("ellval");
		//var ell=ell=$("#sy_edit_button").attr("ell");
		var numM=$("#numM").val();	
		var val=$("#ellval").val();	
		var ell=$("#ell").val();		
		for(var i=0; i<numM; i++){			
			$("#"+ell+i).val(val);			
			if(ell=='edit_startdate_'){
				//$("#edit_starttime_"+i).val($("#sy_edit_button").attr("ellval_stime"));
				$("#edit_starttime_"+i).val($("#ellval_stime").val());
			}
			if(ell=='edit_expiration_'){
				$("#edit_endtime_"+i).val($("#ellval_etime").val());
			}
		}
		
	}

	function onmouseover_tr(id){		
		if(!$('#coupon_'+id).attr('checked')){
			$('#tr_'+id).css('background-color','#FBF0E3');
		}		
	}

	function onmouseout_tr(id){		
		if(!$('#coupon_'+id).attr('checked')){
			$('#tr_'+id).css('background-color','#FFFFFF');
		}		
	}
	
	function checkIsValidTime(dateTimeStr)
	{
	   //var pattern = /^(\d{2}):(\d{2}):(\d{2})$/;
	  // var pattern = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/;
	   var pattern = /^(20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/;
	   if(!pattern.test(dateTimeStr)) return false;
	   return true;
	}

	function checkFloat(str){
		var pattern = /^[\d]+(\.[\d]{1,2})?$/;
		if(!pattern.test(str)) return false;
		return true;
	}

	function calendarsubmit(){
		$("form:first").submit();
	}

	function calendartimeset(id,n){
		$("#"+id).val(n);
	}

	function showTags(id){
		//hideTags();
		$("#tags_"+id).toggle("normal");
	}

	function hideTags(){
		$("span[id^='tags_']").slideUp("normal");
	}
	
	function openWin(url){
		window.open( url, 'newwindow', 'height=550, width=1000, top=100, left=200, toolbar=no,menubar=no, scrollbars=no, resizable=no,location=n o, status=no') ;
		}
	function openWinUnrecommend(url){
		if(!confirm("Sure cancel recommend?")){
			return false;
		}else{
			window.open( url, 'newwindow', 'height=550, width=1000, top=100, left=200, toolbar=no,menubar=no, scrollbars=no, resizable=no,location=n o, status=no') ;
		}
	}
	
	function resetMerchant(){
		$("#merchant").val(0);
		$("form:first").submit();
	}

	function reportIssue(merchantid){
		
		var site = $("#site").val();
		task_site = "http://task.megainformationtech.com";
		url = task_site + '/front/merchant_issue_list.php?act=addnew&site=' + site + '&mid=' + merchantid;
		window.open( url, 'newwindow', 'height=650, width=800, top=100, left=200, toolbar=no,menubar=no, scrollbars=yes, resizable=no,location=n o, status=no') ;
	}
	function merchantSearchChange(e){
		if(e.value == ""){
			$("#merchant").val("");
		}
	}

	function moreFilter(){
		$(".filter_more").show();
		$("#more_filter").hide();
		$("#showmorefilter").val("YES");
		$("#lessHref").show();
	}
	function hideFilter(){
		$(".filter_more").hide();
		$("#more_filter").show();
		$("#showmorefilter").val("NO");
	}
	
	function hideFilter(){
		$(".filter_more").hide();
		$("#more_filter").show();
		$("#showmorefilter").val("NO");
	}
	
	function setTagExclusive(id){
		var status = "NO";
		if($("#tagexclusive_"+id).attr("checked") == true){
			status = "YES";
		}
		$.ajax({type: "get",
			url: "/editor/promo.php?site="+escape($("#site").val())+"&action=settagexclusive&id="+id+"&status="+status,			
			success: function(msg){
				if(msg == "YES"){
					$("#tagexclusive_"+id).attr("checked", true);
				}else{
					$("#tagexclusive_"+id).attr("checked", false);
				}
			}			
		});	
	
	}