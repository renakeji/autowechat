<?php
/**
 * @Author: renakeji
 * @Date:   2016-10-24 17:14:59
 * @Last Modified by:   renakeji
 * @Last Modified time: 2016-10-25 16:27:57
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
class news extends controller{

    public function _initialize(){
        //插入行业
        $cats=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('parentid','0')->select();
        foreach ($cats as $k => $v) {
            if($cats[$k]['childid'] != ''){
                $child=explode(',', $cats[$k]['childid']);
                $cats[$k]['child']=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('id','in',$child)->select();
            }else{
                $cats[$k]['child']='';
            }
        }

        $this->assign('cats',$cats);
        //结束
    }
    public function index (){

        //插入新闻
        $news=Db::name('news')->where('isdel','<>','1')->where('isshow','1')->select();
        // foreach ($cats as $k => $v) {
        //     if($cats[$k]['childid'] != ''){
        //         $child=explode(',', $cats[$k]['childid']);
        //         $cats[$k]['child']=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('id','in',$child)->select();
        //     }else{
        //         $cats[$k]['child']='';
        //     }
        // }

        $this->assign('news',$news);
        //结束
        return $this->fetch();
    }

    public function add(){
        if (Request::instance()->isPOST()){
            $s=input('post.');
            $data=array(
                'title'=>$s['newstitle'],
                'catid'=>$s['catid'],
                'img'=>$s['imgurl'],
                'dcps'=>$s['newsdcps'],
                'content'=>$s['content'],
                'isshow'=>1,
                'trueauthor'=>'admin',
                'addtime'=>time()
            );
            // Db::name('cat')->insert($data);
            $cid=Db::name('news')->insertGetId($data);
            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 ){
                    echo json_encode(array("status"=>1,"content"=>"添加成功"));exit;
                }else{
                    echo json_encode(array("status"=>0,"content"=>"添加失败，请稍后重试"));exit;
                }
        }else{
            return $this->fetch();
        }
    }

    public function edit(){
        if (Request::instance()->isPOST()){
            $s=input('post.');
            $nid=$s['nid'];
            $data=array(
                'title'=>$s['newstitle'],
                'catid'=>$s['catid'],
                'img'=>$s['imgurl'],
                'dcps'=>$s['newsdcps'],
                'content'=>$s['content'],
                'isshow'=>1,
                'trueauthor'=>'admin',
                'updatetime'=>time()
            );
            // Db::name('cat')->insert($data);
            $cid=Db::name('news')->where('id',$nid)->update($data);
            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 ){
                    echo json_encode(array("status"=>1,"content"=>"修改成功"));exit;
                }else{
                    echo json_encode(array("status"=>0,"content"=>"修改失败，请稍后重试"));exit;
                }
        }else{
            $nid=input('get.nid');
            $thisnew=Db::name('news')->where('isdel','<>','1')->where('isshow','1')->where('id',$nid)->find();
            $this->assign('thisnew',$thisnew);
            return $this->fetch();
        }
    }

    public function del(){
        $cid=input('get.nid');
        if(Db::name('news')->where('id',$cid)->update(['isdel'=>1,'updatetime'=>time()])){
            echo json_encode(array("status"=>1,"content"=>"删除成功"));exit;
        }else{
            echo json_encode(array("status"=>0,"content"=>"删除失败，请稍后重试"));exit;
        }
    }
}

