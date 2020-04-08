<?php


namespace app\api\controller;

use app\model\ImgModel;
use app\model\MemberModel;
use app\model\MemberRequestLogModel;
use app\model\StoreModel;
use think\App;
use think\Controller;
use app\api\service\Token as TokenService;
use think\Request;

class Base extends Controller
{
    protected $uid;//用户id
    protected $listRows = 10;//每页显示数量

    public function __construct(App $app = null)
    {
        parent::__construct($app);
       // $this->uid = TokenService::getCurrentUid();//获取用户id
        $this->setListRows();//设置分页数量
    }

    /**
     * 设置分页数量
     */
    protected function setListRows(){
        if(preg_match("/^[1-9][0-9]*$/",input('list_rows'))){
            $this->listRows = input('list_rows');
        }
    }

    protected function checkParams(&$params)
    {
        foreach ($params as $key => $param) {
            if (!$param || $key == 'limit' || $key == 'page') {
                unset($params[$key]);
            }
        }
    }
}