<?php
namespace app\api\validate;

class MyCouponDetail extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty'
    ];

}
