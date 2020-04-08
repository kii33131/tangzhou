<?php

namespace app\admin\validates;

use app\model\MemberModel;

class AgentValidate extends AbstractValidate
{
    protected $rule = [
        'account|代理帐号' => 'require',
        'province|省份' => 'require',
/*        'city|城市' => 'require',
        'district|区域' => 'require',*/
        'name|联系人' => 'require',
        'phone|联系电话' => 'require|mobile',
        'pumping_ratio|抽水比例' => 'require|percentage',
        'residence_rebate|入驻返利' => 'require|percentage',
        'member_id|微信用户' => 'require|member',
    ];

    /**
     * 验证member_id是否正确
     */
    protected function member($value,$rule,$data,$name,$description){
        $member = MemberModel::where('id',$value)->find();
        if(!$member){
            return '微信用户不存在';
        }
        return true;
    }
}