<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Data
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
 * The base object from which all ZF4 data handling objects are inherited
 *
 * <p>Data handling objects expect to have to store data permanently.</p>
 * <p>All classes derived from this class must provide the following data attributes</p>
 * <ul>
 * <li>int 		id 		Unique identifier for the object</li>
 * <li>int 		rowUid	SystUser id of last user to edit the data</li>
 * <li>datetime	rowDt	Date and time of last data edit</li>
 * <li>string	rowSts	Status of data = active, suspended or defunct</li>
 * </ul>
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Data
 */
abstract class ZF4_Object_Data extends ZF4_Object {

	/**
	 * Every object has an id
	 * ZF4_Defines::RID_FLD = 0
	 *
	 * @var int
	 */
	public $id = 0;
	/**
	 * object status
	 * ZF4_Defines::RSTAT_FLD = ZF4_Defines::RSTAT_ACT
	 *
	 * @var string
	 */
	private $_rowSts = 'active';
	/**
	 * object edit datetime
	 * ZF4_Defines::RDT_FLD = null
	 *
	 * @var date
	 */
	private $_rowDt  = null;
	/**
	 * object last edit user id
	 * ZF4_Defines::RUID_FLD = 0
	 *
	 * @var int
	 */
	private $_rowUid  = 0;

    /**
     * Is the data new?
     *
     * True in all cases except when data has been read in from a datastore
     *
     * @var bool
     */
    protected $_isNew = true;
    /**
     * Has the data been changed?
     *
     * False unless data has been set using one of the setter methods
     *
     * @var bool
     */
    protected $_isDirty = false;

    /**
     * key parameter names
     *
     * @var array
     */
    protected $_keys = array('id');

    /**
	 * Constructor
	 *
	 * @param boolean $noLang If True, do not load the translator for messaging service
	 */
    public function __construct($noLang = false) {
    	parent::__construct($noLang);
    	$this->initData();
    }


    /**
     * Setter method, Extends ancestor to set the object as 'dirty'
     *
     * @param string $offset
     * @param mixed $value
     * @return Fluent_Interface
     * @throws ZF4_Object_Exception if parameter is unreachable
     */
    public function offsetSet($index, $value) {
    	try {
    		parent::offsetSet($index, $value);
	        $this->setDirty();
    	} catch (ZF4_Object_Exception $e) {
    		//rethrow
    		throw $e;
    	}
    }

    /**
     * Returns TRUE if the $index is a named value
     * or FALSE if $index was not found.
     * Psuedonym for isset()
     *
     * @param  string $index
     * @return boolean
     * @deprecated use isset()
     * @throws ZF4_Object_Exception notice error
     */
    public function exists($index)
    {
    	trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated, use isset() instead', E_USER_NOTICE);
        return $this->offsetExists($index);
    }

    /**
     * Initialise the public object data to standard
     * public parameters
     *
     * @return Fluent_Interface
     */
    public function initData() {
        $this->exchangeArray(array());
        $this->setDirty(false);
        $this->setNew();
        return $this;
    }

    /**
     * Set public data array
     *
     * @param array $arr An aray of data
     * @return Fluent_Interface
     * @deprecated use exchangeArray() instead
     */
    public function setData(array $arr) {
    	trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated, use exchangeArray() instead', E_USER_NOTICE);
        return $this->exchangeArray($arr);
    }

    /**
     * Exchange the current public parameters with a new set
     *
     * @param array $arr array of parameters
     * @return Fluent_Interface
     */
    public function exchangeArray(array $arr) {
    	//set ID field if not present
    	if (!isset($arr[ZF4_Defines::RID_FLD])) $arr[ZF4_Defines::RID_FLD] = 0;
    	//transfer status fields if present
    	if (isset($arr[ZF4_Defines::RSTAT_FLD]) || !empty($arr[ZF4_Defines::RSTAT_FLD])) {
    		$this->_rowSts = $arr[ZF4_Defines::RSTAT_FLD];
    		unset($arr[ZF4_Defines::RSTAT_FLD]);
    	} else {
    		$this->_rowSts = ZF4_Defines::RSTAT_ACT ;
    	}
    	if (isset($arr[ZF4_Defines::RDT_FLD]) || !empty($arr[ZF4_Defines::RDT_FLD])) {
    		$this->_rowDt = $arr[ZF4_Defines::RDT_FLD];
    		unset($arr[ZF4_Defines::RDT_FLD]);
    	} else {
    		$this->_rowDt = null;
    	}
    	if (isset($arr[ZF4_Defines::RUID_FLD]) || !empty($arr[ZF4_Defines::RUID_FLD])) {
    		$this->_rowUid = $arr[ZF4_Defines::RUID_FLD];
    		unset($arr[ZF4_Defines::RUID_FLD]);
    	} else {
    		$this->_rowUid = 0;
    	}
    	parent::exchangeArray($arr);
    	$this->_checkParams();
    	$this->setDirty();
        return $this;
    }

