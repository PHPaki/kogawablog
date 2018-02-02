<?php
namespace app\home\controller;

use app\home\controller\Base;

class Index extends Base
{
    public function index()
    {
        $this->articleList();
        $this->catList();
        $this->tagList();
        $this->commentList();

        return $this->fetch('index');
    }


}