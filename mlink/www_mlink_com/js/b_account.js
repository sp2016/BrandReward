function show_profile_account(is_form){
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:"act=show_profile_account&is_form="+is_form,
		async:false,
		success: function(htmltxt){
			$('#panel_account .row').html(htmltxt);
			if(is_form>0){
				$('#a_edit_account').css('display','none');
			}else{
				$('#a_edit_account').css('display','');
			}
		}
	});
}

function edit_profile_account(){
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:$('#f_account').serialize(),
		async:false,
		success: function(res){
			show_profile_account(0);
		}
	});
}

function show_profile_site(site_id){
	$(".siteErrorMsg").html("");
	$("#siteId").val(site_id);
	$("#pub_contentDiv select").each(function(){
		$(this).val("");
	})
	$('#pub_contentCategory4').val("");
	$("#pub_trafficDiv select").each(function(){
		$(this).val("");
	})

	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:"act=show_profile_site&site_id="+site_id,
		dataType:"json",
		async:false,
		success: function(htmltxt){
			$("#site-domain").val(htmltxt.Domain);
			$("#site-alias").val(htmltxt.Alias);
//			console.log(htmltxt);
			var siteTypeArr = htmltxt.SiteTypeNew.split('+');
		    for(var i = 0;i<siteTypeArr.length;i++){
		       $('.pub_contentCategory'+i).find("option[value='"+siteTypeArr[i]+"']").prop("selected",true);
		    }
		    var sitetype = siteTypeArr.pop();
		    if($('#pub_contentDiv select').eq(0).val() != sitetype && $('#pub_contentDiv select').eq(1).val() != sitetype && $('#pub_contentDiv select').eq(2).val() != sitetype) {
		    	$('#pub_contentCategory4').val(sitetype);
		    }
		    
		    var geoBreakdownArr = htmltxt.GeoBreakdown.split('+');
		    for(var i = 0;i<geoBreakdownArr.length;i++){
		       $('.pub_traffic'+i).find("option[value='"+geoBreakdownArr[i]+"']").prop("selected",true);
		    }
			
			$("#site-desc").val(htmltxt.Description);
			$('#dialog-site').modal();
		}
	});
}

function show_change_pwd(){
	$('#dialog-pwd').modal();
}

function edit_profile_site(){
	$(".siteErrorMsg").html("");
	//alert($('#checkurl').val());
	//return false;
	var text=$('#site-domain').val();
	var reg = /^https?:\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;
	if(!reg.test(text)){
		$("#domainHasError").html("Please enter the correct format Such as http://www.brandreward.com");
//		$('.siteErrorMsg').html('Please enter the correct format Such as http://www.brandreward.com');
		/*if($('.check label').length <= 1){
			$('#checkurl').after('<label style="margin-top: 10px;"><font color="red">Please enter the correct format Such as http://www.brandreward.com</font></label>')
		}*/
		return false;
	}
	/*else{
		$('#checkurl').next().remove();
	}*/
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:$('#form_edit_profile_site').serialize(),
		dataType:"json",
		async:false,
		success: function(res){
			if(res.code == 1){
				window.location.href = CON_BASE_URL+"/b_account.php";
			}else{
				$("#"+res.errorId).html(res.msg);
			}
	/*		var resobj = jQuery.parseJSON(res);
			if(resobj.res == '1')
				$('#profile-site table').append('<tr><td>'+resobj.data.Domain+'</td><td>'+resobj.data.Alias+'</td><td>'+resobj.data.ApiKey+'</td><td>'+resobj.data.SiteType+'</td><td>'+resobj.data.TargetCountry+'</td><td><a href="javascript:void(0)" id="a_site_'+resobj.data.ID+'" onclick="show_profile_site('+resobj.data.ID+',\'view\')">Edit Details</a></td></tr>');
			else if(resobj.res == '2'){
				var tr = $('#a_site_'+resobj.data.ID).parent().parent();
				$(tr.find('td')[0]).html(resobj.data.Domain);
				$(tr.find('td')[1]).html(resobj.data.Alias);
				$(tr.find('td')[2]).html(resobj.data.ApiKey);
				$(tr.find('td')[3]).html(resobj.data.SiteType);
				$(tr.find('td')[4]).html(resobj.data.TargetCountry);
				$(tr.find('td')[5]).html('<a href="javascript:void(0)" id="a_site_'+resobj.data.ID+'" onclick="show_profile_site('+resobj.data.ID+',\'view\')">Edit Details</a>');
			}
			$('#dialog-site').modal('hide');*/
		}
	});
	return false;
}

function change_pwd(){
	var flag = 0;
	var oldpwd = $('#pub_pwd_old').val();
	var pub_pwd = $('#pub_pwd').val();
	var pub_pwd_ag = $('#pub_pwd_ag').val();
	var act = 'publish_change_pwd';
	var data = {'pub_pwd_old':oldpwd,'pub_pwd':pub_pwd,'pub_pwd_ag':pub_pwd_ag,'act':act}
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:data,
		async:false,
		success: function(res){
			var err_1 = 0;
			var err_2 = 0;
			var err_3 = 0;
			if(res == '1'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'Incorrect password');
			}else if(res == '2'){
				err_2 = 1;
				set_msg('pub_pwd',0,'You need a minimum of 8 characters');
			}else if(res == '3'){
				err_3 = 1;
				set_msg('pub_pwd_ag',0,'Incorrect password');
			}else if(res == '4'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'System error,please try again');
			}else if(res == '0'){
				alert('Success');
				$('#pub_pwd_old').val('');
				$('#pub_pwd').val('');
				$('#pub_pwd_ag').val('');
				$('#dialog-pwd').modal('hide');
			}else{
				err_1 = 1;
				set_msg('pub_pwd_old',0,'System error,please try again');
			}

			if(err_1 == 0)
				set_msg('pub_pwd_old',1);
			if(err_2 == 0)
				set_msg('pub_pwd',1);
			if(err_3 == 0)
				set_msg('pub_pwd_ag',1);
		}
	});
	return false;
}

