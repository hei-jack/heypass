<?php
//分类验证器
namespace app\common\validate;
use think\Validate;

class Category extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'  => ['require','number'],  //不能为空 并且只能是数字
        'c_en' => ['require','max' => 20,'alphaDash'], //英文名称 字母和数字，下划线_及破折号-
        'c_zh' => ['require','max' => 20,'chs'], //中文名称 只能是汉字
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
        'c_en.require' => '分类标识不能为空！',
        'c_en.max' => '分类标识长度有误',
        'c_en.alphaDash' => '分类标识格式有误！',
        'c_zh.require' => '分类名称不能为空！',
        'c_zh.max' => '分类名称长度有误',
        'c_zh.chs' => '分类名称格式有误！',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        'add'  =>  ['c_en','c_zh','__token__'], //新增场景
        'edit' => ['id','c_en','c_zh','__token__'], //常用场景
    ];
}