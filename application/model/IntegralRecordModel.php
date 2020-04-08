<?php

namespace app\model;

use app\enum\IntegralRecordEnum;

class IntegralRecordModel extends BaseModel
{
    protected $name = 'integral_record';


    /**
     * 用户关联
     * @return \think\model\relation\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo('MemberModel');
    }

    public function getModeAttr($value){
        $modes = IntegralRecordEnum::MODE;
        if(array_key_exists($value,$modes)){
             return $modes[$value];
        }else{
            return '';
        }
    }

    public function getCreateTimeAttr($value){
        return date('Y-m-d H:i:s',$value);
    }
    /**
     * Records List
     *
     * @time at 2018年11月14日
     * @param $params
     * @return \think\Paginator
     */
    public function getList($params, $limit = self::LIMIT)
    {
        $records = $this->alias('r')->join('store s','s.id=r.store_id');
        if(isset($params['type'])){
            $records = $records->where('r.type',$params['type']);
        }
        if(isset($params['mode'])){
            $records = $records->where('r.mode',$params['mode']);
        }

        if(isset($params['member_id'])){
            $records = $records->where('r.member_id',$params['member_id']);
        }

        if (isset($params['name'])) {
            $records = $records->whereLike('s.name', '%'.$params['name'].'%');
        }
        foreach (['province','city','district'] as $val){
            if(!empty($params[$val])){
                $records = $records->where('s.' . $val,$params[$val]);
            }
        }
        return $records->order('r.id desc')->paginate($limit, false, ['query' => request()->param()]);
    }


}