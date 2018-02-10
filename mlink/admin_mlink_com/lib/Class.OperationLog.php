<?php
class OperationLog extends LibFactory{

    public function get_operation_log_list($condition=array(),$pagesize = 20){
        $list = array();
        $page_num['p'] = isset($condition['p']) && !is_null($condition['p']) ? $condition['p'] : 1;
        $start  = 0+$pagesize*($page_num['p']-1);
        unset($condition['p']);
        $short_condition = '';
        if(!empty($condition['Affiliate'])) {
            $short_condition .= " AND wf_aff.Name LIKE '%".$condition['Affiliate']."%'";
        }
        if(!empty($condition['BatchOperator'])){
            $short_condition .= " AND table_change_log_batch.BatchOperator LIKE '%".$condition['BatchOperator']."%'";
        }

        $data_sql = "SELECT table_change_log_batch.*,wf_aff.Name FROM table_change_log_batch,wf_aff WHERE table_change_log_batch.BatchPrimaryKeyValue = wf_aff.ID".$short_condition." ORDER BY table_change_log_batch.BatchCreationTime DESC LIMIT ".$start.','.$pagesize;
        $list = $this->getRows($data_sql);

            //batch
        if(!$list){
            $short_condition = '';
            $data_sql = "SELECT table_change_log_batch.*,wf_aff.Name FROM table_change_log_batch,wf_aff WHERE table_change_log_batch.BatchPrimaryKeyValue = wf_aff.ID  ORDER BY table_change_log_batch.BatchCreationTime DESC LIMIT ".$start.','.$pagesize;
            $list = $this->getRows($data_sql);
        }
        $num_sql    = "SELECT COUNT(*) as count FROM table_change_log_batch,wf_aff WHERE table_change_log_batch.BatchPrimaryKeyValue = wf_aff.ID".$short_condition;
        $count  = mysql_fetch_assoc(mysql_query($num_sql));

            //detail
        $csql = '';
        foreach($list as $data){
            $csql = $data['BatchId'].','.$csql;
        }
        $csql = '('.trim($csql,',').')';
        $data_sql_detail = "SELECT * FROM table_change_log_detail WHERE BatchId IN ".$csql;
        $list2 = $this->getRows($data_sql_detail);


        //batch&detail
        foreach($list as &$value){
            foreach($list2 as $data){
                if($value['BatchId'] == $data['BatchId']){
                    $value['detail'][] = $data;
                }
            }
        }
        $page['page_total']  = ceil($count['count']/$pagesize);
        $page['page_now']    = $page_num['p'];
        $page['page_size']   = $pagesize;
        $page['data']        = $list;

        return $page;
    }
}