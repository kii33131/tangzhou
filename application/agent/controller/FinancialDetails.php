<?php

namespace app\agent\controller;

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
        'integral_recharge' => '积分充值记录'
    ];
    public function initialize()
    {
        $LoginUser = $this->getLoginUser();
        $this->assign(
            [
                'filters' => $this->filters,
                'province' => $LoginUser['province'],
                'city' => $LoginUser['city'],
                'district' => $LoginUser['district']
            ]
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
        //过滤省市区
        $loginUser = $this->getLoginUser();
        foreach (['province','city','district'] as $val){
            if(!empty($loginUser[$val])){
                $params[$val] = $loginUser[$val];
            }
        }
        switch ($filter){
            case 'settled'://入驻记录
                $storeModel = new StoreModel();
                $this->lists = $storeModel->getAllList($params, $this->limit);
                break;
            case 'integral_recharge'://积分充值记录
                $integralRecordModel = new IntegralRecordModel();
                $params['type'] = 1;
                $params['mode'] = 3;
                $this->lists = $integralRecordModel->getList($params, $this->limit);
                break;
            default :
                $this->error('错误');
        }
        return $this->fetch($filter);
    }

}