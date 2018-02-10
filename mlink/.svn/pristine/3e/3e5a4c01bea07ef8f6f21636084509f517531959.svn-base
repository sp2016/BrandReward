<?php 
include_once('../../../../conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
$objAccount = new Account;
	if(!$objAccount->get_login_user()){
		$isLogin = 0;
	}else {
	    $isLogin = 1;
	    $uid = $USERINFO['ID'];
	    $objTran = new Transaction;
	    $sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find();
	    $apiKey = isset($sites[0]['ApiKey'])?$sites[0]['ApiKey']:'';
	    
	    /* $sitetypeResult = $objTran->table('publisher_detail')->where('PublisherId = '.intval($uid))->field('sitetype')->findOne();
	    $sitearr = explode('+',$sitetypeResult['sitetype']);
	    $siteType = 'content';
	    foreach($sitearr as $k){
	        if($k == '1_e' || $k == '2_e'){
	            $siteType = 'coupon';
	        }
	    } */
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Bookmarklet content</title>

    <link rel="stylesheet" href="../../../../css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="../css/br-buttons.css" type="text/css" />
    <link rel="stylesheet" href="../css/br-base.css" type="text/css"/>
    <link rel="stylesheet" href="../css/publisher-bookmarklet.css" type="text/css" />
    
    <script type="text/javascript" src="../../../../js/jquery.min.js" ></script>
    <script type="text/javascript" src="../../../../js/clipboard.min.js" ></script>
    <script type="text/javascript" src="../js/require.js" ></script>
<!--     <script type="text/javascript" src="../../../js/bootstrap.min.js" ></script> -->
    
    <script type="text/javascript">
        require(['../js/deepLinkWidget'], function (DeepLinkWidget) {
        	DeepLinkWidget({}).start(location.href);
        });

    </script>
</head>
<body class="br-account-manager main">
<div id="output" class="clearfix">
<?php if($isLogin):?>
    <div style="float:right" id="logout-btn"><span style="cursor: pointer;display: inline-block;color:green">logout</span></div>
    <div style="text-align:center;"><h1><?=$LANG[$language]['backend']['createlink']['a1'] ?></h1></div>
    <div class="row" style="padding:20px 0;">

      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading"><?=$LANG[$language]['backend']['createlink']['a1'] ?></div>
          <div class="panel-body">
              <div class="form-group">
                <label><?=$LANG[$language]['backend']['createlink']['a2'] ?>:</label>
                <select class="form-control" id="f_site">
                  <?php foreach ($sites as $site):?>
                    <option value="<?=$site['ApiKey'] ?>"><?=$site['Domain'] ?></option>
                  <?php endforeach;?>
                </select>
              </div>
              <div class="form-group html">
                <label><?=$LANG[$language]['backend']['createlink']['a4'] ?>:</label>
                <p id="f_afflink" class="bg-success" style="padding:15px;word-break: break-all;"></p>
                <div class="form-group copy"><input style="border: 1px solid #333;" type="button" data-clipboard-action="copy" data-clipboard-target="#f_afflink" class="btn  b-primary copydata"  name="build"  value="Copy Url"/></div>
              </div>
              <p id="domain-result" style="color:red;"></p>
          </div>
        </div>
      </div>
    </div>
<?php else :?>
<div class="login-box">
    <h3>Client Login</h3>
    <div class="error-message"></div>
    <form id="login-form" class="login-form">
        <div class="inputs">
            <input type="hidden" name="act" value='publish_login' />
            <input id="username" class="input-medium" name="pub_account" placeholder='Login ID' type="text">
            <input id="password" class="input-medium" name="pub_pwd" placeholder='Password' type="password">
        </div>
        <div class="buttons">
            <button id="login-btn" class="btn-d primary-btn" type="button">submit</button>
        </div>
        <div class="clear"></div>
        <div id="has-error" style="background: none;color:red"></div>
    </form>
    <div class="links">
        <div class="forgot-password"><a href="<?=constant('BASE_URL') ?>" target="_blank">Forgot Password</a></div>
        <div class="signup"><a href="<?=constant('BASE_URL') ?>/signup.php" target="_blank">Sign Up</a></div>
    </div>
</div>
<?php endif;?>
</div>
</body>
</html>
<script>
$(function(){
	<?php if($isLogin):?>
		var localhref = window.location.href;
		var indexToStartOfParameters = localhref.indexOf('?url=');
		
    	if (indexToStartOfParameters >= 0) {
			var nowUrl = localhref.substr(indexToStartOfParameters + 5);
// 			$.ajax({
// 		        type:"post",
// 		        url:"../../../../b_tools_createlink.php",
//		        data:{url:decodeURIComponent(nowUrl),siteType:'<?php //$siteType ?>'},
// 		        success: function(res){
// 		            if(res==1){
//		           	 $("#domain-result").html('<span style="color:green">"'+decodeURIComponent(nowUrl)+'"<?php //$LANG[$language]['backend']['createlink']['a10'] ?></span>');
// 		            }else{
//		           	 $("#domain-result").html('<span style="color:red">"'+decodeURIComponent(nowUrl)+'"<?php //$LANG[$language]['backend']['createlink']['a11'] ?></span>');
// 		            }
// 		        }
// 		    });
			$("#f_afflink").html('<?=constant("GO_URL") ?>'+'?key='+'<?=$apiKey ?>'+'&url='+nowUrl);
		}

	    $(document).delegate("#f_site","change",function(){
	    	$("#f_afflink").html('<?=constant("GO_URL") ?>'+'?key='+$("#f_site").val()+'&url='+nowUrl);
		});
		
		var clipboard = new Clipboard('.copydata');
        clipboard.on('success', function(e) {
          alert('Success');
        });
        clipboard.on('error', function(e) {
          alert('Error');
        });

	<?php endif;?>

	$(document).delegate("#login-btn","click",function(){
		$.ajax({
            type:"post",
            url:"../../../../process.php",
            data:$('#login-form').serialize(),
            async:false,
            success: function(data){
            	if(data == '0'){
            		window.location.reload();
            	}else if(data == 'network' || data == 'advertiser' || data == 'advertiser_2'){
            		$("#has-error").html('This account does not have permission');
            		//退出操作
            		$.ajax({
            	        type:"post",
            	        url:"../../../../process.php",
            	        data:'act=publish_logout',
            	        async:false,
            	        success: function(data){
            	        }
                    });
            	}else{
            	    $("#has-error").html('<?=$LANG[$language]['front']['login']['a12'] ?>');
            	}
            }
        });
	});

	$(document).delegate("#logout-btn","click",function(){
		$.ajax({
	        type:"post",
	        url:"../../../../process.php",
	        data:'act=publish_logout',
	        async:false,
	        success: function(data){
	          window.location.reload();
	        }
        });
	});

	$('#username,#password').on('keydown',function(){
		  $("#has-error").html('');
		  if (event.keyCode == "13"){
		      $("#login-btn").trigger('click');
		  }
	});
	
});

</script>