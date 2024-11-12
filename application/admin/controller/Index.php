<?php
//后台主页控制器
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\logic\User as UserLogic;
use app\common\model\UserLog;
use think\Db;

class Index extends AdminBase{

	//伪主页方法
	public function index(){
		//渲染伪主页页面
		return $this->fetch('/index');
	}

	//真主页方法
	public function main(){
		//先判断是否是管理员
		if ($this->isAdmin()) {
			//如果是管理员 渲染main模板
			//查询统计数据
			//密码条数（总）
			$pass = new \app\common\model\Password();
			$total['pass'] = $pass->count();
			//亲友数量（总量减1）
			$user = new \app\common\model\User();
			$total['user'] = $user->count();
			$total['user'] = $total['user'] - 1;
			//日记数量(总)
			$diary = new \app\common\model\DiaryData();
			$total['diary'] = $diary->count();
			//备忘总数
			$memo = new \app\common\model\Memo();
			$total['memo'] = $memo->count();
			//服务器信息
			//操作系统
			$sys_info['system'] = php_uname('s');
			//运行环境 服务器解译引擎
			$sys_info['software'] = $_SERVER['SERVER_SOFTWARE'];
			//php版本
			$sys_info['php_version'] = PHP_VERSION;

      $sqlite = '';
      if(config('database.type') === "sqlite"){
        $sqlite = 'sqlite_';
      }
			//查询
			$mysql_info = Db::query("SELECT {$sqlite}version() as version");

			//mysql版本
			$sys_info['mysql_verison'] = $mysql_info[0]['version'];
			//服务器ip
			$sys_info['sys_ip'] = $this->request->server('SERVER_ADDR');
			//服务器域名
			$sys_info['host'] = $this->request->server('HTTP_HOST');
			//默认端口
			$sys_info['port'] = $_SERVER['SERVER_PORT'];
			//zend引擎版本
			$sys_info['zend_version'] = zend_version();
			//mb_string拓展
			$sys_info['mb_string'] = function_exists("mb_strlen") ? '支持' : '不支持';
			//php_openssl拓展
			$sys_info['openssl'] = function_exists("openssl_encrypt") ? '支持' : '不支持';
			//curl支持
			$sys_info['curl'] = function_exists('curl_init') ? 'YES' : 'NO';
			//GD库版本
			if (function_exists("gd_info")) {
				//GD库版本
				$gd = gd_info();
				$sys_info['gd_info'] = $gd['GD Version'];
			} else {
				$sys_info['gd_info'] = "未知";
			}
			//访问ip
			$sys_info['user_ip'] = $this->request->ip();
			//分配变量到模板
			$this->assign('total', $total);
			$this->assign('sys_info', $sys_info);
			//并且准备需要的参数
			return $this->fetch('/main');
		} else {
			$uid = session('uid', '', 'admin');
			//密码条数（个人总数）
			$pass = new \app\common\model\Password();
			$total['pass'] = $pass->where('uid', $uid)->count();
			//日记数量(个人总数)
			$diary = new \app\common\model\Diary();
			$total['diary'] = $diary->where('uid', $uid)->where('diary', 1)->count();
			//标记数量
			$total['tag'] = $diary->where('uid', $uid)->where('tag', 1)->count();
			//备忘总数
			$memo = new \app\common\model\Memo();
			$total['memo'] = $memo->where('uid', $uid)->count();
			//分配变量到模板
			$this->assign('total', $total);
			//如果不是管理员 渲染普通用户主页模板
			return $this->fetch('/default');
		}
	}

	//使用教程方法
	public function help(){
		$data = Db::name('help')->where('id', 1)->find();
		$this->assign('data', $data);
		return $this->fetch('/help');
	}

