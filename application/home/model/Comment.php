<?php
namespace app\home\model;

use think\Model;

class Comment extends Model
{
    //时间戳(在database.php中设置了自动输出时间转换)
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

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