<?php

namespace app\agent\controller;


use app\model\BalanceRecordModel;
use app\model\MemberModel;

class MemberBalanceRecord extends Base
{
	public function index(BalanceRecordModel $balanceRecordModel)
	{
		$params = $this->request->param();
		$this->checkParams($params);
		if(empty($params['id'])){
		    $this->error('参数错误');
        }
		if(!$this->isStoreBelong($params['id'])){
            $this->error('商家不存在');
        }
		$params['member_id'] = MemberModel::getUidByStoreId($params['id']);
		unset($params['id']);
		$this->records = $balanceRecordModel->getList($params, $this->limit);

		return $this->fetch();
	}
}