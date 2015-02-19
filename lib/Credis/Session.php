<?php
class Credis_Session {
	const SESSION_PREFIX = "frontend";
	const SESSION_NAMESPACE = "core";
	const CHARS_LOWERS                          = 'abcdefghijklmnopqrstuvwxyz';
	const CHARS_UPPERS                          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const CHARS_DIGITS                          = '0123456789';

	protected $_redis;
	protected $_sessionId = false;
	public function __construct($host = '127.0.0.1', $port = 6379, $timeout='', $redis=null){
		if ($redis){
			$this->_redis = $redis;
		}else{
			$this->_redis = new Credis_Client($host, $port);
			$this->_redis->connect();
		}
	}

	public function getSessionId($prefix=self::SESSION_PREFIX){
		if (!$this->_sessionId){
			$cookies=explode("; ", $_SERVER['HTTP_COOKIE']);
			foreach($cookies as $cookie){
				if (strpos("_;".$cookie, ";$prefix=")){
					$this->_sessionId = str_replace("$prefix=", "", $cookie);
					return $this->_sessionId;
				}
			}
			return false;
		}
		return $this->_sessionId;
        }

	public function hasSessionData(){
		$data = $this->_redis->hGet("sess_" . $this->getSessionId(), "data");
		if ($data){
			$data = Credis_SessionHandler::_decodeData($data);
		}
		return $data;
	}

	public function getRandomString($len, $chars = null)
	{
		if (is_null($chars)) {
			$chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
		}
		for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
			$str .= $chars[mt_rand(0, $lc)];
		}
		return $str;
	}

	private $_cookieParams;
	public function attachCookieParams($cookieParams) {
		$this->_cookieParams = $cookieParams;
	}
	protected $_session_ready = false;
	protected function getSession(){
		if (!$this->_session_ready){
			$session = new Credis_SessionHandler($this->_redis);
			session_name(self::SESSION_PREFIX);
			call_user_func_array('session_set_cookie_params', $this->_cookieParams);
			if ($this->hasSessionData()){
				session_start();
			} else {
				$_namespace = new Zend_Session_Namespace(self::SESSION_NAMESPACE,
							Zend_Session_Namespace::SINGLE_INSTANCE);
				$_namespace->_form_key = $this->getRandomString(16);
			}
			$this->_session_ready = true;
		}
		return $this;
	}
	public function getFormKey(){
		$this->getSession();
		if (!$_SESSION[self::SESSION_NAMESPACE]['_form_key']){
			$_SESSION[self::SESSION_NAMESPACE]['_form_key'] = $this->getRandomString(16);
		}
		return $_SESSION[self::SESSION_NAMESPACE]['_form_key'];
	}

	public function isLogined(){
		$this->getSession();
		return $_SESSION[self::SESSION_NAMESPACE]['customer_id'];
	}

	public function getFormKey_old(){
		$session = new Credis_SessionHandler($this->_redis);
		if ($this->hasSessionData()){
			session_name(self::SESSION_PREFIX);
			session_start();
			if (!$_SESSION[self::SESSION_NAMESPACE]['_form_key']){
				$_SESSION[self::SESSION_NAMESPACE]['_form_key'] = $this->getRandomString(16);
			}
			return $_SESSION[self::SESSION_NAMESPACE]['_form_key'];
		} else {
			session_name(self::SESSION_PREFIX);
			$_namespace = new Zend_Session_Namespace(self::SESSION_NAMESPACE, 
						Zend_Session_Namespace::SINGLE_INSTANCE);
			$_namespace->_form_key = $this->getRandomString(16);
			return $_namespace->_form_key;
		}
	}
}
