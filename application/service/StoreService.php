<?php


namespace app\service;


use app\exceptions\ApiException;
use app\model\AgentModel;
use app\model\StoreModel;

class StoreService
{
    /**
     * 入驻返佣
     * @param $storeId int 门店id
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function agentCommission($storeId){
        //获取门店信息
        $store = StoreModel::where([
            'id' => $storeId,
            'is_pay' => 1
        ])->find();
        if(!$store){
            throw new ApiException([
                'msg' => '门店不存在',
                'errorCode' => 30001
            ]);
        }
        $agentList = $this->getAgentList($store);
        foreach ($agentList as $agent){
            $commission = $store->entry_fee * ($agent['residence_rebate'] / 100);
            if($commission > 0){
                AgentModel::changeBalance($agent['id'],$commission,1,1,$storeId);
            }
        }
        return true;
    }

    /**
     * 充值积分返佣
     * @param $storeId int 门店id
     * @param $amount number 充值金额
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function integralCommission($storeId,$amount){
        //获取门店信息
        $store = StoreModel::where([
            'id' => $storeId,
            'is_pay' => 1
        ])->find();
        if(!$store){
            throw new ApiException([
                'msg' => '门店不存在',
                'errorCode' => 30001
            ]);
        }
        $agentList = $this->getAgentList($store);
        foreach ($agentList as $agent){
            $commission = $amount * ($agent['pumping_ratio'] / 100);
            AgentModel::changeBalance($agent['id'],$commission,1,2,$storeId);
        }
        return true;
    }

    private function getAgentList($store){
        $agentList = [];
        //获取当前省代
        $agentProvince = AgentModel::where([
            'province' => $store['province'],
            'level' => 1,
            'is_delete' => 0
        ])->find();

        //获取当前市代
        $agentCity = AgentModel::where([
            'province' => $store['province'],
            'city' => $store['city'],
            'level' => 2,
            'is_delete' => 0
        ])->find();

        //获取当前区代
        $agentDistrict = AgentModel::where([
            'province' => $store['province'],
            'city' => $store['city'],
            'district' => $store['district'],
            'level' => 3,
            'is_delete' => 0
        ])->find();

        if($agentProvince){
            $agentList[] = $agentProvince;
        }
        if($agentCity){
            $agentList[] = $agentCity;
        }
        if($agentDistrict){
            $agentList[] = $agentDistrict;
        }
        return $agentList;
    }


}