<?php

namespace app\admin\controller;


use app\admin\request\TemplateRequest;
use app\model\TemplateModel;

class Template extends Base
{
    public function index(TemplateModel $templateModell)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->templates  = $templateModell->getTemplateList($params,$this->limit);
        return $this->fetch();
    }

    public function create(TemplateModel $templateModell, TemplateRequest $request){

        if ($request->isPost()) {
            $data = $request->post();
            $data['user_id'] = session('user.id');
            $templateModell->store($data) ? $this->success('添加成功', url('Template/index')) : $this->error('添加失败');
        }
        return $this->fetch();
    }


    public function edit(TemplateModel $templateModell,TemplateRequest $request){
        $template_id= $this->request->param('id');
        if (!$template_id) {
            $this->error('不存在的数据');
        }
        $result=$templateModell->findBy($template_id);
        if (!$result) {
            $this->error('不存在的数据');
        }

        if ($request->isPost()) {
            $data = $request->post();
            $data['user_id'] = session('user.id');

            if($templateModell->updateBy($data['id'], $data) !== false){
                $this->success('编辑成功', url('Template/index'));
            }else{
                $this->error('');
            }
        }
        $this->assign('template',$result);
        return $this->fetch();
    }


    public function delete(TemplateModel $templateModell){

        $template_id = $this->request->post('id');
        if (!$template_id) {
            $this->error('不存在数据');
        }
        $result=$templateModell->findBy($template_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($templateModell->deleteBy($template_id)) {

            $this->success('删除成功', url('Template/index'));
        }
        $this->error('删除失败');


    }

}