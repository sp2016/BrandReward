<?php
namespace App\Http\Entity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;

abstract class Entity extends Fluent
{
    protected static $validatorRules = [];
    protected $customizeMessages = [];
    protected $customizeAttributes = [];

    public function validate($throwOnError = true)
    {
        $this->registerValidators();
        $validator = \Validator::make(
            $this->toArray(),
            static::$validatorRules,
            $this->customizeMessages,
            $this->customizeAttributes
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            if (true === $throwOnError) {
                try {
                    \Log::info('VALIDATE_FAILS', $this->toArray());
                } catch (\Exception $e) {
                    //keep silent
                }
                throw new ValidationException($validator);
            }
            return $errors;
        }


    }

    public function registerValidators()
    {
        //register custom validators
        \Validator::extend(
            'mobile',
            function ($attribute, $value, $parameter) {
                return (boolean)preg_match("/^((1[3-9][0-9])|200)[0-9]{8}$/", $value);
            },
            ':attribute格式无效'
        );
        \Validator::extend(
            'phoneExt',
            function ($attribute, $value, $parameter) {
                return (boolean)preg_match("/^0[0-9]{2,3}$/", $value);
            },
            ':attribute格式无效'
        );
        \Validator::extend(
            'phone',
            function ($attribute, $value, $parameter) {
                return (boolean)preg_match("/^[1-9]\d{5,7}$/", $value);
            },
            ':attribute格式无效'
        );
        \Validator::extend(
            'idCard',
            function ($attribute, $value, $parameter) {
                return (boolean)preg_match("/^[0-9]{15}$|^[0-9]{17}[a-zA-Z0-9]$/", $value);
            },
            ':attribute格式无效'
        );
    }

    public function merge($element = array())
    {
        !empty($element) && $this->attributes = array_merge($this->attributes, $element);
    }

    public function set($key, $value)
    {
        if (!array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = $value;

            return true;
        } else {
            return false;
        }
    }
}