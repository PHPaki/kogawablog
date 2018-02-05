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

    //无限级分类(可直接静态调用获取按层级顺序分类的栏目)
    //无需输入参数,参数在递归中自动传入,$parent :父级id, $tab :缩进数量
    public static function getDivide($parent_id = 0, $tab = -1)
    {
        $data = Category::all();
        $list = [];
        $tab++;
        foreach ($data as $vo) {
            if ($vo->parent_id == $parent_id) {
                $vo['tab'] = $tab;
                $vo['tabName'] = str_repeat('--', $vo['tab']).$vo['name'];
                $list[] = $vo;
                $list = array_merge($list, self::getDivide($vo->id, $tab));
            }
        }
        return $list;
    }


}