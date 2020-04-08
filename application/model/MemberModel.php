<?php

namespace app\model;


use app\exceptions\ApiException;
use app\service\BalanceWithdrawalService;
use think\Db;

class MemberModel extends BaseModel
{
    protected $name = 'member';

    /**
     * 根据uid获取门店id
     * @param $uid
     * @return mixed
     */
    public static function getStoreIdByUid($uid,$throw = false){
        $storeId = StoreModel::where([
            'user_id'=>$uid,
            'is_pay' => 1,
            'is_delete' => 0,
            'state'=>2
        ])->value('id');
        if($throw && empty($storeId)){
            throw new ApiException([
                'msg' => '未成为商家',
                'errorCode' => 30002
            ]);
        }
        return $storeId;
    }

    /**
     * 根据门店id获取uid
     * @param $uid
     * @return mixed
     */
    public static function getUidByStoreId($storeId,$throw = false){
        $userId = StoreModel::where([
            'id'=>$storeId,
            'is_pay' => 1,
            'is_delete' => 0,
            'state'=>2
        ])->value('user_id');
        if($throw && empty($userId)){
            throw new ApiException([
                'msg' => '未成为商家',
                'errorCode' => 30002
            ]);
        }
        return $userId;
    }

    /**
     * 根据uid判断是否是有门店
     * @param $uid
     * @return bool
     */
    public static function isStore($uid){
        if(!empty(self::getStoreIdByUid($uid))){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 充值/消费 积分
     * @param $memberId     用户id
     * @param $integral     积分变动值
     * @param $type         类型 1：充值 2：消费
     * @param $mode         消费方式 1：下载抢购券 2：下载促销券 3:积分充值 4：入驻赠送积分  5：发布优惠券
     * @param string $amount 充值金额
     * @return bool
     * @throws ApiException
     */
    public static function changeIntegral($memberId,$integral,$type,$mode,$amount = '0.00'){
        if($integral <= 0){
            return false;
        }
        $member = self::get($memberId);
        if(empty($member)){
            throw new ApiException([
                'msg' => '获取用户信息失败',
                'errorCode' => 20001
            ]);
        }
        $storeId = self::getStoreIdByUid($memberId);
        if(empty($storeId)){
            $storeId = 0;
        }
/*        Db::startTrans();
        try{*/
            //修改积分
            if($type == 1){
                $residualIntegral = $member['integral'] + $integral;
                self::where(['id'=>$memberId])->setInc('integral',$integral);
            }else{
                $residualIntegral = $member['integral'] - $integral;
                if($member['integral'] < $integral){
                    throw new ApiException([
                        'msg' => '用户积分不足',
                        'errorCode' => 20002
                    ]);
                }
                self::where(['id'=>$memberId])->setDec('integral',$integral);
            }
            //创建积分记录
            IntegralRecordModel::create([
                'member_id' => $memberId,
                'store_id' => $storeId,
                'integral' => $integral,
                'residual_integral' => $residualIntegral,
                'amount' => $amount,
                'type' => $type,
                'mode' => $mode,
                'create_time' => time()
            ]);
/*            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }*/
//        return true;
    }

    /**
     * 充值/消费 余额
     * @param $memberId int 用户id
     * @param $amount int 用户金额
     * @param $type int 类型
     * @param int $state
     * @param int $couponId
     * @param int $storeId
     * @param int $giftPackageId
     * @return bool
     * @throws ApiException
     * @throws \think\Exception
     */
    public static function changeBalance($memberId,$amount,$type,$state = 0,$couponId = 0,$storeId = 0,$giftPackageId = 0){
        if($amount <= 0){
            return false;
        }
        $member = self::get($memberId);
        if(empty($member)){
            throw new ApiException([
                'msg' => '获取用户信息失败',
                'errorCode' => 20001
            ]);
        }
        if($type == 1){
            $residualAmount = $member['balance'] + $amount;
            self::where(['id' => $memberId])->setInc('balance',$amount);
        }else{
            $residualAmount = $member['balance'] - $amount;
            if($member['balance'] < $amount){
                throw new ApiException([
                    'msg' => '用户余额不足',
                    'errorCode' => 20002
                ]);
            }
            self::where(['id' => $memberId])->setDec('balance',$amount);
        }
        BalanceRecordModel::create([
            'member_id' => $memberId,
            'amount' => $amount,
            'residual_amount' => $residualAmount,
            'type' => $type,
            'state' => $state,
            'coupon_id' => $couponId,
            'store_id' => $storeId,
            'gift_package_id' => $giftPackageId,
            'pay_type' => 1
        ]);
    }

    /**
     * 添加微信支付记录
     * @param $memberId int 用户id
     * @param $amount number 金额
     * @param int $state
     * @param int $couponId
     * @param int $storeId
     * @param int $giftPackageId
     * @return bool
     * @throws ApiException
     */
    public static function wxPayRecord($memberId,$amount,$state = 0,$couponId = 0,$storeId = 0,$giftPackageId = 0){
        if($amount <= 0){
            return false;
        }
        $member = self::get($memberId);
        if(empty($member)){
            throw new ApiException([
                'msg' => '获取用户信息失败',
                'errorCode' => 20001
            ]);
        }
        BalanceRecordModel::create([
            'member_id' => $memberId,
            'amount' => $amount,
            'residual_amount' => $member['balance'],
            'type' => 2,
            'state' => $state,
            'coupon_id' => $couponId,
            'store_id' => $storeId,
            'gift_package_id' => $giftPackageId,
            'pay_type' => 2
        ]);
    }

    /**
     * 余额提现
     * @param $memberId
     * @param $data
     * @return bool
     * @throws \think\Exception
     */
    public function balanceWithdrawal($memberId,$data){
        Db::startTrans();
        try{
            $member = $this->lock(true)->where('id',$memberId)->find();
            if(!$member){
                error('用户不存在',20001);
            }
            if($member['balance'] < $data['amount']){
                error('用户余额不足',20003);
            }
            $serviceChargeProportion = ConfigModel::getParam('service_charge');
            //提现手续费扣减
            $serviceCharge = 0;
            if($serviceChargeProportion > 0){
                $serviceCharge = $data['amount'] * ($serviceChargeProportion / 100);
            }
            $this->changeBalance($memberId,$data['amount'],2,5);
            $recordData = [
                'member_id' => $memberId,
                'amount' => $data['amount'],
                'type' => 1,
                'service_charge' => $serviceCharge,
                'actual_payment' => $data['amount'] - $serviceCharge,
                'mode' => $data['mode']
            ];
            if($data['mode'] == 2){
                $recordData['bank_card_number'] =$data['bank_card_number'];
                $recordData['real_name'] =$data['real_name'];
                $recordData['bank'] =$data['bank'];
            }
            $record = BalanceWithdrawalRecordModel::create($recordData);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        //自动审核
        if(ConfigModel::getParam('cash_audit') == 1){
            try{
                $balanceWithdrawalService = new BalanceWithdrawalService();
                $balanceWithdrawalService->pass($record['id']);
            }catch (\Exception $e){
            }
        }
        return true;
    }

    /**
     * 根据用户id获取openid
     * @param $memberId
     * @return mixed
     * @throws ApiException
     */
    public static function getOpenIdByUid($memberId){
        $openid = self::where('id',$memberId)->value('openid');
        if(empty($openid)){
            throw new ApiException([
                'msg' => '获取用户信息失败',
                'errorCode' => 20001
            ]);
        }
        return $openid;
    }

    public function getList($params, $limit = self::LIMIT)
    {
        $lists = $this;
        if(!empty($params['name'])){
            $lists = $lists->whereLike('name',"%{$params['name']}%");
        }
        return $lists->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }

    /**
     * 获取用户推广记录
     * @param $memberId int 用户id
     * @param int $listLows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getUserExtensionRecords($memberId,$listLows = self::LIMIT){
        $lists = $this->alias('m')
            ->field('m.name,m.picture,s.entry_time')
            ->join('store s','s.user_id = m.id')
            ->where('s.recommender_id',$memberId)
            ->where('s.state',2)
            ->order('s.id desc')
            ->paginate($listLows)->each(function ($item){
                $item->entry_time = date('Y-m-d h:i:s',$item->entry_time);
            });
        return $lists;
    }

    /**
     * 生成推荐码
     * @return int
     */
    public static function generateExtensionCode(){
        $extensionCode = strtolower(rand(100000,999999));
        if(self::where('extension_code',$extensionCode)->find()){
            return self::generateExtensionCode();
        }
        return $extensionCode;
    }

}