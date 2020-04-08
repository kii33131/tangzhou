<?php

namespace app\model;

use Exception;
use think\Db;

class UserCouponModel extends BaseModel
{
    protected $name = 'user_coupon';

    public function userInfo()
    {
        return $this->belongsTo('MemberModel','user_id')->bind('name,picture');
    }

    public function couponInfo(){
        return $this->belongsTo('CouponModel','coupon_id');
    }
    //获取下载券领取和核销列表
    public function getDownloadCouponReciveList($params,$limit = self::LIMIT,$user_id){

        $downloadcoupon=DownloadCouponModel::where(['coupon_id'=>$params['id'],'user_id'=>$user_id])->find();
        if(!$downloadcoupon){
            try{
                exception('下载卡券不存在','40001');
            }catch (Exception $e){
                error($e->getMessage(),$e->getCode());
            }
        }
        $coupons = $this->alias('u')->join('user_download_coupon d','d.id=u.download_coupon_id')->join('member m','m.id=u.user_id');
        if($params['pull_type']==3){
            $coupons = $coupons->where('u.state',2);
        }
        $coupons = $coupons->where('u.download_coupon_id',$downloadcoupon->id);

        $data= $coupons->field('m.picture portrait,m.name ,u.created_at as date_time')->order('date_time','desc')->paginate($limit);

        return $data;
    }
    //获取自己创建的券领取和核销列表
    public function getCouponReciveList($params,$limit = self::LIMIT,$store_id){
        $coupon=CouponModel::where(['id'=>$params['id'],'store_id'=>$store_id])->find();
        if(!$coupon){
            try{
                exception('这不是你的卡券','40001');
            }catch (Exception $e){
                error($e->getMessage(),$e->getCode());
            }
        }
        $coupons = $this->alias('u')->join('member m','m.id=u.user_id');
        if($params['pull_type']==3){
            $coupons = $coupons->where('u.state',2);
        }
        $coupons = $coupons->where('u.coupon_id',$params['id']);
        $data= $coupons->field('m.picture portrait,m.name ,u.created_at as date_time')->order('date_time','desc')->paginate($limit);
        return $data;
    }

    /**
     * 领取卡券
     * @param $userId 用户ID
     * @param $couponId 卡券ID
     * @param int $num 领取数量
     * @param int $downloadCouponId 下载券ID
     */
    public static function receiveCoupon($userId,$couponId,$num = 1,$downloadCouponId = 0){
        for ($i = 0;$i < $num;$i++){
            self::create([
                'user_id' => $userId,
                'receive_user_id' => $userId,
                'coupon_id' => $couponId,
                'download_coupon_id' => $downloadCouponId,
                'state' => 1,
                'residual_is_pay' => 1,
                'code' => self::generateCode()
            ]);
        }
    }

