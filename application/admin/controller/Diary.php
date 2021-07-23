<?php
//日记模块控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\Diary as DiaryModel;
use app\common\model\DiaryData;

class Diary extends AdminBase {

	//日记列表
	public function index() {
		if($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'year' => input('post.year','','addslashes'),  
			        'month' => input('post.month','','addslashes'),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.diary_list');
			if ($result !== true) {
				$this->res['mess'] = '获取失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$data['year'] = (int)$data['year'];
			$data['month'] = (int)$data['month'];
			$model = new DiaryModel();
			$this->res['total'] = $model->getSubtotal($data['year'],$data['month']);
			if ($this->res['total'] === 0) {
				$this->res['status'] = 200;
				$this->res['mess'] = '获取成功';
				$this->res['data'] = '但是数据空空如也~';
				return json($this->res);
			}
			$this->res['data'] = $model->getMonthList($data['year'], $data['month']);
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			return json($this->res);
		}
		return $this->fetch('./diary_list');
	}

	//新增标记
	public function addTag() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id','','addslashes'),  
			        'date' => input('post.date','addslashes'), 
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.tag');
			if ($result !== true) {
				$this->res['mess'] = '标记失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new DiaryModel();
			if (!$model->addTag($data)) {
				$this->res['mess'] = '标记失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '标记成功';
			$this->res['data'] = '是的你没听错!';
			return json($this->res);
		}
		return json($this->res);
	}

	//取消标记
	public function delTag() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id','','addslashes'),  
			        'date' => input('post.date','addslashes'), 
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.tag');
			if ($result !== true) {
				$this->res['mess'] = '取消失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new DiaryModel();
			if (!$model->delTag((int)$data['id'])) {
				$this->res['mess'] = '取消失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '取消成功';
			$this->res['data'] = '取消了就什么都没有了!';
			return json($this->res);
		}
		//如果不是post请求 直接返回
		return json($this->res);
	}

	//添加日记
	public function add() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/add');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$send_key = hash('sha256',config('app.send_key'));
			$start = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'date' => UserLogic::rsaDecrypt($key, input('post.date')),  
			        'content' => openssl_decrypt(input('post.content'), 'aes-256-cbc', $start, 0, $iv),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.diary_add');
			if ($result !== true) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new DiaryModel();
			if (!$model->add($data)) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = '请稍候再试或检查该日记已经存在';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '新增成功';
			$this->res['data'] = '请到日记列表页面查看!';
			return json($this->res);
		} else {
			$send_key = hash('sha256',config('app.send_key'));
			$key = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			return $this->fetch('/add_diary');
		}
	}

	//查看日记
	public function diary() {
		if ($this->request->isGet()) {
			$data = [
			        'id' => input('get.id', '', 'strip_tags,addslashes'),
			      ];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$data['id'] = (int)$data['id'];
			$model = new DiaryModel();
			$title = $model->getDiaryTitle($data['id']);
			if ($title === null) return '非法请求，请检查后重试~';
			$data_model = new DiaryData();
			$content = $data_model->getContent($data['id']);
			if ($content === null) return '系统错误，请联系管理员！';
			$send_key = hash('sha256', config('app.send_key'));
			$key = substr($send_key, 32, 32);
			$iv = substr($send_key, 8, 16);
			$this->assign('token2', $key);
			$this->assign('sign2', $iv);
			$this->assign('title', $title);
			$this->assign('content', $content);
			return $this->fetch('/view_misc');
		}
		return json($this->res);
	}

	//编辑日记
	public function edit() {
		if($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/edit');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$send_key = hash('sha256',config('app.send_key'));
			$start = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id','','addslashes'),  
			        'date' => UserLogic::rsaDecrypt($key,input('post.date')),  
			        'content' => openssl_decrypt(input('post.content'), 'aes-256-cbc', $start, 0, $iv),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.diary_edit');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new DiaryModel();
			if (!$model->edit($data)) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '保存成功';
			$this->res['data'] = '请到备忘列表页面查看!';
			return json($this->res);
		} else {
			$data = [
			        'id' => input('get.id','','strip_tags,addslashes'),
			      ];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$data['id'] = (int)$data['id'];
			$model = new DiaryModel();
			$date = $model->getDiaryInfo($data['id']);
			if ($date === null) return '非法请求，请检查后重试~';
			$data_model = new DiaryData();
			$content = $data_model->getContent($data['id']);
			if ($content === null) return '系统错误，请联系管理员！';
			$send_key = hash('sha256',config('app.send_key'));
			$key = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			$this->assign('date',$date);
			$this->assign('content',$content);
			return $this->fetch('/edit_diary');
		}
	}

	//删除日记
	public function del() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Diary/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id','','addslashes'),
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Paging.idt');
			if ($result !== true) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new DiaryModel();
			if (!$model->del((int)$data['id'])) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = '请稍后再试！';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['data'] = '该条备忘删除成功，表格即将自动刷新！';
			$this->res['mess'] = '删除成功';
			return json($this->res);
		}
		return json($this->res);
	}
}