<?php
namespace app\admin\request;

use app\admin\validates\HelpCenterValidate;

class HelpCenterRequest extends FormRequest
{
    public function validate()
    {
        return (new HelpCenterValidate())->getErrors($this->post());
    }
}