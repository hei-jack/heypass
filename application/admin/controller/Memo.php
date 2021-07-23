<?php
//备忘模块控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\Category;
use app\common\model\Memo as MemoModel;
use app\common\model\MemoData;

class Memo extends AdminBase {

	//备忘列表
	public function index() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Memo/index');
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
			$model = new MemoModel();
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
			$this->res['data'] = $model->getMemoList($uid,$data['id'],$data['page'], $data['limit']);
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
			return $this->fetch('/memo_list');
		}
	}

	//新增备忘
	public function add() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Memo/add');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$send_key = hash('sha256',config('app.send_key'));
			$start = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$data = [
			        '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			        'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			        'cid' => input('post.cid'),  
			        'title' => UserLogic::rsaDecrypt($key, input('post.title')),  
			        'content' => openssl_decrypt(input('post.content'), 'aes-256-cbc', $start, 0, $iv),  
			      ];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.add');
			if ($result !== true) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new MemoModel();
			if (!$model->add($data)) {
				$this->res['mess'] = '新增失败';
				$this->res['data'] = '请稍候再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '新增成功';
			$this->res['data'] = '请到备忘列表页面查看!';
			return json($this->res);
		} else {
			$model = new Category();
			$cat = $model->field('id,c_zh as zh')->all();
			$send_key = hash('sha256',config('app.send_key'));
			$key = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			$this->assign('empty','<option value="-2" selected="selected">-- 请先添加密码分类 --</option>');
			$this->assign('cat',$cat);
			return $this->fetch('/add_memo');
		}
	}

	//删除备忘接口
	public function del() {
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Memo/index');
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
			$model = new MemoModel();
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

	//编辑备忘
	public function edit() {
		if($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Memo/edit');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$send_key = hash('sha256',config('app.send_key'));
			$start = substr($send_key,32,32);
			$iv = substr($send_key,8,16);
			$data = [
			      '__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),  
			      'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')), 
			      'id' => input('post.id'),  
			      'cid' => input('post.cid'),  
			      'title' => UserLogic::rsaDecrypt($key, input('post.title')),  
			      'content' => openssl_decrypt(input('post.content'), 'aes-256-cbc', $start, 0, $iv),  //内容太长 前端rsa加密出错 故更换为aes-256-cbc加密
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Misc.memo_edit');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new MemoModel();
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
			$model = new MemoModel();
			$memo = $model->getMemoInfo($data['id']);
			if ($memo === null) return '非法请求，请检查后重试~';
			$data_model = new MemoData();
			$content = $data_model->getContent($data['id']);
			if ($content === null) return '系统错误，请联系管理员！';
			$cat_model = new Category();
			$cat = $cat_model->field('id,c_zh as zh')->all();
			//将密钥和向量分配到页面
			$send_key = hash('sha256',config('app.send_key'));
			//返回64位
			$key = substr($send_key,32,32);
			//截取最后32位作为key
			$iv = substr($send_key,8,16);
			//截取前面剩余32位中间16位作为$iv
			$this->assign('token2',$key);
			$this->assign('sign2',$iv);
			$this->assign('empty','<option value="-2" selected="selected">-- 请先添加密码分类 --</option>');
			$this->assign('cat',$cat);
			$this->assign('memo',$memo);
			$this->assign('content',$content);
			return $this->fetch('/edit_memo');
		}
	}
  
	//查看备忘页面
	public function memo() {
		if ($this->request->isGet()) {
			$data = [
			        'id' => input('get.id', '', 'strip_tags,addslashes'),
			      ];
			$result = $this->validate($data, '\app\common\validate\Paging.id');
			if ($result !== true) return '非法请求，请检查后重试~';
			$data['id'] = (int)$data['id'];
			$model = new MemoModel();
			$title = $model->getMemoTitle($data['id']);
			if ($title === null) return '非法请求，请检查后重试~';
			$data_model = new MemoData();
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
}