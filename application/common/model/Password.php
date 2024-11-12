<?php
//密码模型
namespace app\common\model;
use think\Model;
use app\admin\logic\User as UserLogic;

class Password extends Model{

  //添加 @param array $data 要入库的数据包含项cid、p_name、p_pass、p_title、p_url、p_other
  public function add($data){
    //将数据进行安全过滤
    $data = safe_filter_special($data);

    //cid
    $cid = (int)$data['cid'];
    unset($data['cid']);
    //如果备注还有网址为空 就释放了
    // if(strlen($data['p_other']) === 0) unset($data['p_other']);
    // if(strlen($data['p_url']) === 0) unset($data['p_url']);
    //执行三轮加密操作
    foreach($data as $key => $v){
      if($key === 'p_title'){
        // 关联标题不进行加密
        continue;
      }
      $data[$key] = UserLogic::threeEnc($v);
    }
    $data['cid'] = $cid;
    $data['update_time'] = time();
    $data['create_time'] = $data['update_time'];
    $data['uid'] = session('uid','','admin');
    //执行写入操作
    if($this->strict(false)->insert($data) !== 1) return false;
    return true;
  }

  //删除密码
  public function del($id){
    //查询该id是否存在 并且验证用户身份
    $uid = session('uid','','admin');
    //查询该条记录是否存在
    $pass = $this->field('id,uid')->where('id',$id)->find();
    if($pass === null) return false;
    //存在则校验用户身份
    if($uid !== $pass->getAttr('uid')) return false;
    //删除该记录
    $this->destroy($id);
    return true;
  }

  //修改/编辑密码 $data 要入库的数据包含项id、cid、p_name、p_pass、p_title、p_url、p_other
  public function edit($data){
    //id 先执行查询操作
   $id = (int)$data['id'];
   unset($data['id']);
   //查询该id是否存在 并且验证用户身份
   $uid = session('uid','','admin');
   $pass = $this->field('id,uid')->where('id',$id)->find();
   if($pass === null) return false; //查空
   if($uid !== $pass->getAttr('uid')) return false; //身份有误

   //将数据进行安全过滤
   $data = safe_filter_special($data);

   //cid
   $cid = (int)$data['cid'];
   unset($data['cid']);

   //如果备注还有网址为空 就释放了
  //  if(strlen($data['p_other']) === 0) unset($data['p_other']);
  //  if(strlen($data['p_url']) === 0) unset($data['p_url']);

   //执行三轮加密操作
   foreach($data as $key => $v){
    if($key === 'p_title'){
      // 关联标题不进行加密
      continue;
     }
     $data[$key] = UserLogic::threeEnc($v);
   }
   $data['cid'] = $cid;
   $data['update_time'] = time();
   //执行更新操作
   if($this->where('id',$id)->update($data) === 0) return false;
   return true;
  }

  //获取密码记录条数(小计 每个用户 每个分类)
  /*
   * @param int $uid 用户id（一般指当前用户id）
   * @param int $cid 分类id
   * @param string $likeTitle 模糊搜索标题 默认为空
   * @return int 当前用户该分类下的密码记录条数
   */
  public function getSubTotal($uid,$cid,$likeTitle = ""){
    if($cid === 0){
      //如果cid为0 说明是获取当前用户所有分类的密码记录
      $db = $this->field('id')->where('uid',$uid);
      if(mb_strlen($likeTitle) === 0){
        return $db->count();
      }else{
        return $db->where('p_title', 'like', "%${likeTitle}%")->count();
      }
    }else{
      //否则说明是要获取单个分类下的密码记录
      $db = $this->field('id')->where('uid',$uid)->where('cid',$cid);
      if(mb_strlen($likeTitle) === 0){
        return $db->count();
      }else{
        return $db->where('p_title', 'like', "%${likeTitle}%")->count();
      }
    }
    //如果cid等于0 说明是要获取当前用户所有分类的密码记录条数 否则返回单个分类下的记录条数
    //return $cid === 0 ? $this->where('uid',$uid)->count():$this->where('uid',$uid)->where('cid',$cid)->count();
  }

  //查询密码列表
  /*
   * @param int $uid 用户id（一般指当前用户id）
   * @param int $cid 分类id
   * @param int $page 页码
   * @param string $likeTitle 模糊搜索标题 默认为空
   * @return 当前用户该分类下的密码记录
   */
  public function getPwdList($uid,$cid,$page,$limit,$likeTitle = ""){
    if($cid === 0){
      //如果cid为0 说明是获取当前用户所有分类的密码记录
      $db = $this->field('id,cid,p_pass as pass,p_name as name,p_title as title,p_url as url,p_other as other,update_time,create_time')->where('uid',$uid);
      if(mb_strlen($likeTitle) === 0){
        return $db->page($page, $limit)->select();
      }else{
        return $db->where('p_title', 'like', "%${likeTitle}%")->page($page, $limit)->select();
      }
    }else{
      //否则说明是要获取单个分类下的密码记录
      $db = $this->field('id,cid,p_pass as pass,p_name as name,p_title as title,p_url as url,p_other as other,update_time,create_time')->where('uid',$uid)->where('cid',$cid);
      if(mb_strlen($likeTitle) === 0){
        return $db->page($page, $limit)->select();
      }else{
        return $db->where('p_title', 'like', "%${likeTitle}%")->page($page, $limit)->select();
      }
    }
  }

  //获取单个分类下的密码总数
  public function getCatTotal($cid){
    return $this->where('cid',$cid)->count();
  }

  //查询密码信息
  public function getPassInfo($id){
    return $this->field('cid,p_pass as pass,p_name as name,p_title as title,p_url as url,p_other as other')->where('id',$id)->find();
  }

  //账号字段获取器
  public function getNameAttr($value){
    return UserLogic::sendEnc(UserLogic::threeDec($value));
  }

  //密码字段获取器
  public function getPassAttr($value){
    return UserLogic::sendEnc(UserLogic::threeDec($value));
  }

  //关联名称字段获取器
  // public function getTitleAttr($value){
  //   return UserLogic::sendEnc(UserLogic::threeDec($value));
  // }

  //关联网址字段获取器
  public function getUrlAttr($value){
    if(strlen($value) === 0) return $value;
    return UserLogic::sendEnc(UserLogic::threeDec($value));
  }

  //备注字段获取器
  public function getOtherAttr($value){
    if(strlen($value) === 0) return $value;
    return UserLogic::sendEnc(UserLogic::threeDec($value));
  }
}