<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Database
 * @subpackage  Table
 * @author 		Ashley Kitson
 *
 */

/**
 * Adds field encryption to the table adapter
 *
 * NB - DO NOT ENCRYPT THE TABLE KEYS or any other index field
 * Field types to carry encrypted data must be of type BLOB
 *
 * @category	ZF4
 * @package 	Database
 * @subpackage  Table
 */
class ZF4_Db_Table_Encrypt extends ZF4_Db_Table_Model {

	/**
	 * Encrypted fields
	 *
	 * You can set these in your ancestor or pass them during construction
	 *
	 * @var array
	 */
	protected $_encFlds = array();

	/**
	 * Encryption seed
	 *
	 * You can set this in your ancestor or pass it during construction
	 *
	 * @var string
	 */
	protected $_encSalt = 'RP/C4yQWBi5FHBLjZ4/HHKs6EQ2UFX0NOURyY/ofI8aIlKoBMvUxja7MhmVZVLoDrK1AbjoZR7x5XyqUXjzcsg==';

	/**
	 * The encryption device
	 *
	 * @var ZF4_Crypt
	 */
	private $_crypt;

    /**
     * Constructor
     *
	 * If you have overidden $this->_encFlds and $this->_encSalt in your ancestor
	 * you do not need to give the parameters
	 *
     * @param string the name of a table
     * @param string $uniqueCol  Unique name field for record
     * @param int|string $id id or unique name for record to set to object public parameters
     * @param boolean $useMac Use machine mac address as part of encryption key - dafault = no
     * @param array field names to encrypt contents for - if you have set $this->_encFlds it will be used
     * @package string $encSalt A salt string to use for encryption - if you have set $this->_encSalt it will be used
     * @param string encryption salt
     */
    public function __construct($table, $uniqueCol = null, $id = null, $useMac = true, $encFlds = null, $encSalt = null) {
    	$this->_setEncrypter($useMac, $encFlds, $encSalt);
        parent::__construct($table, $uniqueCol, $id);
	}

	/***********************************************************
	 * CRUD
	 ***********************************************************/

	/**
	 * Insert data into table
	 *
	 * Overides ancestor by encrypting any required field values
	 *
	 * @param array $data
	 * @return int
	 */
	public function insert(array $data) {
		return parent::insert($this->encrypt($data));
	}

	/**
	 * Update data into table
	 *
	 * Overides ancestor by encrypting any required field values
	 *
	 * @param array $data
	 * @return int
	 */
	public function update(array  $data, $where) {
		return parent::update($this->encrypt($data), $where);
	}

	/**
	 * Fetch all data - decrypts as necessary
	 *
	 * Overide ancestor
	 *
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
		$rowSet = parent::fetchAll($where,$order,$count,$offset);
		if (count($rowSet) > 0) {
			$rowSet = $this->decrypt($rowSet);
		}
		return $rowSet;
	}

	/**
	 * Fetch a single row - decrypt as necessary
	 *
	 * Overide ancestor
	 *
	 * @return Zend_Db_Table_Row
	 */
	public function fetchRow($where = null, $order = null) {
		$row = parent::fetchRow($where,$order);
		if (null !== $row) {
			$row = $this->decrypt($row);
		}
		return $row;
	}

	/***********************************************************
	 * ENCRYPTION
	 ***********************************************************/

	/**
	 * Set up the encrypter
	 *
	 * If you have overidden $this->_encFlds and $this->_encSalt in your ancestor
	 * you do not need to give the parameters
	 *
	 * @param boolean $useMc  Use machine mac address as part of the encryption
     * @param array field names to encrypt contents for
     * @param string encryption salt
	 */
	private function _setEncrypter($useMac = true, $encFlds = null, $encSalt = null) {
		$this->_encFlds = (is_array($encFlds) && (count($encFlds)>0) ? $encFlds : $this->_encFlds);
		$this->_encSalt = (is_string($encSalt) && !empty($encSalt) ? $encSalt : $this->_encSalt);
		$this->_crypt = new ZF4_Crypt($this->_encSalt, $useMac);
	}

	/**
	 * Encrypt a data set
	 *
	 * @param array $data
	 */
	public function encrypt($data) {
		//check to see if we have any encrypted fields
		if (count($this->_encFlds) > 0) {
			//encrypt each field as necessary
			foreach ($this->_encFlds as $fld) {
				if (array_key_exists($fld,$data)) {
					$data[$fld] = $this->_crypt->mcEncrypt($data[$fld],$this->_encSalt);
				}
			}
		}
		return $data;
	}

	/**
	 * Decrypt a data set
	 *
	 * @param array $data
	 */
	public function decrypt($data) {
		//check to see if we have any encrypted fields
		if (count($this->_encFlds) > 0) {
			if ($data instanceof Zend_Db_Table_Rowset) {
				$data = $this->_decryptRowset($data);
			} elseif (is_array($data) && is_array($data[0])) {
				$data = $this->_decryptArraySet($data);
			} elseif (is_array($data)) {
				$data = $this->_decryptSingleRow($data);
			}
		}
		return $data;
	}
	
	/**
	 * Decrypt a rowset
	 *
	 * @param Zend_Db_Table_Rowset $rowset
	 * @return Zend_Db_Table_Rowset
	 */
	private function _decryptRowset(Zend_Db_Table_Rowset $rowset) {
		foreach ($rowset as $row) {
			foreach ($this->_encFlds as $fld) {
				if (isset($row->$fld)) {
					$row->$fld = $this->_crypt->mcDecrypt($row->$fld,$this->_encSalt);
				}
			}
		}
		return $rowset;
	}
	
	/**
	 * Decrypt a single row of data
	 *
	 * @param array $data
	 * @return array
	 */
	private function _decryptSingleRow(array $data) {
		//encrypt each field as necessary
		foreach ($this->_encFlds as $fld) {
			if (array_key_exists($fld,$data)) {
				$data[$fld] = $this->_crypt->mcDecrypt($data[$fld],$this->_encSalt);
			}
		}
		return $data;		
	}
	
	/**
	 * Decrypt a rowset array of data
	 *
	 * @param array $rowset
	 * @return array
	 */
	private function _decryptArraySet(array $rowset) {
		$ret = array();
		foreach ($rowset as $row) {
			$ret[] = $this->_decryptSingleRow($row);
		}
		return $ret;
	}
}