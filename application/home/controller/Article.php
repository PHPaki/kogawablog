<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Article as ArticleModel;
use app\home\model\Category;
use app\home\model\Tag;
use think\Cookie;

class Article extends Base
{
    protected $article_id = 0;

    //文章显示页面
    public function article()
    {
        $this->catList();
        $this->tagList();
        $this->commentList();
        $this->articlePage();
        return $this->fetch('article');
    }     

    //文章添加页面
    public function articleAdd()
    {
        $this->catList();
        $this->tagList();
        $this->commentList();

        $this->catShow();
        return $this->fetch('article_add');
    }

    //文章的内容
    protected function articlePage()
    {
        if ($this->request->has('article_id', 'param')) {
            $this->article_id = $this->request->param('article_id');
            $article = ArticleModel::get($this->article_id);
            $this->assign('article', $article);
        } else {
            $this->error('未查询到该文章');
        }
    }

    //文章添加后台处理
    public function articleAddExecu()
    {

        $data = $this->request->param();
        //用cookie存储编辑器内容
        Cookie::set('content', $data['content'], 3600);
        //检查输入合法性
        $check_result = $this->validate($data, 'Article');
        if ($check_result !== true) {
            return $this->error($check_result);
        }
        //图片上传处理
        $file = $this->uploadImg();
        $data['img_path'] = $file['img_path'];
        //生成缩略图
        $data['thumb_path'] = $this->createThumb($file['img_path'], $file['ext']);
        //用户信息
        $data['user_id'] = $this->request->session('user_id');

        //存入article
        $article = new ArticleModel($data);
        $article->allowField(true)->save();
        //存入article_content
        $article->articleContent()->save(['content'=>$data['content']]);
        //存入tag表
        $status = $this->tagSave($article, $data['tags']);
        if ($status){
            $this->error($status);
        } else {
            Cookie::delete('content');
            $this->success("发布文章成功", 'index/index');
        }
    }

    //为添加页面的选择框提供所有栏目(按分类排序)
    public function catShow()
    {
        $catShow = Category::getDivide();
        $this->assign('catShow', $catShow);
    }

    //图片上传处理,接受post的img文件信息,返回数组:存储地址和文件后缀
    protected function uploadImg()
    {
        $file = $this->request->file('img');
        $data['ext'] = strrchr($file->getInfo()['name'], '.');
        if ($file) {
            //文件验证后移动到指定文件夹(文件夹不存在自动创建)
            $fileInfo = $file->validate(['size'=>2097152, 'ext'=>'jpg,jpeg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($fileInfo) {
                $data['img_path'] = DS . "uploads" . DS . $fileInfo->getSaveName();
            } else {
                $this->error($fileInfo->getError());
            }
        }
        return $data;
    }

    //生成缩略图,输入原图路径和文件后缀,输出缩略图路径
    protected function createThumb($img_path, $ext)
    {
        $thumb_path = dirname($img_path) . DS . md5(microtime()) . $ext;
        $image = \think\Image::open('.' . $img_path);
        $image->thumb(364, 227)->save('.' . $thumb_path);
        return $thumb_path;
    }

    //输入文章对象,tags字符串,检测tag表是否存在同名tag
    //存在则只中间表新增关系,不存在则新增tag,并中间表新增关系
    protected function tagSave($article, $tags)
    {
        $new_tag = explode(',', $tags);
        foreach ($new_tag as $vo) {
            $check_tag = Tag::where('name', $vo)->find();
            //不存在该Tag
            if ($check_tag == null) {
                $status = $article->tag()->save(['name'=>$vo]) ? false : true;
            //存在该Tag
            } else {
                $status = $article->tag()->save($check_tag) ? false : true;
            }
            if ($status) {
                return $article->tag()->getError();
            }
        }
    }

}