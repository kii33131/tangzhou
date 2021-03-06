<?php
/**
 * UserRequest.php
 * Created by wuyanwen <wuyanwen1992@gmail.com>
 * Date: 2018/11/29 0029 21:56
 */
namespace app\admin\request;

use app\admin\validates\PermissionValidate;

class PermissionRequest extends FormRequest
{
    public function validate()
    {
        return (new PermissionValidate())->getErrors($this->post());
    }
}