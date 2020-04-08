<?php
namespace app\agent\validates;

class UserValidate extends AbstractValidate
{
	protected  $rule = [
		'old_password|旧密码' => 'require|min:6|max:20|alphaDash',
		'password|密码' => 'require|confirm|min:6|max:20|alphaDash|confirm',
	];
}