<?php


namespace app\api\service;


use app\exceptions\ApiException;
use app\model\ConfigModel;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Http\StreamResponse;
use think\Exception;

class Wechat
{
    protected $app;
    protected $config;

    public function __construct()
    {
        $this->config = [
            'app_id' => ConfigModel::getParam('appid'),
            'secret' => ConfigModel::getParam('appsecret'),

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
        ];
        $this->app =  $app = Factory::miniProgram($this->config);
    }

    public function appCodeUnlimit($page ='',$scene = '',$width = 600){
        $response = $this->app->app_code->getUnlimit($scene, [
            'page'  => $page,
            'width' => $width,
        ]);
        if (!($response instanceof StreamResponse)) {
            throw new Exception($response['errmsg']);
        }
        return $response;
    }
}