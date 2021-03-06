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



}