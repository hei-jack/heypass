<?php
//密码记录验证器
namespace app\common\validate;
use think\Validate;

//继承公共验证器
class Password extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'  => ['require','number'],  //不能为空 并且只能是数字
        'cid' => ['require','number'], //分类id
        'p_name' => ['require','max' => 50], //账号
        'p_pass' => ['require','max' => 50], //密码
        'p_title' => ['require','max' => 20,'chsDash'], //中文名称 只能是汉字、字母和数字，下划线_及破折号-
        'p_url' => ['max' => 50,'url', 'regex' => '/^[a-zA-Z0-9_\?\.\/:=-]+$/'], //只能是url 但是filter_var感觉不太靠谱 增加正则 只允许字母数字_?.-=通过
        'p_other' => ['max' => 50,'regex' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9,\/\._:，。\?？-]+/u'], //不能超过50个字
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
        'cid.require' => '非法请求！',
        'cid.number' => '非法请求！',
        'p_name.require' => '账号不能为空！',
        'p_name.max' => '账号长度有误！',
        'p_pass.require' => '密码不能为空！',
        'p_pass.max' => '密码长度有误！',
        'p_title.require' => '关联名称不能为空！',
        'p_title.max' => '关联名称长度有误！',
        'p_title.chsDash' => '关联名称格式有误！',
        // 'p_url.require' => '关联网址不能为空！',
        'p_url.max' => '关联网址长度有误',
        'p_url.url' => '关联网址格式有误！',
        'p_url.regex' => '关联网址格式有误！',
        'p_other.max' => '备注长度有误',
        'p_other.regex' => '备注格式有误！',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        'add'  =>  ['cid','p_name','p_pass','p_title','p_url','p_other','__token__'], //新增场景
        'edit' => ['id','cid','p_name','p_pass','p_title','p_url','p_other','__token__'], //编辑场景
    ];
}