<?php
//获取允许请求的最大页码（分页）
/*
 * @param1 int $count 数据总量
 * @param2 int $limit 单次获取的数据量
 * return int 返回允许请求的最大页码
 */
function max_page($count, $limit){
  //如果数据总量能整除单次获取的数据量  直接返回页码
  //如果不能整除说明 页码要多1页 floor向下取整后加1
  return $count % $limit === 0 ? $count / $limit : floor($count / $limit) + 1;
}

/*
* 对xss字符串的检测
*
* @access public
* @param string $str
* @return boolean
*/
function xssCheck($str){
  $search = 'abcdefghijklmnopqrstuvwxyz';
  $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $search .= '1234567890!@#$%^&*()';
  $search .= '~`";:?+/={}[]-_|\'\\';

  for ($i = 0; $i < strlen($search); $i++) {
    // ;? matches the ;, which is optional
    // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

    // &#x0040 @ search for the hex values
    $str = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $str); // with a ;
    // &#00064 @ 0{0,7} matches '0' zero to seven times
    $str = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $str); // with a ;
  }

  return !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19]|' . "\r|\n|\t" . ')/', $str);
}


//过滤xss函数 使用第三方类库HTML Purifier（composer导入 vendor目录下） 不允许任何标签通过
function remove_all_xss($data){
  //实例化HTML Purifier对象配置方法
  $_clean_xss_config = HTMLPurifier_Config::createDefault();
  $_clean_xss_config->set('Core.Encoding', 'UTF-8');
  // 设置保留的html标签
  $_clean_xss_config->set('HTML.Allowed', '');
  // 设置保留的html属性 src必须保留 否则会报错
  $_clean_xss_config->set('HTML.AllowedAttributes', 'src');
  //放行默认css属性
  $_clean_xss_config->set('CSS.AllowedProperties', '');

  //实例化HTML Purifier对象
  $_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
  // 执行过滤
  return $_clean_xss_obj->purify($data);
}

//过滤xss函数 允许白名单标签和属性通过
function remove_xss($data){
  //实例化HTML Purifier对象配置方法
  $_clean_xss_config = HTMLPurifier_Config::createDefault();
  $_clean_xss_config->set('Core.Encoding', 'UTF-8');
  //不支持列表 area[shape|coords|href|alt],article,aside,audio[autoplay|controls|loop|preload|src|class|style],bdi[dir],details[open],footer,header,mark,nav,section,video[autoplay|controls|loop|preload|src|height|width|class|style]
  //允许通过的标签
  $html_value = "a[target|href|title|class|style],abbr[title|class|style],address[class|style],b[class|style],bdo[dir],big,blockquote[cite|class|style],br,caption[class|style],center,cite,code[class|style],col[align|valign|span|width|class|style],colgroup[align|valign|span|width|class|style],dd[class|style],del,div[class|style],dl[class|style],dt[class|style],em[class|style],font[color|size|face],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style],hr,i[class|style],img[src|alt|title|width|height|id|class],ins,li[class|style],ol[class|style],p[class|style],pre[class|style],s,small,span[class|style],sub[class|style],sup[class|style],strong[class|style],table[width|border|align|class|style],tbody[align|valign|class|style],td[width|rowspan|colspan|align|valign|class|style],tfoot[align|valign|class|style],th[width|rowspan|colspan|align|valign|class|style],thead[align|valign|class|style],tr[align|valign|class|style],tt,u,ul[class|style],video[autoplay|controls|loop|preload|src|height|width|class|style|data-setup]";
  // 设置保留的html标签
  $_clean_xss_config->set('HTML.Allowed', $html_value);
  // 设置保留的html属性
  $_clean_xss_config->set('HTML.AllowedAttributes', 'id,src,style,class,href,title,target,controls,autoplay,type,preload,autoplay,loop,data-setup');
  //避免id冲突 统一加上user前缀
  // $_clean_xss_config->set('Attr.EnableID', true);
  // $_clean_xss_config->set('Attr.IDPrefix', 'user_');
  //放行默认css属性
  $_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align,line-height,text-indent');
  $def = $_clean_xss_config->getHTMLDefinition(true);
  //允许a标签target属性通过 并且限制值
  $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(
    array('_blank', '_self', '_target', '_top')
  ));
  
  //自定义放行video标签
  $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
    'id' => 'Text',
    'src' => 'URI',
    'type' => 'Text',
    'poster' => 'URI',
    'preload' => 'Enum#auto,metadata,none',
    'controls' => 'Text',
    'autoplay' => 'Text',
    'loop' => 'Text',
    'width' => 'Number',
    'height' => 'Number',
    'data-setup' => 'Text',
    'class' => 'Text',
    'style' => 'Text'
  ]);
  //自定义放行audio标签
  $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
    'id' => 'Text',
    'src' => 'URI',
    'type' => 'Text',
    'poster' => 'URI',
    'preload' => 'Enum#auto,metadata,none',
    'controls' => 'Text',
    'autoplay' => 'Text',
    'loop' => 'Text',
    'width' => 'Number',
    'height' => 'Number',
    'data-setup' => 'Text',
    'class' => 'Text',
    'style' => 'Text',
    'data-setup' => 'Text'
  ]);
  //自定义放行source标签
  $def->addElement('source', 'Block', 'Flow', 'Common', [
    'src' => 'URI',
    'type' => 'Text',
  ]);

  //实例化HTML Purifier对象
  $_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
  // 执行过滤
  return $_clean_xss_obj->purify($data);
}

