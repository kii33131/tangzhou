<?php

namespace app\model;

use app\api\service\StoreService;
use app\api\service\Token as TokenService;
use app\exceptions\ApiException;

class UserDownloadCouponModel extends BaseModel
{
    protected $name = 'user_download_coupon';

    public function userInfo()
    {
        return $this->belongsTo('MemberModel','user_id')->bind('name,picture');
    }

    /**
     * 获取用户下载的卡券列表
     * @param $params
     * @param $uid
     * @param $listRows
     * @return \think\db\Query|\think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getListbyUser($params,$uid,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $userId = StoreService::getStoreUserId($uid);
        $coupons = self::alias('d')
            ->join('coupon c','d.coupon_id=c.id')->join('store s','c.store_id=s.id')
            ->where('d.user_id',$userId)
            ->where('d.is_delete',0);
        //判断卡券类型
        if(isset($params['type'])){
            $coupons = $coupons->where('c.type',$params['type']);
        }

        //筛选可使用卡券
        if(!empty($params['use']) && $params['use'] == 1){
            //筛选可使用卡券
            $coupons = $coupons->where([
                ['c.end_time' ,'>=',time()],
                ['d.stock' ,'>',0]
            ]);
        }else{
            //判断券是否过期
            if(isset($params['is_overdue'])){
                if($params['is_overdue'] == 1){//未过期
                    $coupons = $coupons->where('c.end_time','>=',time());
                }elseif ($params['is_overdue'] == 2){//已过期
                    $coupons = $coupons->where('c.end_time','<',time());
                }
            }
        }
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";

        $coupons = $coupons->field("d.id,c.id coupon_id,c.name,c.logo,s.name store_name,d.stock,c.end_time,
        c.original_price,c.buying_price,c.rebate_commission,c.promotion_commission,c.type{$field_distance}")
            ->order('d.id desc')
            ->paginate($listRows)
            ->each(function ($item){
                $item->end_time = date('Y-m-d',$item->end_time);
                $item->distance = round($item->distance,3);
            });

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
        $coupons = $this->alias('d')->join('coupon c','d.coupon_id=c.id')->join('store s','s.id=c.store_id')
            ->where('d.id',$params['id'])
            ->where('d.is_delete',0);
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";
        $coupons = $coupons->field("c.name,c.logo,s.name store_name,s.logo store_logo,s.store_mobile,s.longitude,s.latitude,s.exhibition,s.address,s.contacts,s.introduce,
        d.stock,c.rebate_commission,c.promotion_commission,c.original_price,c.buying_price,c.id,
        c.start_time,c.end_time,s.start_hours,s.end_hours,c.store_id,s.industry_category_id,c.instructions,c.type,c.state,d.total,c.pattern
        {$field_distance}")->find();
        if(!$coupons){
            throw new ApiException([
                'errorCode' => '40001',
                'msg' => '卡券不存在'
            ]);
        }

        $coupons->start_time = date('Y-m-d',$coupons->start_time);
        $coupons->end_time = date('Y-m-d',$coupons->end_time);
        $coupons->distance = round($coupons->distance,3);
        $coupons->exhibition = json_decode($coupons->exhibition,true);
        return $coupons;
    }

    /**
     * 卡券下载列表
     * @param $couponId
     * @param int $listsRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function downLoadLlist($couponId,$listsRows = 10){
        $coupons = $this->with('userInfo')
            ->where('coupon_id',$couponId)
            ->order('id desc')
            ->visible(['name','picture','create_time'])
            ->paginate($listsRows);
        return $coupons;
    }

    /**
     * 获取该门店下的卡券用户下载量
     * @param $storeId int 门店id
     * @return float
     */
    public static function userDownLoadCouponNum($storeId){
        return self::alias('d')
            ->join('coupon c','c.id = d.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->where('s.id',$storeId)
            ->sum('d.total');
    }

    /**
     * 获取该门店下的卡券用户下载列表
     * @param $storeId int 门店id
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function userDownLoadCouponList($storeId,$listRows = 10){
        return self::alias('d')
            ->join('coupon c','c.id = d.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->join('member m','m.id = d.user_id')
            ->field('m.name,m.picture,d.create_time,c.name coupon_name,d.total')
            ->where('s.id',$storeId)
            ->order('d.id desc')
            ->paginate($listRows);
    }
}