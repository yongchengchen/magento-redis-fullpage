<?php
class Ycc_Mcache_Model_Observer
{
	const REDIS_FULLPAGE_CACHE_WHITE_LIST = 'dev/redis_cache/whitelist';
	const REDIS_STATIC_HTML = 'dev/redis_cache/static_html';
	const REDIS_JSAPPEND 	= 'dev/redis_cache/append_js';
        public function cacheResponse($observer){
		if (Mage::app()->getStore()->isAdmin()){
			return;
		}

		// is Ajax post request
                if (Mage::app()->getRequest()->isXmlHttpRequest() && Mage::app()->getRequest()->isPost()) { 
                        return;
                }

		if ("GET" == $_SERVER['REQUEST_METHOD'] && !$_GET['_redis_no_cache']
			&& strlen($observer->getResponse()->getBody())>0){
			$uri= $_SERVER['REQUEST_URI'];
			$host = $_SERVER['HTTP_HOST'];
		    try{
			$mageRunCode=Mage::app()->getStore()->getCode();
			include MAGENTO_ROOT . '/includes/redis-config.php';
			$redis = new Credis_ClientEx(REDIS_FULLCACHE_HOST, REDIS_FULLCACHE_PORT, REDIS_FULLCACHE_LIFETIME);
			list($no_whitelist, $can_cache) = $redis->is_can_cache($mageRunCode, $uri, $host);
			if ($no_whitelist){
				$redis->setRedisCacheParams($mageRunCode . Credis_ClientEx::WHITELIST_KEY,
					Mage::getStoreConfig(self::REDIS_FULLPAGE_CACHE_WHITE_LIST));
				$redis->setRedisCacheParams($mageRunCode . Credis_ClientEx::CACHE_STATIC_HTML,
					Mage::getStoreConfig(self::REDIS_STATIC_HTML));
				$redis->setCookieParams($mageRunCode, $this->getCookieParams());
				$jsAppend = Mage::getStoreConfig(self::REDIS_JSAPPEND, null);
				if ($jsAppend){
					$redis->setRedisCacheParams(
						$mageRunCode.Credis_ClientEx::APPEND_JS, 
						$jsAppend);
				}
			}
			if ($can_cache){
				if ('on' == $_SERVER['HTTPS']){ $host="s:".$host; } else { $host=":".$host;}
				$redis->set($host.$uri, $observer->getResponse()->getBody());
			}
		    } catch(Exception $ex){ }
		}
        }
	
	public function CronCleanRedisCache(){
		define('MAGENTO_ROOT', getcwd());
		include MAGENTO_ROOT . '/includes/redis-config.php';
		$redis = new Credis_ClientEx(REDIS_FULLCACHE_HOST, REDIS_FULLCACHE_PORT, REDIS_FULLCACHE_LIFETIME);
		$redis->flushAll();
	}

	private function getCookieParams(){
		$cookie = Mage::getSingleton('core/cookie');
		$cookieParams = array(
		    'lifetime' => $cookie->getLifetime(),
		    'path'     => $cookie->getPath(),
		    'domain'   => $cookie->getConfigDomain(),
		    'secure'   => $cookie->isSecure(),
		    'httponly' => $cookie->getHttponly()
		);

		if (!$cookieParams['httponly']) {
		    unset($cookieParams['httponly']);
		    if (!$cookieParams['secure']) {
			unset($cookieParams['secure']);
			if (!$cookieParams['domain']) {
			    unset($cookieParams['domain']);
			}
		    }
		}

		if (!isset($cookieParams['domain'])) {
		    $cookieParams['domain'] = $cookie->getDomain();
		}
		return $cookieParams;
	}
}
