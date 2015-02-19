<?php
class Ycc_Mcache_Model_Layout extends Mage_Core_Model_Layout
{
	protected $_deferblocks = array();
	public function addDeferBlock($name){
		$this->_deferblocks[$name] = 1;
	}
	protected $_onlydefer=false;
	public function OnlyDefer(){
		$this->_onlydefer = true;
	}
	
	protected $_emptyBlock;
	protected function getEmptyBlock($className){
		if (!$this->_emptyBlock){
			/*
			$block = $this->_getBlockInstance("core/template", array());
			$block = Mage::getModel("ycc_mcache/dynamicclass")->_inheritCore($block);
			$block->addJs = function(){};
			$block->addCss = function(){};
			$block->addItem = function(){};
			$block->addItemRender = function(){};
			$block->addToParentGroup = function(){};
			*/
			$block = Mage::getModel("ycc_mcache/dynamicclass")->_inheritCore(null);
			$block->insert = function(){};
			$block->append = function(){};
			$this->_emptyBlock = $block;
		}
		return $this->_emptyBlock;
	}
	public function wrapBlock($srcblock){
		$wrapper = Mage::getModel("ycc_mcache/dynamicclass")->_inheritCore($srcblock);
		$wrapper->toHtml = function($pcore){
			if ($pcore){
				$bname = $pcore->getNameInLayout();
				Mage::getSingleton('core/layout')->addDeferBlock($bname);
				$ret = "<div id='defer".md5($bname)."'>";
				$ret .= $pcore->toHtml();
				$ret .= "</div>";
				return $ret;
			}
		};
		return $wrapper;
	}
	protected function _generateBlock($node, $parent){
		$isDefer = !empty($node['defer']) || (!empty($node['inheritdefer']));
		$bname = (string)$node['name'];
		if ($this->_onlydefer && !$isDefer){
			if (!empty($node['class'])) {
			    $className = (string)$node['class'];
			} else {
			    $className = (string)$node['type'];
			}
			$className = Mage::getConfig()->getBlockClassName($className);
			$this->_blocks[$bname] = $this->getEmptyBlock($className);
			return;
		}
		if ($isDefer && !$this->_onlydefer){
			if (!empty($node['parent'])) {
				$parentName = (string)$node['parent'];
			} else {
				$parentName = $parent->getBlockName();
			}
			if (!empty($parentName)) {
				$parentBlock = $this->getBlock($parentName);
			}
			if (get_class($parentBlock) !="Ycc_Mcache_Model_Dynamicclass"){
				$parentcover = Mage::getModel("ycc_mcache/dynamicclass")->_inheritCore($parentBlock);
			} else {
				$parentcover = $parentBlock;
			}
			$parentcover->insert = function($pcore,$block, $siblingName = '', $after = false, $alias=''){
				$pcore->insert(Mage::getSingleton('core/layout')->wrapBlock($block),$siblingName, $after, $alias);
			};
			$parentcover->append = function($pcore, $block, $alias = '') {
				$pcore->append(Mage::getSingleton('core/layout')->wrapBlock($block),$alias);
			};
			$this->_blocks[$parentName] = $parentcover;
		}
		parent::_generateBlock($node, $parent);
		if ($isDefer && !$this->_onlydefer){
			$block = $this->getBlock($bname);
			$this->_blocks[$bname] = $this->wrapBlock($block);
		}
		if ($isDefer) {
			if ($this->_onlydefer){
				foreach ($node as $sub) {
					$sub['inheritdefer'] = 1;
				}
			}
			//if (!empty($node['defer'])){
				//$this->_deferblocks[$bname] = 1;
			//}
		}
	}

	protected function _generateAction($node, $parent){
		if ($this->_onlydefer){
			$isDefer = !empty($node['defer']) || (!empty($node['inheritdefer']));
			if ($isDefer){
				return parent::_generateAction($node, $parent);
			}
			return $this;
		}
		return parent::_generateAction($node, $parent);
	}

	public function getOutput(){
		$out = parent::getOutput();
		if (!$this->_onlydefer && count($this->_deferblocks)>0) {
			$handle = base64_encode($this->getFullActionName());
			$blocks = base64_encode(implode(",",array_keys($this->_deferblocks)));
			$out .= "<script>
                                g_lazy.load('/mcache/lazy/blocks?h=$handle&b=$blocks');
                                </script>";
		}
		return $out;
	}

	protected function getFullActionName($delimiter='_') {
		$str = Mage::app()->getRequest()->getRequestedRouteName().$delimiter.
			Mage::app()->getRequest()->getRequestedControllerName().$delimiter.
			Mage::app()->getRequest()->getRequestedActionName();
		return strtolower($str);
	}
}
