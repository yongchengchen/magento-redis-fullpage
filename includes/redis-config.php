<?php
define('REDIS_FULLCACHE_ON', true);
define('REDIS_FULLCACHE_HOST', '127.0.0.1');
define('REDIS_FULLCACHE_PORT', '6379');
define('REDIS_SESSION_PORT', '6379');
define('REDIS_FULLCACHE_LIFETIME', '57600');//16 hours
define('REDIS_SESSION_CHECK_SENSER_WORD', 'customer_id'); //if have paramater name is REDIS_SESSION_CHECK_SENSER_WORD, then, will do sensership check
