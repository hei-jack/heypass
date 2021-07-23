<?php
//设置报错级别
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//引入类
require_once('./Helper.php');

//实例化安装助手函数
$helper = new Helper();
//拦截非post请求
if($_SERVER['REQUEST_METHOD'] !== 'POST') $helper->json(405,'非法请求！');

$action = @$_GET['a'];
$data = @$_POST;
if($action !== null && $data !== null){
  //如果两个参数都不为空 开始进行下一步
  if($action === 'step1'){
    //如果是步骤一 环境检测
    
  }else if($action === 'step2'){

  }else if($action === 'step3'){

  }else if($action === 'finsh'){

  }
  switch(strval($action)){
    case 'index':
      $helper->getInfo();
      break;
    case 'step1':
      $helper->step1();
  }
}