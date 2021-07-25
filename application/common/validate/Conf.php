<?php
//配置验证器
namespace app\common\validate;
use think\Validate;

//继承公共验证器
class Conf extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'web_site_name' => ['require','regex' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9,\/\._:，。-]+/u'],
        'web_site_url' => ['require','url'], //不能为空 并且是有效的url
        'web_site_key' => ['require','regex' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9,\/\._:，。-]+/u'],
        'web_site_desc' => ['require','regex' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9,\/\._:，。-]+/u'],
        'web_site_theme' => ['require','alphaDash'], //只允许数字字母下划线和破折号
        'web_index_theme' => ['require','alphaDash'],
        'web_index_state' => ['require','alpha'], //只允许纯字母
        'admin_forbid_ip' => ['require','regex' => '/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|\*)\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|\*)\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|\*)\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|\*)([,])?)+$/'],
        '__token__' => ['require','max' => 33,'token'],
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'web_site_name.require' => '站点标题不能为空！',
        'web_site_name.regex' => '站点标题格式有误！',
        'web_site_url.require' => '站点域名不能为空！',
        'web_site_url.url' => '站点域名格式有误！',
        'web_site_key.require' => '站点关键词不能为空！',
        'web_site_key.regex' => '站点关键词格式有误！',
        'web_site_desc.require' => '站点描述不能为空！',
        'web_site_desc.regex' => '站点描述格式有误！',
        'web_site_theme.require' => '后台主题不能为空！',
        'web_site_theme.alphaDash' => '后台主题格式有误！',
        'web_index_theme.require' => '前台模板不能为空',
        'web_index_theme.alphaDash' => '前台模板格式有误！',
        'web_index_state.require' => '非法请求',
        'web_index_state.alpha' => '非法请求',
        'admin_forbid_ip.require' => '黑名单ip不能为空',
        'admin_forbid_ip.regex' => '黑名单ip格式有误',
        '__token__.max' => '非法请求！',
        '__token__.require' => '非法请求！',
        '__token__.token' => '页面过期，请刷新重试！',
    ];

    //验证场景
    protected $scene = [
        // 'get_log'  =>  ['limit','page','__token__'], //登录验证场景
        // 'default' => ['limit','__token__'], //常用场景
    ];
}
