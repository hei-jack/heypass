<?php
//网站设置控制器
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\common\model\Conf as ConfModel;
use app\admin\logic\User;

class Conf extends AdminBase{

	//系统设置
	public function setting(){
		if ($this->request->isPost()) {
			$referer = $this->request->server('HTTP_REFERER');
			$host = $this->request->host(true);
			$url = url('admin/Conf/setting');
			if (!User::checkLogin($referer, $host, $url)) return json($this->res);
			$key = config('app.rsa_private_key');
			$data = [
				'__token__'  => User::rsaDecrypt($key, $this->request->header('Access-token')),
				'host' => User::rsaDecrypt($key, $this->request->header('Access-token2')),
				'web_site_name' => User::rsaDecrypt($key, input('post.web_site_name')),
				'web_site_url' => User::rsaDecrypt($key, input('post.web_site_url')),
				'web_site_key' => User::rsaDecrypt($key, input('post.web_site_key')),
				'web_site_desc' => User::rsaDecrypt($key, input('post.web_site_desc')),
				'web_site_theme' => User::rsaDecrypt($key, input('post.web_site_theme')),
				'web_index_theme' => User::rsaDecrypt($key, input('post.web_index_theme')),
				'web_index_state' => User::rsaDecrypt($key, input('post.web_index_state')),
				'admin_forbid_ip' => User::rsaDecrypt($key, input('post.admin_forbid_ip')),
			];
			if (strtolower($data['host']) !== $host) return json($this->res);
			$result = $this->validate($data, '\app\common\validate\Conf');
			if ($result !== true) {
				$this->res['mess'] = '保存失败';
				$this->res['data'] = $result;
				return json($this->res);
			}
			$ip = $this->request->ip();
			//验证通过 将数据入库
			if (!ConfModel::setConfig($data)) {
				//记录到日志
				\app\common\model\UserLog::addLog(session('username', '', 'admin'), '修改设置', 3, '系统出错了', $ip);
				$this->res['mess'] = '保存失败';
				$this->res['data'] = '请稍后再试';
				return json($this->res);
			}
			$this->res['status'] = 200;
			$this->res['mess'] = '保存成功';
			$this->res['data'] = url('admin/Conf/setting');
			//记录到日志
			\app\common\model\UserLog::addLog(session('username', '', 'admin'), '修改设置', 1, '修改成功', $ip);
			return json($this->res);
		} else {
			//查询所有设置项
			$all = ConfModel::getAllConfig();
			if ($all === false) return '系统错误';
			// var_dump($all);
			//分配变量给模板
			$this->assign('config', $all);
			return $this->fetch('/setting');
		}
	}
}
