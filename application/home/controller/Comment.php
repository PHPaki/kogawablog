<?php
namespace app\home\controller;

use app\base\controller\Base;
use app\home\model\Article;

class Comment extends Base
{
    //评论添加后台验证,返回新增数量
    public function commentAddExecu()
    {
        if (is_null(USER_ID)) {
            return 0;
        }
        $data['content'] = $this->request->param('content');
        $check = $this->validate($data, 'Comment');
        if ($check !== true) {
            return $check;
        }
        $data['user_id'] = $this->request->session('user_id');
        $article = Article::get($this->request->param('article_id'));
        $res = $article->comment()->save($data);
        return true;
    }
}
