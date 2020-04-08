<?php
namespace app\admin\request;

use app\admin\validates\AgentValidate;

class AgentRequest extends FormRequest
{
    public function validate()
    {
        return (new AgentValidate())->getErrors($this->post());
    }
}