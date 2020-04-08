<?php


namespace app\api\service;


use app\exceptions\ApiException;
use Exception;

class StoreStaff
{
    public function getCode($storeId){
        $key = $this->saveToCache($storeId);
        return url('QrCode/code',[
            'content'=> json_encode([
                'type' => 2,
                'code' => $key
            ])
        ],'',true);
    }

    /**
     * 根据KEY获得门店ID
     * @param $key
     * @return mixed
     * @throws ApiException
     */
    public static function getStoreIdByKey($key){
        if(empty($key)){
            throw new ApiException([
                'msg' => 'key参数错误',
                'errorCode' => 10000
            ]);
        }
        $storeId = cache($key);
        if(empty($storeId)){
            throw new ApiException([
                'msg' => 'Key已过期或无效Key',
                'errorCode' => 60000
            ]);
        }
        return $storeId;
    }

    private function saveToCache($storeId)
    {
        $key = $this->grantKey();
        $expire_in = config('api.token_expire_in');
        $result = cache($key, $storeId, $expire_in);
        if (!$result){
            throw new Exception([
                'msg' => '服务器缓存异常',
                'errorCode' => 999
            ]);
        }
        return $key;
    }

    /**
     * 生成KEY
     * @return string
     */
    private function grantKey()
    {
        $key = 'store_staff_' . getRandChar(15);
        if(!empty(cache($key))){
            return $this->grantKey();
        }else{
            return $key;
        }
    }

}