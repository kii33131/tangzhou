<?php

namespace app\model;


use think\Exception;

class ConfigModel extends BaseModel
{
    protected $name = 'config';
    public static $config = [];

    /**
     * 根据key获取参数
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public static function getParam($key){
        if(empty(self::$config)){
            $config = self::get(1);
            if(empty($config)){
                throw new Exception('微信小程序配置信息不存在',999);
            }
            self::$config = $config->toArray();
        }
        if(array_key_exists($key,self::$config)){
            return self::$config[$key];
        }else{
            throw new Exception('尝试获取的参数不存在',999);
        }
    }
}