/**
 * 用原生js模拟封装bootstrap-table插件到my-jquery中
 * @author:hei-jack
 * GMT2021-06-25
 */
'use strict';
if($ === undefined) throw new Error('my jquery is not found!');
$.table = function(options){
  function MyTable(options){
    if(options === undefined)  throw(new Error('options is empty!'));
    if(typeof(options) !== 'object')  throw(new Error('options is error!'));
    if(options.id === undefined) throw(new Error('id is empty!'));
    this.el = $(options.id); //表格元素
    if(options.classes === undefined) options.classes = '';
    this.classes = options.classes; //设置表格的样式
    this.limit = options.limit === undefined ? 10:options.limit; //获取记录条数
    if(options.url === undefined) throw(new Error('url is error!'));
    this.url = options.url; //请求地址
    if(options.method === undefined) options.method = 'GET';
    this.method = options.method; //请求方式
    this.ajaxOptions = options.ajaxOptions === undefined ? null:options.ajaxOptions; //ajax请求的头部
    if(options.queryParams === undefined) throw(new Error('queryParams is empty!'));
    this.queryParams = options.queryParams; //ajax请求参数
    this.dataType = options.dataType === undefined ? 'json':options.dataType; //期待返回参数
    this.pagination = options.pagination === undefined ? true:options.pagination; //是否显示分页
    this.sortOrder = options.sortOrder === undefined ? 'asc':options.sortOrder.toLowerCase(); //升序还是降序 toLowerCase转为小写
    if(options.columns === undefined) throw(new Error('columns is empty!'));
    this.columns = options.columns; //columns 数组 array
    this.col_len = this.columns.length; //字段长度
    this.padding = options.padding === undefined ? false:options.padding; //当返回数据条数小于limit时 是否填充剩下的行数 默认不填充
    if(options.onLoadSuccess === undefined) options.onLoadSuccess = function(){}
    this.onLoadSuccess = options.onLoadSuccess; //载入成功事件
    if(options.onLoadError === undefined) options.onLoadError = function(){}
    this.onLoadError = options.onLoadError;//载入失败事件
    this.firstFlag = true; //首次ajax标记位
    this.init();
  }
  //拓展方法
  MyTable.prototype = {
    constructor: MyTable,
    //初始化表格
    init:function(){
      this.setClasses();
      this.createThead();
      this.loading(); //先加载载入效果
      this.getData();  //ajax获取数据
      //回调
    },
    //设置表格样式
    setClasses:function(){
      this.el.setAttribute('class',this.classes);
    },
    //创建表头
    createThead:function(){
      var thead = document.createElement('thead');
      var tr = document.createElement('tr');
      var th;
      for(var i = 0;i < this.col_len;i++){
        //遍历columns创建表头
        th = document.createElement('th');
        th.setAttribute('data-field',this.columns[i].field);
        th.textContent = this.columns[i].title;
        if(this.columns[i].align !== undefined) th.setAttribute('class','text-' + this.columns[i].align);
        tr.appendChild(th);
      }
      thead.appendChild(tr);
      this.el.appendChild(thead);
    },
    //载入状态
    loading:function(){
      //创建载入状态
      this.tbody = document.createElement('tbody');
      //创建tr元素
      var tr = document.createElement('tr');
      tr.setAttribute('class','table-loading');
      var td = document.createElement('td');
      td.setAttribute('class','text-center');
      td.setAttribute('colspan',this.col_len);
      td.textContent = '正在加载数据中，请稍等......';
      tr.appendChild(td);
      this.tbody.appendChild(tr);
      this.el.appendChild(this.tbody);
      var _flag = false; //加载动画的标记位 false表示减少 true增加
      this.timer = setInterval(function(){
        var len = td.innerText.length;
        if(_flag){
          //如果是增加
          td.innerText = td.innerText + '.';
          if(len === 16) _flag = false;
        }else{
          td.innerText = td.innerText.slice(0, len - 5);
          _flag = true;
        }
      },500);
    },
    //ajax请求数据
    getData:function(page){
      if(this.firstFlag){
        //首次ajax请求
        this.now_page = 1;
      }else{
        this.now_page = page;
      }

      var params = this.queryParams({limit: this.limit,page: this.now_page});  //首次请求参数
      var that = this; //保存当前this指向
      //如果是函数 就动态执行该函数 如果不是函数就直接设置为该项的值
      var ajaxOptions = typeof(this.ajaxOptions) === 'function' ? this.ajaxOptions():this.ajaxOptions;
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
          that.showMessage(false); //说明返回数据不合法 返回数据失败
          //执行失败回调函数
          that.onLoadError();
          return;
          }

          // console.log(result.data);

          //如果合法 但是没有数据 直接返回当前数据为空
          if(result.total === 0 || result.data === null || result.data.length === 0){
          that.showMessage(true); //数据为空
          //执行失败回调函数
          that.onLoadError(result.data);
          return;
          }

          //有数据则动态渲染到页面
          //先获取数据总条数
          that.total = result.total;

          var pages;

          //计算数据总页码数
          pages = that.total % that.limit === 0 ? that.total / that.limit : Math.floor(that.total / that.limit) + 1;
          //如果不是第一次请求 且总页码发生变化
          if(that.pages !== pages && !that.firstFlag){
            that.showMessage(false); //说明返回数据不合法 返回数据失败
            //执行失败回调函数
            that.onLoadError();
            return;
          }
          that.pages = pages; //赋值

          that.removeLoading();  //移除加载动画

          
          //展示数据
          that.showData(result.data);

          //执行成功回调函数 新增xmlhttp
          that.onLoadSuccess(result.data,xmlhttp);

          //将json数据赋值给当前对象
          that.data = result.data;
        },
        error: function(obj, status) {
          //请求失败之后返回请求数据失败
          that.showMessage(false);
          //执行失败回调函数
          that.onLoadError();
        }
      });
    },
    //移除加载状态
    removeLoading:function(){
      //移除计时器
      clearInterval(this.timer);
      //移除表格体
      this.tbody.removeChild($('.table-loading'));
    },
    //表格获取数据失败状态展示 flag为false表示获取数据失败 true表示获取数据为空
    showMessage:function(flag){
      // 移除计时器
      clearInterval(this.timer);
      // 将提示语更改为失败提示语
      $('.table-loading td').innerText = flag ? '数据空空如也，赶紧去添加吧~':'数据获取失败，请检查网络状态或刷新重试~';
    },
    //展示数据
    showData:function(data){
      // console.log(data);
      data = this.sortData(data); //对数据进行排序
      var len = data.length;
      //如果是初次请求 创建分页等标签
      if(this.firstFlag){
        //计算初次返回的数据开始与结束
        this.start = 1;
        this.end = len;
        this.createPagination();
        this.firstFlag = false; //首次创建之后 更改标记位
      }else{
        //计算展示开始到结束记录
        this.start = (this.limit * (this.now_page - 1)) + 1; //当前页码乘以限制记录条数 +1 等于开始条数
        this.end = this.start + len - 1;
        this.updatePagination(this.now_page); //更新页码
        //更新记录数
        this.updateRecord();
      }

      //保存this指向
      var table = this;

      //追加数据
      //表单设置的字段数据

      //遍历返回的数据创建对应元素
      for(var i = 0;i < len;i++){
        var tr = document.createElement('tr');
        tr.setAttribute('data-index',i); //设置data-index属性
        this.tbody.appendChild(tr); //追加tr
        for(var j in this.columns){
          var field = this.columns[j].field;
          var td = document.createElement('td');

          //设置单元格样式
          if(this.columns[j].align !== undefined){
            td.setAttribute('class','text-' + this.columns[j].align);
          }

          //遍历json数据 
          // if(data[i][field]){ //旧写法 当该项值为0时就傻了
          if(data[i].hasOwnProperty(field)){
            //如果该字段数据存在 则插入数据
            td.innerHTML = data[i][field];
          }else{
            td.innerHTML = ''; //设置为空
          }

          //如果格式化函数不为空 回调该函数
          if(this.columns[j].formatter !== undefined) td.innerHTML = this.columns[j].formatter(data[i][field],data[i],i); //value,row,index

           //如果存在绑定事件对象 开始进行绑定事件操作
           if(this.columns[j].events !== undefined){
             
            var _events = this.columns[j].events;
            for(var t in _events){
              //暂时只允许点击事件
              var _els = td.getElementsByClassName(t.replace('click .',''))[0]; //元素

              //此处直接使用var 会产生i值覆盖问题 也就是i不是等于当前i 而是最后的一个i值（返回数据长度） 使用let可以解决 但ie9不支持
              //使用自调用的匿名函数内嵌一个闭包，可以完美解决var for循环变量传值的问题
              (function(n,t){
              var value = data[n][field];
              var row = data[n];
              //绑定事件
              _els.onclick = function(event){
                _events[t](event,value,row,table); //回调该函数
              }
              })(i,t);
              
            }
           }


        //将td追加到tr中
        tr.appendChild(td);
        }
      }

      //为了美观 如果返回数据数小于limit 如果已经开启补齐 则补齐余下的行数占位
      if(this.padding && len < this.limit){
        var diff = this.limit - len;
        for(var i = 0;i < diff;i++){
          var tr = document.createElement('tr');
          tr.setAttribute('style','border:none;');
          this.tbody.appendChild(tr); //追加tr
          var td = document.createElement('td');
          td.innerHTML = '&nbsp;';
          td.setAttribute('colspan',this.col_len);
          // td.setAttribute('style','background: rgba(0,0,0,.001);boder:none;');
          tr.appendChild(td);
        }
      }
    },
    //移除原有数据
    removeData:function(){
      //只移除数据 一条一条移除数据会导致tbody残留
      /*var trs = this.tbody.getElementsByTagName('tr');
      var len = trs.length;
      if(len === 0) return false;
      //遍历移除 从后往前移除 否则会出错
      for(var i = len - 1;i >= 0;i--){
        this.tbody.removeChild(trs[i]);
      }*/

      this.el.removeChild(this.tbody);
    },
    //创建分页
    createPagination:function(){
      var footer = document.createElement('div');
      footer.setAttribute('class','table-footer');
      var fixed = document.createElement('div');
      fixed.setAttribute('class','fixed-table-pagination');
      footer.appendChild(fixed);
      //左侧记录信息
      var float_left = document.createElement('div'); //左侧盒子
      float_left.setAttribute('class','float-left pagination-detail');
      fixed.appendChild(float_left);
      var info = document.createElement('span');
      info.setAttribute('class','pagination-info');
      info.innerText = '显示第 ' + this.start + ' 到第 ' + this.end + ' 条记录，总共 ' + this.total + ' 条记录';
      float_left.appendChild(info);
      var list = document.createElement('span');
      list.setAttribute('class','page-list');
      list.innerHTML = '每页显示 <span class="btn-group dropdown dropup">' + '<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">' + '<span class="page-size">' + this.limit + '</span><span class="caret"></span></button></span> 条记录';
      info.appendChild(list);
      //右侧页码
      var float_right = document.createElement('div');
      float_right.setAttribute('class','float-right pagination');
      fixed.appendChild(float_right);
      var ul = document.createElement('ul');
      ul.setAttribute('class','pagination');
      float_right.appendChild(ul);
      //上一页
      var page_pre = document.createElement('li');
      page_pre.setAttribute('class','page-item page-pre');
      ul.appendChild(page_pre);
      var page_link = document.createElement('a');
      page_link.setAttribute('class','page-link');
      page_link.setAttribute('aria-label','上一页');
      page_link.setAttribute('href','javascript:void(0);');
      page_link.innerText = '<';
      page_pre.appendChild(page_link);
      //生成页码
      if(this.pages <= 7){
        for(var i = 0;i < this.pages;i++){
        var page = i + 1;
        var li = document.createElement('li');
        if(page === this.now_page){
          li.setAttribute('class','page-item active');
        }else{
          li.setAttribute('class','page-item');
        }
        var link = document.createElement('a');
        link.setAttribute('class','page-link');
        link.setAttribute('data-page',page);
        link.setAttribute('href','javascript:void(0);');
        link.innerText = String(page); //页码
        li.appendChild(link);
        ul.appendChild(li);
       }
      }else{
        //大于7页则隐藏倒数第二页
      for(var i = 0;i < this.pages;i++){
        var page = i + 1;
        var li = document.createElement('li');
        if(page === this.now_page){
          li.setAttribute('class','page-item active');
        }else{
          if(page === 6){
            //隐藏第6页页码 处理为省略号 禁用按钮
            li.setAttribute('class','page-item page-last-separator disabled');
          }else{
            li.setAttribute('class','page-item');
          }
        }
        var link = document.createElement('a');
        link.setAttribute('class','page-link');
        if(page !== 6){
          link.setAttribute('data-page',page);
          link.innerText = String(page); //页码
        }else{
          link.setAttribute('data-page','');
          link.innerText = '...'; //页码
        }
        if(page === 7){
          link.setAttribute('data-page',this.pages);
          link.innerText = String(this.pages); //页码
        }
        link.setAttribute('href','javascript:void(0);');
        li.appendChild(link);
        ul.appendChild(li);

        if(i === 6) break;  //打断循环
       }
      }

      //下一页
      var page_next = document.createElement('li');
      page_next.setAttribute('class','page-item page-next');
      ul.appendChild(page_next);
      page_link = document.createElement('a');
      page_link.setAttribute('class','page-link');
      page_link.setAttribute('aria-label','下一页');
      page_link.setAttribute('href','javascript:void(0);');
      page_link.innerText = '>';
      page_next.appendChild(page_link);
      //将footer追加到页面中
      $('.table-container').appendChild(footer);

      this.bindPageClick();
    },
    //更新页码
    updatePagination:function(page){
      //先移除原有active页码
      $('ul.pagination .active').setAttribute('class','page-item');
      var pages = $('ul.pagination .page-item');
      var len = pages.length;
      if(this.pages <= 7){
        //如果总页码小于等于7 直接改变active即可
        for(var i = 0;i < len;i++){
          var a = pages[i].getElementsByTagName('a')[0];
          if(Number(a.getAttribute('data-page')) === page){
            pages[i].setAttribute('class','page-item active');
          }
        }
      }else{
        //如果总页码大于7 分情况
        if(page <= 4){
          //情况一 传入的页码小于等于4
          try{
             //取消正序的禁用 因为my-jquery可能会报错 所以捕捉错误 然后忽略错误
          $('.page-first-separator').setAttribute('class','page-item');
          }catch(e){}

          //设置倒数第二位的禁用
          pages[6].getElementsByTagName('a')[0].setAttribute('data-page',''); //设置属性为空
          pages[6].getElementsByTagName('a')[0].innerText = '...'; //设置页码为省略号
          pages[6].setAttribute('class','page-item page-last-separator disabled'); //设置样式

          //先更新页码（除开上一页之外的5位）
          for(var i = 1;i < len -3;i++){
            //更新页码
            var a = pages[i].getElementsByTagName('a')[0];
            a.innerText = i;
            a.setAttribute('data-page',i);
            if(i === page) pages[i].setAttribute('class','page-item active'); //改变active
          }

        }else if(page > 4 && page < this.pages - 3){
          //情况二 传入页码大于4且小于总页码减3
          //将正序第二页码（不包括上一页按钮）
          pages[2].getElementsByTagName('a')[0].setAttribute('data-page',''); //设置属性为空
          pages[2].getElementsByTagName('a')[0].innerText = '...'; //设置页码为省略号
          pages[2].setAttribute('class','page-item page-first-separator disabled'); //设置样式

          //将倒数第二页码（不包含下一页按钮)
          pages[6].getElementsByTagName('a')[0].setAttribute('data-page',''); //设置属性为空
          pages[6].getElementsByTagName('a')[0].innerText = '...'; //设置页码为省略号
          pages[6].setAttribute('class','page-item page-last-separator disabled'); //设置样式

          var start = page - 1; //开始页码

          //更新页码中间页码（中间三个 索引3-5)
          for(var i = 3;i < len - 3;i++){
            pages[i].getElementsByTagName('a')[0].setAttribute('data-page',start);
            pages[i].getElementsByTagName('a')[0].innerText = start;
            if(i === 4) pages[i].setAttribute('class','page-item active'); //改变active
            start++; //开始页码自增
          }

        }else if(page >= this.pages - 3){
          //情况三 传入页码大于等于总页码减3

          //将正序第二页码（不包括上一页按钮）
          pages[2].getElementsByTagName('a')[0].setAttribute('data-page',''); //设置属性为空
          pages[2].getElementsByTagName('a')[0].innerText = '...'; //设置页码为省略号
          pages[2].setAttribute('class','page-item page-first-separator disabled'); //设置样式

          try{
             //取消倒数第二的禁用 因为my-jquery可能会报错 所以捕捉错误 然后忽略错误
          $('.page-last-separator').setAttribute('class','page-item');
        }catch(e){}
         

          //更新最后5个页码(不包含下一页按钮)
          var start = this.pages - 3 - 1; //开始页码

          //更新页码中间页码（中间三个 索引3-5)
          for(var i = 3;i < len - 1;i++){
            pages[i].getElementsByTagName('a')[0].setAttribute('data-page',start);
            pages[i].getElementsByTagName('a')[0].innerText = start;
            if(start === page) pages[i].setAttribute('class','page-item active'); //改变active
            start++; //开始页码自增
          }
        }
      }
    },
    //移除原有页码
    removePagination:function(){
      //如果table-footer不存在 myjquery会报错  直接忽略即可
      try{
        $('.table-container').removeChild($('.table-footer'));
      }catch(e){}
    },
    //绑定页码点击事件
    bindPageClick:function(){
      //获取所有页码相关元素
      var _els = $('.page-item');
      var len = _els.length;
      var that = this; //存储this指向
      //for循环遍历绑定一次性事件
      for(var i = 0;i < len;i++){
        //此处直接使用var 会产生i值覆盖问题 也就是i不是等于当前i 而是最后的一个i值（返回数据长度） 使用let可以解决 但ie9不支持
        //使用自调用的匿名函数内嵌一个闭包，可以完美解决var for循环变量传值的问题
        (function(n){
          _els[n].onclick = function(){
            var classes = this.getAttribute('class');
            //如果发现是禁用的省略号按钮 直接返回 如果发现是活动页 也直接返回
            if(classes.indexOf('disabled') !== -1 || classes.indexOf('active') !== -1 || that.pages === 1) return false;
            
            //先移除原有数据 并且开启加载动画
            that.removeData();

            //开启加载动画
            that.loading();

            //如果是上一页按钮
            if(classes.indexOf('page-pre') !== -1){
              //发送ajax请求
              //先获取当前选中的页码 如果当前页码是第一页就回到总页码最后一页 否则-1
              that.now_page === 1 ? that.getData(that.pages):that.getData(that.now_page - 1);
              return true;
            }
            //如果是下一页按钮
            if(classes.indexOf('page-next') !== -1){
              
              //先获取当前选中的页码 如果等于总页码 就回到第一页 否则+1
              that.now_page === that.pages ? that.getData(1):that.getData(that.now_page + 1);
              return true;
            }

            //如果是普通页码按钮
            that.getData(Number(this.getElementsByTagName('a')[0].getAttribute('data-page')));
            //this.onclick = null;
            return true;
          }
        })(i);

      }
    },
    //更新页码右侧记录数
    updateRecord:function(){
      //移除原有元素
      $('.pagination-detail').removeChild($('.pagination-info'));

      //重新创建
      var info = document.createElement('span');
      info.setAttribute('class','pagination-info');
      info.innerText = '显示第 ' + this.start + ' 到第 ' + this.end + ' 条记录，总共 ' + this.total + ' 条记录';
      $('.pagination-detail').appendChild(info);
      var list = document.createElement('span');
      list.setAttribute('class','page-list');
      list.innerHTML = '每页显示 <span class="btn-group dropdown dropup">' + '<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">' + '<span class="page-size">' + this.limit + '</span><span class="caret"></span></button></span> 条记录';
      info.appendChild(list);
    },
    //对数据进行排序 以第一个字段为排序字段（如id）
    sortData:function(data){
      var len = data.length;
      var sortField;
      var field = this.columns[0].field;
      if(this.sortOrder === 'desc'){
        sortField = function(a,b){
          return b[field] - a[field];
        };
      }else{
        //防止用户传入莫名其妙的字符串 统一按asc升序搞
        sortField = function(a,b){
          return a[field] - b[field];
        };
      }
      return data.sort(sortField);
    },
    //刷新表格
    refresh:function(){
        //先移除原有数据
        this.removeData();
        //开启加载动画
        this.loading();
        //重新获取本页数据
        this.getData(this.now_page); 
    },
    //重启表格（从第一页开始请求）
    restart:function(){
      //先移除原有数据
      this.removeData();
      //开启加载动画
      this.loading();
      //重置为第一次
      this.firstFlag = true;
      //移除原有页码
      this.removePagination();
      //重置页码
      this.now_page = 1;
      //重新获取数据
      this.getData(this.now_page);
    }
  }
  return new MyTable(options);
};


