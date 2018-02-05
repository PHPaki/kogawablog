<?php
namespace app\home\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title|标题'=>'require|chsAlphaNum|length:2,16|unique:article' ,
        'subtitle|小标题'=>'require|length:5,66',
        'content|文章内容'=>'require|min:5',
        'tags'=>'checkTags'
    ];

    //tags自定验证规则
    protected function checkTags($value)
    {
        if($value != null) {
            $arr = explode(',', $value);
            foreach ($arr as $vo) {
                if (mb_strlen($vo, 'utf-8') < 2 || mb_strlen($vo, 'utf-8') > 5) {
                    return "请按,隔开标签,每个标签长度在2-5之间";
                }
            }
        }
        return true;
    }
  
}

