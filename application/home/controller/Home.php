<?php

namespace app\home\controller;



use think\Db;

class Home extends Base
{
    public function index()
    {
        //echo 'aa111';exit;
        return $this->fetch();
    }

    public function img()
    {  // echo '<pre>';
       // print_r($_GET);exit;
        //echo 'aa111';exit;
        $this->assign('img',$_GET['backurl']);
        return $this->fetch();
    }
}