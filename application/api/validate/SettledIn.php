<?php
namespace app\api\validate;

class SettledIn extends BaseValidate
{
    protected $rule = [
        'name' => 'require|isNotEmpty',
        'industry_category_id' => 'require|isPositiveInteger',
        'logo' => 'require|isNotEmpty',
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'district' => 'require|isNotEmpty',
        'address' => 'require|isNotEmpty',
        'contacts' => 'require|isNotEmpty',
        'phone' => 'require|isNotEmpty',
        'business_license' => 'require|isNotEmpty',
        'id_card_positive' => 'require|isNotEmpty',
        'id_card_back' => 'require|isNotEmpty',
        'start_hours' => 'require|isNotEmpty',
        'end_hours' => 'require|isNotEmpty|checkHours',
        'introduce' => 'require|isNotEmpty|max:100',
        'exhibition' => 'require|isNotEmpty|exhibition',
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty',
        'store_mobile' => 'require|isNotEmpty',
        'extension_code|推广码' => 'require|isNotEmpty|max:30'
    ];

    protected $scene = [
        'add' => ['name','logo','contacts','phone','longitude','latitude','province','city','district','address',
            'store_mobile','industry_category_id','business_license','id_card_positive','id_card_positive','id_card_back',
            'extension_code'],
        'update' => ['name','industry_category_id','logo','province','city','district','address','contacts',
            'phone','business_license','id_card_positive','id_card_back','start_hours','end_hours',
            'introduce','exhibition','longitude','latitude','store_mobile']
    ];

    //验证时间
    protected function checkHours($value,$rule,$data,$name,$description){
        $arr = [
            $data['start_hours'],
            $data['end_hours']
        ];
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

    protected function exhibition($value,$rule,$data,$name,$description){
        if(!is_array($value)){
            return "{$description}输入不正确";
        }
        if(count($value) > 6){
            return "{$description}输入不正确";
        }
        return true;
    }

}
