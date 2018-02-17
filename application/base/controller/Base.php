<?php
namespace app\base\controller;

use think\Controller;
use think\Session;
use app\home\model\Article;
use app\home\model\Category;
use app\home\model\Tag;
use app\home\model\Comment;
use app\index\model\BlogInfo;

class Base extends Controller
{
    protected function _initialize() 
    {
        parent::_initialize();
        if (!defined('USER_ID')) {
            define('USER_ID', Session::get('user_id'));
        }
    }

    //在主页判断用户是否登录
    protected function isLogin()
    {
        if (is_null(USER_ID)) {
            $this->error('请登录', url('index/login/login'));
        }
    }

    //在登录页判断用户是否已经登录
    protected function alreadyLogin()
    {
        if(!is_null(USER_ID)) {
            $this->error('您已登录,请不要重新登录', url('home/index/index'));
        }
    }

    //主栏目列表
    public function catList()
    {
        $cat = Category::all(function($query) {
            $query->where('parent_id', 0)->order('id desc')->limit(5);
        });
        //得到全体数据用于得到子栏目文章数量
        $catAll = Category::all();
        //查询下文章数量,作为属性输入对象
        for($i = 0; $i < count($cat); $i++) {
            $cat[$i]->num = $this->getNum($cat[$i]->id, $catAll) + $cat[$i]->article()->count();
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
    
    //最新评论列表
    public function commentList()
    {
        $comment = Comment::all(function($query) {
            $query->order('id desc')->limit(5);
        });
        $this->assign('comment', $comment);
        return $comment;
    }

    //获取个人信息及博客信息
    public function blogInfo() 
    {
        $info = BlogInfo::get(1);
        $this->assign('info', $info);
        return $info;
    }

    //按顺序显示各级栏目(为添加页面的选择框提供所有栏目)
    public function catShow()
    {
        $catShow = Category::getDivide();
        $this->assign('catShow', $catShow);
        return $catShow;
    }

    //获取子栏目文章数量(递归获得,排除关闭的栏目)
    protected function getNum($id, $cat) 
    {
        $num = 0;
        foreach ($cat as $v) {
            if ($v->parent_id == $id && $v->status == 1) {
                $num = $num + $v->article()->count() + $this->getNum($v->id, $cat);
                // echo "---".$v->article()->count()."---".$v->name."<br>";
            } 
        }
        return $num;
    }

    //获取滚动文章
    public function getSlideArt()
    {
        $article = new Article;
        $slide = $article->limit(3)->order('id', 'desc')->select();
        foreach ($slide as $vo) {
            $time = $vo->getData('create_time');
            $vo->time = date('Y-m-d', $time);
        }
        $this->assign('slide', $slide);
        return $slide;
    }

    //判断用户身份
    public function getRole()
    {
        return $this->request->session->user_info->role;
    }
}