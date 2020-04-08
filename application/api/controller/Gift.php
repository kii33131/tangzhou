<?php


namespace app\api\controller;


use app\api\service\StoreService;
use app\api\service\UserToken;
use app\api\service\Wechat;
use app\api\service\WechatPay;
use app\api\validate\GiftValidate;
use app\api\validate\IDMustBePositiveInt;
use app\model\GiftPackageDetailModel;
use app\model\GiftPackageModel;
use app\api\service\Gift as GiftService;
use app\model\GiftPackageOrderModel;
use think\Db;

class Gift extends Base
{
    /**
     * 礼包列表
     * @url api/gift/index
     * @http POST
     */
    public function index(){
        $userId = StoreService::getStoreUserId($this->uid);
        success(
            GiftPackageModel::where(['user_id'=>$userId,'is_delete'=>0])
            ->field('id,name,pay_money,stock')
            ->order('id','desc')
            ->paginate($this->listRows)
        );
    }

    /**
     * 礼包详情
     * @url api/gift/detail
     * @http POST
     */
    public function detail($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        $gift= GiftPackageModel::field('id,pay_money,name,num,stock,gift')
            ->where([
                'id' => $id,
                'is_delete' => 0
            ])
            ->find();
        if(!$gift){
            error('礼包不存在',50003);
        }
        $gift->received_num = $gift->num - $gift->stock;
        success($gift);
    }

    /**
     * 礼包详情
     * @url api/gift/detailed_list
     * @http POST
     */
    public function detailedList(){
        $giftValidate  = new GiftValidate();
        $giftValidate->scene('list')->goCheck();
        $data=$giftValidate->getDataByRule(input('post.'));
        $mode = New GiftPackageDetailModel();
        success($mode->detailedList($data,$this->listRows));
    }

    /**
     * 创建礼包
     * @url api/gift/created_package
     * @http POST
     */
    public function createdPackage(){
        $giftValidate  = new GiftValidate();
        $giftValidate->scene('create')->goCheck();
        $data = $giftValidate->getDataByRule(input('post.'));
        if(!is_array($data['data'])){
            $data['data'] = json_decode($data['data'],true);
        }
        $model = new GiftPackageModel();
        $model->createdPackage($data,$this->uid);
        success();
    }

    /**
     * 删除礼包
     * @url api/gift/delete_package
     * @http POST
     */
    public function deletePackage($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        GiftPackageModel::deletePackage($id,$this->uid);
        success();
    }

    /**
     * 获取转赠码
     * @url api/gift/get_giving_code
     * @http GET
     */
    public function getGivingCode($id = ''){
        (new IDMustBePositiveInt())->goCheck();
        //判断礼包是否存在
        $giftPackage = GiftPackageModel::where([
            'id' => $id,
            'user_id' => StoreService::getStoreUserId($this->uid),
            'is_delete' => 0
        ])->find();
        if(!$giftPackage){
            error('礼包不存在',50003);
        }
        if($giftPackage['stock'] <=0){
            error('礼包库存不足',50004);
        }
        $filename = "wxapp_code/gift_package/{$id}.png";
        $code = getRandChar(6);
        if(!file_exists(config('upload_file').$filename)){
            $response = (new Wechat())->appCodeUnlimit('pages/loadingIndex/loadingIndex',"id={$id},type=1,code={$code}");
            $response->saveAs(config('upload_file'), $filename);
        }
        success([
            'code_img' => $filename,
            'code' => $code
        ]);
    }

    /**
     * 用户预领取礼包
     * @url api/gift/receive
     * @http POST
     */
    public function preReceive($id = '',$code = ''){
        (new IDMustBePositiveInt())->goCheck();
        $giftService = new GiftService();
        $giftService->preReceive($id,$this->uid,$code);
//        GiftPackageModel::receivePackage($id,$this->uid);
        success();
    }

    /**
     * 商家获取预领取用户信息（轮询）
     * @url api/gift/get_pre_receive_user
     * @http POST
     */
    public function getPreReceiveUser($id = '',$code = ''){
        (new IDMustBePositiveInt())->goCheck();
        $giftService = new GiftService();
        $rebateUserInfo = $giftService->getPreReceiveUser($id,$code);
        $result = [
            'is_exist' => 0
        ];
        if($rebateUserInfo !== false){
            $result['is_exist'] = 1;
            $result['data'] = $rebateUserInfo;
        }
        success($result);
    }

    /**
     * 商家确认领取礼包
     * @url api/gift/confirm_receive
     * @http POST
     */
    public function confirmReceive($id = '',$pay_type = 0){
        (new IDMustBePositiveInt())->goCheck();
        $result = [
            'pay' => 0
        ];
        $order = GiftPackageOrderModel::where([
            'id' => $id,
            'is_pay' => 0
        ])->find();
        if(!$order){
            error('礼包订单不存在',50006);
        }
        if($order->getData('create_time') < time() - 300){
            error('订单已过期，请用户重新领取礼包',50006);
        }
        if($pay_type == 0){
            $giftPackageModel = new GiftPackageModel();
            $giftPackageModel->giftReceive($order['order_no'],$order['amount'],0);
        }else{
            $wechatPay = new WechatPay();
            $result['wx_pay'] = $wechatPay->orderPay([
                'body' => '礼包支付',
                'out_trade_no' => $order['order_no'],
                'total_fee' => $order['amount'] * 100,
                'notify_url' => url('wxpay/Notify/giftReceive','','',true),
                'trade_type' => 'JSAPI',
                'openid' => UserToken::getCurrentTokenVar('openid'),
            ]);
            $result['pay'] = 1;
            //TODO 跳板支付开始
            skip_pay('giftReceive',$order['order_no'],$order['amount']);
            //TODO 跳板支付结束
        }
        success($result);
    }


}