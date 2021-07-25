//为my.jquery拓展一个home插件 主页插件
"use strict";
(function (global) {
  //声明一个函数 构造对象
  function MyIframeHome() {
    this.is_moblie = $.isMobile(); //是否为移动端设备
    this.sidebar_flag = true; //侧边栏状态 true显示/false隐藏 默认是显示的
    this.themes_time = 7; //主题过期时间 单位天
    this.show_drop = ''; //处于显示状态的下拉框 不允许多个下拉框同时显示
  }
  //拓展方法
  MyIframeHome.prototype = {
    constructor: MyIframeHome,
    //初始化
    init: function () {
      this.loadThemes(); //载入主题
      this.togglerBind(); //绑定侧边栏折叠按钮事件
      this.bindTabsLink(); //绑定所有的heypass-tabs a标签点击事件
      this.bindTab(0); //绑定主页标签的相关事件
      this.bindDropBtn('.dropdown-themes','.dropdown-themes .dropdown-menu'); //主题下拉框显示隐藏特效
      this.bindDropBtn('.user-info','.dropdown-user .dropdown-menu'); //用户头像下拉框显示隐藏特效
      this.bindDropBtn('.tools-dropdown','#dropdown-tab-tools'); //标签栏选项卡显示隐藏特效
      this.themesCancelBubble(); //取消主题下拉框事件冒泡
      this.bindTabsToolBtn();//绑定标签条按钮相关事件
    },

    //侧边栏折叠按钮绑定点击事件
    togglerBind: function () {
      var that = this;
      $('.heypass-aside-toggler').on('click', function () {
        that.sidebarAnimate();
      });
    },

    //侧边栏特效
    sidebarAnimate: function () {
      //先判断是否是移动端 分发过去 然后进行分发
      $.isMobile() ? this.sidebarMobile() : this.sidebarPc();
    },

    //移动端 侧边栏折叠与展开特效 遮罩层
    sidebarMobile: function () {
      //如果是移动端设备 点击按钮显示 然后动态追加一个遮罩层
      var sidebar = $('.heypass-layout-sidebar');
      //sidebar.show();
      sidebar.slideLeft(); //左滑动画
      var modal = document.createElement('div');
      modal.setAttribute('class', 'mask-modal');
      document.body.insertBefore(modal, sidebar.parentNode);
      //当遮罩层发生点击事件时 隐藏侧边栏 移除遮罩层
      modal.onclick = function () {
        sidebar.hide();
        document.body.removeChild(this);
        modal.onclick = null;
      }
    },

    //PC端 侧边栏折叠与展开特效
    sidebarPc: function () {
      if (this.sidebar_flag) {
        //如果已经显示 进行折叠操作
        $('.heypass-toggler-bar')[1].style.width = 20 + 'px'; //将按钮中间杠变长

        //折叠侧边栏
        var sidebar = $('.heypass-layout-sidebar');

        sidebar.style.display = 'block';

        var n = sidebar.offsetWidth; //获取元素长度
        var id = setInterval(function () {
          n = n - 5;
          sidebar.style.width = n + 'px';
          $('.heypass-layout-header').style.paddingLeft = n + 'px'; //头部
          $('.heypass-layout-main').style.paddingLeft = n + 'px'; //主体
          if (n <= 60) clearInterval(id); //清除定时器
        }, 1000 / 250);
        $('.sidebar-header a img').style.marginLeft = -25 + 'px';
        $('.sidebar-header a img').style.width = 180 + 'px';
        $('.sidebar-footer p').hide();
        $('.sidebar-main').style.overflow = 'hidden';
        var span = $('.nav-item .heypass-tabs span');
        for (var i = 0; i < span.length; i++) {
          span[i].style.display = 'none';
        }
        this.sidebar_flag = false;
      } else {
        //如果已经折叠  进行展开操作
        $('.heypass-toggler-bar')[1].style.width = 15 + 'px'; //将按钮中间杠变短
        var sidebar = $('.heypass-layout-sidebar');
        sidebar.style.display = 'block';

        var n = sidebar.offsetWidth; //获取元素长度
        var id = setInterval(function () {
          n = n + 5;
          sidebar.style.width = n + 'px';
          $('.heypass-layout-header').style.paddingLeft = n + 'px'; //头部
          $('.heypass-layout-main').style.paddingLeft = n + 'px'; //主体
          if (n >= 240) clearInterval(id); //清除定时器
        }, 1000 / 250);
        $('.sidebar-header a img').style.cssText = '';
        $('.sidebar-main').style.cssText = '';
        var span = $('.nav-item .heypass-tabs span');
        for (var i = 0; i < span.length; i++) {
          span[i].style.cssText = '';
        }
        $('.sidebar-footer p').show();
        this.sidebar_flag = true;
      }
    },

    //载入主题配色
    loadThemes:function(){
      //从cookie中读取存入的配色信息 读取所有的cookie
      var themes = $.cookie();
      //载入logo
      this.loadLogo(themes.logobg);
      //载入各部分的背景色
      this.loadBg('logobg',themes.sidebarbg);
      this.loadBg('headerbg',themes.headerbg);
      this.loadBg('sidebarbg',themes.sidebarbg);
      //绑定各部分的背景色切换
      this.bindBgClick('logobg');
      this.bindBgClick('headerbg');
      this.bindBgClick('sidebarbg');
    },

    /* 载入背景颜色
     * @param string key 位置 logo/header/sidebar
     * @param string val 值 cookie存储的主题值
     */
    loadBg:function(key,val){
      if(val !== undefined){
        $('body').setAttribute('data-' + key, val);
        //根据当前背景色是否为默认采用不同方法更新对应背景色单选框的选择状态
        var el = val.indexOf('default') !== -1 ? $('#' + key.replace('bg','_bg_') + 1):$('#' + key.replace('bg','_bg_') + val.replace('color_', ''));
        el.checked = true;
        }
    },

    //载入logo 白色背景需要变成黑色logo
    loadLogo:function(logobg){
      var url = $('.sidebar-header a img').getAttribute('src');
      var path = url.substring(0,url.lastIndexOf('/') + 1);
      if(logobg !== undefined){
        if(logobg.indexOf('default') !== -1) $('.sidebar-header a img').setAttribute('src', path + 'logo1.png');
      }else{
        $('.sidebar-header a img').setAttribute('src', path + 'logo1.png');
      }
    },

    //绑定背景色点击事件 @param string key 位置 logo/header/sidebar
    bindBgClick:function(key){
      var that = this; //保存this指向
      var bg = $("input[name='" + key.replace('bg','_bg') + "']");
      var len = bg.length;
      var url = $('.sidebar-header a img').getAttribute('src');
      var path = url.substring(0,url.lastIndexOf('/') + 1);
      for (var i = 0; i < len; i++) {
        bg[i].onclick = function () {
          $('body').setAttribute('data-' + key, this.value);
          $.cookie(key, this.value, {exdays: that.themes_time,}); //设置cookie 过期时间
          if (key === 'logobg' && this.value === 'default') $('.sidebar-header a img').setAttribute('src', path + 'logo1.png');
          if (key === 'logobg' && this.value !== 'default')  $('.sidebar-header a img').setAttribute('src', path + 'logo-sidebar1.png');
        }
      }
    },

    //绑定所有class为heypass-tabs的a标签
    bindTabsLink:function(){
      //获取class为heypass-tabs的元素
      var links = $('.heypass-tabs');
      var len = links.length;
      //保存this指向
      var that = this;
      //循环绑定事件
      for(var i = 0;i < len;i++){
        links[i].onclick = function(eve){
          //阻止a链接的默认跳转
          var e = eve || window.event;
          e.preventDefault ? e.preventDefault():e.returnValue = false;
          // console.log(this);
          //获取a标签的href属性 前往创建标签和标签页（iframe页面）
          that.createTab(this.getAttribute('href'),this.innerText);
        }
      }
    },

    /*
     * 创建标签和标签页
     * @param string url 标签页地址
     * @param string title 标签页名称
     */
    createTab:function(url,title){
      //先检查对应标签是否已经存在
      var tabs = $('.nav-tabs').getElementsByClassName('nav-item');
      var len = tabs.length;
      var clear_url = url.replace(/\?.*/g,''); //去除可能存在的参数
      //遍历已有标签
      for(var i = 0;i < len;i++){
        if(tabs[i].getElementsByTagName('a')[0].getAttribute('data-url') === clear_url){
          //如果已经存在 展示对应页面等
          this.showTab(i);
          //不用继续往下执行
          return;
        }
      }

      //获取data-index的值 计算id
      var temp_arr = [];
      var tab_items = $('#iframe-pages').getElementsByClassName('tab-item');
      for (var i = 0; i < len; i++) {
        temp_arr.push(tab_items[i].getAttribute('data-index'));
      }
    
     var tab_index = Math.max.apply(null, temp_arr) + 1; //数组最大索引+1

      //如果不存在 说明要创建对应标签
      //创建标签页
      var new_iframe = document.createElement('iframe');
      new_iframe.setAttribute('id', 'heypass_tabs_' + tab_index);
      new_iframe.setAttribute('class', 'tab-item');
      new_iframe.setAttribute('width', '100%');
      new_iframe.setAttribute('height', '100%');
      new_iframe.setAttribute('frameborder', '0');
      new_iframe.setAttribute('src', url);
      new_iframe.setAttribute('style', 'height: 100%;');
      new_iframe.setAttribute('data-index', tab_index);
      $('#iframe-pages').appendChild(new_iframe);
      //创建新标签
      var new_tab = document.createElement('li');
      new_tab.setAttribute('class', 'nav-item'); //设置类属性
      var new_tab_link = document.createElement('a');
      new_tab_link.setAttribute('class', 'nav-tab-link');
      new_tab_link.setAttribute('data-id', 'heypass_tabs_' + tab_index); //自定义id
      new_tab_link.setAttribute('data-type', 'info'); //自定义类型属性
      new_tab_link.setAttribute('data-url', clear_url); //自定义url属性
      new_tab_link.setAttribute('href', 'javascript:void(0);'); //href属性
      new_tab_link.setAttribute('style', 'display: inline-block;'); //style属性
      new_tab_link.textContent = title;
      //标签关闭小图标按钮
      var new_close = document.createElement('span');
      new_close.setAttribute('class', 'close');
      new_close.textContent = 'x';
      new_tab.appendChild(new_tab_link);
      new_tab.appendChild(new_close);
      $('.nav-tabs').appendChild(new_tab);

      //保存当前this指向
      var that = this;

      //展示刚刚创建的标签
      this.showTab(len);

      //假如当前标签总长度超过标签栏总长度 需要修正margin-left的值
      if(this.checkOverflow()){
        this.checkLeft();
      }

      //绑定标签事件
      this.bindTab(len);

      //为标签关闭小图标按钮绑定点击事件
      new_close.onclick = function(eve){
        var e = eve || window.event;
        document.all ? e.cancelBubble = true : e.stopPropagation(); //禁止事件冒泡
        //获取当前关闭小图标按钮的父元素在父元素中索引
        var ancestor = this.parentNode.parentNode; //祖先元素 也就是标签的父元素
        var childs = ancestor.getElementsByTagName('li');
        var childs_len = childs.length;
        var prev = this.previousElementSibling; //上一个同胞元素 也就是a标签
        var data_id = prev.getAttribute('data-id'); //data-id属性
        //遍历元素
        for(var i = 0;i < childs_len;i++){
          if(childs[i].getElementsByTagName('a')[0].getAttribute('data-id') === data_id){
            //删除标签
            that.deleteTab(i);
            //打断循环
            break;
          }
        }
      }
    },

    //删除标签和标签页 @param string index 索引
    deleteTab:function(index){
      //获取标签
      var tab = $('.nav-tabs').getElementsByClassName('nav-item')[index];

      //如果当前标签为活动页 则展示前一个标签
      if(tab.getAttribute('class').indexOf('active') !== -1) this.showTab(index - 1);

      //获取标签页
      var page = $('#' + tab.getElementsByTagName('a')[0].getAttribute('data-id'));
      //删除标签
      $('.nav-tabs').removeChild(tab);
      //删除标签页
      $('#iframe-pages').removeChild(page);
    },

    //刷新标签页
    //@param string selector 选择器 标签页对应选择器
    //@param stirng url 标签url
    refreshTab:function(selector,url){
      var time = new Date().getTime(); //获取毫米级时间戳
      var src = $(selector).getAttribute('src'); //获取对应标签页的src
            
      /* 弃用
      if(src.indexOf('=') !== -1){
        //如果发现等号 继续分辨
        if(src.indexOf('?__nocache=') !== -1){
          //发现说明是无参数网址刷新后
          $(selector).setAttribute('src',src.substring(0,src.lastIndexOf('=') + 1) + time);
        }else if(src.indexOf('&__nocache=') !== -1){
          //发现标志说明是有参数网址刷新后
          $(selector).setAttribute('src',src.substring(0,src.lastIndexOf('=') + 1) + time);
        }else{
          //说明是有参数网址 未刷新
          $(selector).setAttribute('src',src + '&__nocache=' + time);
        }
      }else{
        //没有发现等号说明是无参数 未刷新
        $(selector).setAttribute('src',url + '?__nocache=' + time);
      } */

      //如果发现等号 说明要么是有参数的网址 要么是无参数刷新后的网址
      if(src.indexOf('=') !== -1){
        //如果发现等号且存在标识字符 说明是 无参数-刷新 或者 有参数-刷新 都只需要更新最后一个等号时间戳即可 反之 有参数未刷新 在后面追加__nocache+时间戳
        src.indexOf('__nocache') !== -1 ? $(selector).setAttribute('src',src.substring(0,src.lastIndexOf('=') + 1) + time) : $(selector).setAttribute('src',src + '&__nocache=' + time);    
      }else{
        //没有发现等号说明是无参数 未刷新 后面追加?__nocache=时间戳
        $(selector).setAttribute('src',url + '?__nocache=' + time);
      } 
    },

    //为标签绑定事件 index number 索引
    bindTab:function(index){
      //获取标签
      var el = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item')[index];
      // console.log(main);
      var that = this;//保存this指向

      //阻止默认的鼠标右击事件
      el.oncontextmenu = function(eve){
        var e = eve || window.event;
        e.preventDefault ? e.preventDefault():e.returnValue = false;
      }

      //用onmouseup事件来模拟鼠标右击事件
      el.onmouseup = function (eve) {
        var e = eve || window.event;
        if (e.button === 2) {
          //如果鼠标弹起事件是由鼠标右击触发的 弹出下拉菜单 根据子元素的data-type属性判定标签类型 弹出不一样的下拉框 主页不允许关闭
          
          //移除可能已经创建下拉菜单
          //如果最后一个节点不是元素节点
          //ie9出现兼容问题 弃用
          // var last = $('body').lastChild.nodeType !== 1 ? $('body').lastChild.previousElementSibling:$('body').lastChild; 
          var last = $('body').lastElementChild;
          // console.log($('body').lastElementChild);
          //根据id判断是否是下拉菜单 如果是则移除
          if(last.getAttribute('id') === 'contextify-menu'){
            $('body').removeChild(last);
          }

          //隐藏其他的下拉框
          if(that.show_drop.length !== 0 && that.show_drop !== '#contextify-menu'){
          //隐藏该显示的菜单
          $(that.show_drop).hide();
          $(that.show_drop).show_flag = false;
          //清空已经显示
          that.show_drop = '';
         }

          var close = this.getElementsByTagName('a')[0].getAttribute('data-type') !== 'main';
          //获取当前标签子元素的data-id属性
          var data_id = this.getElementsByTagName('a')[0].getAttribute('data-id');
          var data_url = this.getElementsByTagName('a')[0].getAttribute('data-url');

          //创建下拉菜单
          var ul = document.createElement('ul');
          ul.setAttribute('class', 'dropdown-menu dropdown-tabs'); //设置类
          ul.setAttribute('id', 'contextify-menu');
          //从鼠标点击位置开始显示
          ul.setAttribute('style', 'top:' + e.clientY + 'px; left:' + e.clientX + 'px; position:fixed; display:block;width: 100px;');
          var style = 'style="cursor: pointer;display: block;clear: both;font-weight: 400;color: #212529;text-align: inherit;white-space: nowrap;border: 0;"'; //a标签的样式

          //获取子元素（第一个子节点是文本节点 的下一个同胞节点 也就是a元素）
          var close_html = close ? '<li><a class="dropdown-item close-this" ' + style + '>关闭</a></li>' : '';
          ul.innerHTML = '<li><a class="dropdown-item refresh-tab" ' + style + '>刷新</a></li>' + close_html + '<li><a class="dropdown-item close-other" ' + style + '>关闭其他</a></li>';
          document.body.appendChild(ul);
          that.show_drop = '#contextify-menu';

          //为按钮绑定事件
          //当下拉菜单刷新按钮发生点击事件时
          $('#contextify-menu .refresh-tab').on('click',function(){
            //刷新对应tab
            that.refreshTab('#' + data_id,data_url);
            //销毁当前下拉框
            $('body').removeChild($('#contextify-menu'));
            that.show_drop = ''; //清空
          });

          if(close){
            //如果不是主页 则绑定关闭按钮事件
            $('#contextify-menu .close-this').on('click',function(){
              //获取标签
              var tabs = $('.nav-tabs').getElementsByTagName('li'); //祖先元素 也就是标签的父元素
              var len = tabs.length;
              //遍历元素
              for(var i = 0;i < len;i++){
                if(tabs[i].getElementsByTagName('a')[0].getAttribute('data-id') === data_id){
                //删除标签
                that.deleteTab(i);
                //打断循环
                break;
                }
              }
              //销毁当前下拉框
              $('body').removeChild($('#contextify-menu'));
              that.show_drop = ''; //清空
            });
          }


          //当关闭其他按钮发生点击事件时
          $('#contextify-menu .close-other').on('click',function(){
            //如果是主页 就去关闭所有标签 如果不是 就去关闭其他标签
             data_id === 'heypass_tabs_main' ? that.closeAllTab():that.closeOtherTab(data_id);

             //调整margin-left
             $('.nav-tabs').setAttribute('style', 'margin-left: 0px;');

            //销毁当前下拉框
            $('body').removeChild($('#contextify-menu'));
            that.show_drop = ''; //清空
          });

        }
      }

      //当标签发生点击时
      el.onclick = function(){
        //展示这个标签页
        //获取当前标签在父元素中索引
        var parent = this.parentNode; //祖先元素 也就是标签的父元素
        var sibling = parent.getElementsByTagName('li');
        var sibling_len = sibling.length;
        var child = this.getElementsByTagName('a')[0]; //子元素 也就是a标签
        var data_id = child.getAttribute('data-id'); //data-id属性
        //遍历元素
        for(var i = 0;i < sibling_len;i++){
          if(sibling[i].getElementsByTagName('a')[0].getAttribute('data-id') === data_id){
            //展示标签
            that.showTab(i);
            //打断循环
            break;
          }
        }
      }
    },

    //展示标签页 索引
    showTab:function(index){
      //隐藏已经展示的iframe页面
      $('#iframe-pages .active').hide();
      $('#iframe-pages .active').setAttribute('class','tab-item');

      //移除原有的标签active
      $('.nav-tabs .active').setAttribute('class','nav-item');
      //设置新的标签active
      $('.nav-tabs').getElementsByClassName('nav-item')[index].setAttribute('class','nav-item active');

      //获取当前的标签url
      var url = $('.nav-tabs').getElementsByClassName('nav-item')[index].getElementsByTagName('a')[0].getAttribute('data-url');

      // console.log(url);

      var tabs = $('#iframe-pages').getElementsByClassName('tab-item');
      var len = tabs.length;
      for(var i = 0;i < len;i++){
        // console.log(tabs[i].getAttribute('src').replace(/\?.*/g,''));
        //查找对应ifreme页面 根据url
        if(tabs[i].getAttribute('src').replace(/\?.*/g,'') === url){
          tabs[i].setAttribute('class','tab-item active');
          tabs[i].style.display = 'block';
        }
      }
      
      /* //展示对应页面
      $('iframe[data-index="' + index + '"]').show();
      //设置为active
      $('iframe[data-index="' + index + '"]').setAttribute('class','tab-item active'); */
      
      //移除侧边栏原有的active
      var active = $('.nav-drawer').getElementsByClassName('active')[0];
      // console.log(active);
      //如果存在 先对比是否相同 若相同返回 否则移除
      
      if(active){
        var old = active.getElementsByTagName('a')[0].getAttribute('href');
        if(old === url) return;
        active.setAttribute('class','nav-item');
      }
      //设置新的侧边栏active
      //跟侧边栏的href属性进行对比 如果有相等的 则设置为active
      //获取侧边栏所有的li标签
      var lis = $('.nav-drawer .nav-item');
      var len = lis.length;
      for(var i = 0;i < len;i++){
        var href = lis[i].getElementsByTagName('a')[0].getAttribute('href');
        if(href === url){
          //如果找到 则设置为active 打断循环(return函数效果等同break)
          lis[i].setAttribute('class','nav-item active');
          return;
        }
      }
    },

    //创建标签栏下拉框按钮（左滑 右滑 显示当前选项卡 关闭所有标签页 关闭其他标签页）
    bindTabsToolBtn:function(){
      var that = this; //保存this指向
      //当标签栏左滑按钮发生点击事件时
      $('.nav-tools-left').on('click',function(){
        that.moveLeft();
      });

      //当标签栏右滑按钮发生点击事件时
      $('.tools-move-right').on('click',function(){
        that.moveRight();
      });

      //当标签栏下拉框显示当前选项卡按钮发生点击事件时
      //当显示当前选项卡发生点击事件时
      $('.show-now-tab').on('click', function () {
        //获取所有标签
        var li = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item');
        var li_len = li.length;
        var now_width = 0;
        var panel_width = $('.nav-tools-panel').offsetWidth;
        for (var i = 0; i < li_len; i++) {
          //如果发现当前标签存在active 就打断循环
          if (li[i].getAttribute('class').indexOf('active') !== -1) {
            now_width += li[i].offsetWidth;
            break;
          }
          now_width += li[i].offsetWidth;
        }
        if (now_width >= panel_width) {
          //超出面板长度 就调整margin-left的值
          var now_left = now_width - li[li.length - 1].offsetWidth - $('.nav-tools-left').offsetWidth;
          $('.nav-tabs').setAttribute('style', 'margin-left: -' + now_left + 'px;');
        } else {
          //否则调整为0
          $('.nav-tabs').setAttribute('style', 'margin-left: 0px;');
        }

        //隐藏当前下拉框
        $('#dropdown-tab-tools').hide();
        $('#dropdown-tab-tools').show_flag = false;
        that.show_drop = '';
      });

      //当下拉框关闭所有标签页发生点击事件时
      $('.close-all-tab').on('click',function(){
        that.closeAllTab();

        //调整margin-left
        $('.nav-tabs').setAttribute('style', 'margin-left: 0px;');

        //隐藏当前下拉框
        $('#dropdown-tab-tools').hide();
        $('#dropdown-tab-tools').show_flag = false;
        that.show_drop = '';
      });

      //当下拉框关闭其他标签页发生点击事件时
      $('.close-other-tab').on('click',function(){
        //获取当前活动标签的data_id
        var data_id = $('.nav-tabs .active').getElementsByTagName('a')[0].getAttribute('data-id');
        //如果是主页 就去关闭所有标签 如果不是 就去关闭其他标签
        data_id === 'heypass_tabs_main' ? that.closeAllTab():that.closeOtherTab(data_id);

        //调整margin-left
        $('.nav-tabs').setAttribute('style', 'margin-left: 0px;');

        //隐藏当前下拉框
        $('#dropdown-tab-tools').hide();
        $('#dropdown-tab-tools').show_flag = false;
        that.show_drop = '';
      });
    },

    /*
     * 绑定下拉菜单显示/隐藏按钮
     * @param string btn_sel 按钮选择器
     * @param stirng menu_sel 下拉菜单选择器
     */
    bindDropBtn:function(btn_sel,menu_sel){
      // 保存this指向
      var that = this;
      //当下拉菜单的显示/隐藏按钮发生点击事件时
      $(btn_sel).on('click',function(){
         //先判断是否已经有除了当前之外的菜单处于显示状态 如果有 先关闭 否则忽略
         if(that.show_drop.length !== 0 && that.show_drop !== menu_sel){
          //隐藏该显示的菜单
          $(that.show_drop).hide();
          $(that.show_drop).show_flag = false;
          //清空已经显示
          that.show_drop = '';
        }

        //如果当前元素对象
        if($(menu_sel).show_flag){
          //隐藏菜单
          $(menu_sel).hide();
          $(menu_sel).show_flag = false;
          //清空已经显示
          that.show_drop = '';
        }else{
          //显示菜单
          $(menu_sel).show();
          //将当前元素对象的属性 this.show_flag 赋值
          $(menu_sel).show_flag = true;
          //将当前下拉菜单的选择器存入that
          that.show_drop = menu_sel;
        }
      });
    },

    //阻止主题下拉菜单冒泡
    themesCancelBubble:function(){
      //阻止主题下拉菜单冒泡 此处为了使用模拟jquery 否则可以使用addEventListener()
      $('.dropdown-themes .dropdown-menu').on('click', function (e) {
        var event = e || window.event;
        //阻止冒泡事件,否则会触发下拉菜单消失
        //阻止事件传播到包容对象，必须把该属性设为 true //不再派发事件 FF,Chrome
        document.all ? event.cancelBubble = true : event.stopPropagation();
      });
    },

    //关闭其他标签 @param string data_id 要保留的id
    closeOtherTab:function(data_id){
      //遍历标签
      var tabs = $('.nav-tabs').getElementsByTagName('li');
      var len = tabs.length;
      //获取当前data-id的索引 展示对应页面
      for(var i = 0;i < len;i++){
        if(tabs[i].getElementsByTagName('a')[0].getAttribute('data-id') === data_id){
          this.showTab(i);
          break;
        }
      }
      for(var i = len - 1;i >= 1;i--){
        var id = tabs[i].getElementsByTagName('a')[0].getAttribute('data-id');
        if(id === data_id) continue; //跳出本轮循环
        //移除当前对应标签的标签页
        $('#iframe-pages').removeChild($('#' + id));
        //移除当前标签
        $('.nav-tabs').removeChild(tabs[i]);
      }
    },

    //关闭所有标签(其实保留主页)
    closeAllTab:function(){
      //展示主页
      this.showTab(0);
      //遍历删除除主页之外的标签
      var tabs = $('.nav-tabs').getElementsByTagName('li');
      var len = tabs.length;
      for(var i = len - 1;i >= 1;i--){
        var id = tabs[i].getElementsByTagName('a')[0].getAttribute('data-id');
        //移除当前对应标签的标签页
        $('#iframe-pages').removeChild($('#' + id));
        //移除当前标签
        $('.nav-tabs').removeChild(tabs[i]);
      }
    },

    //计算li的长度是否超过nav-tools-panel的长度
    checkOverflow:function() {
      var panel_width = $('.nav-tools-panel').offsetWidth;
      return this.getNavItemWidth() > panel_width;
    },

    //计算移动的margin-left的值
    moveRight:function() {
      var now_style = $('.nav-tabs').getAttribute('style');
      if (now_style) {
        //如果获取到了 计算值
        var now_left = Number(now_style.replace(/[^0-9]/ig, ""));
        var li = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item');
        var panel_width = $('.nav-tools-panel').offsetWidth;
        //如果不等于最大偏移量 进行递增操作 直到等于或大于最大偏移量为止
        now_left += panel_width;
        //如果等于最大偏移量 直接返回
        if (now_left >= this.getNavItemWidth() - li[li.length - 1].offsetWidth - $('.nav-tools-left').offsetWidth) {
          now_left = this.getNavItemWidth() - li[li.length - 1].offsetWidth - $('.nav-tools-left').offsetWidth;
          $('.nav-tabs').setAttribute('style', 'margin-left: -' + now_left + 'px;');
          return false;
        }
        $('.nav-tabs').setAttribute('style', 'margin-left: -' + now_left + 'px;');
      } else {
        //如果没有获取style的值 直接返回
        return false;
      }
    },

    //计算移动的margin-left的值
    moveLeft:function() {
      var now_style = $('.nav-tabs').getAttribute('style');
      if (now_style) {
        //如果获取到了 计算值
        //var now_left = Number(now_style.replace('.margin-left: -','').replace('px;',''));
        var now_left = Number(now_style.replace(/[^0-9]/ig, ""));
        var li = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item');
        var panel_width = $('.nav-tools-panel').offsetWidth;
        //如果不等于最大偏移量 进行递减操作 直到等于或大于最小偏移量为止
        now_left -= panel_width;
        //如果等于最小偏移量 直接返回
        if (now_left <= 0) {
          $('.nav-tabs').setAttribute('style', 'margin-left: ' + 0 + 'px;');
          return false;
        }
        $('.nav-tabs').setAttribute('style', 'margin-left: -' + now_left + 'px;');
      } else {
        //如果没有获取style的值 直接返回
        return false;
      }
    },

    //检查margin-left的距离
    checkLeft:function () {
      //当li的总长度超过nav-tools-panel时才计算
      //获取当前超过的临界值
      //获取所有li相加的长度
      var li = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item');
      var panel_width = $('.nav-tools-panel').offsetWidth;
      var temp_width = this.getNavItemWidth();
      var move_left = temp_width - li[li.length - 1].offsetWidth - $('.nav-tools-left').offsetWidth;
      $('.nav-tabs').setAttribute('style', 'margin-left: -' + move_left + 'px;');
    },

    //获取所有标签的总长度
    getNavItemWidth:function() {
      var li = document.getElementsByClassName('nav-tabs')[0].getElementsByClassName('nav-item');
      var li_len = li.length;
      var temp_width = 0;
      for (var i = 0; i < li_len; i++) {
        temp_width += li[i].offsetWidth;
      }
      return temp_width;
    },

  }

  if(global.$ === undefined) throw new Error('my jqeury is not found!');
  //将当前对象实例化
  var home = new MyIframeHome();
  //将当前对象实例 赋值给$.home
  $.home = home;
  return $.home;
})(window);