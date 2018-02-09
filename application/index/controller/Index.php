<?php
namespace app\index\controller;

use app\index\model\User;
use app\base\controller\Base;

class Index extends Base
{
    public function index()
    {
        $this->isLogin();
        $this->assign([
            'title'=>'博客管理系统',
            'keywords'=>'教育',
            'description'=>'教学的各类资源的信息管理',
    ]);
        return $this->view->fetch();
    }
}
