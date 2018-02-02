<?php
namespace app\index\controller;

use app\index\model\Teacher as TeacherModel;
use think\Db;

class Teacher extends Base
{
    public function teacherList()
    {
        $this->isLogin();
        $this->assign([
            'title'=>'教学管理系统',
            'keywords'=>'教育',
            'description'=>'教学的各类资源的信息管理',
        ]);

        //获取分页的数据
        $list = TeacherModel::paginate(5);
        $count = $list->total();
        //赋值模版
        $this->assign('list', $list);
        $this->assign('count', $count);
        return $this->fetch('teacher_list');
    }

   //教师编辑页面
    public function teacherEdit()
    {
        //利用Db类直接提供可选的课程数据
        $list = Db::table('lesson')->field('name,id')->select();
        $this->assign('list', $list);
        //基本数据
        $id = $this->request->param('id');
        $res = TeacherModel::get($id);
        $this->assign('res', $res);
        return $this->view->fetch('teacher_edit');
    }

    //增加教师页面
    public function teacherAdd() 
    {
        //利用Db类直接提供可选的课程数据
        $list = Db::table('lesson')->field('name,id')->select();
        $this->assign('list', $list);
        return $this->fetch('teacher_add');
    } 

    //提取新增教师或更新的数据
    protected function getPostData() 
    {
        $res = $this->request->param();
        if (isset($res['id'])){
            $data['id'] = $res['id'];
            $data['status'] = isset($res['status']) ? $res['status'] : 1;
        }
        $data['name'] = $res['name'];
        $data['salary'] = $res['salary'];
        $data['lesson_id'] = $res['lesson'];
        //为什么模型里面的类型转换无法实现?这里手工转换
        $data['hire_date'] = strtotime($res['hire_date']);
        return $data;
    }

    //教师和课程是一对一关系,把选中该课程的所有老师(一位)lesson_id置0
    public function setTeacher($lesson_id)
    {
        if ($lesson_id != 0) {
            $list = TeacherModel::all(['lesson_id'=>$lesson_id]);
            foreach ($list as $v) {
                $v->lesson_id = 0;
                $v->save();
            }
        }
    }

    //教师增加提交
    public function addTeacher() 
    {
        $data = $this->getPostData();
        //清空选中该课的另一位教师的标记(如果存在)
        $clean = new TeacherModel();
        $clean->save(['lesson_id'=>0], ['lesson_id'=>$data['lesson_id']]);
        //新增一条教师数据
        $teacher = new TeacherModel($data);
        $status = $teacher->save();
        return $status;
    } 

    //教师编辑修改提交
    public function editTeacher() 
    {
        $data = $this->getPostData();
        //清空选中该课的另一位教师的标记(如果存在)
        $clean = new TeacherModel();
        $clean->save(['lesson_id'=>0], ['lesson_id'=>$data['lesson_id']]);
        //更新教师数据
        $teacher = TeacherModel::get($data['id']);
        $status = $teacher->save($data);
        return $status;

    } 

    //教师删除(软删除)
    public function teacherDelete()
    {
        $res = $this->request->param();
        foreach ($res as $v) {
            if (is_numeric($v)) {
                $id[] = $v;
            }
        }
        $status = TeacherModel::destroy($id);
        return $status;
    }

    //教师恢复
    public function teacherRestore() 
    {
        //设定能够恢复的删除记录是时间在3天内的
        $allowDate = time()-3*24*60*60;
        $num = 0;
        $res = TeacherModel::onlyTrashed()->where('delete_time', 'GT', $allowDate)->select();
        foreach ($res as $teacher) {
            $teacher->delete_time = null;
            $num += $teacher->save();
        }
        return $num;
    }

    //教师停用
    public function teacherStop()
    {
        $id = $this->request->param('id');
        $res = TeacherModel::where('id', $id)->update(['status'=>0]);
        return $res;
    }

    //教师启用
    public function teacherStart()
    {
        $id = $this->request->param('id');
        $res = TeacherModel::where('id', $id)->update(['status'=>1]);
        return $res;
    }

    //验证操作,返回数组到前台页面
    protected function dataValidate($data, $rule) 
    {
        $result = $this->validate($data, $rule);
        $status = ($result === true)? 1 : 0;
        return ['status'=>$status, 'message'=>$result];
    }

    //验证教师名是否合法
    public function checkName()
    {
        $name = $this->request->param('name');
        $id = $this->request->param('id');
        $data = ['name'=>$name];
        $rule = [
            'name|教师名'=>'require|length:2,20|unique:teacher,name,'.$id,
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证薪水输入
    public function checkSalary()
    {
        $salary = $this->request->param('salary');
        $data = ['salary'=>$salary];
        $rule = ['salary|薪水'=>'require|between:0,65535'];
        return $this->dataValidate($data, $rule);
    }

    //验证课时输入
    public function checkHire_date()
    {
        $hire_date = $this->request->param('hire_date');
        $data = ['hire_date'=>$hire_date];
        $rule = ['hire_date|入职时间'=>'require|dateFormat:Y/m/d'];
        return $this->dataValidate($data, $rule);
    }
}