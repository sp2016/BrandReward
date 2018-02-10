<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function all_post()
    {
        $entity = new Summary_all_entity($this->_post_args);
        $data = $this->all($entity);
        $this->response($data, 200);
    }



    public function all_get()
    {
        $entity = new Summary_all_entity($this->_get_args);
        $data = $this->all($entity);
        $this->response($data, 200);
    }

    /**
     * @description 多线程汇总POST入口
     */
    public function all_mt_post()
    {
        $entity = new Summary_all_entity($this->_post_args);
        $data = $this->all_mt($entity);
        $this->response($data, 200);
    }

    /**
     * @description 多线程汇总GET入口
     */
    public function all_mt_get()
    {
        $entity = new Summary_all_entity($this->_get_args);
        $data = $this->all_mt($entity);
        $this->response($data, 200);
    }



    /**
     * @description 多线程汇总POST入口
     */
    public function group_mt_post()
    {
        $entity = new Summary_group_entity($this->_post_args);
        $data = $this->group_mt($entity);
        $this->response($data, 200);
    }

    /**
     * @description 多线程汇总GET入口
     */
    public function group_mt_get()
    {
        $entity = new Summary_group_entity($this->_get_args);
        $data = $this->group_mt($entity);
        $this->response($data, 200);
    }

    /**
     * @description api接口：提供all汇总基础信息,仅支持POST方式
     */
    public function all_api_post()
    {
        $entity = new Summary_query_entity($this->_post_args);
        $data = $this->summary_all_api($entity);
        $this->response($data, 200);
    }

    /**
     * @description api接口：提供group汇总基础信息,仅支持POST方式
     */
    public function group_api_post()
    {
        $entity = new Summary_query_entity($this->_post_args);
        $data = $this->summary_group_api($entity);
        $this->response($data, 200);
    }

    /** 
     * @param $entity Summary_query_entity实例
     *
     * @return bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    private function summary_all_api($entity)
    {
        $data = new \Illuminate\Support\Collection();
        if ($entity instanceof Basic_entity) {
            !$entity->is_empty('site') && $entity->merge(['site' => json_decode($entity->site,true)]);
            !$entity->is_empty('store_ids') && $entity->merge(['store_ids' => json_decode($entity->store_ids,true)]);
            $a_c_logic = new Affiliate_calculation_logic($entity);
            $cal_logic = $a_c_logic->get_instance($entity);
            $calculation_type = 'ALL';
            if ($cal_logic instanceof Deal_link_data_logic)
            {
                $calculation_type = 'LINK';
            }
            $data = $cal_logic->get_calculation_data($calculation_type);
        }
        
        return $data;
    }
    

    /**
     * 多线程汇总
     */
    private function all_mt($entity)
    {
        if ($entity instanceof Basic_entity) {
            $ml = new Multi_process_logic();
            $s_logic = new Summary_logic();
            $q_entity = $s_logic->prefix_entity($entity);
            $dates = $ml->slice_date($q_entity->start_date,$q_entity->end_date,8);
            $process = array();
            foreach ($dates as $date)
            {
                //统计类型
                $q_entity->merge(['query_type' => array_get($date, 0)]);
                $q_entity->merge(['start_date' => array_get($date, 1)]);
                $q_entity->merge(['end_date' => array_get($date, 2)]);
                !$q_entity->is_empty('site') && $q_entity->merge(['site' => json_encode($q_entity->site)]);
                !$q_entity->is_empty('store_ids') && $q_entity->merge(['store_ids' => json_encode($q_entity->store_ids)]);
                array_push($process, ['url' => '/index.php/summary/all_api', 'params' => $q_entity->to_array()]);
            }
            $data = $ml->multi_process($process);
            if (empty($data))
            {
                return $this->result_format->success([], 1, 1);
            }

            $output = array(
                'clicks'             => 0,
                'clicks_robot'       => 0,
                'clicks_maybe_robot' => 0,
                'sales'              => 0,
                'orders'             => 0,
                'commission'         => 0,
                'commission_b'       => 0,
                'commission_p'       => 0,
            );

            foreach ($data as $item)
            {
                foreach ($item as $key => $value) {
                    isset($value['CLS']) && $output['clicks'] += $value['CLS'];
                    isset($value['ROB']) && $output['clicks_robot'] += $value['ROB'];
                    isset($value['ROP']) && $output['clicks_maybe_robot'] += $value['ROP'];
                    isset($value['SAL']) && $output['sales'] += $value['SAL'] * 0.0001;
                    isset($value['ORD']) && $output['orders'] += $value['ORD'];
                    isset($value['CMS']) && $output['commission'] += $value['CMS'] * 0.0001;
                    isset($value['SRV']) && isset($value['SRV']) && $output['commission_b'] += ($value['CMS'] - $value['SRV']) * 0.0001;
                    isset($value['SRV']) && $output['commission_p'] += $value['SRV'] * 0.0001;
                }
            }
            
            $f_f_transaction = in_array('*', $entity->filter) ? $output : array_intersect_key($output,array_flip($entity->filter));

            return $this->result_format->success($f_f_transaction, 1, 1);
        }

        return $this->result_format->success([], 1, 1);
    }
    
    private function all($entity)
    {
        if ($entity instanceof Basic_entity) {
            $s_logic = new Summary_logic();
            $q_entity = $s_logic->prefix_entity($entity);
            $a_c_logic = new Affiliate_calculation_logic($q_entity);
            $cal_logic = $a_c_logic->get_instance($q_entity);
            $calculation_type = 'AFFILIATE';
            if ($cal_logic instanceof Deal_link_data_logic)
            {
                $calculation_type = 'LINK';
            }
            $data = $cal_logic->get_calculation_data($calculation_type);
            if (empty($data))
            {
                return $this->result_format->success([], 1, 1);
            }

            $output = array(
                'clicks'             => 0,
                'clicks_robot'       => 0,
                'clicks_maybe_robot' => 0,
                'sales'              => 0,
                'commission'         => 0,
                'commission_b'       => 0,
                'commission_p'       => 0,
            );

            foreach ($data as $key => $value)
            {
                isset($value['CLS']) && $output['clicks'] += $value['CLS'];
                isset($value['ROB']) && $output['clicks_robot'] += $value['ROB'];
                isset($value['ROP']) && $output['clicks_maybe_robot'] += $value['ROP'];
                isset($value['SAL']) && $output['sales'] += $value['SAL'] * 0.0001;
                isset($value['CMS']) && $output['commission'] += $value['CMS'] * 0.0001;
                isset($value['SRV']) && isset($value['SRV']) && $output['commission_b'] += ($value['CMS'] - $value['SRV']) * 0.0001;
                isset($value['SRV']) && $output['commission_p'] += $value['SRV'] * 0.0001;
            }

            $f_f_transaction = in_array('*', $entity->filter) ? $output : array_intersect_key($output,array_flip($entity->filter));

            return $this->result_format->success($f_f_transaction, 1, 1);
        }

        return $this->result_format->success([], 1, 1);
    }


    public function group_get()
    {
        $entity = new Summary_group_entity($this->_get_args);
        $data = $this->group($entity);
        $this->response($data, 200);
    }

    public function group_post()
    {
        $entity = new Summary_group_entity($this->_post_args);
        $data = $this->group($entity);
        $this->response($data, 200);
    }

    private function group($entity)
    {
        if ($entity instanceof Basic_entity) {
            //格式化请求对象;
            $s_logic = new Summary_logic();
            $q_entity = $s_logic->prefix_entity($entity);
            $group_type = $entity->group;
            if (empty($group_type)) 
            {
                return $this->result_format->failed(['GROUP TYPE INVALID_1!']);
            }
            //分组类型-->分组逻辑生成器
            switch ($group_type)
            {
                case 'ADVERTISER' :
                    $a_c_logic = new Store_calculation_logic($q_entity);
                    break;
                case 'NETWORK' :
                    $a_c_logic = new Affiliate_calculation_logic($q_entity);
                    break;
                case 'PUBLISHER' :
                    $a_c_logic = new Publisher_calculation_logic($q_entity);
                    break;

            }
            //获取分组逻辑实例
            $cal_logic = $a_c_logic->get_instance($q_entity);
            //获取分组键值KEY
            switch ($group_type)
            {
                case 'ADVERTISER' :
                    $cal_type = 'STORE';
                    break;
                case 'NETWORK' :
                    $cal_type = 'AFFILIATE';
                    break;
                case 'PUBLISHER' :
                    $cal_type = 'PUBLISHER';
                    break;
                default :
                    $cal_type = '';
                    break;
            }
            if (empty($cal_type))
            {
                return $this->result_format->failed(['GROUP TYPE INVALID_2!']);
            }
            //获取分组数据
            $data = $cal_logic->get_calculation_data($cal_type,$group_type);
            if (empty($data))
            {
                return $this->result_format->failed(['EMPTY CALCULATION DATA!']);
            }
            //排序操作
            $sort_bys = $entity->sort_by;
            $sort_by = array_get($sort_bys, 0, 'CLICKS_DESC');
            switch ($sort_by)
            {
                case 'COMMISSION_ASC' :
                    $sorted_data = $data->sortBy('CMS');
                    break;
                case 'COMMISSION_DESC' :
                    $sorted_data = $data->sortByDesc('CMS');
                    break;
                case 'CLICKS_ASC' :
                    $sorted_data = $data->sortBy('CLS');
                    break;
                case 'CLICKS_DESC' :
                    $sorted_data = $data->sortByDesc('CLS');
                    break;
                default :
                    $sorted_data = $data->sortByDesc('CLS');
            }
            $f_data_s = array();
            foreach ($sorted_data as $key => $value)
            {
                if (is_null(object_get($value, $group_type)))
                {
                    continue;
                }
                $f_data = array(
                    strtolower($group_type) => object_get($value, $group_type,''),
                    'clicks' => object_get($value, 'CLS', 0),
                    'clicks_robot' => object_get($value, 'ROB', 0),
                    'clicks_maybe_robot' => object_get($value, 'ROP', 0),
                    'sales' => object_get($value, 'SAL', 0) * 0.0001,
                    'orders' => object_get($value, 'ORD', 0),
                    'commission' => object_get($value, 'CMS', 0) * 0.0001,
                    'commission_b' => object_get($value, 'SAL', false) && object_get($value, 'SRV', false) ? object_get($value, 'CMS', 0) * 0.0001 - object_get($value, 'SRV', 0) * 0.0001 : 0,
                    'commission_p' => object_get($value, 'SRV', 0) * 0.0001,
                );

                $f_f_data = in_array('*', $entity->filter) ? $f_data : array_intersect_key($f_data,array_flip($entity->filter));

                array_push($f_data_s, $f_f_data);
            }

            return $this->result_format->success($f_data_s,count($f_data_s),1,count($f_data_s));
        }
    }

    public function group_mt($entity)
    {
        if ($entity instanceof Basic_entity) {
            //格式化请求对象;
            $s_logic = new Summary_logic();
            $q_entity = $s_logic->prefix_entity($entity);
            $group_type = $entity->group;
            $q_entity->merge(['group' => $group_type]);
            if (empty($group_type)) {
                return $this->result_format->failed(['GROUP TYPE INVALID_1!']);
            }

            $ml = new Multi_process_logic();
            $dates = $ml->slice_date($q_entity->start_date,$q_entity->end_date,8);
            $process = array();
            foreach ($dates as $date)
            {
                //统计类型
                $q_entity->merge(['query_type' => array_get($date, 0)]);
                $q_entity->merge(['start_date' => array_get($date, 1)]);
                $q_entity->merge(['end_date' => array_get($date, 2)]);
                !$q_entity->is_empty('site') && $q_entity->merge(['site' => json_encode($q_entity->site)]);
                !$q_entity->is_empty('store_ids') && $q_entity->merge(['store_ids' => json_encode($q_entity->store_ids)]);
                array_push($process, ['url' => '/index.php/summary/group_api', 'params' => $q_entity->to_array()]);
            }

            $data = $ml->multi_process($process);
            if (empty($data))
            {
                return $this->result_format->success([], 0, 1);
            }
            //整合数据
            $output = array();
            foreach ($data as $item)
            {
                foreach ($item as $key => $value) {
                    $group_type_value = array_get($value, $group_type, 0);
                    if (!isset($output[$group_type_value]))
                    {
                        $output[$group_type_value] = array(
                            'CLS'       => 0,
                            'ROB'       => 0,
                            'ROP'       => 0,
                            'ORD'       => 0,
                            'SAL'       => 0,
                            'CMS'       => 0,
                            'SRV'       => 0,
                            $group_type => $group_type_value
                        );
                    }
                    isset($value['ORD']) && $output[$group_type_value]['ORD'] += $value['ORD'];
                    isset($value['CLS']) && $output[$group_type_value]['CLS'] += $value['CLS'];
                    isset($value['ROB']) && $output[$group_type_value]['ROB'] += $value['ROB'];
                    isset($value['ROP']) && $output[$group_type_value]['ROP'] += $value['ROP'];
                    isset($value['SAL']) && $output[$group_type_value]['SAL'] += $value['SAL'];
                    isset($value['CMS']) && $output[$group_type_value]['CMS'] += $value['CMS'];
                    isset($value['SRV']) && $output[$group_type_value]['SRV'] += $value['SRV'];
                }
            }
            //排序操作
            $sort_bys = $entity->sort_by;
            $sorted_data = $this->summary_sort_by(array_values($output),$sort_bys);
            $f_data_s = array();
            //格式化输出
            foreach ($sorted_data as $key => $value)
            {
                if (is_null(array_get($value, $group_type)))
                {
                    continue;
                }
                $f_data = array(
                    strtolower($group_type) => array_get($value, $group_type,''),
                    'clicks' => array_get($value, 'CLS', 0),
                    'clicks_robot' => array_get($value, 'ROB', 0),
                    'clicks_maybe_robot' => array_get($value, 'ROP', 0),
                    'sales' => array_get($value, 'SAL', 0) * 0.0001,
                    'orders' => array_get($value, 'ORD', 0),
                    'commission' => array_get($value, 'CMS', 0) * 0.0001,
                    'commission_b' => array_get($value, 'SAL', false) && array_get($value, 'SRV', false) ? array_get($value, 'CMS', 0) * 0.0001 - array_get($value, 'SRV', 0) * 0.0001 : 0,
                    'commission_p' => array_get($value, 'SRV', 0) * 0.0001,
                );

                $f_f_data = in_array('*', $entity->filter) ? $f_data : array_intersect_key($f_data,array_flip($entity->filter));

                array_push($f_data_s, $f_f_data);
            }
            //分页操作
            $f_data_s_l = array_slice(
                $f_data_s,
                ($q_entity->offset - 1) * $q_entity->limit,
                $q_entity->limit
            );

            return $this->result_format->success($f_data_s_l,count($f_data_s),$q_entity->offset,$q_entity->limit);
        }

    }

    public function summary_group_api($q_entity)
    {
        $data = new \Illuminate\Support\Collection();
        if ($q_entity instanceof Basic_entity) {
            !$q_entity->is_empty('site') && $q_entity->merge(['site' => json_decode($q_entity->site,true)]);
            !$q_entity->is_empty('store_ids') && $q_entity->merge(['store_ids' => json_decode($q_entity->store_ids,true)]);
            $group_type = $q_entity->group;
            if (empty($group_type))
            {
                return $this->result_format->failed(['GROUP TYPE INVALID_1!']);
            }

            //分组类型-->分组逻辑生成器
            switch ($group_type)
            {
                case 'ADVERTISER' :
                    $a_c_logic = new Store_calculation_logic($q_entity);
                    break;
                case 'NETWORK' :
                    $a_c_logic = new Affiliate_calculation_logic($q_entity);
                    break;
                case 'PUBLISHER' :
                    $a_c_logic = new Publisher_calculation_logic($q_entity);
                    break;

            }
            //获取分组逻辑实例
            $cal_logic = $a_c_logic->get_instance($q_entity);
            //获取分组键值KEY
            switch ($group_type)
            {
                case 'ADVERTISER' :
                    $cal_type = 'STORE';
                    break;
                case 'NETWORK' :
                    $cal_type = 'AFFILIATE';
                    break;
                case 'PUBLISHER' :
                    $cal_type = 'PUBLISHER';
                    break;
                default :
                    $cal_type = '';
                    break;
            }
            if (empty($cal_type))
            {
                return $this->result_format->failed(['GROUP TYPE INVALID_2!']);
            }
            //获取分组数据
            $data = $cal_logic->get_calculation_data($cal_type,$group_type);
        }

        return $data;
    }

    /**
     * @param array $sort_data
     * @param array $sort_bys
     *
     * @return $this
     */
    private function summary_sort_by($sort_data = array(), $sort_bys = array())
    {
        $data = new \Illuminate\Support\Collection($sort_data);
        $sort_by = array_get($sort_bys, 0, 'CLICKS_DESC');
        switch ($sort_by)
        {
            case 'COMMISSION_ASC' :
                $sorted_data = $data->sortBy('CMS');
                break;
            case 'COMMISSION_DESC' :
                $sorted_data = $data->sortByDesc('CMS');
                break;
            case 'CLICKS_ASC' :
                $sorted_data = $data->sortBy('CLS');
                break;
            case 'CLICKS_DESC' :
                $sorted_data = $data->sortByDesc('CLS');
                break;
            case 'ORDERS_ASC' :
                $sorted_data = $data->sortBy('ORD');
                break;
            case 'ORDERS_DESC' :
                $sorted_data = $data->sortByDesc('ORD');
                break;
            case 'SALES_ASC' :
                $sorted_data = $data->sortBy('SAL');
                break;
            case 'SALES_DESC' :
                $sorted_data = $data->sortByDesc('SAL');
                break;
            CASE 'CLICKS_ROBOT_ASC' :
                $sorted_data = $data->sortBy('ROB');
                break;
            CASE 'CLICKS_ROBOT_DESC' :
                $sorted_data = $data->sortByDesc('ROB');
                break;
            CASE 'CLICKS_MAYBE_ROBOT_ASC' :
                $sorted_data = $data->sortBy('ROP');
                break;
            CASE 'CLICKS_MAYBE_ROBOT_DESC' :
                $sorted_data = $data->sortByDesc('ROP');
                break;
            default :
                $sorted_data = $data->sortByDesc('CLS');
        }

        return $sorted_data;
    }
}