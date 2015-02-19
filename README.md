# magento-redis-fullpage

1. all page has only one key in redis,easy to manage every page
		key							value[encrypted]
	www.exmple.com/index.html 		html characters
	
2. support update session infomation to magento

3. easy to edit filters to decide which page will be cached

4. for some blocks, use async call to update special blocks

5. blocks can just be update by some event

6. Before using it
	1) You should already install redis server, if you don't know what's redis, please don't install it
	2) create your redis db instance
	3) config redis for fullpage cache

7. How to use
	1) you should edit index.php file
	open index.php, and insert this line before Mage::run($mageRunCode, $mageRunType);
		require(MAGENTO_ROOT . '/lib/Credis/cache.php');

	2) go to magento backend, and go to "configuration"->"ADVANCED"->"Developer"->"Redis Full Page Cache"
		There are some tags:
		I) Cache Static Page(.html)
		II) Cache White List
	3) setting some blocks as aysnc block
	
