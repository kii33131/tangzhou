<?php

namespace app\admin\controller;

use app\service\FinancialSummaryService;

class FinancialSummary extends Base
{
    protected $filters = [
        'settled' => '入驻收入',
        'coupon' => '卡券收入',
        'recharge' => '充值收入',
        'integral' => '积分收入'
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
        $financialSummaryService = new FinancialSummaryService($params);
        $data = [
            'month' => $financialSummaryService->startMonth
        ];
        switch ($filter){
            case 'settled'://入驻收入
                $data['settled_number'] = $financialSummaryService->settledNumber();//入驻数量
                $data['platform_revenue'] = $financialSummaryService->platformRevenue();//平台收入
                $data['agent_revenue'] = $financialSummaryService->agentRevenue();//代理点收入
                $data['recommender_income'] = $financialSummaryService->recommenderIncome();//推荐人收入
                break;
            case 'coupon'://卡券收入
                $data['receive_number'] = $financialSummaryService->couponReceiveNumber();//卡券领取数量
                $data['platform_revenue'] = $financialSummaryService->couponPlatformRevenue();//卡券平台收入
                break;
            case 'recharge'://充值收入
                $data['recharge_amount'] = $financialSummaryService->rechargeAmount();//充值总额
                $data['recharge_number'] = $financialSummaryService->rechargeNumber();//充值用户数
                break;
            case 'integral'://积分收入
                $data['recharge_amount'] = $financialSummaryService->integralRechargeAmount();//充值总额
                $data['integral'] = $financialSummaryService->integralIntegral();//充值总积分
                $data['recharge_number'] = $financialSummaryService->integralRechargeNumber();//充值总人数
                break;
            default :
                $this->error('错误');
        }
        $this->assign('data',$data);
        return $this->fetch($filter);
    }

}