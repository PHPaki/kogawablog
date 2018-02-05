<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Article;
use app\home\model\Tag;
use app\home\model\Category;
use app\index\model\User;

class Index extends Base
{
    protected $cat_id = 0;
    protected $tag_id = 0;
    protected $author_id = 0;

    public function index()
    {

        //是否按栏目分类显示
        if ($this->request->has('cat_id', 'param')) {
            $this->catPage();
        //是否按标签分类显示
        } elseif ($this->request->has('tag_id', 'param')) {
            $list = $this->tagPage();
        //是否按作者分类显示
        } elseif ($this->request->has('author_id', 'param')) {
            $this->authorPage();
        //默认显示
        } else {
            $this->articleList();
        }
        $this->catList();
        $this->tagList();
        $this->commentList();
        return $this->fetch('index');
    }

    //默认显示文章列表
    protected function articleList()
    {
        $list = Article::order('id desc')->paginate(5);
        $this->assign('list', $list);
        return $list;
    }

    //按栏目显示(一对多关联)
    protected function catPage()
    {
        $this->cat_id = $this->request->param('cat_id');
        $list = Article::where('cat_id', $this->cat_id)->paginate(5);
        $this->assign('list', $list);
        //查询页面主标题(防止如果该栏目没有文章时报错)
        $cat_name = Category::get($this->cat_id)->name;
        $this->assign('topTitle', $cat_name);
        return $list;
    }

    //按标签显示(多对多关联)
    protected function tagPage()
    {
        $this->tag_id = $this->request->param('tag_id');
        $tag = Tag::get($this->tag_id);
        $list = $tag->article()->paginate(5);
        $this->assign('list', $list);
        $this->assign('topTitle', $tag->name);
        return $list;
    }

    //按作者显示(一对多关联)
    protected function authorPage()
    {
        $this->author_id = $this->request->param('author_id');
        $list = Article::where('user_id', $this->author_id)->paginate(5);
        $this->assign('list', $list);
        //查询页面主标题(防止如果该用户没有文章时报错)
        $author_name = User::get($this->author_id)->nick.'的文章';
        $this->assign('topTitle', $author_name);

    }
}