    /**
     * Return all public data for this object
     *
     * @return array [propName=>propValue ..]
     * @deprecated use toArray()
     */
    public function getData() {
    	trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated, use toArray() instead', E_USER_NOTICE);
    	return $this->toArray;
    }

    /**
     * Return array of public and status parameters
     *
     * @return array
     * @see toArray()
     * @see getStatus()
     */
    public function toArrayAll() {
    	return array_merge($this->toArray(),$this->getStatus());
    }

    /**
     * magic function allows you to echo $class as string
     * or use $b = (string) $class
     *
     * Returns comma delimited string of <keyName>=<value> of public variables
     *
     * @see http://uk.php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     * @return string
     */
    public function __toString() {
    	$str = array();
    	foreach ($this as $key=>$value) {
    		$str[] = "{$key}={$value}";
    	}
    	return implode(",",$str);
    }

    /**
     * Return json encoded public variables of this object
     *
     * @param int $opt json_encode() option (PHP >= 5.3.0)
     * @see http://uk.php.net/manual/en/function.json-encode.php for details of $opt values
     * @return string
     */
    public function toJson($opt = 0) {
    	if (PHP_VERSION >= '5.3.0') {
    		return json_encode($this->toArray(),$opt);
    	} else {
    		return json_encode($this->toArray());
    	}
    }

    /**
     * Setter for dirty flag
     * Dirty = true if the data has changed
     *
     * @param bool $flag
     * @return Fluent_Interface
     */
    public function setDirty($flag = true) {
        $this->_isDirty = $flag;
        return $this;
    }
    /**
     * Getter for dirty flag
     * Dirty = true if the data has changed
     *
     * @return bool
     */
    public function getDirty() {
        return $this->_isDirty;
    }
    /**
     * Setter for New flag
     * New = true unless data has just been read in from store
     *
     * @param bool $flag
     * @return Fluent_Interface
     */
    public function setNew($flag = true) {
        $this->_isNew = $flag;
        return $this;
    }
    /**
     * Getter for New flag
     * New = true unless data has just been read in from store
     *
     * @return bool
     */
    public function getNew() {
        return $this->_isNew;
    }

    /** KEY FUNCTIONALITY **/

    /**
     * Set the key(s) for this object
     *
     * @param null|array $key array of keys [<fld> => <value> .. ]
     * @return Fluent_Interface
     * @throws ZF4_Object_Exception if $key is not an array
     */
    public function setKey($key) {
        if ($key!=null) {
            //check that it is an array
            if (is_array($key)) {
                $this->_keys = array_keys($key);
                foreach ($key as $k => $v) {
                	$this[$k] = $v;
                }
            } else {
				$msg = sprintf(self::$this->_('Parameter to %s must be an array'),"ZF4_Object_DB_Record::setKey()");
                throw new ZF4_Object_Exception($msg,Zend_Log::ERR );
            }
        } else {
            $this->_keys = null;
        }
        return $this;
    }

    /**
     * Get the keys for this object
     *
     * @return array key value(s) (k1=>V1, ..., kn=>Vn)
     */

    public function getKey() {
    	$ret = array();
    	foreach ($this->_keys as $k) {
    		if (isset($this[$k])) {
    			$ret[$k] = $this[$k];
    		} else {
    			$ret[$k] = null;
    		}
    	}
       return $ret;
    }

    /** GENERIC OBJECT STATUS MANIPULATION **/

