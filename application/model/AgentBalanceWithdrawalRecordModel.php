<?php

namespace app\model;

class AgentBalanceWithdrawalRecordModel extends BaseModel
{
    protected $name = 'agent_balance_withdrawal_record';

/*    public function getList($params, $limit = self::LIMIT)
    {
        $lists = $this;
        if(!empty($params['agent_id'])){
            $lists = $lists->where('agent_id',$params['agent_id']);
        }
        return $lists->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }*/

    public function getList($params, $limit = self::LIMIT)
    {
        $lists = $this->alias('b')
            ->join('agent a','a.id= b.agent_id')
            ->field('b.*,a.name');

        if (isset($params['name'])) {
            $lists = $lists->whereLike('a.name', '%'.$params['name'].'%');
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