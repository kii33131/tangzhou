<?php


namespace app\api\controller;

use app\api\service\UserToken;
use app\api\service\WechatPay;
use app\api\validate\GiftCoupon;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\MyCouponDetail;
use app\api\validate\TouristDetail;
use app\exceptions\ApiException;
use app\model\ConfigModel;
use app\model\CouponModel;
use app\model\GiftCouponModel;
use app\model\GiftUserCouponModel;
use app\model\MemberModel;
use app\model\UserCouponModel;
use app\model\UserCouponOrderModel;
use app\model\UserCouponResidualAmountOrderModel;
use app\model\UserDownloadCouponModel;
use think\Db;


class TouristCoupon extends Base
{

    /**
     * 优惠券详情
     * @url api/coupon/detail
     * @http POST
     * @post string code  分享码
     */
    public function detail()
    {
        $validate = new TouristDetail();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $giftCoupon = GiftCouponModel::where('code',$data['code'])->find();
        if(!$giftCoupon){
            throw new ApiException([
                'msg' => '卡券不存在',
                'errorCode' => 40001
            ]);
        }
        if($giftCoupon->download_coupon_id == 0){
            $data['id'] = $giftCoupon->coupon_id;
            $data['type'] = 1;
        }else{
            $data['id'] = $giftCoupon->download_coupon_id;
            $data['type'] = 2;
        }
        //优惠券
        if($data['type'] == 1){
            $couponModel = new CouponModel();
            $coupon = $couponModel->detail($data);
        }else{
            //下载的优惠券
            $userDownloadCouponModel = new UserDownloadCouponModel();
            $coupon = $userDownloadCouponModel->detail($data);
        }
        $coupon->stock = $giftCoupon->stock;//赋值库存
        $coupon['coupon_amount'] = ConfigModel::getParam('coupon_amount');
        success($coupon);
    }

    /**
     * 优惠券领取
     * @url api/tourist_coupon/receive
     * @http POST
     */
    public function receive($code = ''){
        if(empty($code)){
            error('code不得为空',10000);
        }
        $couponModel = new CouponModel();
        $order = $couponModel->userReceive($code,$this->uid);
        $result = [
            'amount' => $order['data']['amount'],
            'order_no' => $order['data']['order_no']
        ];
        success($result);
    }

    /**
     * 优惠券领取支付
     * @url api/tourist_coupon/receive_pay
     * @http POST
     * @param $order_no
     * @param $pay_type
     * @throws ApiException
     */
    public function receivePay($order_no,$pay_type){
        $order = UserCouponOrderModel::where([
            'order_no' => $order_no,
        ])->find();
        if(!$order){
            error('订单不存在',40013);
        }
        if($order['state'] != 0){
            error('订单已支付',40014);
        }
        //如果金额为0，默认余额支付
        if($order['amount'] <= 0){
            $pay_type = 0;
        }
        if($pay_type == 0){
            Db::startTrans();
            try{
                CouponModel::userReceiveCoupon($order_no);
                MemberModel::changeBalance($this->uid,$order['amount'],2,10,$order['coupon_id']);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                throw $e;
            }
            success();
        }else{
            $payData = [
                'body' => '优惠券支付',
                'out_trade_no' => $order['order_no'],
                'total_fee' => $order['amount'] * 100,
                'notify_url' => url('wxpay/Notify/userReceiveCoupon','','',true),
                'trade_type' => 'JSAPI',
                'openid' => UserToken::getCurrentTokenVar('openid'),
                'time_expire'=>date('YmdHis', $order['expiration_time'])//设置订单过期支付时间
            ];
            $wechatPay = new WechatPay();
            $wxResult = $wechatPay->orderPay($payData);
            //TODO 跳板支付开始
            skip_pay('userReceiveCoupon',$order_no,$order['amount']);
            //TODO 跳板支付结束
            $result = [
                'wx_pay' => $wxResult
            ];
            success($result);
        }
    }
    /**
     * 支付卡券剩余金额
     * @url api/tourist_coupon/pay_coupon_residual_amount
     * @http POST
     */
    public function payCouponResidualAmount($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        $userCoupon = UserCouponModel::where([
            'id' => $id,
            'user_id' => $this->uid,
            'residual_is_pay' => 0
        ])->find();
        if(!$userCoupon){
            error('卡券订单无需支付',40004);
        }
        $orderNo = WechatPay::generateOrderNo();
        UserCouponResidualAmountOrderModel::create([
            'user_coupon_id' => $id,
            'is_pay' => 0,
            'order_no' => $orderNo,
            'amount' => $userCoupon['residual_amount']
        ]);
        $result = [
            'amount' => $userCoupon['residual_amount'],
            'order_no' => $orderNo
        ];
        success($result);
    }

