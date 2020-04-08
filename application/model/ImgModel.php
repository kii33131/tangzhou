<?php

namespace app\model;

class ImgModel extends BaseModel
{
    protected $name = 'img';
    public function getAllList($params, $limit = self::LIMIT)
    {
        $banner = $this;
        foreach (['province','city','district'] as $val){
            if(!empty($params[$val])){
                $banner = $banner->where($val,$params[$val]);
            }
        }
        return $banner->order('id desc')->paginate($limit, false, ['query' => request()->param()])->each(function ($item){
            $item->img = str_replace('assets/uploads','', $item->img);
        });
    }

}