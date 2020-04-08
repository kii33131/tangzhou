<?php

namespace app\admin\validates;

class TemplateValidate extends AbstractValidate
{
    protected $rule = [
        'name|模板名称' => 'require',
        'content|模板内容' => 'require',

    ];


}