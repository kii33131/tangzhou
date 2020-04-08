<?php


namespace app\enum;


class PlatformBalanceRecord
{
    //充值方式
    const PAYTYPE = [
        1 => '微信'
    ];

    //类型
    const TYPE = [
        1 => '充值',
        2 => '消费'
    ];

    //消费方式
    const STATE = [
        1 => '后台充值',
        2 => '商家入驻',
        3 => '用户积分充值',
        4 => '用户余额充值',
        5 => '卡券收入(平台价格)',
        6 => '卡券收入(剩余价格)',
        7 => '礼包收入',
        8 => '用户提现'
    ];

}