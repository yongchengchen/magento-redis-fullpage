<?php
class Credis_SessionHandler
{
    public $ttl = 3600; // 60 minutes default
    protected $db;
    protected $prefix;

 
    public function __construct($db, $prefix = 'sess_') {
        $this->db = $db;
        $this->prefix = $prefix;
	session_set_save_handler(
	    array($this, 'open'),
	    array($this, 'close'),
	    array($this, 'read'),
	    array($this, 'write'),
	    array($this, 'destroy'),
	    array($this, 'gc')
	);
    }

    public function open($savePath, $sessionName) {
        // No action necessary because connection is injected
        // in constructor and arguments are not applicable.
    }
 
    public function close() {
        $this->db = null;
        unset($this->db);
    }

    public static function _decodeData($data)
    {
        if (substr($data,0,4) == ":gz:") {
            return gzuncompress(substr($data,4));
        }
        return $data;
    }

    public function _encodeData($data)
    {
        return $data;
        if (strlen($data)>2048){
            $data = gzcompress($data, 1);
            if($data) {
                $data = ':gz:'. $data;
            }
        }
        return $data;
    }

    public function read($id) {
        $id = $this->prefix . $id;
        $sessData = $this->db->hGet($id, "data");
	if ($sessData){
		$sessData = self::_decodeData($sessData);
	}
        $this->db->expire($id, $this->ttl);
        return $sessData;
    }
 
    public function write($id, $data) {
        $id = $this->prefix . $id;
        $this->db->hSet($id, 'data', $this->_encodeData($data));
        $this->db->expire($id, $this->ttl);
    }
 
    public function destroy($id) {
        $this->db->del($this->prefix . $id);
    }
 
    public function gc($maxLifetime) {
        // no action necessary because using EXPIRE
    }
}
