<?php
namespace app\index\controller;

use app\base\controller\Base;
use app\home\model\Category as CategoryModel;

class Category extends Base
{
    public function categoryList() 
    {
        $this->isLogin();
        $this->assign([
            'title'=>'栏目管理',
            'keywords'=>'博客栏目',
            'description'=>'小川的博客栏目管理',
        ]);
        $cat = $this->catList();


        $catShow = $this->catShow();
        //显示各个栏目文章数量(多级分类可用)
        for ($i = 0; $i < count($catShow); $i++) {
            $catShow[$i]->num = $this->getNum($catShow[$i]->id, $catShow) + $catShow[$i]->article()->count();
        }
        $this->assign('catShow', $catShow);
        dump($catShow);


    }

}