<?php
namespace app\api\validate;

class TouristDetail extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty',
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty'
    ];

}
