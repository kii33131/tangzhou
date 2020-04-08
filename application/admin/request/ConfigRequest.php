<?php
namespace app\admin\request;

use app\admin\validates\ConfigValidate;

class ConfigRequest extends FormRequest
{
    public function validate()
    {
        return (new ConfigValidate())->getErrors($this->post());
    }
}