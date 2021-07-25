<?php
// 控制器基类
namespace app\common\controller;
use think\Controller;

// 声明类并继续公共控制器
class Base extends Controller{
    // 定义空操作
    public function _empty(){
      abort(404, '该页面不存在！');
    }
}