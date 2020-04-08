<?php

namespace app\traits;

use app\model\AgentModel;
use think\facade\Session;
use think\Request;
use think\Validate;

trait AgentAuth
{
	protected $loginUserKey = 'agent';

	public function authLogin(Request $request)
	{
		$err = $this->validateLogin($request);
		if ($err) {
			$this->error($err);
		}

		// 正常输入登录
        $agentModel = new AgentModel();
		$user = $agentModel::where([
		    'account' => input('account'),
            'password' => md5(input('password')),
            'is_delete' => 0
        ])->find();

		if (!$user) {
			$this->error('账号或密码错误');
		}
        Session::set($this->loginUserKey, $user);
        $this->success('登录成功', url($this->redirect));
	}

	/**
	 * 退出
	 * @return void
	 */
	public function authLogout()
	{
		$user = Session::get($this->loginUserKey);
		Session::delete($this->loginUserKey);
	}

	/**
	 * 验证
	 * @param Request $request
	 * @return array|bool
	 */
	protected function validateLogin(Request $request)
	{
		$validate = new Validate($this->rule());
		if (!$validate->check($request->param())) {
			return $validate->getError();
		}
		return false;
	}

	/**
	 * 登录验证规则
	 * @return array
	 */
	protected function rule()
	{
		return [
			$this->name()    => 'require|alphaDash',
			'password|密码'  => 'require|alphaDash',
			'captcha|验证码' => 'require|captcha'
		];
	}

	/**
	 * 设置登录字段
	 *
	 * @return string
	 */
	protected function name()
	{
		return 'account|用户名';
	}

}