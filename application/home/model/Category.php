<?php
namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;

class Category extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //关联user表
    public function article()
    {
        return $this->hasMany('Article', 'cat_id', 'id');
    }

}