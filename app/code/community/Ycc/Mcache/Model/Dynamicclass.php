<?php
class Ycc_Mcache_Model_Dynamicclass{
	private $_core;
	public function _inheritCore($b){
		$this->_core = $b;
		return $this;
	}

	public function __call($method, $args)
	{
		if (isset($this->$method)) {
		    array_splice($args, 0, 0, array(&$this->_core));
		    return call_user_func_array($this->$method, $args);
		}
		/*if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $args);
		}*/
		if (method_exists($this->_core, $method)) {
			return call_user_func_array(array($this->_core, $method), $args);
		}
	}
}
?>
