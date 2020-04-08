<?php

namespace app\model;

use think\Db;

class UserCouponOrderModel extends BaseModel
{
    protected $name = 'user_coupon_order';

    //释放过期未支付订单库存
    public function releaseInventory(){
        $order = self::where([
            'is_expiration' => 0
        ]);
        $order = $order->where('expiration_time','<',time());
        $order->chunk(100,function($orders){
            Db::startTrans();
            try {
                foreach ($orders as $k=>$v){
                    self::where(['id'=>$v->id])->update(['is_expiration' => 1]);
                    if($v->state == 0){
                        GiftCouponModel::where('id',$v->gift_coupon_id)->setInc('stock');
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