function set_msg(id,res,msg){
    var fgroup = $('#'+id).parent();
	if(res == 0){
      fgroup.removeClass('has-error has-success');
      fgroup.addClass('has-error');
		if(fgroup.find('label')[0]){
        fgroup.find('label').html(msg);
      }else{
        fgroup.prepend('<label class="control-label">'+msg+'</label>');
      }
    }else{
      fgroup.removeClass('has-error has-success');
      fgroup.addClass('has-success');
       if(fgroup.find('label')[0]){
        fgroup.find('label').remove();
      }
    }
  }

function send_contact_message(){
	var name = $('.con_name').val();
	var emails = $('.con_email').val();
	var sel = $('.con_type').val();
	var mes = $('.con_message').val();
	var data = {'act':'send_contact_message','con_name':name,'con_email':emails,'con_type':sel,'con_message':mes};
	if(!emails.match(/^(\w|-)+(\.\w+)*@(\w|-)+((\.\w+)+)$/)){
		$('.con_email').val('');
		$('.con_email').prev().css('color','red');
		if($('.formemail font').length <= 0){
			$('.con_email').after('<font color="red">Please enter the correct email format such as 123456@qq.com</font>')
			return false;
		}
	}else{
		$('.con_email').prev().removeAttr('style');
		$('.formemail font').remove();
	}
	var errors = 0;
	$.each(data, function(i, item){
		if(item == ''){
			$('.'+i).prev().css('color','red');
			errors = 1;
		}else{
			$('.'+i).prev().removeAttr('style');
		}
	});
	if( errors == 1){
		alert('Something seems to be missing, please review your information.');
		return false;
	}
	$.ajax({
		type:"post",
		url:"process.php",
		data:data,
		async:false,
		success: function(res){
			if (res == '1') {
				alert('success');
				$('.nulls').val('');
				$('.con_type option').eq(0).prop('selected',true);
			}else{
				alert('error');
			}
		}
		});
	}

function send_partnership_message(){
	$.ajax({
		type:"post",
		url:"process.php",
		data:$('#part_form').serialize(),
		async:false,
		success: function(res){
			if (res == '0') {
				set_msg('part_email', 0, 'You must input your email');
			}
			if (res == '1') {
				set_msg('part_email', 0, 'Please input valid email');
			}
			if (res == '2') {
				set_msg('part_message', 0, 'Please fill in your message');
			}
			if(res!=0 && res!=1)
				set_msg('part_email', 1, '');
			if(res!=2)
				set_msg('part_message', 1, '');


			if (res == '3') {
				$('#myModal').modal({
					backdrop:true,
					keyboard:true,
					show:true
				});
			}
		}
	});
}



function remodal_login(){
//$('#bac').css('display','block');
//	$('#login').css('display','block');
	$('#bac').fadeIn('normal');
	$('#login').fadeIn('normal');
};

function hideModal(){
	$('#bac').fadeOut('normal');
	$('#login').fadeOut('normal');
};

function logout(){
	$.ajax({
		type:"post",
		url:"process.php",
		data:'act=publish_logout',
		async:false,
		success: function(data){
			window.location.reload();
		}
	});
}
function forgotPwd(){
	//$('#bac').css('display','none');
	$('#login').css('display','none');
	$('#forgotPwd').slideDown('normal');
	$('#f_x').click(function(){
		$('#bac').fadeOut('display','none');
		$('#forgotPwd').slideUp('fast');
	})
}
function retrieve_password(){
	set_msg('reset_msg', 1, '');
	$.ajax({
		type:"post",
		url:"process.php",
		data:$('#re_form').serialize(),
		dataType:'json',
		async:false,
		success: function(res){
			if(res.code == 1){
				alert(res.msg);
				window.location.href = CON_BASE_URL;
			}else{
				set_msg('reset_msg', 0, res.msg);
			}
	/*		if (res == '0') {
				set_msg('re_email', 0, 'email can not be empty');
			}
			if (res == '1') {
				set_msg('re_email', 0, 'email form error');
			}
			if (res == '2') {
				set_msg('re_email', 0, 'no such email in our system');
			}
			if(res!=0 && res!=1 && res!=2)
				set_msg('re_email', 1, '');
*/

			/*if(res == 3)
				set_msg('re_pwd', 0, 'Please fill in the password');
			if(res == 4)
				set_msg('re_pwd', 0, 'You need a minimum of 8 characters');
			if(res!=3 && res!=4)
				set_msg('re_pwd', 1, '');


			if(res == 5)
				set_msg('re_pwd_again', 0, 'Please confirm new passwords');
			if(res!=5)
				set_msg('re_pwd_again', 1, '');

			if (res == '6') {
				alert('Success');
			}*/
		}
	});
	return false;
}