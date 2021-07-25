-- 创建用户表
create table if not exists `y_user`(
  id tinyint unsigned primary key auto_increment,
  u_name varchar(20) not null unique comment '用户名',
  u_pass char(60) not null comment '密码',
  u_state tinyint(1) unsigned not null default 1 comment '状态 0禁用 1正常',
  u_level tinyint(1) unsigned not null comment '级别 0超管 1亲友',
  u_ip char(15) not null default '0.0.0.0' comment '最近登陆ip',
  u_email varchar(50) not null default 'heypass@admin.com' comment '邮箱',
  u_nickname char(7) not null default 'heypass' comment '昵称',
  login_time int(10) unsigned  not null comment '最近登陆时间',
  create_time int(10) unsigned not null comment '创建时间'
)engine Innodb charset utf8;

-- 插入测试超管 密码admin12345
insert into `y_user` values(0,'admin','$2y$12$st18jEh79zXZF9su.ShgyuVNb9sYQpTL7elUB0bgnu8uP7HObFX7y',default,0,default,default,default,unix_timestamp(now()),unix_timestamp(now()));

-- 创建后台操作日志表
create table if not exists `y_user_log`(
id int unsigned auto_increment primary key,
l_user varchar(20) not null comment '操作用户',
l_name varchar(10) not null comment '操作名称',
l_state tinyint(1) unsigned not null default 1 comment '操作状态 0失败 1成功 2危险',
l_desc varchar(50) not null comment '操作描述',
l_ip char(15) not null default '0.0.0.0' comment 'ip',
l_time int(10) unsigned not null comment '操作时间'
)engine Innodb charset utf8;

-- 创建用户加密数据表
create table if not exists `y_user_enc`(
  id tinyint unsigned primary key auto_increment,
  u_id tinyint not null unique comment '用户id',
  u_text char(44) not null comment '加密内容'
)engine Innodb charset utf8;

-- 系统设置表
create table if not exists `y_conf`(
  id tinyint unsigned primary key auto_increment,
  c_name char(15) not null unique comment '英文名',
  c_cname varchar(255) not null comment '中文名',
  c_value varchar(255) not null comment '设置项值',
  c_type char(10) not null comment '类型',
  c_desc varchar(255) not null default '' comment '备注'
)engine Innodb charset utf8;

-- 插入系统配置项
insert into `y_conf` values(1,'web_site_name','站点名称','HeyPass-免费开源的个人隐私管理系统','input',''),
(2,'web_site_url','站点域名','https://github.com/hei-jack/heypass','input',''),
(3,'web_site_key','站点关键词','HeyPass,隐私,个人,开源,管理','input',''),
(4,'web_site_desc','站点描述','HeyPass是一款免费开源的个人隐私管理系统，它也许会是你进行隐私管理的好帮手','textarea',''),
(5,'web_site_theme','站点主题','my_jquery','select','{\"my_jquery\":\"默认主题\",\"default\":\"主题二\"}'),
(6,'web_index_theme','前台模板','index','select','{\"index\":\"默认\",\"test\":\"测试\"}'),
(7,'web_index_state','前台开关','on','switch','{\"on\":\"开\",\"off\":\"关\"}'),
(8,'admin_forbid_ip','后台禁止访问ip','192.168.2.*','textarea','只支持ipv4地址，匹配IP段用\"*\"占位，如192.168.*.*，多个IP地址请用英文逗号\",\"分割');

-- 密码分类表
create table if not exists `y_category`(
  id tinyint unsigned primary key auto_increment,
  c_en varchar(20) not null unique comment '分类英文名',
  c_zh varchar(20) not null comment '分类中文名'
)engine Innodb charset utf8;

-- 插入测试分类
insert into `y_category` values(1,'office','办公'),(2,'recreation','娱乐'),(3,'social','社交'),(4,'film','影视'),(5,'game','游戏'),(6,'study','学习'),(7,'other','其他');

-- 密码记录表
create table if not exists `y_password`(
  id int unsigned primary key auto_increment,
  cid tinyint unsigned not null comment '分类id',
  uid tinyint unsigned not null comment '用户id',
  p_name varchar(255) not null comment '账号',
  p_pass varchar(255) not null comment '密码',
  p_title varchar(255) not null comment '关联名称',
  p_url varchar(255) not null default '' comment '关联网址',
  p_other varchar(255) not null default '' comment '备注',
  update_time int(10) unsigned  not null comment '更新时间',
  create_time int(10) unsigned not null comment '创建时间'
)engine Innodb charset utf8;

