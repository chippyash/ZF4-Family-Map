<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	Crypt
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE V3
 * 
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *    License text is located in /docs/LICENSE.FAMILYMAP.txt
 */

/**
 * Encryption Handler
 *
 * Whilst the Zend_Crypt functionality is static, this is not - you need to construct
 *
 * @category	ZF4
 * @package  	Crypt
 */
class ZF4_Crypt extends Zend_Crypt  {

    /**
     * messenger class.  Also holds translator
     *
     * @var ZF4_Messenger
     */
    private $_messenger;

    private $_seed;

    private $_useMac = true;
    
    /**
     * Constructor
	 * @param string $seed Encryption key seed. If null will use an internal one
	 * @param boolean $useMac Is the machine mac address as part of the encryption key
     *
     */
    public function __construct($seed = null, $useMac = true) {
    	$this->_messenger = new ZF4_Messenger();
    	if (is_null($seed)) {
     		$seed = md5('fe1348a5f67d391009a699c8aca9a476');
     	}
    	$this->_seed = $seed;
    }


	/**
	 * Encrypt data using machine specific encryption
	 *
	 * @param string $data Data to be encrypted
	 * @return string|False Encrypted data.  False if unable to encrypt
	 */
	public function mcEncrypt($data) {
		$this->clearMsg();
    	$key = $this->_getCryptKey();
    	if ($key !== false) {
    		$enc = $this->_cryptastic($data,$key);
    	} else {
    		$enc = false;
    	}
		return $enc;
	}

	/**
	 * Unencrypt data using machine specific encryption
	 *
	 * @param string $enc data to be decrypted
	 * @return string The decrypted data
	 */
	public function mcDecrypt($enc) {
		if (empty($enc)) return $enc; //nothing to do
		$this->clearMsg();
    	$key = $this->_getCryptKey();
    	if ($key !== false) {
    		$data = $this->_cryptastic($enc,$key,false);
    	} else {
    		$data = false;
    	}
		return $data;
	}


     /**
     * Get the encryption key
     *
     * Encryption key is made up of the user's system password (in its encrypted form)
     * and the machine MAC address
     *
     * @return string|boolean	The cryptkey else false on error
     */
     private function _getCryptKey() {
     	if ($this->_useMac) {
	   		$mac = $this->_getMac();
			if ($mac === false) {
				//cannot find mac address
				return false;
			}
			$cryptkey = $mac . $this->_seed;
   		} else {
   			$cryptkey = $this->_seed;
   		}
    	return $cryptkey;
    }

	/**
	 * Get machine MAC address - linux only
	 *
	 * @todo create variant for windows
	 * @return string mac address else 'NoMac' if not found
	 */
	private function _getMac() {
		$cmd = "ifconfig";  //linux only
		$output = array();
		$ret = exec($cmd,$output);
		$found = false;
		foreach ($output as $line) {
			$p = strpos($line,'HWaddr');
			if ($p !== false) {
				$found = $line;
				break;
			}
		}
		if ($found !== false) {
			$mac = substr($found,$p+7);
		} else {
			$this->setMsg('Cannot deduce MAC address');
			$mac = 'NOMAC';
		}
		return $mac;
	}

    /**
     * encryption/decryption routine
     *
     * Data is serialized if encrypting and unserialized if decrypting
     *
     * @param mixed $data Data to encrypt/decrypt
     * @param string $key Encryption key
     * @param boolean $encrypt True to encrypt, false to decrypt
     * @return blobtext|False Encrypted/decrypted content. False if unable to operate
     * @uses MCRYPT php extension
     * @author Andrew Johnson http://www.itnewb.com/user/Andrew
     */
	private function _cryptastic( $data, $key, $encrypt=true ) {
		// Serialize, if encrypting
		if ( $encrypt ) {
			$data = serialize($data);
		}
		// Open cipher module
		if ( ! $td = mcrypt_module_open('rijndael-256', '', 'cfb', '') ) {
			return false;
		}
		$ks = mcrypt_enc_get_key_size($td);     // Required key size
		$key = substr(sha1($key), 0, $ks);      // Harden / adjust length
		$ivs = mcrypt_enc_get_iv_size($td);     // IV size
		$iv = $encrypt ?
		  mcrypt_create_iv($ivs, MCRYPT_RAND) :   // Create IV, if encrypting
		  substr($data, 0, $ivs);                 // Extract IV, if decrypting
		// Extract data, if decrypting
		if ( ! $encrypt ) $data = substr($data, $ivs);
		// Initialize buffers
		if ( mcrypt_generic_init($td, $key, $iv) !== 0 ) {
			return false;
		}

		$data = $encrypt ?
		  mcrypt_generic($td, $data) :    // Perform encryption
		  mdecrypt_generic($td, $data);   // Perform decryption
		if ( $encrypt ) {
			$data = $iv . $data;    // Prepend IV, if encrypting
		}
		mcrypt_generic_deinit($td);             // Clear buffers
		mcrypt_module_close($td);               // Close cipher module
		// Unserialize, if decrypting
		if ( ! $encrypt ) {
			try { 
				$data= @unserialize($data);
				$err = error_get_last();
				if (isset($err['message']) && strstr($err['message'],'unserialize()') !== false) {
					throw new ZF4_Crypt_Exception('Invalid encryption key used');
				}
			} catch (Exception $e) {
				//cannot unserialize - usually because the wrong key has been used
				if (strpos($e->getMessage(),'Error at offset') !== false) {
					throw new ZF4_Crypt_Exception('Invalid encryption key used');
				} else {
					throw $e;
				}
			}
		}
		return $data;
	}



    /**
     * Function overloading to support messaging
     * public functions
     *
     * @param string $method Method to call
     * @param array $params Parameters to pass to method
     * @throws ZF4_Crypt_Exception if method doesn't exist on messenger object
     */
    public function __call($method, $params) {
    	if (method_exists($this->_messenger,$method)) {
    		$ret = call_user_func_array(array($this->_messenger,$method),$params);
    		return $ret;
    	} else {
    		throw new ZF4_Crypt_Exception('Unknown method in ZF4_Crypt');
    	}
    }

}
