<?php

namespace app\admin\controller;

use app\admin\request\StoreRequest;
use app\enum\StoreEnum;
use app\model\IndustryCategoryModel;
use app\model\MessageModel;
use app\model\StoreModel;

class StoreExamine extends Base
{
    public function index(StoreModel $storeModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->stores = $storeModel->getList($params, $this->limit);
        $this->assign([
            'storeStates' => StoreEnum::STATE,
        ]);
        return $this->fetch();
    }

    /**
     * Edit Data
     *
     * @return mixed|string
     */
    public function edit(StoreModel $storeModel, StoreRequest $request)
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('不存在的数据');
        }
        $store = $storeModel->findBy($id);
        if (!$store) {
            $this->error('不存在的数据');
        }
        $this->assign([
            'storeStates' => StoreEnum::STATE,
        ]);
        $this->assign('parentIndustryCategory',IndustryCategoryModel::getParentCategory());
        $store->industry_category_pid = IndustryCategoryModel::where('id',$store['industry_category_id'])->value('pid');
        $this->assign('store',$store);
        return $this->fetch();
    }

    /**
     * Delete Data
     *
     * @return void
     */
    public function delete(StoreModel $storeModel)
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        $store = $storeModel->findBy($id);
        if (!$store) {
            $this->error('不存在的数据');
        }
        if($storeModel->updateBy($id, [
            'is_delete' => 1
            ]) !== false) {
            $this->success('删除成功', url('StoreExamine/index'));
        }
        $this->error('删除失败');
    }

    /**
     * 审核通过
     *
     * @return void
     */
    public function pass(StoreModel $storeModel)
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        $store = $storeModel->findBy($id);
        if (!$store) {
            $this->error('不存在的数据');
        }
        if ($storeModel->update([
            'id' => $id,
            'state' => 2,
            'entry_time' => time()
        ])) {
            MessageModel::create([
                'member_id' => $store->user_id,
                'msg' => "您入驻的店铺已审核通过！"
            ]);
            $this->success('通过成功', url('StoreExamine/index'));
        }
        $this->error('通过失败');
    }

    /**
     * 审核拒绝
     *
     * @return void
     */
    public function refuse(StoreModel $storeModel)
    {
        $id = $this->request->post('id');
        $msg = $this->request->post('msg');
        if (!$id) {
            $this->error('不存在数据');
        }
        $store = $storeModel->findBy($id);
        if (!$store || $store['state'] != 1) {
            $this->error('不存在的数据');
        }
        if ($storeModel->update([
            'id' => $id,
            'state' => 3,
            'reject_reason' => $msg
        ])) {
            MessageModel::create([
                'member_id' => $store->user_id,
                'msg' => "您入驻的店铺已被拒绝！"
            ]);
            $this->success('拒绝成功', url('StoreExamine/index'));
        }
        $this->error('拒绝失败');
    }

    public function getIndustryCategoryByPid(){
        return IndustryCategoryModel::field('id,name')->where('pid',input('pid'))->select();
    }
}