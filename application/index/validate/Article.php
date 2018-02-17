<?php
namespace app\index\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title|标题'      =>'require|length:2,16|unique:article' ,
        'subtitle|小标题' =>'require|length:5,60',
        'content|文章内容'=>'require|min:5',
        'tags'            =>'checkTags'
    ];
    protected $message = [
        'title.require'         =>'标题必须有' ,
        'title.length:2,16'     =>'标题长度在2-16之间' ,
        'title.unique:article'  =>'标题重复' ,
        'subtitle.require'      =>'需要填写摘要',
        'subtitle.length:5,60'  =>'摘要长度在5-60之间',
        'content.require'       =>'内容不能为空',
        'content.min:5'         =>'内容最少一个字符',
        'tags.checkTags'        =>'请按,隔开标签,每个标签长度在2-5之间',
    ];

    // protected $scene = [
    //     'edit'=>['title'=>'require|length:2,16', 'subtitle', 'content', 'tags'],
    // ];

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

