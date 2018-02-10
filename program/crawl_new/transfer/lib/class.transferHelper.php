<?php
/**
 * User: rzou
 * Date: 2017/10/11
 * Time: 17:34
 */
class transferHelper
{
    var $objMysql,$compareDB;
    var $instances = array();
    var $notCompareField = array('ID', 'AffId', 'AddTime', 'LastUpdateTime', 'Creator', 'SEMPolicyExt', 'Description', 'LogoUrl', 'CreateDate', 'CategoryFirst', 'CategorySecond','TargetCountryIntOld','RankInAff','DetailPage','TargetCountryInt','LogoName');

    function __construct()
    {
        if(!isset($this->objMysql))
            $this->objMysql = new MysqlPdo();
        if(!isset($this->brO1_bdg_go_base))
            $this->brO1_bdg_go_base = new MysqlPdo(BRO1_DB_NAME, BRO1_DB_HOST, BRO1_DB_USER, BRO1_DB_PASS);
        if(!isset($this->brO3_bdg_go_base_test))
            $this->brO3_bdg_go_base_test = new MysqlPdo(BDG_GO_BASE_TEST_DB_NAME, BDG_GO_BASE_TEST_DB_HOST, BDG_GO_BASE_TEST_DB_USER, BDG_GO_BASE_TEST_DB_PASS);
    }

    function getAffNamesById($reqAffid, $reqAccid, $reqSite)
    {
        if (!$reqAffid && !$reqAccid && !$reqSite) {
            mydie("die: getAffNamesById failed, Please pass in any one ID\n");
        }

        $where = 'WHERE 1=1';
        if ($reqAffid) {
            $where .= sprintf(" AND AffID='%s'", $reqAffid);
        }
        if ($reqAccid) {
            $where .= sprintf(" AND AccountID='%s'", $reqAccid);
        }
        if ($reqSite) {
            $where .= sprintf(" AND SiteID='%s'", $reqSite);
        }

        $sql = sprintf('select Name from affiliate_account_site %s', $where);
        $arr = $this->objMysql->getRows($sql,"Name");
        if(empty($arr)) mydie("die: getAffNamesById failed," . str_replace('WHERE 1=1 AND','', $where) . " not found\n");
        return $arr;
    }

    function getAffAccountSiteByName($affSiteAccName)
    {
        $sql = "select * from affiliate_account_site where Name = '$affSiteAccName'";
        $arr = $this->objMysql->getFirstRow($sql);
        if(empty($arr)) mydie("die: getAffAccountSiteByName failed, Name = '$affSiteAccName' not found\n");
        return $arr;
    }

    function getInstance($aff_id,$site_name)
    {
        $class_name = 'LinkFeed_' . $site_name;
        $class_file = $this->getClassFilePath($class_name);
        if (!is_file($class_file)) {
            mydie("get class file of $class_name failed");
        }
        include_once($class_file);
        $obj = new $class_name($site_name, $this);
        if (!is_object($obj)) {
            mydie("get Instance of $class_name failed");
        }
        $this->instances[$aff_id] = $obj;
        return $obj;
    }

    function getClassFilePath($class_name)
    {
        $class_file = "class." . $class_name . ".php";
        return INCLUDE_ROOT . "lib/LinkFeed/" . $class_file;
    }

    /******************************************** 数据更新函数块 *******************************************/
    function updateProgram($affId, $arr_info)
    {
        $arr_update = array();
        $idInAff = array_keys($arr_info);
        $sql = "SELECT IdInAff FROM program_tmp_test WHERE AffId = '$affId' AND IdInAff IN ('".implode("','",$idInAff)."')";
        $return_arr = $this->objMysql->getRows($sql,"IdInAff");
        foreach($return_arr as $k => $v)
        {
            if(isset($arr_info[$k]) === true)
            {
                $arr_update[$k] = $arr_info[$k];
                unset($arr_info[$k]);
            }
        }
        unset($return_arr);
        if(count($arr_info)){
            $this->doInsertProgram($arr_info);
        }
        if(count($arr_update)){
            $this->doUpdateProgram($arr_update);
        }

        return true;
    }

