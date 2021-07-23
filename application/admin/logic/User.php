<?php
//用户逻辑层模型
namespace app\admin\logic;
//Bip39
use \FurqanSiddiqui\BIP39\BIP39;
//语言包
use \FurqanSiddiqui\BIP39\WordList;

class User
{

  //检查登录的相关信息 方便拦截无效请求
  /*
   * @param string $referer 来路地址
   * @param string $host 当前主机地址（域名）
   * @param string $url 路由地址（登录）
   * @return bool true通过/false未通过
   * 
   */
  public static function checkLogin($referer, $host, $url){
    //如果cookie里面没有login 返回错误信息
    if (cookie('heypass_token') === null) return false;

    //检查来路地址是否登录页面
    //处理来路地址
    $referer = explode("/", $referer);

    $len = count($referer); //获取数组长度

    //如果来源地址域名不等于当前域名
    if ($referer[2] !== $host) return false;

    $remaining = '';  //存储除了域名之外的剩余部分
    for ($i = 3; $i < $len; $i++) {
      $remaining .= '/' . $referer[$i];
    }

    //去除可能存在的参数
    if(strpos($remaining,'?') !== false){
      //如果找到问号 说明可能存在参数 去除问号之后的所有内容（截取问号之前的字符串）
      $remaining = substr($remaining,0,strpos($remaining,'?'));
    }

    // var_dump($remaining);
    // var_dump($url);

    //如果来路地址剩余部分不等于 路由地址 返回错误
    if (strtolower($remaining) !== $url) return false;
    return true;
  }

   //sha256加盐加密  为了防止cookie伪造
    /*
     * hash_hmac 使用 HMAC 方法生成带有密钥的哈希值 参数1加密算法：如md5 sha256 参数2：data 要加密的数据 参数3：密钥（自定义）
     * 此处使用sha256hmac算法
     * @param str $str 要加密的字符串
     * @param str $key 密钥 （使用系统配置cookie_key）
     * return str 返回加密后的结果
     *
     */
    public static function cookieSign($str,$key){
      return hash_hmac('sha256',$str,$key);
    }

