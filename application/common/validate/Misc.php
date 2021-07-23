<?php
//杂项验证器
namespace app\common\validate;
use think\Validate;

//继承公共验证器
class Misc extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id' => ['require','number'],
        'cid' => ['require','number'],
        'date' => ['require','number','length' => 8], //8位日期字符串 只能是存数字
        'year' => ['require','number','length' => 4], //4位年
        'month' => ['require','number','length' => '1,2'], //1到2位月份
        'title'  => ['require','max' => 50],
        'content'  => ['require','max' => 100000,],
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
        'date.require' => '非法请求！',
        'date.number' => '非法请求！',
        'date.length' => '非法请求！',
        'cid.require' => '非法请求！',
        'cid.number' => '非法请求！',
        'title.require' => '标题不能为空！',
        'title.max' => '标题长度不能超过50字！',
        'content.require' => '内容不能为空！',
        'content.max' => '内容长度超限！',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        'add'  =>  ['cid','title','content','__token__'], //验证场景id场景
        'memo_edit' => ['id','cid','title','content','__token__'],
        'tag' => ['id','date','__token__'], //日记标签操作
        'diary_list' => ['year','month','__token__'], //日记列表
        'diary_add' => ['date','content','__token__'], //添加日记
        'diary_edit' => ['id','date','content','__token__'],//编辑日记
    ];
}
