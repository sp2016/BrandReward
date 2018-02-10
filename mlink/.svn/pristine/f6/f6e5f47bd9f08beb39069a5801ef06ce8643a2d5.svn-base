<?php
	if(isset($_FILES) && !empty($_FILES)){
	    if(!is_dir('img/adv_logo/')){
	        mkdir('img/adv_logo/',0777);
	    }
		move_uploaded_file($_FILES['files']['tmp_name'][0],'img/adv_logo/'.$_FILES['files']['name'][0]);
		echo $_FILES['files']['name'][0];
	}else{
		echo 0;
	}
?>