# heypass

HeyPass一款基于 bip39 标准和thinkphp5.1框架的个人隐私管理系统，目前实现了密码管理、日记管理、备忘管理等功能。

## 本项目使用的加密技术和标准

- BIP39标准
- RSA
- AES-CBC-256
- HMAC-SHA256

## 演示与截图

### 演示地址

感谢**infinityfree**开放的免费虚拟主机服务。

**后台**：[https://heypass.infinityfreeapp.com/public/admin/login.html](https://heypass.infinityfreeapp.com/public/admin/login.html)

**演示账号**: `admin00001`

**演示密码**: `admin00001`

**演示账号安全密码**：`签而独桑，荣萄口仿。卫官枪准，恶户单造。`

**前台**：[https://heypass.infinityfreeapp.com](https://heypass.infinityfreeapp.com/)

*注意*：因为演示使用的是虚拟主机，实际部署到服务器之后url中是不会有`public`路径的。

### 运行截图

**PC端**：

![PC端效果展示一](https://z3.ax1x.com/2021/07/23/WrWLwQ.png "PC端效果展示一")

![PC端效果展示二](https://z3.ax1x.com/2021/07/23/WrfkTJ.png "PC端效果展示二")

![PC端效果展示三](https://z3.ax1x.com/2021/07/23/WrfmSx.png "PC端效果展示三")

**移动端**：

![移动端效果展示1](https://z3.ax1x.com/2021/07/23/Wrf1TH.png "移动端效果展示1")

![移动端效果展示2](https://z3.ax1x.com/2021/07/23/Wrf474.png "移动端效果展示2")

## 使用和借鉴的作品

### 借鉴的作品

**感谢下面这些优秀的作品，给本系统的开发提供了很多好的思路和方向。**

- Jquery：项目中使用原生js模拟Jquery开发了my-jquery库

- Bootstrap：使用原生CSS开发时深受其影响
- [Bootstrap-table](https://github.com/wenzhixin/bootstrap-table)：项目中使用原生js给my-jquery开发了一个类似的my-table插件
- [光年后台管理系统(Light Year Admin)](https://gitee.com/yinqi/Light-Year-Admin-Using-Iframe-v4)：ui设计大量借鉴 部分样式直接进行了使用

### 使用的第三方库

感谢下面这些棒棒哒的作品，让我能够白嫖成功。

- [furqansiddiqui/bip39-mnemonic-php](https://github.com/furqansiddiqui/bip39-mnemonic-php)：对其进行了二次修改，主要是兼容php5.6、添加中文简体
- [iancoleman/bip39](https://github.com/iancoleman/bip39)
- [thinkphp5.1](https://gitee.com/liu21st/thinkphp/tree/5.1/)
- [htmlpurifier](http://htmlpurifier.org)
- [autosize.js](https://hub.fastgit.org/jackmoore/autosize)
- [showdown.js](https://github.com/showdownjs/showdown)
- [video.js](https://github.com/videojs/video.js)
- [clipboard.js](https://github.com/zenorocha/clipboard.js/)
- [cryptojs](https://code.google.com/p/crypto-js/)
- [jsencrypt](https://github.com/travist/jsencrypt/)

### 其他

待续