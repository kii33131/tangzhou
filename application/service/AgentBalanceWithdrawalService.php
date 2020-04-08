<?php


namespace app\service;


use app\exceptions\ApiException;
use app\model\AgentBalanceWithdrawalRecordModel;
use app\model\AgentModel;
use app\model\ConfigModel;
use app\model\MemberModel;
use app\model\MessageModel;
use app\model\PlatformModel;
use think\Db;

class AgentBalanceWithdrawalService
{
    public function withdrawal($agentId,$data){
        Db::startTrans();
        try{
            $agent = AgentModel::lock(true)->where('id',$agentId)->find();
            if(!$agent){
                throw new \Exception('代理用户不存在');
            }
            if($agent['balance'] < $data['amount']){
                error('余额不足',20003);
            }
            $serviceChargeProportion = ConfigModel::getParam('service_charge');
            //提现手续费扣减
            $serviceCharge = 0;
            if($serviceChargeProportion > 0){
                $serviceCharge = $data['amount'] * ($serviceChargeProportion / 100);
            }
            AgentModel::changeBalance($agentId,$data['amount'],2,3);
            $recordData = [
                'agent_id' => $agentId,
                'amount' => $data['amount'],
                'type' => 1,
                'service_charge' => $serviceCharge,
                'actual_payment' => $data['amount'] - $serviceCharge,
                'member_id' => $agent->member_id,
                'mode' => $data['mode']
            ];
            if($data['mode'] == 2){
                $recordData['bank_card_number'] =$data['bank_card_number'];
                $recordData['real_name'] =$data['real_name'];
                $recordData['bank'] =$data['bank'];
            }
            $record = AgentBalanceWithdrawalRecordModel::create($recordData);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        //自动审核
        if(ConfigModel::getParam('cash_audit') == 1){
            try{
                $this->pass($record['id']);
            }catch (\Exception $e){
            }
        }
        return true;

    }

    /**
     * 提现拒绝
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function refuse($id){
        Db::startTrans();
        try{
            $recorrd = AgentBalanceWithdrawalRecordModel::lock(true)->where([
                'id' => $id,
                'type' => 1
            ])->find();
            if(!$recorrd){
                throw new ApiException([
                    'msg' => '提现记录不存在'
                ]);
            }
            $recorrd->type = 3;
            $recorrd->audit_time = time();
            $recorrd->save();
            AgentModel::changeBalance($recorrd['agent_id'],$recorrd['amount'],1,4);
            MessageModel::create([
                'member_id' => $recorrd->member_id,
                'msg' => "您申请的提现已被拒绝！"
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 提现通过
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function pass($id){
        Db::startTrans();
        try{
            $recorrd = AgentBalanceWithdrawalRecordModel::lock(true)->where([
                'id' => $id,
                'type' => 1
            ])->find();
            if(!$recorrd){
                throw new \Exception([
                    'msg' => '提现记录不存在'
                ]);
            }
            $recorrd->type = 2;
            $recorrd->audit_time = time();
            $recorrd->order_id = WechatPayService::generateOrderNo();
            $recorrd->save();
            if($recorrd->mode == 1){
                $wechatPayService = new WechatPayService();
                $wechatPayService->transferToBalance([
                    'partner_trade_no' => $recorrd->order_id,
                    'openid' => MemberModel::getOpenIdByUid($recorrd->member_id),
                    'amount' => $recorrd->actual_payment * 100,
                    'desc' => '余额提现',
                    'check_name' => 'NO_CHECK'
                ]);
            }
            //平台消费
            PlatformModel::changeBalance($recorrd->actual_payment,2,8);
            MessageModel::create([
                'member_id' => $recorrd->member_id,
                'msg' => "您申请的提现审核已通过！"
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }
}