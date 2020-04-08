<?php

namespace app\api\validate;

use app\exceptions\ApiException;
use think\facade\Request;
use think\Validate;

/**
 * Class BaseValidate
 * 验证类的基类
 */
class BaseValidate extends Validate
{
    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @throws ParameterException
     * @return true
     */
    public function goCheck()
    {
        //必须设置contetn-type:application/json
        $request = Request::instance();
        $params = $request->param();
        $params['token'] = $request->header('token');

        if (!$this->check($params)) {
            $exception = new ApiException(
                [
                    'errorCode' => 10000,
                    'msg' => is_array($this->error) ? implode(
                        ';', $this->error) : $this->error,
                ]);
            throw $exception;
        }
        return true;
    }

    /**
     * @param array $arrays 通常传入request.post变量数组
     * @return array 按照规则key过滤后的变量数组
     * @throws ParameterException
     */
    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ApiException([
                'errorCode' => 10000,
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        }
        if(!empty($this->currentScene) && is_array($this->scene) && array_key_exists($this->currentScene,$this->scene)){
            return $this->getDataByScene($arrays);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            if(array_key_exists($key,$arrays)){
                $newArray[$key] = $arrays[$key];
            }
        }
        return $newArray;
    }

    /**
     * 根据规则获取参数
     * @param $arrays
     * @return array
     */
    private function getDataByScene($arrays){
        $newArray = [];
        foreach ($this->scene[$this->currentScene] as $value){
            if(array_key_exists($value,$arrays)){
                $newArray[$value] = $arrays[$value];
            }
        }
        return $newArray;
    }

    protected function isPositiveInteger($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return $field . '必须是正整数';
    }

    protected function isPositiveNumber($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && $value > 0) {
            return true;
        }
        return $field . '必须是正数';
    }

    protected function isNotEmpty($value, $rule='', $data='', $field='')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }

    //手机号的验证规则
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证是否为百分比（可两位小数）
     */
    protected function percentage($value,$rule,$data=[],$name,$description){
        if(preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $value) &&  $value <=100 ){
            return true;
        }else{
            return $description . '输入不正确';
        }
    }

    /**
     * 验证是否为正确金额
     * rule为1时金额必须大于0
     */
    protected function amount($value,$rule,$data,$name,$description){
        if(preg_match('/^([1-9]\d{0,9}|0)([.]?|(\.\d{1,2})?)$/', $value)){
            if($rule == 1){
                if($value > 0){
                    return true;
                }else{
                    return $description . '输入不正确';
                }
            }
            return true;
        }else{
            return $description . '输入不正确';
        }
    }

}