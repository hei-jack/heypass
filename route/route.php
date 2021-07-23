<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//刷新token路由
Route::post('refresh/token',function(){
    Request::token();  //需要客户端请求表明ajax才会将token放入响应头
    return md5(time());  //返回当前时间戳md5混淆视听
});

//后台路由
Route::group('admin',function(){
    Route::rule('login','admin/Index/login','GET|POST');
    Route::rule('index','admin/Index/index','GET|POST');
    Route::rule('main','admin/Index/main','GET|POST');
    Route::rule('help','admin/Index/help','GET|POST');
    Route::rule('twoauth','admin/Index/twoAuth','GET|POST');
    Route::rule('settwoauth','admin/Index/setTwoAuth','GET|POST');
    Route::get('logout','admin/Index/logout');
    Route::rule('mypwd','admin/Index/myPwd','GET|POST');
    Route::rule('getlog','admin/Index/getLog','GET|POST');
    Route::rule('dellog','admin/Index/delLog','GET|POST');
    Route::rule('setting','admin/Conf/setting','GET|POST');
    //用户
    Route::rule('user_list','admin/User/index','GET|POST');
    Route::rule('user_add','admin/User/add','GET|POST');
    Route::rule('user_del','admin/User/del','GET|POST');
    Route::rule('user_edit','admin/User/edit','GET|POST');
    //分类
    Route::rule('cat_list','admin/Category/index','GET|POST');
    Route::rule('cat_add','admin/Category/add','GET|POST');
    Route::rule('cat_del','admin/Category/del','GET|POST');
    Route::rule('cat_edit','admin/Category/edit','GET|POST');
    //密码
    Route::rule('pwd_list','admin/Password/index','GET|POST');
    Route::rule('pwd_add','admin/Password/add','GET|POST');
    Route::rule('pwd_del','admin/Password/del','GET|POST');
    Route::rule('pwd_edit','admin/Password/edit','GET|POST');
    Route::rule('rand','admin/Password/rand','GET|POST');
    //备忘
    Route::rule('memo$','admin/Memo/memo','GET|POST');
    Route::rule('memo_list','admin/Memo/index','GET|POST');
    Route::rule('memo_add','admin/Memo/add','GET|POST');
    Route::rule('memo_del','admin/Memo/del','GET|POST');
    Route::rule('memo_edit','admin/Memo/edit','GET|POST');
    //日记
    Route::rule('diary$','admin/Diary/diary','GET|POST');
    Route::rule('diary_list','admin/Diary/index','GET|POST');
    Route::rule('diary_add','admin/Diary/add','GET|POST');
    Route::rule('diary_del','admin/Diary/del','GET|POST');
    Route::rule('diary_edit','admin/Diary/edit','GET|POST');
  }
);

return [

];
