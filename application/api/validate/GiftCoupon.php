<?php
namespace app\api\validate;

class GiftCoupon extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'num' => 'require|isPositiveInteger'
    ];

}
