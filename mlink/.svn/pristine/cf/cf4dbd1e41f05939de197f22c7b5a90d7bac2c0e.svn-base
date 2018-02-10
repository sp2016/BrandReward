<?php
namespace App\Http\Entity;

class BasicEntity extends Entity
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }


    public function isEmpty($name)
    {
        if (isset($this->attributes[$name])){
            if (empty($this->attributes[$name])){
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}