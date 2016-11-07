<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
// use Symfony\Component\HttpFoundation\Request;
use think\Session;
use Wechat\Wechat;
class Index extends controller
{
    public function index($token='',$id=28)
    {
        if($token != 'yourtoken'){
            // echo '不允许访问，';exit;
        }
        //获取用户信息
        $user=Db::name('mpusers')->where('id',$id)->find();
        $options = array(
            'appid'=>$user['appid'],
            'appsecret'=>$user['appsecret']
        );
        // $weObj = new Wechat($options);
        // $authtoken=$weObj->checkAuth();
        // 根据需求取出相应行业的相应条数的信息
        $news=Db::name('news')->where('catid',$user['catid'])->where('isdel','0')->select();
        $max=count($news);
        if($max > $user['nums']){
            shuffle($news);
            for($i=0;$i<$user['nums'];$i++){
                // $news[$i]['content']=htmlspecialchars($news[$i]['content']);
                $sendnews[]=$news[$i];
            }
        }else{
            $sendnews=$news;
        }
        // echo $authtoken;
        // var_dump($sendnews);exit;
        //上传图文信息的缩略图到微信服务器
        foreach ($sendnews as $k => $v) {
            //上传缩略图
            // $realimg=str_replace("\\", "/", realpath(__DIR__.'/../../../public/uploads/'.$sendnews[$k]['img']));
            // $imgid=$weObj->uploadMedia(array('media'=>'@'.$realimg),'image');
            // $sendnews[$k]['imgid']=$imgid['media_id'];
            //96微信编辑器的图片修正
            $sendnews[$k]['content']=str_replace('data-wxsrc','src',$sendnews[$k]['content']);
            //本地上传图片的处理
            //处理文章内的图片
           preg_match_all('/<img.*? src=\"http:\/\/im2\.96weixin\.com?(.*?\.(jpg|gif|bmp|bnp|png|mp4))\"?.*?>/i',$sendnews[$k]['content'],$match);
            $result_media=$match[1];

            foreach($result_media as $key_media=>$val_media){
                var_dump($val_media);
                $url_media=explode('"',$val_media);
                $url_me=$url_media[0];
                // var_dump($url_me);
           //      $urllh=GrabImage($url_media[0],'');
           //      $urlmedia=$weObj->uploadImg(array("media"=>"@".$urllh),'image');
           //      $sendnews[$k]['content'] = str_replace($val_media,'\"'.$urlmedia['url'],$sendnews[$k]['content']);//将URL替换为微信平台返回的URL
            }
        }
        exit;
        //拼凑图文
        $articles[]=array(
            "thumb_media_id"=>$sendnews[0]['imgid'],
            // "author"=>$user['name'],
            "title"=>$sendnews[0]['title'],
            "content"=>$sendnews[0]['content'],
            "show_cover_pic"=>1
        );
        $newss=count($sendnews);
        for ($i=1; $i < $newss; $i++) {
            $articles[]=array(
                "thumb_media_id"=>$sendnews[$i]['imgid'],
                // "author"=>$user['name'],
                "title"=>$sendnews[$i]['title'],
                "content"=>$sendnews[$i]['content'],
                "show_cover_pic"=>0
            );
        }
        // print_r($articles);
        $sendarticles=array('articles'=>$articles);
// var_dump($sendarticles);exit;
        // 上传素材
        $status=$weObj->uploadArticles($sendarticles);
        // var_dump($status);exit;
        $news_media_id=$status['media_id'];
        // $news_media_id='YQiz8htW61FxZpR44wcQbiODRF48Di9I3raXmy1LGlqs8JUnhtc0C8eRJSduEOx8';
        //获取用户列表
        $userlists=$weObj->getUserList();
        $userlist=$userlists['data']['openid'];
        //拼凑数据
        $touser=array("touser"=>$userlist,"mpnews"=>array("media_id"=>$news_media_id),"msgtype"=>"mpnews");
        //发送
        $sendstatus=$weObj->sendMassMessage($touser);
        // Session::set("sendstatus",$sendstatus);
        //结果处理
        // $sendstatus=Session::get('sendstatus');
        if($sendstatus['errcode']==0){
            $data['content']='errcode'.$sendstatus['errcode'].'errmsg'.$sendstatus['errmsg'].'msg_id'.$sendstatus['msg_id'].'msg_data_id'.$sendstatus['msg_data_id'];
            $data['actiontime']=time();
            $data['uid']=$user['id'];
            $data['msgid']=$sendstatus['msg_id'];
            // $data['ip']=getClientIp();
            $data['action']='is_to_all';
            $cid=Db::name('logs')->insertGetId($data);
            if($cid > 0){
                echo '1';
            }else{
                echo '0';
            }
        }
        // var_dump($sendstatus);exit;
        // $group=$weObj->getGroup();
        // var_dump($group);

        // return $this->fetch();
    }

    public function checkuser($token,$id){
        if($token != 'yourtoken'){
            // echo '不允许访问，';exit;
        }else{
            $user=Db::name('mpusers')->where('id',$id)->find();
            if($user['endtime'] < time() || $user['status']==0){
                echo '0';
            }else{
                echo '1';
            }
        }
    }
}
