<?php

namespace app\admin\controller;

use app\enum\AgentBalanceRecordEnum;
use app\model\AgentBalanceRecordModel;

class AgentBalance extends Base
{
    public function balanceRecords(AgentBalanceRecordModel $agentBalanceRecordModel){
        $this->assign('balanceState',AgentBalanceRecordEnum::STATE);
        $params = $this->request->param();
        $this->checkParams($params);
        $this->records = $agentBalanceRecordModel->getAllList($params,$this->limit);
        return $this->fetch();
    }
}