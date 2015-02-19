<?php
//require "/home/humangroup/git/mytraining/lib/Credis/Encrypt.php";
class Ycc_Mcache_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $_encrypt;
	function __construct(){
		$this->_encrypt = new Credis_Encrypt();
	}
	public function getLazyUrl($t, $b=false){
		$url = "/mcache/lazy?";
		if ($b){
			$url .=  "b=";
			$url .= $this->_encrypt->encrypt_url($b);
			$url .=  "&";
		}
		$url .=  "t=";
		$url .= $this->_encrypt->encrypt_url($t);
		return $url;
	}
}
