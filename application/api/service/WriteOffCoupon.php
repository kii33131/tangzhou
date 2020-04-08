<?php


namespace app\api\service;


use app\exceptions\ApiException;
use app\model\CouponModel;
use app\model\MemberModel;
use app\model\StoreStaffModel;
use app\model\UserCouponModel;
use app\model\UserDownloadCouponModel;
use think\Db;

class WriteOffCoupon
{
    /**
     * 核销卡券
     * @param $code string 核销码
     * @param $userId int 用户id
     * @throws ApiException
     */
    public function writeOff($code,$userId){
        if(empty($code)){
            error('code参数错误',$code);
        }
        Db::startTrans();
        try{
            $coupon = UserCouponModel::lock(true)
                ->where([
                    'code' => $code,
                    'state' => 1,
                    'residual_is_pay' => 1,
                    'is_delete' => 0,
                    'is_share' => 0
                ])->find();
            if(!$coupon){
                error('卡券不存在',40001);
            }
            if($coupon->couponInfo->getData('start_time') > time()){
                error('优惠券使用时间未开始',40005);
            }
            if($coupon->couponInfo->getData('end_time') < time()){
                error('优惠券已过期',40006);
            }
            if(!$this->isCanWriteOffCoupon($coupon->couponInfo->id,$userId)){
                error('用户无权限核销当前卡券',40009);
            }
            if($coupon->couponInfo->pattern == 2){
                $this->promotionRebate($coupon);
            }
            $coupon->state = 2;
            $coupon->write_off_time = time();
            $coupon->save();
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 判断用户是否可核销卡券
     * @param $couponId int 卡券id
     * @param $userId in 用户id
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function isCanWriteOffCoupon($couponId,$userId){
        $coupon = CouponModel::where('id',$couponId)->find();
        if(!$coupon){
            return false;
        }
        $storeId= MemberModel::getStoreIdByUid($userId);
        if($storeId == $coupon['store_id']){
            return true;
        }
        $storeStaff = StoreStaffModel::where([
            'store_id' => $coupon['store_id'],
            'user_id' => $userId
        ])->find();
        if($storeStaff){
            return true;
        }
        return false;
    }

    /**
     * 推广返利
     * @param $coupon
     * @return bool
     * @throws \think\Exception
     */
    private function promotionRebate($coupon){
        if($coupon->couponInfo->promotion_commission > 0 &&
            $coupon->residual_amount > 0
        ){
            $returnAmount = $coupon->residual_amount * ($coupon->couponInfo->promotion_commission / 100);//返佣金额
            $isReturn = false;//是否返佣
            if($coupon->download_coupon_id != 0){
                $downloadCoupon = UserDownloadCouponModel::where('id',$coupon->download_coupon_id)->find();
                if($downloadCoupon){
                    MemberModel::changeBalance($downloadCoupon->user_id,$returnAmount,1,4);
                    $isReturn = true;
                }
            }else{
                if($coupon->user_id != $coupon->receive_user_id){
                    MemberModel::changeBalance($coupon->receive_user_id,$returnAmount,1,4);
                    $isReturn = true;
                }
            }
            $storeUserId = MemberModel::getUidByStoreId($coupon->couponInfo->store_id);//获取卡券商家用户id
            if($storeUserId){
                $couponMoney = $coupon->residual_amount;//卡券返给商家金额
                if($isReturn){
                    $couponMoney = $couponMoney - $returnAmount;//卡券金额减去已返利金额
                }
                MemberModel::changeBalance($storeUserId,$couponMoney,1,7);
            }
        }else{
            return false;
        }
    }

}