<?php


namespace app\api\controller;


use app\api\service\UserToken;
use app\api\service\WechatPay;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\SettledIn as SettledInValidate;
use app\exceptions\ApiException;
use app\model\ConfigModel;
use app\model\CouponModel;
use app\model\IndustryCategoryModel;
use app\model\MemberModel;
use app\model\MessageModel;
use app\model\StoreModel;
use app\model\UserCollectionModel;
use app\model\UserCouponModel;
use app\model\UserDownloadCouponModel;
use think\Db;
use app\api\service\WriteOffCoupon as WriteOffCouponService;

class Store extends Base
{
    /**
     * 门店详情
     * @url api/store/detail
     * @http POST
     * @param $id 门店id
     */
    public function detail($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        $store = StoreModel::field('name,logo,start_hours,end_hours,store_mobile,contacts,longitude,latitude,introduce,exhibition,industry_category_id,address')->get($id);
        if(!$store){
            throw new ApiException([
                'msg' => '商家不存在',
                'errorCode' => 30001
            ]);
        }
        $industryCategory = IndustryCategoryModel::getSecondLevelName($store['industry_category_id']);
        $store['category_name_p'] = $industryCategory['category_name_p'];
        $store['category_name_s'] = $industryCategory['category_name_s'];
        unset($store['industry_category_id']);
        if(!is_array($store['exhibition'])){
            $store['exhibition'] = [];
        }
        //是否收藏店铺
        $store['collection_status'] = UserCollectionModel::isCollection($this->uid,$id);
        success($store);
    }

    /**
     * 收藏/取消收藏 店铺
     * @url api/store/do_collection
     * @http POST
     * @param $id 门店id
     * @param $state 0：取消   1：收藏
     */
    public function doCollection($id = '',$state = 1){
        (new IDMustBePositiveInt())->goCheck();
        if($state == 1){
            $collection = UserCollectionModel::where([
                'store_id' => $id,
                'user_id' => $this->uid
            ])->find();
            if(!$collection){
                UserCollectionModel::create([
                    'store_id' => $id,
                    'user_id' => $this->uid
                ]);
            }
        }else{
            UserCollectionModel::where([
                'store_id' => $id,
                'user_id' => $this->uid
            ])->delete();
        }
        success();
    }

