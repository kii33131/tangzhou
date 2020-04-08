<?php

namespace app\model;

class FeedbackModel extends BaseModel
{
    protected $name = 'feedback';

    /**
     * feedback List
     * @param $params
     * @return \think\Paginator
     */
    public function getList($params, $limit = self::LIMIT)
    {
        return $this->order('id desc')->paginate($limit, false, ['query' => request()->param()]);
    }
}