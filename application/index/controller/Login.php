<?php
namespace app\index\controller;

use app\index\model\User as UserModel;
use think\Session;

class Login extends Base
{
    //用户注册
    public function register() 
    {
        return $this->view->fetch('login/reg');
    }

    //注册验证,写入数据库
    public function checkRegister()
    {
        $res = $this->request->param();
        //数据校验
        $result = $this->validate($res, 'CheckLogin.register');
        $status = 1;
        //开始写入数据库
        if ($result === true) {
            $data['name'] = $res['name'];
            $data['password'] = md5($res['password']);
            $guest = new UserModel($data);
            $num = $guest->save();
            if ($num == 1) {
                $result = "注册成功,点击确定登录到主页";
                $status = 0;
                $user = UserModel::get($guest->id);
                Session::set('user_id', $user->id);
                //把登录信息存入session
                Session::set('user_info', $user);
                //把这次的内容时间更新到数据库
                $user->login_time = time();
                $user->save();
            } else {
                $result = '数据库新增错误';
                $status = 1;
            }
        }
        return ['status'=>$status, 'message'=>$result];
    }

    //用户登录
    public function login()
    {
        $this->alreadyLogin();
        return $this->view->fetch();
    }

    //登录验证 
    public function checkInput()
    {
        $data = $this->request->param();
        //数据校验
        $result = $this->validate($data, 'CheckLogin.login');
        $status = 1;
        //开始数据库校验
        if ($result === true) {
            return $this->checkDb($data);
        } else {
        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
        }
    }

    //数据库验证
    protected function checkDb($data)
    {
        $user = UserModel::get(['name'=>$data['name']]);//返回对象实例
        if ($user === null) {
            $status = 2;
        } else {
            $status = ($user->password == md5($data['password'])) ? 0 : 1;
        }
        switch ($status) {
            case 0 :
                $result = "恭喜验证成功";
                $user->login_count += 1;
                $user->save();
                Session::set('user_id', $user->id);
                Session::set('user_info', $user);
                $user->login_time = time();
                $user->save();
                break;
            case 1 :
                $result = "密码错误";
                break;
            case 2 :
                $result = "用户名不存在";
                break;
            default :
                $result = "未知错误";
                break;
        }
        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //退出登录
    public function logout()
    {
        Session::delete('user_id');
        Session::delete('user_info');
        $this->success('退出登录成功'.$this->request->session('user_id'), 'login/login');
    }
}