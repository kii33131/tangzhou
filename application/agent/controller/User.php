<?php

namespace app\agent\controller;

use app\model\AgentModel;
use app\agent\request\UserRequest;

class User extends Base
{
	public function edit(AgentModel $agentModel, UserRequest $request)
	{
		if ($request->isPost()) {
			$data = $request->post();
			$user = $this->getLoginUser();
			if($user['password'] != md5($data['old_password'])){
                $this->error('旧密码错误');
            }
            $password = md5($data['password']);
            $agentModel->updateBy($user['id'], ['password' => $password]) ? $this->success('修改成功') : $this->error('修改失败');
		}
		return $this->fetch();
	}
}