    /**
     * 支付卡券剩余金额
     * @url api/tourist_coupon/pay_coupon_residual_amount_pay
     * @http POST
     */
    public function payCouponResidualAmountPay($order_no,$pay_type){
        $order = UserCouponResidualAmountOrderModel::where([
            'order_no' => $order_no,
        ])->find();
        if(!$order){
            error('订单不存在',40013);
        }
        if($order['is_pay'] != 0){
            error('订单已支付',40014);
        }
        //如果金额为0，默认余额支付
        if($order['amount'] <= 0){
            $pay_type = 0;
        }
        if($pay_type == 0){
            Db::startTrans();
            try{
                //修改订单状态
                $order->is_pay = 1;
                $order->save();
                $userCoupon = UserCouponModel::where('id',$order['user_coupon_id'])->find();
                //修改支付状态
                $userCoupon->residual_is_pay = 1;
                $userCoupon->save();
                //减少余额
                MemberModel::changeBalance($this->uid,$order['amount'],2,10,$userCoupon['coupon_id']);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                throw $e;
            }
            success();
        }else{
            $payData = [
                'body' => '优惠券支付',
                'out_trade_no' => $order_no,
                'total_fee' => $order['amount'] * 100,
                'notify_url' => url('wxpay/Notify/couponResidualAmount','','',true),
                'trade_type' => 'JSAPI',
                'openid' => UserToken::getCurrentTokenVar('openid')
            ];
            $wechatPay = new WechatPay();
            $wxResult = $wechatPay->orderPay($payData);
            //TODO 跳板支付开始
            skip_pay('couponResidualAmount',$order_no,$order['amount']);
            //TODO 跳板支付结束
            success([
                'wx_pay' => $wxResult
            ]);
        }
    }

    /**
     * 核销券详情
     * @url api/tourist_coupon/write_off_detail
     * @http POST
     */
    public function writeOffDetail($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        $userCouponModel = new UserCouponModel();
        $coupon = $userCouponModel->writeOffDetail($id);
        $coupon->code_img = url('QrCode/code',[
            'content'=> json_encode([
                'type' => 1,
                'code' => $coupon->code
            ])
        ],'',true);
        success($coupon);

    }

    /**
     * 领取赠送的卡券
     * @url api/tourist_coupon/receiving_gift_coupon
     * @http POST
     */
    public function receivingGiftCoupon($code = ''){
        if(empty($code)){
            error('code参数错误','10000');
        }
        $userCouponModel =new UserCouponModel();
        $userCouponModel->receivingGiftCoupon($code,$this->uid);
        success();
    }

    /**
     * 用户赠送卡券
     * @url api/tourist_coupon/gift_coupon
     * @http POST
     */
    public function giftCoupon(){
        $validate = new GiftCoupon();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $userCouponModel = new UserCouponModel();
        $code = $userCouponModel->giftCoupon($data['id'],$data['num'],$this->uid);
        success([
            'code' => $code
        ]);
    }

    /**
     * 用户赠送卡券详情页
     * @url api/tourist_coupon/gift_coupon_detail
     * @http POST
     */
    public function giftCouponDetail($code = ''){
        $validate = new TouristDetail();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $giftUserCoupon = GiftUserCouponModel::where('code',$data['code'])->find();
        if(!$giftUserCoupon){
            throw new ApiException([
                'msg' => '卡券不存在',
                'errorCode' => 40001
            ]);
        }
        $data['id'] = $giftUserCoupon->coupon_id;
        $data['type'] = 1;
        //优惠券
        $couponModel = new CouponModel();
        $coupon = $couponModel->detail($data);
        $coupon->stock = $giftUserCoupon->stock;//赋值库存
        success($coupon);
    }

    /**
     * 我的优惠券详情
     * @url api/tourist_coupon/my_coupon_detail
     * @http POST
     */
    public function myCouponDetail(){
        $validate = new MyCouponDetail();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $couponModel = new CouponModel();
        $coupon = $couponModel->detail($data);
        $coupon->stock = UserCouponModel::where([
            'coupon_id' => $data['id'],
            'is_share' => 0,
            'is_delete' => 0,
            'state' => 1
        ])->count();
        success($coupon);
    }
}