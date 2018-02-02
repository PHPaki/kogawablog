<?php
namespace app\index\controller;

use think\Controller;
use think\Session;

class Base extends Controller
{
    protected function _initialize() 
    {
        parent::_initialize();
        define('USER_ID', Session::get('user_id'));
    }

    //在主页判断用户是否登录
    protected function isLogin()
    {
        if (is_null(USER_ID)) {
            $this->error('请登录', url('login/login'));
        }
    }

    //在登录页判断用户是否已经登录
    protected function alreadyLogin()
    {
        if(!is_null(USER_ID)) {
            $this->error('您已登录,请不要重新登录', url('login/index'));
        }
    }
}