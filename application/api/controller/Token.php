<?php


namespace app\api\controller;


use app\api\service\UserToken;
use app\api\validate\TokenGet;
use app\model\StoreModel;

class Token
{
    /**
     * 用户获取令牌（登陆）
     * @url api/token/user
     * @http POST
     * @POST code       登录凭证 code
     * @POST name       昵称
     * @POST picture    头像
     */
    public function getToken($code = '', $name = '', $picture = '')
    {
        (new TokenGet())->goCheck();
        $wx = new UserToken($code, $name, $picture);
        $result = $wx->get();
        success($result);
    }
}