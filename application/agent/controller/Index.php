<?php

namespace app\agent\controller;

use app\enum\AgentEnum;
use app\model\AgentModel;
use app\traits\AgentControllerTrait;

class Index extends Base
{
    use AgentControllerTrait;

	/**
	 * 首页
	 *
	 * @time at 2018年11月15日
	 * @return mixed|string
	 */
    public function index()
    {
    	$loginUser = $this->getLoginUser();
		$this->loginUser   = $loginUser;
        return $this->fetch();
    }

	/**
	 * main
	 *
	 * @time at 2018年11月16日
	 * @return mixed|string
	 */
    public function main()
    {
        $loginUser = $this->getLoginUser();
        $agent = AgentModel::where('id',$loginUser['id'])->find();
        $this->agent  = $agent;
        $this->assign('agentLevel',AgentEnum::LEVEL);
        return $this->fetch();
    }
}