<?php
//用户模块控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\User as UserModel;

class User extends AdminBase {

	//渲染用户列表页
	public function index() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/User/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'limit' => input('post.limit'),  
			        'page' => input('post.page'),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Paging.default');
			if ($result !== true) {
				$this->res['mess'] = '获取失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$data['limit'] = (int)$data['limit'];
			$data['page'] = (int)$data['page'];
			if ($data['limit'] !== 10 || $data['page'] === 0) {
				$this->res['mess'] = '非法请求,请求错误的数据~';
				return json($this->res);
			}
			$model = new UserModel();
			$this->res['total'] = $model->getTotal();
			if ($this->res['total'] === 0) {
				$this->res['status'] = 200;
				$this->res['mess'] = '获取失败,用户为空~';
				return json($this->res);
			}
			$max_page = max_page($this->res['total'], $data['limit']);
			if ($data['page'] > $max_page) {
				$this->res['mess'] = '请求页码超出范围~';
				return json($this->res);
			}
			$this->res['data'] = $model->getUserList($data['page'], $data['limit']);
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			return json($this->res);
		} else {
			return $this->fetch('/user_list');
		}
	}

	//新增亲友
	public function add() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/User/add');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'username' => UserLogic::rsaDecrypt($key, input('post.username')),  
			        'password' => UserLogic::rsaDecrypt($key, input('post.password')),  
			        'nickname' => UserLogic::rsaDecrypt($key, input('post.nickname')),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.add');
			if ($result !== true) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new UserModel();
			if (!$model->add($data, $this->request->ip())) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = '请稍候再试或检查亲友账号是否存在重复';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '新增成功';
			$this->res['data'] = '请到亲友管理页面查看!';
			return json($this->res);
		} else {
			return $this->fetch('/add_user');
		}
	}

	//删除用户接口
	public function del() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/User/index');
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
			$model = new UserModel();
			if (!$model->delUser((int)$data['id'],$this->request->ip())) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = '请稍后再试！';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['data'] = '用户已经被删除，表格即将自动刷新！';
			$this->res['mess'] = '删除成功';
			return json($this->res);
		}
		return json($this->res);
	}
  
	//编辑用户
	public function edit() {
		if($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/User/edit');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'id' => input('post.id','','strip_tags,addslashes'), 
			        'username' => UserLogic::rsaDecrypt($key, input('post.username')),  
			        'nickname' => UserLogic::rsaDecrypt($key, input('post.nickname')),  
			        'state' => input('post.state','','strip_tags,addslashes'),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.edit');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new UserModel();
			if (!$model->edit($data,$this->request->ip())) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '保存成功';
			$this->res['data'] = '请到亲友管理页面查看!';
			return json($this->res);
		} else {
			$data = [
			        'id' => input('get.id','','strip_tags,addslashes'),
			      ];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$model = new UserModel();
			$user = $model->getUserInfo((int)$data['id']);
			if($user === null) return '非法请求，请检查后重试~';
			$this->assign('user',$user);
			return $this->fetch('/edit_user');
		}
	}
}