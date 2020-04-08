<?php


namespace app\api\controller;


use app\exceptions\ApiException;
use app\model\ImgModel;
use think\Exception;

class Upload extends Base
{

    /**
     * 图片上传
     * @url api/upload/img
     * @http POST
     */
    public function img(){
        if(!empty($_FILES['file']['name'])){
            $file = request()->file('file');
            $token = input('post.token');
            $str = input('post.str');
            if(!$token){
                error('请传递token',400);
            }
            $str = md5($str.'tangzhou');
            if($token != $str){
                error('无效的token',400);
            }
            $info = $file->move(config('upload_file'));
            if($info){
                $code =$this->createCode(6);
                $date = date('Y-m-d H:i:s');
                ImgModel::create(['img'=>'/assets/uploads/'.$info->getSaveName(),'code'=>$code,'created_at'=>$date]);
                success(['img'=>'/assets/uploads/'.$info->getSaveName(),'code'=>$code,'created_at'=>$date]);
            }else{
                throw new Exception([
                    'msg' => $file->getError(),
                    'errorCode' => '999'
                ]);
            }
        }else{
            throw new ApiException([
                'msg' => '未上传图片',
                'errorCode' => '10000'
            ]);
        }
    }

    public  function createCode($user_id) {

       return substr(base_convert(md5(uniqid(md5(microtime(true)),true)), 16, 10), 0, 6);

    }


}