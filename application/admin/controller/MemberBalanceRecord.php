<?php

namespace app\admin\controller;


use app\model\BalanceRecordModel;

class MemberBalanceRecord extends Base
{
	public function index(BalanceRecordModel $balanceRecordModel)
	{
		$params = $this->request->param();
		$this->checkParams($params);
		$this->records = $balanceRecordModel->getList($params, $this->limit);

		return $this->fetch();
	}
}