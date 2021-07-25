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

## 安装

### 下载或clone

下载或clone本仓库发行版本后，到网站根目录解压，之后**设置public为根目录**。

### 安装引导

设置根目录操作完成后，运行`域名/install`根据步骤安装即可。

需要注意的是，如果安装失败提示`RSA密钥生成失败`，则需要`寻找openssl.cnf路径`替换`public/install/Helper.php`中的`findOpensslPath`方法下任意`$path`。

若出现其他安装失败，请检查目录读写权限或数据库信息。

如果安装失败，就删除之后从头再来几次。

还是不行的话，请在`issue`寻求解答。

### 安装必看

**请注意**：`beta`版本表示正在进行安全测试，暂不建议部署到线上生产环境。

## 目录说明

`public/install`: 安装引导目录，请务必在安装成功后删除~

`public/static/my_jquery`:存放默认主题`my_jquery`的前端js文件

`templates`:模板存放路径

`templates/home`:前台主题存放路径

`templates/my_jquery`:默认主题存放路径，命名来源于me用原生js模拟封装的jquery。暂不抽离公共头部模板是为了方便现阶段测试维护。

目录这样设计的目录是为了方便根据情况进行拓展其他主题。

**其他目录请参考thinkphp5.1目录结构。**


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
- [hitokoto一言 api](https://developer.hitokoto.cn/sentence/)

## 免责声明

1. 本系统完全出于个人兴趣爱好，由本人在业余时间开发，在一般场合下，对任何使用者不收取任何费用，不包含任何恶意代码，也不收集任何用户信息。 

2. 利用本系统二次开发的任何作品，和本人无关。

3. 因使用本系统而引致的任何意外、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其所造成的任何损失，本人概不负责，亦概不承担任何民事或刑事法律责任。

4. 当你第一次开始使用本人所提供的系统及资源的那一刻起就将被视为对本声明全部内容的认可。同时您必须认可上述免责条款，方可使用本系统及资源。如有任何异议，请立刻删除本系统及资源并且停止使用。

5. 本声明可能因为实际情况随时进行必要调整，请您自行关注,恕不另行通知。

6. 以上内容，本人保留最终解释权。