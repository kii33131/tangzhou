<?php


namespace app\service;


use app\exceptions\ApiException;
use app\model\ConfigModel;
use EasyWeChat\Factory;

class WechatPayService
{
    protected $app;
    protected $config;

    public function __construct()
    {
        $this->config = [
            // 必要配置
            'app_id'             => ConfigModel::getParam('appid'),
            'mch_id'             => ConfigModel::getParam('mch_id'),
            'key'                => ConfigModel::getParam('pay_key'),   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => env('root_path') . 'pay_cery/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => env('root_path') . 'pay_cery/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '',     // 你也可以在下单时单独设置来想覆盖它
        ];
        $this->app =  Factory::payment($this->config);
    }

    /**
     * 下单
     * @param $data
     * @return array|string
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function orderPay($data){
        $result = $this->app->order->unify($data);
        if($result['return_code'] != 'SUCCESS'){
            throw new ApiException([
                'errorCode' => 999,
                'msg' => $result['return_msg']
            ]);
        }
        if($result['result_code'] != 'SUCCESS'){
            throw new ApiException([
                'errorCode' => 999,
                'msg' => $result['err_code_des']
            ]);
        }
        return $result;
    }

    public function transferToBalance($data){
        $result = $this->app->transfer->toBalance($data);
        if($result['return_code'] != 'SUCCESS'){
            throw new \Exception($result['return_msg']);
        }
        if($result['result_code'] != 'SUCCESS'){
            throw new \Exception($result['err_code_des']);
        }
        return $result;
    }

    /**
     * 生成订单号
     */
    public static function generateOrderNo(){
        return date('YmdHis') . rand(100000,999999);
    }
}