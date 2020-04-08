<?php

namespace app\model;

use app\api\service\StoreService;
use app\exceptions\ApiException;
use think\Db;

class GiftPackageModel extends BaseModel
{
    protected $name = 'gift_package';

    /**
     * 创建礼包
     * @param $data
     * @param $uid
     * @return bool
     * @throws ApiException
     */
    public function createdPackage($data,$uid){
        $package['name'] = $data['name'];
        $package['gift'] = $data['gift'];
        $package['pay_money'] = $data['pay_money'];
        $package['num'] = $data['num'];
        $package['state'] = 1;
        $package['stock'] = $data['num'];
        $userId = StoreService::getStoreUserId($uid);
        $package['user_id'] = $userId;
        Db::startTrans();
        try {
            $GiftPackage=GiftPackageModel::create($package);
            foreach ($data['data'] as $k=>$v){
                $packagedetail = [];
                $coupon=CouponModel::get($v['id']);
                if(!$coupon){
                    error('卡券不存在','40001');
                }
                $bool=$this->checkCouponIsDownload($v['id'],$userId);
                if($bool){
                    //用户下载的优惠券塞入礼包
                    $DownloadCoupon=UserDownloadCouponModel::lock(true)
                        ->where([
                            'coupon_id'=>$v['id'],
                            'user_id'=>$userId,
                            'is_delete' => 0
                        ])->find();
                    if(!$DownloadCoupon){
                        error('下载卡券不存在','40001');
                    }
                    if($v['num'] != 1){
                        error('下载券放入礼包数需为1张','50000');
                    }
                    if($DownloadCoupon->stock < $v['num'] * $data['num']){
                        error('下载券库存不足','40003');
                    }
                    UserDownloadCouponModel::where([
                        'coupon_id'=>$v['id'],
                        'user_id'=>$userId,
                        'is_delete' => 0
                    ])->setDec('stock',$v['num'] * $data['num']);
                    $packagedetail['download_coupon_id'] = $DownloadCoupon->id;
                }else{
                    //用户自己创建的优惠券塞入礼包
                    $usercoupon=CouponModel::lock(true)->where([
                        'id'=>$v['id'],
                        'store_id'=>StoreService::getStoreId($uid),
                        'state' => 3,
                        'is_delete' => 0
                    ])->find();
                    if(!$usercoupon){
                        error('用户卡券不存在','40001');
                    }
                    if($usercoupon->stock < $v['num'] * $data['num']){
                        error('用户优惠券库存不足','40003');
                    }
                    CouponModel::where([
                        'id'=>$v['id']
                    ])->setDec('stock',$v['num'] * $data['num']);
                }
                $packagedetail['gift_package_id'] = $GiftPackage->id;
                $packagedetail['user_id'] = $userId;
                $packagedetail['coupon_id'] = $v['id'];
                $packagedetail['num'] = $v['num'];
                GiftPackageDetailModel::create($packagedetail);
            }
            // 提交事务
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            throw $e;
        }
    }
    //当优惠券存在的情况判断优惠券是否是下载券
    public function checkCouponIsDownload($id,$user_id){
        $downloadCoupon = UserDownloadCouponModel::where([
            'coupon_id'=>$id,
            'user_id'=>$user_id,
            'is_delete' => 0
        ])->find();
        if($downloadCoupon){
            return true;
        }
        return false;
    }

    /**
     * 删除礼包
     * @param $id 礼包id
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    public static function deletePackage($id,$uid){
        Db::startTrans();
        try{
            $userId = StoreService::getStoreUserId($uid);
            //判断礼包是否存在
            $giftPackage = self::lock(true)->where([
                'id' => $id,
                'user_id' => $userId,
                'is_delete' => 0
            ])->find();
            if(!$giftPackage){
                error('礼包不存在',50003);
            }
            //判断库存是否大于0
            if($giftPackage->stock > 0){
                //还原库存
                $giftPackageDetail = GiftPackageDetailModel::where('gift_package_id',$giftPackage->id)->select();
                if($giftPackageDetail){
                    foreach ($giftPackageDetail as $val){
                        if($val->download_coupon_id > 0){
                            //还原下载券库存
                            UserDownloadCouponModel::where('id',$val->download_coupon_id)->setInc('stock',$val->num * $giftPackage->stock);
                        }else{
                            //还原卡券库存
                            CouponModel::where('id',$val->coupon_id)->setInc('stock',$val->num * $giftPackage->stock);
                        }
                    }
                }
            }
            //删除礼包
            self::where('id',$id)->update(['is_delete'=>1,'stock'=>0]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 领取礼包
     * @param $id 礼包id
     * @param $user_id 用户id
     * @return bool
     * @throws \Exception
     */
    public static function receivePackage($id,$user_id){
        //判断礼包是否存在
        $giftPackage = self::lock(true)->where([
            'id' => $id,
            'is_delete' => 0
        ])->find();
        if(!$giftPackage){
            error('礼包不存在',50003);
        }
        if($giftPackage->user_id == $user_id){
            error('不能领取自己的礼包',50005);
        }
        if($giftPackage->stock < 1){
            error('礼包库存不足',50004);
        }
        //领取卡券到礼包
        $giftPackageDetail = GiftPackageDetailModel::where('gift_package_id',$giftPackage->id)->select();
        if(!$giftPackageDetail){
            error('礼包不存在',50003);
        }
        foreach ($giftPackageDetail as $val){
            //领取卡券到卡包
            UserCouponModel::receiveCoupon($user_id,$val->coupon_id,$val->num,$val->download_coupon_id);
        }
        //减少礼包库存
        self::where('id',$giftPackage->id)->setDec('stock');
        return true;
    }

    /**
     * 支付后领取礼包
     * @param $orderNo string 订单号
     * @param $amount number 金额
     * @param int $pay_type 支付类型  0：余额支付   1：微信支付
     * @return bool
     */
    public function giftReceive($orderNo,$amount,$pay_type = 0){
        Db::startTrans();
        $order = GiftPackageOrderModel::lock(true)->where([
            'order_no' => $orderNo,
            'is_pay' => 0,
            'amount' => $amount
        ])->find();
        if($order){
            try {
                if($amount > 0){
                    $giftPackage = $this->where('id',$order->gift_package_id)->find();
                    $storeId = MemberModel::getStoreIdByUid($giftPackage->user_id);
                    //余额支付
                    if($pay_type == 0){
                        MemberModel::changeBalance($giftPackage->user_id,$order['amount'],2,8,0,$storeId,$order->gift_package_id);
                    }else{
                        //增加微信支付记录
                        MemberModel::wxPayRecord($giftPackage->user_id,$order['amount'],8,0,$storeId,$order->gift_package_id);
                    }
                    //平台充值
                    PlatformModel::changeBalance($amount,1,7,0,0,$giftPackage->user_id,0,$storeId,$order->gift_package_id);
                    //返利
                    MemberModel::changeBalance($order['rebate_user_id'],$order['amount'],1,3,0,0,$order->gift_package_id);
                }
                //领取卡券
                $this->receivePackage($order['gift_package_id'],$order['user_id']);

                $order->is_pay=1;
                $order->save();
                // 提交事务
                Db::commit();
                return true;
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                throw $e;
            }
        }
        Db::commit();
    }
}