<?php
namespace app\home\controller;

use think\Controller;
use think\Session;
use app\home\model\Article;
use app\home\model\Category;
use app\home\model\Tag;
use app\home\model\Comment;

class Base extends Controller
{
    protected function _initialize() 
    {
        parent::_initialize();
        define('USER_ID', Session::get('user_id'));
    }

        //文章列表
    public function articleList()
    {
        $list = Article::paginate(5);
        $this->assign('list', $list);
        return $list;
    }

    //栏目列表
    public function catList()
    {
        $cat = Category::all(function($query) {
            $query->order('id desc')->limit(5);
        });
        //查询下文章数量,作为属性输入对象
        for($i = 0; $i < count($cat); $i++) {
            $cat[$i]->num = $cat[$i]->article()->count();
        }
        $this->assign('cat', $cat);
        return $cat;
    }

    //标签列表
    public function tagList()
    {
        $tag = Tag::all(function($query) {
            $query->limit(15);
        });
        $this->assign('tag', $tag);
        return $tag;
    }
    
    //评论列表
    public function commentList()
    {
        $comment = Comment::all(function($query) {
            $query->order('id desc')->limit(5);
        });
        $this->assign('comment', $comment);
        return $comment;
    }
}