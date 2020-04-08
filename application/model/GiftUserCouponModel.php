<?php

namespace app\model;

use app\exceptions\ApiException;
use think\Db;

class GiftUserCouponModel extends BaseModel
{
    protected $name = 'gift_user_coupon';

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
                    self::where(['id'=>$val->id])->update(['is_expiration'=>1]);
                    if($val->stock > 0){
                        UserCouponModel::where([
                            'coupon_id'  => $val->coupon_id,
                            'is_delete' => 0,
                            'state' => 1,
                            'user_id' => $val->member_id,
                            'is_share' => 1
                        ])->limit($val->stock)
                            ->update([
                                'is_share' => 0
                            ]);
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