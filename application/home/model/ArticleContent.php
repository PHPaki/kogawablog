<?php

namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;

class ArticleContent extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //关联article表
    public function articleContent()
    {
        return $this->belongsTo('Article', 'article_id', 'id');
    }
}