<?php


namespace app\enum;


class AgentBalanceWithdrawalRecordEnum
{
    const TYPE = [
        1 => '待审核',
        2 => '审核通过',
        3 => '已拒绝'
    ];

    const MODE = [
        1 => '微信',
        2 => '银行卡'
    ];


}