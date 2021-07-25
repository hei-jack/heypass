<?php
//数据库db类 封装pdo

class Db{
  //pdo对象
  private $pdo;
  //表前缀
  private $prefix;

//构造方法
public function __construct($database_info = array(),$drivers = array()){
    //php7以上可以使用NULL 合并运算符 ??
    $type = isset($database_info['type']) ? $database_info['type']:'mysql';		//默认mysql数据库
    $host = isset($database_info['host']) ? $database_info['host']:'localhost'; //数据库地址
    $port = isset($database_info['port']) ? $database_info['port']:'3306'; //数据库端口
    $user = isset($database_info['user']) ? $database_info['user']:'root'; //用户名
    $pass = isset($database_info['pass']) ? $database_info['pass']:'root'; //密码
    $dbname = isset($database_info['dbname']) ? $database_info['dbname']:'test'; //数据库名
    $charset = isset($database_info['charset']) ? $database_info['charset']:'utf8'; //连接字符集
    
    //控制属性 异常模式 如果不传入 默认异常模式
    $dirvers[PDO::ATTR_ERRMODE] = isset($dirvers[PDO::ATTR_ERRMODE]) ? $dirvers[PDO::ATTR_ERRMODE]:PDO::ERRMODE_EXCEPTION;
    
    //连接认证
    try{
        //增加错误抑制符防止意外
        $this->pdo = @new PDO($type . ':host=' . $host . ';port=' . $port . ';dbname=' . $dbname,$user,$pass,$drivers);
    }catch(PDOException $e){
      return false;
    }
    
    //设定字符集
    try{
        $this->pdo->exec("set names " . $charset);
    }catch(PDOException $e){
        return false;
    }
  }


    
}