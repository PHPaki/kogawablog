<?php
namespace app\index\controller;

use app\index\model\Student as StudentModel;
use think\Db;

class Student extends Base
{
    //学生显示页面
    public function studentList()
    {
        $this->isLogin();
        $this->assign([
            'title'=>'教学管理系统',
            'keywords'=>'教育',
            'description'=>'教学的各类资源的信息管理',
        ]);

        //获取分页的数据
        $list = StudentModel::paginate(5);
        $count = $list->total();
        //赋值模版
        $this->assign('list', $list);
        $this->assign('count', $count);
        // dump($list);
        return $this->fetch('student_list');
    }

   //学生编辑页面
    public function studentEdit()
    {
        $id = $this->request->param('id');
        $res = StudentModel::get($id);
        $this->assign('res', $res);
        //课程名单
        $list = Db::table('lesson')->field('id,name')->select();
        $this->assign('list', $list);
        return $this->view->fetch('student_edit');
    }

    //学生增加页面
    public function studentAdd() 
    {
        //课程名单
        $list = Db::table('lesson')->field('id,name')->select();
        $this->assign('list', $list);
        return $this->fetch('student_add');
    } 

    //提取新增学生或更新的数据
    protected function getPostData() 
    {
        $res = $this->request->param();
        if (isset($res['id'])){
            $data['id'] = $res['id'];
            $data['status'] = isset($res['status']) ? $res['status'] : 1;
        }
        $data['name'] = $res['name'];
        $data['age'] = $res['age'];
        $data['sex'] = $res['sex'];
        $data['lesson_id'] = $res['lesson'];
        return $data;
    }

    //学生增加提交
    public function addStudent() 
    {
        $data = $this->getPostData();
        $student = new StudentModel($data);
        $status = $student->save();
        return $status;
    } 

    //学生编辑修改提交
    public function editStudent() 
    {
        $data = $this->getPostData();
        $student = new StudentModel();
        $status = $student->save($data, ['id'=>$data['id']]);
        return $status;
    } 

    //学生删除(软删除)
    public function studentDelete()
    {
        $res = $this->request->param();
        foreach ($res as $v) {
            if (is_numeric($v)) {
                $id[] = $v;
            }
        }
        $status = StudentModel::destroy($id);
        return $status;
    }

    //学生恢复
    public function studentRestore() 
    {
        //设定能够恢复的删除记录是时间在3天内的
        $allowDate = time()-3*24*60*60;
        $num = 0;
        $res = StudentModel::onlyTrashed()->where('delete_time', 'GT', $allowDate)->select();
        foreach ($res as $student) {
            $student->delete_time = null;
            $num += $student->save();
        }
        return $num;
    }

    //学生停用
    public function studentStop()
    {
        $id = $this->request->param('id');
        $res = StudentModel::where('id', $id)->update(['status'=>0]);
        return $res;
    }

    //学生启用
    public function studentStart()
    {
        $id = $this->request->param('id');
        $res = StudentModel::where('id', $id)->update(['status'=>1]);
        return $res;
    }

    //验证操作,返回数组到前台页面
    protected function dataValidate($data, $rule) 
    {
        $result = $this->validate($data, $rule);
        $status = ($result === true)? 1 : 0;
        return ['status'=>$status, 'message'=>$result];
    }

    //验证学生名是否合法
    public function checkName()
    {
        $name = $this->request->param('name');
        $id = $this->request->param('id');
        $data = ['name'=>$name];
        $rule = [
            'name|学生名'=>'require|length:2,20|unique:student,name,'.$id,
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证年龄输入
    public function checkAge()
    {
        $age = $this->request->param('age');
        $data = ['age'=>$age];
        $rule = ['age|年龄'=>'require|between:0,150'];
        return $this->dataValidate($data, $rule);
    }

}