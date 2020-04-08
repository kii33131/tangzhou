<?php

namespace app\model;


class PlatformModel extends BaseModel
{
    protected $name = 'platform';


    public static function changeBalance($amount,$type,$state = 0,$userId = 0,$payType = 0,$memberId = 0,$couponId = 0,$storeId = 0,$giftPackageId = 0){
        if($type == 1){
            self::where('id',1)->setInc('balance',$amount);
        }else{
            self::where('id',1)->setDec('balance',$amount);
        }
        $residualAmount = self::where('id',1)->value('balance');
        PlatformBalanceRecordModel::create([
            'amount' => $amount,
            'residual_amount' => $residualAmount,
            'type' => $type,
            'state' => $state,
            'pay_type' => $payType,
            'member_id' => $memberId,
            'coupon_id' => $couponId,
            'store_id' => $storeId,
            'gift_package_id' => $giftPackageId,
            'user_id' => $userId
        ]);
    }

}