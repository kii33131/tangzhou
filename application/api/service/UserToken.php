<?php

namespace app\api\service;

use app\exceptions\ApiException;
use app\model\ConfigModel;
use app\model\MemberModel;
use app\model\StoreModel;
use EasyWeChat\Factory;
use think\Exception;


class UserToken extends Token
{
    protected $code;
    protected $name;
    protected $picture;
    protected $config = [];
    protected $app;

    function __construct($code,$name,$picture)
    {
        $this->code = $code;
        $this->name = $name;
        $this->picture = $picture;
        $wxConfig = ConfigModel::field('appid,appsecret')->get(1);
        if(empty($wxConfig) || empty($wxConfig['appid']) || empty($wxConfig['appsecret'])){
            throw new Exception('微信小程序配置信息不存在',999);
        }
        $this->config = [
            'app_id' => $wxConfig['appid'],
            'secret' => $wxConfig['appsecret'],
            'response_type' => 'array'
        ];
        $this->app = Factory::miniProgram($this->config);
    }


    /**
     * 获取token
     * @return string
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function get()
    {
        $wxResult = $this->app->auth->session($this->code);
        $loginFail = array_key_exists('errcode', $wxResult);
        if ($loginFail) {
            throw new ApiException([
                'msg' => $wxResult['errmsg'],
                'errorCode' => 999
            ]);
        }
        else {
            return $this->grantToken($wxResult);
        }
    }


    // 写入缓存
    private function saveToCache($wxResult)
    {
        $key = self::generateToken();
        $value = json_encode($wxResult);
        $expire_in = config('api.token_expire_in');
        $result = cache($key, $value, $expire_in);

        if (!$result){
            throw new Exception([
                'msg' => '服务器缓存异常',
                'errorCode' => 999
            ]);
        }
        return $key;
    }

    private function grantToken($wxResult)
    {
        $openid = $wxResult['openid'];
        $member = MemberModel::where('openid',$openid)->find();
        $result = [
            'token' => '',
            'is_store' => 0
        ];
        if (!$member)
        {
            $uid = $this->newUser($openid);
        }
        else {
            $uid = $member->id;
            if(MemberModel::isStore($uid)){
                $result['is_store'] = 1;
            }
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);
        $result['token'] = $token;
        return $result;
    }


    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        return $cachedValue;
    }

    // 创建新用户
    private function newUser($openid)
    {
        $member = MemberModel::create(
            [
                'openid' => $openid,
                'name' => $this->name,
                'picture' => $this->picture,
                'extension_code' => '',
                'create_time' => time()
            ]);
        return $member->id;
    }

    /**
     * 生成推荐码
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function generateExtensionCode(){
        $extensionCode = strtolower(getRandChar(6));
        if(MemberModel::where('extension_code',$extensionCode)->find()){
            return $this->generateExtensionCode($extensionCode);
        }
        return $extensionCode;
    }
}