    function doInsertProgram($arr)
    {
        $field_list = array();
        $value_list = array();
        foreach($arr as $k => $v){
            if(!count($field_list)){
                $field_list = array_keys($v);
                $field_list[] = "Creator";
                $field_list[] = "AddTime";
            }
            if (isset($v['Name']))
                $v['Name'] = html_entity_decode($v['Name']);
            $v["Creator"] = "System";
            $v["AddTime"] = date("Y-m-d H:i:s");
            $value_list[] = "('".implode("','", array_values($v))."')";
        }
        $sql = "INSERT IGNORE INTO program_tmp_test(".implode(",",$field_list).") VALUES ".implode(",",$value_list);
        try
        {
            $this->objMysql->query($sql);
        }
        catch (Exception $e)
        {
            echo $e->getMessage()."\n";
        }
    }

    function doUpdateProgram($arr)
    {
        $field_list = array();
        $value_list = array();
        foreach($arr as $k => $v){
            if(!count($field_list)){
                $field_list = array_keys($v);
            }
            if (isset($v['Name']))
                $v['Name'] = html_entity_decode($v['Name']);

            $value_list[] = "('".implode("','", array_values($v))."')";
        }
        $sql = "REPLACE INTO program_tmp_test(".implode(",",$field_list).") VALUES ".implode(",",$value_list);
        try
        {
            $this->objMysql->query($sql);
        }
        catch (Exception $e)
        {
            echo $e->getMessage()."\n";
        }
    }

    function getNotUpdateProgram($AffId, $check_date){
        $prgm_arr = array();
        $sql = "SELECT ID, IdInAff, AffId FROM program_tmp_test WHERE AffId ='$AffId' AND (LastUpdateTime < '{$check_date}' OR ISNULL(LastUpdateTime)) AND StatusInAff = 'Active' AND Partnership = 'Active'";
        $prgm_arr = $this->objMysql->getRows($sql);

        return $prgm_arr;
    }
    function setProgramOffline($AffId, $prgm_arr = array()){

        foreach($prgm_arr as $v){
            $v["StatusInAff"] = 'Offline';
            foreach($v as &$vv){
                $vv = addslashes($vv);
            }
            $sql = "UPDATE program_tmp_test SET StatusInAff = 'Offline', StatusInAffRemark = 'Can not find program in aff', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ID = {$v['ID']}";
            $this->objMysql->query($sql);
        }
    }

    /******************************************************************************************************/

    function transferDataToDb($aff_id, $site_name, $oldSystemAffId)
    {
        $obj = $this->getInstance($aff_id, $site_name);
        $obj->transferDataToDb();
        $this->compareDataWithTrueData($aff_id, $oldSystemAffId);
    }

