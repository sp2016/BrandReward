<?php
/**
 * User: rzou
 * Date: 2017/10/9
 * Time: 14:18
 */
class ProgramApiDb extends MysqlPdo
{
    public $affId;
    public $accountId;
    public $siteId;
    public $affPreNameInDB;
    public $siteName;

    public function getSiteInfoBySiteName($siteName)
    {
        $return_arr = array('code' => 0, 'error_msg' => '');

        $this->siteName = addslashes($siteName);
        if (!$this->siteName) {
            $return_arr['error_msg'] = 'The site_name can not be empty !';
            return $return_arr;
        }

        $sql = "SELECT aff.AffID,aff.Name,acc.AccountID,s.SiteID 
                FROM affiliate_account_site s INNER JOIN affiliate aff INNER JOIN affiliate_account acc
                ON s.AffID=aff.AffID AND s.AccountID=acc.AccountID
                WHERE s.Name='{$this->siteName}' AND s.Status='Active' AND aff.Status='Active' AND acc.Status='Active' LIMIT 1";
        $result = $this->getFirstRow($sql);
        if (!$result) {
            $return_arr['error_msg'] = 'The site_name provided does not exist !';
            return $return_arr;
        }

        $db_name = trim($result['Name']);
        if (($pos = strpos($db_name, "(")) !== false) $db_name = trim(substr($db_name, 0, $pos));
        $db_name = str_replace(array(" ", ".", "-"), "_", $db_name);
        $db_name = strtolower($db_name);
        if (!$db_name) {
            $return_arr['error_msg'] = 'The site_name provided does not exist !';
            return $return_arr;
        }

        $this->affId = $result['AffID'];
        $this->accountId = $result['AccountID'];
        $this->siteId = $result['SiteID'];
        $this->affPreNameInDB = $db_name;

        $return_arr['code'] = 1;
        return $return_arr;
    }

    public function getAllProgramInfo($page, $pageSize)
    {
        $return_arr = array('code' => 0);

        $sql = sprintf("SELECT COUNT(1) FROM program WHERE AffID = '%s'", $this->affId);
        $cnt = intval($this->getFirstRowColumn($sql));
        if (!$cnt) {
            return array('code'=>1, 'total'=>0, 'data'=>array());
        }
        $return_arr['total'] = $cnt;

        if($page < 1) {
            $page = 1;
        } else {
            $page = intval($page);
        }
        if($pageSize < 1 || $pageSize > PAGESIZE) {
            $pageSize = PAGESIZE;
        } else {
            $pageSize = intval($pageSize);
        }
        $startIndex = ($page - 1) * $pageSize;

        if($startIndex > $cnt) {
            $return_arr['error_msg'] = 'please input correct page number and page size!';
            return $return_arr;
        }

        //根据关联关系生成返回字段的字段名。
        $sql_fields = 'p.IdInAff as ProgramId,';
        $getFieldsSql = sprintf("SELECT DISTINCT Name,PrototypeName FROM program_prototype_rel WHERE AffID='%s' AND PrototypeName IS NOT NULL", $this->affId);
        $fields = $this->getRows($getFieldsSql);
        foreach ($fields as $val) {
            if ($val['Name'] == 'Partnership') {
                $sql_fields .= "affrsp.{$val['Name']} as {$val['PrototypeName']},";
            } else {
                $sql_fields .= "affp.{$val['Name']} as {$val['PrototypeName']},";
            }
        }
        $sql_fields = rtrim($sql_fields, ',');

        $sql = "SELECT $sql_fields 
                FROM program p INNER JOIN {$this->affPreNameInDB}_program affp INNER JOIN {$this->affPreNameInDB}_r_site_program affrsp
                ON p.ProgramID=affp.ProgramID AND p.ProgramID=affrsp.ProgramID
                WHERE p.AffID='{$this->affId}' AND affrsp.SiteID='{$this->siteId}' LIMIT {$startIndex},{$pageSize}";

        try {
            $data = $this->getRows($sql, 'ProgramId');
        }catch (PDOException $e) {
            $return_arr['error_msg'] = $e->getMessage();
            return $return_arr;
        }
        $return_arr['data'] = $data;

        $return_arr['code'] = 1;

        return $return_arr;
    }

}

?>