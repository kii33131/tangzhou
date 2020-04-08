<?php


namespace app\exceptions;


use Exception;
use think\exception\Handle;
use think\facade\Log;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e)
    {
        if($e instanceof SuccessMessage){
            $result = [
                'error_code' => $e->errorCode,
                'data'  => $e->data,
            ];
            return json($result, $e->code);
        }
        if ($e instanceof ApiException)
        {
            //如果是自定义异常，则控制http状态码，不需要记录日志
            //因为这些通常是因为客户端传递参数错误或者是用户请求造成的异常
            //不应当记录日志
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }
        else{
            // 如果是服务器未处理的异常，将http状态码设置为500，并记录日志
            if(config('app_debug')){
                // 调试状态下需要显示TP默认的异常页面，因为TP的默认页面
                // 很容易看出问题
                $this->recordErrorLog($e);
                return parent::render($e);
            }
            $this->code = 500;
            $this->msg = 'sorry，we make a mistake. (^o^)Y';
            $this->errorCode = 999;

            $this->recordErrorLog($e);
        }

        $result = [
            'error_code' => $this->errorCode,
            'msg'  => $this->msg
        ];
        return json($result, $this->code);
    }

    /*
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        Log::record($e->getMessage() . " parame:" . json_encode(input()),'sys_error');
    }
}