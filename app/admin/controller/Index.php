<?php
namespace app\admin\controller;
use think\Controller;
class Index extends controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }
}
