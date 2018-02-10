<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends REST_Controller
{
    private $result_format;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->result_format = new Response();
    }

    public function lists_post()
    {
        $entity = new Payment_entity($this->input->post());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    public function lists_get()
    {
        $entity = new Payment_entity($this->input->get());
        $data = $this->lists($entity);
        $this->response($data, 200);
    }

    private function lists($entity)
    {
        if ($entity instanceof Basic_entity)
        {

        }   
    }

}