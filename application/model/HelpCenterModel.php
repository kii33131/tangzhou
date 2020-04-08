<?php

namespace app\model;

class HelpCenterModel extends BaseModel
{
    protected $name = 'help_center';

    public function getList($params, $limit = self::LIMIT){

        $helpCenter = $this->alias('c')->join('users u','u.id=c.user_id')
            ->field('u.name user_name,c.name,c.content,c.create_time,c.id')
            ->order('id desc')
            ->paginate($limit, false, ['query' => request()->param()]);
        return $helpCenter;
    }


}