    function compareDataWithTrueData($oldSystemAffId, $compareField = '*')
    {
        echo "\tThe compare start !\r\n";

        if (!$oldSystemAffId || empty($compareField)) {
            mydie("\r\nParameter error!");
        }

        $fields = '';
        if (is_string($compareField)) {
            $fields = explode(',', $compareField);
            foreach ($fields as $key => &$val) {
                $val = trim($val);
                if (!$val) {
                    unset($fields[$key]);
                }
            }
        }

        $sql = sprintf('SELECT * FROM program WHERE AffId="%s" LIMIT 1', $oldSystemAffId);
        $fields_true = $this->brO3_bdg_go_base_test->getFirstRow($sql);
        $fields_true = array_keys($fields_true);

        foreach ($fields as $val) {
            if ($val != '*') {
                if (!in_array($val, $fields_true)) {
                    mydie("There find wrong field:$val !");
                }
            }
        }

        if (!in_array('*', $fields) && !in_array('IdInAff', $fields)) {
            $fields[] = 'IdInAff';
        }


        $totle_sql = "SELECT COUNT(1) FROM program WHERE AffId='$oldSystemAffId'";
        $cnt = $this->brO3_bdg_go_base_test->getFirstRowColumn($totle_sql);
        $pre_sql = 'SELECT ' . join(',',$fields);

        $diff_data_arr = array();
        $new_data_IdInff_List = array();
        $onlyInNewData = array();
        $compareNum = 0;

        $page = 1;
        $pageSize = 50;
        $hasNextPage = true;
        while ($hasNextPage) {
            echo "page:$page\t";
            $start_index = ($page - 1) * $pageSize;
            if ($start_index > $cnt) {
                $hasNextPage = false;
                break;
            }
            $sql = "$pre_sql FROM program WHERE AffId='$oldSystemAffId' limit $start_index,$pageSize";
            $newData = $this->brO3_bdg_go_base_test->getRows($sql, 'IdInAff');
            if (!$newData) {
                break;
            }

            $new_IdInAff_List = array_keys($newData);
            $sql = "$pre_sql FROM program WHERE AffId=$oldSystemAffId AND IdInAff IN ('" . join("','",$new_IdInAff_List) . "')";
            try{
                $oldData = $this->brO1_bdg_go_base->getRows($sql, 'IdInAff');
            }catch (PDOException $e) {
                die("Get data from br01 bdg_go_base field! error:" . $e->getMessage());
            }

            $old_IdInAff_List = array_keys($oldData);
            $onlyInNew = array_diff($new_IdInAff_List, $old_IdInAff_List);

            foreach ($new_IdInAff_List as $val) {
                array_push($new_data_IdInff_List, $val);
            }
            if (!empty($onlyInNew)){
                foreach ($onlyInNew as $v) {
                    array_push($onlyInNewData, $v);
                }
            }

            $commonKey = array_intersect($new_IdInAff_List, $old_IdInAff_List);
            $compareNum += count($commonKey);
            foreach ($commonKey as $val) {
                foreach ($newData[$val] as $k => $v) {
                    if (in_array($k, $this->notCompareField)) {
                        continue;
                    }
                    if (strcmp(trim($v),trim($oldData[$val][$k]))) {
                        $diff_data_arr[$k][] = $val;
                    }
                }
            }
            $page ++;
        }

        $check_time = date("Y-m-d H:i:s",strtotime('-2 days'));

        $sql = "SELECT IdInAff,StatusInAff,Partnership FROM program WHERE AffId=$oldSystemAffId AND LastUpdateLinkTime> '$check_time' AND IdInAff NOT IN ('" . join("','", $new_data_IdInff_List) . "')";
        try {
            $onlyInOldData = $this->brO1_bdg_go_base->getRows($sql, 'IdInAff');
        }catch (PDOException $e) {
            die("Get data from br01 bdg_go_base field! error:" . $e->getMessage());
        }
        $this->brO1_bdg_go_base->close();

        $print_msg = "\t";
        foreach ($diff_data_arr as $key=>$val) {
            $print_msg .= $key.'('. count($val).'), ';
        }

        $diff_data_arr['OnlyInNewData'] = $onlyInNewData;
        $diff_data_arr['OnlyInOldData'] = $onlyInOldData;

        echo "\tThe compare result is:\r\n";

        $show_arr = array();
        foreach ($diff_data_arr as $kk => $vv) {
            if (!empty($vv)) {
                $tmp_arr = array();
                foreach ($vv as $val) {
                    $tmp_arr[] = $val;
                }
                $show_arr[$kk] = "'" . join("','", $tmp_arr) . "'";
            }
        }

        print_r($show_arr);

        echo "This compare find ".count($onlyInNewData).' program only in new crawl data,'.count($onlyInOldData) . " program only in old crawl data, and compare $compareNum common program find wrong fields and count like that: \n" . $print_msg;

        echo "\r\n\tThe compare end !\r\n";
    }

}