<?php
//分类模型
namespace app\common\model;
use think\Model;

class Category extends Model{

  //新增类别
  public function add($data,$ip){
    //先对数据进行安全过滤
    $data = safe_filter($data);
    try{
     //开始进行数据入库
     if($this->strict(false)->insert($data) !== 1) return false;
    }catch(\Exception $e){
      //唯一键冲突
      return false;
    }
    
    //记录到日志
    UserLog::addLog(session('username','','admin'),'新增分类',1,'新增分类成功:' . $data['c_zh'],$ip);
    return true;
  }

  //删除类别
  public function del($id){
    //检查该分类下是否有密码记录
    $this->destroy($id);
    return true;
  }

  //获取分类总数
  public function getTotal(){
    return $this->count();
  }

  public function getCatList($page,$limit){
    //获取密码分类列表
    return $this->field('id,c_en as en,c_zh as zh')->page($page, $limit)->select();
  }

  //查询分类信息 根据id
 public function getCatInfo($id){
    return $this->where('id',$id)->find();
 }

 //更新分类信息
  /*
   * @param array $data 表单数据 id,u_en,u_zh
   */
  public function edit($data){
    //进行安全过滤
    $data = safe_filter($data);
    //将参数类型转换
    $data['id'] = (int)$data['id'];
    $update = $this->where('id',$data['id'])->update($data);
    //更新失败
    if($update === 0) return false;
    return true;
  }

 /*
  * 英文名称字段获取器 处理
  */
  public function getEnAttr($value){
      return htmlentities($value,ENT_QUOTES);
  }

  /*
   * 中文名称字段获取器
   * @param mixed $value 查询到的原始数据
   * @return 处理后的值
   */
  public function getZhAttr($value){
    return htmlentities($value,ENT_QUOTES);
  }

}