<?php

namespace app\model;

class AgentModel extends BaseModel
{
    protected $name = 'agent';

    public function memberInfo()
    {
        return $this->belongsTo('MemberModel');
    }


    public static function changeBalance($agentId,$amount,$type,$state = 0,$storeId = 0){
        $agent = AgentModel::where('id',$agentId)->find();
        if(!$agent){
            error('代理点不存在',70000);
        }
        if($type == 1){
            $residualAmount = $agent['balance'] + $amount;
            self::where(['id' => $agentId])->setInc('balance',$amount);
        }else{
            $residualAmount = $agent['balance'] - $amount;
            if($agent['balance'] < $amount){
                error('代理点余额不足',70002);
            }
            self::where(['id' => $agentId])->setDec('balance',$amount);
        }
        AgentBalanceRecordModel::create([
            'agent_id' => $agentId,
            'amount' => $amount,
            'residual_amount' => $residualAmount,
            'type' => $type,
            'state' => $state,
            'store_id' => $storeId
        ]);
        return true;
    }

    public static function changeIntegral($agentId,$integral,$type,$state = 0,$storeId = 0){
        $agent = self::where('id',$agentId)->find();
        if(empty($agent)){
            error('代理点不存在',70000);
        }
        if($type == 1){
            $residual_integral = $agent['integral'] + $integral;
            self::where(['id' => $agentId])->setInc('integral',$integral);
        }else{
            $residual_integral = $agent['integral'] - $integral;
            self::where(['id' => $agentId])->setDec('integral',$integral);
        }
        AgentIntegralRecordModel::create([
            'agent_id' => $agentId,
            'integral' => $integral,
            'residual_integral' => $residual_integral,
            'type' => $type,
            'state' => $state,
            'store_id' => $storeId
        ]);
        return true;
    }



    //区域代理是否存在
    public static function regionIsExist($params){
        $anget = self::where('is_delete',0);
        $level = 1;
        if(!empty($params['province'])) {
            $anget = $anget->where('province', $params['province']);
            $level = 1;
        }
        if(!empty($params['city'])) {
            $anget = $anget->where('city', $params['city']);
            $level = 2;
        }
        if(!empty($params['district'])) {
            $anget = $anget->where('district', $params['district']);
            $level = 3;
        }
        $anget = $anget->where('level',$level);
        if(!empty($params['id'])){
            $anget = $anget->where('id','<>', $params['id']);
        }
        if($anget->find()){
            return true;
        }else{
            return false;
        }
    }

    public function getAllList($params, $limit = self::LIMIT)
    {
        $anget = $this->where('is_delete',0);
        if(!empty($params['province'])) {
            $anget = $anget->where('province', $params['province']);
        }
        if(!empty($params['city'])) {
            $anget = $anget->where('city', $params['city']);
        }
        if(!empty($params['district'])) {
            $anget = $anget->where('district', $params['district']);
        }
        return $anget->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }
}