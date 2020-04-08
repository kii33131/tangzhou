<?php
namespace app\admin\request;

use app\admin\validates\TemplateValidate;

class TemplateRequest extends FormRequest
{
    public function validate()
    {
        return (new TemplateValidate())->getErrors($this->post());
    }
}