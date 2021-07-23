<?php
//分类模块控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\Category as CatModel;

class Category extends AdminBase{

	//获取密码分类
	public function index(){
		if ($this->request->isPost()) {
			//如果是post请求
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Category/index');
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
			if ($data['limit'] !== 20 || $data['page'] === 0) {
				$this->res['mess'] = '非法请求,请求错误的数据~';
				return json($this->res);
			}
			$model = new CatModel();
			$this->res['total'] = $model->getTotal();
			if ($this->res['total'] === 0) {
				$this->res['status'] = 200;
				$this->res['mess'] = '获取失败,密码分类为空~';
				return json($this->res);
			}
			$max_page = max_page($this->res['total'], $data['limit']);
			if ($data['page'] > $max_page) {
				$this->res['mess'] = '请求页码超出范围~';
				return json($this->res);
			}
			$this->res['data'] = $model->getCatList($data['page'], $data['limit']);
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			return json($this->res);
		} else {
			return $this->fetch('/cat_list');
		}
	}

	//新增密码分类
	public function add(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Category/add');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'c_en' => UserLogic::rsaDecrypt($key, input('post.en')),
				'c_zh' => UserLogic::rsaDecrypt($key, input('post.zh')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Category.add');
			if ($result !== true) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new CatModel();
			if (!$model->add($data, $this->request->ip())) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = '请稍候再试或检查分类标识是否存在重复';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '新增成功';
			$this->res['data'] = '请到密码分类页面查看!';
			return json($this->res);
		} else {
			return $this->fetch('/add_cat');
		}
	}

	//删除密码分类
	public function del(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Category/index');
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
			$model = new CatModel();
			if (!$model->del((int)$data['id'])) {
				$this->res['mess'] = '删除失败';
				$this->res['data'] = '请稍后再试！';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['data'] = '该分类删除成功，表格即将自动刷新！';
			$this->res['mess'] = '删除成功';
			return json($this->res);
		}
		return json($this->res);
	}

	//编辑密码分类
	public function edit(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Category/edit');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'id' => input('post.id', '', 'strip_tags,addslashes'),
				'c_en' => UserLogic::rsaDecrypt($key, input('post.en')),
				'c_zh' => UserLogic::rsaDecrypt($key, input('post.zh')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Category.edit');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new CatModel();
			if (!$model->edit($data)) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '保存成功';
			$this->res['data'] = '请到密码分类页面查看!';
			return json($this->res);
		} else {
			$data = [
				'id' => input('get.id', '', 'strip_tags,addslashes'),
			];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$model = new CatModel();
			$cat = $model->getCatInfo((int)$data['id']);
			if ($cat === null) return '非法请求，请检查后重试~';
			$this->assign('cat', $cat);
			return $this->fetch('/edit_cat');
		}
	}

	//获取单个分类下密码总数
	public function getSubtotal(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Category/index');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  //token令牌
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), //域名 rsa解密
				'id' => input('post.id', '', 'strip_tags,addslashes'),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Paging.idt'); //调用验证器进行验证 id场景
			if ($result !== true) {
				$this->res['mess'] = '非法请求';
				$this->res['data'] = '非法请求数据';
				return json($this->res);
			}
			$model = new \app\common\model\Password();
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			$this->res['data'] = $model->getCatTotal((int)$data['id']);
			return json($this->res);
		}
		return json($this->res);
	}
}
