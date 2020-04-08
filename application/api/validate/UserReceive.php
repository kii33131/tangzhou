<?php
namespace app\api\validate;

class UserReceive extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',//
        'store_id' => 'require|isPositiveInteger',//领取分享店铺id
    ];

}
