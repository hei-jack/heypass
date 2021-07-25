<?php
//安装助手类
class Helper{
  //返回json数据
  public function json($code, $msg, $data = ''){
    //设置响应头
    header('Content-type: application/json;charset=UTF-8');
    echo json_encode(array('status' => $code, 'mess' => $msg, 'data' => $data));
    die; //退出脚本运行
  }

  //获取环境信息
  public function getInfo(){
    $data = array();
    $data['system'] = PHP_OS; //操作系统
    $data['php_vesion'] = PHP_VERSION; //php版本
    //判断php mysqli拓展是否开启
    $data['mysqli'] = function_exists('mysqli_query') ? 'YES' : 'NO';
    $data['openssl'] = function_exists('openssl_encrypt') ? 'YES' : 'NO';
    $data['gd'] = function_exists('gd_info') ? 'YES' : 'NO';
    $data['mb_string'] = function_exists('mb_strlen') ? 'YES' : 'NO';
    $this->json(200, '返回成功', $data);
  }

  //检查cookie是否存在
  public function checkCookie($key){
    if (isset($_COOKIE[$key])) return true;
    return false;
  }

  //步骤2 后台设置
  /*
   * @param array $data 
   */
  public function step2($data){
    //环境检测cookie是否已经就绪
    if (!$this->checkCookie('install_step1')) return $this->json(403, '请先完成环境检测', '否则安装了小心跑不起来！');
    //假如已经设置过 不用重复处理
    if ($this->checkCookie('install_step2')) return $this->json(403, '请勿重复设置');
    //预留主题设置
    if ((int)$data['themes'] !== 1) return $this->json(403, '主题设置有误', '除默认主题之外其他暂未开放！');
    $len = strlen($data['admin']);
    //校验后台地址是否合法
    if ($len < 6 || $len > 10) return $this->json(403, '后台地址有误', '请检查长度是否为6-10位');
    if (preg_match('/[^a-z0-9_-]+/i', $data['admin'])) return $this->json(403, '后台地址有误', '请检查是否包含非法字符');
    //校验通过 开始进行后台地址设置
    //路由目录
    $route_path = ROOT_PATH . DS . 'route' . DS;
    $admin = $data['admin'];
    //读取安装路由文件
    $content = file_get_contents($route_path . 'install_route'); //读取安装路由文件
    $replace = str_replace('//_ins_', '', $content); //去除原注释
    $replace = str_replace("_ins_",$admin, $replace); //替换路由中待定部分为设置的路由地址
    //将设置好的内容写入路由文件
    if (file_put_contents($route_path . 'route.php', $replace) !== false) {
      //写入成功 删除原先的安装文件
      unlink($route_path . 'install_route');
      //将完成界面的{$admin}替换为后台地址
      $finsh = file_get_contents('./finsh.html');
      $finsh = str_replace('{$admin}', '/' . $data['admin'] . '/login.html',$finsh);
      //重新写会finsh.html
      if(file_put_contents('./finsh.html',$finsh) !== false ) return $this->json(200, '设置成功', '请继续下一步');
    }
    return $this->json(403, '设置失败', '请检查目录权限');
  }

