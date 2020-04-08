<?php


namespace app\api\controller;

use app\api\service\CouponService;
use app\api\service\StoreService;
use app\api\validate\Coupon as CouponValidate;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\Position;
use app\api\validate\ReceiptList as ReceiptListValidate;
use app\api\validate\ShareCoupon;
use app\exceptions\ApiException;
use app\model\ConfigModel;
use app\model\CouponModel;
use app\model\DownloadCouponModel;
use app\model\GiftCouponModel;
use app\model\IntegralRecordModel;
use app\model\MemberModel;
use app\model\MessageModel;
use app\model\TemplateModel;
use app\model\UserCouponModel;
use app\model\UserDownloadCouponModel;
use think\Db;
use think\Exception;


class Coupon extends Base
{

    /**
     * 优惠券列表
     * @url api/coupon/index
     * @http POST
     * @post longitude   经度
     * @post latitude    纬度
     */
    public function index()
    {
        (new Position())->goCheck();
        $params = input('post.');
        $couponModel = new CouponModel();
        $storeId = StoreService::getStoreId($this->uid);
        success($couponModel->searchCoupon($params,$storeId,$this->listRows)->toArray());
    }

    /**
     * 优惠券详情
     * @url api/coupon/detail
     * @http POST
     * @post id   优惠券id
     */
    public function detail($id = '')
    {
        (new IDMustBePositiveInt())->goCheck();
        (new Position())->goCheck();
        $params = input('post.');
        $couponModel = new CouponModel();
        $coupon = $couponModel->detail($params);
        $coupon['download_integral'] = ConfigModel::getParam('download_integral');
        success($coupon);
    }

    /**
     * 添加优惠券
     * @url api/coupon/add_coupon
     * @http POST
     */
    public function addCoupon()
    {
        $validate = new CouponValidate();
        $scene = '';
        $data = input('post.');
        if(empty($data['type']) || !in_array($data['type'],[1,2])){
            throw new ApiException([
                'msg' => '卡券类型错误',
                'errorCode' => 10000
            ]);
        }
        if($data['type'] == 1){
            if(empty($data['pattern']) || !in_array($data['pattern'],[1,2])){
                throw new ApiException([
                    'msg' => '卡券模式错误',
                    'errorCode' => 10000
                ]);
            }
            if($data['pattern'] == 1){
                $scene = 'buy1_add';
            }else{
                $scene = 'buy2_add';
            }
        }else{
            $scene = 'promotion_add';
        }
        $validate->scene($scene)->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $data['store_id'] = StoreService::getStoreId($this->uid);
        if(empty($data['store_id'])){
            throw new ApiException([
                'msg' => '当前用户未成为商家'
            ]);
        }
        $data['state'] = 1;
        //是否自动审核
        if(ConfigModel::getParam('coupon_audit') == 1){
            $data['state'] = 2;
        }
        CouponModel::create($data);
        success();
    }

    /**
     * 编辑优惠券
     * @url api/coupon/update_coupon
     * @http POST
     */
    public function updateCoupon()
    {
        (new IDMustBePositiveInt())->goCheck();
        $validate = new CouponValidate();
        $scene = '';
        $data = input('post.');
        //判断是否有权限编辑卡券
        $coupon = CouponModel::where([
            'id' => $data['id'],
            'store_id' => StoreService::getStoreId($this->uid)
        ])->find();
        if(empty($coupon)){
            throw new ApiException([
                'msg' => '卡券不存在',
                'errorCode' => 40001
            ]);
        }
        if(!in_array($coupon['state'],[0,2,5])){
            throw new ApiException([
                'msg' => '无权限修改卡券',
                'errorCode' => 999
            ]);
        }
        $data['type'] = $coupon['type'];
        if(!in_array($data['type'],[1,2])){
            throw new ApiException([
                'msg' => '卡券类型错误',
                'errorCode' => 10000
            ]);
        }
        if($data['type'] == 1){
            if(empty($data['pattern']) || !in_array($data['pattern'],[1,2])){
                throw new ApiException([
                    'msg' => '卡券模式错误',
                    'errorCode' => 10000
                ]);
            }
            if($data['pattern'] == 1){
                $scene = 'buy1_update';
            }else{
                $scene = 'buy2_update';
            }
        }else{
            $scene = 'promotion_update';
        }
        $validate->scene($scene)->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        //已发布的修改需重新审核
        if($coupon['state'] == 2){
            if(ConfigModel::getParam('coupon_audit') != 1){
                $data['state'] = 1;
            }
        }
        CouponModel::update($data);
        success();
    }

