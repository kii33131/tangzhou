<?php

namespace app\model;

class StoreModel extends BaseModel
{
    protected $name = 'store';
    protected $type = [
        'exhibition'    =>  'array',
        'entry_time' => 'timestamp:Y-m-d h:i:s',
        'apply_time' => 'timestamp:Y-m-d h:i:s'
    ];
    /**
     * 行业分类关联
     * @return \think\model\relation\BelongsTo
     */
    public function industryCategory()
    {
        return $this->belongsTo('IndustryCategoryModel');
    }

    /**
     * 推荐人关联
     * @return \think\model\relation\BelongsTo
     */
    public function recommender()
    {
        return $this->belongsTo('MemberModel','recommender_id');
    }

    public function getStartHoursAttr($value)
    {
        return substr($value,0,-3);
    }

    public function getEndHoursAttr($value)
    {
        return substr($value,0,-3);
    }


    /**
     * 距离排序
     * @param $longitude
     * @param $latitude
     */
    public static function distanceSort($storeId,$lng,$lat,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $store = StoreModel::get($storeId);
        $result=self::field("id,name,industry_category_id,logo,start_hours,end_hours,(2 * $EARTH* ASIN(SQRT(POW(SIN($PI*(".$lat."-latitude)/360),2)+COS($PI*".$lat."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$lng."-longitude)/360),2)))) as distance")
            ->where([
                'state' => 2,
                'is_pay' => 1,
                'is_delete' => 0
            ])
            ->where('industry_category_id','<>',$store->industry_category_id) //过滤同行业门店
            ->whereOr('id','=',$storeId)
            ->order('distance asc')
            ->paginate($listRows);
        return $result;
    }


    /**
     * Store List
     *
     * @param $params
     * @return \think\Paginator
     */
    public function getList($params, $limit = self::LIMIT)
    {
        $store = $this->where([
            'is_delete' => 0,
            'is_pay' => 1
        ]);
        if(isset($params['state'])){
            $store = $store->where('state',$params['state']);
        }

        if (isset($params['name'])) {
            $store = $this->whereLike('name', '%'.$params['name'].'%');
        }

        if(!empty($params['province'])) {
            $store = $store->where('province',$params['province']);
        }

        if(!empty($params['city'])) {
            $store = $store->where('city',$params['city']);
        }

        if(!empty($params['district'])) {
            $store = $store->where('district',$params['district']);
        }

        if(!empty($params['entry_time'])) {
            $entryTime = explode(' - ',$params['entry_time']);
            if(count($entryTime) == 2){
                $entryTime[0] = strtotime($entryTime[0]);
                $entryTime[1] = strtotime($entryTime[1]) + 86400;
                $store = $store->where([
                    ['entry_time','>=',$entryTime[0]],
                    ['entry_time','<',$entryTime[1]]
                ]);
            }
        }

        return $store->with('industryCategory,recommender')
            ->order('id desc')
            ->paginate($limit, false, ['query' => request()->param()])->each(function ($item,$key){
                //统计卡券发布数量
                $item->couponTotal = CouponModel::where('store_id',$item->id)->sum('total');
                //统计卡券领取数量
                $item->couponReceiveNum = UserCouponModel::alias('uc')
                    ->join('coupon c','c.id = uc.coupon_id')
                    ->where('c.store_id',$item->id)
                    ->count();
                //统计卡券核销数量
                $item->couponWriteOffNum = UserCouponModel::alias('uc')
                    ->join('coupon c','c.id = uc.coupon_id')
                    ->where('c.store_id',$item->id)
                    ->where('uc.state',2)
                    ->count();
                //统计卡券下载数量
                $item->couponDownNum = UserDownloadCouponModel::alias('udc')
                    ->join('coupon c','c.id = udc.coupon_id')
                    ->where('c.store_id',$item->id)
                    ->sum('udc.total') - UserDownloadCouponModel::alias('udc')
                        ->join('coupon c','c.id = udc.coupon_id')
                        ->where('c.store_id',$item->id)
                        ->sum('udc.stock');
                $member = MemberModel::get($item->user_id);
                $item->balance = $member->balance ?? '';
                $item->integral = $member->integral ?? '';
                $item->member_id = $item->user_id;
            });
    }

    /**
     * Store List
     *
     * @param $params
     * @return \think\Paginator
     */
    public function getAllList($params, $limit = self::LIMIT)
    {
        $store = $this->where([
            'is_pay' => 1
        ]);
        if(isset($params['state'])){
            $store = $store->where('state',$params['state']);
        }

        if (isset($params['name'])) {
            $store = $this->whereLike('name', '%'.$params['name'].'%');
        }

        foreach (['province','city','district'] as $val){
            if(!empty($params[$val])){
                $store = $store->where($val,$params[$val]);
            }
        }
        return $store->with('industryCategory,recommender')->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }
}