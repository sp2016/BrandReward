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
	$('#dialog-password').modal();
}

function edit_profile_site(){
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:$('#form_edit_profile_site').serialize(),
		async:false,
		success: function(res){
			var resobj = jQuery.parseJSON(res);
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
			$('#dialog-site').modal('hide');
		}
	});
}

function change_pwd(){
	var flag = 0;
	$.ajax({
		type:"post",
		url:CON_BASE_URL+"/process.php",
		data:$('#dialog-password form').serialize(),
		async:false,
		success: function(res){
			var err_1 = 0;
			var err_2 = 0;
			var err_3 = 0;
			if(res == '1'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'Password is wrong');
			}else if(res == '2'){
				err_2 = 1;
				set_msg('pub_pwd',0,'The length of the password not less than 8');
			}else if(res == '3'){
				err_3 = 1;
				set_msg('pub_pwd_ag',0,'password input is wrong');
			}else if(res == '4'){
				err_1 = 1;
				set_msg('pub_pwd_old',0,'system error');
			}else if(res == '0'){
				$('#pub_pwd_old').val('');
				$('#pub_pwd').val('');
				$('#pub_pwd_ag').val('');
				$('#dialog-password').modal('hide');
			}else{
				err_1 = 1;
				set_msg('pub_pwd_old',0,'system error');
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