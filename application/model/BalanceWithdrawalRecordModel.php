<?php

namespace app\model;

class BalanceWithdrawalRecordModel extends BaseModel
{
    protected $name = 'balance_withdrawal_record';

    public function getList($params, $limit = self::LIMIT)
    {
        $lists = $this->alias('b')
            ->join('member m','m.id= b.member_id')
            ->field('b.*,m.name');

        if (isset($params['name'])) {
            $lists = $lists->whereLike('m.name', '%'.$params['name'].'%');
        }

        if (isset($params['member_id'])) {
            $lists = $lists->whereLike('b.member_id', $params['member_id']);
        }

        if (isset($params['type'])) {
            if(is_array($params['type'])){
                $lists = $lists->where('b.type','in', $params['type']);
            }else{
                $lists = $lists->where('b.type', $params['type']);
            }
        }
        return $lists->order('b.id desc')->paginate($limit, false, ['query' => request()->param()]);
    }

}