<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Outbound_log extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function lists_post()
    {
        $entity = new Outbound_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    public function lists_get()
    {
        $entity = new Outbound_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    public function detail_post()
    {
        $entity = new Outbound_detail_entity($this->input->post());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }

    public function detail_get()
    {
        $entity = new Outbound_detail_entity($this->input->get());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }

    protected function detail($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            if (!$entity->is_empty('id')) {
                $model = new OutboundLogMin();
                $log = $model::query()->find($entity->id);
                $format_log = $this->format_log($log);
                $f_f_log = in_array('*', $entity->filter) ? $format_log : array_intersect_key($format_log,array_flip($entity->filter));

                return $this->result_format->success($f_f_log, 1, 1);
            }

        }
    }
    
    
    protected function lists($entity)
    {
        if ($entity instanceof Basic_entity) {
            $logic_object = new Outbound_log_logic();
            $logs = $logic_object->get_logs($entity);

            $f_logs = array();
            foreach ($logs as $log)
            {
                $f_log = $this->format_log($log);
                $f_f_log = in_array('*', $entity->filter) ? $f_log : array_intersect_key($f_log,array_flip($entity->filter));
                array_push($f_logs,$f_f_log);
            }

            return $this->result_format->success(
                $f_logs,
                $logic_object->get_logs_total($entity),
                $entity->offset,
                $entity->limit
            );
        }
    }


    private function format_log($log)
    {
        $f_log = array();
        if ($log instanceof OutboundLogMin)
        {
            $o_log = $log->outboundLog;
            $p_account = $log->publisherAccount;
            $p_obj = !empty($p_account) ? $p_account->publisher : null;
            $domain = $log->domain;
            $store = $domain->store[0];
            $network = $log->network;
            $program = $log->program;
            $f_log = [
                'out_id'    => $o_log->id,
                'clicktime' => $o_log->created,
                'click_robot' => $o_log->IsRobet != 'YES' ? 'NO' : 'YES',
                'click_mayberobot' => $log->IsRobet != 'POTENTIAL' ? 'NO' : 'YES',
                'landingpage_url' => $o_log->pageUrl,
                'referrer_url' => $o_log->referer,
                'user_ip' => $o_log->ip,
                'user_country' => $o_log->country,
                'site_key' => $o_log->site,
                'site_domain' => empty($p_account) ? '' : $p_account->Domain,
                'site_type' => empty($p_account) ? '' : $p_account->SiteOption,
                'site_country' => empty($p_obj) ? '' : $p_obj->country->CountryCode,
                'pub_id' => empty($p_account) ? '' : $p_account->PublisherId,
                'pub_email' => empty($p_obj) ? '' : $p_obj->Email,
                'pub_manager' => empty($p_obj) ? '' : $p_obj->Manager,
                'adv_id' => $store->ID,
                'adv_name' => empty($store->NameOptimized) ? $store->Name : $store->NameOptimized,
                'dom_id' => empty($domain) ? 0 : $domain->ID,
                'dom_domain' => empty($domain) ? '' : $domain->Domain,
                'ntw_id' =>  empty($network) ? 0 : $network->ID,
                'ntw_name' => empty($network) ? '' : $network->Name,
                'pgm_id' => empty($program) ? 0 : $program->ID,
                'pgm_idinaff' => empty($program) ? 0 : $program->IdInAff,
                'pgm_name' => empty($program) ? '' : $program->Name,
                'hasorder' => $log->transactions()->count('ID') > 0 ? 'YES' : 'NO',
                'sales' => $log->transactions()->sum('Sales'),
                'commission' => $log->transactions()->sum('Commission'),
                'commission_b' => $log->transactions()->sum('TaxCommission'),
                'commission_p' => $log->transactions()->sum('ShowCommission'),
            ];
        }

        return $f_log;
    }




}