	//用户登录方法
	public function login(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/login');
			//前置检查
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			//获取表单数据
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'username' => UserLogic::rsaDecrypt($key, input('post.username')),
				'password' => UserLogic::rsaDecrypt($key, input('post.password')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.login');
			if ($result !== true) {
				$this->res['mess'] = '登录失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new \app\common\model\User();
			if (!$model->login($data['username'], $data['password'], $this->request->ip())) {
				$this->res['mess'] = '登录失败';
				$this->res['data'] = '账号或密码错误';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '登录成功';
			$this->res['data'] = url('admin/Index/index');
			return json($this->res);
		} else {
			//如果已经登录跳转主页
			if ($this->isLogin()) $this->success('欢迎回来，' . cookie('heypass_nickname'), 'index');
			return $this->fetch('/login');
		}
	}

	//二次验证方法
	public function twoAuth(){
		$enc = new \app\common\model\UserEnc();
		$res = $enc->getEnc(session('uid', '', 'admin'));
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/twoAuth');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'twoAuth' => UserLogic::rsaDecrypt($key, input('post.password')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.two_auth');
			if ($result !== true) {
				$this->res['mess'] = '验证失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			//如果用户还未设置安全密码  直接返回非法请求
			if ($res === null) return json($this->res);
			if (!UserLogic::checkTwoAuth($data['twoAuth'])) {
				//记录到用户日志
				UserLog::addLog(session('username', '', 'admin'), '安全校验', 2, '用户输入安全密码错误！', $this->request->ip());
				$this->res['mess'] = '验证失败';
				$this->res['data'] = '安全密码有误~';
				return json($this->res);
			}
			UserLog::addLog(session('username', '', 'admin'), '安全校验', 1, '用户安全密码校验通过！', $this->request->ip());
			$this->res['status'] = 200;
			$this->res['mess'] = '验证通过';
			$this->res['data'] = url('admin/Index/index');
			return json($this->res);
		} else {
			//如果用户还未设置安全密码 重定向到设置安全密码界面
			if ($res === null) $this->redirect('admin/Index/setTwoAuth');
			//如果用户已经通过安全验证 则重定向到主页
			if (session('twoAuth', '', 'admin') !== null) $this->redirect('admin/Index/index');
			//否则渲染二次验证页面
			return $this->fetch('/two_auth');
		}
	}

	//设置安全密码
	public function setTwoAuth(){
		$enc = new \app\common\model\UserEnc();
		$res = $enc->getEnc(session('uid', '', 'admin'));
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/setTwoAuth');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'twoAuth' => UserLogic::rsaDecrypt($key, input('post.password')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.two_auth');
			if ($result !== true) {
				//记录到用户日志
				UserLog::addLog(session('username', '', 'admin'), '安全校验', 3, '用户设置安全密码失败！', $this->request->ip());
				$this->res['mess'] = '失败了';
				$this->res['data'] = $result;
				return json($this->res);
			}
			if ($res !== null) return json($this->res);
			//验证通过开始进行安全密码校验工作
			if (!UserLogic::simpleCheck($data['twoAuth'])) return json($this->res);
			$key = session('twoAuth', '', 'admin');
			$len = strlen($key);
			$start = substr($key, 0, $len / 2);
			$end = $len === 32 ? substr($key, $len / 2) : hex2bin(substr($key, $len / 2));
			$temp_len = $len === 32 ? '128' : '256';
			if ($enc->addEnc(session('uid', '', 'admin'), UserLogic::encAes(get_rand_token(), $start, $end, $temp_len)) !== 1) {
				session('twoAuth', null, 'admin');
				return json($this->res);
			}
			//记录到用户日志
			UserLog::addLog(session('username', '', 'admin'), '安全校验', 1, '用户设置安全密码成功！', $this->request->ip());
			//存入成功则返回成功信息并跳转
			$this->res['status'] = 200;
			$this->res['mess'] = '设置成功~';
			$this->res['data'] = url('admin/Index/twoAuth');
			return json($this->res);
		} else {
			//如果不是post请求
			if ($res !== null) return '非法请求，禁止访问此页面！';
			//否则渲染模板
			return $this->fetch('/set_two_auth');
		}
	}

	//用户退出登录方法
	public function logout(){
		//记录到用户日志
		UserLog::addLog(session('username', '', 'admin'), '退出', 1, '用户退出成功！', $this->request->ip());
		//清除cookie和session
		$this->clear();
		$this->success('退出成功', 'admin/Index/login');
	}

	//修改密码方法
	public function myPwd(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/myPwd');
			if (!UserLogic::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => UserLogic::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => UserLogic::rsaDecrypt($key, $this->request->header('Access-token2')),
				'password' => UserLogic::rsaDecrypt($key, input('post.old_password')),
				'new_password' => UserLogic::rsaDecrypt($key, input('post.new_password')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\User.mypwd');
			if ($result !== true) {
				$this->res['mess'] = '修改失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$model = new \app\common\model\User();
			$ip = $this->request->ip();
			if (!$model->changeMyPwd($data['password'], $data['new_password'])) {
				$this->res['data'] = '旧密码错误~';
				UserLog::addLog(session('username', '', 'admin'), '修改密码', 0, $this->res['data'], $ip);
				$this->res['mess'] = '修改失败';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '修改成功';
			$this->res['data'] = url('admin/Index/login');
			UserLog::addLog(session('username', '', 'admin'), '修改密码', 1, '修改成功', $ip);
			$this->clear();
			return json($this->res);
		} else {
			return $this->fetch('/mypass');
		}
	}

	//获取系统日志接口
	public function getLog(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/main');
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
			$this->res['total'] = UserLog::getTotal();
			if ($this->res['total'] === 0) {
				$this->res['status'] = 200;
				$this->res['mess'] = '获取失败,系统日志为空~';
				return json($this->res);
			}
			$max_page = max_page($this->res['total'], $data['limit']);
			if ($data['page'] > $max_page) {
				$this->res['mess'] = '请求页码超出范围~';
				return json($this->res);
			}
			$this->res['data'] = UserLog::getLog($data['page'], $data['limit']);
			$this->res['status'] = 200;
			$this->res['mess'] = '获取成功';
			return json($this->res);
		}
		$this->res['data'] = '';
		return json($this->res);
	}

	//清空系统日志接口
	public function delLog(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Index/main');
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
				$this->res['mess'] = '清空失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			if (!UserLog::clearLog($this->request->ip())) {
				$this->res['mess'] = '清空失败';
				$this->res['data'] = '请稍后再试！';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['data'] = '日志已经清空成功';
			$this->res['mess'] = '清空成功';
			return json($this->res);
		}
		return json($this->res);
	}
}
