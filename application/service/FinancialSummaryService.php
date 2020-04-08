<?php


namespace app\service;


use app\model\AgentBalanceRecordModel;
use app\model\BalanceRecordModel;
use app\model\IntegralRecordModel;
use app\model\StoreModel;
use app\model\UserCouponModel;

class FinancialSummaryService
{
    public $startMonth; //开始月份
    public $endMonth; //结束月份
    private $region = []; //地区
    public function __construct($params)
    {
        if(empty($params['month'])){
            $this->startMonth = date('Y-m');
        }else{
            $this->startMonth = $params['month'];
        }
        $this->endMonth = date('Y-m-d',strtotime("{$this->startMonth} +1 month"));
        foreach (['province','city','district'] as $val){
            if(!empty($params[$val])){
                $this->region[$val] = $params[$val];
            }
        }
    }

    /**
     * 获取入驻数量
     */
    public function settledNumber(){
        $storeModel = StoreModel::whereBetweenTime('apply_time', $this->startMonth, $this->endMonth)
            ->where('is_pay',1);
        $storeModel = $this->whereRegion($storeModel);
        return $storeModel->count();
    }

    /**
     * 平台收入
     */
    public function platformRevenue(){
        $storeModel = StoreModel::whereBetweenTime('apply_time', $this->startMonth, $this->endMonth)
            ->where('is_pay',1);
        $storeModel = $this->whereRegion($storeModel);
        return $storeModel->sum('entry_fee');
    }

    /**
     * 代理点收入
     */
    public function agentRevenue(){
        $agentBalanceRecordModel = AgentBalanceRecordModel::alias('a')
            ->join('store s','s.id = a.store_id')
            ->whereBetweenTime('a.create_time', $this->startMonth, $this->endMonth)
            ->where('a.type',1)
        ;
        $agentBalanceRecordModel = $this->whereRegion($agentBalanceRecordModel,'s');
        return $agentBalanceRecordModel->sum('amount');
    }

    /**
     * 推荐人收入
     */
    public function recommenderIncome(){
        return BalanceRecordModel::whereBetweenTime('create_time', $this->startMonth, $this->endMonth)
            ->where('state',2)
            ->sum('amount');//推荐人收入
    }

    /**
     * 卡券领取数量
     */
    public function couponReceiveNumber(){
        $userCouponModel = UserCouponModel::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->whereBetweenTime('uc.create_time', $this->startMonth, $this->endMonth);
        $userCouponModel = $this->whereRegion($userCouponModel,'s');
        return $userCouponModel->count();
    }

    /**
     * 卡券平台收入
     */
    public function couponPlatformRevenue(){
        $userCouponModel = UserCouponModel::alias('uc')
            ->join('coupon c','c.id = uc.coupon_id')
            ->join('store s','s.id = c.store_id')
            ->whereBetweenTime('uc.create_time', $this->startMonth, $this->endMonth);
        $userCouponModel = $this->whereRegion($userCouponModel,'s');
        return $userCouponModel->sum('amount');
    }

    /**
     * 充值总额
     */
    public function rechargeAmount(){
        return BalanceRecordModel::whereBetweenTime('create_time', $this->startMonth, $this->endMonth)
            ->where('state',1)
            ->sum('amount');
    }

    /**
     * 充值用户数
     */
    public function rechargeNumber(){
        return BalanceRecordModel::whereBetweenTime('create_time', $this->startMonth, $this->endMonth)
            ->where('state',1)
            ->count();
    }

    /**
     * 积分充值总额
     */
    public function integralRechargeAmount(){
        $integralRecordModel = IntegralRecordModel::alias('i')
            ->join('store s','s.id = i.store_id')
            ->whereBetweenTime('i.create_time', $this->startMonth, $this->endMonth)
            ->where('type',1);
        $integralRecordModel = $this->whereRegion($integralRecordModel,'s');
        return $integralRecordModel->sum('amount');
    }

    /**
     * 充值总积分
     */
    public function integralIntegral(){
        $integralRecordModel = IntegralRecordModel::alias('i')
            ->join('store s','s.id = i.store_id')
            ->whereBetweenTime('i.create_time', $this->startMonth, $this->endMonth)
            ->where('type',1);
        $integralRecordModel = $this->whereRegion($integralRecordModel,'s');
        return $integralRecordModel->sum('integral');
    }

    /**
     * 充值总人数
     */
    public function integralRechargeNumber(){
        $integralRecordModel = IntegralRecordModel::alias('i')
            ->join('store s','s.id = i.store_id')
            ->whereBetweenTime('i.create_time', $this->startMonth, $this->endMonth)
            ->where('type',1);
        $integralRecordModel = $this->whereRegion($integralRecordModel,'s');
        return $integralRecordModel->count();
    }

    private function whereRegion($model,$prefix = ''){
        $arr = [];
        if($prefix != ''){
            $prefix = $prefix . '.';
        }
        foreach ($this->region as $key => $val){
            $arr[$prefix . $key] = $val;
        }
        if($arr){
            return $model->where($arr);
        }else{
            return $model;
        }
    }
}