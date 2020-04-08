<?php
namespace app\api\validate;

class TokenGet extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty',
        'name' => 'require|isNotEmpty',
        'picture' => 'require|isNotEmpty'
    ];

}
