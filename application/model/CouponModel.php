<?php

namespace app\model;

use app\api\controller\User;
use app\api\service\StoreService;
use app\api\service\Token as TokenService;
use app\api\service\UserToken;
use app\api\service\WechatPay;
use app\exceptions\ApiException;
use think\Db;

class CouponModel extends BaseModel
{
    protected $name = 'coupon';
    protected $type = [
        'start_time'    =>  'timestamp:Y-m-d'
    ];
    /**
     * 门店关联
     * @return \think\model\relation\BelongsTo
     */
    public function storeInfo()
    {
        return $this->belongsTo('StoreModel');
    }

    public function getEndTimeAttr($value){
        return date('Y-m-d',$value);
    }
    public function setEndTimeAttr($value)
    {
        return strtotime(date('Y-m-d',strtotime($value))) + 86399;
    }
    /**
     * 搜索优惠券
     * @param $params
     * @param $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function searchCoupon($params,$storeId,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $coupons = $this->alias('c')->join('store s','s.id=c.store_id')
            ->where('c.state',3)
            ->where('c.is_delete',0)
            ->where('c.stock','>',0)
            ->where('s.is_delete',0)
            ->where('s.state',2)
            ->where('c.stock','>',0)
            ->whereTime('c.end_time', '>=', time());

        //名称搜索
        if(!empty($params['name'])){
            $coupons = $coupons->whereLike('c.name', '%'.$params['name'].'%');
        }

        //筛选门店
        if(!empty($params['store_id'])){
            $coupons = $coupons->where('c.store_id',$params['store_id']);
        }else{
            //过滤同行业券
            $store = StoreModel::get($storeId);
            $coupons = $coupons->where('s.industry_category_id <> :industry_category_id or s.id = :store_id',[
                'industry_category_id' => $store->industry_category_id,
                'store_id' => $storeId
            ]);
        }

        //筛选行业分类
        if(!empty($params['industry_category_id'])){
            $industryCategory = IndustryCategoryModel::where('id',$params['industry_category_id'])->find();
            if($industryCategory){
                if($industryCategory->pid != 0){
                    //筛选具体行业卡券
                    $coupons = $coupons->where('s.industry_category_id',$params['industry_category_id']);
                }else{
                    //筛选一级行业下所有卡券
                    $coupons = $coupons->where('s.industry_category_id', 'IN', function ($query) use($industryCategory){
                        $query->name('industry_category')->where('pid', $industryCategory->id)->field('id');
                    });
                }
            }
        }

        //筛选卡券类型
        if(empty($params['type'])){
            $params['type'] = 1;
        }
        $coupons = $coupons->where('c.type',$params['type']);

        //筛选地区
        if(!empty($params['province'])){
            $coupons = $coupons->where('s.province',$params['province']);
        }
        if(!empty($params['city'])){
            $coupons = $coupons->where('s.city',$params['city']);
        }
        if(!empty($params['district'])){
            $coupons = $coupons->where('s.district',$params['district']);
        }


        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";
        return $coupons->field("c.id,c.name,c.logo,s.name store_name,c.stock,c.rebate_commission,c.end_time,
        c.promotion_commission,c.original_price,c.buying_price{$field_distance}")
            ->order('distance asc')
            ->paginate($listRows)
            ->each(function ($item){
                $item->distance = round($item->distance,3);
            });
    }

    /**
     * 根据UID查询已发布的优惠券
     * @param $uid
     * @param $type
     * @param $listRows
     * @return \think\Paginator
     * @throws ApiException
     * @throws \think\exception\DbException
     */
    public static function releaseCouponByUid($uid,$data,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $field_distance = '';
        if(!empty($data['latitude']) && !empty($data['longitude'])){
            $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$data['latitude']."-latitude)/360),2)+COS($PI*".$data['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$data['longitude']."-longitude)/360),2)))) as distance";
        }
        $storeId = StoreService::getStoreId($uid);
        $coupons = self::alias('c')
            ->field("c.id,c.name,c.logo,s.name store_name,c.stock,c.rebate_commission,c.end_time,
            c.promotion_commission,c.original_price,c.buying_price,c.state{$field_distance}")
            ->join('store s','s.id=c.store_id')
            ->where('c.type',$data['type'])
            ->where('c.store_id',$storeId)
            ->where('c.is_delete',0);
        //筛选可使用卡券
        if(!empty($data['use']) && $data['use'] == 1){
            $coupons = $coupons->where([
                ['c.state','=',3],
                ['c.end_time' ,'>=',time()],
                ['c.stock' ,'>',0]
            ]);
        }
        $coupons = $coupons->order('c.id desc')->paginate($listRows)->each(function ($item){
            //已过期状态为6
            if($item->getData('end_time') < time()){
                $item->state = 6;
            }
        });
        if(isset($data['latitude']) && isset($data['longitude'])){
            $coupons = $coupons->each(function ($item){
                $item->distance = round($item->distance,3);
            });
        }

        return $coupons;
    }

    /**
     * 优惠券详情
     * @param $params
     * @return array|\PDOStatement|string|\think\db\Query|\think\Model|null
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($params){
        $coupons= $this->detailCommon($params);
        $coupons->distance = round($coupons->distance,3);
        $coupons->start_hours = substr($coupons->start_hours,0,-3);
        $coupons->end_hours = substr($coupons->end_hours,0,-3);
        $industryCategory = IndustryCategoryModel::getSecondLevelName($coupons->industry_category_id);
        $coupons->category_name_p = $industryCategory['category_name_p'];
        $coupons->category_name_s = $industryCategory['category_name_s'];
        $coupons->is_collection = UserCollectionModel::isCollection(TokenService::getCurrentUid(),$coupons->store_id);
        return $coupons;
    }

    public function detailCommon($params){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $coupons = $this->alias('c')->join('store s','s.id=c.store_id')
            ->where('c.id',$params['id'])
            ->where('c.is_delete',0);
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";
        $coupons = $coupons->field("c.name,c.logo,s.name store_name,s.logo store_logo,s.store_mobile,s.longitude,s.latitude,s.exhibition,s.address,s.contacts,s.introduce,s.introduce,
        c.stock,c.rebate_commission,c.promotion_commission,c.original_price,c.buying_price,c.id,
        c.start_time,c.end_time,s.start_hours,s.end_hours,c.store_id,s.industry_category_id,c.instructions,c.type,c.state,c.total,c.pattern
        {$field_distance}")->find();
        if(!$coupons){
            throw new ApiException([
                'errorCode' => '40001',
                'msg' => '卡券不存在'
            ]);
        }
        $coupons->distance = round($coupons->distance,3);
        $coupons->exhibition = json_decode($coupons->exhibition,true);
        return $coupons;
    }

    /**
     * Coupon List
     *
     * @param $params
     * @return \think\Paginator
     */
    public function getList($params, $limit = self::LIMIT)
    {
        $coupons = $this->alias('c')
            ->join('store s','s.id=c.store_id')
            ->where('c.state','<>',0)
            ->where('c.is_delete',0);

        if(!empty($params['province'])){
            $coupons = $coupons->where([
                's.province' =>$params['province'],
                's.city' => $params['city'],
                's.district' => $params['district']
            ]);
        }
        if (isset($params['store_id'])) {
            $coupons = $coupons->where('c.store_id', $params['store_id']);
        }
        if (isset($params['name'])) {
            $coupons = $coupons->whereLike('c.name', '%'.$params['name'].'%');
        }
        if(!empty($params['type'])){
            $coupons = $coupons->where('c.type' , $params['type']);
        }
        if(!empty($params['industry_category_id'])){
            $coupons = $coupons->where('s.industry_category_id' , $params['industry_category_id']);
        }
        if(!empty($params['state'])){
            $coupons = $coupons->where('c.state' , $params['state']);
        }
        return $coupons->field('c.*,s.name store_name,s.longitude,s.latitude')
            ->order('c.id desc')
            ->paginate($limit, false, ['query' => request()->param()])->each(function ($item,$key){
                //卡券领取数量
                $item->couponReceiveNum = UserCouponModel::where('coupon_id',$item->id)->count();
                //卡券核销数量
                $item->couponWriteOffNum = UserCouponModel::where([
                    'coupon_id' => $item->id,
                    'state' => 2
                ])->count();
            });
    }

    /**
     * 用户领取优惠券预支付订单
     * @param int $code 分享id
     * @param int $userId   用户ID
     * @return mixed
     */
    public function userReceive($code,$userId){
        //判断是否已有订单
        $result = [
            'pay' => 0,
            'data' => ''
        ];//待返回数据
        $payOrder = $this->isHasPayOrder($code,$userId);
        if($payOrder !== false){
            $result['pay'] = 1;
            $result['data'] = $payOrder;
            return $result;
        }
        Db::startTrans();
        try{
            $giftCoupon = GiftCouponModel::lock(true)->where('code',$code)->find();
            if(!$giftCoupon){
                error('卡券不存在',40001);
            }
            if($giftCoupon->stock < 1){
                error('卡券已被领取完',40003);
            }
            if($giftCoupon->getData('create_time') < time() - config('api.coupon_expiration')){
                error('卡券已过期退还', 40003);
            }
            $giftCoupon->setDec('stock');//扣除库存
            //判断是否已领取当前卡券
            $userCoupon = UserCouponModel::where([
                'coupon_id' => $giftCoupon->coupon_id,
                'user_id' => $userId
            ])->find();
            if($userCoupon){
                error('已领取过当前卡券，不能重复领取',40012);
            }
            //获取后台配置用户领取卡券只支付1块钱其余线下支付
            $couponAmount = ConfigModel::getParam('coupon_amount');
            $orderNo = WechatPay::generateOrderNo();
            $result['order_no'] = $orderNo;
            $result['amount'] = $couponAmount;
            //创建预支付订单
            $order = UserCouponOrderModel::create([
                'member_id' => $userId,
                'coupon_id' => $giftCoupon->coupon_id,
                'download_coupon_id' => $giftCoupon->download_coupon_id,
                'gift_coupon_id' => $giftCoupon->id,
                'amount' => $couponAmount,
                'state' => 0,
                'order_no' => $orderNo,
                'expiration_time' => time() + 5 * 60,//订单过期时间5分钟
                'is_expiration' => 0
            ]);
            $result['pay'] = 1;
            $result['data'] = $order;
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return $result;
    }

    //是否存在未支付订单
    public function isHasPayOrder($code,$userId){
        $giftCoupon = GiftCouponModel::where('code',$code)->find();
        if(!$giftCoupon){
            error('卡券不存在',40001);
        }
        $userCouponOrder = UserCouponOrderModel::where([
            ['gift_coupon_id','=',$giftCoupon->id],
            ['member_id','=',$userId],
            ['state','=',0],
            ['is_expiration','=',0],
            ['expiration_time','>',time() + 30]
        ])->find();
        if($userCouponOrder){
            return $userCouponOrder;
        }else{
            return false;
        }
    }

    /**
     * 领取优惠券到卡包
     * @param $orderNo  订单号
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function userReceiveCoupon($orderNo,$payType = 1){
        $order = UserCouponOrderModel::lock(true)->where([
            'order_no' => $orderNo,
            'state' => 0
        ])->find();
        if(!$order){
            exception('订单不存在');
        }
        if($payType == 2){
            //增加微信消费记录
            MemberModel::wxPayRecord($order['member_id'],$order['amount'],10,$order['coupon_id']);
            //平台充值
            PlatformModel::changeBalance($order['amount'],1,5,0,0,$order['member_id'],$order['coupon_id']);
        }

        $order->state=1;
        $order->save();
        $coupon = self::where('id',$order->coupon_id)->find();
        if(!$coupon){
            exception('卡券不存在');
        }
        $residualAmount = $coupon->buying_price - $order->amount;//剩余支付金额
        $residualIsIay = 0;//剩余金额是否支付
        if($residualAmount <= 0){
            $residualAmount = 0;
            $residualIsIay = 1;
        }
        UserCouponModel::create([
            'coupon_id' => $order->coupon_id,
            'user_id' => $order->member_id,
            'receive_user_id' => $order->member_id,
            'state' => 1,
            'download_coupon_id' => $order->download_coupon_id,
            'amount' => $order->amount,
            'residual_amount' => $residualAmount,
            'residual_is_pay' => $residualIsIay,
            'code' => UserCouponModel::generateCode(),
            'gift_coupon_id' => $order->gift_coupon_id
        ]);
    }

    /**
     * 门店发布的卡券数量
     * @param $storeId int 门店id
     * @return float
     */
    public static function releaseCouponNum($storeId){
        return self::where('store_id',$storeId)
            ->where('is_delete',0)
            ->sum('total');
    }

    /**
     * 门店发布的卡券列表
     * @param $storeId
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function releaseCouponList($storeId,$listRows = self::LIMIT){
        $storeName = StoreModel::where('id',$storeId)->value('name');
        return self::where('store_id',$storeId)
            ->where('total','>',0)
            ->where('is_delete','=',0)
            ->order('id desc')
            ->paginate($listRows)->each(function ($item) use($storeName){
                //已过期状态改为6
                if($item->getData('end_time') < time()){
                    $item->state = 6;
                }
                $item->store_name = $storeName;
            });
    }
}