    /*
     * AES 128位 cbc模式加密（对称加密）
     * @param1 string $data 要加密的字符串
     * @param2 string $key 加密密钥
     * @param3 string $iv 偏移量
     * @param4 string $len 128/256
     * return string 加密后的密文
     *
     */
  public static function encAes($data, $key, $iv, $len){
    $data = openssl_encrypt($data, 'aes-' . $len . '-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($data);
  }

  /*
   * AES 128位 cbc模式解密
   * @param1 string $data 要解密的字符串
   * @param2 string $key 加密密钥
   * @param3 string $iv 偏移量
   * @param4 string $len 128/256
   * return string 解密后的明文
   *
   */
  public static function decAes($data, $key, $iv, $len){
    $encrypted = base64_decode($data);
    return openssl_decrypt($encrypted, 'aes-' . $len . '-cbc', $key, OPENSSL_RAW_DATA, $iv);
  }

  //密码加密/对比 password_hash
    /*
     * @param1 string $pass 密码原文
     * @param2 string/bool $de_pass 加密后的密码 默认false
     * @return string/bool
     */
  public static function passHash($pass, $dec_pass = false){
    //如果$de_pass是默认值 则说明是加密
    if (!$dec_pass) {
      $options = [
        'cost' => 12,   //cost代表算法使用的 cost 8-10之间最好  暂时 下面salt可以考虑省略 如果在php7以上运行
        //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),  //此函数mcrypt_create_iv 这个函数在PHP 7.1.0弃用,并在PHP 7.2.0移除。 p
        //php7。1.0以上使用random_bytes()函数代替mcrypt_create_iv() 但是php7以上已经移除salt选项 为了兼容故不进行设置
      ];
      //生成加密的字符串 返回给调用处
      return password_hash($pass, PASSWORD_BCRYPT, $options);
    }
    //返回密码对比结果
    return password_verify($pass, $dec_pass);
  }

    /* rsa解密数据
     * @param1 str $key rsa私钥
     * @param2 str $data 要解密的数据
     * return str
     */
  public static function rsaDecrypt($key, $data){
    //处理私钥 因为没有换行还有去掉了头部和结尾信息  config::get thinkphp获取配置信息 wordwrap — 打断字符串为指定数量的字串
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----\n";
    //echo $privateKey;

    //判断私钥是否正确或可用
    $getPrivateKey = openssl_pkey_get_private($privateKey); //这个函数可用来判断私钥是否是可用的，可用，返回资源，私钥可用
    // var_dump(!$getPrivateKey);

    //如果私钥有误(解析失败)
    if (!$getPrivateKey)  return null;  //返回null 同下面保持一致

    
    // 解密数据 私钥解密 $end是用来存储解密结果的变量
    if (!openssl_private_decrypt(base64_decode($data), $end, $privateKey))  return null;
    // var_dump($end);
    return $end;  //返回解密结果
  }

  //检查二次验证密钥是否符合要求 @param string $key 安全密码
  public static function checkTwoAuth($key){

    //先进行简单检查
    if(!self::simpleCheck($key)) return false;

    //检查通过后从session获取该熵值
    $key = session('twoAuth','','admin');

    $len = strlen($key);

    //将转换成功的熵分割为两部分 进行aes解密
    $start = substr($key,0,$len / 2); //截取前一半
    $end = $len === 32 ? substr($key,$len / 2):hex2bin(substr($key,$len / 2)); //截取后一半 32位转二进制变16位字符串 否则iv向量报错
    //实例化用户解密模型
    $model = new \app\common\model\UserEnc();

    $data = $model->getEnc(session('uid','','admin'));
    //查询是否存在解密测试数据
    if($data === null){
      //清除session中的值
      session('twoAuth', null,'admin');
      return false;
      }

    //存在则开始进行解密工作
    $temp_len = $len === 32 ? '128':'256';
    $dec = self::decAes($data->u_text,$start,$end,$temp_len);
    //解密失败返回false
    if($dec === false){
      //清除session中的值
      session('twoAuth', null,'admin');
      return false;
      }
    // var_dump($dec);
    return true;
  }

  //简单检查设置的安全密码格式是否符合要求 以及是否符合bip39要求
  public static function simpleCheck($key){
    $len = mb_strlen($key);
    //总长度不符合要求
    if ($len !== 20 && $len !== 32) return false;

    //校验汉字数量是否符合要求
    $temp = preg_replace('/[，。]+/u', '', $key); //正则替换，和。
    $temp_len = mb_strlen($temp);

    //汉字数量不符合要求
    if ($temp_len !== 16 && $temp_len !== 28) return false;

    //校验符号位置是否正确
    $orders = array(
      array('comma' => '4,14', 'end' => '9,19'),
      array('comma' => '7,23', 'end' => '15,31')
    );

    $comma = mb_strpos($key, "，", 0, 'utf-8') . ',' . mb_strripos($key, '，', true, 'utf-8');
    $end = mb_strpos($key, "。", 0, 'utf-8') . ',' . mb_strripos($key, '。', true, 'utf-8');

    $index = $len === 20 ? 0 : 1;

    if ($orders[$index]['comma'] !== $comma) return false;
    if ($orders[$index]['end'] !== $end) return false;

    //验证通过直接打断为数组
    //此处的正则很奇怪
    $arr = mb_split('[，。]', $key); //打散为数组
    $str = '';
    //for循环去除第一个字 每句的第一个字只是伪装
    for ($i = 0; $i < 4; $i++) {
      $arr[$i] = mb_substr($arr[$i], 1, null, 'utf-8');
      $str .= $arr[$i];
    }


    //将去除后的结果合并后重新打散为数组
    $arr = preg_split('//u', $str, 0, PREG_SPLIT_NO_EMPTY); //重新打散为数组 每个字符
    // var_dump($arr);

    $temp_len = count($arr);
    $str = ''; //重置原有字符串
    //将字符合并为bip39所需格式 遍历添加空格
    for ($i = 0; $i < $temp_len; $i++) {
      $str .= $arr[$i] . ' ';
    }
    $str = trim($str, ' '); //去除最后一位多余的空格
    // var_dump($str);

    //从中文词转换回熵 bip39 需要捕捉可能出现的错误
    try {
      $mnemonic = BIP39::Words($str, WordList::Chinese());
      $key = $mnemonic->entropy; //将加密的熵赋值给$key
    } catch (\Exception $e) {
      // echo $e->getMessage(); 打印错误消息
      //一旦出现错误 不管三七二十一直接返回false
      return false;
    }

    session('twoAuth',$key,'admin');  //将熵存入session
    return true;
  }

  //bip加密
  public static function bipEnc($data){
    //从session获取该熵值
    $key = session('twoAuth','','admin');

    $len = strlen($key);

    //将转换成功的熵分割为两部分 进行aes解密
    $start = substr($key,0,$len / 2); //截取前一半
    $end = $len === 32 ? substr($key,$len / 2):hex2bin(substr($key,$len / 2)); //截取后一半 32位转二进制变16位字符串 否则iv向量报错
    
    //第一轮加密
    $temp_len = $len === 32 ? '128':'256';
    return self::encAes($data,$start,$end,$temp_len);
  }

  //bip解密
  public static function bipDec($data){
    //从session获取该熵值
    $key = session('twoAuth','','admin');

    $len = strlen($key);

    //将转换成功的熵分割为两部分 进行aes解密
    $start = substr($key,0,$len / 2); //截取前一半
    $end = $len === 32 ? substr($key,$len / 2):hex2bin(substr($key,$len / 2)); //截取后一半 32位转二进制变16位字符串 否则iv向量报错
    
    //第一轮加密
    $temp_len = $len === 32 ? '128':'256';
    return self::decAes($data,$start,$end,$temp_len);
  }

  //cookey加密
  public static function cookeyEnc($data){
    //第二轮加密 cookie校验码 先将cookie校验码进行sha256
    $key = hash('sha256',config('app.cookie_key')); //返回64位
    $start = substr($key,16,32);//截取中间32位作为key
    $end = substr($key,52,8) . substr($key,4,8); //截取后面剩余16的中间8位+前面剩余16位中间8位作为$iv
    return self::encAes($data,$start,$end,'256');
  }

  //cookey解密
  public static function cookeyDec($data){
    //第二轮加密 cookie校验码 先将cookie校验码进行sha256
    $key = hash('sha256',config('app.cookie_key')); //返回64位
    $start = substr($key,16,32);//截取中间32位作为key
    $end = substr($key,52,8) . substr($key,4,8); //截取后面剩余16的中间8位+前面剩余16位中间8位作为$iv
    return self::decAes($data,$start,$end,'256');
  }

  //两轮加密
  /*
   * @param string $data 明文数据
   * @return string 加密后的密文
   */
  public static function twoEnc($data){
    //第一轮使用bip加密
    $enc = self::bipEnc($data);
    //第二轮加密cookey加密
    return self::cookeyEnc($enc);
  }

  //两轮解密
  /*
   * @param string $data 密文数据
   * @return string 解密后的明文
   */
  public static function twoDec($data){
    $dec = self::cookeyDec($data);
    return self::bipDec($dec);
  }

  //三轮加密
  public static function threeEnc($data){
    $enc = self::twoEnc($data); //先进行两轮加密
    return self::bipEnc($enc);
  }

  //三轮解密
  public static function threeDec($data){
    //先进行bip解密
    $dec = self::bipDec($data);
    return self::twoDec($dec);
  }

  //send加密 传输加密
  public static function sendEnc($data){
    $key = hash('sha256',config('app.send_key')); //返回64位
    $start = substr($key,32,32);//截取最后32位作为key
    $iv = substr($key,8,16); //截取前面剩余32位中间16位作为$iv
    return openssl_encrypt($data, 'aes-256-cbc', $start, 0, $iv);
  }
}