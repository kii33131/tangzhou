<?php

namespace app\model;



class GiftPackageDetailModel extends BaseModel
{
    protected $name = 'gift_package_detail';


    public function detailedList($params,$listRows){
        $EARTH=6378.137; //地球半径
        $PI=3.1415926535898; //PI值
        $coupons = $this->alias('d')->join('coupon c','d.coupon_id=c.id')->join('store s','c.store_id=s.id');
        $coupons = $coupons->where('d.gift_package_id',$params['id']);
        $field_distance = ",(2 * {$EARTH}* ASIN(SQRT(POW(SIN($PI*(".$params['latitude']."-latitude)/360),2)+COS($PI*".$params['latitude']."/180)* COS(latitude * $PI/180)*POW(SIN($PI*(".$params['longitude']."-longitude)/360),2)))) as distance";
        $coupons = $coupons->field("c.id,c.name,c.logo,s.name store_name,d.num stock,c.original_price,c.buying_price,
        c.end_time,c.rebate_commission,c.promotion_commission,d.num,c.type,c.pattern{$field_distance}")
            ->paginate($listRows)
            ->each(function ($item){
                $item->end_time = date('Y-m-d',$item->end_time);
                $item->distance = round($item->distance,3);
            });

        return $coupons;

    }


}