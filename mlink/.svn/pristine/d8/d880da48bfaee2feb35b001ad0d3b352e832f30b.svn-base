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

function show_profile_site(site_id,method){
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:"act=show_profile_site&site_id="+site_id+"&type="+method,
		async:false,
		success: function(htmltxt){
			$('#dialog-site .modal-body').html(htmltxt);
			$('#dialog-site').modal();
		}
	});
}

function show_change_pwd(){
	$('#dialog-pwd').modal();
}

function edit_profile_site(){
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:$('#form_edit_profile_site').serialize(),
		async:false,
		success: function(res){
			window.location.href = CON_BASE_URL+"/b_account.php";

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
}

function change_pwd(){
	var flag = 0;
	var pub_pwd_old = $('#pub_pwd_old').val();
	var pub_pwd = $('#pub_pwd').val();
	var pub_pwd_ag = $('#pub_pwd_ag').val();
	var act = 'publish_change_pwd';
	var data = {'pub_pwd_old':pub_pwd_old,'pub_pwd':pub_pwd,'pub_pwd_ag':pub_pwd_ag,'act':act}
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:data,
		async:false,
		success: function(res){
			//alert(res);
			var err_1 = 0;
			var err_2 = 0;
			var err_3 = 0;
			if(res == '1'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'密码错误');
			}else if(res == '2'){
				err_2 = 1;
				set_msg('pub_pwd',0,'密码长度不得少于8个字符');
			}else if(res == '3'){
				err_3 = 1;
				set_msg('pub_pwd_ag',0,'密码输入有误');
			}else if(res == '4'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'系统出错');
			}else if(res == '0'){
				$('#pub_pwd_old').val('');
				$('#pub_pwd').val('');
				$('#pub_pwd_ag').val('');
				$('#dialog-pwd').modal('hide');
			}else{
				err_1 = 1;
				set_msg('pub_pwd_old',0,'系统出错');
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
	$.ajax({
		type:"post",
		url:"process.php",
		data:$('#con_form').serialize(),
		async:false,
		success: function(res){
			if (res == '0') {
				set_msg('con_email', 0, '邮箱必填');
			}
			if (res == '1') {
				set_msg('con_email', 0, '邮箱格式有误');
			}
			if (res == '2') {
				set_msg('con_message', 0, '发送消息不能为空');
			}
			if(res!=0 && res!=1)
				set_msg('con_email', 1, '');
			if(res!=2)
				set_msg('con_message', 1, '');


			if (res == '3') {
			alert('发送成功');
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
				set_msg('part_email', 0, '邮箱必填');
			}
			if (res == '1') {
				set_msg('part_email', 0, '邮箱格式有误');
			}
			if (res == '2') {
				set_msg('part_message', 0, '发送消息不能为空');
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
	$.ajax({
		type:"post",
		url:"process.php",
		data:$('#re_form').serialize(),
		async:false,
		success: function(res){
			if (res == '0') {
				set_msg('re_email', 0, '邮箱必填');
			}
			if (res == '1') {
				set_msg('re_email', 0, '邮箱格式有误');
			}
			if (res == '2') {
				set_msg('re_email', 0, '系统找不到该邮箱');
			}
			if(res!=0 && res!=1 && res!=2)
				set_msg('re_email', 1, '');


			if(res == 3)
				set_msg('re_pwd', 0, '密码必填');
			if(res == 4)
				set_msg('re_pwd', 0, '密码长度不得少于8个字符');
			if(res!=3 && res!=4)
				set_msg('re_pwd', 1, '');


			if(res == 5)
				set_msg('re_pwd_again', 0, '重新输入新密码');
			if(res!=5)
				set_msg('re_pwd_again', 1, '');

			if (res == '6') {
				alert('success');
			}
		}
	});
}