<?php
namespace app\index\controller;
//引入前台基类控制器
use \app\common\controller\HomeBase;

class Index extends HomeBase{
    //站点主页
    public function index(){
        //渲染模板
        return $this->fetch('../home/index');
    }
}
