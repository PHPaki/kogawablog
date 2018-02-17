<?php
namespace app\index\controller;

use app\base\controller\Base;
use app\home\model\Comment as CommentModel;
use app\home\model\Article;

class Comment extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }
    
    public function commentList()
    {
        $type = $this->request->has('type', 'param') ? $this->request->param('type') : 0;
        switch ($type) {
            //按文章分类显示
            case '1':
                $comment_list = CommentModel::with('article')->order('article_id desc,id desc')->paginate(10);        
                break;
            //搜索文章显示
            case '2':
                if ($this->request->has('search', 'param')) {
                    $search = '%' . $this->request->param('search') . '%';
                    $article= Article::field('id')->where('title', 'like', $search)->find();
                    $comment_list = CommentModel::with('article')->where('article_id', $article->id)->order('id', 'desc')->paginate(10);
                } 
                break;
            //点击文章显示
            case '3':
                if ($this->request->has('article_id', 'param')) {
                    $article_id = $this->request->param('article_id');
                    $comment_list = CommentModel::with('article')->where('article_id', $article_id)->order('id', 'desc')->paginate(10);
                }
                break;
            //默认显示
            default:
                $comment_list = CommentModel::with('article')->order('id', 'desc')->paginate(10);
                break;
        }
        if (!empty($comment_list)) {
            $this->assign('comment_list', $comment_list);
            $this->assign('count', $comment_list->total());
        }
        return $this->fetch('comment');
    }

    public function commentDelete()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $list = [];
            foreach ($data as $v) {
                if (is_numeric($v)) $list[] = $v;
            }
            $res = CommentModel::destroy($list);
            return $res;
        } else {
            return "非法操作";
        }
    }
}