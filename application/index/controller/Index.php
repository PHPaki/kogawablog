<?php
namespace app\index\controller;

use app\index\model\User;

class Index extends Base
{
    public function index()
    {
        $this->isLogin();
        $this->assign([
            'title'=>'教学管理系统',
            'keywords'=>'教育',
            'description'=>'教学的各类资源的信息管理',
    ]);
        return $this->view->fetch();
    }
}
