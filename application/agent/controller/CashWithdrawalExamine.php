<?php

namespace app\agent\controller;

use app\enum\AgentBalanceWithdrawalRecordEnum;
use app\model\AgentBalanceWithdrawalRecordModel;

class CashWithdrawalExamine extends Base
{
    public function index(AgentBalanceWithdrawalRecordModel $agentBalanceWithdrawalRecordModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $loginUser = $this->getLoginUser();
        $params['agent_id'] = $loginUser['id'];
        $this->lists = $agentBalanceWithdrawalRecordModel->getList($params, $this->limit);
        $this->assign([
            'types' => AgentBalanceWithdrawalRecordEnum::TYPE,
            'modes' => AgentBalanceWithdrawalRecordEnum::MODE
        ]);
        return $this->fetch();
    }
}