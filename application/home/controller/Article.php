<?php
namespace app\home\controller;

use app\base\controller\Base;
use app\home\model\Article as ArticleModel;
use app\home\model\Category;
use app\home\model\Tag;
use think\Cookie;
use think\Db;
use think\Exception;

class Article extends Base
{
    protected $article_id = 0;

    //文章显示页面
    public function article()
    {
        $this->catList();
        $this->tagList();
        $this->commentList();
        $this->articlePage();
        return $this->fetch('article');
    }     

    //文章添加页面
    public function articleAdd()
    {
        //先检查是否登录
        $this->isLogin();
        $this->catList();
        $this->tagList();
        $this->commentList();
        $this->catShow();
        return $this->fetch('article_add');
    }

    //文章的内容
    protected function articlePage()
    {
        if ($this->request->has('article_id', 'param')) {
            $this->article_id = $this->request->param('article_id');
            if (!$article = ArticleModel::get($this->article_id)) $this->redirect('index/index');;
            $this->assign('article', $article);
        } else {
            $this->redirect('index/index');
        }
    }
}