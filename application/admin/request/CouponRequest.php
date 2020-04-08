<?php
namespace app\admin\request;

use app\admin\validates\CouponValidate;

class CouponRequest extends FormRequest
{
    public function validate()
    {
        return (new CouponValidate())->getErrors($this->post());
    }
}