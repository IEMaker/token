<?php
namespace IEMaker\Token;

use Illuminate\Config\Repository;

class Token {
	protected $config;
	protected $auth;
	protected $path;
	protected $ext = '.token';
	protected $expire = 86400;

	public function __construct(Repository $config) {
		$this->config = $config;
		$this->init();
	}

	protected function init() {
		if ($this->config->has('token')) {
			foreach ($this->config->get('token') as $key => $val) {
				$this->{$key} = $val;
			}
		}
		if (!is_dir($this->path)) {
			if (!mkdir($this->path, 0777, 1)) {
				exit('Failed to initialize Token.');
			}
		}
	}

	public function set($data, $expire = 86500) {
		$data['create_time'] = time();
		$data['expire'] = $expire;

		$token_id = sha1(uniqid(mt_rand()));
		$token_file = $this->path . $token_id . $this->ext;
		$token_data = serialize($data);
		if (!file_put_contents($token_file, $token_data)) {
			return false;
		}
		touch($token_file, time() + $expire);
		return $token_id;
	}

	public function get($code) {
		if (empty($code)) {
			return false;
		}
		$file = $this->path . $code . $this->ext;
		if (!file_exists($file)) {
			return false;
		}
		if (filemtime($file) < time()) {
			$this->del($code);
			return false;
		}
		$data = unserialize(file_get_contents($file));
		$expire = array_key_exists('expire', $data) ? intval($data['expire']) : $this->expire;
		touch($file, time() + $expire);
		return array_diff_key($data, array_flip(['create_time', 'expire']));
	}

	public function edit($code, $data) {
		if (empty($code) || (!is_array($data))) {
			return false;
		}
		$token_file = $this->path . $code . $this->ext;
		if (!file_exists($token_file)) {
			return false;
		}

		$token_data = array_merge(unserialize(file_get_contents($token_file)), $data);
		if (!file_put_contents($token_file, serialize($token_data))) {
			return false;
		}

		$expire = array_key_exists('expire', $token_data) ? intval($token_data['expire']) : $this->expire;
		touch($token_file, time() + $expire);
		return true;
	}

	public function del($token_code) {
		$token_file = $this->path . $token_code . $this->ext;
		if (is_file($token_file)) {
			return unlink($token_file);
		} else {
			return true;
		}
	}

	public function clear() {
		$dh = opendir($this->path);
		while ($file = readdir($dh)) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$file_ext = '.' . pathinfo($file, PATHINFO_EXTENSION);
			if ($file_ext == $this->token_ext) {
				$file = $this->path . $file;
				if (filemtime($file) < time()) {
					unlink($file);
				}
			}
		}
		closedir($dh);
		return true;
	}

	public function __destruct() {
		clearstatcache();
	}
}