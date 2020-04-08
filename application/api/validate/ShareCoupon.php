<?php
namespace app\api\validate;

class ShareCoupon extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'type' => 'require|in:1,2',
        'num' => 'require|isPositiveInteger'
    ];

}
