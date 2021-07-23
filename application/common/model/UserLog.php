<?php
//后台用户日志模型
namespace app\common\model;
use think\Model;

class UserLog extends Model{

  //添加用户登陆日志
  /*
   * @param1 str $user 用户名
   * @param2 str $name 操作名称
   * @param3 int $state 操作状态
   * @param4 str $desc 操作描述
   * @param5 str $ip ip
   */
  public static function addLog($user,$name,$state,$desc,$ip){
    self::create([
        'l_user' => $user,
        'l_name' => $name,
        'l_state' => $state,
        'l_desc' => $desc,
        'l_ip' => $ip,
        'l_time' => time(),  //写入当前时间戳
    ],true);
  }

  /*
   * 获取用户登陆日志(倒序查询)
   * @param1 int $page 页码
   * @param2 int $limit 数据量(返回多少条数据)
   *
   */
  public static function getLog($page,$limit){
      $self = new self();
      //字段别名干扰别有用心的人
      return $self->field('id,l_user as get_user,l_name as get_name,l_state as state,l_desc as get_desc,l_ip as ip,l_time as get_time')->order('id', 'desc')->page($page, $limit)->select(); //返回分页数据
  }

  //获取日志数量
  public static function getTotal(){
      $self = new self(); //实例化自己的模型
      return $self->count();
  }

  //清空日志
  public static function clearLog($ip){
    //执行清空表格的sql语句 返回false说明执行失败
    if(self::execute('truncate table ' . config('database.prefix') . 'user_log') === false) return false;
    //执行成功之后插入当前操作者信息 记录当前操作
    self::addLog(session('username','','admin'),'清空日志',2,'清空日志成功！',$ip);
    return true;
  }
}