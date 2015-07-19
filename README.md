# magento-redis-fullpage

Why this module?

As we know magento already have fullpage cache functionality, and also there is many other fullpage modules base on redis and other memory cache, but why I still develop this module?

1. shortage of magento fullpage cache and other memory cache solution
	Magento fullpage cache for every page cached, for every request, still need to inital magento instance
	For some pages,they can not cache whole page, they only cache some blocks, and combine all these blocks, they also need to inital magento instance
	Initaling magento instance comsume a lot of resouces
	
	Other memory cache system, such as Varnish is very complicated

	

1. all page has only one key in redis,easy to manage every page
		key							value[encrypted]
	www.exmple.com/index.html 		html characters
	
2. support update session infomation to magento

3. easy to edit filters to decide which page will be cached

4. for some blocks, use async call to update special blocks

5. blocks can just be update by some event

6. you can see which page is stored, and you can just flush what page you want


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
	
	for example: Home page account_login block in customer.xml
	
	
	<customer_account_login translate="label">
            <label>Customer Account Login Form</label>
            <!-- Mage_Customer -->
            <remove name="right"/>
            <remove name="left"/>

            <reference name="root">
                <action method="setTemplate"><template>page/1column.phtml</template></action>
            </reference>
            <reference name="content">
                <block type="customer/form_login" name="customer_form_login" template="customer/form/login.phtml" defer="1"/>
            </reference>
        </customer_account_login>

	
