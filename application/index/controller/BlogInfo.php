<?php
namespace app\index\controller;

use app\base\controller\Base;
use think\Db;
use think\Exception;
use app\index\model\BlogInfo as BlogInfoModel;

class BlogInfo extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }

    public function InfoData()
    {
        $this->assign('title', '博客信息管理');
        $this->assign('keywords', '博客管理');
        $this->assign('desc', '博客的后台管理');
        $info = BlogInfoModel::get(1);
        $this->assign('info', $info);
        return $this->fetch('blog_info/blog_info');
    }

    public function infoDataEdit()
    {
        Db::startTrans();
        try {
            //组合表单中的参数
            $data = $this->request->param();
            $data['img'] = $this->request->file('img');
            // 验证参数
            $result = $this->validate($data, 'BlogInfo');
            if ($result !== true) throw new Exception($result);
            //得到图像路径
            $protrait_path = $this->uploadProtrait($data['img']);
            if ($protrait_path) {
                $data['protrait_path'] = $protrait_path;
            }
            $blog_info = new BlogInfoModel;
            $res = $blog_info->isUpdate(true)->allowField(true)->save($data);
            //如果完全没有任何的操作就提交,抛出异常
            if (!$res && !$data['img']) throw new Exception('未更新博客信息');
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        return true;
    }

    //上传头像
    protected function uploadProtrait($file)
    {
        if ($file) {
            $ext = strrchr($file->getInfo()['name'], '.');
            $info = $file->move(ROOT_PATH . 'public' . DS . 'protrait', 'protrait');
            if ($info){
                //上传成功,返回路径
                $img_path = '/protrait/' . $info->getSaveName();
                $thumb_path = $this->createThumb($img_path, $ext);
                return $thumb_path;
            } else {
                throw new Exception($file->getError());
            }
        } else {
            return false;
        }
    }

    //生成缩略图,输入原图路径和文件后缀,输出缩略图路径
    protected function createThumb($img_path, $ext)
    {
        $thumb_path = dirname($img_path) . DS . 'thumb_protrait' . $ext;
        $image = \think\Image::open('.' . $img_path);
        $image->thumb(290, 150)->save('.' . $thumb_path);
        return $thumb_path;
    }

}   
