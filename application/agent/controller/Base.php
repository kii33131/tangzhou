<?php

namespace app\agent\controller;

use app\model\StoreModel;
use app\traits\AgentControllerTrait;
use think\Controller;

abstract class Base extends Controller
{
    use AgentControllerTrait;

    protected $limit = 10;

	protected $page  = 1;

	protected $middleware = ['checkAgentLogin'];

    protected $region = [];
    public function initialize()
    {
        $user = $this->getLoginUser();
        foreach (['province','city','district'] as $val){
            if(!empty($user[$val])){
                $this->region[$val] = $user[$val];
            }
        }
    }
	/**
	 * 过滤参数
	 *
	 * @time at 2018年11月15日
	 * @param $params
	 * @return void
	 */
	protected function checkParams(&$params)
	{
		$this->limit = $params['limit'] ?? $this->limit;
		$this->page  = $params['page'] ?? $this->page;

		foreach ($params as $key => $param) {
			if (!$param || $key == 'limit' || $key == 'page') {
				unset($params[$key]);
			}
		}
		$this->start = $this->start();
	}

	/**
	 * Table ID Start
	 *
	 * @time at 2018年11月16日
	 * @return float|int
	 */
	protected function start()
	{
		return (int)$this->limit * ((int)$this->page - 1) + 1;
	}

    /**
     * 添加地区参数
     * @param $params
     */
	public function addRegionParams(&$params){
	    foreach ($this->region as $key => $val){
	        $params[$key] = $val;
        }
    }

    /**
     * 代理是否有权限管理当前商家
     * @param $id int 商家id
     * @return bool
     */
    public function isStoreBelong($id){
        $store = StoreModel::where('id',$id)->where($this->region)->find();
        if($store){
            return true;
        }else{
            return false;
        }
    }
}
