<?php
namespace app\index\controller;

use app\base\controller\Base;
use app\home\model\Article as ArticleModel;
use app\home\model\Tag;
use think\Cookie;
use think\Db;
use think\Exception;

class Article extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }
    
    //文章显示
    public function articleList()
    {
        $this->assign([
            'title'=>'博客文章管理',
            'keywords'=>'文章',
            'description'=>'博客的各类文章管理',
        ]);

        //获取分页的数据
        $list = ArticleModel::order('id asc')->paginate(5);
        $count = $list->total();
        //赋值模版
        $this->assign('list', $list);
        $this->assign('count', $count);
        return $this->fetch('article_list');
    }

    //文章编辑页面
    public function articleEdit()
    {
        $id = $this->request->param('id');
        $res = ArticleModel::get($id);
        $this->assign('res', $res);
        //把栏目内容放入
        $this->catShow();
        return $this->view->fetch('article_edit');
    }

    //增加文章页面
    public function articleAdd() 
    {
        $this->catShow();
        return $this->fetch('article_add');
    } 

    //获取文章数据,返回数组或者跳转错误页面
    protected function getPostData() 
    {
        $data = $this->request->param();
        //用cookie存储编辑器内容
        if ($this->request->has('content', 'param')) Cookie::set('content', $data['content'], 3600);
        //验证数据
        $check_result = $this->validate($data, "Article");
        if ($check_result !== true) {
            throw new Exception($check_result);
        }
        //判断是否图片上传处理
        if ($this->request->has('img', 'file')) {
            $file = $this->uploadImg();
            $data['img_path'] = $file['img_path'];
            //生成缩略图
            $data['thumb_path'] = $this->createThumb($file['img_path'], $file['ext']);
            
        } else {
            //使用默认图片
            $data['img_path'] = '/static/home/assets/i/f18.jpg';
            $data['thumb_path'] = '/static/home/assets/i/f18.jpg';
        }
        //用户信息
        $data['user_id'] = $this->request->session('user_id');
        //修正tags
        return $data;
    }

    //文章新增提交
    public function articleAddExecu()
    {
        Db::startTrans();
        try {
            $data = $this->getPostData();
            //存入article
            $article = new ArticleModel($data);
            if (!$article->allowField(true)->save()) throw new Exception('存入article表错误');
            //存入article_content
            if (!$article->articleContent()->save(['content'=>$data['content']])) throw new Exception('存入article_content表错误');
            //存入tag表
            if (str_replace(' ', '', $data['tags'])) {
                $this->tagSave($article, $data['tags']);
            }
            Db::commit();
        } catch(Exception $e) {
            Db::rollback();
            if ($this->request->isAjax()) {
                return $e->getMessage();
            } else {
                $this->error($e->getMessage());
            }
        }
        Cookie::delete('content');
        if ($this->request->isAjax()) {
            return true;
        } else {
            $this->success("发布文章成功", 'home/index/index');
        }
    }

    //文章编辑提交
    public function articleEditExecu() 
    {
        Db::startTrans();
        try {
            $data = $this->getPostData();
            $field = $this->request->file('img') ? true : ['title', 'subtitle', 'cat_id', 'tags'];
            if (empty($data['id'])) throw new Exception('文章id传入错误');
            $article = ArticleModel::get($data['id']);
            if ($article->articleContent->save(['content'=>$data['content']]) === false) throw new Exception('更新article_content表错误');
            //更新tag的步骤是把关联tag全部删除后,再新增tag
            if (str_replace(' ', '', $article->tags)) $this->tagDelete($article);
            if (str_replace(' ', '', $data['tags'])) $this->tagSave($article, $data['tags']);
            $article = new ArticleModel();
            if (!$article->allowField($field)->save($data, ['id'=>$data['id']])) throw new Exception("更新article表错误");
            Db::commit();
        } catch(Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        Cookie::delete('content');
        return true;      
        
    } 

    //文章删除(返回文章删除数量或者异常信息)
    public function articleDelete()
    {
        $arr = $this->request->param();
        $res = is_array($arr) ? $arr : ['arr'=>$arr];
        $num = 0;
        //遍历要删除的id,将关联内容删除
        foreach ($res as $v) {
            if (is_numeric($v)) {
                // 开启事务
                Db::startTrans();
                try {
                $article = ArticleModel::get($v);
                //删除关联文章内容
                $content = $article->articleContent->content;
                if (!$article->articleContent->delete()) throw new Exception("文章内容删除失败");
                $this->artPicDelete($content);
                //删除关联评论(可能不存在)
                if ($article->comment()->count() != 0) {
                    if (!$article->comment()->delete()) throw new Exception("文章评论删除失败");
                }
                //删除关联标签(可能不存在)
                if (str_replace(' ', '', $article->tags)) {
                    $this->tagDelete($article);
                }
                //删除文章
                $thumb_path = $this->request->server('DOCUMENT_ROOT') . $article->thumb_path;
                $img_path = $this->request->server('DOCUMENT_ROOT') . $article->img_path;
                if (!$article->delete()) throw new Exception("文章删除失败");
                // 判断是否是上传的图片,删除图片文件(如何预防单文件删除失败?)
                if (strpos($thumb_path, 'uploads')) {
                    if (!unlink($thumb_path)) throw new Exception("缩略图文件删除失败");
                    if (!unlink($img_path)) throw new Exception("图片文件删除失败");
                }
                Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    return $e->getMessage();
                } 
                $num++;
            }
        }
        return $num;
    }

    //图片上传处理,接受post的img文件信息,返回数组:存储地址和文件后缀
    protected function uploadImg()
    {
        $file = $this->request->file('img');
        //这里的getInfo是魔术方法
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

    //保存文章关联tag  参数为 文章对象,tags字符串  检测tag表是否存在同名tag
    protected function tagSave($article, $tags)
    {
        $new_tag = explode(',', $tags);
        foreach ($new_tag as $vo) {
            //不允许存在空标签
            $vo = str_replace(' ', '', $vo);
            if ($vo) {
                $check_tag = Tag::where('name', $vo)->find();
                //不存在该Tag,新增tag,中间表新增关系
                if ($check_tag == null) {
                    if (!$article->tag()->save(['name'=>$vo])) throw new Exception("新增tag失败");
                //存在该Tag,只中间表新增关系
                } else {
                    if (!$article->tag()->save($check_tag)) throw new Exception("tag数量增加失败");
                    $check_tag->num += 1;
                    $check_tag->save();
                }
            }
        }
        return true;
    }

    //清空文章关联tag 参数为 文章对象
    protected function tagDelete($article) 
    {
        //删除tag关联Tag,不存在文章的
        foreach ($article->tag as $t) {
            if ($t->num == 1) {
                if (!$t->delete()) throw new Exception("文章标签删除失败");
            } else {
                $t->num -= 1;
                if (!$t->save()) throw new Exception("文章标签修改失败");
            }
        }
        //删除tag关联该文章的全部数据
        if (!Db::table('tag_relation')->where('article_id', $article->id)->delete()) throw new Exception("文章标签中间表修改失败");
    }  

    //删除文章内容里的图片文件
    protected function artPicDelete($content)
    {
        //u编辑器内的图片会自动上传到后台,并在内容中留下图片的路径
        $data = explode('<img src="', $content);
        array_shift($data);
        if ($data) {
            foreach ($data as $v) {
                if (!unlink($this->request->server('DOCUMENT_ROOT') . substr($v, 0, strpos($v, '"')))) throw new Exception("文章内图片删除失败");;
            }
        }
    }
}