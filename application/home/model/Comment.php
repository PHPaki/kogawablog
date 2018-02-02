<?php
namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;

class Comment extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //关联article表
    public function article()
    {
        return $this->belongsTo('Article', 'article_id', 'id');
    }

    //关联user表
    public function user()
    {
        return $this->belongsTo('app\index\model\User', 'user_id', 'id');
    }
}