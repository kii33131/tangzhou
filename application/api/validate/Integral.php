<?php
namespace app\api\validate;

class Integral extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',

    ];



}
