<?php


namespace app\api\controller;

use app\api\validate\IDMustBePositiveInt;
use app\model\MemberModel;
use app\api\service\StoreStaff as StoreStaffService;
use app\model\StoreStaffModel;

class StoreStaff extends Base
{
    /**
     * 员工列表
     * @url api/store_staff/index
     * @http GET
     */
    public function index(){
        $storeId = MemberModel::getStoreIdByUid($this->uid);
        success(StoreStaffModel::getList($storeId,$this->listRows));
    }

    /**
     * 添加员工
     * @url api/store_staff/add_staff
     * @http POST
     */
    public function addStaff($key = ''){
        $storeId = StoreStaffService::getStoreIdByKey($key);
        StoreStaffModel::addStaff($storeId,$this->uid);
        success();
    }

    /**
     * 获取添加员工码
     * @url api/store_staff/add_staff_code
     * @http GET
     */
    public function addStaffCode(){
        $storeId = MemberModel::getStoreIdByUid($this->uid);
        $storeStaffService = new StoreStaffService();
        success([
            'code_img' => $storeStaffService->getCode($storeId)
        ]);
    }

    /**
     * 删除员工
     * @url api/store_staff/del_staff
     * @http POST
     */
    public function delStaff($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        StoreStaffModel::where([
            'id' => $id,
            'store_id' => MemberModel::getStoreIdByUid($this->uid)
        ])->delete();
        success();
    }

}