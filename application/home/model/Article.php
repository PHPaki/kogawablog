<?php
namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;

class Article extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //时间戳(在database.php中设置了自动输出时间转换)
    protected $autoWriteTimestamp = true;

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

}