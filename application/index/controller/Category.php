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

        //按级别顺序顺序获取全部文章
        $cat = $this->catShow();
        $catList = $cat;
        // 设置访问方式
        if ($this->request->has('type', 'param')) {
            $type = $this->request->param('type');
            switch ($type) {
                case '1':
                    $catList = $this->getCatByLevel(1);
                    break;
                case '2':
                    $catList = $this->getCatByLevel(2);
                    foreach ($catList as $v) {
                        $v->tabName = $v->name;
                    }
                    break;
                default:
                    break;
            }
        }
        //获取该页各个栏目文章数量,加入对象中
        for ($i = 0; $i < count($catList); $i++) {
            $catList[$i]->num = $this->getNum($catList[$i]->id, $cat) + $catList[$i]->article()->count();
        }
        $count = count($catList);
        $this->assign('count', $count);
        $this->assign('catList', $catList);
        return $this->fetch('category_list');
    }

    public function categoryAdd() 
    {
        $this->assign('select_list', $this->selectCat());
        return $this->fetch('category_add');
    }

    public function categoryEdit() 
    {
        if (!$this->request->has('id', 'param')) return "未获取编辑栏目id信息";
        $id = $this->request->param('id');
        $category_info = CategoryModel::get($id);
        $this->assign('category_info', $category_info);
        $this->assign('select_list', $this->selectCat());
        return $this->fetch('category_edit');
    }

    public function categoryAddExecu()
    {
        $data = $this->request->param();
        $result = $this->validate($data, 'Category');
        if ($result !== true) {
            return $result;
        }
        //判断一级栏目数量是否已经5个
        if ($data['parent_id'] == 0 && count($this->getCatByLevel(1)) == 5) {
            return "一级栏目只能有5个,请选择修改其他一级栏目或者删除其他一级栏目";
        }
        $category = new CategoryModel($data);
        $res = $category->allowField(true)->save();
        return $res;
    }

    public function categoryEditExecu()
    {
        $data = $this->request->param();
        $result = $this->validate($data, 'Category');
        if ($result !== true) {
            return $result;
        }
        //判断一级栏目数量是否已经5个
        if ($data['parent_id'] == 0 && count($this->getCatByLevel(1)) == 5) {
            return "一级栏目只能有5个,请选择修改其他一级栏目或者删除其他一级栏目";
        }
        $category = CategoryModel::get($data['id']);
        $res = $category->allowField(true)->save($data);
        return $res;
    }

    public function categoryDelete()
    {
        $data = $this->request->param();
        // return $data;
        if ($data['num'] === '0') {
            $category = CategoryModel::get($data['id']);
            $status = $category->delete();
            return $status;
        } else {
            return "栏目下存在文章,请在删除栏目下的所有文章后再进行操作";
        }
    }

    public function categoryChangeStatus() 
    {
        if (!$this->request->has('id', 'param')) return "未获取编辑栏目id信息";
        $id = $this->request->param('id');
        $category = CategoryModel::get($id);
        if ($category->parent_id === 0) {
            return "主栏目不允许关闭";
        }
        if ($category->status == 1) {
            $result = $category->save(['status'=>0]);
        } else {
            $result = $category->save(['status'=>1]);
        }
        if ($result) {
            return true;
        } else {
            return "更改栏目状态失败";
        }
    }

    //添加以及编辑栏目页面的下拉框内容(只显示一级内容)
    protected function selectCat()
    {
        return $this->getCatByLevel(1);
    }

    //输入级别,获取指定级别的文章,返回对象集
    protected function getCatByLevel($level)
    {
        $cat = $this->catShow();
        $level -= 1;
        $catList = [];
        foreach ($cat as $v) {
            if ($v->tab == $level) {
                $v->tabName = $v->name;
                $catList[] = $v;
            }
        }
        return $catList;
    }
}