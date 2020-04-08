<?php

namespace app\admin\controller;

use app\enum\BalanceWithdrawalRecordEnum;
use app\model\BalanceWithdrawalRecordModel;
use app\service\BalanceWithdrawalService;

class CashWithdrawalExamine extends Base
{
    public function index(BalanceWithdrawalRecordModel $balanceWithdrawalRecordModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->lists = $balanceWithdrawalRecordModel->getList($params, $this->limit);
        $this->assign([
            'types' => BalanceWithdrawalRecordEnum::TYPE,
            'modes' => BalanceWithdrawalRecordEnum::MODE
        ]);
        return $this->fetch();
    }

    /**
     * 审核通过
     * @param BalanceWithdrawalRecordModel $balanceWithdrawalRecordModel
     */
    public function pass(BalanceWithdrawalRecordModel $balanceWithdrawalRecordModel)
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        $balanceWithdrawalService = new BalanceWithdrawalService();
        try{
            $balanceWithdrawalService->pass($id);
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('通过成功', url('CashWithdrawalExamine/index'));
    }

    /**
     * 审核拒绝
     * @param BalanceWithdrawalRecordModel $balanceWithdrawalRecordModel
     */
    public function refuse(BalanceWithdrawalRecordModel $balanceWithdrawalRecordModel)
    {
        $id = $this->request->post('id');
        $msg = $this->request->post('msg');
        if (!$id) {
            $this->error('不存在数据');
        }
        $record = $balanceWithdrawalRecordModel->findBy($id);
        if (!$record || $record['type'] != 1) {
            $this->error('不存在的数据');
        }
        $balanceWithdrawalService = new BalanceWithdrawalService();
        try{
            $balanceWithdrawalService->refuse($id,$msg);
        }catch (\Exception $e){
            $this->error('拒绝失败');
        }
        $this->success('拒绝成功', url('CashWithdrawalExamine/index'));
    }
}