<?php
namespace app\api\validate;

class Coupon extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'name' => 'require|isNotEmpty',//卡券名称
        'original_price' => 'require|isNotEmpty|isPositiveNumber',//原价
        'buying_price|抢购价' => 'require|isNotEmpty|isPositiveNumber|>=:1',//抢购价
        'start_time' => 'require|isNotEmpty|isActivityTime',//开始日期
        'end_time' => 'require|isNotEmpty|isActivityTime',//结束日期
        'logo' => 'require|isNotEmpty',//LOGO
        'rebate_commission' => 'require|isNotEmpty|percentage|between:1,100',//返利佣金
        'promotion_commission' => 'require|isNotEmpty|percentage|between:1,100',//推广佣金
        'pattern' => 'isNotEmpty|in:1,2',//模式
        'type' => 'require|isNotEmpty|in:1,2',//优惠券类型
        'instructions' => 'require|max:300',//使用说明
        'num' => 'require|isPositiveInteger',//数量
        'longitude' => 'require|isNotEmpty',
        'latitude' => 'require|isNotEmpty',
        'state' => 'require|isNotEmpty|in:1,2',//
        'pull_type' => 'require|isNotEmpty|in:1,2,3',//优惠券类型


    ];
    protected $scene = [
        'buy1_add' => ['name','buying_price','original_price','start_time','end_time','logo','rebate_commission','pattern','type','instructions'],
        'buy2_add' => ['name','buying_price','original_price','start_time','end_time','logo','rebate_commission','pattern','promotion_commission','type','instructions'],
        'promotion_add' => ['name','original_price','start_time','end_time','logo','rebate_commission','type','instructions'],
        'add_num'=>['id','num'],
        'buy1_update' => ['id','name','buying_price','original_price','start_time','end_time','logo','rebate_commission','pattern','instructions'],
        'buy2_update' => ['id','name','buying_price','original_price','start_time','end_time','logo','rebate_commission','pattern','promotion_commission','instructions'],
        'promotion_update' => ['id','name','original_price','start_time','end_time','logo','rebate_commission','instructions'],
        'download_coupon' => ['id','num'],
        'receive_coupon' => ['id'],
        'download_detail' => ['id','state','longitude','latitude'],
        'data_list' => ['id','state','pull_type'],
    ];

    protected function isActivityTime($value, $rule='', $data='', $field='')
    {
        if(strtotime($data['start_time']) >= (strtotime(date('Y-m-d',strtotime($data['end_time']))) + 86399)){
            return '开始时间不能大于结束时间';
        }
        return true;
    }

}
