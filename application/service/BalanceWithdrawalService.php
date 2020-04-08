<?php


namespace app\service;


use app\exceptions\ApiException;
use app\model\BalanceWithdrawalRecordModel;
use app\model\MemberModel;
use app\model\MessageModel;
use app\model\PlatformModel;
use think\Db;

class BalanceWithdrawalService
{
    /**
     * 提现拒绝
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function refuse($id,$msg){
        Db::startTrans();
        try{
            $recorrd = BalanceWithdrawalRecordModel::lock(true)->where([
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
            $recorrd->reject_reason = $msg;
            $recorrd->save();
            MemberModel::changeBalance($recorrd['member_id'],$recorrd['amount'],1,6);
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
            $recorrd = BalanceWithdrawalRecordModel::lock(true)->where([
                'id' => $id,
                'type' => 1
            ])->find();
            if(!$recorrd){
                throw new ApiException([
                    'msg' => '提现记录不存在'
                ]);
            }
            $recorrd->type = 2;
            $recorrd->audit_time = time();
            $recorrd->order_id = WechatPayService::generateOrderNo();
            $recorrd->save();
            //微信提现
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
                'msg' => "您申请的提现已审核通过！"
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        return true;
    }
}