    /**
     * 我发布的优惠券
     * @url api/coupon/my_release_coupon
     * @http POST
     */
    public function myReleaseCoupon(){
        $data = input('post.');
        if( !isset($data['type']) || !in_array($data['type'],[1,2])){
            throw new ApiException([
                'msg' => 'type参数错误',
                'errorCode' => 10000
            ]);
        }
        success(CouponModel::releaseCouponByUid($this->uid,$data,$this->listRows));

    }

    /**
     * 修改卡券状态
     * @url api/coupon/update_state
     * @http POST
     */
    public function updateState($id = '',$type = '',$num = 0){
        (new IDMustBePositiveInt())->goCheck();
        if(!in_array($type,[1,2,3,4,5,6])){
            throw new ApiException([
                'msg' => 'type参数错误',
                'errorCode' => 10000
            ]);
        }
        $storeId = StoreService::getStoreId($this->uid);
        $coupon = CouponModel::where([
            'id' => $id,
            'store_id' => $storeId
        ])->find();
        if(empty($coupon)){
            throw new ApiException([
                'msg' => '卡券不存在',
                'errorCode' => 40001
            ]);
        }
        //类型对应状态验证
        $tpyeStates = [
            1 => 1, //撤回申请
            2 => 2, //立即发布
            3 => 3, //立即下架
            4 => [0,5],  //提交审核
            6 => 4 //重新发布
        ];
        if(isset($tpyeStates[$type])){
            if(is_array($tpyeStates[$type])){
                if(!in_array($coupon->state,$tpyeStates[$type])){
                    throw new ApiException([
                        'msg' => '状态错误',
                        'errorCode' => 999
                    ]);
                }
            }else if($coupon->state != $tpyeStates[$type]){
                throw new ApiException([
                    'msg' => '状态错误',
                    'errorCode' => 999
                ]);
            }
        }
        Db::startTrans();
        try{
            switch ($type){
                case 1://撤回申请
                    $coupon->state = 0;
                    break;
                case 2://立即发布
                    if(!preg_match("/^[1-9][0-9]*$/" ,$num)){
                        throw new ApiException([
                            'msg' => '数量错误',
                            'errorCode' => 10000
                        ]);
                    }
                    //发布需要积分
                    $couponReleaseIntegral = ConfigModel::getParam('coupon_release_integral');
                    if($couponReleaseIntegral > 0){
                        $integral = $couponReleaseIntegral * $num;
                        CouponModel::where([
                            'id' => $id,
                            'store_id' => $storeId
                        ])->setInc('cost_integral', $integral);
                        MemberModel::changeIntegral(MemberModel::getUidByStoreId($storeId),$integral,2,5);
                    }
                    $coupon->state = 3;
                    $coupon->total = $num;
                    $coupon->stock = $num;
                    break;
                case 3://立即下架
                    $coupon->state = 4;
                    $couponService = new CouponService();
                    $couponService->downCoupon($id);
                    break;
                case 4://提交审核
                    $coupon->state = 1;
                    if(ConfigModel::getParam('coupon_audit') == 1){
                        $coupon->state = 2;
                        MessageModel::create([
                            'member_id' => MemberModel::getUidByStoreId($coupon->store_id),
                            'msg' => "您创建的卡券《{$coupon['name']}》已审核通过！"
                        ]);
                    }
                    break;
                case 5://立即删除
                    $coupon->is_delete = 1;
                    $couponService = new CouponService();
                    $couponService->downCoupon($id);
                    break;
                case 6://重新发布
                    $coupon->state = 3;
                    break;
            }
            $coupon->save();
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        success();
    }

    /**
     * 卡券添加数量
     * @url api/coupon/add_num
     * @http POST
     */
    public function addNum(){
        $validate = new CouponValidate();
        $validate->scene('add_num')->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $storeId = StoreService::getStoreId($this->uid);
        // 启动事务
        Db::startTrans();
        try {
            CouponModel::where([
                'id' => $data['id'],
                'store_id' => $storeId
            ])->setInc('stock', $data['num']);
            CouponModel::where([
                'id' => $data['id'],
                'store_id' => $storeId
            ])->setInc('total', $data['num']);
            //增加数量需要积分
            $couponReleaseIntegral = ConfigModel::getParam('coupon_release_integral');
            if($couponReleaseIntegral > 0){
                $integral = $couponReleaseIntegral * $data['num'];
                CouponModel::where([
                    'id' => $data['id'],
                    'store_id' => $storeId
                ])->setInc('cost_integral', $integral);
                MemberModel::changeIntegral(MemberModel::getUidByStoreId($storeId),$integral,2,5);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw $e;
        }
        success();
    }

    /**
     * 优惠券详情（编辑）
     * @url api/coupon/edit_detail
     * @http POST
     */
    public function editDetail($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        $coupon = CouponModel::where([
            'id' => $id,
            'store_id' => StoreService::getStoreId($this->uid)
        ])->find();
        if(empty($coupon)){
            throw new ApiException([
                'msg' => '卡券不存在',
                'errorCode' => 40001
            ]);
        }
        success($coupon->hidden(['is_delete']));
    }

    /**
     * 卡券模板列表
     * @url api/coupon/templat_list
     * @http GET
     */
    public function templatList()
    {
        success(TemplateModel::field('id,name,content')->select());
    }

    /**
     * 优惠券下载
     * @url api/coupon/download
     * @http POST
     */
    public function download(){
        $validate = new CouponValidate();
        $validate->scene('download_coupon')->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $downloadIntegral = ConfigModel::getParam('download_integral') * $data['num'];
        $userId = StoreService::getStoreUserId($this->uid);
        $member = MemberModel::get($userId);
        if(!$member){
            error('获取用户信息失败','20001');
        }
        if($member['integral'] < $downloadIntegral){
            error('用户积分不足','20002');
        }
        $storeId = StoreService::getStoreId($this->uid);
        Db::startTrans();
        $coupon=CouponModel::lock(true)->where(['state'=>3 ,'is_delete' => 0 ,'id'=>$data['id']])->find();
        try{
            if(!$coupon){
                error('卡券不存在','40001');
            }
            if($storeId == $coupon->store_id){
                error('不能下载自己的优惠券','40002');
            }
            if($coupon['stock'] < $data['num']){
                error('卡券不足','40003');
            }
        }catch (\Exception $e){
            Db::rollback();
            throw $e;
        }
        //查询是否已领取过当前券
        $downloadCoupon = UserDownloadCouponModel::where([
            'coupon_id' => $data['id'],
            'user_id' => $userId,
            'store_id' => $storeId,
            'is_delete' => 0
        ])->find();
        try{
            //扣除用户积分
            MemberModel::where(['id'=>$userId])->setDec('integral',$downloadIntegral);
            //记录用户积分记录
            IntegralRecordModel::create([
                'member_id' => $userId,
                'store_id' => $storeId,
                'integral' => $downloadIntegral,
                'residual_integral' => $member['integral'] - $downloadIntegral,
                'type' => 2,
                'mode' => $coupon['type']
            ]);
            //减少卡券库存
            CouponModel::where(['id'=>$data['id']])->setDec('stock',$data['num']);
            if($downloadCoupon){
                //增加已下载券数量
                UserDownloadCouponModel::where([
                    'id' => $downloadCoupon->id
                ])->setInc('total',$data['num']);
                UserDownloadCouponModel::where([
                    'id' => $downloadCoupon->id
                ])->setInc('stock',$data['num']);
            }else{
                UserDownloadCouponModel::create([
                    'coupon_id' => $data['id'],
                    'user_id' => $userId,
                    'total' => $data['num'],
                    'stock' => $data['num'],
                    'store_id' => $storeId
                ]);
            }
            // 提交事务
            Db::commit();
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            throw $e;
        }
        success();
    }

    /**
     * 删除已下载的优惠券
     * @url api/coupon/delete_download_coupon
     * @http POST
     */
    public function deleteDownloadCoupon($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        UserDownloadCouponModel::where([
            'id' => $id,
            'user_id' => StoreService::getStoreUserId($this->uid)
        ])->update([
            'is_delete' => 1
        ]);
        success();
    }

    /**
     * 下载的卡券/我发布的卡券 详情
     * @url api/coupon/delete_download_coupon
     * @http POST
     */
    public function downloadDetail(){
        $validate = new CouponValidate();
        $validate->scene('download_detail')->goCheck();
        $data = input('post.');
        $userId = StoreService::getStoreUserId($this->uid);
        if($data['state']==1){
            $downloadCoupon= DownloadCouponModel::where([
                'user_id'=>$userId,
                'id'=>$data['id'],
                'is_delete' => 0
            ])->find();
            if(!$downloadCoupon){
                error('卡券不存在',40001);
            }
            $downloadCouponId = $data['id'];
            $data['id'] = $downloadCoupon['coupon_id'];
        }
        $couponModel = new CouponModel();
        $coupon= $couponModel->detailCommon($data);
        if($data['state']==1){//下载的卡券
            $coupon->stock = $downloadCoupon->stock;//剩余数量
            $coupon->total = $downloadCoupon->total;//总量
            $coupon->received_num = UserCouponModel::where('download_coupon_id',$downloadCouponId)->count();
            $coupon->write_off_num = UserCouponModel::where([
                'state'=>2,
                'coupon_id'=>$data['id'],
                'download_coupon_id'=>$downloadCoupon['id']
            ])->count();//核销数量
        }else{//发布的卡券
            $coupon->received_num = UserCouponModel::where('coupon_id',$data['id'])->count();//已领取的数量
            $coupon->write_off_num = UserCouponModel::where(['state'=>2,'coupon_id'=>$data['id']])->count();//核销数量
        }
        success($coupon);
    }

    /**
     * 优惠券领取列表/下载列表/核销列表
     * @url api/coupon/receipt_list
     * @http POST
     * @param type 类型 1：自建券  2：下载券
     * @param state 1：领取列表  2：下载列表  3：核销列表
     */
    public function receiptList(){
        $validate = new ReceiptListValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input());
        $coupons = [];
        switch ($data['state']){
            case 1:
                $userCouponModel = new UserCouponModel();
                $coupons = $userCouponModel->getReceiptList($data['id'],$data['type']);
                break;
            case 2:
                $userDownloadCouponModel = new UserDownloadCouponModel();
                $coupons = $userDownloadCouponModel->downLoadLlist($data['id'],$this->listRows);
                break;
            case 3:
                $userCouponModel = new UserCouponModel();
                $coupons = $userCouponModel->getWriteOffList($data['id'],$data['type']);
                break;
        }
        success($coupons);
    }


    /**
     * 分享卡券
     * @url api/coupon/share_coupon
     * @http POST
     * @param int id  卡券id/下载券id
     * @param int type  类型 1：自建券  2：下载券
     * @param int num  分享数量
     */
    public function shareCoupon(){
        $validate = new ShareCoupon();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $giftCouponModel = new GiftCouponModel();
        if($data['type'] == 1){
            $code = $giftCouponModel->shareCoupon($data['id'],$data['num'],$this->uid);
        }else{
            $code = $giftCouponModel->shareDownloadCoupon($data['id'],$data['num'],$this->uid);
        }
        success([
            'code' => $code
        ]);
    }
}