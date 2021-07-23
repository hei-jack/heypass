/**
 * 用原生js模拟封装一个自己的jquery（只实现了ajax、show、hide、on、jquery-cookie插件等部分小功能）
 * @author:hei-jack
 * GMT2021-06-13
 */
'use strict';
      (function(global) {
        //注册全局变量$
        global.$ = function(el) {
          if (el !== undefined){
            var element = document.querySelectorAll(el);
            if(element.length > 1) return element; //如果大于一个元素 直接返回
            element = document.querySelector(el);
            if(element === null) throw(new Error('Element is empty!')); //如果为null 直接给个错误
            
            element.hide = function(){
              this.style.display = 'none';
            };
            
            element.show = function(){
              this.style.display = 'block';
            };
            /*
             * @param1 string event 事件名称
             * @param2 function func  回调函数
             * 
             */
            element.on = function(event,func){
              this['on' + event] = func;
            };
            
            //移除由on方法添加的事件
            element.off = function(event){
              this['on' + event] = null;
            };
            
            //手动触发事件
            element.trigger = function(event){
              this[event]();
            };
            
            //左滑动画效果
            element.slideLeft = function(){
              this.style.display = 'block';
              this.style.position = 'absolute';
              this.style.left = '-' + this.offsetWidth + 'px';
              var n = - this.offsetWidth;
              var id = setInterval(function(){
              n = n + 5;
              this.style.left = n + 'px';
              //console.log(n);
              if(n >= 0) window.clearInterval(id);
              }.bind(this),1000/250);
            };
            
            return element; //获取元素的函数 如果传入元素直接返回元素对象
          }
          return new _$().fn.init();
        }

        //构造稳妥对象
        function _$() {
          //--------------------------------------- 模拟ajax ------------------------------------------
          //检查数据函数
          function checkData(data) {
            var end = ''; //最终返回字符串形式
            if (typeof(data) === 'object') {
              //检查对象是否为空
              var arr = Object.getOwnPropertyNames(data);
              if (arr.length === 0) throw (new Error('data is empty!'));

              //不为空处理为字符串形式
              for (var temp in data) {
                end += '&' + temp + '=' + encodeURIComponent(data[temp]); //对数据的值进行编码
              }
              end = end.slice(1); //从第一位开始截取 因为多一个&号
            } else if (typeof(data) === 'string') {
              if (data.length === 0) throw (new Error('data is empty!'));
              // console.log(data);
              //用正则简单校验一下
              var reg =
                /^([&]?[a-zA-Z_-]+[=]{1}[\u4e00-\u9fa5a-zA-Z\d,\.，。_-]+([&]{1}[a-zA-Z_-]+[=]{1}[\u4e00-\u9fa5a-zA-Z\d,\.，。_-]+)?)+$/g;
              if (!(reg.test(data))) throw (new Error('data is error!'));

              //如果发现&号说明是多个请求参数
              if(end.indexOf('&')){
                var arr = data.split('&'); //从&处打散为数组
                var len = arr.length;
                //遍历数组项进行处理
                for(var i = 0;i < len;i++){
                  var temp = arr[i].indexOf('='); //返回所在位置
                  // 对每项的=号后面的值进行编码处理 并将&号重新加回去
                  arr[i] = arr[i].substr(0,temp + 1) + encodeURIComponent(arr[i].substr(temp + 1)) + '&';
                  end += arr[i];
                }
                //去除最后一个多余的&号
                end = end.substr(0,end.length - 1);
              }else{
                //否则说明是单个请求参数
                var arr = end.split('=');
                //对请求参数的值进行编码
                arr[1] = encodeURIComponent(arr[1]);
                 //将等号重新添加回去
                end = arr[0] + '=' + arr[1];
              }
              // console.log(end);
              // throw (new Error('data is error!'));
              //去除可能存在的多余?号
              end = end.replace('?','');
            } else {
              throw (new Error('data is error!'));
            }
            return end;
          }

          //封装ajax函数
          function _ajax(options) {
            var xmlhttp; //创建ajax函数

            //兼容性处理 针对当前项目而言 其实没有多大必要
            if (window.XMLHttpRequest) {
              //  IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
              xmlhttp = new XMLHttpRequest();
            } else {
              // IE6, IE5 浏览器执行代码
              xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }

            //当 readyState 改变时触发 onreadystatechange 事件被触发 5 次（0 - 4），对应着 readyState 的每个变化。
            xmlhttp.onreadystatechange = function() {
              //当请求完成且响应状态为200
              if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                //var contentType= xmlhttp.getResponseHeader("Content-Type"); 获取返回的响应头类型
                //处理返回data格式
                try{
                  var data = checkResData(xmlhttp.getResponseHeader("Content-Type"), options.dataType, xmlhttp);
                }catch(e){
                  return _error(xmlhttp, e.message, options.error);  //解析类型失败进入失败回调
                }
                _success(data, xmlhttp.status, xmlhttp, options.success); //执行成功回调函数
              }

              if (xmlhttp.readyState === 4 && xmlhttp.status !== 200) _error(xmlhttp, xmlhttp.status, options.error); //执行失败回调函数
            }
            //是否需要缓存的字符串
            var cacheStr = options.cache ? '' : '&__cache=' + Math.random();
            options.type === 'GET' ? xmlhttp.open(options.type, options.url + '?' + options.data + cacheStr, options.async) : xmlhttp.open(options.type, options.url, options.async);
            
            //如果开启默认头部
            if(options.default_headers){
            xmlhttp.setRequestHeader("Content-type", options.contentType); //设置头部(发送信息至服务器时内容编码类型) 必须放在open和send之间
            xmlhttp.setRequestHeader("X-Requested-With", 'XMLHttpRequest');  //设置ajax请求头部 方便服务器识别请求类型
            }
            //var len = Object.getOwnPropertyNames(options.headers).length; //获取用户设置的头部长度

            //遍历添加可能存在的用户设置头部信息
            for (var header in options.headers) {
              xmlhttp.setRequestHeader(header, options.headers[header]);
            }

            options.type === 'POST' ? xmlhttp.send(options.data) : xmlhttp.send(); //发送请求 data只对post有效
          }

          //ajax成功处理函数
          function _success(data, status, xmlhttp, success) {
            //console.log(data);
            success(data, status, xmlhttp);
          }

          //ajax失败处理函数
          function _error(xmlhttp, status, error) {
            error(xmlhttp, status);
          }

          //ajax检查返回响应数据
          function checkResData(contentType, dataType, xmlhttp) {
            //console.log(contentType);
            var data;
            //如果没有设置预期返回数据类型
            if (dataType === null) {
              //直接根据返回头类型来判定
              if (contentType.indexOf('application/json') !== -1) {
                //处理json数据
                data = JSON.parse(xmlhttp.responseText); //解析json数据
              } else if (contentType.indexOf('text/plain') !== -1) {
                return xmlhttp.responseText;
              } else if (contentType.indexOf('/xml') !== -1) {
                data = xmlhttp.responseText; //获取原有数据
                return parseXML(data); //解析成xml（dom树）
              } else {
                return xmlhttp.responseText; //直接返回原来的字符串
              }
            } else {
              //假如已经设置了dataType
              switch (dataType) {
                case 'json':
                  //尝试进行解析
                  try {
                    data = JSON.parse(xmlhttp.responseText);
                    return data;
                  } catch (e) {
                    //抛出解析错误
                    throw(new Error('parsererror'));
                  }
                case 'text':
                  return xmlhttp.responseText;
                case 'xml':
                if(contentType.indexOf('/xml') === -1){
                  throw(new Error('parsererror')); //抛出解析错误
                }else{
                  return parseXML(xmlhttp.responseText);
                }
              }

            }
          }

          // 传入xml 字符串，返回DOM 树
          function parseXML(ret) {
            var xml, tmp;
            if (window.DOMParser) { // 现代浏览器
              tmp = new DOMParser();
              xml = tmp.parseFromString(ret, "text/xml");
            } else { // IE
              xml = new ActiveXObject("Microsoft.XMLDOM");
              xml.async = "false";
              xml.loadXML(ret);
            }
            return xml;
          }

          //创建要返回的对象
          //var obj = new Object();
          //ajax快捷方法 初始化参数
          this.ajax = function(options) {
            if (options === undefined) throw (new Error('ajax parameter is empty!'));
            //url参数
            options.url = options.url === undefined ? null : options.url;
            if (options.url === null) throw (new Error('ajax url is empty!'));
            //是否异步 默认为true
            options.async = options.async === undefined ? true : options.async;
            //是否缓存 针对get方法
            options.cache = options.cache === undefined ? false : options.cache;
            //请求类型 转为大写
            options.type = options.type === undefined ? 'GET' : options.type.toUpperCase();
            //发送信息至服务器时内容编码类型
            options.contentType = options.contentType === undefined ? "application/x-www-form-urlencoded;charset=utf-8" : options.contentType;
            //预期服务器返回的数据类型 转为小写
            options.dataType = options.dataType === undefined ? null : options.dataType.toLowerCase(); //暂时只打算 xml、text、json三个类型
            //如果请求数据为空 直接报错
            if (options.data === undefined) throw (new Error('data is empty!'));
            //请求数据
            options.data = checkData(options.data);
            //默认设置的header头部是否开启 Content-type和X-Requested-With
            options.default_headers = options.default_headers === undefined ? true:options.default_headers;
            //headers头部
            options.headers = typeof(options.headers) !== 'object' ? null : options.headers;
            //成功回调函数
            options.success = typeof(options.success) !== 'function' ? null : options.success;
            //失败回调函数
            options.error = typeof(options.error) !== 'function' ? null : options.error;

            //如果没有headers头部信息 就设置为空
            if (options.headers === null) options.headers = {};
            //如果没有回调函数 就准备一个空函数
            if (options.success === null) options.success = function() {};
            if (options.error === null) options.error = function() {};

            // console.log('url=> ' + options.url);
            // console.log('async=> ' + options.async);
            // console.log('type=> ' + options.type);
            // console.log('dataType=> ' + options.dataType);
            // console.log('data=> ' + options.data);
            // console.log('headers=> ' + JSON.stringify(options.headers));
            // console.log('success=> ' + options.success);
            // console.log('error=> ' + options.error);

            //将处理后的参数传入_ajax函数
            _ajax(options);
          };
          
          //--------------------------------------- 模拟ready事件 ------------------------------------------
          //dom树加载完成就开始执行 比onload早 模拟jquery ready事件
          this.ready = function(func){
          	if(document.addEventListener){
              //监听DOMContentLoaded事件
          		/* document.addEventListener('DOMContentLoaded',function(){
                console.log(this);
          			document.removeEventListener('DOMContentLoaded',arguments.callee,false); //移除 严格模式下不能使用arguments.callee
          			func();
          		},false); */
              document.addEventListener('DOMContentLoaded',function handler(){
                document.removeEventListener('DOMContentLoaded',handler,false);
              	func();
              },false);
          	}else if(document.attachEvent){		//IE浏览器
          		/* document.attachEvent('onreadystatechange',function(){
          			if(document.readyState == 'complete'){
          				document.detachEvent('onreadystatechange',arguments.callee);
          				func();		//函数运行
          			}
          		}); */
              
              document.attachEvent('onreadystatechange',function handler(){
              	if(document.readyState == 'complete'){
              		document.detachEvent('onreadystatechange',handler);
              		func();		//函数运行
              	}
              });
          	}
          };
          
          //--------------------------------------- 模拟jquery-cookie插件 ------------------------------------------
          /* 
           * cookie快捷方法
           * @param1 string name
           * @param2 string value
           * @param3 object options
           */
          this.cookie = function(name,value,options){
            if(name === undefined) return getAllCookie();//如果一个参数都没有传 直接去获取所有cookie信息
            if(value === undefined) return getCookie(name); //获取单个cookie
            if(options === undefined) return setCookie(name,value); //设置cookie 会话cookie
            return setCookie(name,value,options); //设置cookie
          };
          
          //移除cookie
          this.removeCookie = function(name,options){
            if(name === undefined) return false; //如果没有传入cookie名称 直接返回false
            return removeCookie(name,options);
          };
          
          //cookie函数
          //获取cookie值
          function getCookie(name){
            var arr = document.cookie.split(";"); //打散为数组
            var len = arr.length;
            if(arr[0].length === 0) return false; //如果没有就返回 false  判断cookie是否为空 为空返回false
              //如果传入要获取的cookie名 开始寻找
              for(var i = 0;i<len;i++){
              	var str = decodeURIComponent(arr[i].trim());  //trim() 去掉两端空白
              	if(str.indexOf(name) == 0) return str.substring(name.length + 1,str.length)  //返回截取的字符串 也就是cookie值
              }
              return false;
          }
          
          //获取所有cookie索引和值 返回对象
          function getAllCookie(){
            var arr = document.cookie.split(";"); //打散为数组
            var len = arr.length;
            if(arr[0].length === 0) return false; //如果没有就返回 false  判断cookie是否为空 为空返回false
            //如果没有传入要获取的cookie名 直接返回包含所有cookie的对象
            var result = {}; //声明一个空对象
            for(var i = 0;i < len;i++){
            	var str = arr[i].trim();  //trim() 去掉两端空白
              //截取第一个等号之前的值
              var index = arr[i].indexOf('=');
              var key = arr[i].slice(0,index).trim();
              result[key] = decodeURIComponent(arr[i].slice(index + 1).trim());  //decodeURIComponent对encodeURIComponent编码进行解码
            }
            return result;
          }
          
          //设置cookie
          function setCookie(name,value,options){
            if(options === undefined){
              options = {};
              options.exdays = 0;
              options.path = '/';
            }
            // console.log(options);
            var exdays = options.exdays === undefined ? 0:options.exdays; //默认为0 会话cookie
            var path = options.path === undefined ? '/':options.path; //默认为根目录
            var d = new Date(); //获取当前时间日期对象
            //utc领先中国8小时
            d.setTime(d.getTime() + exdays*24*60*60*1000);  //当前时间+exdays天
            
            var expires = "expires=" + d.toUTCString();  //拼接cookie过期时间参数
            if(exdays === 0) expires = ''; //如果没有传入过期时间 默认设置为会话cookie
            value = encodeURIComponent(value); //编码;/?:@&=+$,# 等字符 %
            // console.log(name + "=" + value + ";" + expires + ";path=" + path);
            window.document.cookie = name + "=" + value + ";" + expires + ";path=" + path;
          }
          
          //移除cookie
          function removeCookie(name,options){
            if(!getCookie(name)) return false; //如果没有找到对应cookie 直接返回false
            if(options === undefined){
              options = {};
              options.exdays = -1; //过期时间设置为昨天
              options.path = '/';
            }else{
              options.exdays = -1; //过期时间设置为昨天
            }
            setCookie(name,null,options); //其实就是将cookie过期时间设置为昨天
            return !getCookie(name);
          }
          
          //检测当前设备是否是移动端设备
          this.isMobile = function() {
          	return navigator.userAgent.match(/(iPhone|iPad|Android|ios)/i) ? true : false;
          };

          //将this.fn指向原型 再将原型指向一个构造函数
          this.fn = this.prototype = {
            constructor: _$,
            init: function() {
              return this;
            }
          };
          //将init方法的原型又指向fn
          this.fn.init.prototype = this.fn;
          $.ajax = this.ajax; //将$.ajax指向this.ajax其实就是指向$_.ajax
          $.isMobile = this.isMobile;
          $.ready = this.ready; //将$.ready指向this.ready其实就是指向$_.ready
          $.cookie = this.cookie;
          $.removeCookie = this.removeCookie;
          return this; //将this返回
        }
        return $(); //匿名自执行函数返回执行后的函数
      })(window);
      
      //测试模拟jquery的对象
      //console.log($.ajax());
      /* $.ajax({
        data:'url=aaa&jc=bbb',
        success:function(){
          console.log('假装成功函数');
        }
      });
      */
      /* $.ajax({
        url: '/api/',
        type: 'get',
        dataType: 'json',
        data: {
          'c': 'd',
          'encode': 'text',
        },
        headers: {
          'Access-token': '123456',
          'fuck-abc': '12355',
        },
        success: function() {
          console.log('hhhhh');
        },
        error: function(obj, status) {
          console.log(status);
          alert(obj.responseText);
        }
      }); 
      
      //cookie测试
      //完整参数设置  过期时间一天 路径/dev
      $.cookie('hello','123456 ',{exdays:1,path:'/dev'});
      //设置hello的cookie 默认会话cookie 浏览器关闭后销毁 路径为根目录
      console.log($.cookie('hello','hhh'));
      //获取所有cookie的对象
      console.log($.cookie());
      //移除hello /dev的 cookie
      console.log($.removeCookie('hello',{path:'/dev'}));
      //获取所有cookie
      console.log($.cookie());
      */