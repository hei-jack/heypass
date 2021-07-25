'use strict';
$.ready(function () {

  //滑动验证
  var no_captcha = new NoCaptcha();
  var success = false; //标记位
  var flag = false;
  //先销毁滑动验证
  no_captcha.destroy();

  //点击关闭按钮时隐藏登录面板
  $('.close').on('click', function () {
    $('.login-form').hide();
    $('h1').hide();
  });

  //当账号输入框发生键盘弹起事件时
  $('[name="username"]').on('keyup', function () {
    //正则表达式验证 禁止包含除字母数字之外的数字 暂时不验证长度
    var reg = /[^A-Za-z0-9]+/g;
    this.style.cssText = reg.test(this.value) ? 'color:#FA5858;' : 'color:#81F79F;';
  });

  //当密码输入框发生键盘弹起事件时
  $('[name="password"]').on('keyup', function () {
    //正则表达式验证 禁止包含除字母数字之外的数字 暂时不验证长度
    var reg = /[^A-Za-z0-9#@_,-]+/g;
    this.style.cssText = reg.test(this.value) ? 'color:#FA5858;' : 'color:#81F79F;';
  });

  //登陆校验 当登录按钮发生点击事件时
  $('[type="submit"]').on('click', function () {
    if (!success && flag) {
      //如果开启了滑动验证 则校验滑动验证是否通过
      showTopMessage('请先进行滑动验证', false, 1500);
      return false;
    }
    //账号密码
    var username = $('[name="username"]').value;
    var password = $('[name="password"]').value;
    if (username.length === 0 || password.length === 0) {
      showTopMessage('请输入账号密码', false, 1500);
      return false;
    }

    $.cookie('heypass_token', dataHelper('HeyPass' + Math.random()));
    login(username, password);
  });

  //监听document的keyup事件
  window.addEventListener('keyup',function(eve){
    var event = eve || window.event;
    var code;

    event.preventDefault(); //阻止默认动作

    //兼容性处理
  if (event.key !== undefined) {
    code = event.key; //现代浏览器
  } else if (event.keyIdentifier !== undefined) {
    code = event.keyIdentifier; //safari
  } else if (event.keyCode !== undefined) {
    code = event.keyCode; //旧浏览器
  }else{
    code = '';
  }

  code = String(code).toUpperCase(); //转为字符串 并且转为英文大写

    if (code === 'ENTER' || code === 13)  $('[type="submit"]').trigger('click');//手动触发提交按钮点击事件
});

  //ajax登录方法
  function login(username, password) {
    //校验通过发送ajax请求
    $.ajax({
      url: getPathName(),
      type: 'post',
      dataType: 'json',
      data: {
        'username': dataHelper(username),
        'password': dataHelper(password),
      },
      headers: {
        'Access-token': dataHelper($('[name="token"]').value),
        'Access-token2': dataHelper(window.location.host), //伪装 发送域名
      },
      success: function (res, code, xmlhttp) {
        if (res.status === 200) {
          showTopMessage('登录成功，正在跳转，请稍候~', true, 1500);
          //1.5秒后开始进行跳转操作
          setTimeout(function () {
            window.location.href = res.data;
          }, 1500);
          return;
        }
        //刷新token
        refreshToken();
        showTopMessage(res.mess + ',' + res.data, false, 1500);
        //否则开启滑动验证
        if (!flag) {
          no_captcha.resize(); //重新创建滑块验证
          no_captcha.init(function () {
            success = true;
          }); //重新初始化滑块
          flag = true;
          return;
        }
        no_captcha.resize(function () {
          success = true;
        }); //将滑块复位
        success = false;
      },
      error: function (obj, status) {
        console.log(status);
        showTopMessage('请检查网络情况或稍后再试', false, 1500);
      }
    });
  }
});