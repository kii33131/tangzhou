<?php
namespace app\admin\controller;

use app\admin\request\IndustryCategoryRequest;
use app\model\IndustryCategoryModel;
use app\service\IndustryCategoryService;
use think\Collection;

class IndustryCategorySettings extends Base
{
    public function index(IndustryCategoryService $industryService)
    {
        $this->industrys = new Collection($industryService->sort(IndustryCategoryModel::with('user')->select()));
        return $this->fetch();
    }

	/**
	 * Create Data
	 *
	 * @return mixed|string
	 */
    public function create(IndustryCategoryModel $industryCategory,IndustryCategoryRequest $request, IndustryCategoryService $industryService)
    {
    	if ($request->isPost()) {
    		$data = $request->post();
            //图标上传
            if(!empty($_FILES['icon']['name'])){
                $file = request()->file('icon');
                $info = $file->move(config('upload_file'));
                if($info){
                    $data['icon'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }else{
                $this->error('请选择图标');
            }
            $data['user_id'] = session('user.id');
            $industryCategory->store($data) ? $this->success('添加成功', url('IndustryCategorySettings/index')) : $this->error('添加失败');
	    }
	    $this->industrys = $industryService->sort(IndustryCategoryModel::where('pid',0)->select());
    	$this->industryId  = $this->request->param('id') ?? 0;
        return $this->fetch();
    }

	/**
	 * Edit Data
	 *
	 * @return mixed|string
	 */
    public function edit(IndustryCategoryModel $industryCategory,IndustryCategoryRequest $request, IndustryCategoryService $industryService)
    {
        $industryId = $this->request->param('id');
        if (!$industryId) {
            $this->error('不存在的数据');
        }
        $industry = $industryCategory->findBy($industryId);
        if (!$industry) {
            $this->error('不存在的数据');
        }
        if ($request->isPost()) {
    		$data = $request->post();
    		unset($data['icon']);
            //图标上传
            if(!empty($_FILES['icon']['name'])){
                $file = request()->file('icon');
                $info = $file->move(config('upload_file'));
                if($info){
                    $data['icon'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
    		if($industryCategory->updateBy($data['id'], $data) !== false){
    		    if(!empty($data['icon']) && !empty($industry['icon'])){
                    @unlink(config('upload_file') . $industry['icon']);
                }
                $this->success('编辑成功', url('IndustryCategorySettings/index'));
            }else{
                $this->error('');
            }
	    }
        $this->assign('industry',$industry);
        $this->industrys = $industryService->sort(IndustryCategoryModel::where('pid',0)->select());
        return $this->fetch();
    }

	/**
	 * Delete Data
	 *
	 * @return void
	 */
    public function delete(IndustryCategoryModel $industryCategory)
    {
    	$industryId = $this->request->post('id');
    	if (!$industryId) {
    		$this->error('不存在数据');
	    }
	    if (IndustryCategoryModel::where('pid', $industryId)->find()) {
    		$this->error('请先删除子行业');
	    }
        $industry = $industryCategory->findBy($industryId);
        if (!$industry) {
            $this->error('不存在的数据');
        }
        if ($industryCategory->deleteBy($industryId)) {
            if(!empty($industry['icon'])){
                @unlink(config('upload_file') . $industry['icon']);
            }
		    $this->success('删除成功', url('IndustryCategorySettings/index'));
	    }
	    $this->error('删除失败');
    }
}