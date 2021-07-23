<?php
//网站设置模型
namespace app\common\model;
use think\Model;

class Conf extends Model{

  //获取所有配置项
  public static function getAllConfig(){
    $self = new self();
    $result = $self->field('c_name as en,c_cname as zh,c_value as val,c_type as type,c_desc as note')->all();
    if($result->isEmpty()) return false;
    //将多选框的值进行处理
    foreach($result as $config){
      if($config['type'] === 'select'){
        //解析数据
        $config['note'] = json_decode($config['note']);
      }
   }
   return $result;
  }

  //更改配置项
  public static function setConfig($data){
    //先对数据进行安全过滤
    $data = safe_filter($data);

    // 如果黑名单ip最后一位是,号则清理该,号
    if(substr($data['admin_forbid_ip'],-1) === ',') $data['admin_forbid_ip'] = substr($data['admin_forbid_ip'],0,strlen($data['admin_forbid_ip']) - 1);

    //查询数据
    $self = new self(); //实例化自己的模型
    $res = $self->field('id,c_name as en')->all();
    if($res->isEmpty()) return false;
    //foreach循环遍历写入
    // var_dump($res);
    foreach($res as $config){
      $self->where('id',$config->getAttr('id'))->update(['c_value' => $data[$config->getAttr('en')]]);
    }
    return true;
  }

  //获取黑名单ip
  public static function getConfig($key){
    $self = new self();
    $res = $self->field('c_value as val')->where('c_name',$key)->find();
    //如果没有查询到数据 一般不会
    if($res === null) return false;
    return $res->getAttr('val');
  }
}