    /**
     * 获取用户领取的卡券
     * @param $params
     * @param $uid
     * @param $listRows
     * @return \think\db\Query|\think\Paginator
     */
    public static function getListbyUser($params,$uid,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $coupons = self::alias('uc')
            ->join('coupon c','uc.coupon_id=c.id')->join('store s','c.store_id=s.id')
            ->where('uc.user_id',$uid)
            ->where('uc.is_delete',0)
            ->where('uc.is_share',0);
        //判断卡券类型
        if(isset($params['type'])){
            switch ($params['type']){
                case 1:
                    $coupons = $coupons->where([
                        ['uc.state','=',1],
                        ['end_time','>=',time()]
                    ]);
                    break;
                case 2:
                    $coupons = $coupons->where([
                        ['uc.state','=',2]
                    ]);
                    break;
                case 3:
                    $coupons = $coupons->where([
                        ['end_time','<',time()]
                    ]);
                    break;
                case 4:
                    $coupons = $coupons->where([
                        ['uc.residual_is_pay','=',0],
                        ['end_time','>=',time()]
                    ]);
                    break;
            }
        }
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";

        $coupons = $coupons->field("count(*) num,uc.residual_is_pay,uc.id,uc.code,c.id coupon_id,c.name,c.logo,s.name store_name,c.original_price,
        c.start_time,c.end_time,c.buying_price,c.rebate_commission,c.type{$field_distance}")
            ->group('uc.coupon_id')
            ->order('uc.id desc')
            ->paginate($listRows)
            ->each(function ($item){
                $item->start_time = date('Y-m-d',$item->start_time);
                $item->end_time = date('Y-m-d',$item->end_time);
                $item->distance = round($item->distance,3);
            });
        return $coupons;

    }

    /**
     * 获取已领取的卡券用户
     * @param $id   自建券ID/下载券ID
     * @param $type 1：自建券   2：下载券
     * @param int $listRows
     * @return \think\Paginator
     */
    public function getReceiptList($id,$type,$listRows = 10){
        $coupons = $this;
        if($type == 1){
            $coupons = $coupons->where([
                'coupon_id' => $id
            ]);
        }elseif ($type == 2){
            $coupons = $coupons->where([
                'download_coupon_id' => $id
            ]);
        }
        return $coupons->with('userInfo')->visible(['name','picture','create_time'])->order('id','desc')->paginate($listRows);
    }

    /**
     * 获取已领取的卡券用户
     * @param $id   自建券ID/下载券ID
     * @param $type 1：自建券   2：下载券
     * @param int $listRows
     * @return \think\Paginator
     */
    public function getWriteOffList($id,$type,$listRows = 10){
        $coupons = $this->where('state',2);
        if($type == 1){
            $coupons = $coupons->where([
                'coupon_id' => $id
            ]);
        }elseif ($type == 2){
            $coupons = $coupons->where([
                'download_coupon_id' => $id
            ]);
        }
        return $coupons->with('userInfo')->visible(['name','picture','create_time'])->order('id','desc')->paginate($listRows);
    }

    /**
     * 生成核销码
     * @return string|null
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
     * 核销券详情
     * @param $id int 用户券id
     * @return array|PDOStatement|string|Model|null
     */
    public function writeOffDetail($id){
        $coupon = $this->alias('uc')
            ->field('c.name,c.logo,c.start_time,c.end_time,c.instructions,uc.code,uc.residual_is_pay,uc.state')
            ->join('coupon c','uc.coupon_id = c.id')
            ->where('uc.id',$id)
            ->where('uc.is_delete',0)
            ->find();
        if(!$coupon){
            error('优惠券不存在',40001);
        }
        if($coupon['state'] == 2){
            error('优惠券已被使用',40008);
        }
        if($coupon['start_time'] > time()){
            error('优惠券使用时间未开始',40005);
        }
        if($coupon['end_time'] < time()){
            error('优惠券已过期',40006);
        }
        if($coupon['residual_is_pay'] != 1){
            error('优惠券剩余金额未支付',40007);
        }
        $coupon->start_time = date('Y-m-d',$coupon->start_time);
        $coupon->end_time = date('Y-m-d',$coupon->end_time);
        return $coupon;
    }

    /**
     * 用户赠送卡券
     * @param $couponId int 卡券id
     * @param $num int 数量
     * @param $userId int 用户id
     * @return string|null
     * @throws Exception
     */
    public function giftCoupon($couponId,$num,$userId){
        Db::startTrans();
        try{
            //查询可分享数量
            $canShareNum = $this->lock(true)->where([
                'coupon_id'  => $couponId,
                'is_delete' => 0,
                'state' => 1,
                'user_id' => $userId,
                'is_share' => 0
            ])->count();
            if($canShareNum < $num){
                error('卡券数量不足',40003);
            }
            $this->where([
                'coupon_id'  => $couponId,
                'is_delete' => 0,
                'state' => 1,
                'user_id' => $userId,
                'is_share' => 0
            ])->limit($num)
                ->update([
                    'is_share' => 1
                ]);
            $code = GiftUserCouponModel::generateCode();
            GiftUserCouponModel::create([
                'coupon_id' => $couponId,
                'member_id' => $userId,
                'num' => $num,
                'stock' => $num,
                'code' => $code
            ]);
            Db::commit();
            return $code;
        }catch (Exception $e){
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 领取赠送的卡券
     * @param $code string 分享code
     * @param $userId int 用户id
     * @return bool
     * @throws Exception
     */
    public function receivingGiftCoupon($code,$userId){
        Db::startTrans();
        try {
            $giftUserCoupon = GiftUserCouponModel::lock(true)
                ->where('code',$code)
                ->find();
            if(!$giftUserCoupon){
                error('卡券不存在', 40001);
            }
            if($giftUserCoupon->member_id == $userId){
                error('自己不能领取自己的卡券',40010);
            }
            if($giftUserCoupon->stock < 1){
                error('卡券已被领取完', 40003);
            }
            if($giftUserCoupon->getData('create_time') < time() - config('api.coupon_expiration')){
                error('卡券已过期退还', 40003);
            }
            //用户只能领取一张
            $giftUserCouponOrder = GiftUserCouponOrderModel::where('gift_user_coupon_id',$giftUserCoupon->id)
                ->find();
            if($giftUserCouponOrder){
                error('已领取过当前卡券，不能重复领取', 40012);
            }
            $giftUserCoupon->setDec('stock');//减库存
            //领取到自己账户
            UserCouponModel::where([
                'is_share' => 1,
                'coupon_id' => $giftUserCoupon->coupon_id,
                'user_id' => $giftUserCoupon->member_id
            ])->limit(1)
                ->update([
                    'is_share' => 0,
                    'user_id' => $userId,
                    'code' => $this->generateCode()
                ]);
            //生成领取记录
            GiftUserCouponOrderModel::create([
                'gift_user_coupon_id' => $giftUserCoupon->id,
                'member_id' => $userId
            ]);
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 营销中心
     * @param $storeId int 门店id
     * @param int $listRows 分页数量
     * @return \think\Paginator
     */
    public function marketingCenter($storeId,$listRows = 10){
        $coupons = $this->alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('member m','m.id = uc.user_id')
            ->field('m.name,m.picture,uc.create_time,c.name coupon_name')
            ->where('c.store_id',$storeId)
            ->order('uc.id desc')
            ->paginate($listRows);
        return $coupons;
    }

    /**
     * 获取该门店下的卡券领取量
     * @param $storeId int 门店id
     * @return float|string
     */
    public static function receiveCouponNum($storeId){
        return self::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->where('s.id',$storeId)
            ->count();
    }

    /**
     * 获取该门店下的卡券领取列表
     * @param $storeId int 门店id
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function receiveCouponList($storeId,$listRows = 10){
        return self::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->join('member m','m.id = uc.receive_user_id')
            ->group('uc.coupon_id,uc.receive_user_id')
            ->where('s.id',$storeId)
            ->field('m.name,m.picture,uc.create_time,c.name coupon_name,count(uc.id) num')
            ->order('uc.id desc')
            ->paginate($listRows);
    }

    /**
     * 获取该门店下的卡券使用量
     * @param $storeId int 门店id
     * @return float|string
     */
    public static function useCouponNum($storeId){
        return self::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->where('uc.state',2)
            ->where('s.id',$storeId)
            ->count();
    }

    /**
     * 获取该门店下的使用列表
     * @param $storeId int 门店id
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function useCouponNumList($storeId,$listRows = 10){
        return self::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->join('member m','m.id = uc.receive_user_id')
            ->where('s.id',$storeId)
            ->where('uc.state',2)
            ->field('m.name,m.picture,uc.write_off_time,c.name coupon_name')
            ->order('uc.id desc')
            ->paginate($listRows)->each(function($item, $key){
                $item->write_off_time = date('Y-m-d h:i:s',$item->write_off_time);
            });

    }
}