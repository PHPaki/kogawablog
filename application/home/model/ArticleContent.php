<?php

namespace app\home\model;

use think\Model;

class ArticleContent extends Model
{
    //关联article表
    public function articleContent()
    {
        return $this->belongsTo('Article', 'article_id', 'id');
    }
}