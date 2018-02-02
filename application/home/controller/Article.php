<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Article as ArticleModel;

class Article extends Base
{
    public function article()
    {
        $this->catList();
        $this->tagList();
        $this->commentList();

        return $this->fetch('article');
    }     

    public function articleAdd()
    {
        $this->catList();
        $this->tagList();
        $this->commentList();
        
        return $this->fetch('article_add');
    }
}