<?php
// 后台基类控制器
namespace app\common\controller;
use app\common\controller\Base;
// 引入用户日志模型
use app\common\model\UserLog;

class AdminBase extends Base{

  //无需登录也无需鉴权的方法
  protected $notCheck = 'admin/Index/login'; //后台登录界面 只有一个页面 没必要用数组

  //需要鉴权的方法 暂时不多 先写死 后期有需要从数据库读取
  protected $check = array(
    'admin/Category/index',  //密码分类
    'admin/Category/add',  //新增密码分类
    'admin/Category/edit',  //编辑密码分类
    'admin/Category/del',  //删除密码分类
    'admin/User/index',  //用户列表
    'admin/User/add',  //新增用户
    'admin/User/edit',  //编辑用户
    'admin/User/del',  //删除用户
    'admin/Index/getlog', //获取日志 方法不能大写 否则不能拦截
    'admin/Index/dellog', //清空日志
    'admin/Conf/setting',  //系统设置
  );

  //接口返回值
  protected $res = [
    'status' => 403,
    'data' => '请刷新页面或稍候再试',
    'mess' => '非法请求',
  ];

  //初始化方法 相当于 原生__construct
  protected function initialize(){
    $module = $this->request->module(); //获取当前模块名称
    $con = $this->request->controller(); //获取当前控制器名称
    $action = $this->request->action();  //获取当前方法名称
    $rules = $module . '/' . $con . '/' . $action;
    $ip = $this->request->ip(); //获取当前访问ip

    // var_dump($rules);

    $Blocklist = \app\common\model\Conf::getConfig('admin_forbid_ip'); //从数据库读取禁止ip名单字符串
    if($Blocklist === false) exception('系统发生错误，请联系管理员！');

    //先判断是否是无需登录和鉴权的方法
    if ($rules !== $this->notCheck) {
      // 如果不是 则需要进行判断用户是否登录
      if(!$this->isLogin()) $this->error('请先登录！','admin/Index/login'); //跳转登录页面

      //检查用户是否已经被禁用 如果被禁用直接清除session和cookie 跳转登录界面
      if(!$this->isDisable()){
        //记录到日志
        UserLog::addLog(session('username','','admin'),'访问网站',2,'被禁用亲友试图继续访问网站！',$ip);
        //清空cookie和session（admin作用域）
        $this->clear();
        $this->error('当前账号已被禁用，请联系管理员！','admin/Index/login');
      }

      // 用户已经登录 则先进行ip拦截设置
      // 检查IP地址访问
      $arr = explode(',', $Blocklist);
      foreach ($arr as $val) {
        //是否是IP段
        if (strpos($val, '*')) {
          if (strpos($ip, str_replace('.*', '', $val)) !== false){
            //记录到日志
            UserLog::addLog(session('username','','admin'),'访问网站',2,'被禁用ip段试图访问网站！',$ip);
            //清空cookie和session（admin作用域）
            $this->clear();
            $this->error('403:您的IP属于IP禁止段内,禁止访问！');
          }
        } else {
          //不是IP段,用绝对匹配
          if ($ip === $val){
            //记录到日志
            UserLog::addLog(session('username','','admin'),'访问网站',2,'被禁用ip试图访问网站！',$ip);
            //清空cookie和session（admin作用域）
            $this->clear();
            $this->error('403:您的IP地址已被禁止访问！');
          }
        }
      }

      //验证cookie签名  防止cookie和session伪造
      if(!$this->checkSign()){
        //记录到日志
        UserLog::addLog(session('username','','admin'),'风控机制',2,'触发安全风控，被要求重新登录！' . $rules,$ip);
        $this->clear();
        $this->error('触发安全风控，请重新登录！','admin/Index/login');
      }

      // var_dump($rules);
      // var_dump(url('admin/Index/twoAuth'));
      // 检查是否需要进行双重验证 如果用户还未进行双重验证的话
      if(!$this->isTwoStepAuth() && strpos($rules,'twoauth') === false){
        //实例化用户解密模型
        $enc = new \app\common\model\UserEnc();
        //如果已经设置了安全密码 则跳转到二次验证处 否则跳转设置二次密码
        $enc->getEnc(session('uid','','admin')) === null ? $this->redirect('admin/Index/setTwoAuth'):$this->redirect('admin/Index/twoAuth'); //如果用户还未进行验证双重验证功能 则重定向到双重验证页面
      }

      // 检查当前用户是否为超级管理员
      if(!$this->isAdmin()){
        //如果当前用户不是超级管理员 则需要进行权限校验
        if(in_array($rules,$this->check,true)){
          UserLog::addLog(session('username','','admin'),'试图越权',2,'用户没有权限浏览该页面！'. $rules,$ip);
          $this->error('403:权限不足，禁止访问！','admin/Index/main');//如果当前访问地址属于需要鉴权的方法 则返回提示 跳转到真主页
        }
      }
    }
  }

  
  //检查当前登录用户是否被禁用
  final protected function isDisable(){
    $model = new \app\common\model\User();
    //查询用户状态
    return $model->getState();
  }

  // 检查是否登录
  final protected function isLogin(){
    //如果admin作用域下的session中没有uid 说明用户还未登录
    if (session('uid', '', 'admin') === null) return false;
    return true;
  }

  // 检查是否是超级管理员
  final protected function isAdmin(){
    if(session('uid', '', 'admin') === 1) return true;
    return false;
  }

  // 检查是否已经通过二次验证
  final protected function isTwoStepAuth(){
    if(session('twoAuth', '', 'admin') === null) return false;
    return true;
  }

  //检查sign
  final protected function checkSign(){
    $model = new \app\common\model\User();
    return $model->checkSign();
  }

  //清空cookie和session
  final protected function clear(){
    //清空cookie
    cookie(null,'heypass_');
    //清空session admin作用域
    session(null, 'admin');
  }

  // 操作错误跳转方法
  final public function error($msg = '', $url = null, $data = '', $wait = 3, array $header = []){
    // 记入操作日志 模型实例化写入
    if($this->request->isGet()) parent::error($msg, $url, $data, $wait, $header);  //如果是get请求直接跳转
    //否则返回json数据
    header('Content-type: application/json;charset=UTF-8'); //设置响应头
    echo json_encode(['mess' => $msg,'status' => 0]);
    die;
  }

  // 操作错误跳转方法
  final public function success($msg = '', $url = null, $data = '', $wait = 3, array $header = []){
    // 记入操作日志 模型实例化写入
    if($this->request->isGet()) parent::success($msg, $url, $data, $wait, $header);
    //否则返回json数据
    header('Content-type: application/json;charset=UTF-8'); //设置响应头
    echo json(['mess' => $msg,'status' => 1]);
    die;
  }
}
