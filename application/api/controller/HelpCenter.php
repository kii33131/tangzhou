<?php


namespace app\api\controller;


use app\model\HelpCenterModel;

class HelpCenter extends Base
{
    /**
     * 帮助中心列表
     * @url api/help_center/index
     * @http GET
     */
    public function index(){
        success(HelpCenterModel::field('id,name,content')->order('id desc')->paginate($this->listRows));
    }
}