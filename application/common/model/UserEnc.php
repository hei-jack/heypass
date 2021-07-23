<?php
//enc模型
namespace app\common\model;
//引入公共模型
use think\Model;

class UserEnc extends Model{
  /*
   * 获取用户对应的解密测试内容
   * @param int $uid 用户id
   * 
   */
  public function getEnc($uid){
    return $this->field('u_text')->where('u_id',$uid)->find();
  }

  //测试加密数据入库
  /**
   * 
   * @param int $uid 用户id
   * @param string $enc 加密之后的密文
   * 
   */
  public function addEnc($uid,$enc){
    
    $data = array(
      'u_id' => $uid,
      'u_text' => $enc,
    );
   return $this->insert($data);
  }
}