<?php
namespace app\admin\request;

use app\admin\validates\IndustryCategoryValidate;

class IndustryCategoryRequest extends FormRequest
{
    public function validate()
    {
        return (new IndustryCategoryValidate())->getErrors($this->post());
    }
}