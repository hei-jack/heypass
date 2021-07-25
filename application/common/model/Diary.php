<?php
//日记模型
namespace app\common\model;
use think\Model;
use app\admin\logic\User as UserLogic;

class Diary extends Model{
  
  //添加标记 @param array $data 包含id、date 字符串
  public function addTag($data){
    $data['id'] = (int)$data['id'];
    //获取用户id
    $data['uid'] = session('uid','','admin');
    //如果id不为0 说明已有数据 直接更新即可
    if($data['id'] !== 0){
      //如果id不等于0 进行更新操作
      //先验证用户身份
      $result = $this->field('uid')->where('id',$data['id'])->find();
      if($result === null) return false;
      if($result->getAttr('uid') !== $data['uid']) return false;
      $update = $this->where('id',$data['id'])->update(['tag' => 1]);
      //身份验证通过开始进行更新操作
      if($update === 0) return false;
      return true;
    }
    //id为0 直接释放
    unset($data['id']);
    //截取日期字符串
    $data['year'] = (int)substr($data['date'],0,4);
    $data['month'] = (int)substr($data['date'],4,2);
    $data['day'] = (int)substr($data['date'],6,2);
    //组织唯一识别码
    $data['diary_no'] = $data['date'] . '-' . $data['uid'];
    $data['tag'] = 1;
    $data['diary'] = 0; 
    //日记未完成 因为没有id
    
    //防止出现用户重复插入 唯一键冲突
    try{
      //执行写入操作
    if($this->strict(false)->insert($data) !== 1) return false;
    return true;
    }catch(\Exception $e){
      return false;
    }
  }

  //取消标记
  public function delTag($id){
    //先查询数据
    $result = $this->field('uid')->where('id',$id)->find();
    if($result === null) return false;
    //获取用户id
    $uid = session('uid','','admin');
    //用户id不等 说明用户身份验证不通过
    if($result->getAttr('uid') !== $uid) return false;

    //验证通过开始更新数据(根据标签状态)
    $update = $this->where('id',$id)->update(['tag' => 0]);
    //身份验证通过开始进行更新操作
    if($update === 0) return false;
    return true;
  }

  //新增日记 @param array $data 包含date、content
  public function add($data){
    //执行安全过滤操作
    $data = safe_filter_html($data);

    $data['uid'] = session('uid','','admin');
    // var_dump($data['uid']);

    $data['diary_no'] = $data['date'] . '-' . $data['uid'];
    //先查询是否该日记索引是否已经存在
    $result = $this->field('id,diary')->where('diary_no',$data['diary_no'])->find();

    //说明日记已经存在 直接返回false
    if($result !== null && $result->getAttr('diary') !== 0) return false;

    //截取日期字符串
    $data['year'] = (int)substr($data['date'],0,4);
    $data['month'] = (int)substr($data['date'],4,2);
    $data['day'] = (int)substr($data['date'],6,2);
    $data['tag'] = 0;
    $data['diary'] = 1; //要设置为已完成
    $data['update_time'] = time();
    $data['create_time'] = $data['update_time'];
    //执行两轮加密操作
    $data['content'] = UserLogic::twoEnc($data['content']);

    //实例化日记数据模型
    $model = new DiaryData();

    // 开启事务
    $this->startTrans();

    try{
    //根据日记索引是否存在进行不同操作
    //日记索引不存在则创建 否则更新索引中的diary字段
    if($result === null){
      $data['did'] = $this->strict(false)->insertGetId($data);
    }else{
      $data['did'] = $result->getAttr('id');
      $this->where('id',$result->getAttr('id'))->update(['diary' => 1]);
    }
    //调用日记数据模型进行插入操作
    //开始进行数据入库
    $model->strict(false)->insert($data);
    // 提交事务
    $this->commit();
    return true;
    }catch(\Exception $e){
      // var_dump($e->getMessage());
      //回滚事务
      $this->rollback();
      return false;
    }
  }

