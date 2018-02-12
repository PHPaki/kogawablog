<?php
namespace app\index\validate;

use think\Validate;

class Category extends Validate
{
    protected $rule = [
        'name|栏目标题'=>'require|length:2,10|unique:category',
    ];
}