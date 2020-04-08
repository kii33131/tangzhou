<?php
namespace app\api\validate;

class Feedback extends BaseValidate
{
    protected $rule = [
        'phone' => 'require|mobile',//手机号
        'content' => 'require|length:5,300',//内容
        'email' => 'email',//邮箱
        'qq' => 'length:5,15',//QQ
    ];
}
