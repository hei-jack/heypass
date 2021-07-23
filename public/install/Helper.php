<?php
//安装助手类
class Helper{
  //判断函数是否存在
  public function exists(){

  }

  //返回json数据
  public function json($code,$msg,$data = ''){
    //设置响应头
    header('Content-type: application/json;charset=UTF-8');
    echo json_encode(array('status' => $code,'mess' => $msg,'data' => $data));
    die; //退出脚本运行
  }
  
  //获取环境信息
  public function getInfo(){
    $data = array();
    $data['system'] = PHP_OS; //操作系统
    $data['php_vesion'] = PHP_VERSION; //php版本
    //判断php mysqli拓展是否开启
    $data['mysqli'] = function_exists('mysqli_query') ? 'YES' : 'NO';
    $data['openssl'] = function_exists('openssl_encrypt') ? 'YES' : 'NO';
    $data['gd'] = function_exists('gd_info') ? 'YES' : 'NO';
    $data['mb_string'] = function_exists('mb_strlen') ? 'YES' : 'NO';
    $this->json(200,'返回成功',$data);
  }

  //判断是否完成第一步
  public function step1(){

  }

  //删除文件
  public function delFile(){

  }
}