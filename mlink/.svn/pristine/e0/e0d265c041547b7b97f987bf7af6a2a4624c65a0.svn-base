<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function list_post()
    {
        $entity = new Coupon_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    public function list_get()
    {
        $entity = new Coupon_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    private function lists($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            $c_logic = new Promotion_logic();
            $coupons = $c_logic->get_promotions($entity);
            $f_coupons = array();
            foreach ($coupons as $coupon)
            {
                $f_coupon = $this->format_coupon($coupon);
                $f_f_coupon = in_array('*', $entity->filter) ? $f_coupon : array_intersect_key($f_coupon,array_flip($entity->filter));

                array_push($f_coupons, $f_f_coupon);
            }

            return $this->result_format->success(
                $f_coupons,
                $c_logic->get_promotion_total($entity),
                $entity->offset,
                $entity->limit
            );
        }
    }


    private function format_coupon($coupon)
    {
        $f_coupon = [];
        if ($coupon instanceof Coupon)
        {
            //利润率
            $p_c_r = 0;
            if ($coupon->program->intell instanceof ProgramIntell)
            {
                $c_type = $coupon->program->intell->CommissionType;
                $c_value = $coupon->program->intell->CommissionUsed;
                $c_currency = $coupon->program->intell->CommissionCurrency;
                switch ($c_type)
                {
                    case 'Percent' :
                        $p_c_r = $c_value . '%';
                        break;
                    case 'Value' :
                        $p_c_r = $c_currency  . $c_value;
                        break;
                }
            }
            $f_coupon = [
                'promo_id' => $coupon->ID,
                'promo_title' => $coupon->Title,
                'promo_desc' => $coupon->Desc,
                'promo_couponcode' => $coupon->CouponCode,
                'promo_linkid' => $coupon->EncodeId,
                'promo_commission_rate' => $p_c_r,
                'promo_period_from' => $coupon->StartDate,
                'promo_period_to' => $coupon->EndDate,
                'adv_id' => $coupon->StoreId,
                'adv_name' => $coupon->store->Name,
                'adv_category' => $coupon->store->category instanceof CategoryStd ? $coupon->store->category->Name : '',
                'ntw_id' => $coupon->program instanceof Program ? $coupon->program->AffId : 0,
                'ntw_name' => $coupon->program instanceof Program ? $coupon->program->network->Name : '',
                'promo_type' => $coupon->Type,
                'promo_country' => $coupon->country,
                'promo_source' => $coupon->source,
                'promo_status' => $coupon->Status,
                'promo_lang' => $coupon->language,
            ];
        }

        return $f_coupon;
    }
}