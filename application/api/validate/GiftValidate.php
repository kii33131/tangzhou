<?php
namespace app\api\validate;

class GiftValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty',
        'name' =>'require|isNotEmpty',
        'pay_money' =>'require|isPositiveInteger',
        'num' =>'require|isPositiveInteger',
        'data' =>'require|isNotEmpty|isPackageDetail',
    ];

    protected $scene = [
        'detail' => ['id'],
        'list' => ['id','latitude','longitude'],
        'create'=>['name','pay_money','gift','num','data'],
    ];

    /**
     * 礼包详情验证
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     */
    protected function isPackageDetail($value, $rule='', $data='', $field=''){
        if(!is_array($value)){
            $value = json_decode($value,true);
        }
        if(empty($value)){
            return $field . '格式有误';
        }
        foreach ($value as $val){
            if(empty($val['id']) || $this->isPositiveInteger($val['id']) !== true){
                return $field . '格式有误';
            }
            if(empty($val['num']) || $this->isPositiveInteger($val['num']) !== true){
                return $field . '格式有误';
            }
        }
        return true;
    }
}
