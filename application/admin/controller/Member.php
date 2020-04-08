<?php

namespace app\admin\controller;


use app\model\MemberModel;

class Member extends Base
{
	public function index(MemberModel $memberModel)
	{
		$params = $this->request->param();
		$this->checkParams($params);
		$this->members = $memberModel->getList($params, $this->limit);

		return $this->fetch();
	}
}