<?php
namespace app\index\validate;

use think\Validate;

class BlogInfo extends Validate
{
    protected $rule = [
        //验证图片
        'img|头像'=>'image|fileSize:2097152',
        'user_name|博主名'=>'require|length:2,16',
        'introduction|个人介绍'=>'require|length:5,100',
        'blog_name|博客名'=>'require|length:2,30',
        'blog_headtext|博客小标题'=>'require|length:2,50',
    ];
}
