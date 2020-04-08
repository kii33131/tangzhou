<?php

namespace app\model;

use app\enum\BalanceRecordEnum;

class BalanceRecordModel extends BaseModel
{
    protected $name = 'balance_record';

    public function storeInfo()
    {
        return $this->belongsTo('StoreModel');
    }

    public function couponInfo()
    {
        return $this->belongsTo('CouponModel');
    }

    public function getStateAttr($value,$data)
    {
        $status = BalanceRecordEnum::STATE;
        if(array_key_exists($value,$status)){
            return $status[$value];
        }else{
            return '';
        }
    }

    public function getPayTypeAttr($value,$data)
    {
        $payTypes = BalanceRecordEnum::PAY_TYPE;
        if(array_key_exists($value,$payTypes)){
            return $payTypes[$value];
        }else{
            return '';
        }
    }

    public function getList($params, $limit = self::LIMIT)
    {
        $lists = $this->alias('b')
            ->join('member m','m.id= b.member_id')
            ->field('b.*,m.name');

        if (isset($params['state'])) {
            if(is_array($params['state'])){
                $lists = $lists->where('b.state','in',$params['state']);
            }else{
                $lists = $lists->where('b.state',$params['state']);
            }
        }

        if (isset($params['member_id'])) {
            $lists = $lists->where('b.member_id',$params['member_id']);
        }

        if (isset($params['type'])) {
            $lists = $lists->where('b.type',$params['type']);
        }

        if (isset($params['pay_type'])) {
            $lists = $lists->where('b.pay_type',$params['pay_type']);
        }
        return $lists->order('b.id desc')->paginate($limit, false, ['query' => request()->param()]);
    }
}