<?php

namespace app\admin\controller;

use app\enum\BalanceRecordEnum;
use app\enum\BalanceWithdrawalRecordEnum;
use app\enum\IntegralRecordEnum;
use app\model\BalanceRecordModel;
use app\model\BalanceWithdrawalRecordModel;
use app\model\IntegralRecordModel;
use app\model\StoreModel;

class FinancialDetails extends Base
{
    protected $filters = [
        'settled' => '入驻记录',
        'recharge' => '充值记录',
        'integral_recharge' => '积分充值记录',
        'integral_consume' => '积分消费记录',
        'rebate' => '返利记录',
        'withdraw' => '提现记录'
    ];
    public function initialize()
    {
        $this->assign(
            ['filters' => $this->filters]
        );
    }
    public function index()
    {
        $params = $this->request->param();
        if(empty($params['filter']) || !array_key_exists($params['filter'],$this->filters)){
            $filter = 'settled';
        }else{
            $filter = $params['filter'];
        }
        $this->checkParams($params);
        switch ($filter){
            case 'settled'://入驻记录
                $storeModel = new StoreModel();
                $this->lists = $storeModel->getAllList($params, $this->limit);
                break;
            case 'recharge'://充值记录
                $balanceRecordModel = new BalanceRecordModel();
                $params['type'] = 1;
                $params['state'] = 1;
                $this->lists = $balanceRecordModel->getList($params,$this->limit);
                break;
            case 'rebate'://返利记录
                $balanceRecordModel = new BalanceRecordModel();
                $params['type'] = 1;
                $params['state'] = [2,3,4];
                $this->states = BalanceRecordEnum::STATE;
                $this->lists = $balanceRecordModel->getList($params,$this->limit);
                break;
            case 'integral_recharge'://积分充值记录
                $integralRecordModel = new IntegralRecordModel();
                $params['type'] = 1;
                $params['mode'] = 3;
                $this->lists = $integralRecordModel->getList($params, $this->limit);
                break;
            case 'integral_consume'://积分消费记录
                $this->assign('modes',IntegralRecordEnum::MODE);
                $integralRecordModel = new IntegralRecordModel();
                $params['type'] = 2;
                $this->lists = $integralRecordModel->getList($params, $this->limit);
                break;
            case 'withdraw'://提现记录
                $balanceWithdrawalRecordModel = new BalanceWithdrawalRecordModel();
                $params['type'] = [2,3];
                $this->assign([
                    'types' => BalanceWithdrawalRecordEnum::TYPE,
                ]);
                $this->lists = $balanceWithdrawalRecordModel->getList($params, $this->limit);
                break;
            default :
                $this->error('错误');
        }
        return $this->fetch($filter);
    }

}