<?php


namespace app\api\controller;


use app\api\service\UserToken;
use app\api\service\Wechat;
use app\api\service\WechatPay;
use app\api\validate\Amount as AmountValidate;
use app\api\validate\downloadCouponList as downloadCouponListValidate;
use app\api\validate\Feedback as FeedbackValidate;
use app\api\validate\Position;
use app\exceptions\ApiException;
use app\model\BalanceOrderModel;
use app\model\BalanceRecordModel;
use app\model\BalanceWithdrawalRecordModel;
use app\model\FeedbackModel;
use app\model\ImgModel;
use app\model\IntegralRecordModel;
use app\model\MemberModel;
use app\model\StoreStaffModel;
use app\model\UserCollectionModel;
use app\model\UserCouponModel;
use app\model\UserDownloadCouponModel;

class User extends Base
{


    public function img(){
        $validate = new \app\api\validate\User();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $result=ImgModel::where(['code'=>$data['code']])->find();
        if(!$result){
            error('验证码错误或图片已过期',400);
        }
        success($result);
    }
}