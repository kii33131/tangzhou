<?php

namespace app\admin\controller;


use app\admin\request\TemplateRequest;
use app\model\HelpCenterModel;
use app\model\TemplateModel;

class HelpCenter extends Base
{
    public function index(HelpCenterModel $HelpCenterModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->helpcenters  = $HelpCenterModel->getList($params,$this->limit);
        return $this->fetch();
    }

    public function create(HelpCenterModel $HelpCenterModel, TemplateRequest $request){

        if ($request->isPost()) {
            $data = $request->post();
            $data['user_id'] = session('user.id');
            $HelpCenterModel->store($data) ? $this->success('添加成功', url('HelpCenter/index')) : $this->error('添加失败');
        }
        return $this->fetch();
    }

    public function edit(HelpCenterModel $HelpCenterModel, TemplateRequest $request){
        $HelpCenter_id= $this->request->param('id');
        if (!$HelpCenter_id) {
            $this->error('不存在的数据');
        }
        $result=$HelpCenterModel->findBy($HelpCenter_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($request->isPost()) {
            $data = $request->post();
            $data['user_id'] = session('user.id');
            if($HelpCenterModel->updateBy($data['id'], $data) !== false){
                $this->success('编辑成功', url('HelpCenter/index'));
            }else{
                $this->error('');
            }
        }
        $this->assign('helpcenter',$result);
        return $this->fetch();
    }

    public function delete(HelpCenterModel $HelpCenterModel){

        $HelpCenter_id = $this->request->post('id');
        if (!$HelpCenter_id) {
            $this->error('不存在数据');
        }
        $result=$HelpCenterModel->findBy($HelpCenter_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($HelpCenterModel->deleteBy($HelpCenter_id)) {

            $this->success('删除成功', url('HelpCenter/index'));
        }
        $this->error('删除失败');


    }


}