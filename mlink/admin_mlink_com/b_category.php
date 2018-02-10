<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
mysql_query("SET NAMES UTF8");
	$statis = new Statis();
	$category = $statis->getCategory();
	if(isset($_POST['table']) && !empty($_POST['table'])){
		$page = $_POST['start'];
		$pagesize = $_POST['length'];
		$condition  = '';
		if($_POST['cateType'] == 'All')
			$condition .= "1=1";
		else if($_POST['cateType'] == 'notRelated'){
			$condition .= "(a.IdRelated IS NULL OR a.IdRelated = '')";
		}else if($_POST['cateType'] == 'related'){
			$condition .= "(a.IdRelated IS NOT NULL AND a.IdRelated != '')";
		}
		if($_POST['network'] == 'All')
			;
		else if($_POST['network'] == '-1'){
			$condition .= " and (a.Affid IS NULL OR a.IdRelated = '')";
		}else{
			$condition .= " and (a.Affid = '{$_POST['network']}')";
		}

		$salesman_array = array(
			'alain' => '26,395,34,559,208,18,13,14,20,22,36,503,29',
			'senait' => '115,28,533,46,3,2002',
			'nicolas' => '15,64,418,491,63,500,26,539,133,415,667,429,35,469,27,5,425,426,65,427,52,10',
			'lillian' => '6',
			'giulia' => '182,58,59',
			'sarah' => '197,49,2001,196,124,240,163,57,557',
			'vivienne' => '152,360'
		);
		if($_POST['salesman'] == 'All')
		{
			;
		}
		else if($_POST['salesman'] == 'monica')
		{
			$sales = implode(',',$salesman_array);
			$condition .= "and (a.Affid not in ('{$sales}') or a.Affid is null)";
		}
		else
		{
			$condition .= "and a.Affid in ('{$salesman_array[$_POST['salesman']]}')";
		}
		
		$sql = "SELECT a.ID,a.`Name`,a.ManualCtrl,a.IdRelated,b.`Name` as AffName FROM category_ext a left join wf_aff b on a.AffId=b.id WHERE $condition ORDER BY `Name` ASC Limit $page,$pagesize";
		$category_list = $db->getRows($sql);
		
		$sql = "SELECT COUNT(1) FROM category_ext a WHERE $condition ";
		$count = $db->getFirstRowColumn($sql);

		$res['data'] = $category_list;
		$res['start'] = $page/$pagesize+1;
		$res['recordsFiltered'] = $count;
		echo json_encode($res);
		die;
	}
	$objOutlog = new Outlog;
	$networks = $objOutlog->get_affname();
	$networks[] = array('ID'=>-1,'Name'=>'Other');

	$objTpl->assign('category', $category);
	$objTpl->assign('networks', $networks);
	$objTpl->assign('title','category');
	$objTpl->assign('sys_header', $sys_header);
	$objTpl->display('b_category.html');
?>