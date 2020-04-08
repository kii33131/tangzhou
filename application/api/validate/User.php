<?php
namespace app\api\validate;

class User extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty',
    ];

}
