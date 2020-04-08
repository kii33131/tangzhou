<?php

namespace app\admin\controller;

use app\enum\AgentBalanceWithdrawalRecordEnum;
use app\model\AgentBalanceWithdrawalRecordModel;
use app\service\AgentBalanceWithdrawalService;

class AgentCashWithdrawalExamine extends Base
{
    public function index(AgentBalanceWithdrawalRecordModel $agentBalanceWithdrawalRecordModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->lists = $agentBalanceWithdrawalRecordModel->getList($params, $this->limit);
        $this->assign([
            'types' => AgentBalanceWithdrawalRecordEnum::TYPE,
            'modes' => AgentBalanceWithdrawalRecordEnum::MODE
        ]);
        return $this->fetch();
    }

    /**
     * 审核通过
     */
    public function pass()
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        try{
            $agentBalanceWithdrawalService = new AgentBalanceWithdrawalService();
            $agentBalanceWithdrawalService->pass($id);
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('通过成功', url('AgentCashWithdrawalExamine/index'));
    }

    /**
     * 审核拒绝
     */
    public function refuse()
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        try{
            $agentBalanceWithdrawalService = new AgentBalanceWithdrawalService();
            $agentBalanceWithdrawalService->refuse($id);
        }catch (\Exception $e){
            $this->error('拒绝失败');
        }
        $this->success('拒绝成功', url('AgentCashWithdrawalExamine/index'));
    }
}