<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function list_post()
    {
        $entity = new Transaction_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    public function list_get()
    {
        $entity = new Transaction_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    public function detail_post()
    {
        $entity = new Transaction_detail_entity($this->input->post());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }

    public function detail_get()
    {
        $entity = new Transaction_detail_entity($this->input->get());
        $data = $this->detail($entity);
        $this->response($data, 200);
    }

    private function lists($entity)
    {
        if ($entity instanceof Basic_entity) {
            $t_logic = new Transaction_logic();
            $transactions = $t_logic->get_transactions($entity);
            $f_transactions = array();
            foreach ($transactions as $transaction) {
                $f_transaction = $this->format_transaction($transaction);
                $f_f_transaction = in_array(
                    '*',
                    $entity->filter
                ) ? $f_transaction : array_intersect_key(
                    $f_transaction,
                    array_flip($entity->filter)
                );
                array_push($f_transactions, $f_f_transaction);
            }

            return $this->result_format->success(
                $f_transactions,
                $t_logic->get_transaction_total($entity),
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
                $model = new TransactionUnique();
                $transaction = $model::query()->find($entity->id);
                $f_transaction = $this->format_transaction($transaction);
                $f_f_transaction = in_array('*', $entity->filter) ? $f_transaction : array_intersect_key($f_transaction,array_flip($entity->filter));

                return $this->result_format->success($f_f_transaction, 1, 1);
            }

        }
    }

    private function format_transaction($transaction)
    {
        $f_transaction = array();
        if ($transaction instanceof TransactionUnique)
        {
            $domain = $transaction->domain;
            $stores = $domain instanceof Domain ? $domain->store : [];
            $store = array_get($stores, 0, null);
            $f_transaction = [
                'tran_id'                  => $transaction->ID,
                'tran_brid'                => $transaction->BRID,
                'ntw_id'                   => $transaction->AffId,
                'ntw_name'                 => $transaction->network instanceof WfAfflilate ? $transaction->network->Name : 0,
                'pgm_id'                   => $transaction->programId,
                'pgm_name'                 => $transaction->ProgramName,
                'dom_id'                   => $transaction->domainId,
                'dom_domain'               => $transaction->domainUsed,
                'adv_id'                   => $store instanceof Store ? $store->ID : 0,
                'adv_name'                 => $store instanceof Store ? $store->Name : 0,
                'tran_original_sales'      => $transaction->OldSales,
                'tran_original_commission' => $transaction->OldCommission,
                'tran_original_currency'   => $transaction->OldCur,
                'tran_sales'               => $transaction->Sales,
                'tran_commission'          => $transaction->Commission,
                'tran_currency'            => 'USD',
                'pub_commission_rate'      => $transaction->ShowRate,
                'tran_commission_b'        => $transaction->TaxCommission,
                'tran_commission_p'        => $transaction->ShowCommission,
                'tran_sid'                 => $transaction->SID,
                'tran_pub_trackingid'      => $transaction->PublishTracking,
                'tran_ntw_orderid'         => $transaction->OrderId,
                'tran_ntw_tradeid'         => $transaction->TradeId,
                'tran_state'               => $transaction->TradeStatus,
                'tran_cancel_reason'       => empty($transaction->TradeCancelReason) ? '' : $transaction->TradeCancelReason,
                'tran_ispaid'              => $transaction->State != 'PAID' ? 'NO' : 'YES',
                'tran_isfine'              => $transaction->State != 'FINE' ? 'NO' : 'YES',
                'tran_isreceive'           => $transaction->State != 'CONFIRMED' ? 'NO' : 'YES',
                'tran_time_click'          => $transaction->Visited,
                'tran_time_create'         => $transaction->Created instanceof \Carbon\Carbon ? $transaction->Created->toDateTimeString() : 0,
                'tran_time_lastupdate'     => $transaction->Updated instanceof \Carbon\Carbon ? $transaction->Updated->toDateTimeString() : 0,
            ];
        }

        return $f_transaction;
    }




}