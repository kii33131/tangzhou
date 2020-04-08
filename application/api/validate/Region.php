<?php
namespace app\api\validate;

class Region extends BaseValidate
{
    protected $rule = [
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'district' => 'require|isNotEmpty'
    ];

}
