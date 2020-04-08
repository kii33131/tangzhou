<?php

namespace app\admin\validates;

class CashWithdrawalValidate extends AbstractValidate
{
    protected $rule = [
        'amount|提现金额' => 'require|amount:1',
        'mode|提现方式' => 'require|in:1,2|bankCard',
        'bank_card_number|银行卡号' => 'number',
        'real_name|真实姓名' => 'min:1',
        'bank|所属银行' => 'min:2'
    ];

    protected function bankCard($value, $rule='', $data='', $field='')
    {
        $keyName = [
            'bank_card_number' => '银行卡号',
            'real_name' => '真实姓名',
            'bank' => '所属银行'
        ];
        if($value == 2){
            foreach (['bank_card_number','real_name','bank'] as $val){
                if(empty($data[$val])){
                    return $keyName[$val] . '不得为空';
                }
            }
        }
        return true;
    }
}