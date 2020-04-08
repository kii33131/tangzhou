<?php


namespace app\api\service;


use app\model\CouponModel;
use app\model\MemberModel;
use app\model\StoreModel;

class CouponService
{
    /**
     * 下架卡券退还积分
     * @param $id
     */
    public function downCoupon($id){
        $coupon = CouponModel::where('id',$id)->find();
        $stock = $coupon->stock;
        $total = $coupon->total;
        if($stock > 0){
            $coupon->total -= $stock;
            $coupon->stock = 0;
            $returnIntegral = ($coupon->cost_integral / $total) * $stock;//需退还的积分
            if($returnIntegral > 0){
                $coupon->cost_integral -= $returnIntegral;
                $userId = StoreModel::where('id',$coupon->store_id)->value('user_id');
                MemberModel::changeIntegral($userId,$returnIntegral,1,6);
            }
            $coupon->save();
        }
    }
}