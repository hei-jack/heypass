//滑块验证对象
function NoCaptcha() {
  //创建要返回的对象
  var o = new Object();

  //可以在这里定义私有变量
  var oParent = $(".no-captcha");
  var oBg = $(".no-captcha-bg");
  var oText = $(".no-captcha-text");
  var oBtn = $(".no-captcha-btn");
  var success = false; //判断验证是否成功
  var flag = false; //标记位 用来判断是否需要执行事件
  var distance = oParent.offsetWidth - oBtn.offsetWidth; //验证成功的距离 offsetWidth 水平方向 width + 左右padding + 左右border-width
  var startX;
  var is_mobile = isMobile(); //判断是否移动端
  //私有方法
  //检测是否是移动端
  function isMobile() {
    return navigator.userAgent.match(/(iPhone|iPad|Android|ios)/i) ? true : false;
  }

  //pc端执行主要函数
  function goPc(func) {
    /*兼容性写法 去除浏览器（主要是ie）中h5拖拽事件的默认行为 ie9和ie10会出现onmouseup事件无法触发 */
    document.getElementsByClassName('no-captcha')[0].ondragstart = function(event) {
      event.preventDefault ? event.preventDefault() : event.returnValue = false;
    };
    document.getElementsByClassName('no-captcha')[0].ondragend = function(event) {
      event.preventDefault ? event.preventDefault() : event.returnValue = false;
    };

    //当按钮鼠标按下时
    oBtn.onmousedown = function(eve) { //给物块设置鼠标按下事件
      //在点击事件按下后 清除后面设置的transition属性
      oBg.style.transition = ""; 
      oBtn.style.transition = "";
      var e = eve || window.event;
      startX = e.clientX; //获取鼠标刚按下时的坐标 相对于浏览器页面

      //当鼠标移动时
      document.onmousemove = function(eve) {
        var e = eve || window.event;
        var moveX = e.clientX; 
        var offsetX = moveX - startX; //物块移动的距离

        if (offsetX > distance) { //如果移动的距离已经大于本应该移动的距离 那么就将它设置为验证成功时的距离
          offsetX = distance;
        } else if (offsetX < 0) { //同样 如果移动的距离小于0时 将它设置为0 保证不会超出范围
          offsetX = 0;
        }
        oBtn.style.left = offsetX + "px";
        oBg.style.width = offsetX + "px";
        if (offsetX == distance) { //判断验证通过
          oText.innerHTML = "验证通过";
          oBtn.getElementsByTagName('img')[0].setAttribute('src','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAA/0lEQVRIS+2TMW7CQBBF/1wnnCAXcZOCgiJFCq/kFBRIQJGCIpq/BRQpUqRIES5BxRG4jbXISEZrAh5bNkJIbL373uyfGcGVj1yZj4fATPi+InLOzQBMSR4L7+0HJRzAnGQhOpxeBBH8g+QkbkxnQQkXkYWqjk+7XhGkaboEsPHer83xABDBP1X1/dybisA5twXwHEJILEkUiyfpLhVUEWRZ9pTn+R+AQZ2khIcQVt77t7rf/uuBJYkq/yL5akV5tsmXJBH8m+TIgteO6alERAbFEgH4ITlsAjf3IJYcLov8qupLU7gpKC5Ekh3JpA28kaAtsHbRusLMRXsIbhLRHrN6eRnHDVHLAAAAAElFTkSuQmCC');
          oText.style.color = "#FFF";
          //oBtn.style.color = "rgb(39, 233, 21)";
          success = true; //验证通过时的条件
          document.onmousemove = null; //验证通过后 鼠标按下事件和鼠标移动都没用了 因此需要清除
          oBtn.onmousedown = null;
          setTimeout(func, 10);
        }
      }

      //当按钮鼠标抬起时
      document.onmouseup = function() { //这里也是给document设置鼠标抬起事件
        if (success) { //如果已经验证成功了 那么结束函数
          return;
        } else { //反之 验证没有通过 则物块原来的位置
          oBtn.style.left = 0;
          oBg.style.width = 0;
          oBg.style.transition = "width 1s ease";
          oBtn.style.transition = "left 1s ease";
        }
        document.onmousemove = null; //返回到原来的位置过程中 需要清除鼠标移动事件和鼠标抬起事件
        oBtn.onmouseup = null;
      }
    }
  }

  //移动端执行函数
  function goMobile(func) {
    /*
    oBtn.addEventListener('touchstart', function(eve) {
      oBg.style.transition = ""; //在点击事件按下后 必须清除后面设置的transition属性 否则会造成物块移动的bug 具体可自行测试
      oBtn.style.transition = "";
      var e = eve.touches[0];
      startX = e.clientX; //获取鼠标刚按下时的坐标 相对于浏览器页面
    }, false);
    */

    oBtn.ontouchstart = function(eve) {
      oBg.style.transition = ""; //在点击事件按下后 清除后面设置的transition属性
      oBtn.style.transition = "";
      var e = eve.touches[0];
      startX = e.clientX; //获取鼠标刚按下时的x坐标 相对于浏览器页面
    };

    oBtn.ontouchend = function() {
      if (success) { //如果已经验证成功了 那么结束函数
        return;
      } else { //反之 验证没有通过 则物块原来的位置
        oBtn.style.left = 0;
        oBg.style.width = 0;
        oBg.style.transition = "width 1s ease";
        oBtn.style.transition = "left 1s ease";
      }
    };
    /*
    oBtn.addEventListener("touchend", function() {
      if (success) { //如果已经验证成功了 那么结束函数
        return;
      } else { //反之 验证没有通过 则物块原来的位置
        oBtn.style.left = 0;
        oBg.style.width = 0;
        oBg.style.transition = "width 1s ease";
        oBtn.style.transition = "left 1s ease";
      }
    }, false);
    */

    oBtn.ontouchmove = function(eve) {
      if (!flag) {
        var e = eve.touches[0];
        var moveX = e.clientX; //获取鼠标移动时的坐标 相对于浏览器页面
        var offsetX = moveX - startX; //物块移动的距离
        if (offsetX > distance) { //如果移动的距离已经大于本应该移动的距离 那么就将它设置为验证成功时的距离
          offsetX = distance;
        } else if (offsetX < 0) { //同样 如果移动的距离小于0时 将它设置为0 保证不会超出范围
          offsetX = 0;
        }
        oBtn.style.left = offsetX + "px";
        oBg.style.width = offsetX + "px";
        if (offsetX == distance) { //判断验证通过
          oText.innerHTML = "验证通过";
          oBtn.getElementsByTagName('img')[0].setAttribute('src','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAA/0lEQVRIS+2TMW7CQBBF/1wnnCAXcZOCgiJFCq/kFBRIQJGCIpq/BRQpUqRIES5BxRG4jbXISEZrAh5bNkJIbL373uyfGcGVj1yZj4fATPi+InLOzQBMSR4L7+0HJRzAnGQhOpxeBBH8g+QkbkxnQQkXkYWqjk+7XhGkaboEsPHer83xABDBP1X1/dybisA5twXwHEJILEkUiyfpLhVUEWRZ9pTn+R+AQZ2khIcQVt77t7rf/uuBJYkq/yL5akV5tsmXJBH8m+TIgteO6alERAbFEgH4ITlsAjf3IJYcLov8qupLU7gpKC5Ekh3JpA28kaAtsHbRusLMRXsIbhLRHrN6eRnHDVHLAAAAAElFTkSuQmCC');
          oText.style.color = "#FFF";
          //oBtn.style.color = "rgb(39, 233, 21)";
          success = true; //验证通过时的条件

          setTimeout(func, 10);
          flag = true;
        }
      }
    }

    /*
     //触摸滑动事件
     oBtn.addEventListener("touchmove", function(eve) {
       if (!flag) {
         var e = eve.touches[0];
         var moveX = e.clientX; //获取鼠标移动时的坐标 相对于浏览器页面
         var offsetX = moveX - startX; //物块移动的距离
         if (offsetX > distance) { //如果移动的距离已经大于本应该移动的距离 那么就将它设置为验证成功时的距离
           offsetX = distance;
         } else if (offsetX < 0) { //同样 如果移动的距离小于0时 将它设置为0 保证不会超出范围
           offsetX = 0;
         }
         oBtn.style.left = offsetX + "px";
         oBg.style.width = offsetX + "px";
         if (offsetX == distance) { //判断验证通过
           oText.innerHTML = "验证通过";
           oBtn.getElementsByTagName('img')[0].setAttribute('src',
             'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAA/0lEQVRIS+2TMW7CQBBF/1wnnCAXcZOCgiJFCq/kFBRIQJGCIpq/BRQpUqRIES5BxRG4jbXISEZrAh5bNkJIbL373uyfGcGVj1yZj4fATPi+InLOzQBMSR4L7+0HJRzAnGQhOpxeBBH8g+QkbkxnQQkXkYWqjk+7XhGkaboEsPHer83xABDBP1X1/dybisA5twXwHEJILEkUiyfpLhVUEWRZ9pTn+R+AQZ2khIcQVt77t7rf/uuBJYkq/yL5akV5tsmXJBH8m+TIgteO6alERAbFEgH4ITlsAjf3IJYcLov8qupLU7gpKC5Ekh3JpA28kaAtsHbRusLMRXsIbhLRHrN6eRnHDVHLAAAAAElFTkSuQmCC'
           );
           oText.style.color = "#FFF";
           //oBtn.style.color = "rgb(39, 233, 21)";
           success = true; //验证通过时的条件
     
           setTimeout(func, 10);
           flag = true;
         }
       }
     }, false);
     */
    //去除移动端浏览器默认右滑上一页的效果 滑动验证区域
    document.getElementsByClassName('no-captcha')[0].addEventListener('touchmove', function(e) {
      e.preventDefault();
      e.stopPropagation();
    }, false);
  }

  //销毁移动端滑动验证方法
  function destroyMobile() {
    //先清除绑定事件(事件处理程序)
    oBtn.ontouchstart = null;
    oBtn.ontouchend = null;
    oBtn.ontouchmove = null;
    //再删除元素节点
    oParent.parentNode.removeChild(oParent);
  }

  //销毁pc端滑动验证方法
  function destroyPc() {
    //先清除绑定事件(事件处理程序)
    oBtn.onmousedown = null;
    document.onmousemove = null;
    document.onmouseup = null;
    //再删除元素节点
    oParent.parentNode.removeChild(oParent);
  }

  //重新创建元素
  function restart() {
    //重新创建元素
    var oForm = $("form");
    var no_captcha = document.createElement('div');
    no_captcha.setAttribute('id', 'no-captcha');
    no_captcha.setAttribute('class', 'no-captcha');
    var no_captcha_bg = document.createElement('div');
    no_captcha_bg.setAttribute('class', 'no-captcha-bg');
    no_captcha.appendChild(no_captcha_bg);
    var no_captcha_text = document.createElement('div');
    no_captcha_text.className = 'no-captcha-text';
    no_captcha_text.innerText = '向右滑动验证';
    no_captcha_bg.setAttribute('onselectstart', 'return false;');
    no_captcha.appendChild(no_captcha_text);
    var no_captcha_btn = document.createElement('div');
    no_captcha_btn.setAttribute('class', 'no-captcha-btn');
    var btn_img = document.createElement('img');
    btn_img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADNklEQVRIS7WVTWgTURDHZ7JJA6W29aCIF78uKnpR9CKiBxG8iCCmaazFigSaNN1dkqgHK6kofqXd97IqWKEeiiItFCwUKYqigigKfoKCgkVpxQpClQo2bEdemYR2TZoo+G47OzO/ef95bx7Cf16Yyx+Px1dPTk76bNt+UYxpGMb+kZGRnr6+PqfcuqYBKrnjOL0A4COiRinlY3cCwzAOAcAZAOh3HCdk2/avciA5wEoGrAWAj4jYaFnWvZkJDMNoA4DjbBuora2tT6VSP0tB8hIlk8kV2WxW7WIdAIzxToZmJjBN8wgRnWLbTcdxgrZtf58Lkgcop2g0usTn8ynIRgD4QUQNUsoB104SAHCObbcQMWhZ1rdikFkA5dTS0rLY6/UqyCYAyCJig2VZ6ju/TNPUiUgoAyLe1TQtmE6nxwpB/gAop1gstkDTNJV0Kwc1CiF6XJAIEV1g2wMAqBNCfHZDCgKUU3Nz83y/368g29Q3EYWllJddcoUB4BL/f6RpWqCzs/PTTJ+iAOUUiUSqKioqFGQHB7UKIWzXTpqIqJttT71ebyCdTn/I+cwJYLn8LNdODkoKIdIzIbquNyBiTsLnU1NTgUwm8266R3Mdsdy/VCrlGR8f7yWi3SxHm5TyhAtSh4jX2fYaAAJCiDdlAcLhsK+yslJJtYsTHBVCnHQBQoh4lW2vVD86OjrelgSEw+EaTr6dg+NCiE5Xsw8CwPQBIKJnHo8nYFnW+5ISJZPJRXy7N3NwVEp50ZU8BgAZtj1haYZLNtk0zeVEpGRZP10J4gHLsq64ZDmMiKf5/0N1q8s6pq2trWs8Ho9Kvoor3yulvOY6nu1EdIxt9wEgWNZF03V9AyKq5EvVqODAflfys0SUZNsdHnpfC53IWU02TXMLy7IQANSUDAkhBl2an1dzkW1DiBgqa9gZhqFuq6q8CgC+8JC77dK8GxGb2DbID0/pca3r+m6WxQMAw/zgqAGWX4ZhqEtUxw29MTExEerq6irvwYnFYtWapqlqq4lon5RSHbdZS9f1PVxEf01NTX17e/tkIc3dtnwPEonEsmw2O09K+bJYoIKMjo72//WjX04l/+rzG+fDXChzW61+AAAAAElFTkSuQmCC';
    btn_img.setAttribute('alt', '右滑箭头');
    btn_img.setAttribute('draggable', 'false');
    no_captcha_btn.appendChild(btn_img);
    no_captcha.appendChild(no_captcha_btn);
    oForm.appendChild(no_captcha);

    //重新赋值变量
    oParent = $(".no-captcha");
    oBg = $(".no-captcha-bg");
    oText = $(".no-captcha-text");
    oBtn = $(".no-captcha-btn");
    success = false; //判断验证是否成功
    flag = false; //标记位 用来判断是否需要执行事件
    distance = oParent.offsetWidth - oBtn.offsetWidth; //验证成功的距离
  }


  //公有方法
  //初始化方法 分发到不同函数
  o.init = function(func) {
    is_mobile ? goMobile(func) : goPc(func);
  };
  //销毁方法
  o.destroy = function() {
    is_mobile ? destroyMobile() : destroyPc();
  };
  //重置方法 传入函数表示重新初始化 不传表示重置滑快验证
  o.resize = function(func) {
    //重置图标
    oBtn.getElementsByTagName('img')[0].setAttribute('src','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADNklEQVRIS7WVTWgTURDHZ7JJA6W29aCIF78uKnpR9CKiBxG8iCCmaazFigSaNN1dkqgHK6kofqXd97IqWKEeiiItFCwUKYqigigKfoKCgkVpxQpClQo2bEdemYR2TZoo+G47OzO/ef95bx7Cf16Yyx+Px1dPTk76bNt+UYxpGMb+kZGRnr6+PqfcuqYBKrnjOL0A4COiRinlY3cCwzAOAcAZAOh3HCdk2/avciA5wEoGrAWAj4jYaFnWvZkJDMNoA4DjbBuora2tT6VSP0tB8hIlk8kV2WxW7WIdAIzxToZmJjBN8wgRnWLbTcdxgrZtf58Lkgcop2g0usTn8ynIRgD4QUQNUsoB104SAHCObbcQMWhZ1rdikFkA5dTS0rLY6/UqyCYAyCJig2VZ6ju/TNPUiUgoAyLe1TQtmE6nxwpB/gAop1gstkDTNJV0Kwc1CiF6XJAIEV1g2wMAqBNCfHZDCgKUU3Nz83y/368g29Q3EYWllJddcoUB4BL/f6RpWqCzs/PTTJ+iAOUUiUSqKioqFGQHB7UKIWzXTpqIqJttT71ebyCdTn/I+cwJYLn8LNdODkoKIdIzIbquNyBiTsLnU1NTgUwm8266R3Mdsdy/VCrlGR8f7yWi3SxHm5TyhAtSh4jX2fYaAAJCiDdlAcLhsK+yslJJtYsTHBVCnHQBQoh4lW2vVD86OjrelgSEw+EaTr6dg+NCiE5Xsw8CwPQBIKJnHo8nYFnW+5ISJZPJRXy7N3NwVEp50ZU8BgAZtj1haYZLNtk0zeVEpGRZP10J4gHLsq64ZDmMiKf5/0N1q8s6pq2trWs8Ho9Kvoor3yulvOY6nu1EdIxt9wEgWNZF03V9AyKq5EvVqODAflfys0SUZNsdHnpfC53IWU02TXMLy7IQANSUDAkhBl2an1dzkW1DiBgqa9gZhqFuq6q8CgC+8JC77dK8GxGb2DbID0/pca3r+m6WxQMAw/zgqAGWX4ZhqEtUxw29MTExEerq6irvwYnFYtWapqlqq4lon5RSHbdZS9f1PVxEf01NTX17e/tkIc3dtnwPEonEsmw2O09K+bJYoIKMjo72//WjX04l/+rzG+fDXChzW61+AAAAAElFTkSuQmCC');
    //重置文字
    oText.innerHTML = '向右滑动验证';
    //重置背景颜色
    oBtn.style.left = 0;
    oBg.style.width = 0;
    oBg.style.transition = "width 1s ease";
    oBtn.style.transition = "left 1s ease";
    //重置成功属性
    success = false;
    //重置flag标志位
    flag = false;
    //重新初始化或重新创建
    func === undefined ? restart() : this.init(func);
  }

  //返回对象
  return o;
}