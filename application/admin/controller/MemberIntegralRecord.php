<?php

namespace app\admin\controller;


use app\model\IntegralRecordModel;

class MemberIntegralRecord extends Base
{
	public function index(IntegralRecordModel $integralRecordModel)
	{
		$params = $this->request->param();
		$this->checkParams($params);
		$this->records = $integralRecordModel->getList($params, $this->limit);

		return $this->fetch();
	}
}