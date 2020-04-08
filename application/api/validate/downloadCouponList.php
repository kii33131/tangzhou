<?php
namespace app\api\validate;

class downloadCouponList extends BaseValidate
{
    protected $rule = [
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty',
        'type' => 'require|isNotEmpty|in:1,2',
        'is_overdue' => 'require|isNotEmpty|in:1,2',
        'use' => 'in:0,1'
    ];

}
