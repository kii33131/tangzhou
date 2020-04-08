<?php

namespace app\agent\controller;

use app\enum\AgentIntegralRecordEnum;
use app\model\AgentIntegralRecordModel;

class Integral extends Base
{
    public function IntegralRecords(AgentIntegralRecordModel $agentIntegralRecordModel){
        $this->assign('integralStates',AgentIntegralRecordEnum::STATE);
        $params = $this->request->param();
        $this->checkParams($params);
        $loginUser = $this->getLoginUser();
        $params['agent_id'] = $loginUser['id'];
        $this->records = $agentIntegralRecordModel->getAllList($params,$this->limit);
        return $this->fetch();
    }
}