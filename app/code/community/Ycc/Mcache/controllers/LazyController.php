<?php
class Ycc_Mcache_LazyController extends Mage_Core_Controller_Front_Action
{
        public function indexAction() {
		if ($this->getRequest()->isPost()){ return;}
		#if (!strpos($_SERVER['HTTP_REFERER'], "mytraining")){
			#return;
		#}
		$encrypter = new Credis_Encrypt();
		$block = $this->getRequest()->getParam('b');
		$temp = $encrypter->decrypt_url($this->getRequest()->getParam('t'));
		if (!$block){
			$block = 'core/template';
		}else{
			$block = $encrypter->decrypt_url($block);
		}

		$this->getLayout()->setBlock('head', new Varien_Object());
		$subblock = $this->getLayout()
			->createBlock($block)
			->setData('controller', $this)
			->setTemplate("$temp.phtml");
		$this->getResponse()->setBody($subblock->toHtml());
	}

	public function blocksAction(){
		$blks = $this->getRequest()->getParam("b");
		if(!$blks){return;}
		$deferBlocks = explode(",",base64_decode($blks));
		
		$handle = $this->getRequest()->getParam("h");
		if ($handle){
			$handle =  base64_decode($handle);
			$this->getLayout()->OnlyDefer();
			$this->getLayout()->getUpdate()->addHandle("default");
			$this->getLayout()->getUpdate()->addHandle($handle);
			$this->getLayout()->getUpdate()->load();
			$this->generateLayoutXml();
			$this->getLayout()->generateBlocks();
			
			//$deferBlocks = $this->getLayout()->getDeferBlocks();
			if (count($deferBlocks)>0){
				$ret = "";
				foreach($deferBlocks as $b){
					$md5 = md5($b);
					$ret .= "<div id='after$md5'>";
					$ret .= $this->getLayout()->getBlock($b)->toHtml();
					$ret .= "</div>";
				}
				$ret .= "<script>";
				foreach($deferBlocks as $b){
					$md5 = md5($b);
					$ret .= "jQuery('#defer$md5').html('');";
					$ret .= "jQuery('#after$md5').appendTo('#defer$md5');";
				}
				$ret .= "</script>";

/*
				$ret .= "<script>";
				foreach($deferBlocks as $b){
					$md5 = md5($b);
					$ret .= "jQuery('#defer$md5').replaceWith(\"";
					$ret .= htmlentities($this->getLayout()->getBlock($b)->toHtml());
					$ret .= "\");";
				}
				$ret .= "</script>";
*/
				echo $ret;
			}
		}
	}

	public function cleanRedisAction()
	{
		try {
			include MAGENTO_ROOT . '/includes/redis-config.php';
			$redis = new Credis_ClientEx(REDIS_FULLCACHE_HOST,REDIS_FULLCACHE_PORT,REDIS_FULLCACHE_LIFETIME);
			$redis->flushAll();
			$this->_getSession()->addSuccess(
				Mage::helper('adminhtml')->__('Redis full page cache was cleaned.')
			);
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$this->_getSession()->addException($e,
				Mage::helper('adminhtml')->__('An error occurred while clearing redis full page cache.')
			);
		}
		$this->_redirect("adminhtml/cache/index/");
	}

}
