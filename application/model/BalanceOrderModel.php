<?php

namespace app\model;

use app\api\service\WechatPay;

class BalanceOrderModel extends BaseModel
{
    protected $name = 'balance_order';

    public function createOrder($amount,$userId){
        return $this->create([
            'amount' => $amount,
            'user_id' => $userId,
            'order_no' => WechatPay::generateOrderNo(),
            'is_pay' => 0
        ]);
    }
}