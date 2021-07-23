//为my.jquery拓展一个diary插件 日记插件
"use strict";
if (window.$ === undefined) throw new Error('my jqeury is not found!');
$.diary = function (options) {
  //声明一个函数 构造对象
  function MyDiary(options) {
    if(options === undefined) throw(new Error('options is not found!'));
    
    if(options.url === undefined) throw(new Error('url is error!'));
    this.url = options.url; //请求地址
    if(options.method === undefined) options.method = 'GET';
    this.method = options.method; //请求方式 默认get
    this.ajaxOptions = options.ajaxOptions === undefined ? null:options.ajaxOptions; //ajax请求的头部
    if(options.queryParams === undefined) throw(new Error('queryParams is empty!'));
    this.queryParams = options.queryParams; //ajax请求参数
    this.dataType = options.dataType === undefined ? 'json':options.dataType; //期待返回参数
    if(options.onLoadSuccess === undefined) options.onLoadSuccess = function(){}
    this.onLoadSuccess = options.onLoadSuccess; //载入成功事件
    if(options.onLoadError === undefined) options.onLoadError = function(){}
    this.onLoadError = options.onLoadError;//载入失败事件
    this.wait = options.wait === undefined ? 1500:options.wait; //强制用户必须等待的最小时间 单位毫秒

    //获取当前时间
    var now = new Date();
    this.year = now.getFullYear(); //年份
    this.month = now.getMonth() + 1; //月份
    this.day = now.getDate(); //日
    this.now_flag = true; //检查当前日期标记位
    
    this.init();
  }
  //拓展方法
  MyDiary.prototype = {
    constructor: MyDiary,
    //初始化
    init: function(){
      //先载入主题
      this.loadTheme(this.month);

      //不停不停的打印时间
      var timer = setInterval(function(){
        this.currentTime();
      }.bind(this),500);

      //打印当前年月
      this.printYearMonth(this.year,this.month);

      //绑定按钮
      this.bindPrevBtn(); //上一月
      this.bindNextBtn(); //下一月
      this.bindCurrentBtn(); //返回当前时间年月
      this.bindYearBtn(); //点击年按钮
      this.bindMonthBtn(); //绑定月按钮
      this.bindSelectYears(); //绑定年份选择界面按钮
      this.bindSelectMonths(); //绑定月份选择界面按钮
      this.bindDayBtn();//绑定日按钮
      this.bindDialogBtn(); //绑定弹窗关闭按钮事件

      //从服务器获取数据
      this.getData(this.year,this.month);
    },
    //打印当前时间方法
    currentTime:function(){
      //获取当前日期的年月日时分秒
      var now = new Date();
      var year = now.getFullYear();
      var month = now.getMonth() + 1;
      month = month < 10 ? '0' + month:String(month);
      var day = now.getDate();
      day = day < 10 ? '0' + day:String(day);
      var hour = now.getHours();
      hour = hour < 10 ? '0' + hour:String(hour);
      var minute = now.getMinutes();
      minute = minute < 10 ? '0' + minute:String(minute);
      var second = now.getSeconds();
      second = second < 10 ? '0' + second:String(second);
      //打印到对应部分
      $('#today').innerText = year + '年' + month + '月' + day + '日';
      $('#nowtime').innerText = hour + ':' + minute + ':' + second;
    },
    //打印年月 number
    printYearMonth:function(year,month){
      $('#year').innerText = String(year);
      $('#month').innerText = month < 10 ? '0' + month:String(month);
    },
    //载入主题 月份
    loadTheme:function(month){
      $('.diary-container').setAttribute('data-theme',month);
    },
    //展示loading动画
    showLoading:function(){
      //先隐藏原来的活动界面
      this.removeActiveClass();
      $('.diary-loading').setAttribute('class',$('.diary-loading').getAttribute('class') + ' diary-show');
    },
    //设置活动界面
    setActiveClass:function(sel){
      //先隐藏原来的活动界面
      this.removeActiveClass();
      //设置新的活动界面
      $(sel).setAttribute('class',$(sel).getAttribute('class') + ' diary-show');
    },
    //隐藏活动的界面
    removeActiveClass:function(){
      //隐藏原先展示的界面
      $('.diary-show').setAttribute('class',$('.diary-show').getAttribute('class').replace(' diary-show',''));
    },
    //获取当前活动的界面类
    getActiveBox:function(){
      return $('.diary-show').getAttribute('class').replace(' diary-show','');
    },
    //主体过渡动画属性 deg 角度
    transitionMain:function(deg){
      $('.diary-main').setAttribute('style','transform: rotateY(' + deg + 'deg);');
    },
    //头部过渡动画属性 deg 角度
    transitionHeader:function(deg){
      $('.diary-center').setAttribute('style','transform: rotateX(' + deg + 'deg);');
    },
    //设置上一月动画
    setPrevAnimate:function(){
        var that = this;
        var start = 0;
        var timer2 = setInterval(function(){
          start += 10;
          that.transitionMain(-start);
          that.transitionHeader(start);
          if(start > 45){
            //设置主题
            that.loadTheme(Number($('#month').innerText));
            clearInterval(timer2);
            that.transitionMain(0);
            that.transitionHeader(0);
          }
        },100);
    },
    //设置下一月动画
    setNextAnimate:function(){
      var that = this;
      var start = 0;
      var timer2 = setInterval(function(){
        start += 10;
        that.transitionMain(start);
        that.transitionHeader(start);
        if(start > 45){
          //设置主题
          that.loadTheme(Number($('#month').innerText));
          clearInterval(timer2);
          that.transitionMain(0);
          that.transitionHeader(0);
        }
      },100);
    },
    //发送ajax请求获取数据
    getData:function(year,month){
      //获取请求参数
      var params = this.queryParams({year: year,month: month});  
      //如果是函数 就动态执行该函数 如果不是函数就直接设置为该项的值 构造请求头
      var ajaxOptions = typeof(this.ajaxOptions) === 'function' ? this.ajaxOptions():this.ajaxOptions;
      //保存this指向
      var that = this;
      //开始ajax请求
      $.ajax({
        url: this.url, //接口地址
        type: this.method, //请求方法
        dataType: this.dataType, //期待返回数据类型
        data: params, //请求参数
        headers: ajaxOptions, //请求头信息
        success: function(result,status,xmlhttp) {

          //请求成功之后 获取数据是否合法
          if(result.status !== 200){
          //展示对应月份日历
          that.showDate(year,month);
          //执行失败回调函数
          that.onLoadError();
          return;
          }

          //如果合法 但是没有数据 也展示对应日历
          if(result.total === 0){
          //展示对应月份日历
          that.showDate(year,month);
          //执行成功回调函数
          that.onLoadSuccess(result.data,xmlhttp);
          return;
          }

          //有数据则动态渲染到页面
          //先获取数据总条数
          that.total = result.total;

          //展示对应月份日历
          that.showDate(year,month);
          
          //展示数据
          that.viewData(result.data);

          //执行成功回调函数
          that.onLoadSuccess(result.data,xmlhttp);

          //将json数据赋值给当前对象
          that.data = result.data;
        },
        error: function(obj, status) {
          //执行失败回调函数
          that.onLoadError();
        }
      });
    },
    //展示数据
    viewData:function(data){
      //拿到数据之后先获取数据长度
      var len = data.length;
      //遍历添加对应类
      for(var i = 0;i < len;i++){
        console.log(data[i].diary);
        //如果当前日期已经完成日记 先添加success类
        if(data[i].diary === 1) $('[data-day="' + data[i].day + '"]').setAttribute('class',$('[data-day="' + data[i].day + '"]').getAttribute('class') + ' success');
        //如果当前日期被标记 添加tag类tag
        if(data[i].tag === 1) $('[data-day="' + data[i].day + '"]').setAttribute('class',$('[data-day="' + data[i].day + '"]').getAttribute('class') + ' tag');
        $('[data-day="' + data[i].day + '"]').setAttribute('data-id',data[i].id);
      }
    },
    //展示对应月份的日历
    showDate:function(year,month){
      //载入主题
      // this.loadTheme(month); 考虑移动到加载动画结束
      //获取当前时间
      var now = new Date();
      var now_year = now.getFullYear();
      var now_month = now.getMonth() + 1;
      var now_day = now.getDate();
      var flag = false; //标记位

      if(now_year === year && now_month === month) flag = true;

      //获取界面选中月份天数
      var go_month_days = this.getMonthDays(year,month);
      //获取界面选中月份第一天是周几
      var start = this.getMonthWeek(year,month);

      //打印界面选中年月
      this.printYearMonth(year,month);

      //获取界面月份1号是从周几开始的
      //如果界面月份为1 上月则为上年的最后一月
      if(month === 1){
        year -= 1;
        month = 12;
      }
      //获取上月的天数
      var prev_month_days = this.getMonthDays(year,month - 1);
      // console.log(prev_month_days);
      var spans = $('.diary-days span');
      var len = spans.length;
      var prev_month_start = prev_month_days - start + 1; //上月开始天数

      // 遍历生成上月的天数 不可用 仅展示
      for(var i = 0;i < start;i++){
        spans[i].setAttribute('class','disabled');
        spans[i].setAttribute('data-day',''); //设置data-day属性为空
        spans[i].setAttribute('data-id',''); //设置data-id属性为空
        spans[i].innerText = prev_month_start.toString();
        prev_month_start++;
      }
      
      // 遍历生成将要显示的月份天数
      for(var i = 0;i < go_month_days;i++){
        var temp = i + 1;
        spans[start].innerText = temp.toString();
        spans[start].setAttribute('class','diary-item');
        spans[start].setAttribute('data-day',temp);
        spans[start].setAttribute('data-id',''); //设置data-id属性为空
        start++;
      }

      //年月与现在相同 需要选中当前天
      if(flag) $('[data-day="' + now_day + '"]').setAttribute('class',$('[data-day="' + now_day + '"]').getAttribute('class') + ' active'); //选择对应日期加上active

      var temp = 0;
      //遍历生成下月天 不可用 仅展示
      for(var i = start;i < len;i++){
        temp++;
        spans[i].setAttribute('class','disabled');
        spans[i].setAttribute('data-day',''); //设置data-day属性为空
        spans[start].setAttribute('data-id',''); //设置data-id属性为空
        spans[i].innerText = temp.toString();
      }

      // console.log(this.getActiveBox());
      //如果前一个活动界面是loading界面
      if(this.getActiveBox() === 'diary-loading'){
        //强制用户等待一秒
        this.timer = setTimeout(function(){

        this.loadTheme(month);
        //设置天数为活动界面
        this.setActiveClass('.diary-days');
        clearTimeout(this.timer);
        }.bind(this),this.wait);
        return;
      }
      
      //设置天数为活动界面
      this.setActiveClass('.diary-days');
    },
    //获取任意月份的天数
    getMonthDays:function(year,month){
      //传入年份和月份 获取该年对应月份的天数
      var date = new Date(year,month,0); //当天数为0 js自动处理为上一月的最后一天
      return date.getDate();
    },
    //获取任意月份1号是从周几开始的
    getMonthWeek:function(year,month){
      //javascript月份是从0开始的
      var date = new Date(year,month - 1,1);
      return date.getDay() === 0 ? 7:date.getDay() - 1;
    },
    //绑定上一月按钮
    bindPrevBtn:function(){
      //保存this指向
      var that = this;
      $('.diary-prev').on('click',function(){
      //如果当前活动界面是loading界面 直接返回
      if(that.getActiveBox() === 'diary-loading') return;
        //获取点击时的年份
      var year = Number($('#year').innerText);
      //点击时的月份
      var month = Number($('#month').innerText);
      if(year <= 1970) return;
      //上一月动画特效
      that.setPrevAnimate();
      //开启加载动画
      that.showLoading();
      //如果是一月 则需要跳转到前年12月
      month === 1 ? that.getData(--year,12):that.getData(year,--month);
      });
    },
    //绑定下一月按钮
    bindNextBtn:function(){
      //保存this指向
      var that = this;
      $('.diary-next').on('click',function(){
      //如果当前活动界面是loading界面 直接返回
      if(that.getActiveBox() === 'diary-loading') return;
        //获取点击时的年份
      var year = Number($('#year').innerText);
      //点击时的月份
      var month = Number($('#month').innerText);
      if(year >= 2119) return;
      //下一月动画特效
      that.setNextAnimate();
      //展示载入动画
      that.showLoading();
      //如果是一月 则需要跳转到下年1月
      month === 12 ? that.getData(++year,1):that.getData(year,++month);
      });
    },
    //绑定返回当月的按钮
    bindCurrentBtn:function(){
      //保存this指向
      var that = this;
      $('.diary-today').on('click',function(){
        //如果当前活动界面是loading界面 直接返回
      if(that.getActiveBox() === 'diary-loading') return;
      //获取点击时的年份
      var year = Number($('#year').innerText);
      //点击时的月份
      var month = Number($('#month').innerText);
       //获取当前时间 年月
       var now = new Date();
       var now_year = now.getFullYear();
       var now_month = now.getMonth() + 1;
       //如果已经是当前年月 返回
       if(year === now_year && month === now_month) return;
       //加载动画
       that.showLoading();
       //否则展示当前年月
       that.getData(now_year,now_month);
      });
    },
    //绑定年份按钮
    bindYearBtn:function(){
      //保存this指向
      var that = this;
      $('#year').on('click',function(){
        //如果前一界面是loading界面 直接返回
        if(that.getActiveBox() === 'diary-loading') return;
        //如果已经是选择年份界面 直接返回 避免重复点击
        if(that.getActiveBox() === 'diary-select-years') return;
        //获取点击时的年份
        var year = Number($('#year').innerText);
        that.createYears(year);
        });
    },
    //绑定月份按钮
    bindMonthBtn:function(){
      //保存this指向
      var that = this;
      $('#month').on('click',function(){
        //如果前一界面是loading界面 直接返回
        if(that.getActiveBox() === 'diary-loading') return;
        //如果已经是选择月份界面 直接返回 避免重复点击
        if(that.getActiveBox() === 'diary-select-months') return;
        //点击时的月份
        var month = Number($('#month').innerText);
        that.setActiveMonth(month);
        });
    },
    //绑定年份选择界面按钮
    bindSelectYears:function(){
      var that = this;

      for(var i = 0;i < 10;i++){
        //绑定年份项点击事件 绑定的也是父元素
      $('.years-item')[i].parentNode.onclick = function(){
        //如果点击的是当前月份 隐藏月份选择界面后返回
        if(this.getElementsByClassName('years-item')[0].getAttribute('class').indexOf('active') !== -1){
          that.setActiveClass('.diary-days');
          return;
        }

        //如果前一界面是loading界面 直接返回
        if(that.getActiveBox() === 'diary-loading') return;

        //获取点击的年份
        var year = Number(this.innerText);
        var month = Number($('#month').innerText);
        //展示loading动画
        that.showLoading();
        //展示对应年份
        that.getData(year,month);
      };
      }
      

      //绑定上一页年份点击事件(父元素)
      $('#prev-years').parentNode.onclick = function(){
        var year = Number($('.years-item')[0].innerText) - 10;
        that.createYears(year);
      }
      //绑定下一页年份点击事件(父元素)
      $('#next-years').parentNode.onclick = function(){
        var year = Number($('.years-item')[0].innerText) + 10;
        that.createYears(year);
      }
    },
    //绑定月份选择界面按钮
    bindSelectMonths:function(){
      var that = this;
      for(var i = 0;i < 12;i++){
        //绑定的也是父元素
        $('.months-item')[i].parentNode.onclick = function(){
          //如果点击的是当前月份 隐藏月份选择界面后返回
          if(this.getElementsByClassName('months-item')[0].getAttribute('class').indexOf('active') !== -1){
            that.setActiveClass('.diary-days');
            return;
          }

          //如果前一界面是loading界面 直接返回
        if(that.getActiveBox() === 'diary-loading') return;
          
         var year = Number($('#year').innerText);
         //获取点击的月份
         var month = parseInt(this.innerText);
          
         //开启loaidng动画
         that.showLoading();
          //展示对应月份
          that.getData(year,month);
        }
      }
    },
    //绑定天按钮事件
    bindDayBtn:function(){
      //保存this指向
      var that = this;
      //获取所有的天数按钮
      var spans = $('.diary-days').getElementsByTagName('span');
      var len = spans.length;
      //遍历绑定
      for(var i = 0;i < len;i++){
        spans[i].onclick = function(){
          var classes = this.getAttribute('class');
          //如果当前天数被禁用 直接返回
          if(classes === 'disabled') return;
          var id = 0,tag = 0,diary = 0;
          //根据当前日期的类 准备弹窗按钮
          if(classes.indexOf('success') !== -1) diary = 1;
          if(classes.indexOf('tag') !== -1) tag = 1;
          if(this.hasAttribute('data-id') && this.getAttribute('data-id').length !== 0) id = Number(this.getAttribute('data-id'));
          //获取当前年月日
          var year = $('#year').innerText;
          var month = $('#month').innerText;
          var day = this.innerText;
          if(day.length === 1) day = '0' + day;
          //准备弹窗
          that.helpDialog(id,tag,diary,year + month + day);
          //显示弹窗
          that.showDialog();
        }
      }
    },
    //创建待选择的年份
    createYears:function(year){
      //获取显示年份
      var show_year = Number($('#year').innerText);
      //遍历生成
      //计算开始年份 整十开始 如2025 则以2020年开始
      var start = year - (year % 10);
      //如果小于或等于1970年 或者大于2120年 直接返回 没有必要进行展示
      if(year < 1970 || year >= 2120) return;
      var active = $('.diary-select-years').getElementsByClassName('active'); //获取当前可能存在的活动年份 也就是当前年份
      if(active.length !== 0) active[0].setAttribute('class','years-item');
      for(var i = 0;i < 10;i++){
        if(start === show_year) $('.years-item')[i].setAttribute('class','years-item active');
        $('.years-item')[i].innerText = start.toString();
        start++;
      }
      //展示选择年份界面
      this.setActiveClass('.diary-select-years');
    },
    //设置活动的月份
    setActiveMonth:function(month){
      //先去除可能存在的活动月份
      var active = $('.diary-select-months').getElementsByClassName('active');
      if(active.length !== 0) active[0].setAttribute('class','months-item');
      //设置活动月份
      $('.months-item')[month - 1].setAttribute('class','months-item active');
      //展示选择月份界面
      this.setActiveClass('.diary-select-months');
    },
    /*
     * 准备弹窗
     * @param number id 记录id
     * @param number tag 标记状态 0未标记/1已标记
     * @param number diary 日记状态 0未完成/1完成
     * @param string date 日期 八位年月日
     */
    helpDialog:function(id,tag,diary,date){
      $('#view-btn').setAttribute('data-id',id);
      $('#view-btn').setAttribute('data-tag',tag);
      $('#view-btn').setAttribute('data-diray',diary);
      $('#view-btn').setAttribute('data-date',date);
      //根据日记状态 则go_btn为添加
      if(diary === 1){
        //编辑状态
        $('#go-btn').setAttribute('data-mode',1);
        $('#go-btn .mdi').innerText = '编辑';
      }else{
        //添加状态
        $('#go-btn').setAttribute('data-mode',0);
        $('#go-btn .mdi').innerText = '添加';
      }

      //根据标记状态 则tag-btn添加
      if(tag === 1){
        $('#tag-btn').setAttribute('data-mode',1);
        $('#tag-btn .mdi').innerText = '取消';
      }else{
        $('#tag-btn').setAttribute('data-mode',0);
        $('#tag-btn .mdi').innerText = '标记';
      }
    },
    //显示弹窗
    showDialog:function(){
      $('body').setAttribute('style','overflow:hidden;');
      $('.hey-dialog').show();
    },
    //隐藏弹窗
    hideDialog:function(){
      $('body').setAttribute('style','');
      $('.hey-dialog').hide();
    },
    //绑定弹窗按钮事件
    bindDialogBtn:function(){
      var that = this;
      //关闭按钮
      $('.hey-dialog-close').on('click',function(){
        that.hideDialog();
      });

      //其他按钮事件放到外面去绑定 方便调整
    }
  }
  return new MyDiary(options);
}