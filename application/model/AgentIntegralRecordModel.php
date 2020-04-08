<?php

namespace app\model;

class AgentIntegralRecordModel extends BaseModel
{
    protected $name = 'agent_integral_record';

    public function storeInfo(){
        return $this->belongsTo('storeModel');
    }

    public function getAllList($params = [], $limit = self::LIMIT)
    {
        $record = $this;
        if(isset($params['state'])){
            $record = $this->where([
                'state' => $params['state']
            ]);
        }
        if(isset($params['agent_id'])){
            $record = $this->where([
                'agent_id' => $params['agent_id']
            ]);
        }
        if(isset($params['type'])){
            $record = $this->where([
                'type' => $params['type']
            ]);
        }
        return $record->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }

}