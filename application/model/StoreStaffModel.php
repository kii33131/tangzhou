<?php

namespace app\model;

class StoreStaffModel extends BaseModel
{
    protected $name = 'store_staff';

    public function userInfo(){
        return $this->belongsTo('MemberModel','user_id')->bind('name,picture');
    }

    /**
     * 添加员工
     * @param $storeId 门店ID
     * @param $userId 用户ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addStaff($storeId,$userId){
        $data = [
            'store_id' => $storeId,
            'user_id' => $userId
        ];
        $staff = self::where($data)->find();
        if(!$staff){
            self::create($data);
        }
        return true;
    }

    /**
     * 获取员工列表
     * @param $storeId  门店ID
     * @param int $listRows 每页数量
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getList($storeId,$listRows = 10){
        return self::with('userInfo')
            ->where('store_id',$storeId)
            ->order('id desc')
            ->paginate($listRows)->hidden(['user_id','store_id']);
    }
}