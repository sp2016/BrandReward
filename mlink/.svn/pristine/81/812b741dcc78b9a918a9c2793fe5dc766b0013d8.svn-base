function editSubscription(sid){
	$("#div_editSubscription_" + sid).show();
	$("#link_editSubscription_" + sid).hide();
}
function cancelEditSubscription(sid){
	$("#div_editSubscription_" + sid).hide();
	$("#link_editSubscription_" + sid).show();
}

function saveEditSubscription(sid,site){
	var oldEmail = $("#span_editSubscription_" + sid).text();	
	var newEmail = $("#edit_Subscription_" + sid).val();	

	var emailPattern = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
	if(newEmail.length < 0 || !emailPattern.test(newEmail)){
		alert("Please input the right email.");
		return false;
	}
	if(newEmail == oldEmail){
		alert("The new email was same as the old one, please select a different one.");
		return false;
	}

	$.ajax({
		type: "post",
		async: true,
		url: "/editor/subscription_list.php?action=updateEmail&sid=" + sid + "&site=" + site + "&value=" + newEmail,
		success: function (msg) {
			if(msg == "success"){
				alert("Email Saved!");
				$("#div_editSubscription_" + sid).hide();
				$("#link_editSubscription_" + sid).show();
				location.reload();
			}else{
				alert(msg);
			}
		},
		error: function (){
			alert("Operate Error!");
		}
	});	
}

function activeSubscription(sid,site){	
	var answer = confirm("Continue To Active This Subscription?");
	if (answer){
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/subscription_list.php?action=activeSubscription&sid=" + sid + "&site=" + site,
			success: function (msg) {
				if(msg == "success"){
					alert("Active This Subscription Successfully!");
					location.reload();
				}else{
					alert(msg);
				}
			},
			error: function (){
				alert("Operate Error!");
			}
		});	
	}
}

function activeSubscriptions(sid,site){
	var answer = confirm("Continue To Active All Subscriptions Under This Email?");
	if (answer){
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/subscription_list.php?action=activeSubscriptions&sid=" + sid + "&site=" + site,
			success: function (msg) {
				if(msg == "success"){
					alert("Active All Subscriptions Under This Email Successfully!");
					location.reload();
				}else{
					alert(msg);
				}
			},
			error: function (){
				alert("Operate Error!");
			}
		});	
	}
}

function unSubscribe(sid,site){
	var answer = confirm("Continue To Un-Subscribe This Subscription?");
	if (answer){
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/subscription_list.php?action=unSubscribe&sid=" + sid + "&site=" + site,
			success: function (msg) {
				if(msg == "success"){
					alert("Un-Subscribe This Subscription Successfully!");
					location.reload();
				}else{
					alert(msg);
				}
			},
			error: function (){
				alert("Operate Error!");
			}
		});
	}
}

function unSubscribes(sid,site){
	var answer = confirm("Continue To Un-Subscribe All Subscriptions Under This Email?");
	if (answer){
		$.ajax({
			type: "post",
			async: true,
			url: "/editor/subscription_list.php?action=unSubscribes&sid=" + sid + "&site=" + site,
			success: function (msg) {
				if(msg == "success"){
					alert("Un-Subscribe All Subscriptions Under This Email Successfully!");
					location.reload();
				}else{
					alert(msg);
				}
			},
			error: function (){
				alert("Operate Error!");
			}
		});
	}
}