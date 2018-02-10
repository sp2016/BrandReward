<?php
class Feedback extends LibFactory{
    public $msg = '';

    function setting_get($type=null){
        $where = '';
        if(!empty($type)){
            $where = " WHERE `Type` = '".addslashes($type)."'";
        }
        $sql = "SELECT * FROM fb_setting ".$where." ORDER BY `Order` ASC";
        $rows = $this->getRows($sql);

        $return = array();
        foreach($rows as $k=>$v){
            $return[$v['Type']][] = $v;
        }

        if(empty($type)){
            return $return;
        }else{
            return $return[$type];
        }
    }

    function setting_save($data){
        if(!empty($data)){
            $lastversion = $data[0]['LastVersion'];
            if(!empty($data)){
                $sql = $this->getBatchUpdateSql($data,'fb_setting','ID');
                $this->query($sql);
            }

            $sql = "DELETE FROM fb_setting WHERE LastVersion != '".$lastversion."'";
            $this->query($sql);
        }
    }

    function get_publisher_option_rows(){
        $sql = "SELECT ID,Email FROM publisher WHERE `Status` = 'Active'";
        $rows = $this->getRows($sql);
        return $rows;
    }

    function get_admin_user(){
        $sql = "SELECT * FROM user_admin";
        return $this->getRows($sql);
    }
}
