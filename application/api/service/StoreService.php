<?php


namespace app\api\service;


use app\exceptions\ApiException;
use app\model\MemberModel;
use app\model\StoreStaffModel;

class StoreService
{
    /**
     * 获取门店id
     * @param $userId int 用户id
     * @return mixed
     * @throws ApiException
     */
    public static function getStoreId($userId){
        $storeId = request()->header('storeid');
        if(!empty($storeId)){
            $storeStaff = StoreStaffModel::where([
                'store_id' => $storeId,
                'user_id' => $userId
            ])->find();
            if(!$storeStaff){
                throw new ApiException([
                    'msg' => '员工无权限管理当前门店',
                    'errorCode' => 30006
                ]);
            }
        }else{
            $storeId = MemberModel::getStoreIdByUid($userId,true);
        }
        return $storeId;
    }

    /**
     * 获取用户id
     * @param $userId
     * @return mixed
     * @throws ApiException
     */
    public static function getStoreUserId($userId){
        $storeId = self::getStoreId($userId);
        return MemberModel::getUidByStoreId($storeId,true);
    }
}