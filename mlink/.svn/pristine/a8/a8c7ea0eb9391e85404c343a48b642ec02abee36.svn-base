<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Response class
 *
 * @author    Phil Sturgeon, Chris Kacerguis, @softwarespot
 * @license   http://www.dbad-license.org/
 */
class Response
{

    private $code;
    private $message;
    private $data;
    private $total;
    private $offset;
    private $limit;

    public function success($data,$total = 0, $offset = 0, $limit = 10)
    {
        $this->code = 1;
        $this->message = 'success';
        $this->data = $data;
        $this->total = $total;
        $this->offset = $offset;
        $this->limit  = $limit;
        return $this->to_array();
    }

    public function failed($data)
    {
        $this->code = 0;
        $this->message = 'failed';
        $this->data = $data;

        return $this->to_array();
    }

    public function to_array()
    {
        return [
            '@total'  => [
                'code'       => $this->code,
                'message'    => $this->message,
                'total'      => $this->total,
                'page_size'  => $this->limit,
                'page_now'   => $this->offset,
                'page_total' => $this->limit > 0 ? ceil($this->total / $this->limit) : 0
            ],
            'Actions' => $this->data
        ];
    }
}