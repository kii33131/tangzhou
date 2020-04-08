<?php


namespace app\api\service;


use app\model\GiftPackageModel;
use app\model\GiftPackageOrderModel;
use app\model\MemberModel;
use app\model\UserCouponModel;
use app\model\UserDownloadCouponModel;

class Gift
{
    private $preCacheName = 'gift_receive_order_';
    private $preCacheExpire = 300;

    /**
     * 预领取
     * @param $id int 礼包id
     * @param $uid
     * @param string $randStr
     */
    public function preReceive($id,$uid,$randStr = ''){
        $cacheName = $this->getCacheName($id,$randStr);
        $flag = cache($cacheName,$uid,$this->preCacheExpire);
        if($flag !== true){
            throw new \Exception('礼包预领取失败');
        }
    }

    /**
     * 获取预领取用户id
     * @param $id int 礼包id
     * @param string $randStr
     * @return bool|mixed
     */
    public function getPreReceiveUser($id,$randStr = ''){
        $cacheName = $this->getCacheName($id,$randStr);
        $userId = cache($cacheName);
        if(empty($userId)){
            return false;
        }
        cache($cacheName,NULL);
        return $this->getReceiveRebateInfo($id,$userId);
    }

    /**
     * 礼包返佣信息
     * @param $id int 礼包id
     * @param $userId int 用户id
     * @return array
     * @throws \app\exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getReceiveRebateInfo($id,$userId){
        $result = [
            'user_info' => [],//领取礼包用户信息
            'has_rebate' => 0,//是否有返利
//            'rebate_info' => []//返利详细
        ];
        $giftPackage = GiftPackageModel::where([
            ['id','=',$id],
            ['is_delete','=',0]
        ])->find();
        if(!$giftPackage){
            error('礼包不存在',50003);
        }
        if($giftPackage['stock'] < 1){
            error('礼包库存不足',50004);
        }
        if($giftPackage['user_id'] == $userId){
            error('不能领取自己的礼包',50005);
        }
        $result['user_info'] = MemberModel::where('id',$userId)->field('name,picture')->find()->toArray();
        if(!$result['user_info']){
            error('领取礼包用户不存在',20001);
        }
        //获取本店已核销卡券
        $userCouponModel = new UserCouponModel();
        $coupon = $userCouponModel->alias('uc')
            ->field('c.type coupon_type,c.rebate_commission,uc.*')
            ->join('coupon c','c.id = uc.coupon_id')
            ->where([
                ['uc.state','=',2],
                ['c.store_id','=',MemberModel::getStoreIdByUid($giftPackage['user_id'],true)],
                ['uc.write_off_time','>',(time() - 86400)],
                ['uc.user_id','=',$userId],
            ])
            ->order('uc.write_off_time desc')
            ->find();
        $rebateUserId = 0;//返佣用户id
        if($coupon){
            //商家自建券
            if($coupon['download_coupon_id'] == 0){
                //分享后使用才有返利
                if($coupon['receive_user_id'] != $coupon['user_id']){
                    $rebateUserId = $coupon['receive_user_id'];
                }
            }else{
                //下载券用户返佣
                $userDownloadCoupon = UserDownloadCouponModel::where('id',$coupon['download_coupon_id'])->find();
                if($userDownloadCoupon){
                    $rebateUserId = $userDownloadCoupon['user_id'];
                }
            }
            if($rebateUserId != 0){
                //返佣用户信息
                $rebateUserInfo = MemberModel::where('id',$rebateUserId)->field('name,picture')->find();
                if($rebateUserInfo){
                    $result['rebate_info']['user'] = $rebateUserInfo->toArray();
                    $result['rebate_info']['amount'] = round($giftPackage['pay_money'] * ($coupon['rebate_commission']/100),2);
                    $result['has_rebate'] = 1;
                }
            }
        }
        $createOrderData = [
            'amount' => 0,
            'gift_package_id' => $id,
            'rebate_user_id' => 0,
            'user_id' => $userId,
            'order_no' => WechatPay::generateOrderNo(),
            'is_pay' => 0
        ];
        if($result['has_rebate'] == 1){
            $createOrderData['amount'] = $result['rebate_info']['amount'];
            $createOrderData['rebate_user_id'] = $rebateUserId;
        }
        $order = GiftPackageOrderModel::create($createOrderData);
        $result['order_id'] = $order['id'];
        return $result;
    }

    private function getCacheName($id,$randStr){
        return $this->preCacheName . $id . '_' . $randStr;
    }

}