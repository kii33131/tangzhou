<?php

namespace app\model;

class IndustryCategoryModel extends BaseModel
{
    protected $name = 'industry_category';

    /**
     * 获取关联管理员
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('UserModel','user_id');
    }

    /**
     * 获取两级分类名称
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSecondLevelName($id){
        $result = [
            'category_name_p' => '',
            'category_name_s' => ''
        ];
        $categoryNameS = self::where('id',$id)->find();
        if($categoryNameS){
            if($categoryNameS['pid'] != 0){
                $categoryNameP = self::where('id',$categoryNameS['pid'])->find();
                if($categoryNameP){
                    $result['category_name_p'] = $categoryNameP['name'];
                    $result['category_name_s'] = $categoryNameS['name'];
                }else{
                    $result['category_name_p'] = $categoryNameP['name'];
                }
            }else{
                $result['category_name_p'] = $categoryNameS['name'];
            }
        }
        return $result;
    }


    public static function getParentCategory($limit=0){
        if($limit){
            return self::where('pid',0)->limit($limit)->select();
        }else{
            return self::where('pid',0)->select();
        }

    }

    public function get_category_by_id($id){
       return self::get($id);
    }

    public function get_pcategory_by_id($id){
        $result= self::get($id);
        if($result){
            $presult= self::get($result['pid']);

        }
        return $presult;
    }

}