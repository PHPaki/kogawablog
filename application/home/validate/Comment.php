<?php
namespace app\home\validate;

use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'content|评论'=>'require|length:2,50' ,
    ];
  
}