//安全过滤函数 移除所有html元素 并且转义单引号、双引号、反斜杠（\）、null
function safe_filter($data){
  //先将数据中的多余项去掉
  unset($data['__token__']);
  unset($data['host']);
  //遍历数组元素进行xss过滤
  foreach ($data as $key => $v) {
    $data[$key] = addslashes(remove_all_xss($v)); //移除所有html元素 并且转义单引号、双引号、反斜杠（\）、null
  }
  return $data;
}

//安全过滤函数 将单引号、双引号、&、大于>、小于<转为html实体
function safe_filter_special($data){
  //先将数据中的多余项去掉
  unset($data['__token__']);
  unset($data['host']);
  //遍历数组元素进行xss过滤
  foreach ($data as $key => $v) {
    $data[$key] = htmlspecialchars(remove_all_xss($v), ENT_QUOTES, 'utf-8', false); //将单引号、双引号、&、大于>、小于<转为html实体
  }
  return $data;
}

//安全过滤函数 允许白名单html通过
function safe_filter_html($data){
  //先将数据中的多余项去掉
  unset($data['__token__']);
  unset($data['host']);
  //遍历数组元素进行xss过滤
  foreach ($data as $key => $v) {
    $data[$key] = remove_xss($v); //允许白名单的标签和属性通过
  }
  return $data;
}



/*
* 处理XSS跨站攻击的过滤函数
*
* @author kallahar@kallahar.com
* @link http://kallahar.com/smallprojects/php_xss_filter_function.php
* @access public
* @param string $val 需要处理的字符串
* @return string
*/
function remove_xss_func($val){
  // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
  // this prevents some character re-spacing such as <java\0script>
  // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
  $val = preg_replace('/([\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', '', $val);

  // straight replacements, the user should never need these since they're normal characters
  // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
  $search = 'abcdefghijklmnopqrstuvwxyz';
  $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $search .= '1234567890!@#$%^&*()';
  $search .= '~`";:?+/={}[]-_|\'\\';

  for ($i = 0; $i < strlen($search); $i++) {
    // ;? matches the ;, which is optional
    // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

    // &#x0040 @ search for the hex values
    $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
    // &#00064 @ 0{0,7} matches '0' zero to seven times
    $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
  }

  // now the only remaining whitespace attacks are \t, \n, and \r
  $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
  $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
  $ra = array_merge($ra1, $ra2);

  $found = true; // keep replacing as long as the previous round replaced something
  while ($found == true) {
    $val_before = $val;
    for ($i = 0; $i < sizeof($ra); $i++) {
      $pattern = '/';
      for ($j = 0; $j < strlen($ra[$i]); $j++) {
        if ($j > 0) {
          $pattern .= '(';
          $pattern .= '(&#[xX]0{0,8}([9ab]);)';
          $pattern .= '|';
          $pattern .= '|(&#0{0,8}([9|10|13]);)';
          $pattern .= ')*';
        }
        $pattern .= $ra[$i][$j];
      }
      $pattern .= '/i';
      $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
      $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags

      if ($val_before == $val) {
        // no replacements were made, so exit the loop
        $found = false;
      }
    }
  }

  return $val;
}


//获取16位随机令牌（包含5位汉字）
function get_rand_token(){
  // 字母数字特殊符号
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
  $token = [];
  for ($i = 0; $i < 11; $i++) {
    array_push($token, $chars[mt_rand(0, strlen($chars) - 1)]);
  }
  //中文
  for ($i = 0; $i < 5; $i++) {
    // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
    $temp = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
    // 转码
    array_push($token, iconv('GB2312', 'UTF-8', $temp));
  }

  //随机打散数组元素
  shuffle($token);
  return implode($token);
}

//获取汉字
function get_chinese(){
  $temp = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
  return iconv('GB2312', 'UTF-8', $temp);
}
