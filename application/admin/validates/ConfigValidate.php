<?php

namespace app\admin\validates;

class ConfigValidate extends AbstractValidate
{
    protected $rule = [
        'appid|小程序appid' => 'max:30',
        'appsecret|小程序appsecret' => 'max:50',
        'mch_id|商户号' => 'max:50',
        'pay_key|支付密钥' => 'max:50',
        'introduce|简介' => 'max:100',
        'entry_amount|入驻金额' => 'positiveNumber|amount',
        'download_integral|卡券下载所需积分' => 'positiveNumber',
        'entry_gift_points|入驻赠送积分' => 'number',
        'coupon_release_integral|卡券发布所需积分' => 'number',
        'coupon_amount|卡券领取金额' => 'positiveNumber|amount',
        'service_charge|提现手续费比例' => 'percentage',
        'entry_rebate|入驻返利比例' => 'percentage',
        'integral_rebate|积分返利比例' => 'percentage',
    ];
}