-- 备忘索引表
create table if not exists `y_memo`(
  id int unsigned primary key auto_increment,
  cid tinyint unsigned not null comment '分类id',
  uid tinyint unsigned not null comment '用户id',
  title varchar(255) not null comment '标题',
  update_time int(10) unsigned  not null comment '更新时间',
  create_time int(10) unsigned not null comment '创建时间'
)engine Innodb charset utf8;

-- 备忘内容表
create table if not exists `y_memo_data`(
 id int unsigned primary key auto_increment,
 mid int unsigned not null unique comment '索引id',
 content text not null comment '备忘内容'
)engine Innodb charset utf8;

-- 日记索引表
create table if not exists `y_diary`(
  id int unsigned primary key auto_increment,
  uid tinyint unsigned not null comment '用户id',
  `diary_no` char(12) not null unique comment '日记唯一识别码 八位日期-uid', 
  `year` smallint(4) unsigned not null comment '年',
  `month` tinyint(2) unsigned not null comment '月',
  `day` tinyint(2) unsigned not null comment '日',
  `diary` tinyint(1) unsigned not null comment '0完成 1未完成',
  `tag` tinyint(1) unsigned not null comment '标记'
)engine Innodb charset utf8;

-- 日记内容表
create table if not exists `y_diary_data`(
 id int unsigned primary key auto_increment,
 did int unsigned not null unique comment '索引id',
 content text not null comment '日记内容',
 update_time int(10) unsigned  not null comment '更新时间',
 create_time int(10) unsigned not null comment '创建时间'
)engine Innodb charset utf8;

-- 系统帮助表 存放使用教程之类的内容
create table if not exists `y_help`(
  id int unsigned primary key auto_increment,
  title varchar(255) not null comment '标题',
  content text not null comment '内容'
)engine Innodb charset utf8;

