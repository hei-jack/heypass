<?php
//密码模块控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\Password as PwdModel;
use app\common\model\Category;

class Password extends AdminBase {

	//获取密码列表
	public function index() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Password/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'limit' => input('post.limit'),  
			        'page' => input('post.page'),  
			        'id' => input('post.cid'), 
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Paging.ilpt');
			if ($result !== true) {
				$this->res['mess'] = '获取失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$data['limit'] = (int)$data['limit'];
			$data['page'] = (int)$data['page'];
			$data['id'] = (int)$data['id'];
			if ($data['limit'] !== 10 || $data['page'] === 0) {
				$this->res['mess'] = '非法请求,请求错误的数据~';
				return json($this->res);
			}
			$model = new PwdModel();
			$uid = session('uid','','admin');
			$this->res['total'] = $model->getsubTotal($uid,$data['id']);
			if ($this->res['total'] === 0) {
				$this->res['status'] = 200;
				$this->res['mess'] = '获取失败,当前分类下数据为空~';
				return json($this->res);
			}
			$max_page = max_page($this->res['total'], $data['limit']);
			if ($data['page'] > $max_page) {
				$this->res['mess'] = '请求页码超出范围~';
				return json($this->res);
			}
			$this->res['data'] = $model->getPwdList($uid,$data['id'],$data['page'], $data['limit']);
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			return json($this->res);
		} else {
			$model = new Category();
			$cat = $model->field('id,c_zh as zh')->all();
			$this->assign('cat',$cat);
			$send_key = hash('sha256',config('app.send_key'));
			$key = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			return $this->fetch('/pwd_list');
		}
	}

	//新增密码
	public function add() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Password/add');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'cid' => input('post.cid'),  
			        'p_name' => UserLogic::rsaDecrypt($key, input('post.username')),  
			        'p_pass' => UserLogic::rsaDecrypt($key, input('post.password')),  
			        'p_title' => UserLogic::rsaDecrypt($key, input('post.title')),  
			        'p_url' => UserLogic::rsaDecrypt($key, input('post.url')),  
			        'p_other' => UserLogic::rsaDecrypt($key, input('post.other')),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Password.add');
			if ($result !== true) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new PwdModel();
			if (!$model->add($data)) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '新增成功';
			$this->res['data'] = '请到密码列表页面核对!';
			return json($this->res);
		} else {
			$model = new Category();
			$cat = $model->field('id,c_zh as zh')->all();
			$this->assign('empty','<option value="-2" selected="selected">-- 请先添加密码分类 --</option>');
			$this->assign('cat',$cat);
			return $this->fetch('/add_pass');
		}
	}

	//删除密码
	public function del() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Password/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			         '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			         'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			         'id' => UserLogic::rsaDecrypt($key, input('post.id')),  
			       ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Paging.idt');
			if ($result !== true) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new PwdModel();
			if (!$model->del((int)$data['id'])) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = '请稍后再试！';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['data'] = '该条密码删除成功，表格即将自动刷新！';
			$this->res['mess'] = '删除成功';
			return json($this->res);
		}
		return json($this->res);
	}

	//编辑密码
	public function edit() {
		if($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Password/edit');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id'), 
			        'cid' => input('post.cid'),  
			        'p_name' => UserLogic::rsaDecrypt($key, input('post.username')),  
			        'p_pass' => UserLogic::rsaDecrypt($key, input('post.password')),  
			        'p_title' => UserLogic::rsaDecrypt($key, input('post.title')),  
			        'p_url' => UserLogic::rsaDecrypt($key, input('post.url')),  
			        'p_other' => UserLogic::rsaDecrypt($key, input('post.other')),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Password.edit');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new PwdModel();
			if (!$model->edit($data)) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '保存成功';
			$this->res['data'] = '请到密码列表页面核对!';
			return json($this->res);
		} else {
			$data = [
			        'id' => input('get.id','','strip_tags,addslashes'),
			      ];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$model = new PwdModel();
			$pass = $model->getPassInfo((int)$data['id']);
			if($pass === null) return '非法请求，请检查后重试~';
			$model = new Category();
			$cat = $model->field('id,c_zh as zh')->all();
			$send_key = hash('sha256',config('app.send_key'));
			$key = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			$this->assign('empty','<option value="-2" selected="selected">-- 请先添加密码分类 --</option>');
			$this->assign('cat',$cat);
			$this->assign('pass',$pass);
			return $this->fetch('/edit_pass');
		}
	}

  //随机密码页面
	public function rand() {
		return $this->fetch('/rand_pass');
	}
}