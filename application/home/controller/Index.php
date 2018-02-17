<?php
namespace app\home\controller;

use app\base\controller\Base;
use app\home\model\Article;
use app\home\model\Tag;
use app\home\model\Category;
use app\index\model\User;
use think\Db;

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
            $this->assign('type', 'normal');
            $this->articleList();
        }
        $this->catList();
        $this->tagList();
        $this->blogInfo();
        $this->commentList();
        $this->getSlideArt();
        return $this->fetch('index');
    }

    //默认显示文章列表
    protected function articleList()
    {
        $list = Article::hasWhere('category', ['status'=>1])->order('id desc')->paginate(5);
        // $category = new Catagory();
        // $list = $catagory->article->hasWhere(order('id desc')->paginate(5);
        $this->assign('list', $list);
        return $list;
    }

    //按栏目显示(一对多关联)
    protected function catPage()
    {
        $this->cat_id = $this->request->param('cat_id');
        $catAll = Category::all();
        //获得一级栏目下的所有子栏目id
        $arr = $this->getIdByCat($this->cat_id , $catAll);
        $condition = 'cat_id='.$this->cat_id;
        foreach ($arr as $v) {
            $condition .= " or cat_id=" . $v;
        }
        //获取一级栏目下的所有文章(包括子栏目内的文章)
        $list = Article::hasWhere('category', ['status'=>1])->where($condition)->paginate(5);
        $this->assign('list', $list);
        //查询页面主标题(防止如果该栏目没有文章时报错)
        $cat_name = Category::get($this->cat_id)->name;
        $this->assign('topTitle', $cat_name);
        return $list;
    }

    //按标签显示(多对多关联)
    protected function tagPage()
    {

        //普通关联,不排除关闭栏目的文章
        // $this->tag_id = $this->request->param('tag_id');
        // $tag = Tag::get($this->tag_id);
        // $list = $tag->article()->paginate(5);

        //排除关闭了栏目的文章
        $this->tag_id = $this->request->param('tag_id');
        $data = Db::name('tag_relation')->where('tag_id', $this->tag_id)->field('article_id')->select();
        $article_id_list = [];
        //将该存在该标签的id合成一个索引数组
        foreach ($data as $vo) {
            $article_id_list[] = $vo['article_id'];
        }
        $list = Article::hasWhere('category', ['status'=>1])->where('article.id', 'in', $article_id_list)->paginate();
        $tag = Tag::get($this->tag_id);
        $this->assign('list', $list);
        $this->assign('topTitle', $tag->name);
        return $list;
    }

    //按作者显示(一对多关联)
    protected function authorPage()
    {
        $this->author_id = $this->request->param('author_id');
        $list = Article::hasWhere('category', ['status'=>1])->where('user_id', $this->author_id)->paginate(5);
        $this->assign('list', $list);
        //查询页面主标题(防止如果该用户没有文章时报错)
        $author_name = User::get($this->author_id)->nick.'的文章';
        $this->assign('topTitle', $author_name);
    }

    //递归获取所有子栏目的id(数组)
    protected function getIdByCat($id, $cat) 
    {
        $arr = [];
        foreach ($cat as $v) {
            if ($v->parent_id == $id) {
                $arr[] = $v->id;
                $arr = array_merge($arr, $this->getIdByCat($v->id, $cat));
            } 
        }
        return $arr;
    }

}