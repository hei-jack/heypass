<?php
//备忘模型
namespace app\common\model;
use think\Model;
use app\admin\logic\User as UserLogic;
use app\common\model\MemoData;

class Memo extends Model{

  //新增
  public function add($data){
    $cid = (int)$data['cid'];
    unset($data['cid']);
    //先对数据进行安全过滤 允许白名单html通过
    $data = safe_filter_html($data);
    //执行两轮加密操作
    foreach($data as $key => $v){
      $data[$key] = UserLogic::twoEnc($v);
    }

    $data['cid'] = $cid;
    //过滤和加密都完成开始组织数据
    $data['uid'] = session('uid','','admin'); //当前用户id
    //更新时间
    $data['update_time'] = time();
    //创建时间
    $data['create_time'] = $data['update_time'];

    //开启事务
    $this->startTrans();

    try {
      //开始进行数据入库
     $data['mid'] = $this->strict(false)->insertGetId($data);
    //  var_dump($data['mid']);
     //关联模型入库操作
     $model = new MemoData();
     $model->add($data);
     $this->commit();
     return true;
    } catch (\Exception $e) {
      // var_dump($e->getMessage());
      // 回滚事务
      $this->rollback();
      return false;
    }  
  }

  //删除备忘
  public function del($id){
    //查询该条记录是否存在
    $result = $this->field('id,uid')->where('id',$id)->find();
    if($result === null) return false;
    //查询该id是否存在 并且验证用户身份
    $uid = session('uid','','admin');
    //存在则校验用户身份
    if($uid !== $result->getAttr('uid')) return false;
    //删除该记录
    $this->destroy($id);
    //删除对应数据模型的记录
    $model = new MemoData();
    $model->where('mid',$id)->delete();
    return true;
  }

  //编辑备忘 更新 @param array 表单数据 包含id、cid、title、content
  public function edit($data){
    //id 先执行查询操作
   $id = (int)$data['id'];
   unset($data['id']);
   $cid = (int)$data['cid'];
   unset($data['cid']);

   $memo = $this->field('id,uid')->where('id',$id)->find();
   if($memo === null) return false; //查空
   //查询该id是否存在 并且验证用户身份
   $uid = session('uid','','admin');
   if($uid !== $memo->getAttr('uid')) return false; //身份有误

   //将数据进行安全过滤
   $data = safe_filter_html($data);

   //执行两轮加密操作
   foreach($data as $key => $v){
     $data[$key] = UserLogic::twoEnc($v);
   }

   $data['cid'] = $cid;
   $data['update_time'] = time();
   
   //执行更新操作 数据
   $model = new MemoData();

   //开启事务
   $this->startTrans();

   try {
     //执行更新操作 索引
   if($this->strict(false)->where('id',$id)->update($data) === 0) throw new \think\Exception('更新失败了');
   if($model->strict(false)->where('mid',$id)->update($data) === 0) throw new \think\Exception('更新失败了');
    $this->commit();
    return true;
   } catch (\Exception $e) {
    //  var_dump($e->getMessage());
     // 回滚事务
     $this->rollback();
     return false;
   }
  }

  //获取备忘记录条数(小计 每个用户 每个分类)
  /*
   * @param int $uid 用户id（一般指当前用户id）
   * @param int $cid 分类id
   * @return int 当前用户该分类下的备忘记录条数
   */
  public function getSubtotal($uid,$cid){
    /* if($cid === 0){
      //如果cid等于0 说明是要获取当前用户所有分类的密码记录条数
      return $this->where('uid',$uid)->count();
    }else{
      //否则返回单个分类下的记录条数
      return $this->where('uid',$uid)->where('cid',$cid)->count();
    } */
    //如果cid等于0 说明是要获取当前用户所有分类的密码记录条数 否则返回单个分类下的记录条数
    return $cid === 0 ? $this->where('uid',$uid)->count():$this->where('uid',$uid)->where('cid',$cid)->count();
  }

  //查询备忘列表
  /*
   * @param int $uid 用户id（一般指当前用户id）
   * @param int $cid 分类id
   * @param int $page 页码
   * @return int 当前用户该分类下的备忘记录条数
   */
  public function getMemoList($uid,$cid,$page,$limit){
    if($cid === 0){
      //如果cid为0 说明是获取当前用户所有分类的密码记录
      return $this->field('id,cid,title,update_time,create_time')->where('uid',$uid)->page($page, $limit)->select();
    }else{
      //否则说明是要获取单个分类下的密码记录
      return $this->field('id,cid,title,update_time,create_time')->where('uid',$uid)->where('cid',$cid)->page($page, $limit)->select();
    }
    //如果cid为0 说明是获取当前用户所有分类的密码记录 否则说明是要获取单个分类下的密码记录
    // return $cid === 0 ? $this->field('id,cid,p_pass as pass,p_name as name,p_title as title,p_url as url,p_other as other,update_time,create_time')->where('uid',$uid)->select():$this->field('id,cid,p_pass as pass,p_name as name,p_title as title,p_url as url,p_other as other,update_time,create_time')->where('uid',$uid)->where('cid',$cid)->select();
  }

  //获取备忘标题信息
  public function getMemoTitle($id){
    //获取用户id
    $res = $this->field('uid,title')->where('id',$id)->find();
    if($res === null) return null;
    //如果不为空说明查询到了 开始校验用户身份
    $uid = session('uid','','admin');
    if($uid !== $res->getAttr('uid')) return null;
    //校验通过 返回标题
    return $res->getAttr('title');
  }

  //获取备忘索引信息 uid、标题和分类id
  public function getMemoInfo($id){
    //获取用户id
    $res = $this->field('uid,cid,title')->where('id', $id)->find();
    if ($res === null) return null;
    //如果不为空说明查询到了 开始校验用户身份
    $uid = session('uid', '', 'admin');
    if ($uid !== $res->getAttr('uid')) return null;
    //校验通过 返回标题
    return $res;
  }

  //左连接查询获取备忘的所有信息 标题、分类、内容 ------- 虽然查询方便 但此方法不能调用模型的数据进行解密 弃用 --------
  public function getMemoAllInfo($id){
    return $this->alias('m')->field('m.id,title,content')->leftJoin(config('app.database.prefix').'memo_data d','m.id = d.mid')->select();
  }

  //标题字段获取器 两轮解密后 传输加密
  public function getTitleAttr($value){
    return UserLogic::sendEnc(UserLogic::twoDec($value));
  }
}