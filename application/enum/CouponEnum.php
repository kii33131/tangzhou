<?php


namespace app\enum;


class CouponEnum
{
    const STATE = [
        1 => '待审核',
        2 => '待发布',
        3 => '已发布',
        4 => '已下架',
        5 => '已拒绝'
    ];

    const TYPE = [
        1 => '抢购券',
        2 => '促销券'
    ];

    const EDITSTATE = [
        0,//待提交审核
        1,//待审核
        2//待发布
    ];
}