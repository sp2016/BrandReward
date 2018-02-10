<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }


    public function list_get()
    {
        $entity = new Network_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    public function list_post()
    {
        $entity = new Network_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    public function detail_get()
    {
        $entity = new Network_detail_entity($this->input->get());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }


    public function detail_post()
    {
        $entity = new Network_detail_entity($this->input->post());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }

    public function lists($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            $logic_object = new Network_logic();
            $networks = $logic_object->get_networks($entity);
            
            $f_networks = array();
            foreach ($networks as $network)
            {
                $f_network = $this->format_network($network);
                $f_f_network = in_array('*', $entity->filter) ? $f_network : array_intersect_key($f_network,array_flip($entity->filter));
                array_push($f_networks, $f_f_network);
            }
            return $this->result_format->success(
                $f_networks,
                $logic_object->get_network_total($entity),
                $entity->offset,
                $entity->limit
            );
        }
    }



    protected function detail($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            if (!$entity->is_empty('id')) {
                $model = new WfAfflilate();
                $network = $model::query()->find($entity->id);
                $f_network = $this->format_network($network);
                $f_f_network = in_array('*', $entity->filter) ? $f_network : array_intersect_key($f_network,array_flip($entity->filter));

                return $this->result_format->success($f_f_network, 1, 1);
            }

        }
    }

    private function format_network($network)
    {
        $f_network = array();

        if ($network instanceof WfAfflilate)
        {
            $f_network = [
                'ntw_id' => $network->ID,
                'ntw_name' => $network->Name,
                'ntw_short' => $network->ShortName,
                'ntw_code' => $network->Alias,
                'ntw_domain' => $network->Domain,
                'ntw_login_url' => $network->LoginUrl,
                'ntw_account' => $network->Account,
                'ntw_password' => $network->Password,
                'ntw_manager' => $network->Manager,
                'ntw_join_date' => $network->JoinDate,
                'ntw_rank' => $network->ImportanceRank,
                'ntw_isactive' => $network->IsActive,
                'fin_acc_id' => $network->RevenueAccount,
                'fin_acc_name' => empty($network->revenueAccount) ? '' : $network->revenueAccount->Name,
                'ntw_finance_if_received' => $network->RevenueReceived,
                'ntw_finance_payment_cycle' => $network->RevenueCycle,
                'ntw_finance_payment_remark' => $network->RevenueRemark,
                'ntw_comment' => $network->Comment,
            ];

        }

        return $f_network;
    }





}