  //更新日记内容 date、content、id
  public function edit($data){
    //id 先执行查询操作
   $id = (int)$data['id'];
   unset($data['id']);
   unset($data['date']);

   $diary = $this->field('id,uid,diary')->where('id',$id)->find();
   if($diary === null) return false; //查空

   //查询该id是否存在 并且验证用户身份
   $uid = session('uid','','admin');
   if($uid !== $diary->getAttr('uid')) return false; //身份有误

   //还要校验日志是否存在
   if($diary->getAttr('diary') !== 1) return false;

   //将数据进行安全过滤
   $data = safe_filter_html($data);

   //执行两轮加密
   $data['content'] = UserLogic::twoEnc($data['content']);

   $data['update_time'] = time();
   
   //执行更新操作 数据
   $model = new DiaryData();

  //  var_dump($data);

   //防止更新失败
   try{
    if($model->where('did',$id)->update($data) === 0) return false;
    return true;
   }catch(\Exception $e){
     return false;
   }
  }

  //删除日记
  public function del($id){
    //查询该条记录是否存在
    $result = $this->field('id,uid,diary')->where('id',$id)->find();
    if($result === null) return false;
    //查询该id是否存在 并且验证用户身份
    $uid = session('uid','','admin');
    //存在则校验用户身份
    if($uid !== $result->getAttr('uid')) return false;
    //还要校验日志是否存在
    if($result->getAttr('diary') !== 1) return false;
    
    //删除对应数据模型的记录
    $model = new DiaryData();

    // 开启事务
    $this->startTrans();

    try{
      //更新索引记录
    if($this->where('id',$id)->update(['diary' => 0]) === 0) throw new \think\Exception('更新失败了');
    if($model->where('did',$id)->delete() === 0) throw new \think\Exception('删除失败了');
    // 提交事务
    $this->commit();
    return true;
     }catch(\Exception $e){
       //回滚事务
      $this->rollback();
      return false;
     }
    
  }

  //获取日记标题(日期)
  public function getDiaryTitle($id){
    //获取用户id
    $res = $this->field('uid,diary_no as title,diary')->where('id',$id)->find();
    if($res === null) return null;
    //如果不为空说明查询到了 开始校验用户身份
    $uid = session('uid','','admin');
    if($uid !== $res->getAttr('uid')) return null;
    //还要校验日志是否存在
    if($res->getAttr('diary') !== 1) return null;
    //校验通过 返回标题
    return $res->getAttr('title');
  }

  //字段获取器 被别名为title的才会进行
  public function getTitleAttr($value){
    //先截取字符串的日期部分
    $value = substr($value,0,8);
    //在第四位第六位第八位添加
    $value = substr($value,0,4) . '年' . substr($value,4,2) . '月' . substr($value,6) . '日';
    //传输加密
    return UserLogic::sendEnc($value);
  }

  //获取日记索引信息 返回日记唯一识别编号
  public function getDiaryInfo($id){
     //获取用户id
     $res = $this->field('uid,diary_no,diary')->where('id', $id)->find();
     if ($res === null) return null;
     //如果不为空说明查询到了 开始校验用户身份
     $uid = session('uid', '', 'admin');
     if ($uid !== $res->getAttr('uid')) return null;
     //还要校验日志是否存在
     if($res->getAttr('diary') !== 1) return null;
    //  var_dump($res->getAttr('diary_no'));
     //校验通过 返回处理后的日期 传输加密
     return UserLogic::sendEnc(substr($res->getAttr('diary_no'),0,8));
  }

  //获取当月数据条数小计
  public function getSubtotal($year,$month){
    return $this->where('uid',session('uid','','admin'))->where('year',$year)->where('month',$month)->count();
  }

  //获取单月份数据列表
  public function getMonthList($year,$month){
    return $this->field('id,day,diary,tag')->where('uid',session('uid','','admin'))->where('year',$year)->where('month',$month)->select();
  }
}