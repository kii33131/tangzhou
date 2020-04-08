<?php
namespace app\admin\request;

use app\admin\validates\CashWithdrawalValidate;

class CashWithdrawalRequest extends FormRequest
{
    public function validate()
    {
        return (new CashWithdrawalValidate())->getErrors($this->post());
    }
}