<?php

namespace app\agent\controller;

use app\admin\request\CashWithdrawalRequest;
use app\enum\AgentBalanceRecordEnum;
use app\model\AgentBalanceRecordModel;
use app\service\AgentBalanceWithdrawalService;

class Balance extends Base
{
    public function balanceRecords(AgentBalanceRecordModel $agentBalanceRecordModel){
        $this->assign('balanceState',AgentBalanceRecordEnum::STATE);
        $params = $this->request->param();
        $this->checkParams($params);
        $loginUser = $this->getLoginUser();
        $params['agent_id'] = $loginUser['id'];
        $this->records = $agentBalanceRecordModel->getAllList($params,$this->limit);
        return $this->fetch();
    }

    public function cashWithdrawal(CashWithdrawalRequest $request){
        if($request->isPost()){
            $data = $request->post();
            $loginUser = $this->getLoginUser();
            try{
                $agentBalanceWithdrawalService = new AgentBalanceWithdrawalService();
                $agentBalanceWithdrawalService->withdrawal($loginUser['id'],$data);
            }catch (\Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('发起提现成功',url('agent/index/main'));
        }
        return $this->fetch();
    }
}