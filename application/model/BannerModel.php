<?php

namespace app\model;

class BannerModel extends BaseModel
{
    protected $name = 'banner';
    protected $type = [
        'imgs'    =>  'json',
    ];
    public function get_banner($city){
        $list = self::where(['state'=>1,'city'=>$city])->field('id,title,img1,img2,img3,url')->order('id', 'desc')->select();
        return $list;
    }

    public function getAllList($params, $limit = self::LIMIT)
    {
        $banner = $this;
        foreach (['province','city','district'] as $val){
            if(!empty($params[$val])){
                $banner = $banner->where($val,$params[$val]);
            }
        }
        return $banner->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }
}