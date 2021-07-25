<?php
//设置报错级别
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

//定义路径分隔符常量
define('DS',DIRECTORY_SEPARATOR);

//定义根目录
define('ROOT_PATH',dirname(dirname(__DIR__)));

//引入助手类
require_once('./Helper.php');


//实例化安装助手类
$helper = new Helper();
//拦截非post请求
if($_SERVER['REQUEST_METHOD'] !== 'POST') $helper->json(405,'非法请求！');

$action = @$_GET['a'];
$data = @$_POST;
if($action !== null && $data !== null){
  //如果两个参数都不为空 开始进行下一步
  switch(strval($action)){
    case 'index':
      $helper->getInfo();
      break;
    case 'step2':
      $helper->step2($data);
      break;
    case 'step3':
      $helper->step3($data);
      break;
    case 'finsh':
      $helper->finsh();
  }
}