<?php

namespace app\model;

use app\api\service\StoreService;
use app\exceptions\ApiException;
use think\Db;

class GiftCouponModel extends BaseModel
{
    protected $name = 'gift_coupon';

    /**
     * 分享卡券
     * @param $id int 卡券id
     * @param $num int 分享数量
     * @param $uid int 用户id
     * @return bool
     * @throws \Exception
     */
    public function shareCoupon($id,$num,$uid){
        Db::startTrans();
        try{
            $coupon = CouponModel::where([
                'id' => $id,
                'type' => 1
            ])->find();
            if(!$coupon){
                throw new ApiException([
                    'msg' => '卡券不存在',
                    'errorCode' => 40001
                ]);
            }
            if($coupon->stock < $num){
                throw new ApiException([
                    'msg' => '卡券数量不足',
                    'errorCode' => 40003
                ]);
            }
            $userId = MemberModel::getUidByStoreId($coupon->store_id,true);
            if($userId != StoreService::getStoreUserId($uid)){
                throw new ApiException([
                    'msg' => '无权限分享他人卡券',
                    'errorCode' => 40011
                ]);
            }
            $this->save([
                'coupon_id' => $id,
                'store_id' => $coupon->store_id,
                'num' => $num,
                'stock' => $num,
                'code' => $this->generateCode()
            ]);
            //减库存
            $coupon->setDec('stock',$num);
            Db::commit();
            return $this->code;
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 分享下载的卡券
     * @param $id int 卡券id
     * @param $num int 分享数量
     * @param $uid int 用户id
     * @return bool
     * @throws \Exception
     */
    public function shareDownloadCoupon($id,$num,$uid){
        Db::startTrans();
        try{
            $downloadCoupon = UserDownloadCouponModel::where('id',$id)->find();
            if(!$downloadCoupon){
                throw new ApiException([
                    'msg' => '卡券不存在',
                    'errorCode' => 40001
                ]);
            }
            $coupon = CouponModel::where([
                'id' => $downloadCoupon->coupon_id,
                'type' => 1
            ])->find();
            if(!$coupon){
                throw new ApiException([
                    'msg' => '卡券不存在',
                    'errorCode' => 40001
                ]);
            }
            if($downloadCoupon->stock < $num){
                throw new ApiException([
                    'msg' => '卡券数量不足',
                    'errorCode' => 40003
                ]);
            }
            if(StoreService::getStoreUserId($uid) != $downloadCoupon->user_id){
                throw new ApiException([
                    'msg' => '无权限分享他人卡券',
                    'errorCode' => 40011
                ]);
            }
            $this->save([
                'coupon_id' => $downloadCoupon->coupon_id,
                'download_coupon_id' => $downloadCoupon->id,
                'store_id' => $coupon->store_id,
                'num' => $num,
                'stock' => $num,
                'code' => $this->generateCode()
            ]);
            //减库存
            $downloadCoupon->setDec('stock',$num);
            Db::commit();
            return $this->code;
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 生成分享码
     */
    public static function generateCode(){
        $code = getRandChar(20);
        if(self::where('code',$code)->find()){
            return self::generateCode();
        }else{
            return $code;
        }
    }

    /**
     * 还原分享库存
     */
    public function releaseInventory(){
        self::where([
            ['is_expiration','=',0],
            ['create_time','<',time() - 86400]
        ])->chunk(100,function ($items){
            Db::startTrans();
            try {
                foreach ($items as $key=>$val){
                    if(UserCouponOrderModel::where([
                        'gift_coupon_id' => $val->id,
                        'is_expiration' => 0
                    ])->find()){
                        continue;
                    }
                    self::where(['id'=>$val->id])->update(['is_expiration'=>1]);
                    if($val->stock > 0){
                        if($val['download_coupon_id']){
                            //释放下载券库存
                            UserDownloadCouponModel::where(['id'=>$val->download_coupon_id])
                                ->setInc('stock',$val->stock);
                        }else{
                            //释放自建券库存
                            CouponModel::where(['id'=>$val->coupon_id])
                                ->setInc('stock',$val->stock);
                        }
                    }
                }
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                throw $e;
            }
        });
    }


}