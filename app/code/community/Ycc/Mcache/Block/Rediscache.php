<?php
class Ycc_Mcache_Block_Rediscache extends Mage_Adminhtml_Block_Template
{
    public function getCleanRedisUrl()
    {
        return $this->getUrl('redis/cache/cleanRedis');
    }

    public function getRedisInfo(){
	$redis = new Credis_ClientEx(REDIS_FULLCACHE_HOST, REDIS_FULLCACHE_PORT, REDIS_FULLCACHE_LIFETIME);
	$info = $redis->info();
	$keys = $redis->keys();
	$info['host'] = REDIS_FULLCACHE_HOST;
	$info['port'] = REDIS_FULLCACHE_PORT;
	$info['keys'] = $keys;
	return $info;
    }
}