  //步骤三 数据库设置
  public function step3($data){
    //检查是否通过步骤2
    if (!$this->checkCookie('install_step2')) return $this->json(403, '请先完成后台设置！');
    //检查已经完成数据库设置
    if ($this->checkCookie('install_step3')) return $this->json(403, '请勿重复设置');
    //连接数据库 看看是否成功
    $mysqli = @new mysqli($data['host'], $data['user'], $data['pass'], $data['dbname']);
    //连接错误
    if ($mysqli->connect_errno) return $this->json(403, '请检查数据库信息是否有误！');

    //连接成功 设置连接字符集
    if (!$mysqli->set_charset("utf8")) return $this->json(403, '设置数据库字符集失败！');

    //config路径
    $config_path = ROOT_PATH . DS . 'config' . DS;
    //读取并替换install_database内容
    $ins = file_get_contents($config_path . 'install_database');
    $ins = str_replace('ins_host', $data['host'], $ins);
    $ins = str_replace('ins_dbname', $data['dbname'], $ins);
    $ins = str_replace('ins_user', $data['user'], $ins);
    $ins = str_replace('ins_pass', $data['pass'], $ins);
    $ins = str_replace('ins_prefix', $data['prefix'], $ins);
    //将替换完的install_database内容写入database
    if (file_put_contents($config_path . 'database.php', $ins) === false) return $this->json(403, '安装失败，重来一遍吧！');
    //写入成功 删除install_database文件
    unlink($config_path . 'install_database');

    //进行导入数据库
    //读取安装sql文件
    $sql = file_get_contents('./HeyPass.sql');
    //是否修改前缀
    if ($data['prefix'] !== 'y_') $sql = str_replace('`y_', '`' . $data['prefix'], $sql);
    //打散为数组
    $sql_arr = explode(';', $sql);
    //去除最后的空数组
    array_pop($sql_arr);
    //遍历执行sql
    foreach ($sql_arr as $val) {
        $mysqli->query($val);
    }
    //关闭连接
    $mysqli->close();

    //进行生成rsa密钥工作
    $config = array(
      'config' => $this->findOpensslPath(), //openssl.cnf路径
      'digest_alg'  => 'sha256',
      'private_key_bits' => 2048, //私钥长度
      'private_key_type'  => OPENSSL_KEYTYPE_RSA, //密钥类型
    );

    //获取资源
    $res = openssl_pkey_new($config);

    if($res === false) return $this->json(403, 'RSA密钥生成失败！');

    //生成私钥
    openssl_pkey_export($res, $private_key_pem, null, $config);

    //生成公钥
    $details = openssl_pkey_get_details($res);
    $public_key_pem = $details['key'];

    //将私钥格式化为一行

    //去除前面-----BEGIN PRIVATE KEY----- 有空格
    $private_key = substr($private_key_pem, 28);
    //去除私钥后面 -----END PRIVATE KEY----- 前后都有空格
    $private_key = substr($private_key, 0, 1649);
    //去除空格、换行符
    $private_key = preg_replace("/[\s]+/", "", $private_key);

    //将公钥格式化为一行

    //去除公钥前面-----BEGIN PUBLIC KEY----- 有空格和换行
    $public_key = substr($public_key_pem, 27);
    //去除公钥后面的  -----END PUBLIC KEY----- 
    $public_key = substr($public_key, 0, 398);
    $public_key = preg_replace("/[\s]+/", "", $public_key);

    //生成随机send_key和cookie_key
    $cookie_key = $this->getKey();
    $send_key = $this->getKey();

    //读取install_app文件 替换对应内容
    $app = file_get_contents($config_path . 'install_app');
    $app = str_replace('ins_pri_key',$private_key,$app);
    $app = str_replace('ins_pub_key',$public_key,$app);
    $app = str_replace('ins_cookie_key',$cookie_key,$app);
    $app = str_replace('ins_send_key',$send_key,$app);
    //替换结束 写入app文件
    if (file_put_contents($config_path . 'app.php', $app) === false) return $this->json(403, '配置文件安装失败！');
    //写入结束 删除install_app文件
    unlink($config_path . 'install_app');
    return $this->json(200, '安装已经快要结束了，即将跳转~');
  }

  //查找openssl.cnf的路径
  public function findOpensslPath(){
    //宝塔环境
    $path = '/usr/local/openssl/openssl.cnf';
    if (file_exists($path)) return $path;
    //linux环境
    $path = '/usr/local/ssl/openssl.cnf';
    if (file_exists($path)) return $path;
    //linux环境
    $path = '/usr/lib/ssl/openssl.cnf';
    if (file_exists($path)) return $path;
    //linux环境 Ubuntu
    $path = '/etc/ssl/openssl.cnf';
    if (file_exists($path)) return $path;
    //linux环境
    $path = '/etc/PKI/TLS/openssl.cnf';
    //phpstudy win环境
    $path = dirname(dirname(dirname(dirname(__DIR__)))) . DS . 'Extensions' . DS . 'php' . DS . 'php' . PHP_VERSION . 'nts' . DS . 'extras' . DS . 'ssl' . DS . 'openssl.cnf';
    if (file_exists($path)) return $path;
    return false;
  }

  //生成16位随机密钥
  private function getKey(){
      // 密码字符集，可任意添加你需要的字符
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
     
      $password = '';

      $max = strlen($chars) - 1;
     
      for ( $i = 0; $i < 16; $i++ ){
       $password .= $chars[mt_rand(0,$max)];
      }
      return $password;
  }

  //完成安装 删除install文件夹 跳转后台地址
  public function finsh(){
    $path = ROOT_PATH . DS . 'public' . DS . '/install';
    //先判断传进来的参数是不是合法路径
    if(!is_dir($path)) return $this->json(403, '删除安装文件夹失败！'); //is_dir() 函数检查指定的文件是否是一个目录 如果目录存在返回true
    //确定合法路径后 读取文件夹下所有文件
    $res = opendir($path);  //读取目录 返回一个资源

    //遍历读取 删除目录下所有文件 不加false严格判断 文件名为0可能导致循环停止
    while(false !== ($file = readdir($res))){  //readdir() 函数返回目录中下一个文件的文件名 相当于指针
        if ($file === "." || $file === "..") continue;  //如果文件为当前目录或上级 就跳过 . 表示当前目录 .. 表示当前目录的上一级目录。
        //拼接完整路径 + 文件名
        $file_path = $path . '/' . $file;
        //判断当前完整路径是否为目录 目录就跳过
        if(is_dir($file_path)) continue;
        unlink($file_path); //删除文件
    }

    //关闭资源  用完建议要关闭
    closedir($res);

    //删除自己这个文件夹
    rmdir($path);

    return $this->json(200, '删除安装文件夹成功，正在跳转后台！');
  }
}