    /**
     * Activate the object
     *
     * Only objects that are suspended or already active can be activated
     * You need to update() the object to save it to any permanent store
     * Sets a message on failure
     *
     * You may need to extend this function to also handle any dependent objects
     *
     * @return boolean True if state set else false
     * @throws ZF4_Object_Exception if defunct state cannot be determined
     */
    public function activate() {
        if ($this->_rowSts != ZF4_Defines::RSTAT_DEF) {
            $this->_rowSts = ZF4_Defines::RSTAT_ACT;
            $this->setDirty();
            return true;
        } else {
        	$this->setMsg("Cannot activate a defunct object");
        	return false;
        }
    }
    /**
     * Suspend the object
     *
     * Only objects that are active (or suspended) can be suspended
     * You need to update() the object to save it to any permanent store
     * Sets a message on failure
     *
     * You may need to extend this function to also handle any dependent objects
     *
     * @return boolean True if state set else false
     */
    public function suspend() {
        if ($this->_rowSts != ZF4_Defines::RSTAT_DEF) {
            $this->_rowSts = ZF4_Defines::RSTAT_SUS;
            $this->setDirty();
            return true;
        } else {
        	$this->setMsg("Cannot suspend a defunct object");
        	return false;
        }
    }

    /**
     * Defunct the object
     *
     * This does not delete the object - it defuncts it
     * To truly delete the object use trash()
     *
     * As a general rule you should defunct an object.  Data should not be deleted
     * You need to update() the object to save it to any permanent store.
     *
     * You may need to extend this function to also handle any dependent objects
     *
     * @return boolean True Always succeeds
     */
    public function defunct() {
        $this->_rowSts = ZF4_Defines::RSTAT_DEF;
        $this->setDirty();
        return true;
    }

    /**
     * get Object (row) status
     *
     * @return array of status parameters
     * @see toArray()
     * @see toArrayAll()
     */
    public function getStatus() {
    	$arr = array('rowSts'=>$this->_rowSts,
    				 'rowDt'=>$this->_rowDt,
    				 'rowUid'=>$this->_rowUid);
    	return $arr;
    }

    /**
     * CRUD FUNCTIONALITY
     */

    /**
     * Create a new object record.
     * Calls update() to insert data into underlying data store
     *
     * @param array $fldsArr [<fld1>=><value1, ... ]
     * @param boolean $stripNulls strip out any null fields if set usually because db will take care of them
     * @return int
     * @throws ZF4_Object_Exception if $fldsArr is not an array
     */
    public final function create($fldsArr, $stripNulls = true) {
    	if (!is_array($fldsArr)) {
			$msg = sprintf($this->_('Parameter to %s must be an array'),"ZF4_Object_Data::create()");
    		throw new ZF4_Object_Exception($msg,E_USER_ERROR );
    	}
    	$this->exchangeArray($fldsArr);
    	$this->setNew();
    	$ret = $this->update($stripNulls);
    	return $ret;

    }

    /**
     * Make a new entry in a datastore if available
     * Call update() to do a create
     * @param boolean $stripNulls Strip out null value fields before updating
     * @access private
     * @return number of entries created - should == 1
     */
    abstract protected function _make($stripNulls);
    /**
     * Read an entry from a datastore
     * Use exchangeArray() to store data into object
     *
     * @return Fluent_Interface
     */
    abstract public function read();
    /**
     * Update an entry to a data store
     * Update if existing else create if new.
     * Remember to use toArrayAll() to retrieve ALL object parameters to store away
     *
     * @param boolean $stripNulls Strip out null value fields before updating
     * @return number of entries updated - should == 1
     */
    abstract public function update($stripNulls = false);

    /**
     * Pseudonym for defunct()
     *
     * @return Fluent_Interface
     */
    public final function delete() { return $this>defunct(); }

    /**
     * Permanently delete an entry from datastore
     *
     * @return Fluent_Interface
     */
    abstract public function trash();
    /**
     * Read in the object from storage based on values for columns
     *
     * Use when you want to read in object based on non-primary key values
     *
     * @param array|string|int $colVals array(colName => value), string name or int id on.
     * @return int Number of records retrieved - should be == 1
     */
    abstract public function fetch($colVals);


    /**
     * Determines if an object exists
     *
     * Primarily for use by store based objects
     *
     * @param int|array $obj id of object, array of object flds=>values
     * @return boolean True if found else false
     */
    abstract public function is_a($obj);

    /**
     * GENERIC STORAGE FUNCTIONALITY
     */
    /**
     * Execute a statement that will effect this object's data
     * Provided particularly for storage backed object types
     * to provide a catchall method to do something.
     *
     * @param  mixed $statement
     * @param  bool $read Set true if you want to read teh results of the exec statment into the object
     * @return  int Number of records effected - this may legitimately be zero depending on what is executed
     * @throws  ZF4_Object_Exception if in error
     */
    abstract public function exec($statement,$read = false);

}
