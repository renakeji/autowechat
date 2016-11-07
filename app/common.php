<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


        function checkcat($catid){
            $cats= \think\Db::name('cat')->where('isdel','<>','1')->where('status','1')->select();
            foreach ($cats as $k => $v) {
                if($cats[$k]['id']==$catid){
                    return $cats[$k]['catname'];
                }
            }
        }

        function GrabImage($url, $filename = "") {
            if ($url == ""):return false;
            endif;
            //如果$url地址为空，直接退出
            if ($filename == "") {
                //如果没有指定新的文件名
                $ext = strrchr($url, ".");
                //得到$url的图片格式
                if ($ext != ".gif" && $ext != ".jpg" && $ext != ".png" && $ext != ".jpeg"):return false;
                endif;
                //如果图片格式不为.gif或者.jpg，直接退出
                $filenames=date("dMYHis").mt_rand(10000,99999) . $ext;
                $filename = __DIR__.'/../public/imgtemp/'.$filenames;
                //用天月面时分秒来命名新的文件名
            }
            ob_start();//打开输出
            readfile($url);//输出图片文件
            $img = ob_get_contents();//得到浏览器输出
            ob_end_clean();//清除输出并关闭
            $size = strlen($img);//得到图片大小
            $fp2 = @fopen($filename, "a");
            fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名
            fclose($fp2);
            return realpath($filename);//返回新的文件名
        }