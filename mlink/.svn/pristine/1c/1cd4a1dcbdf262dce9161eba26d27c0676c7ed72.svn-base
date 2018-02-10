<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Basic_entity
{
    protected static $validator_rules = [];
    protected $customize_messages = [];
    protected $attributes = array();
    //过滤字段列表
    protected $allow_fields = array();
    //检查参数_数组_内容
    protected $check_keys = array();

    public function __construct($object = array())
    {
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $this->attributes[$key] = $value;
            }
        }
        if (is_object($object)) {
            $array = $this->object_to_array($object);
            foreach ($array as $key => $value) {
                $this->attributes[$key] = $value;
            }
        }

        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function filter($field = array('ALL'))
    {
        $filter = array();
        if (is_array($field)) {
            foreach ($field as $value) {
                if (in_array($value, $this->allow_fields)) {
                    array_push($filter, $value);
                }
            }
        }
        if (in_array('ALL', $filter)) {
            $filter = array('*');
        }
        $this->merge(['filter' => $filter]);
    }
    
    protected function _initialize()
    {
        foreach ($this->attributes as &$attribute)
        {
            if (is_array($attribute))
            {
                $attribute = array_filter($attribute);
            }
        }
        $this->check_key();
    }

    protected function check_key()
    {
        if (empty($this->check_keys))
        {
            return false;
        }
        
        foreach ($this->check_keys as $check_key => $check_array)
        {
            if (!$this->is_empty($check_key))
            {
                $check_key_value = $this->$check_key;
                unset($this->attributes[$check_key]);
                if (!is_array($check_key_value))
                {
                    if (in_array(strtoupper($check_key_value), $check_array))
                    {
                        $this->attributes[$check_key] = strtoupper($check_key_value);
                    }
                    continue;
                }
                $check_key_result = array();
                foreach ($check_key_value as $c_key_value) {
                    if (in_array(strtoupper($c_key_value), $check_array))
                    {
                        array_push($check_key_result, strtoupper($c_key_value));
                    }
                }
                !empty($check_key_result) && $this->attributes[$check_key] = $check_key_result;
            }
        }
    }

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : '';
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function is_empty($name)
    {
        if (isset($this->attributes[$name])){
            $object = $this->attributes[$name];
            if (empty($object)){
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    public function merge($element = array())
    {
        !empty($element) && $this->attributes = array_merge($this->attributes, $element);
    }

    public function to_json()
    {
        return json_encode($this->to_array());
    }

    public function to_array()
    {
        return empty($this->attributes) ? array() : $this->attributes;
    }

    public function validate()
    {
        return true;
    }

    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    private function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }

        return $obj;
    }


}