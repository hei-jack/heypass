<?php
//日记数据模型
namespace app\common\model;
use think\Model;
use app\admin\logic\User as UserLogic;

class DiaryData extends Model{
  //新增
  public function add($data){
    if($this->strict(false)->insert($data) !== 1) return false;
    return true;
  }

  //查询
  public function getContent($id){
    //查询内容数据
    $res = $this->field('content')->where('did',$id)->find();
    if($res === null) return null;
    return $res->getAttr('content');
  }

  //内容字段获取器
  public function getContentAttr($value){
    return UserLogic::sendEnc(UserLogic::twoDec($value)); //两轮解密后再进行传输加密
  }
}