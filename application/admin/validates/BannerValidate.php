<?php

namespace app\admin\validates;

class BannerValidate extends AbstractValidate
{
    protected $rule = [
        'title|标题' => 'require',
        'imgs|轮播图' => 'require'
    ];


}