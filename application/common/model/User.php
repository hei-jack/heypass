<?php
//用户模型
namespace app\common\model;
use think\Model;
use app\admin\logic\User as UserLogic;

class User extends Model{

  /*
   * 用户登录方法
   * 
   * @param string $username 用户名
   * @param string $password 密码
   * @param string $ip 当前登录ip
   * 
   */
  public function login($username,$password,$ip){
    //先查询用户名是否存在
    $user = $this->where('u_name',$username)->find();
    if($user === null){
      //记录到日志
      UserLog::addLog($username,'登录',0,'用户名不存在！',$ip);
      return false;
    }

    //不为空说明查询到结果 则继续验证密码
    //如果密码验证失败 返回false
    if(!UserLogic::passHash($password,$user->getAttr('u_pass'))){
      //记录到日志
      UserLog::addLog($username,'登录',0,'密码错误！',$ip);
      return false;
    }

    //获取当前时间戳
    $time = time();

    //将ip和登录时间、创建时间、用户名存入cookie方便校验
    $sign = UserLogic::cookieSign($time . $ip . $user->getAttr('create_time') . $username,config('app.cookie_key'));

    //更新登录信息 数据库方法
    $this->where('u_name',$username)->update(['u_ip' => $ip,'login_time' => $time]);

    //设置cookie
    cookie('heypass_sign',$sign);
    //清除cookie中token的值
    cookie('heypass_token',null);
    //将昵称存入cookie
    cookie('heypass_nickname',$user->getAttr('u_nickname'));
    //存入校验cookie 用户登录时间
    // cookie('sign',);
    //校验通过开始存入session
    session('uid', $user->getAttr('id'), 'admin'); //admin作用域
    //将用户名也存入session
    session('username',$username,'admin');
    
    //记录到日志
    UserLog::addLog($username,'登录',1,'登录成功',$ip);

    return true; //返回登录成功
  }

  //获取用户状态
  public function getState(){
    $res = $this->field('u_state')->where('id',session('uid','','admin'))->find();
    if($res === null) return false; //如果查询为空 直接返回false
    return intval($res->getAttr('u_state')) === 1;
  }

  //检查cookie的sign
  public function checkSign(){
    //从cookie中取出值
    $cookie = cookie('heypass_sign');
    //查询用户信息
    //先查询用户是否存在
    $user = $this->where('id',session('uid','','admin'))->find();
    if($user === null) return false;
    //将数据库中的信息拿出来加密后对比
    $sign = UserLogic::cookieSign($user->getAttr('login_time') . $user->getAttr('u_ip') . $user->getAttr('create_time') . $user->getAttr('u_name'),config('app.cookie_key'));
    return $sign === $cookie;
  }

  //修改自己的密码 @param string $newpwd 新密码
  /*
   * @param string $oldpwd  旧密码明文
   * @param string $newpwd 新密码明文
   * @param string $ip ip
   * @return bool true修改成功/false修改失败
   */
  public function changeMyPwd($oldpwd,$newpwd){
    $res = $this->field('u_pass')->where('id',session('uid','','admin'))->find();
    //如果没有查询到数据 一般不会
    if($res === null) return false;
    //先对比旧密码是否正确
    if(!UserLogic::passHash($oldpwd,$res->getAttr('u_pass'))) return false;
    //更新新密码
    $this->where('id',session('uid','','admin'))->update(['u_pass' => UserLogic::passHash($newpwd)]);
    //返回true
    return true;
  }

  public function getUserList($page,$limit){
    //获取用户数据列表
    $list = $this->field('id,u_name as username,u_state as state,u_level as level,u_ip as ip,u_nickname as nickname,login_time as login,create_time')->page($page, $limit)->select();
    //遍历数据 对昵称进行过滤处理
    foreach($list as $user){
      $user['nickname'] = htmlentities($user['nickname'],ENT_QUOTES);
    }
    return $list;
  }

  //获取用户总数
  public function getTotal(){
    return $this->count();
  }

  //新增用户 @param array $data  包含username,password,nickname
  public function add($data,$ip){
    //先对数据进行安全过滤
    $data = safe_filter($data);
    $time = time();
    $data['u_name'] = $data['username'];
    $data['u_pass'] = UserLogic::passHash($data['password']); //加密密码
    $data['u_nickname'] = $data['nickname'];
    $data['u_level'] = 1; //亲友
    $data['login_time'] = $time;
    $data['create_time'] = $time;
    try{
     //开始进行数据入库
     if($this->strict(false)->insert($data) !== 1) return false;
    }catch(\Exception $e){
      //唯一键冲突
      return false;
    }
    
    //记录到日志
    UserLog::addLog(session('username','','admin'),'新增亲友',1,'新增亲友成功:' . $data['username'],$ip);
    return true;
  }

  //删除用户 硬删除
  /*
   * @param int $id 要删除的用户id
   * @param string $ip 操作者ip
   */
  public function delUser($id,$ip){
    if($id === 1) return false; //禁止删除管理员
    $this->destroy($id);
    //记录到日志
    UserLog::addLog(session('username','','admin'),'删除亲友',1,'删除亲友成功',$ip);
    return true;
  }

  //查询用户 根据id
  public function getUserInfo($id){
    return $this->field('u_name as username,u_nickname as nickname,u_state as state')->where('id',$id)->find();
  }

  //更新用户信息
  /*
   * @param array $data 表单数据 id,username,nickname,state
   * @param string $ip 操作者ip
   */
  public function edit($data,$ip){
    //进行安全过滤
    $data = safe_filter($data);
    //将参数类型转换
    $data['id'] = (int)$data['id'];
    $data['state'] = (int)$data['state'];
    //如果是要将管理员禁用 直接返回 不允许管理员将自己禁用自己
    if($data['id'] === 1 && $data['state'] === 0) return false;
    $update = $this->where('id',$data['id'])->update([
      'u_nickname' => $data['nickname'],
      'u_state' => $data['state']
    ]);
    //更新失败
    if($update === 0) return false;
    if($data['state'] === 0) UserLog::addLog(session('username','','admin'),'禁用亲友',1,'禁用成功',$ip);
    return true;
  }
}