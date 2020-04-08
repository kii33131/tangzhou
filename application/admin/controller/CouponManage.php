<?php

namespace app\admin\controller;

use app\enum\CouponEnum;
use app\admin\request\CouponRequest;
use app\model\CouponModel;
use app\model\MemberModel;
use app\model\MessageModel;
use think\Db;

class CouponManage extends Base
{
    public function index(CouponModel $couponModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->coupons = $couponModel->getList($params, $this->limit);
        $this->assign([
            'couponStates' => CouponEnum::STATE,
            'couponTypes' =>CouponEnum::TYPE,
            'couponEditStates' => CouponEnum::EDITSTATE
        ]);
        return $this->fetch();
    }

    public function StoreCoupons(CouponModel $couponModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->coupons = $couponModel->getList($params, $this->limit);
        $this->assign([
            'couponStates' => CouponEnum::STATE,
            'couponTypes' =>CouponEnum::TYPE,
            'couponEditStates' => CouponEnum::EDITSTATE
        ]);
        return $this->fetch();
    }

    /**
     * Edit Data
     *
     * @return mixed|string
     */
    public function edit(CouponModel $couponModel, CouponRequest $request)
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('不存在的数据');
        }
        $coupon = $couponModel->findBy($id);
        if (!$coupon) {
            $this->error('不存在的数据');
        }
        if ($request->isPost()) {
            if(!in_array($coupon['state'],[1,2])){
                $this->error('已发布无法修改');
            }
            $data = $request->post();
            //有效时间处理
            $valid_time = explode(' - ',$data['valid_time']);
            $data['start_time'] = $valid_time[0];
            $data['end_time'] = $valid_time[1];
            if($couponModel->updateBy($data['id'], $data) !== false){
                $this->success('编辑成功', url('CouponManage/index'));
            }else{
                $this->error('');
            }
        }
        $this->assign([
            'coupon' => $coupon,
            'couponEditStates' => CouponEnum::EDITSTATE
        ]);
        return $this->fetch();
    }

    /**
     * Delete Data
     *
     * @return void
     */
    public function delete(CouponModel $couponModel)
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('不存在数据');
        }
        $coupon = $couponModel->findBy($id);
        if (!$coupon) {
            $this->error('不存在的数据');
        }
        if($couponModel->updateBy($id, [
                'is_delete' => 1
            ]) !== false) {
            if($this->request->param('is_store') == 1){
                $this->success('删除成功', url('CouponManage/StoreCoupons'));
            }else{
                $this->success('删除成功', url('CouponManage/index'));
            }
        }
        $this->error('删除失败');
    }

    /**
     * 审核通过
     *
     * @return void
     */
    public function pass(CouponModel $couponModel)
    {
        $ids = $this->request->post('ids');
        if (!$ids) {
            $this->error('不存在数据');
        }
        $id_arr = explode(',',$ids);
        Db::startTrans();
        try{
            foreach ($id_arr as $val){
                $coupon = $couponModel->where([
                    'id' => $val,
                    'state' => 1
                ])->find();
                if($coupon){
                    $coupon->state = 2;
                    $coupon->save();
                    MessageModel::create([
                        'member_id' => MemberModel::getUidByStoreId($coupon->store_id,true),
                        'msg' => "您创建的卡券《{$coupon['name']}》已审核通过！"
                    ]);
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('审核通过成功', url('CouponManage/index'));
    }

    /**
     * 审核拒绝
     *
     * @return void
     */
    public function refuse(CouponModel $couponModel)
    {
        $ids = $this->request->post('ids');
        if (!$ids) {
            $this->error('不存在数据');
        }
        $id_arr = explode(',',$ids);
        Db::startTrans();
        try{
            foreach ($id_arr as $val){
                $coupon = $couponModel->where([
                    'id' => $val,
                    'state' => 1
                ])->find();
                if($coupon){
                    $coupon->state = 5;
                    $coupon->save();
                    MessageModel::create([
                        'member_id' => MemberModel::getUidByStoreId($coupon->store_id,true),
                        'msg' => "您创建的卡券《{$coupon['name']}》已被拒绝！"
                    ]);
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('拒绝成功', url('CouponManage/index'));
    }

}