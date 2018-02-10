<?php
	if(isset($_FILES) && !empty($_FILES)){
		$fname = time().'_'.$_FILES['files']['name'][0];
		$res = move_uploaded_file($_FILES['files']['tmp_name'][0],'/app/site/ezconnexion.com/web/img/adv_logo/'.$fname);
		echo $fname;
	}else{
		echo 0;
	}
?>