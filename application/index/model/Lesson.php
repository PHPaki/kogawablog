<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class Lesson extends Model
{
    //引入软删除,如果字段中is_delete值为0标记删除
    use SoftDelete;
    //用于记录删除时间
    protected $deleteTime = 'delete_time';
    //增加或更新时自动更新时间戳(外面必须由模型对象方式操作)
    protected $autoWriteTimestamp = true;
    //新建自动写入状态
    protected $insert = ['status'=>1];

    //关联教师模型
    public function teacher()
    {
        //参数分别是关联模型名,两表关联外键(在子表), 本表主键名
        return $this->hasOne('Teacher', 'lesson_id', 'id');
    }

    //关联学生模型
        public function student()
    {
        return $this->hasMany('Student', 'lesson_id', 'id');
    }

}