<?php
/**
 * @Author: renakeji
 * @Date:   2016-10-24 17:35:37
 * @Last Modified by:   renakeji
 * @Last Modified time: 2016-11-07 11:14:43
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;

class mpusers extends controller
{
    public  function _initialize(){
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
    }
    public function index(){
        $mpusers=Db::name('mpusers')->where('isdel','<>','1')->select();
        // var_dump($cats);
        $this->assign('mpusers',$mpusers);
        return $this->fetch();
    }


    public function add(){

        if (Request::instance()->isPOST()){

            $s=input('post.');
            // var_dump($s);exit;
            $data=array(
                'name'=>$s['name'],
                'appid'=>$s['appid'],
                'appsecret'=>$s['appsecret'],
                'nums'=>$s['nums'],
                'catid'=>$s['catid'],
                'sendtime'=>strtotime($s['sendtime']),
                'status'=>$s['status'],
                'starttime'=>strtotime($s['starttime']),
                'endtime'=>strtotime($s['endtime']),
                'addtime'=>time()
            );
            // Db::name('cat')->insert($data);
            $sid=Db::name('mpusers')->where('appid',$data['appid'])->where('isdel',0)->find();
            if($sid['id'] > 0){
                echo json_encode(array("status"=>0,"content"=>"已经存在此账户"));exit;
            }
            $cid=Db::name('mpusers')->insertGetId($data);

            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 ){
                    $s=fopen(__DIR__.'/../crond/'.$cid.'.crond','w+');
                    fwrite($s,'#! /bin/bash'."\n");
                    fwrite($s,'SENDTIME='.$data['sendtime']."\n");
                    fwrite($s,'NOWTIME=`date +%s`'."\n");
                    fwrite($s,'let TIMES=($NOWTIME - $SENDTIME)'."\n");
                    fwrite($s,'let CHATIME=($TIMES % 86400)'."\n");
                    fwrite($s,'if test $CHATIME -gt -150 && (test $CHATIME -eq 150 || test $CHATIME -lt 150)'."\n");
                    fwrite($s,'then'."\n");
                    fwrite($s,'if test `curl http://youdomain.com/index/index/index/token/youtoken/id/'.$cid.'` -eq 1'."\n");
                    fwrite($s,'then'."\n");
                    fwrite($s,'echo \'发送成功\' >> /var/www/autowechat/logs/'.$cid.'.log'."\n");
                    fwrite($s,'else'."\n");
                    fwrite($s,'echo \'发送失败\' >> /var/www/autowechat/logs/'.$cid.'.log'."\n");
                    fwrite($s,'fi'."\n");
                    fwrite($s,'else'."\n");
                    fwrite($s,'echo \'时间不对\' > /var/www/autowechat/logs/'.$cid.'.log'."\n");
                    fwrite($s,'fi'."\n");
                    fwrite($s,'exit 0'."\n");
                    fclose($s);
                    if($s['status']==0){
                        $file=__DIR__.'/../crond/'.$cid.'.crond';
                        @unlink($file);
                    }
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
            $uid=$s['uid'];
            $data=array(
                'name'=>$s['name'],
                'appid'=>$s['appid'],
                'appsecret'=>$s['appsecret'],
                'nums'=>$s['nums'],
                'catid'=>$s['catid'],
                'sendtime'=>strtotime($s['sendtime']),
                'status'=>$s['status'],
                'starttime'=>strtotime($s['starttime']),
                'endtime'=>strtotime($s['endtime']),
                'updatetime'=>time()
            );
            // Db::name('cat')->insert($data);
            $cid=Db::name('mpusers')->where('id',$uid)->update($data);
            //当栏目写入成功，并且不是顶级栏目,则将返回的栏目id写入到父id的子id字段
            if($cid > 0 ){
                    $s=fopen(__DIR__.'/../crond/'.$uid.'.crond','w+');
                    fwrite($s,'#! /bin/bash'."\n");
                    fwrite($s,'SENDTIME='.$data['sendtime']."\n");
                    fwrite($s,'NOWTIME=`date +%s`'."\n");
                    fwrite($s,'let TIMES=($NOWTIME - $SENDTIME)'."\n");
                    fwrite($s,'let CHATIME=($TIMES % 86400)'."\n");
                    fwrite($s,'if test $CHATIME -gt -150 && (test $CHATIME -eq 150 || test $CHATIME -lt 150)'."\n");
                    fwrite($s,'then'."\n");
                    fwrite($s,'if test `curl http://youdomain.com/index/index/index/token/yourtoken/id/'.$uid.'` -eq 1'."\n");
                    fwrite($s,'then'."\n");
                    fwrite($s,'echo \'发送成功\' >> /var/www/autowechat/logs/'.$uid.'.log'."\n");
                    fwrite($s,'else'."\n");
                    fwrite($s,'echo \'发送失败\' >> /var/www/autowechat/logs/'.$uid.'.log'."\n");
                    fwrite($s,'fi'."\n");
                    fwrite($s,'else'."\n");
                    fwrite($s,'echo \'时间不对\' > /var/www/autowechat/logs/'.$uid.'.log'."\n");
                    fwrite($s,'fi'."\n");
                    fwrite($s,'exit 0'."\n");
                    fclose($s);
                    if($s['status']==0){
                        $file=__DIR__.'/../crond/'.$cid.'.crond';
                        @unlink($file);
                    }
                    echo json_encode(array("status"=>1,"content"=>"修改成功"));exit;
                }else{
                    echo json_encode(array("status"=>0,"content"=>"修改失败，请稍后重试"));exit;
                }
        }else{
            $uid=input('get.uid');
            $thisuser=Db::name('mpusers')->where('isdel','<>','1')->where('id',$uid)->find();
            $this->assign('thisuser',$thisuser);
            return $this->fetch();
        }
    }

    public function del(){
        $cid=input('get.uid');
        if(Db::name('mpusers')->where('id',$cid)->update(['isdel'=>1,'updatetime'=>time()])){
            $file=__DIR__.'/../crond/'.$cid.'.crond';
            @unlink($file);
            echo json_encode(array("status"=>1,"content"=>"删除成功"));exit;
        }else{
            echo json_encode(array("status"=>0,"content"=>"删除失败，请稍后重试"));exit;
        }
    }

    public function update(){
        $cid=input('get.uid');
        $status=input('get.status');
        if(Db::name('mpusers')->where('id',$cid)->update(['status'=>$status,'updatetime'=>time()])){
            echo json_encode(array("status"=>1,"content"=>"更新成功"));exit;
        }else{
            echo json_encode(array("status"=>0,"content"=>"更新失败，请稍后重试"));exit;
        }
    }
}