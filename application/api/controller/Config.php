<?php


namespace app\api\controller;


use app\model\ConfigModel;
use app\wxpay\Notify;

class Config extends Base
{
    public function getWxConfig(){
        success([
            'wechat_title' => ConfigModel::getParam('wechat_title'),
            'platform_name' => ConfigModel::getParam('platform_name'),
            'platform_phone' => ConfigModel::getParam('platform_phone'),
            'introduce' => ConfigModel::getParam('introduce'),
            'upload_domain' => config('upload_domain'),
            'panic_buying_coupon_release_agreement' => ConfigModel::getParam('panic_buying_coupon_release_agreement'),
            'promotion_coupon_release_agreement' => ConfigModel::getParam('promotion_coupon_release_agreement'),
            'service_charge' => ConfigModel::getParam('service_charge'),
            'coupon_release_integral' => ConfigModel::getParam('coupon_release_integral'),
        ]);
    }
}