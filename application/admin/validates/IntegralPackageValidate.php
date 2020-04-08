<?php

namespace app\admin\validates;

class IntegralPackageValidate extends AbstractValidate
{
	protected $rule = [
		'integral|积分'         => 'require|number|positiveNumber:1',
		'amount|金额'         => 'require|positiveNumber:1',
	];
}