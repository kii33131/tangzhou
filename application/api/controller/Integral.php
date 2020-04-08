<?php


namespace app\api\controller;


use app\api\service\UserToken;
use app\api\service\WechatPay;
use app\api\validate\IDMustBePositiveInt;
use app\model\IntegralOrderModel;
use app\model\IntegralPackageModel;
use app\model\MemberModel;


class Integral extends Base
{

 public function integralCommodity(){
     success(IntegralPackageModel::select());
 }

 public function payIntegral($id = ''){
     (new IDMustBePositiveInt())->goCheck();
     $integra=IntegralPackageModel::get($id);
     if(!$integra){
         error('找不到该积分选项','40001');
     }
     $userresult=MemberModel::get($this->uid);
     if(!$userresult){
         error('找不到该用户','20001');
     }
     $integraldata['store_id'] = MemberModel::getStoreIdByUid($this->uid,true);
     $integraldata['member_id'] =$this->uid;
     $integraldata['create_time'] =time();
     $integraldata['integral'] =$integra['integral'];
     $integraldata['order_no'] = WechatPay::generateOrderNo();
     $integraldata['amount'] =$integra['amount'];
     IntegralOrderModel::create($integraldata);
     //微信支付
     $payData = [
         'body' => '积分支付',
         'out_trade_no' => $integraldata['order_no'],
         'total_fee' => $integraldata['amount'] * 100,
         'notify_url' => url('wxpay/Notify/payIntegral','','',true),
         'trade_type' => 'JSAPI',
         'openid' => UserToken::getCurrentTokenVar('openid'),
     ];
     $wechatPay = new WechatPay();
     $wxResult = $wechatPay->orderPay($payData);
     $result['wx_pay'] = $wxResult;
     //TODO 跳板支付开始
     skip_pay('payIntegral',$integraldata['order_no'],$integraldata['amount']);
     //TODO 跳板支付结束
     success($result);
 }

}