<?php
// 前台基类控制器
namespace app\common\controller;
use app\common\controller\Base;
use app\common\model\Conf;

class HomeBase extends Base{
    //初始化方法
    protected function initialize(){
      //获取前台是否允许访问
      $state = Conf::getConfig('web_index_state');
      if($state !== 'on'){
        echo '<h1 style="padding-top:100px;text-align:center;color:red;">站点正在维护中，敬请期待~</h1>';
        die; //直接强制退出运行
      }

      //分配配置
      //站点名称
      $config['site_title'] = Conf::getConfig('web_site_name');
      //站点关键字
      $config['site_key'] = Conf::getConfig('web_site_key');
      //站点描述
      $config['site_desc'] = Conf::getConfig('web_site_desc');
      //分配给模板
      $this->assign('config',$config);
    }
}