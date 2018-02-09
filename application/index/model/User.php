<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
    //引入软删除,如果字段中is_delete值为0标记删除
    use SoftDelete;
    //用于记录删除时间
    protected $deleteTime = 'delete_time';
    //增加或更新时自动更新时间戳(外面必须由模型对象方式操作)
    protected $autoWriteTimestamp = true;
    //新建自动写入状态
    protected $insert = ['status'=>1];

    public function getRoleAttr($value)
    {
        $role = [
            4=>'超级管理员', 
            3=>'管理员', 
            2=>'高级用户', 
            1=>'普通用户',
        ];
        return $role[$value];
    }

    //定义关联article表
    public function article()
    {
        return $this->hasMany('Article', 'user_id', 'id');
    }

    //定义关联comment表
    public function comment()
    {
        return $this->hasMany('Comment', 'user_id', 'id');
    }

}