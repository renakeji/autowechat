<?php
/**
 * @Author: renakeji
 * @Date:   2016-10-24 17:42:26
 * @Last Modified by:   renakeji
 * @Last Modified time: 2016-10-25 11:25:32
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
class cats extends controller{
    public function index(){
        $cats=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('parentid','0')->select();
        foreach ($cats as $k => $v) {
            if($cats[$k]['childid'] != ''){
                $child=explode(',', $cats[$k]['childid']);
                $cats[$k]['child']=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('id','in',$child)->select();
            }else{
                $cats[$k]['child']='';
            }
        }
        // var_dump($cats);
        $this->assign('cats',$cats);
        return $this->fetch();
    }


    public function add(){
        if (Request::instance()->isPOST()){
            $s=input('post.');
            $data=array(
                'catname'=>$s['catname'],
                'parentid'=>$s['parentid'],
                'addtime'=>time(),
                'author'=>'admin'
            );
            $ss=Db::name('cat')->field('id')->where('catname',$s['catname'])->find();
            if($ss['id'] > 0){
                echo json_encode(array("status"=>0,"content"=>"已经添加过此行业"));exit;
            }
            // Db::name('cat')->insert($data);
            $cid=Db::name('cat')->insertGetId($data);
            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 && $s['parentid'] > 0){
                $child=Db::name('cat')->field('childid')->where('id',$s['parentid'])->find();
                //拼接childid字段，如果为空，则直等，如果不为空，则加入‘,’分隔符
                if($child==''){
                    $childid=$child['childid'];
                }else{
                    $childid=$child['childid'].','.$cid;
                }
                if(Db::name('cat')->where('id',$s['parentid'])->update(['childid'=>$childid])){
                    echo json_encode(array("status"=>1,"content"=>"添加成功"));exit;
                }else{
                    echo json_encode(array("status"=>0,"content"=>"添加失败，请稍后重试"));exit;
                }
            }else{
                echo json_encode(array("status"=>1,"content"=>"添加成功"));exit;
            }
        }else{
            $cats=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('parentid','0')->select();
            foreach ($cats as $k => $v) {
                if($cats[$k]['childid'] != ''){
                    $child=explode(',', $cats[$k]['childid']);
                    $cats[$k]['child']=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('id','in',$child)->select();
                }else{
                    $cats[$k]['child']='';
                }
            }
            // var_dump($cats);
            $this->assign('cats',$cats);
            return $this->fetch();
        }
    }

    //当提交行业修改，在完成本身修改的同时，还应该新增新的父行业的childid，和删除原父行业的childid中本身的id
    public function edit(){
        if (Request::instance()->isPOST()){
            $s=input('post.');
            $ccid=$s['cid'];
            //行业的原父id
            $parid=$s['parid'];
            $data=array(
                'catname'=>$s['catname'],
                'parentid'=>$s['parentid'],
                'updatetime'=>time(),
                'author'=>'admin'
            );
            // $ss=Db::name('cat')->field('id')->where('catname',$s['catname'])->find();
            // if($ss['id'] > 0){
            //     echo json_encode(array("status"=>0,"content"=>"已经添加过此行业"));exit;
            // }
            // Db::name('cat')->insert($data);
            $cid=Db::name('cat')->where('id',$ccid)->update($data);
            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 && $s['parentid'] > 0){
                //在新的父行业中childid字段加入本身id
                $child=Db::name('cat')->field('childid')->where('id',$s['parentid'])->find();
                //拼接childid字段，如果为空，则直等，如果不为空，则加入‘,’分隔符
                if($child['childid']==''){
                    $childid=$child['childid'];
                }else{
                    $childid=$child['childid'].','.$ccid;
                }
                //结束
                //操作原父id，删除该行业id
                $oldchild=Db::name('cat')->field('childid')->where('id',$s['parid'])->find();
                $old=explode(',',$oldchild['childid']);
                $new=array();
                foreach ($old as $k => $v) {
                    if($old[$k]!=$ccid){
                        $new[]=$old[$k];
                    }
                }
                $newchildid=implode(',',$new);
                //结束
                if((Db::name('cat')->where('id',$s['parentid'])->update(['childid'=>$childid])) && (Db::name('cat')->where('id',$s['parid'])->update(['childid'=>$newchildid]))){
                    echo json_encode(array("status"=>1,"content"=>"修改成功"));exit;
                }else{
                    echo json_encode(array("status"=>0,"content"=>"修改失败，请稍后重试"));exit;
                }
            }else if($cid > 0 && $s['parentid'] = 0){
                echo json_encode(array("status"=>1,"content"=>"修改成功"));exit;
            }else{
                echo json_encode(array("status"=>0,"content"=>"修改失败，请稍后重试"));exit;
            }
        }else{
            $cid=input('get.cid');

            $thiscat=Db::name('cat')->where('isdel','<>','1')->where('status','1')->where('id',$cid)->find();

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
            $this->assign('thiscat',$thiscat);
            return $this->fetch();
        }
    }


    public function del(){
        $cid=input('get.cid');
        if(Db::name('cat')->where('id',$cid)->update(['isdel'=>1,'updatetime'=>time()])){
            echo json_encode(array("status"=>1,"content"=>"删除成功"));exit;
        }else{
            echo json_encode(array("status"=>0,"content"=>"删除失败，请稍后重试"));exit;
        }
    }
}