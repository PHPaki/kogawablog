<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\homt\model\Comment;

class Comment extends Base
{
    //评论添加后台验证,返回新增数量
    public function commentAddExcue
    {
        $data['content'] = $this->request->param('content');
        $check = $this->validate($data, 'Comment');
        if ($result !== true) {
            $this->error($result);
        }
        $comment = new Comment($data);
        $result = $comment->save();
        return $result;
    }
}