<?php
class Credis_ClientEx {
	const COMPRESS_PREFIX	= "\x1f\x8b";
	const REDIS_FULLCACHE	= "REDIS_FULLCACHE";
	const COOKIE_PARAMS	= 'Cookie';
	const WHITELIST_KEY	= 'CacheWhitelist';
	const CACHE_STATIC_HTML	= "CacheStaticHtml";
	const APPEND_JS		= "Js";

	protected $_redis;
	public function __construct($host = '127.0.0.1', $port = 6379, $timeout=''){
		$this->_redis = new Credis_Client($host, $port);
		$this->_redis->connect();
	}

	protected function _encodeData($data, $level=1)
	{
		if ($level && strlen($data) >= 10240) {
			$data = gzcompress($data, $level);
			if(!$data) {
				throw new CredisException("Could not compress cache data.");
			}
			return self::COMPRESS_PREFIX.$data;
		}
		return $data;
	}

	protected function _decodeData($data)
	{
		if (substr($data,0,2) == self::COMPRESS_PREFIX) {
			return gzuncompress(substr($data,2));
		}
		return $data;
	}

	public function get($key){
		$data = $this->_redis->get($key);
		if ($data){
			return $this->_decodeData($data);
		}
		return false;
	}

	public function getCookieParams($mageRunCode){
		$data = $this->_redis->hGet(self::REDIS_FULLCACHE, $mageRunCode . self::COOKIE_PARAMS);
		if ($data){
			return unserialize($data);
		}
		return false;
	}
	public function setCookieParams($mageRunCode, $cookieParam=array()){
		return $this->_redis->hSet(self::REDIS_FULLCACHE, $mageRunCode . self::COOKIE_PARAMS, serialize($cookieParam));
	}

	public function is_can_cache($mageRunCode, $uri, $host){
		$uri_arrs = parse_url($uri);
		$whitelist = $this->_redis->hGet(self::REDIS_FULLCACHE, $mageRunCode.self::WHITELIST_KEY);
		
                return array(!$whitelist, 
			($uri_arrs['path'] == "/" 
			    ||($this->_redis->hGet(self::REDIS_FULLCACHE, $mageRunCode.self::CACHE_STATIC_HTML) 
				&& substr($uri_arrs['path'], -5) == ".html")
			    || strpos($whitelist, ";".rtrim($uri_arrs['path'],"/").";")>0)
		);
	}
	public function hit($mageRunCode, $uri, $host, $secure="off", $md5=false){
		list($no_whitelist, $can_cache) = $this->is_can_cache($mageRunCode, $uri, $host);
                if ('on' == $secure){ $host="s:".$host; } else { $host=":".$host; }
		if ($can_cache) {
			if ($md5){
				$data = $this->get(md5($host.$uri));
			} else {
				$data = $this->get($host.$uri);
			}
			return $data;
		}
		return false;
	}

	public function set($key, $data){
		return $this->_redis->set($key, $this->_encodeData($data));
	}

	public function del($key){
		return $this->_redis->del($key);
	}

	public function setRedisCacheParams($key, $val){
		$this->_redis->hSet(self::REDIS_FULLCACHE, $key, $val);
	}
	public function setWhitelist($whitelist){
		$this->_redis->hSet(self::REDIS_FULLCACHE, self::WHITELIST_KEY, ";;$whitelist;");
	}

	public function setCacheSubfixHtml($flg){
		if ($flg){
			$this->_redis->hSet(self::REDIS_FULLCACHE, self::CACHE_STATIC_HTML, '1');
		} else {
			$this->_redis->hDel(self::REDIS_FULLCACHE, self::CACHE_STATIC_HTML);
		}
	}

	public function flushAll(){
		$this->_redis->flushAll();
	}
	
	public function info(){
		return $this->_redis->info();
	}

	public function keys(){
		return $this->_redis->keys("*");
	}
}
