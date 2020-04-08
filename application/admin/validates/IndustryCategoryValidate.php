<?php

namespace app\admin\validates;

class IndustryCategoryValidate extends AbstractValidate
{
	protected $rule = [
		'name|菜单名称'         => 'require',
	];
}