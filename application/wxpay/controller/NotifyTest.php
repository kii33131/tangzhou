<?php


namespace app\wxpay\controller;


use app\model\BalanceOrderModel;
use app\model\ConfigModel;
use app\model\CouponModel;
use app\model\GiftPackageModel;
use app\model\IntegralOrderModel;
use app\model\IntegralRecordModel;
use app\model\MemberModel;
use app\model\MessageModel;
use app\model\PlatformBalanceOrder;
use app\model\PlatformModel;
use app\model\StoreModel;
use app\model\UserCouponModel;
use app\model\UserCouponResidualAmountOrderModel;
use app\service\StoreService;
use think\Db;

class NotifyTest
{
    public $message;

    public function __construct($type, $out_trade_no, $total_fee)
    {
        $this->message = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee * 100
        ];
        $this->$type();
        /*switch ($type) {
            case 'settledIn':
                $this->settledIn();
                break;
            case 'PayIntegral':
                $this->PayIntegral();
                break;
            case 'userReceiveCoupon':
                $this->userReceiveCoupon();
                break;
            case 'platformBalance':
                $this->platformBalance();
                break;
            case 'CouponResidualAmount':
                $this->CouponResidualAmount();
                break;
        }*/
    }

    /**
     * 商家入驻
     */
    public function settledIn()
    {
        $message = $this->message;
        if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                Db::startTrans();
                try {
                    $store = StoreModel::lock(true)->where([
                        'order_no' => $message['out_trade_no'],
                        'is_pay' => 0,
                        'entry_fee' => $message['total_fee'] / 100
                    ])->find();
                    if ($store) {
                        //增加微信支付记录
                        MemberModel::wxPayRecord($store['user_id'],$store['entry_fee'],11,0,$store['id']);

                        $store->is_pay = 1;
                        $store->save();
                        //入驻赠送积分
                        if (ConfigModel::getParam('entry_gift_points') > 0) {
                            MemberModel::changeIntegral(
                                $store->user_id,
                                ConfigModel::getParam('entry_gift_points'),
                                1,
                                4
                            );
                        }
                        //推荐返佣
                        if($store->recommender_id != 0){
                            $entryRebate = ConfigModel::getParam('entry_rebate');
                            $rebateAmount = $store->entry_fee * ($entryRebate / 100);
                            if($entryRebate > 0 && $rebateAmount > 0){
                                MemberModel::changeBalance($store['recommender_id'],$rebateAmount,1,2,0,$store->id);
                            }
                        }
                        //代理点返佣
                        $storeService = new StoreService();
                        $storeService->agentCommission($store->id);
                        //平台充值
                        PlatformModel::changeBalance($store->entry_fee,1,2,0,0,$store->user_id,0,$store->id);
                        //审核通过推送消息
                        if($store->state == 2){
                            MessageModel::create([
                                'member_id' => $store->user_id,
                                'msg' => "您入驻的店铺已审核通过！"
                            ]);
                        }

                        Db::commit();
                        return true;
                    } else {
                        exception('订单不存在！');
                    }
                } catch (\Exception $e) {
                    Db::rollback();
                    throw $e;
                    return ('通信失败，请稍后再通知我');
                }
            }
        }

    }

    /**
     * 积分支付
     */
    public function payIntegral()
    {
        $message = $this->message;

        if ($message['result_code'] === 'SUCCESS') {
            Db::startTrans();
            $order = IntegralOrderModel::lock(true)->where([
                'order_no' => $message['out_trade_no'],
                'state' => 0,
                'amount' => $message['total_fee'] / 100
            ])->find();
            if ($order) {
                try {
                    //增加微信支付记录
                    MemberModel::wxPayRecord($order['member_id'],$order['amount'],12,0,$order['store_id']);

                    $order->state = 1;
                    $order->save();
                    MemberModel::where(['id' => $order['member_id']])->setInc('integral', $order['integral']);
                    $member = MemberModel::get($order['member_id']);
                    $data['store_id'] = $order['store_id'];
                    $data['member_id'] = $order['member_id'];
                    $data['integral'] = $order['integral'];
                    $data['residual_integral'] = $member['integral'];
                    $data['amount'] = $order['amount'];
                    $data['create_time'] = time();
                    $data['type'] = 1;
                    $data['mode'] = 3;
                    IntegralRecordModel::create($data);
                    //代理点返佣
                    $storeService = new StoreService();
                    $storeService->integralCommission($order['store_id'],$order['amount']);
                    //平台充值
                    PlatformModel::changeBalance($order->amount,1,3,0,0,$order['member_id'],0,$order['store_id']);
                    //返利充值
                    $integralRebate = ConfigModel::getParam('integral_rebate');
                    if($integralRebate > 0){
                        $rebateAmount = $order['amount'] * ($integralRebate / 100);
                        if($rebateAmount > 0){
                            $recommenderId = StoreModel::where('id',$order['store_id'])->value('recommender_id');
                            if(!empty($recommenderId)){
                                MemberModel::changeBalance($recommenderId,$rebateAmount,1,9,0,$order['store_id']);
                            }
                        }
                    }
                    // 提交事务
                    Db::commit();
                    return true;
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    exception($e->getMessage());
                }
            }
            Db::commit();
        }
    }

    /**
     * 余额支付
     */
    public function payBalance(){
        $message = $this->message;

        if ($message['result_code'] === 'SUCCESS') {
            Db::startTrans();
            $order=BalanceOrderModel::lock(true)->where([
                'order_no' => $message['out_trade_no'],
                'is_pay' => 0,
                'amount' => $message['total_fee'] / 100
            ])->find();
            if($order){
                try {
                    //增加微信支付记录
                    //MemberModel::wxPayRecord($order['user_id'],$order['amount'],13);

                    $order->is_pay=1;
                    $order->save();
                    //平台充值
                    PlatformModel::changeBalance($message['total_fee'] / 100,1,4,0,0,$order['user_id']);
                    //用户余额充值
                    MemberModel::changeBalance($order['user_id'],$order['amount'],1,1);
                    // 提交事务
                    Db::commit();
                    return true;
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    exception($e->getMessage());
                }
            }
            Db::commit();
        }
    }

    /**
     * 用户领取优惠券支付
     */
    public function userReceiveCoupon()
    {
        $message = $this->message;
        if ($message['result_code'] === 'SUCCESS') {
            Db::startTrans();
            try {
                CouponModel::userReceiveCoupon($message['out_trade_no'],2);
                // 提交事务
                Db::commit();
                return true;
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                exception($e->getMessage());
            }
        }
    }

    /**
     * 平台余额充值
     */
    public function platformBalance()
    {
        $message = $this->message;
        if ($message['result_code'] === 'SUCCESS') {
            Db::startTrans();
            $order = PlatformBalanceOrder::lock(true)->where([
                'order_no' => $message['out_trade_no'],
                'is_pay' => 0,
                'amount' => $message['total_fee'] / 100
            ])->find();
            if ($order) {
                try {
                    $order->is_pay = 1;
                    $order->save();
                    //平台充值
                    PlatformModel::changeBalance($order->amount,1,1,$order->user_id,1);
                    // 提交事务
                    Db::commit();
                    return true;
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    throw $e;
                }
            }
            Db::commit();
        }
    }

    /**
     * 卡券剩余金额支付
     */
    public function couponResidualAmount()
    {
        $message = $this->message;

        if ($message['result_code'] === 'SUCCESS') {
            Db::startTrans();
            $order = UserCouponResidualAmountOrderModel::lock(true)->where([
                'order_no' => $message['out_trade_no'],
                'is_pay' => 0,
                'amount' => $message['total_fee'] / 100
            ])->find();
            if ($order) {
                try {
                    $userCoupon = UserCouponModel::where('id',$order['user_coupon_id'])->find();
                    //增加微信支付记录
                    MemberModel::wxPayRecord($userCoupon['user_id'],$order['amount'],14,$userCoupon['coupon_id']);

                    $order->is_pay = 1;
                    $order->save();
                    UserCouponModel::where('id', $order['user_coupon_id'])->update(['residual_is_pay' => 1]);
                    //平台充值
                    PlatformModel::changeBalance($message['total_fee'] / 100,1,6,0,0,$userCoupon['user_id'],$userCoupon['coupon_id']);
                    // 提交事务
                    Db::commit();
                    return true;
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    throw $e;
                }
            }
            Db::commit();
        }
    }

    /**
     * 礼包领取
     */
    public function giftReceive(){
        $message = $this->message;
        if ($message['result_code'] === 'SUCCESS') {
            $giftPackageModel = new GiftPackageModel();
            $giftPackageModel->giftReceive($message['out_trade_no'],$message['total_fee'] / 100,1);
        }
    }

}