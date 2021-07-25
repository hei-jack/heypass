<?php
//用户验证器
namespace app\common\validate;
use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'  => ['require','number'],
        'username'  => ['require','regex' => '/^[a-zA-Z]{1}[a-zA-Z0-9_]{4,19}$/'],  //不能为空 正则表达式数字、字母、下划线_ 8到20位 首位必须为字母
        'password'  => ['require','regex' => '/^[a-zA-Z]{1}([a-zA-Z0-9]|[-@#_,]){4,19}$/'],  //不能为空 正则表达式数字、字母、下划线  ,-#@ 10到20位 首位必须为字母
        'new_password' => ['require','regex' => '/^[a-zA-Z]{1}([a-zA-Z0-9]|[-@#_,]){4,19}$/'],
        'twoAuth' => ['require','regex' => '/^[\x{4e00}-\x{9fa5}，。]+$/u'], //不能为空只允许中文汉字及中文逗号和句号通过
        'level'   => ['require','length' => '1','number'],   //级别不能为空 必须是1位纯数字
        'state'  => ['require','length' => '1','number'],
        'nickname'  => ['require','max' => 7,'chsDash'],  //不能为空 正则表达式汉字、数字、字母、下划线_和破折号 最多7位
        'email'  => ['require','email'],  //不能为空 邮箱
        '__token__' => ['require','max' => 33,'token'],
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'id.require' => '非法请求！',
        'id.number' => '非法请求！',
        'username.require' => '用户名不能为空！',
        'username.regex' => '用户名格式有误！',
        'password.require' => '密码不能为空！',
        'password.regex' => '密码格式有误！',
        'twoAuth.require' => '安全密码不能为空！',
        'twoAuth.regex' => '安全密码格式有误！',
        'level.require' => '请选择用户级别！',
        'level.length' => '非法请求！',
        'level.number' => '非法请求！',
        'state.require' => '请选择用户状态！',
        'state.length' => '非法请求！',
        'state.number' => '非法请求！',
        'nickname.require' => '昵称不能为空！',
        'nickname.length' => '昵称长度有误！',
        'nickname.chsDash' => '昵称格式有误！',
        'email.require' => '邮箱不能为空！',
        'email.email' => '邮箱格式有误！',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        'login'  =>  ['username','password','__token__'], //登录验证场景
        'two_auth'  =>  ['twoAuth', '__token__'], //安全验证（二次验证）场景
        'mypwd' => ['password', 'new_password', '__token__'],  //修改个人密码
        'add' => ['username','password','nickname','__token__'], //新增亲友
        'edit' => ['id','username','nickname','state'], //编辑亲友
    ];
}
