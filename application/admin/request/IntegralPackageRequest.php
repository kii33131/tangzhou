<?php
namespace app\admin\request;

use app\admin\validates\IntegralPackageValidate;

class IntegralPackageRequest extends FormRequest
{
    public function validate()
    {
        return (new IntegralPackageValidate())->getErrors($this->post());
    }
}