<?php

namespace app\admin\validates;

class StoreValidate extends AbstractValidate
{
	protected  $rule = [
		'name|门店名称'   => 'require|max:30',
		'address|详细地址'    => 'require|max:30',
		'introduce|简介' => 'max:100',
        'business_hours|营业时间' => 'require|time',
        'industry_category_id|行业分类' => 'require'
	];

    /**
     * 验证时间是否正确
     */
    protected function time($value,$rule,$data,$name,$description){
        $arr = explode(' - ',$value);
        if(count($arr) != 2){
            return "{$description}输入不正确";
        }
        foreach ($arr as $val){
            if(strtotime($val) == false){
                return "{$description}输入不正确";
            }
        }
        return true;
    }
}