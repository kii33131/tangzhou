<?php

namespace app\admin\controller;
use app\admin\request\BannerRequest;
use app\model\BannerModel;

class Banner extends Base
{
    public function index(BannerModel $bannerModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->banners = $bannerModel->getAllList($params, $this->limit);
        return $this->fetch();
    }

    public function create(BannerModel $bannerModel, BannerRequest $request){
        if ($request->isPost()) {
            $data = $request->post();
            //判断同一地区是否有重复
            $region = [];
            foreach (['province','city','district'] as $val){
                if(!empty($data[$val])){
                    $region[$val] = $data[$val];
                }else{
                    $region[$val] = '';
                }
            }
            $banner = $bannerModel->where($region)->find();
            if($banner){
                $this->error('同一个区域不能设置两套轮播图');
            }
            $data['user_id'] = session('user.id');
            $bannerModel->store($data) ? $this->success('添加成功', url('Banner/index')) : $this->error('添加失败');
        }
        return $this->fetch();
    }


    public function edit(BannerModel $bannerModel, BannerRequest $request){
        $banner_id= $this->request->param('id');
        if (!$banner_id) {
            $this->error('不存在的数据');
        }
        $result=$bannerModel->findBy($banner_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($request->isPost()) {
            $data = $request->post();
            //判断同一地区是否有重复
            $region = [];
            foreach (['province','city','district'] as $val){
                if(!empty($data[$val])){
                    $region[$val] = $data[$val];
                }else{
                    $region[$val] = '';
                }
            }
            if(BannerModel::where($region)->where('id','<>',$banner_id)->find()){
                $this->error('同一个区域不能设置两套轮播图');
            }
            $data['user_id'] = session('user.id');

            if($bannerModel->updateBy($data['id'], $data) !== false){
                foreach ($result->imgs as $val){
                    if(!empty($val) && !in_array($val,$data['imgs'])){
                        @unlink(config('upload_file') . $val);
                    }
                }
                $this->success('编辑成功', url('Banner/index'));
            }else{
                $this->error('编辑失败');
            }
        }
        $this->assign('banner',$result);
        return $this->fetch();
    }


    public function delete(BannerModel $bannerModel){

        $banner_id = $this->request->post('id');
        if (!$banner_id) {
            $this->error('不存在数据');
        }
        $result=$bannerModel->findBy($banner_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($bannerModel->deleteBy($banner_id)) {
            foreach ($result->imgs as $val){
                @unlink(config('upload_file') . $val);
            }
            $this->success('删除成功', url('Banner/index'));
        }
        $this->error('删除失败');


    }

    public function upload(){
        $file = request()->file('file');
        $info = $file->move(config('upload_file'));
        if($info){
            return json([
                'errorCode' => 0,
                'data' => [
                    'url' => $info->getSaveName()
                ]
            ]);
        }else{
            return json([
                'errorCode' => 10001,
                'msg' => '上传图片失败'
            ]);
        }
    }

}