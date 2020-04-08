<?php
namespace app\admin\request;

use app\admin\validates\BannerValidate;

class BannerRequest extends FormRequest
{
    public function validate()
    {
        return (new BannerValidate())->getErrors($this->post());
    }
}