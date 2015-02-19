<?php
class Credis_Encrypt{
	private function keyED($txt,$encrypt_key) {
                $encrypt_key = md5($encrypt_key);
                $ctr = 0;
                $tmp = "";
                for($i=0;$i<strlen($txt);$i++)
                {
                        if ($ctr==strlen($encrypt_key)){ $ctr=0;}
                        $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
                        $ctr++;
                }
                return $tmp;
        }

	private function encrypt($txt,$key) {
                $encrypt_key = md5(md5($key));
                $ctr=0;
                $tmp = "";
                for ($i=0;$i<strlen($txt);$i++)
                {
                        if ($ctr==strlen($encrypt_key)){ $ctr=0;}
                        $tmp.=substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
                        $ctr++;
                }
                return $this->keyED($tmp,$key);
        }

	private function decrypt($txt,$key) {
		$txt = $this->keyED($txt,$key);
		$tmp = "";
		for($i=0;$i<strlen($txt);$i++)
		{
			$md5 = substr($txt,$i,1);
			$i++;
			$tmp.= (substr($txt,$i,1) ^ $md5);
		}
		return $tmp;
	}

	const URL_ENCRYPT_KEY = "MyTrAINiNG";
	public function encrypt_url($url,$key=self::URL_ENCRYPT_KEY) {
		return rawurlencode(base64_encode($this->encrypt($url,$key)));
	}
	public function decrypt_url($url,$key=self::URL_ENCRYPT_KEY) {
		return $this->decrypt(base64_decode(rawurldecode($url)),$key);
	}
}
