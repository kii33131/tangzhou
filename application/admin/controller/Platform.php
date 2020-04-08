<?php

namespace app\admin\controller;

use app\enum\PlatformBalanceRecord;
use app\model\PlatformBalanceOrder;
use app\model\PlatformBalanceRecordModel;
use app\model\PlatformModel;
use app\service\QrcodeServer;
use app\service\WechatPayService;

class Platform extends Base
{
    public function balance()
    {
        $platform = PlatformModel::get(1);
        $this->assign('platform',$platform);
        return $this->fetch();
    }

    public function balanceRecords(PlatformBalanceRecordModel $platformBalanceRecord){
        $this->assign('balancePayType',PlatformBalanceRecord::PAYTYPE);
        $params = $this->request->param();
        $this->checkParams($params);
        $params['state'] = 1;
        $params['type'] = 1;
        $this->records = $platformBalanceRecord->getAllList($params,$this->limit);
        return $this->fetch();
    }

    public function balanceConsumption(PlatformBalanceRecordModel $platformBalanceRecord){
        $this->assign('balanceState',PlatformBalanceRecord::STATE);
        $params = $this->request->param();
        $this->assign([
            'states' => PlatformBalanceRecord::STATE
        ]);
        $this->checkParams($params);
        $this->records = $platformBalanceRecord->getAllList($params,$this->limit);
        return $this->fetch();
    }

    public function recharge(){
        return $this->fetch();
    }

    public function rechargeWxPay($amount){
        $weChatPay = new WechatPayService();
        $orderNo = $weChatPay->generateOrderNo();
        try{
            //创建预支付订单
            PlatformBalanceOrder::create([
                'amount' => $amount,
                'pay_type' => 1,
                'user_id' => session('user.id'),
                'order_no' => $orderNo,
                'is_pay' => 0
            ]);
            //生成支付订单参数
            $result = $weChatPay->orderPay($payData = [
                'body' => '平台充值',
                'out_trade_no' => $orderNo,
                'total_fee' => $amount * 100,
                'notify_url' => url('wxpay/Notify/platformBalance','','',true),
                'trade_type' => 'NATIVE',
                'product_id' => $orderNo
            ]);
            if(!array_key_exists('code_url',$result)){
                throw new \Exception('获取支付码失败');
            }
            //TODO 跳板支付开始
            skip_pay('platformBalance',$orderNo,$amount);
            //TODO 跳板支付结束

            return json([
                'error_code' => 0,
                'data' => [
                    'code_url' => url('payCode','','') . "?url={$result['code_url']}",
                    'order_no' => $orderNo
                ]
            ]);
        }catch (\Exception $e){
            return json([
                'error_code' => 1,
                'msg' => $e->getMessage()
            ]);
        }

    }

    /**
     * 判断是否支付成功
     * @param $orderNo  订单号
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isPay($orderNo){
        $order = PlatformBalanceOrder::where([
            'order_no' => $orderNo,
            'is_pay' => 1
        ])->find();
        $is_pay = 0;
        if($order){
            $is_pay = 1;
        }
        return json([
            'error_code' => 0,
            'data' => [
                'is_pay' => $is_pay
            ]
        ]);
    }

    /**
     * 生成二维码
     * @param string $url 链接
     */
    public function payCode($url = ''){
        $qr_code = new QrcodeServer();
        $qr_img = $qr_code->createServer($url);
        echo $qr_img;
        exit();
    }

}