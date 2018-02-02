<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class Student extends Model
{
    //引入软删除,如果字段中is_delete值为0标记删除
    use SoftDelete;
    //用于记录删除时间
    protected $deleteTime = 'delete_time';
    //增加或更新时自动更新时间戳(外面必须由模型对象方式操作)
    protected $autoWriteTimestamp = true;
    //新建自动写入状态
    protected $insert = ['status'=>1];

    public function getSexAttr($value)
    {
        $sex = [0=>'女', 1=>'男'];
        return $sex[$value];
    }

    //关联课程模型
    public function lesson()
    {
        return $this->belongsTo('Lesson', 'lesson_id', 'id');
    }

}