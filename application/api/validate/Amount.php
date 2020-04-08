<?php
namespace app\api\validate;

class Amount extends BaseValidate
{
    protected $rule = [
        'amount' => 'require|amount:1',
        'mode' => 'require|in:1,2|bankCard',
        'bank_card_number' => 'number',
        'real_name' => 'min:1',
        'bank' => 'min:2'
    ];

    protected $scene = [
        'payBalance'  =>  ['amount'],
    ];

    protected function bankCard($value, $rule='', $data='', $field='')
    {
        if($value == 2){
            foreach (['bank_card_number','real_name','bank'] as $val){
                if(empty($data[$val])){
                    return $val . '不得为空';
                }
            }
        }
        return true;
    }
}
