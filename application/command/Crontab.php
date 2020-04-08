<?php

namespace app\command;

use app\api\validate\GiftCoupon;
use app\model\GiftCouponModel;
use app\model\GiftUserCouponModel;
use app\model\UserCouponOrderModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Crontab extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('crontab');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
        //用户卡券订单还原库存
        $this->userCouponOrderReleaseInventory();
        //商家分享券24小时候还原库存
        $this->giftCouponReleaseInventory();
        //用户分享券24小时候还原库存
        $this->giftUserCouponReleaseInventory();
    	// 指令输出
    	$output->writeln('1');
    }

    //用户卡券订单还原库存
    private function userCouponOrderReleaseInventory(){
        $userCouponOrderModel = new UserCouponOrderModel();
        $userCouponOrderModel->releaseInventory();
    }

    //商家分享券24小时候还原库存
    private function giftCouponReleaseInventory(){
        $giftCouponModel = new GiftCouponModel();
        $giftCouponModel->releaseInventory();
    }

    //用户分享券24小时候还原库存
    private function giftUserCouponReleaseInventory(){
        $giftUserCouponModel = new GiftUserCouponModel();
        $giftUserCouponModel->releaseInventory();
    }
}
