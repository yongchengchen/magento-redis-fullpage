<?php
class Ycc_Mcache_Block_Item extends Mage_Core_Block_Template{
	protected function _toHtml() {
		$ret .= "<div id='a" .$this->_rid ."'>";
		$ret .= "<script type='text/javascript'>";
		$ret .= "g_lazy.massload('');";
		$ret .= "</script></div>";
		return $ret;
	}
	
	private $_reqid;
	private $_b;
	private $_t;
	public function bindBlock($b, $t){
		$this->_b = $b;
		$this->_t = $t;
		$this->_rid = md5($b . $t);
		return $this;
	}
}
?>
