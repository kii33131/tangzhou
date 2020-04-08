<?php

namespace app\admin\controller;

use app\enum\AgentIntegralRecordEnum;
use app\model\AgentIntegralRecordModel;

class AgentIntegral extends Base
{
    public function IntegralRecords(AgentIntegralRecordModel $agentIntegralRecordModel){
        $this->assign('integralStates',AgentIntegralRecordEnum::STATE);
        $params = $this->request->param();
        $this->checkParams($params);
        $this->records = $agentIntegralRecordModel->getAllList($params,$this->limit);
        return $this->fetch();
    }
}