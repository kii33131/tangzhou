<?php
namespace app\exceptions;
use think\Exception;

/**
 * Class BaseException
 * 自定义API异常类
 */
class ApiException extends Exception
{
    public $code = 400;
    public $msg = '';
    public $errorCode = 999;
    
    public $shouldToClient = true;

    /**
     * 构造函数，接收一个关联数组
     * @param array $params 关联数组只应包含code、msg和errorCode，且不应该是空值
     */
    public function __construct($params=[])
    {
        if(!is_array($params)){
            return;
        }
        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errorCode',$params)){
            $this->errorCode = $params['errorCode'];
        }
        $this->message = $this->msg;
    }
}

