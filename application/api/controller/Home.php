<?php


namespace app\api\controller;


use app\api\service\StoreService;
use app\api\validate\Position;
use app\api\validate\Region;
use app\model\BannerModel;
use app\model\IndustryCategoryModel;
use app\model\MessageModel;
use app\model\StoreModel;


class Home extends Base
{
    /**
     * 获取轮播图
     * @url api/home/get_banner
     * @http POST
     * @post province   省份
     * @post city       城市
     * @post district       区域
     */
    public function getBanner(){
        $validate = new Region();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        foreach (['district','city','province',''] as $val){
            $banner = BannerModel::where([
                'province' => $data['province'],
                'city' => $data['city'],
                'district' => $data['district']
            ])->field('imgs')->find();
            if($banner){
                break;
            }
            if($val != ''){
                $data[$val] = '';
            }
        }
        if(!$banner){
            success([
                'imgs' => []
            ]);
        }else{
            success($banner);
        }
    }

    /**
     * 获取所有分类
     * @url api/home/get_all_catetgory
     * @http GET
     */
    public function getAllCatetgory(){
        $catetgory = IndustryCategoryModel::field('id,name,icon,pid')->select();
        success($catetgory);
    }

    /**
     * 获取附近门店
     * @url api/home/store
     * @http POST
     * @post longitude   经度
     * @post latitude    纬度
     */
    public function storeList(){
        $validate = new Position();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $storeId = StoreService::getStoreId($this->uid);
        $storeList = StoreModel::distanceSort($storeId,$data['longitude'],$data['latitude'],$this->listRows);
        $storeList->each(function($item,$key){
            $industryCategory = IndustryCategoryModel::getSecondLevelName($item->industry_category_id);
            $item->category_name_p = $industryCategory['category_name_p'];
            $item->category_name_s = $industryCategory['category_name_s'];
            $item->distance = round($item->distance,3);
            unset($item->industry_category_id);
        });
        success($storeList);
    }

    /**
     * 获取推送的消息
     * @url api/home/message
     * @http POST
     */
    public function message(){
        $lists = MessageModel::where([
            'member_id' => $this->uid,
            'is_push' => 0
        ])->paginate($this->listRows)->each(function($item,$key){
            $item->is_push = 1;
            $item->save();
        });
        success($lists);
    }



}