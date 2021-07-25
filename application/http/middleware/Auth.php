<?php
//权限验证中间件 弃用
namespace app\http\middleware;

class Auth
{
    public function handle($request, \Closure $next){

        $module = $request->module(); //获取当前模块名称
        $con = $request->controller(); //获取当前控制器名称
        $action = $request->action();  //获取当前方法名称

        $rules = $module. '/' . $con . '/' . $action;

        //无需鉴权的页面
        $notCheck = array(
        'index/Index/index', //前台主页
        'admin/Index/login' //后台登录界面
        );

        //var_dump($request->isGet());

        $isLogin = session('uid','','admin') === null;  //是否登录

        // 如果用户还没有进行登录操作
        if($isLogin){
          //用户访问的是无需鉴权的开放页面
          if(in_array($rules,$notCheck ,true)) return $next($request);
          //用户访问的是需要鉴权的页面
          return redirect('admin/Index/login'); //重定向到登录界面
        }else{
          //如果用户已经登录
          // 从admin作用域读取session 如果不是超级管理员 就需要进行权限验证
          if(session('uid','','admin') !== 1){
            $check = array(
              'admin/Pass/category',  //密码分类
              'admin/Pass/add_category',  //新增密码分类
              'admin/User/add_user',  //新增用户
              'admin/User/user_list',  //用户列表
              'admin/conf/setting',  //系统设置
            );

            //如果当前访问路径受限      
            if(in_array($rules,$check ,true)) return redirect('admin/Index/login'); //重定向到登录界面
          }
        }


        //二次密码验证
        if(session('uid','','admin')){
          echo '假装二次验证';
        }

        return $next($request);
    }
}
