<?php

namespace app\admin\controller;
use app\admin\request\BannerRequest;
use app\model\BannerModel;
use app\model\ImgModel;

class Img extends Base
{
    public function index(ImgModel $imgModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->imgs = $imgModel->getAllList($params, $this->limit);
        return $this->fetch();
    }



}