'use strict';
//刷新token
function refreshToken() {
  //获取时间戳
  var timestamp = String(new Date().getTime());
  //开始进行反转伪装
  timestamp = timestamp.split('').reverse().join('');
  $.ajax({
    url: '/refresh/token',
    type: 'post',
    dataType: 'text',
    data: {
      'token': timestamp,
    },
    success: function (data, code, xmlhttp) {
      $('[name="sign"]').setAttribute('data-sign', data.replace(/["]+/ig, ''));
      $('[name="token"]').setAttribute('data-token', data.replace(/["]+/ig, ''));
      $('[name="token"]').setAttribute('value', xmlhttp.getResponseHeader('__token__'));
    },
    error: function (obj, status) {
      showTopMessage('请检查网络情况或稍后再试', false, 1500);
    }
  });
}

//aes-256-cbc解密数据 @param string data 要解密的数据
function dataHelperAES(data) {
  var key = CryptoJS.enc.Utf8.parse($('#token2').value); //key
  var iv = CryptoJS.enc.Utf8.parse($('#sign2').value); //iv
  //cbc解密
  var dec = CryptoJS.AES.decrypt(data, key, {
    iv: iv,
    mode: CryptoJS.mode.CBC,
    padding:CryptoJS.pad.Pkcs7
  });
  return dec.toString(CryptoJS.enc.Utf8);
}

//aes-256-cbc加密数据 @param string data 要加密的数据
function dataHelperCBC(data){
  var key = CryptoJS.enc.Utf8.parse($('#token2').value); //key
  var iv = CryptoJS.enc.Utf8.parse($('#sign2').value); //iv
  //cbc加密
  var enc = CryptoJS.AES.encrypt(data, key, {
    iv: iv,
    mode: CryptoJS.mode.CBC,
    padding:CryptoJS.pad.Pkcs7
  });
  return enc; //返回加密后的文本
}

//RSA加密数据 @param string data 要加密的数据
function dataHelper(data) {
  //实例化JSEncrypt
  var encrypt = new JSEncrypt();
  //处理公钥 因为没有换行和开始结束的字符
  var key = formatKey($('[name="sign"]').value);
  //设置公钥
  encrypt.setPublicKey(key);
  return encrypt.encrypt(data);
}


//处理公钥格式函数 @param string key 传入的公钥
function formatKey(key) {
  var start = '-----BEGIN PUBLIC KEY-----';
  var end = '\n-----END PUBLIC KEY-----\n';
  var len = key.length;
  var str = '';
  for (var i = 0; i < len; i++) {
    //每次到达第64位 就加入一个换行符 \n
    if (i % 64 === 0) {
      str += '\n';
    }
    str += key.charAt(i);
  }
  return start + str + end; //返回处理的结果
}


/*
 * 头部提示消息
 * @param string message 要展示的消息
 * @param bool status 状态 true成功/false失败
 * @param number time 销毁时间 单位毫秒
 */
function showTopMessage(message, status, time) {
  if (document.getElementsByClassName('message-top')[0]) return false;
  var div = document.createElement('div');
  var bg = status ? 'bg-success' : 'bg-danger';
  div.setAttribute('class', 'message-top ' + bg);
  var icon = status ? '<i class="mdi mdi-checkbox-marked-circle mdi-24px"></i>' : '<i class="mdi mdi-close-circle mdi-24px"></i>';
  div.innerHTML = icon + message;
  document.body.appendChild(div);
  //自动销毁
  var timer = setTimeout(function () {
    document.body.removeChild(div);
    clearTimeout(timer);
  }, time);
}

//获取网址路径
function getPathName(){
  var url = window.location.href;
  //先去除协议头
  var arr = url.split("//");
  var start = arr[1].indexOf("/");
  var rel = arr[1].substring(start);
  //搜索是否含有参数
  if (rel.indexOf("?") !== -1) rel = rel.split("?")[0];
  return rel.replace('.html','');
}

//获取url中的参数 @param str name 参数名
function getUrlParam(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
  var r = window.location.search.substr(1).match(reg);  //匹配目标参数
  if (r != null) return unescape(r[2]);
  return null; //返回参数值
}

/*
 * 时间戳转客户端时间
 * @param string format 返回格式 YYYY-MM-DD HH:mm:ss 可自定义格式传入  年份如果传入两位YY表示返回年份最后两个尾数 除了秒数 其他都不区分大小写
 * @param mixed timestamp 时间戳字符串或数字 秒级别s 如果不传 默认为获取当前时间
 * @return string 格式化之后的日期字符串
 */
 function getFormatDate(format,timestamp){
  //因为要考虑兼容ie9 所有不使用ES6语法 所以要初始化参数
  var target = timestamp === undefined ? new Date():new Date(Number(timestamp) * 1000);
  //或取年月日时分秒 并对年月日时分秒进行补0操作
  var year = target.getFullYear().toString(); //年
  var month = target.getMonth() + 1; //月
  month = month < 10 ? '0' + month:String(month);
  var date = target.getDate() < 10 ? '0' + target.getDate():String(target.getDate()); //日
  var hours = target.getHours() < 10 ? '0' + target.getHours():String(target.getHours()); //时 24小时制
  var minutes = target.getMinutes() < 10 ? '0' + target.getMinutes():String(target.getMinutes()); //分
  var seconds = target.getSeconds() < 10 ? '0' + target.getSeconds():String(target.getSeconds()); //秒
  // console.log(year + '-' + month + '-' + date + ' ' + hours + ':' + minutes + ':' + seconds);
  //根据传入的格式化字符串替换
  //判断年份有几位 只有两位就只给后面两位
  var year_len = format.replace(/[^Y]/g,'').length;
  year = year_len === 2 ? year.substr(2):year;
  return format.replace(/[y]+/ig,year).replace(/[M]+/g,month).replace(/[d]+/ig,date).replace(/[h]+/ig,hours).replace(/[m]/g,minutes).replace(/[s]+/ig,seconds);
}

//html实体转字符 谨慎使用
function entityToString(entity) {
  var div = document.createElement('div');
  div.innerHTML = entity;
  var res = div.innerText || div.textContent;
  return res;
}