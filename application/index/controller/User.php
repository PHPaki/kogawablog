<?php
namespace app\index\controller;

use app\index\model\User as UserModel;
use app\base\controller\Base;
use think\Session;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }

    //管理员管理页面
    public function adminList()
    {   
        $this->assign([
            'title'=>'博客用户管理',
            'keywords'=>'用户,管理',
            'description'=>'博客用户管理页面',
        ]);
        
        //从session中获得当前的身份,区分权限
        if (Session::get('user_info.role_num') == 4){
            $list = UserModel::paginate(10);
            $count = $list->total();
        } else if (Session::get('user_info.role_num') == 3) {
            //只允许管理员查看高级用户和普通用户的信息
            $list = UserModel::where('role', '<', '3')->whereOr('id', $this->request->session('user_id'))->paginate(5);
            $count = $list->total();
        } else {
            $list[] = UserModel::get(['id'=>Session::get('user_id')]);
            $count = 1;
        }
        $this->assign('count', $count);
        $this->assign('list', $list);
        return $this->view->fetch('admin_list');
    }

    //管理员编辑页面
    public function adminEdit()
    {
        $id = $this->request->param('id');
        $res = UserModel::get($id);
        $this->assign('res', $res->getData());
        return $this->view->fetch('admin_edit');
    }

    //增加管理员页面
    public function adminAdd() 
    {
        return $this->fetch('admin_add');
    } 

    //提取新增管理员或更新的数据
    protected function getPostData() 
    {
        $res = $this->request->param();
        if (isset($res['id'])){
            $data['id'] = $res['id'];
            $data['status'] = isset($res['status']) ? $res['status'] : 1;
        }
        $data['name'] = $res['name'];
        $data['password'] = $res['password'];
        $data['email'] = $res['email'];
        if(isset($res['role'])) $data['role'] = $res['role'];
        return $data;
    }

    //管理员增加提交
    public function addUser() 
    {
        $data = $this->getPostData();
        $data['password'] = md5($data['password']);
        $user = new UserModel($data);
        $status = $user->save();
        return $status;
    } 

    //管理员编辑修改提交
    public function editUser() 
    {
        $data = $this->getPostData();
        $oriPassword = UserModel::where('id', $data['id'])->value('password');
        if ($oriPassword != $data['password']) {
            $data['password'] = md5($data['password']);
        }
        $user = new UserModel();
        $status = $user->save($data, ['id'=>$data['id']]);
        return $status;
    } 

    //管理员删除(软删除)
    public function adminDelete()
    {
        $res = $this->request->param();
        //获取一个id的索引数组
        foreach ($res as $v) {
            if (is_numeric($v)) {
                $id[] = $v;
            }
        }
        $status = UserModel::destroy($id);
        return $status;
    }

    //管理员恢复
    public function adminRestore() 
    {
        //设定能够恢复的删除记录是时间在3天内的
        $allowDate = time()-3*24*60*60;
        $num = 0;
        $res = UserModel::onlyTrashed()->where('delete_time', 'GT', $allowDate)->select();
        foreach ($res as $user) {
            $user->delete_time = null;
            $num += $user->save();
        }
        return $num;
    }

    //管理员停用
    public function adminStop()
    {
        $id = $this->request->param('id');
        $res = UserModel::where('id', $id)->update(['status'=>0]);
        return $res;
    }

    //管理员启用
    public function adminStart()
    {
        $id = $this->request->param('id');
        $res = UserModel::where('id', $id)->update(['status'=>1]);
        return $res;
    }

    //验证操作,返回数组到前台页面
    protected function dataValidate($data, $rule) 
    {
        $result = $this->validate($data, $rule);
        $status = ($result === true)? 1 : 0;
        return ['status'=>$status, 'message'=>$result];
    }

    //验证用户名是否合法
    public function checkName()
    {
        $name = $this->request->param('name');
        $id = $this->request->param('id');
        $data = ['name'=>$name];
        $rule = [
            'name|用户名'=>'require|length:3,12|unique:user,name,'.$id,
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证密码是否合法
    public function checkPassword()
    {
        $password = $this->request->param('password');
        $data = ['password'=>$password];
        $rule = [
            'password|密码'=>'require|length:6,32',
        ];
        return $this->dataValidate($data, $rule);
    }
    //验证两次密码是否相同
    public function checkRepassword()
    {
        $repassword = $this->request->param('repassword');
        $password = $this->request->param('password');
        $data = [
            'repassword'=>$repassword,
            'password'=>$password,
        ];
        $rule = [
            'repassword|重新输入密码'=>'require|confirm:password',
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证邮箱是否重复
    public function checkEmail()
    {
        $email = $this->request->param('email');
        $id = $this->request->param('id');
        $data = ['email'=>$email];
        $rule = [
            'email|邮箱'=>'require|email|unique:user,email,'.$id,
        ];
        return $this->dataValidate($data, $rule);
    }

   
}