<?php
namespace app\api\validate;

class ReceiptList extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'type' => 'require|in:1,2',
        'state' => 'require|in:1,2,3'
    ];
}
