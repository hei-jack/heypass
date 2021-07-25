<?php
//分页验证器
namespace app\common\validate;
use think\Validate;

class Paging extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id' => ['require','number'],
        'limit'  => ['require','number'],  //不能为空 并且只能是数字
        'page'  => ['require','number'],  //不能为空 并且只能是数字
        '__token__' => ['require','max' => 33,'token'],
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'limit.require' => '非法请求！',
        'limit.number' => '非法请求！',
        'page.require' => '非法请求！',
        'page.number' => '非法请求！',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        'idt'  =>  ['id','__token__'], //验证场景id场景
        'default' => ['limit','page','__token__'], //常用场景
        'id' => ['id'], //只有数字一个
        'ilpt' => ['id','limit','page','__token__'], //ilpt场景 四个都要
    ];
}
