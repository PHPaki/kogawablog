<?php
namespace app\home\model;

use think\Model;

class Article extends Model
{
    //时间戳(在database.php中设置了自动输出时间转换)
    protected $autoWriteTimestamp = true;

    //关联article_content表
    public function articleContent()
    {
        return $this->hasOne('ArticleContent', 'article_id', 'id');
    }

    //关联user表
    public function user()
    {
        return $this->belongsTo('app\index\model\User', 'user_id', 'id');
    }

    //关联category表
    public function category()
    {
        return $this->belongsTo('Category', 'cat_id', 'id');
    }

    //关联tag表
    public function tag()
    {
        return $this->belongsToMany('Tag', 'tag_relation', 'tag_id', 'article_id');
    }

    //关联comment表
    public function comment()
    {
        return $this->hasMany('Comment', 'article_id', 'id');
    }

}