insert into `y_help` values(1,'使用教程','<p>总体来说，HeyPass的使用还是比较易于上手。  </p>\n<p>估计让普通用户需要花点时间才能熟练应该是markdown语法，所以后面会多花点篇幅来进行markdown简单入门。</p>\n<p>除了markdown之外，其他应该就是一些注意事项和常见问题了，下面让我们先从注意事项开始吧。</p>\n<h2 id=\"\">注意事项</h2>\n<ol>\n<li><em>打开模块的编辑页面和查看页面之前，请务必关闭原先打开的对应页面。</em></li>\n</ol>\n<p>例如，我已经打开了2021年08月01日的日记编辑页面，如果此时我想打开08月20号的日记，那么我必须先关闭原先打开的08月01日的编辑页面，否则系统会直接跳转到08月01日编辑页面。</p>\n<ol start=\"2\">\n<li><strong>每次存储密码结束后，请务必到密码列表页面进行核对。</strong></li>\n</ol>\n<p>因为系统的安全过滤机制，可能会过滤某些冷僻的危险字符。</p>\n<ol start=\"3\">\n<li>密码中请尽量避免使用<code>小于符号\"&lt;\"大于符号\"&gt;\"</code>。理由请看上面。</li>\n</ol>\n<h2 id=\"-1\">常见问题</h2>\n<ol>\n<li>头部出现<em>页面过期或者请求失败</em>，该怎么办？  </li>\n</ol>\n<p><strong>答</strong>：如果你是在使用<em>电脑</em>，直接在主页对应标签鼠标右击然后刷新，或者刷新整个浏览器界面也可以。  如果你是在使用<em>手机</em>，也不用担心可以先关闭对应标签页，再重新打开即可。实在不行你也可以忽略它。</p>\n<ol start=\"2\">\n<li>如果我不慎<em>遗失了安全密码</em>，我存储在网站的数据还能找回吗？</li>\n</ol>\n<p><strong>答</strong>：只要网站的数据还没有遗失，<em>应该还是有几率找回的</em>。前提是你能找到帮你解密的人，反正me（<em>网站开发者hei-jack</em>）是没办法找回来了，所以安全密码一定要妥善保管。</p>\n<ol start=\"3\">\n<li>我在<em>插入音频或者视频</em>时，发现右侧的预览界面显示错乱</li>\n</ol>\n<p><strong>答</strong>：这是一个明目张胆的bug。建议你先检查id是否重复，然后剩下的就是不用管，如果id和音频、视频地址没有错误，那么在查看界面一般是不会出现错乱的。</p>\n<ol start=\"4\">\n<li>我写的文章，该咋排版，还有编辑器该咋用？</li>\n</ol>\n<p><strong>答</strong>：大神请忽略，小白请看下面的<em>Markdown入门</em>和<a href=\"#help1\">编辑器帮助</a>。</p>\n<h2 id=\"markdown\">Markdown入门</h2>\n<p><em>Markdown 是一种轻量级标记语言，它允许人们使用易读易写的纯文本格式编写文档。</em></p>\n<p>这里只讲网站常用的部分，并且只是简单示范，若有错漏请自动忽略。</p>\n<h3 id=\"-2\">标题演示</h3>\n<p>标题共有6个等级，h1到h6递减。</p>\n<p><code>建议日记和备忘的标题都以h2开始</code>，因为系统会自动将日期或者备忘标题显示在文章最顶部为h1标题。</p>\n<h4 id=\"-3\"><strong>标题演示开始</strong></h4>\n<pre><code class=\"markdown language-markdown\"># 一级标题\n\n## 二级标题\n\n### 三级标题\n\n#### 四级标题\n\n##### 五级标题\n\n###### 六级标题\n\n显示效果如下\n</code></pre>\n<h1 id=\"-4\">一级标题</h1>\n<h2 id=\"-5\">二级标题</h2>\n<h3 id=\"-6\">三级标题</h3>\n<h4 id=\"-7\">四级标题</h4>\n<h5 id=\"-8\">五级标题</h5>\n<h6 id=\"-9\">六级标题</h6>\n<h3 id=\"-10\"><strong>标题演示结束</strong></h3>\n<h3 id=\"-11\"><strong>字体效果演示</strong></h3>\n<pre><code class=\"markdown language-markdown\">普通文字\n\n**粗体**\n\n*斜体*\n\n~~删除线~~\n\n`行内代码`\n\n&gt; 引述\n\n显示效果如下\n</code></pre>\n<p>普通文字</p>\n<p><strong>粗体</strong></p>\n<p><em>斜体</em></p>\n<p><del>删除线</del></p>\n<p><code>行内代码</code></p>\n<blockquote>\n  <p>引述</p>\n</blockquote>\n<h3 id=\"-12\"><strong>字体效果演示结束</strong></h3>\n<h3 id=\"help1\">编辑器帮助help-1</h3>\n<p>无序列表其实就是下面这样的效果。</p>\n<pre><code class=\"markdown language-markdown\">- 买菜\n\n- 吃饭\n\n- 打豆豆\n</code></pre>\n<ul>\n<li><p>买菜</p></li>\n<li><p>吃饭</p></li>\n<li><p>打豆豆</p></li>\n</ul>\n<p>有序列表类似，只是带数字。</p>\n<p>表格长这样。</p>\n<pre><code class=\"markdown language-markdown\">| 左对齐 | 居中 | 右对齐 |\n| :--- | :---: | ---: |\n| 内容1 | 内容2 | 内容3 |\n</code></pre>\n<table>\n<thead>\n<tr>\n<th style=\"text-align:left;\">左对齐</th>\n<th style=\"text-align:center;\">居中</th>\n<th style=\"text-align:right;\">右对齐</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style=\"text-align:left;\">内容1</td>\n<td style=\"text-align:center;\">内容2</td>\n<td style=\"text-align:right;\">内容3</td>\n</tr>\n</tbody>\n</table>\n<p>鼠标移入上方其实都有说明的，应该没啥特别困难的，其他的自己上手来吧。  </p>\n<p>小白主要是要理解markdown语法，老鸟直接可以手动撸。</p>\n<p>markdown的详细教程推荐看<a href=\"https://www.runoob.com/markdown/md-tutorial.html\" rel=\"noopener noreferrer\" target=\"_blank\">菜鸟教程-markdown教程</a>。</p>\n<p><code>教程结束......</code></p>');