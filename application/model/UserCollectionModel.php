<?php

namespace app\model;

class UserCollectionModel extends BaseModel
{
    protected $name = 'user_collection';

    //获取收藏的店铺
    public static function getCollectionList($uid,$listRows){
        return UserCollectionModel::alias('c')
            ->field('c.id,s.name,s.logo,s.start_hours,s.end_hours,industry_category_id,s.id store_id')
            ->join('store s','c.store_id = s.id')->order('c.id desc')->where('c.user_id',$uid)->paginate($listRows)->each(function($item, $key){
                //行业分类
                $industryCategory = IndustryCategoryModel::getSecondLevelName($item->industry_category_id);
                $item->category_name_p = $industryCategory['category_name_p'];
                $item->category_name_s = $industryCategory['category_name_s'];
                $item->start_hours = substr($item->start_hours,0,-3);
                $item->end_hours = substr($item->end_hours,0,-3);
                unset($item->industry_category_id);
            });
    }

    /**
     * 是否收藏店铺
     * @param $uid
     * @param $store_id
     * @return int
     */
    public static function isCollection($uid,$store_id){
        if(self::where([
                'store_id' => $store_id,
                'user_id' => $uid
            ])->count() > 0){
            return 1;
        }else{
            return 0;
        }
    }
}