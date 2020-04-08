<?php

namespace app\model;

class DownloadCouponModel extends BaseModel
{
    protected $name = 'user_download_coupon';

    public function getList($params, $limit = self::LIMIT,$user_id){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $coupons = $this->alias('d')->join('coupon c','d.coupon_id=c.id')->join('store s','c.store_id=s.id')
            ->where('c.state',3);
        if(isset($params['type'])){
            $coupons = $coupons->where('c.type',$params['type']);
        }
        $coupons = $coupons->where('c.start_time','<=',time());
        $coupons = $coupons->where('d.user_id',$user_id);

        if(isset($params['is_overdue']) && $params['is_overdue']==1){
            $coupons = $coupons->where('c.end_time','<=',time());
        }
        if(isset($params['is_overdue']) && $params['is_overdue']==2){
            $coupons = $coupons->where('c.end_time','>=',time());
        }
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";

        $coupons = $coupons->field("c.id,c.name,c.logo,s.name store_name,d.num stock,c.original_price,c.buying_price,c.type{$field_distance}")
            ->order('distance asc')
            ->paginate($limit)
            ->each(function ($item){
                $item->distance = round($item->distance,3);
            });

        return $coupons;

    }

    public function downLoadLlist($coupon_id,$limit = self::LIMIT){
        $coupons = $this->alias('d')->join('member m','m.id=d.user_id');
        $coupons = $coupons->where('d.coupon_id',$coupon_id);
        $list=$coupons->field('m.picture portrait,m.name ,d.created_at as date_time')->order('date_time','desc')->paginate($limit);
        return $list;
    }


}