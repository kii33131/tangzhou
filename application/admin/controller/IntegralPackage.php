<?php

namespace app\admin\controller;

use app\admin\request\IntegralPackageRequest;
use app\model\IntegralPackageModel;

class IntegralPackage extends Base
{
    public function index()
    {
        $this->integralPackages = IntegralPackageModel::paginate($this->limit);
        return $this->fetch();
    }

    /**
     * Create Data
     *
     * @return mixed|string
     */
    public function create(IntegralPackageModel $integralPackageModel, IntegralPackageRequest $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            $integralPackageModel->store($data) ? $this->success('添加成功', url('integralPackage/index')) : $this->error('添加失败');
        }
        return $this->fetch();
    }

    /**
     * Edit Data
     *
     * @return mixed|string
     */
    public function edit(IntegralPackageModel $integralPackageModel, IntegralPackageRequest $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            $integralPackageModel->updateBy($data['id'], $data) !== false ? $this->success('编辑成功', url('IntegralPackage/index')) : $this->error('');
        }
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('不存在的数据');
        }
        $this->integralPackage = $integralPackageModel->findBy($id);
        return $this->fetch();
    }

    /**
     * Delete Data
     *
     * @return void
     */
    public function delete(IntegralPackageModel $integralPackageModel)
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        if ($integralPackageModel->deleteBy($id)) {
            $this->success('删除成功', url('IntegralPackage/index'));
        }
        $this->error('删除失败');
    }
}