    /**
     * 商家入驻
     * @url api/store/settled_in
     * @http POST
     */
    public function settledIn(){
        $validate = new SettledInValidate();
        $validate->scene('add')->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $result = [
            'pay' => 0
        ];
        $store = StoreModel::where([
            'user_id'=>$this->uid,
            'is_pay' => 1,
            'is_delete' => 0
        ])->find();
        if(!empty($data['extension_code'])){
            $member = MemberModel::where('extension_code',$data['extension_code'])->find();
            if(!$member || $member['id'] == $this->uid){
                throw new ApiException([
                    'msg' => '推广码不存在',
                    'errorCode' => 20004
                ]);
            }
            $data['recommender_id'] = $member['id'];
        }
        unset($data['extension_code']);
        if($store){
            if($store['state'] == 1){//如果状态为待审核
                throw new ApiException([
                    'msg' => '入驻正在待审核中',
                    'errorCode' => 30004
                ]);
            }elseif ($store['state'] == 2){//如果状态为已通过
                throw new ApiException([
                    'msg' => '已经是商家，请勿重新申请',
                    'errorCode' => 30005
                ]);
            }
            $data['state'] = 1;//修改状态为待审核
            StoreModel::where('id', $store['id'])->update($data);
        }else{
            $data['user_id'] = $this->uid;
            $data['state'] = 1;
            $data['apply_time'] =time();
            $data['entry_fee'] =ConfigModel::getParam('entry_amount');
            $data['order_no'] = WechatPay::generateOrderNo();
            $data['is_pay'] = 0;
            //判断入驻金额是否为0
            if($data['entry_fee'] <= 0){
                $data['is_pay'] = 1;
            }
            //是否自动审核
            if(!empty(ConfigModel::getParam('entry_audit'))){
                $data['state'] = 2;
                $data['entry_time'] = time();
            }
            Db::startTrans();
            try{
                $store = StoreModel::create($data);
                //入驻赠送积分
                if($data['is_pay'] == 1){
                    if(ConfigModel::getParam('entry_gift_points') > 0){
                        MemberModel::changeIntegral(
                            $this->uid,
                            ConfigModel::getParam('entry_gift_points'),
                            1,
                            4
                        );
                    }
                    if($data['state'] == 2){
                        MessageModel::create([
                            'member_id' => $store->user_id,
                            'msg' => "您入驻的店铺已审核通过！"
                        ]);
                    }
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                throw $e;
            }
            //微信支付
            if($data['entry_fee'] > 0){
                $payData = [
                    'body' => '商家入驻',
                    'out_trade_no' => $data['order_no'],
                    'total_fee' => $data['entry_fee'] * 100,
                    'notify_url' => url('wxpay/Notify/settledIn','','',true),
                    'trade_type' => 'JSAPI',
                    'openid' => UserToken::getCurrentTokenVar('openid'),
                ];
                $wechatPay = new WechatPay();
                $wxResult = $wechatPay->orderPay($payData);
                $result['wx_pay'] = $wxResult;
                $result['pay'] = 1;
                //TODO 跳板支付开始
                skip_pay('settledIn',$data['order_no'],$data['entry_fee']);
                //TODO 跳板支付结束
            }
        }
        success($result);
    }

    /**
     * 获取商家入驻协议
     * @url api/store/get_entry_agreement
     * @http GET
     */
    public function getEntryAgreement(){
        success(ConfigModel::getParam('entry_agreement'));
    }

    /**
     * 获取行业分类
     * @url api/store/get_catetgory
     * @http GET
     */
    public function getCatetgory($pid = 0){
        if(empty($pid) || !is_numeric($pid)){
            $pid = 0;
        }
        success(IndustryCategoryModel::field('id,name,icon,pid')->where('pid',$pid)->select());
    }

    /**
     * 获取入驻状态
     * @url api/store/settled_state
     * @http GET
     */
    public function settledState(){
        $result = [
            'state' => 0
        ];
        $storeId = MemberModel::getStoreIdByUid($this->uid);
        if($storeId){
            $store = StoreModel::where('id',$storeId)->find();
        }else{
            $store = StoreModel::where([
                'user_id' => $this->uid,
                'is_delete' => 0,
                'is_pay' => 1,
            ])->order('id desc')->find();
        }
        if($store){
            $result['state'] = $store['state'];
            $result['data'] = $store->hidden(['order_no','is_pay','is_delete','recommender_id']);
            $result['data']['industry_category_pid'] = IndustryCategoryModel::where('id',$result['data']['industry_category_id'])
                ->value('pid');
        }
        success($result);
    }

    /**
     * 商家信息修改
     * @url api/store/update
     * @http POST
     */
    public function update(){
        $validate = new SettledInValidate();
        $validate->scene('update')->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        if(is_array($data['exhibition'])){
            $data['exhibition'] = json_encode($data['exhibition']);
        }
        $storeId = MemberModel::getStoreIdByUid($this->uid);
        StoreModel::where('id',$storeId)->update($data);
        success();
    }

    /**
     * 核销卡券
     * @url api/store/write_off_coupon
     * @http POST
     */
    public function writeOffCoupon($code = ''){
        if(empty($code)){
            error('code参数错误',$code);
        }
        $writeOffCouponService = new WriteOffCouponService();
        $writeOffCouponService->writeOff($code,$this->uid);
        success();
    }

    /**
     * 营销中心
     * @url api/store/marketing_center
     * @http POST
     */
    public function marketingCenter(){
        $result = [
            'release_coupon_num' => 0,//发布的券数量
            'user_downLoad_coupon_num' => 0,//下载的券数量
            'receive_coupon_num' => 0, //领取的券数量
            'use_coupon_num' => 0,//使用的券数量
        ];
        $storeId = MemberModel::getStoreIdByUid($this->uid,true);
        //发布的券数量
        $result['release_coupon_num'] = CouponModel::releaseCouponNum($storeId);
        //下载的券数量
        $result['user_downLoad_coupon_num'] = UserDownloadCouponModel::userDownLoadCouponNum($storeId);
        //领取的券数量
        $result['receive_coupon_num'] = UserCouponModel::receiveCouponNum($storeId);
        //使用的券数量
        $result['use_coupon_num'] = UserCouponModel::useCouponNum($storeId);
        success($result);
    }

    /**
     * 营销中心-门店发布的卡券列表
     * @url api/store/release_coupon_list
     * @http POST
     */
    public function releaseCouponList(){
        $storeId = MemberModel::getStoreIdByUid($this->uid,true);
        $lists = CouponModel::releaseCouponList($storeId,$this->listRows);
        success($lists);
    }

    /**
     * 营销中心-门店发布的卡券用户下载列表
     * @url api/store/user_downLoad_coupon_list
     * @http POST
     */
    public function userDownLoadCouponList(){
        $storeId = MemberModel::getStoreIdByUid($this->uid,true);
        $lists = UserDownloadCouponModel::userDownLoadCouponList($storeId,$this->listRows);
        success($lists);
    }

    /**
     * 营销中心-门店发布的卡券用户领取列表
     * @url api/store/user_receive_coupon_list
     * @http POST
     */
    public function userReceiveCouponList(){
        $storeId = MemberModel::getStoreIdByUid($this->uid,true);
        $lists = UserCouponModel::receiveCouponList($storeId,$this->listRows);
        success($lists);
    }

    /**
     * 营销中心-门店发布的卡券用户核销列表
     * @url api/store/use_coupon_list
     * @http POST
     */
    public function useCouponList(){
        $storeId = MemberModel::getStoreIdByUid($this->uid,true);
        $lists = UserCouponModel::useCouponNumList($storeId,$this->listRows);
        success($lists);
    }


}