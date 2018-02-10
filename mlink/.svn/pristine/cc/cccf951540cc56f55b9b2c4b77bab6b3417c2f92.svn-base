<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_feed extends REST_Controller
{

    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function list_post()
    {
        $entity = new Product_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    public function list_get()
    {
        $entity = new Product_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }


    private function lists($entity)
    {
        if ($entity instanceof Basic_entity) 
        {
            $c_logic = new Product_logic();
            $products = $c_logic->get_products($entity);
            $f_products = array();
            foreach ($products as $product)
            {
                $f_product = $this->format_product($product);
                $f_f_product = in_array('*', $entity->filter) ? $f_product : array_intersect_key($f_product,array_flip($entity->filter));
                array_push($f_products, $f_f_product);
            }

            return $this->result_format->success(
                $f_products,
                $c_logic->get_product_total($entity),
                $entity->offset,
                $entity->limit
            );
        }
    }

    private function format_product($product)
    {
        $f_product = [];
        if ($product instanceof Product)
        {
            //利润率
            $c_c_r = 0;
            if ($product->program->intell instanceof ProgramIntell)
            {
                $c_type = $product->program->intell->CommissionType;
                $c_value = $product->program->intell->CommissionUsed;
                $c_currency = $product->program->intell->CommissionCurrency;
                switch ($c_type)
                {
                    case 'Percent' :
                        $c_c_r = $c_value . '%';
                        break;
                    case 'Value' :
                        $c_c_r = $c_currency . $c_value;
                        break;
                }
            }
            $f_product = [
                'pdt_id' => $product->ID,
                'pdt_title' => $product->ProductName,
                'pdt_desc' => $product->ProductDesc,
                'pdt_linkid' => $product->EncodeId,
                'pdt_commission_rate' => $c_c_r,
                'pdt_image' => $product->ProductImage,
                'pdt_addtime' => $product->AddTime,
                'adv_id' => $product->StoreId,
                'adv_name' => $product->store->Name,
                'adv_category' => $product->store->category instanceof CategoryStd ? $product->store->category->Name : '',
                'ntw_id' => $product->program instanceof Program ? $product->program->AffId : 0,
                'ntw_name' => $product->program instanceof Program ? $product->program->network->Name : '',
                'pdt_country' => $product->Country,
                'pdt_source' => $product->Source,
                'pdt_lang' => $product->Language,
            ];
        }

        return $f_product;
    }
}