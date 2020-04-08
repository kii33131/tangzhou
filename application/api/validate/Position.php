<?php
namespace app\api\validate;

class Position extends BaseValidate
{
    protected $rule = [
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty',
    ];

}
