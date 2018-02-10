<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_logic
{
    public function prefix_query($query = null,$entity = null)
    {
        if ($entity instanceof Basic_entity &&  $query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            if (!$entity->is_empty('start_date'))
            {
                $query->where('PendingDate','>=',$entity->start_date);
            }
            
            if (!$entity->is_empty('end_date'))
            {
                $query->where('PendingDate','<=',$entity->end_date);
            }

            if (!$entity->is_empty('month'))
            {
                $month = $entity->month;
                $date_month = date_create_from_format('Y-m',$month);
                $month_start = date_format($date_month, 'Y-m');
                date_add($date_month, date_interval_create_from_date_string('+1 month'));
                $month_end = date_format($date_month, 'Y-m');

                $query->where('PendingDate','>=',$month_start . '-1');
                $query->where('PendingDate','<',$month_end . '-1');
            }
            
            if (!$entity->is_empty('pub_id'))
            {
                $pub_id = $entity->pub_id;
                $query->whereIn('PublisherId',is_array($pub_id) ? $pub_id : array($pub_id));
            }

            if (!$entity->is_empty('site_id'))
            {
                $site_id = $entity->site_id;
                $site_id = is_array($site_id) ? $site_id : array($site_id);
                $query->whereHas('publisherAccount',
                    function ($subQuery) use ($site_id) {
                        if (empty($site_id)) {
                            $subQuery->whereIn('ID',$site_id);
                        } else {
                            $publisher_id_chunk = array_chunk($site_id, 100);
                            foreach ($publisher_id_chunk as $p_site_id) {
                                $subQuery->orWhereIn('ID',$p_site_id);
                            }
                        }

                    }
                );
            }

            if (!$entity->is_empty('pay_transactionid'))
            {
                
            }
        }
    }


    public function get_pending_payments($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            $model = new PaymentPending();
            $query = $model::query();
            if ($entity instanceof Basic_entity)
            {
                $this->prefix_query($query,$entity);
                if (!$entity->is_empty('paginate')) {
                    $query->take($entity->limit)->skip(($entity->offset-1)* $entity->limit);
                }

                return $query->get(array('*'));
            }
        }
    }
}