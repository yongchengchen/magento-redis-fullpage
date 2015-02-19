# magento-redis-fullpage

1. all page has only one key in redis,easy to manage every page
		key							value[encrypted]
	www.exmple.com/index.html 		html characters
	
2. support update session infomation to magento

3. easy to edit filters to decide which page will be cached

4. for some blocks, use async call to update special blocks

5. blocks can just be update by some event
