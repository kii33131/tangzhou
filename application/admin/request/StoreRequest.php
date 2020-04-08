<?php
namespace app\admin\request;

use app\admin\validates\StoreValidate;

class StoreRequest extends FormRequest
{
    public function validate()
    {
        return (new StoreValidate())->getErrors($this->post());
    }
}