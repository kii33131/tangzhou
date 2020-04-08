<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 钩子行为
 */

use think\permissions\facade\Permissions;
use think\permissions\facade\Roles;

if (!function_exists('hook')) {
	function hook($behavior, $params) {
		\think\facade\Hook::exec($behavior, $params);
	}
}

/**
 * 编辑按钮
 */
if (!function_exists('editButton')) {
	function editButton(string $url, string $name = '编辑') {
		return sprintf('<a href="%s"><button class="btn btn-info btn-xs edit" type="button"><i class="fa fa-paste"></i> %s</button></a>', $url, $name);
	}
}

/**
 * 增加按钮
 */
if (!function_exists('createButton')) {
	function createButton(string $url, string $name, $isBig = true) {
		return $isBig ? sprintf('<a href="%s"> <button type="button" class="btn btn-w-m btn-primary"><i class="fa fa-check-square-o"></i> %s</button></a>', $url, $name) :
			sprintf('<a href="%s"> <button type="button" class="btn btn-xs btn-primary"><i class="fa fa-check-square-o"></i> %s</button></a>', $url, $name);
	}
}

/**
 * 删除按钮
 */
if (!function_exists('deleteButton')) {
	function deleteButton(string $url, int $id, string $name="删除") {
		return sprintf('<button class="btn btn-danger btn-xs delete" data-url="%s" data=%d type="button"><i class="fa fa-trash"></i> %s</button>', $url, $id, $name);
	}
}

/**
 * 通过按钮
 */
if (!function_exists('passButton')) {
	function passButton(string $url, int $id, string $name="通过",string $btn_size = 'btn-xs') {
		return sprintf('<button class="btn btn-success %s pass" data-url="%s" data=%d type="button">%s</button>',$btn_size, $url, $id, $name);
	}
}

/**
 * 拒绝按钮
 */
if (!function_exists('refuseButton')) {
	function refuseButton(string $url, int $id, string $name="拒绝",string $btn_size = 'btn-xs') {
		return sprintf('<button class="btn btn-warning %s refuse" data-url="%s" data=%d type="button">%s</button>',$btn_size, $url, $id, $name);
	}
}

/**
 * diy按钮
 */
if (!function_exists('diyButton')) {
    function diyButton(string $url, string $name = '',string $class = '') {
        return sprintf('<a href="%s"><button class="btn btn-info btn-xs %s" type="button">%s</button></a>', $url,$class,$name);
    }
}

/**
 * 搜索按钮
 */
if (!function_exists('searchButton')) {
	function searchButton(string $name="搜索") {
		return sprintf('<button class="btn btn-white" type="submit"><i class="fa fa-search"></i> %s</button>', $name);
	}
}

/**
 * 生成密码
 */
if (!function_exists('generatePassword')) {
	function generatePassword(string  $password, int $algo = PASSWORD_DEFAULT) {
		return password_hash($password, $algo);
	}
}

/**
 * 权限判断
 * @param $permission
 * @return bool
 */
function can($permission)
{
    $module = request()->module();
    list($controller, $action) = explode('@', $permission);
    $user = request()->session(config('permissions.user'));
    $roleIDs = $user->getRoles(false);
    $permission = Permissions::getPermissionByModuleAnd($module, $controller, $action);
    if (!$permission) {
        return true;
    }
    $permissions = [];
    foreach ($roleIDs as $role) {
        if($role == 1){
            return true;
        }
        $permissions = array_merge($permissions, (Roles::getRoleBy($role)->getPermissions(false)));
    }
    if (!in_array($permission->id, $permissions)) {
        return false;
    }
    return true;
}

/**
 * 生成随机字符串
 * @param $length
 * @return string|null
 */
function getRandChar($length)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0;
         $i < $length;
         $i++) {
        $str .= $strPol[rand(0, $max)];
    }
    return $str;
}

/**
 * api成功返回数据
 * @param $data
 * @throws \app\exceptions\SuccessMessage
 */
function success($data = []){
    throw new \app\exceptions\SuccessMessage([
        'data' => $data
    ]);
}

/**
 * api失败返回数据
 * @param $msg
 * @param $errorCode
 * @param int $code
 * @throws \app\exceptions\ApiException
 */
function error($msg,$errorCode,$code = 400){
    throw new \app\exceptions\ApiException([
        'msg' => $msg,
        'errorCode' => $errorCode,
        'code' => $code
    ]);
}

function skip_pay($type, $out_trade_no, $total_fee){
//    new \app\wxpay\controller\NotifyTest($type, $out_trade_no, $total_fee);
}