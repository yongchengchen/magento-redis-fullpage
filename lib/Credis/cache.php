<?php
//some self define params
//_redis_no_cache
//if has params like "customer_id", we need to do session check
if ("GET" == $_SERVER['REQUEST_METHOD'] && !$_GET['_redis_no_cache']){
	try{
		include MAGENTO_ROOT . '/includes/redis-config.php';
		if (REDIS_FULLCACHE_ON){
		$redis = new Credis_ClientEx(REDIS_FULLCACHE_HOST, REDIS_FULLCACHE_PORT, REDIS_FULLCACHE_LIFETIME);
		$host = $_SERVER['HTTP_HOST'];
		$uri= $_SERVER['REQUEST_URI'];
		$data = $redis->hit($mageRunCode, $uri, $host, $_SERVER['HTTPS']);
		if ($data){ 
			if (REDIS_SESSION_CHECK_SENSER_WORD && $_GET[REDIS_SESSION_CHECK_SENSER_WORD]){
				$ss_redis = new Credis_Session(REDIS_FULLCACHE_HOST, 
						REDIS_SESSION_PORT, REDIS_FULLCACHE_LIFETIME);
				$ss_redis->attachCookieParams($redis->getCookieParams($mageRunCode));
				//$ss_redis->getFormKey();
				$sessions = $_SESSION[Credis_Session::SESSION_NAMESPACE];
				if ($sessions['visitor_data'] && $sessions['visitor_data']['customer_id']){
					$loginid = $sessions['visitor_data']['customer_id'];
				}

				if ($loginid == $_GET[REDIS_SESSION_CHECK_SENSER_WORD]){
					echo $data;die;
				}
			} else {
				echo $data;die;
			}
		}
		}
	} catch(Exception $ex){
		echo $ex->getMessage();die;
	}
}
