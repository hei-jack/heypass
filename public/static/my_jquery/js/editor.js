//为my.jquery拓展一个editor插件 编辑器插件
"use strict";
if (window.$ === undefined) throw new Error('my jqeury is not found!');
if (window.showdown === undefined) throw new Error('showdown.js is not found!');
if (window.videojs === undefined) throw new Error('video.js is not found!');
if (window.autosize === undefined) throw new Error('autosize.js is not found!');
$.editor = function (options) {
  //声明一个函数 构造对象
  function MyEditor(namespace) {
    //如果没有传入命名空间 直接抛出错误
    if (namespace === undefined) throw new Error('namespace is not found!')
    this.namespace = namespace;
    this.init(); //直接初始化
  }
  //拓展方法
  MyEditor.prototype = {
    constructor: MyEditor,
    //初始化
    init: function () {
      //禁止元素的拖拽事件
      this.banDrag();
      //绑定编辑器按钮点击事件
      this.bindBtnClick();
      //绑定编辑器的onchange事件
      this.bindChange();
      //自动调整多行输入框文本框高度
      autosize($('#editor'));
      //检查是否需要恢复上次保存的内容
      this.checkStorage();
    },
    //禁止元素的拖拽事件 否则会影响用户的选择文本
    banDrag: function () {
      /*兼容性写法 去除浏览器（主要是ie）中h5拖拽事件的默认行为 ie9和ie10会出现onmouseup事件无法触发 */
      $('#editor').ondragstart = function (event) {
        event.preventDefault ? event.preventDefault() : event.returnValue = false;
      };
      $('#editor').ondragend = function (event) {
        event.preventDefault ? event.preventDefault() : event.returnValue = false;
      };
    },
    //解析markdown为html 将编辑器editor内的markdown文本解析为html输出到output预览区
    parseMd: function () {
      var md = $('#editor').value;
      var converter = new showdown.Converter({
        tables: true, //解析表格
        strikethrough: true, //解析双波浪线删除
      });
      var html = converter.makeHtml(md);
      $('#output').innerHTML = html;
    },
    //解析html为markdown 传入的html字符串解析为markdown 并显示到编辑器editor
    parseHtml: function (str) {
      var converter = new showdown.Converter({
        tables: true, //解析表格
        strikethrough: true, //解析双波浪线删除
      });
      return converter.makeMd(str);
    },
    //绑定编辑器按钮事件
    bindBtnClick: function () {
      //**粗体**
      this.equalBtn('#bold', '粗体', '**', /(\*\*|__)(.*?)(\*\*|__)/g, /[*]{2,}/g);
      //*斜体*
      this.equalBtn('#italic', '斜体', '*', /(\*)(.*?)(\*)/g, /[*]+/g);
      //~~删除线~~
      this.equalBtn('#strikeout', '删除线', '~~', /(\~\~)(.*?)(\~\~)/g, /[~]{2,}/g);
      //`行内代码`
      this.equalBtn('#line-code', '行内代码', '`', /(`)(.*?)(`)/g, /([`]+)/g);

      //绑定标题 选中标题再次点击标题全部会清除
      //# 一级标题
      this.titleBtn(1);
      //## 二级标题
      this.titleBtn(2);
      //### 三级标题
      this.titleBtn(3);
      //#### 四级标题
      this.titleBtn(4);
      //##### 五级标题
      this.titleBtn(5);
      //###### 六级标题
      this.titleBtn(6);

      //- 无序列表
      this.listBtn(0);
      //1. 有序列表
      this.listBtn(1);

      //保存this指向
      var that = this;

      //> 引述 html实体不会被shadown解析
      $('#quote').on('click', function () {
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '引述';
          editor.value = editor.value + '\r\n>' + sel_text;
        } else {
          var reg = /(>)(.*?)/g;
          //匹配到引述 则去除原有的引述 否则替换选中文本并添加符号
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/[>]+/g, '')) : editor.value.replace(sel_text, '\r\n>' + sel_text);
        }

        //触发解析markdown
        that.parseMd();
      });

      //块状代码
      $('#code').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '块状代码';
          editor.value = editor.value + '\r\n```language\r\n' + sel_text + '\r\n```';
        } else {
          var reg = /```([\s\S]*?)```[\s]*/g;
          //匹配到块状代码 则去除原有的块状代码 否则直接在选中文本前后添加符号
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/([`]+)/g, '')) : editor.value.replace(sel_text,'\r\n```language\r\n' + sel_text + '\r\n```');
        }

        //触发解析markdown
        that.parseMd();
      });

      //表格
      $('#table').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '\r\n| 左对齐 | 居中 | 右对齐 |\r\n| :--- | :---: | ---: |\r\n| 内容1 | 内容2 | 内容3 |';
          editor.value = editor.value + sel_text;
        } else {
          var reg = /[|]/g;
          //匹配到表格 去除原有的表格 否则直接在选中文本前后添加符号
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/([|]+)/g, '')) : editor.value.replace(sel_text,'| ' + sel_text + ' |');
        }

        //触发解析markdown
        that.parseMd();
      });

      //添加链接
      $('#link').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '\r\n[链接](https://github.com/hei-jack/heypass)';
          editor.value = editor.value + sel_text;
        } else {
          var reg = /\[[\s\S]*?\]\([\s\S]*?\)/g; //匹配链接
          //选中文本不为0 检查是否为已经有链接的文本
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/([\[\]\(\)]+)/g, '')) : editor.value.replace(sel_text,'[' + sel_text + '](网址)');
        }

        //触发解析markdown
        that.parseMd();
      });

      //引用链接
      $('#ref-link').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '\r\n[引用超链接][1]\r\n[1]: https://github.com/hei-jack/heypass';
          editor.value = editor.value + sel_text;
        } else {
          var reg = /\[[\s\S]*?\]/g;
          //匹配到链接 则去除原有的链接 否则直接在选中文本前后添加对应符号
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/([\[\]\(\)]+)/g, '')) : editor.value.replace(sel_text,'[' + sel_text + ']');
        }

        //触发解析markdown
        that.parseMd();
      });

      //外链图片
      $('#img').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = "![图片示例](https://s1.ax1x.com/2020/11/07/B4kXWQ.gif '图片示例')";
          editor.value = editor.value + sel_text;
        } else {
          var reg = /\!\[[\s\S]*?\]\([\s\S]*?\)/g;
          //匹配到去除 否则在文本前后添加对应符号
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/([\[\]\(\)!]+)/g, '')) : editor.value.replace(sel_text,'![' + sel_text + ']' + '(链接)');
        }

        //触发解析markdown
        that.parseMd();
      });

      //外链音乐
      $('#music').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        var video = '\r\n<audio id="audio1" class="video-js vjs-theme-fantasy vjs-big-play-centered" controls="true" preload="auto" data-setup="{}">'
            + '\r\n<source src="http://data.flash127.com/mp3_flash127_com/mp3/201812/20181224_2203_145165.mp3" type="audio/mpeg"></source>' + '\r\n<p class="vjs-no-js">' + '您当前浏览器不支持播放此音频，推荐使用新版本浏览器。</p>'
            + '</audio>' + '\r\n`请注意：音频需要修改src地址或者直接鼠标选中音频链接后点击外链音乐，多个音频请递增为不重复id`';

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          editor.value = editor.value + video;
        } else {
            //否则直接替换选中文本
            editor.value = editor.value.replace(sel_text,video.replace('http://data.flash127.com/mp3_flash127_com/mp3/201812/20181224_2203_145165.mp3',sel_text));
        }

        //触发解析markdown
        that.parseMd();
        

        //初始化videojs
        var player = videojs('audio1',{
          //设置宽度
          width: $('#output').offsetWidth,
          //设置语言
          language: 'zh-CN', 
          //设置高度
          height: 54
        });
      });

      //外链视频
      $('#video').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        var video = '\r\n<video id="video1" class="video-js vjs-theme-fantasy vjs-big-play-centered" controls="true" preload="auto" data-setup="">'
            + '\r\n<source src="http://vjs.zencdn.net/v/oceans.mp4" poster="https://w.wallhaven.cc/full/43/wallhaven-43klov.jpg" type="video/mp4"></source>' + '\r\n<p class="vjs-no-js">' + '\r\n您当前浏览器不支持播放此视频，推荐使用新版本浏览器。</p>'
            + '</video>' + '\r\n`请注意：视频地址需要修改src或者直接鼠标选中视频链接后点击外链视频，多个视频请递增为不重复id`';

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          editor.value = editor.value + video;
        } else {
            //否则直接替换选中文本
            editor.value = editor.value.replace(sel_text,video.replace('http://vjs.zencdn.net/v/oceans.mp4',sel_text));
        }

        //触发解析markdown
        that.parseMd();

        //触发解析多媒体
        var player = videojs('video1',{
          //设置宽度
          width: $('#output').offsetWidth,
          //设置语言
          language: 'zh-CN',
        });
      });

      //下划线
      $('#underline').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = '下划线';
          editor.value = editor.value + '<u>' + sel_text + '</u>';
        } else {
          var reg = /<\/?u>/g;
          //匹配到下划线 则去除原有的斜体 否则在前后添加斜体标签
          editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/<\/?u>/g, '')) : editor.value.replace(sel_text, '<u>' + sel_text + '</u>');
        }

        //触发解析markdown
        that.parseMd();
      });

      //横线
      $('#hr').on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          editor.value = editor.value + '\r\n---';
        } else {
          //如果选中的文本中含有三个横杠 替换横杠即可
          editor.value = sel_text.indexOf('---') !== -1 ? editor.value.replace(sel_text, sel_text.replace('---','')) : editor.value.replace(sel_text, '\r\n---');    
        }

        //触发解析markdown
        that.parseMd();
      });

      //清空
      $('#clear').on('click',function(){
        $('#editor').value = '';
        that.parseMd();
      });

      //帮助
      $('#help').on('click',function(){
        //显示帮助文档
        window.parent.$.home.createTab('help.html', '使用教程');
      });

      //隐藏按钮 隐藏预览区
      $('#hide').on('click',function(){
        //隐藏预览区最上层容器
        $('#right-wrapper').hide();
        //修改编辑器父容器为的overflow-y显示
        $('.editor-wrapper').style.cssText = 'overflow-y:visible;min-height:600px;height:auto;';
        //修改编辑器为的overflow-y显示
        $('#editor').style.cssText = 'overflow-y:visible;';
        //修改编辑器最上层容器类
        $('#left-wrapper').setAttribute('class',$('#left-wrapper').getAttribute('class').replace('col-6 ',''));
      });

      //保存按钮
      $('#save').on('click',function(){
        //如果textarea的值不为空 就保存
        var val = $('#editor').value;
        if(val.length === 0) return; //为空直接返回
        //否则进行存入操作 命名空间就是作用地1
        localStorage.setItem(that.namespace, val);
        showTopMessage('内容存入成功，只有一次恢复的机会哟！',true,1500);
      });

    },
    /**
     * 对称符号通用绑定函数
     * @param {string} text 文本 
     * @param {string} symbol 符号
     * @param {RegExp} reg1 匹配正则
     * @param {RegExp} reg2 替换正则
     */
    equalBtn: function (sel, text, symbol, reg1, reg2) {
      var that = this;
      $(sel).on('click', function () {
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = text; //text
          editor.value = editor.value + symbol + sel_text + symbol; //符号
        } else {
          editor.value = reg1.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(reg2, '')) : editor.value.replace(sel_text, symbol + sel_text + symbol);
        }

        that.parseMd();
      });
    },
    //标题按钮通用绑定函数 @param number level 标题等级
    titleBtn: function (level) {
      //存储this指向
      var that = this;
      var level_text;
      switch (level) {
        case 1:
          level_text = '一';
          break;
        case 2:
          level_text = '二';
          break;
        case 3:
          level_text = '三';
          break;
        case 4:
          level_text = '四';
          break;
        case 5:
          level_text = '五';
          break;
        case 6:
          level_text = '六';
          break;
      }
      level_text = level_text + '级';

      //标题 html实体不会被shadown解析
      $('#h' + level).on('click', function () {
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);
        var symbol = '';

        //遍历生成对应符号
        for (var i = 0; i < level; i++) {
          symbol += '#';
        }

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = level_text + '标题';
          editor.value = editor.value + '\r\n' + symbol + ' ' + sel_text;
        } else {
          var reg = /(#)(.*?)/g; 
            //匹配到标题 则去除原有的标题 否则直接给选中文本加入对应标题符号
            editor.value = reg.test(sel_text) ? editor.value.replace(sel_text, sel_text.replace(/[#]+/g, '')) : editor.value.replace(sel_text, '\r\n' + symbol + ' ' + sel_text);
        }

        that.parseMd();
      });
    },
    //列表按钮通用绑定函数 @param number mode 1有序列表 0无序列表
    listBtn:function(mode){
      //保存this指向
      var that = this;
      var sel = 'ol';
      var text = '有序';
      var symbol_1 = '1.';
      var symbol_2 = '2.';
      var symbol_3 = '3.';
      var reg1 = /([0-9]+[\.])(.*?)/g;
      var reg2 = /([0-9]+[\.])/g;
      if(mode === 0){
      sel = 'ul';
      text = '无序';
      symbol_1 = '-';
      symbol_2 = '-';
      symbol_3 = '-';
      reg1 = /(-)(.*?)/g;
      reg2 = /[-]+/g;
      }
      //无序列表
      $('#' + sel).on('click',function(){
        var editor = $('#editor');
        //获取选中的文本
        var sel_text = that.getSelectText(editor);

        //如果没有选中文本 直接在输入框尾部追加
        if (sel_text.length === 0) {
          sel_text = text + '列表';
          editor.value = editor.value + '\r\n' + symbol_1 + ' ' + sel_text + '\r\n' + symbol_2 + ' ' + sel_text + '\r\n' + symbol_3 + ' ' + sel_text;
        } else {
          //选中文本不为0 检查是否为已经无序列表的文本
          if (reg1.test(sel_text)) {
            //匹配到无序列表 则去除原有的无序列表
            editor.value = editor.value.replace(sel_text, sel_text.replace(reg2, ''));
          } else {
            //否则直接在选中文本之前加-
            editor.value = editor.value.replace(sel_text,'\r\n' + symbol_1 + ' ' + sel_text);
          }
        }

        that.parseMd();
      });
    },
    //获取用户鼠标框选中的文字
    getSelectText: function () {
      var sel_text;
      //现代浏览器
      if (window.getSelection) sel_text = window.getSelection().toString();
      if (sel_text.length !== 0) return sel_text;

      var el = $('#editor');

      //getSelection() 在非 Chromium内核浏览器 也就是ie、火狐对<textarea> 不起作用 ie9、10、11
      if (el.selectionStart) sel_text = el.value.substring(el.selectionStart, el.selectionEnd);
      if (sel_text.length !== 0) return sel_text;

      //ie低版本浏览器
      if (document.selection) sel_text = document.selection.createRange().text;
      if (sel_text.length !== 0) return sel_text;

      //最后返回空
      return '';
    },

    //绑定编辑器的onchange事件
    bindChange: function () {
      var that = this;
      //onchange事件需要失焦才触发 所以用oninput 和 onpropertychange 两个事件替代
      $('#editor').on('input', that.parseMd);
      //ie独有
      $('#editor').on('propertychange', that.parseMd);
    },
    //检查是否有上次保存的内容需要恢复
    checkStorage:function(){
      //读取当前命名空间保存的内容
      var temp_data = localStorage.getItem(this.namespace);
      if(temp_data === null) return;
        //如果含有内容
        //弹窗提示用户
        var good = confirm("您上次保存了一些内容，请问是否需要恢复？");
        if (good !== true) return;
        //执行恢复操作
        $('#editor').value = temp_data;
        //清空localStorage中对应数据
        localStorage.removeItem(this.namespace);
        //解析markdown
        this.parseMd();
    }
  }

  return new MyEditor(options);
}