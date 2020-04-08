<?php

namespace app\admin\validates;

class CouponValidate extends AbstractValidate
{
	protected  $rule = [
		'name|卡券名称'   => 'require|max:30',
		'type|卡券类型'    => 'require|in:1,2',
		'pattern|模式选择'    => 'require|in:1,2',
		'original_price|原价'    => 'require|positiveNumber',
		'rebate_commission|返利佣金'    => 'require|percentage',
		'promotion_commission|推广人佣金'    => 'require|percentage',
		'valid_time|有效时间'    => 'require|validDate',
		'instructions|简介' => 'max:300',
	];
    /**
     * 验证日期是否正确
     */
    protected function validDate($value,$rule,$data=[],$name,$description){
        $arr = explode(' - ',$value);
        if(count($arr) != 2){
            return "{$description}输入不正确";
        }
        foreach ($arr as $val){
            if(strtotime($val) == false){
                return "{$description}输入不正确";
            }
        }
        if(strtotime($arr[0]) > strtotime($arr[1])){
            return "{$description}输入不正确";
        }
        return true;
    }
}