/* 用法示例

//初始化表格
        $.table({
          id: '#user-log', //id
          url: '/result.php?len=10', //url
          method: 'post', //post
          pagination: true, //分页
          limit: 10, //返回数据量
          //sortOrder: 'desc', //asc升序 desc降序
          queryParams:function(params){
            var temp = {
                        limit: params.limit,         // 每页数据量
                        page: params.page,  //页码
                        //id: Number(c_id),
                        //search: params.search,
                        //token: $('#token').val(),
                        //offset: params.offset,       // sql语句起始索引
                        //sort: params.sort,           // 排序的列名
                        //sortOrder: params.order      // 排序方式'asc' 'desc'
                    };
            return temp;
          },
          //表头
          columns: [{
            field: 'id',
            title: 'ID',
            align: 'center', //居中
          }, {
            field: 'ID1',
            title: 'ID1',
            align: 'center', //居中
            formatter: function(value, row, index) {
              var value = "";
              value = '<a type="button" class="btn btn-default btn-xs m-r-5 tooltip btn_del"><span class="mdi mdi-window-close" aria-hidden="true"></span><span class="tooltip-text tooltip-top">删除</span></a>';
              return value;
            },
            events: {
              //第四个参数为当前表格对象
              'click .btn_del': function(event, value, row, table) {
                //editUser(row.id);
                console.log(event);
                console.log(row.id);
                //console.log(this); this指向当前对象也就是events
                table.refresh(); //刷新当前表格
              }
            }
          },{
            field: 'ID2',
            title: 'ID2',
            align: 'center', //居中
          },{
            field: 'ID3',
            title: 'ID3',
            align: 'center', //居中
          },{
            field: 'ID4',
            title: 'ID4',
            align: 'center', //居中
          },{
            field: 'ID5',
            title: 'ID5',
            align: 'center', //居中
          },],
          onLoadSuccess: function(data) {
            console.log('获取数据成功');
          },
          onLoadError: function() {
            console.log('获取数据失败');
          },
        });*/