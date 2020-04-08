<?php

namespace app\model;

class PlatformBalanceRecordModel extends BaseModel
{
    protected $name = 'platform_balance_record';

    public function userInfo(){
        return $this->belongsTo('UserModel','user_id');
    }

    public function storeInfo(){
        return $this->belongsTo('StoreModel','store_id');
    }

    public function couponInfo(){
        return $this->belongsTo('CouponModel','coupon_id');
    }

    public function getAllList($params = [], $limit = self::LIMIT)
    {
        $record = $this;
        $where = [];
        if(isset($params['type'])){
            $where[] = ['type','=',$params['type']];
        }

        if(isset($params['state'])){
            $where[] = ['state','=',$params['state']];
        }

        if(!empty($params['date'])) {
            $date = explode(' - ',$params['date']);
            if(count($date) == 2){
                $date[0] = strtotime($date[0]);
                $date[1] = strtotime($date[1]) + 86400;
                $where[] = ['create_time','>=',$date[0]];
                $where[] = ['create_time','<',$date[1]];
            }
        }
        $records = $this->where($where)->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
        $amount = $this->where($where)->where('type',1)->sum('amount') - $this->where($where)->where('type',2)->sum('amount');
        $records->amount = $amount;
        return $records;
    }
}