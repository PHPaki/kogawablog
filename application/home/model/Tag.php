<?php
namespace app\home\model;

use think\Model;

class Tag extends Model
{
    //关联article表
    public function article()
    {
        return $this->belongsToMany('Article', 'tag_relation', 'article_id', 'tag_id');
    }

}