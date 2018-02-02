<?php
namespace app\index\controller;

use app\index\model\Lesson as LessonModel;
use think\Db;

class Lesson extends Base
{
    //课程显示
    public function lessonList()
    {
        $this->isLogin();
        $this->assign([
            'title'=>'教学管理系统',
            'keywords'=>'教育',
            'description'=>'教学的各类资源的信息管理',
        ]);

        //获取分页的数据
        $list = LessonModel::order('id asc')->paginate(5);
        $count = $list->total();
        // //赋值模版
        $this->assign('list', $list);
        $this->assign('count', $count);
        return $this->fetch('lesson_list');
    }

    //课程编辑页面
    public function lessonEdit()
    {
        $id = $this->request->param('id');
        $res = LessonModel::get($id);
        $this->assign('res', $res);
        //把教师内容放入
        $teacher = Db::table('teacher')->field('name, id')->select();
        $this->assign('teacher', $teacher);
        return $this->view->fetch('lesson_edit');
    }

    //增加课程页面
    public function lessonAdd() 
    {
        $teacher = Db::table('teacher')->field('name,id')->select();
        $this->assign('teacher', $teacher);
        return $this->fetch('lesson_add');
    } 

    //提取新增课程或更新的数据
    protected function getPostData() 
    {
        $res = $this->request->param();
        if (isset($res['id'])){
            $data['id'] = $res['id'];
            $data['status'] = isset($res['status']) ? $res['status'] : 1;
        }
        $data['name'] = $res['name'];
        $data['price'] = $res['price'];
        $data['length'] = $res['length'];
        return $data;
    }

    //课程增加提交
    public function addLesson() 
    {
        $data = $this->getPostData();
        //添加一个lesson表字段
        $lesson = new LessonModel($data);
        $status = $lesson->save();
        //lesson添加成功后修改teacher表,将选中的老师的外键修改为当前的课(这并不像一个关联操作)
        if ($status) {
            $teacher_id = $this->request->param('teacher');
            if ($teacher_id != 0) {
                $lesson->teacher()->where('id', $teacher_id)->update(['lesson_id'=>$lesson->id]);
            }
            return $status;
        } else {
            return false;
        }
    } 

    //课程编辑修改提交
    public function editLesson() 
    {
        $data = $this->getPostData();
        //更新一个lesson表字段
        $lesson = LessonModel::get($data['id']);
        $status = $lesson->save($data, ['id'=>$data['id']]);
        //lesson添加成功后修改teacher表
        if (isset($status)) {
            //输入的待关联的教师id
            $teacher_id = $this->request->param('teacher');
            //判断是否设定关联教师,修改teacher表
            if ($teacher_id != 0) {
                //更新关联的教师的lesson_id
                $status = $lesson->teacher()->where('id', $teacher_id)->update(['lesson_id'=>$lesson->id]);
                //原始关联的教师的id
                $ori_teacher_id = empty($lesson->teacher->id) ? $lesson->teacher->id : false;
                if ($ori_teacher_id) {
                    //把原始关联的教师的lesson_id置0
                    $lesson->teacher()->where('id', $ori_teacher_id)->update(['lesson_id'=>0]);
                }
            } else {
                //不设定关联教师
                $status = $lesson->teacher()->where('id', $teacher_id)->update(['lesson_id'=>0]);
            }
            return $status;
        } else {
            return false;
        }
    } 

    //课程删除(软删除)
    public function lessonDelete()
    {
        $res = $this->request->param();
        foreach ($res as $v) {
            if (is_numeric($v)) {
                $id[] = $v;
            }
        }
        $status = LessonModel::destroy($id);
        return $status;
    }

    //课程恢复
    public function lessonRestore() 
    {
        //设定能够恢复的删除记录是时间在3天内的
        $allowDate = time()-3*24*60*60;
        $num = 0;
        $res = LessonModel::onlyTrashed()->where('delete_time', 'GT', $allowDate)->select();
        foreach ($res as $lesson) {
            $lesson->delete_time = null;
            $num += $lesson->save();
        }
        return $num;
    }

    //课程停用
    public function lessonStop()
    {
        $id = $this->request->param('id');
        $res = LessonModel::where('id', $id)->update(['status'=>0]);
        return $res;
    }

    //课程启用
    public function lessonStart()
    {
        $id = $this->request->param('id');
        $res = LessonModel::where('id', $id)->update(['status'=>1]);
        return $res;
    }

    //验证操作,返回数组到前台页面
    protected function dataValidate($data, $rule) 
    {
        $result = $this->validate($data, $rule);
        $status = ($result === true)? 1 : 0;
        return ['status'=>$status, 'message'=>$result];
    }

    //验证课程名是否合法
    public function checkName()
    {
        $name = $this->request->param('name');
        $id = $this->request->param('id');
        $data = ['name'=>$name];
        $rule = [
            'name|课程名'=>'require|length:3,20|unique:lesson,name,'.$id,
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证价格输入
    public function checkPrice()
    {
        $price = $this->request->param('price');
        $data = ['price'=>$price];
        $rule = [
            'price|价格'=>'require|between:0,65535',
        ];
        return $this->dataValidate($data, $rule);
    }

    //验证课时输入
    public function checklength()
    {
        $length = $this->request->param('length');
        $data = ['length'=>$length];
        $rule = [
            'length|课时'=>'require|between:0,36',
        ];
        return $this->dataValidate($data, $rule);
    }
}