<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
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
 * Defines a ZF4 object that relies on a database table for its data
 * This object handles one record only.
 * See ZF4_Object_Db_Table for handling a complete table as an object
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 */
class ZF4_Object_Db_Record extends ZF4_Object_Data {

    /**
     * The database handler
     *
     * @var Zend_Db_Adapter...
     */
    protected $_db;
    /**
     * The table that this record operates on
     *
     * @var string
     */
    protected $_table;

    /**
     * Auto incrementing key column for table if any
     *
     * @var string
     */
    protected $_autoCol;
    /**
     * Name of UNIQUE column that will be used if a string
     * is passed into the constructor to instantiate the object
     * @see setNameCol()
     * @var string
     */
    protected $_nameCol;
    /**
     * Table description
     * see http://framework.zend.com/manual/en/zend.db.html#zend.db.adapter.list-describe
     *
     * @var array
     */
    protected $_tableDesc;
    /**
     * Raw referential integrity parameters
     *
     * @var array
     */
    protected $_refParams;
    /**
     * Parent referential objects
     *
     * @var array
     */
    private $_parents;
    /**
     * Child referential objects
     *
     * @var array
     */
    private $_children;
    /**
     * Flag that determines if we use begin/commit
     * transactions around our SQL.
     *
     * @var boolean
     */
    private $_transact = true;
    /**
     * Table object for this object
     *
     * @var ZF4_Object_Db_Table
     */
    private $_tObject;
    /**
     * Grid object for this object
     *
     * @var ZF4_Datagrid_Grid
     */
    private $_gObject;
    /**
     * Constructor
     *
     * @param string $table Name of database table that this object operates on
     * @param array|string|int $search array of search values [colName=>value], nameCol value or id value
     * @param array $refParams array of parent and child object descriptors for this object
     * @param string $nameCol The name of the column to use when is_string($key).  Can be null in which case will search for a UNIQUE key field and use the first one
	 * @param boolean $noLang If True, do not load the translator for messaging service
     * @throws ZF4_Object_Exception if cannot set up primary/unique keys for object or the search is invalid
     */
    public function __construct($table, $search = null, $refParams = null, $nameCol = null, $noLang = false) {
    	//construct the new object
        parent::__construct($noLang);
       	$this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_table = $table;

        //fetch table data via table object to use the caches
        $this->_tableDesc = $this->getTableObject()->info(Zend_Db_Table_Abstract::METADATA);

        $this->setNameCol($nameCol);	  //set name column
        $this->_setMetaData($search);	  //set meta data and load object
        $this->setRefParams($refParams);  //set up referential integrity
    }


    /**
     * Set the name column
     *
     * <p>This should be a UNIQUE key on the table else
     * results can be unpredictable</p>
     * <p>If $nameCol is null will set up the first varchar type Unique column it can find
     *
     * @param string $nameCol
     */
    public function setNameCol($nameCol = null) {
    	if (!is_null($nameCol)) {
    		$this->_nameCol = $nameCol;
    	} else {
    		//lets go find one
    		$tObj = $this->getTableObject();
    		$uCols = $tObj->getUniqueCols();
    		$info = $tObj->info();
    		if (!is_null($uCols)) {
    			foreach ($uCols as $col) {
    				if ($this->_tableDesc[$col]['DATA_TYPE'] == 'varchar') {
    					$this->_nameCol = $col;
    					break;
    				}
    			}
    		}
    	}
    }

    /**
     * Return the name column for the object
     *
     * @return null|string
     */
    public function getNameCol() {
    	return $this->_nameCol;
    }

    /**
     * Set up meta data
     *
     * @param array,string,int $search
     */
    private function _setMetaData($search = null) {
        //1/ find the primary key column(s)
        //2/ find autoindex columns if any - can only be one
        //3/ set up empty data array in event that we are constructed without search values
        $prime = array();
        $data = array();
        $unique = array();
        $uCols = $this->getTableObject()->getUniqueCols();

        foreach ($this->_tableDesc as $fldName=>$col) {
        	if ($col['PRIMARY']) {
        	    $prime[$fldName] = null;
        	}
        	if (!is_null($uCols) && count($unique) == 0 && in_array($fldName,$uCols)) {
        		$unique[$fldName] = null;
        	}
        	if ($col['IDENTITY']) {
        	    $this->_autoCol =  $fldName;
        	}
            //set up the data array
            $data[$fldName] = null;
        }
        //set primary key column(s)
        if (count($prime) > 0) {
        	//first choice
        	$this->setKey($prime);
        } elseif (!is_null($this->_autoCol)) {
        	//second choice
        	$this->setKey(array($this->_autoCol));
        } elseif (count($unique) == 1) {
        	//third choice
        	$this->setKey($unique);
        } else {
        	$msg = sprintf($this->_("Cannot find primary or unique index for table %s in class %s"),$this->_table,get_class($this));
        	throw new ZF4_Object_Exception($msg, Zend_Log::CRIT );
        }
        if (isset($search)) {
        	//modify the search as necessary
        	if (is_int($search) && ($search == 0)) {
        		$search = null; //no such record as record zero
        	} elseif (is_int($search)) {
        		$t = array_keys($this->getKey());
        		$search = array($t[0]=>$search);
        	} elseif (is_string($search) && !is_null($this->_nameCol)) {
        		$search = array($this->_nameCol=>$search);
        	} elseif (is_string($search) && is_null($this->_nameCol)) {
        		//throw an error
        		$msg = sprintf($this->_("No name column in class %s."),get_class($this));
        		throw new ZF4_Object_Exception( $msg, E_USER_ERROR );
        	} elseif (!is_array($search)) {
        		//throw an error
        		$msg = sprintf($this->_("Invalid object search parameters in class %s. Type= %s, Value= %s"),get_class($this),gettype($search), (string) $search);
        		throw new ZF4_Object_Exception( $msg, Zend_Log::CRIT );
        	}
        }
        if (isset($search)) {
        	//get the object data from store
       		$this->setKey($search);
            $this->read();
        } else {
        	//setup an empty object
            $this->exchangeArray($data);
            $this->setDirty(false);  //need to clear flag as setData will have set it
        }
    }


    /** CRUD FUNCTIONALITY **/

    /**
     * Clean the data prior to inserting into database
     *
     * 1/ remove any fields in the data that are not in the table
     * 2/ convert date fields into DB compatible format
     *
     * Extend this method to add an specialised data cleaning, field mangling etc
     *
     * @param array $data (colname=>value)
     * @return array the cleaned up data
     */
    protected function _cleanData($data) {
        $_cleanData = array();
        foreach ($data as $key => $value) {
        	$t = $this->getTableName();
        	if (array_key_exists($key,$this->_tableDesc)) {
        	    $meta = $this->_tableDesc[$key]['DATA_TYPE'];
        	    switch (substr($meta,0,4)) {
        	    case 'date': //date and datetime
        	        $d = new Zend_Date($value);
        	        $value = $d->get(Zend_Date::ISO_8601 );
       	    		break;
        	    case 'time':
        	        $d = new Zend_Date($value,Zend_Date::TIMES);
        	        $value = $d->get(Zend_Date::HOUR ) . ":" . $d->get(Zend_Date::MINUTE  );
        	        break;
        	    case 'set(':
        	    	$value = (is_array($value) ? implode(',',$value) : $value);
       	    		break;

        	    default:
        	    		break;
        	    }
        		$_cleanData[$key] = $value;
        	}
        }
        return $_cleanData;
    }

    /**
     * Strip data array of null values
     *
     * @param array $data product of this->getData()
     * @return array Data
     */
    private function _stripNulls($data) {
    	foreach ($data as $key => $value) {
    		if ($value == null) {
    			unset($data[$key]);
    		}
    	}
    	return $data;
    }
    /**
     * Create a record based on the values that the object is currently holding
     *
     * @param boolean $_stripNulls Strip out null value fields before updating
     * @return int Number of records created (0 or 1)
     */
    protected function _make($_stripNulls) {
        if ($this->getDirty()) {
            $data = $this->toArrayAll();
            //$this->objDebug($data,"data");
            if (isset($this->_autoCol)) {
                //remove the autoindex column from data array
                unset($data[$this->_autoCol]);
            }
            //remove the row datetime as this will be set by the server
            unset($data[ZF4_Defines::RDT_FLD]);
            //remove the row status as this will be set by the server
            unset($data[ZF4_Defines::RSTAT_FLD]);
            //strip any null values if required
            if ($_stripNulls) {
            	$data = $this->_stripNulls($data);
            }
            //clean up fields that don't belong to the record
            $data = $this->_cleanData($data);
            //set the row editor id
            $data[ZF4_Defines::RUID_FLD] = ZF4_Service_Manager::getService(ZF4_SRVC_AUTHENTICATION)->getUid();
            //$this->objDebug($data,"data");
            //insert
            if ($this->_transact) $this->beginTransaction();
            try {
                $ret = $this->_db->insert($this->_table,$data);
                //$this->objDebug($ret,"ret");
            }
            catch (Exception $e) {
                $msg = get_class($e) . ': ' . $e->getMessage();
                $this->setMsg($msg);
                ZF4_Service_Manager::getService(ZF4_SRVC_LOG)->logApp($msg);
                $ret = 0;
            }
            //if one record created
            if ($ret==1) {
                //if we have auto inc column then update it
                if (isset($this->_autoCol)) {
                    $lastId = $this->_db->lastInsertId();
                    $this[$this->_autoCol] = $lastId;
                }
                if ($this->_transact) $this->commit();
            } else {
            	$this->setMsg('No record created');
                if ($this->_transact) $this->rollBack();
            }

            return $ret;
        } else {
            return 0;
        }
    }

    /**
     * Update the object's record
     *
     * @param boolean $_stripNulls If true then strip out any fields that have null values
     * @return int Number of rows effected (0 or 1)
     */
    protected function _doUpdate($_stripNulls) {
        if (!$this->getDirty()) {
            return 0; //no data amended
        } else {
            //set where clause
            $where = array();
            foreach ($this->getKey() as $col=>$value) {
        	   $where [] = $this->_db->quoteIdentifier($col)
        	               ." = "
        	               .$this->_db->quote($value,$this->_tableDesc[$col]['DATA_TYPE']);
            }
            //set up data to be updated
            $data = $this->toArrayAll();
            //remove auto index columns
            if (isset($this->_autoCol)) {
                //remove the autoindex column from data array
                unset($data[$this->_autoCol]);
            }
            //remove primary key columns
            foreach ($this->getKey() as $key=>$value) {
            	if (isset($data[$key])) {
            	    unset($data[$key]);
            	}
            }
            //remove the row datetime as this will be set by the server
            unset($data[ZF4_Defines::RDT_FLD]);
            //set the row_id
            $data[ZF4_Defines::RUID_FLD] = ZF4_Service_Manager::getService(ZF4_SRVC_AUTHENTICATION)->getUid();
            //
            //clean the data
            $data = $this->_cleanData($data);
			if ($_stripNulls) {
				$data = $this->_stripNulls($data);
			}

            //update
            if ($this->_transact) $this->beginTransaction();
            try {
                $ret = $this->_db->update($this->_table,$data,$where);
            }
            catch (Zend_Db_Statement_Exception $e) {
                $msg = $e->getMessage();
                $this->setMsg($msg);
                ZF4_Service_Manager::getService(ZF4_SRVC_LOG)->logApp($e);
                $ret = 0;
            }
            if ($ret==1) {
                if ($this->_transact) $this->commit();
            } else {
            	//get PDO error information
            	$code = $this->_db->getConnection()->errorCode();
            	if ($code == '00000') {
            		//no error occured - the record did not change
            		$ret = 1;
            		if ($this->_transact) $this->commit();
            	} else {
	            	$msg = $this->_db->getConnection()->errorInfo();
	            	if (is_array($msg)) $msg = implode(', ',$msg);
	            	$msg = $this->_('DB returned ') . $code . ' : ' . $msg;
					$this->setMsg($msg);
	                if ($this->_transact) $this->rollBack();
            	}
            }
            return $ret; //number of rows effected
        }
    }


    /**
     * Update the object to database
     *
     * Will set row_uid to current logged on user if possible
     * Adds record object to the Db manager if update is succesful
     *
     * @param boolean $_stripNulls Strip out null fields before update
     * @return int Number of records effected (0 or 1)
     */
    public function update($_stripNulls = false) {
		//see if we are restricted by chain processing onUpdate:RESTRICT
		if (!$this->_checkRefUpdate()) {
			$this->setMsg("Ref Integrity: Unable to Update");
			return 0;
		}

        if ($this->getNew()) {
            $upRet = $this->_make($_stripNulls);
        } else {
            $upRet =  $this->_doUpdate($_stripNulls);
        }
        if ($upRet==1) {
            //need to read back a record after an update as the database may
            // have changed values (defaults values etc)
            $rdRet = $this->read();
            //add object to registry if not base class
            if (!$this instanceof ZF4_Object_Db_Record ) {
            	ZF4_Object_Db_Manager::addRecordObject($this);
            }
            //$this->objDebug(get_class($this),'classname');
            $ret = $rdRet;
        } else {
            $ret = $upRet;
        }

        if ($ret ==1) {
	        /**
	         * referential chain processing
	         * @todo update ref chain processing
	         */
        }

        return $ret;
    }


    /**
     * Read data from store into object
     *
     * @return number of records read - should be == 1
     * @throws ZF4_Object_Exception if key not set
     */
    public function read() {
    	$keys = $this->getKey();
        if (!isset($keys)) {
            throw new ZF4_Object_Exception($this->_('Keys not set for read'),Zend_Log::ERR );
        }
        $select = $this->_db->select()->from("{$this->_table}");

        foreach ($keys as $k=>$v) {
        	$select->where($this->_db->quoteIdentifier($k)." = ".$this->_db->quote($v));
        }
        return $this->exec($select,true);
    }

	/**
	 * Read data from store into object
	 * based on some arbitrary where clause
	 *
	 * @param string $where the 'where' part of a sql select
     * @return number of records read - should be == 1
	 */
    public function search($where) {
        $select = $this->_db->select()
               ->from("{$this->_table}")
    		   ->where($where);
    	return $this->exec($select,true);
    }

    /**
     * Extend ancestor to allow referential processing
     * This will update the child objects BUT you must still
     * update() this object to defunct it
     *
     * @return boolean If False then check the messages
     */
	public function defunct() {
		$ret = true;
		//see if we are restricted by chain processing onDelete:RESTRICT
		if (!$this->_checkRefDefunct()) {
			$this->setMsg("Ref Integrity: Unable to Defunct");
			return false;
		} else {
			//process child defuncts - no support for parent defuncts yet
			if (is_array($this->_children)) {
				foreach ($this->_children as $objName => $childDet) {
					/**
					 * @todo move following constant definitions into this class
					 */
					if ($childDet['onDelete'] == ZF4_Defines::OBJRECACT_CASCADE ) {
	    				$refObj = new $objName();
						if ($refObj->fetch(array($this->get($childDet['frnFld'])=>intval($saveId))) == 1) {
							$ret = $refObj->defunct();
							if (!$ret) {
								$this->setMsg($refObj->getMsg());
								break;
							} else {
								$refObj->update();
							}
						}
					}
				}
			}
		}
		if ($ret) {
			$ret = parent::defunct();
		}
		return $ret;
	}

    /**
     * Permanently delete the object's record representation
     *
     * @return fluent interface
     */
    public function trash() {
		//see if we are restricted by chain processing RESTRICT
		if (!$this->_checkRefTrash()) {
			$this->setMsg(self::$this->_("Ref Integrity: Unable to Trash"));
			return $this;
		} else {
		    if (isset($this->id)) {
			    $saveId = $this->id;  //save the object id value
			    $refFlag = true;
		    } else {
		        //don't know what to do about tables that don't have an ID yet
		        //@todo sort out tables with no ID field for ref integrity
		        $refFlag = false;
		    }
		}
		//process child deletes - no support for parent deletes yet
		if ($refFlag && is_array($this->_children)) {
			foreach ($this->_children as $objName => $childDet) {
				if ($childDet['onDelete'] == ZF4_Defines::OBJRECACT_CASCADE ) {
    				$refObj = new $objName();
    				$select = $refObj->getTableObject()->select()->where($childDet['frnFld']."=?",intval($saveId));
    				$childObjects = $refObj->getTableObject()->fetchObjects($select);
    				foreach ($childObjects as $delObj) {
    				    $delObj->trash();
    				    $this->setMsg($delObj->getMsg());
    				}
				}
			}
		}
    	//set where clause
        $where = array();
        $keys = $this->getKey();
        foreach ($keys as $col=>$value) {
    	   $where [] = $this->_db->quoteIdentifier($col)
        	           ." = "
        	           .$this->_db->quote($value,$this->_tableDesc[$col]['DATA_TYPE']);
        }
        if ($this->_transact) $this->beginTransaction();
        try {
            $ret = $this->_db->delete($this->_table,$where);
        }
        catch (Zend_Db_Statement_Exception $e) {
            $msg = $e->getMessage();
            $this->setMsg($msg);
            ZF4_Service_Manager::getService(ZF4_SRVC_LOG)->logApp($e);
            $ret = 0;
        }
        if ($ret==1) {
            if ($this->_transact) $this->commit();
            //remove from object manager
            ZF4_Object_Db_Manager::delRecordObject($this);
            //wipe data
            $this->initData();
        } else {
            if ($this->_transact) $this->rollBack();
        }
        return $this;  //remember this object doesn't exist in datastore now.
    }//end function

    /**
     * prepare a select statement for fetching object from Db
     * @todo allow other operators other than '='
     *
     * @param array $colVars col=>value ..
     * @return ZF4_Db_Select
     */
    protected function _prepSelect($colVars) {
    	$select = $this->_db->select()
                    ->from("$this->_table");
        foreach ($colVars as $col => $value) {
        	$select->where($this->_db->quoteIdentifier($col)
        	               ." = "
        	               .$this->_db->quote($value,$this->_tableDesc[$col]['DATA_TYPE']));
        }
        return $select;
    }

    /**
     * Populate the object from store using specific columns in where clause
     *
     * @param array|string|int $colVals array [col=>value ..], nameCol value or primary key (id) value
     * @return int Number of rows retrieved NB only first row retrieved will be used
     * @throws ZF4_Object_Exception if $colVals contains invalid parameters
     */
    public function fetch($colVals) {
    	if (!is_array($colVals)) {
    		if (is_int($colVals)) {
    			$keys = $this->getKey();
    			$colVals = array(key($keys)=>$colVals); //get first key column name
    		} elseif (is_string($colVals) && !is_null($this->_nameCol)) {
    			$colVals = array($this->_nameCol => $colVals);
    		} else {
    			$msg = sprintf($this->_("Invalid parameters passed to fetch() in class: %s"),get_class($this));
    			throw new ZF4_Object_Exception($msg,Zend_Log::CRIT );
    		}
    	}
        $select = $this->_prepSelect($colVals);
        $ret = $this->exec($select,true);
        return $ret;
    }

    /**
     * Generic statement executor
     *
     * @param Zend_Db_Select|string $statement SQL statement to  execute
     * @param bool $read  Read data as result of exec into object
     * @return  int Number of records effected - may be zero
     */
    public function exec($statement ,$read = false) {
        $records = 0;
        $ret = false;
        try {
            $ret = $this->_db->query($statement);
        }
        catch (Zend_Db_Statement_Exception $e){
            $msg = $e->getMessage();
            $this->setMsg($msg);
            ZF4_Service_Manager::getService(ZF4_SRVC_LOG)->logApp($e);
        }
        if ($ret !== false) {
            $records = $ret->rowCount();
        }
        if ($read && $records>0) {
            $data = $ret->fetchAll();
            $data = $this->_cleanDbData($data[0]);
            $this->exchangeArray($data);
            $this->setNew(false);   //not new data
            $this->setDirty(false); //not changed data
        }
        return $records;
    }//end function

    /**
     * Primarily used to convert date types into standardised format for display
     *
     * <p>It checks the datatypes and converts the incoming data into applicable formats</p>
     * <ul>
     * <li>Database => Object</li>
     * <li>scaler => scalar</li>
     * <li>varchar, enum => string</li>
     * <li>set => array()</li>
     * <li>date[time] => String*</li>
     * </ul>
     * <p>* Date/time formatting</p>
     * <ul>
     * <li>datetime, timestamp => 'Zend_Date::DATE_SHORT HH:MM:SS'</li>
     * <li>date => 'Zend_Date::DATE_SHORT'</li>
     * <li>time => 'HH:MM:SS'</li>
     * </ul>
     *
     *
     * @param array $data
     * @return array
     */
    private function _cleanDbData($data) {
        $_cleanData = array();
        foreach ($data as $key => $value) {
        	if (array_key_exists($key,$this->_tableDesc)) {
        	    $meta = $this->_tableDesc[$key]['DATA_TYPE'];
        	    if (substr($meta,0,3) == 'set') {
        	    	$value = explode(',',$value);
        	    } else {
	        	    switch ($meta) {
	        	    	case 'datetime':
	        	    	case 'timestamp':
		        	        $d = new Zend_Date($value);
		        	        $value = $d->get(Zend_Date::DATE_SHORT )
		        	               . " " . $d->get(Zend_Date::HOUR )
		        	               . ":" . $d->get(Zend_Date::MINUTE)
		        	               . ":" . $d->get(Zend_Date::SECOND);
		       	    		break;
	        	    	case 'date':
		        	        $d = new Zend_Date($value);
		        	        $value = $d->get(Zend_Date::DATE_SHORT);
		       	    		break;
	        	    	case 'time':
		        	        $d = new Zend_Date($value,Zend_Date::TIMES);
		        	        $value = $d->get(Zend_Date::HOUR ) . ":" . $d->get(Zend_Date::MINUTE  ) . ":" . $d->get(Zend_Date::SECOND);
		       	    		break;
	        	    	case 'int':
	        	    	case 'bigint':
	        	    	case 'tinyint':
	        	    	case 'smallint':
		        	    	$value = intval($value);
		        	    	break;
	        	    	case 'double':
	        	    	case 'float':
	        	    		$value = floatval($value);
	        	    		break;
	        	    	default:
		        	    	$value = strval($value);
	        	    		break;
	        	    }//end switch
        	    }
        		$_cleanData[$key] = $value;
        	}
        }
        return $_cleanData;
    }

    /**
     * Initialise the data
     *
     * Extends parent to initialise the keys to null
     *
     * @return Fluent_Interface
     */
    public function initData() {
        $this->setKey(null);
        return parent::initData();
    }

    /**
     * Does the object exist in store
     *
     * Does *not* read the object in
     *
     * @param int|string|array $obj id of object, nameCOl name of object, array of object flds=>values
     * @return boolean True if found else false
     * @throws ZF4_Object_Exception if invalid parameter type
     */
    public function is_a($obj) {
    	if (is_array($obj)) {
	        $select = $this->_prepSelect($obj);
    	} elseif (is_int($obj)) {
    		$keys = $this->getKey();
    		$keyFld = (isset($keys[0]) ? $keys[0] : 'id');
    		$cols = array($keyFld => $obj);
	        $select = $this->_prepSelect($cols);
    	} elseif (is_string($obj) && !is_null($this->_nameCol)) {
    		$cols = array($this->_nameCol => $obj);
	        $select = $this->_prepSelect($cols);
    	} else {
			$msg = sprintf(self::$this->_("Invalid parameter type to %s"),"ZF4_Db_Record::is_a()");
            throw new ZF4_Object_Exception($msg,Zend_Log::ERR );
    	}
        $ret = ($this->exec($select) == 1);
    	return $ret;
    }

    /**
     * Functions supporting referential integrity and chain processing
     */

    /**
     * Get the name of the DB table supporting this object
     *
     * @return string
     */
    public function getTableName() {
    	return $this->_table;
    }

    /**
     * Create and return a ZF4_Object_Db_Table object for this object's underlying
     * Db Table
     *
     * @return ZF4_Object_Db_Table
     */
    public function getTableObject() {
    	if (!isset($this->_tObject)) {
    		//check registry first
    		$tblObj = ZF4_Object_Db_Manager::getTableObject($this->_table,get_class($this));
    		if ($tblObj !== false) {
    			$this->_tObject = $tblObj;
    		} else {
    			$this->_tObject = new ZF4_Object_Db_Table($this, $this->_table, $this->_refParams);
    			//save table object to registry
    			ZF4_Object_Db_Manager::addTableObject($this->_tObject);
    		}
    	}
    	return $this->_tObject;
    }


    /**
     * Create and return a ZF4_Datagrid_Grid object based on
     * data for all records of this object type
     *
     * @return ZF4_Datagrid_Grid
     */
    public function getGridObject() {
    	if (!isset($this->gObject)) {
			$table = $this->getTableObject();
			$tableObject = new ZF4_Datagrid_Grid_DataSource_Table($table);
			$this->gObject = new ZF4_Datagrid_Grid($tableObject);
    	}
    	return $this->gObject;
    }

    /**
     * Create and return a ZF4_Datagrid_Grid object based on
     * data defined by the select object passed in
     *
     * @param Zend_Db_Select $select
     * @return ZF4_Datagrid_Grid
     */
    public function getGridSelectObject(Zend_Db_Select $select) {
		$adapter = $this->getTableObject()->getAdapter();
		$source = new ZF4_Datagrid_Grid_DataSource_DbSelect($select,$adapter);
		$grid = new ZF4_Datagrid_Grid($source);
    	return $grid;
    }

    /**
     * Return array of column values indexed by record id (or other column)
     * Usually used as input for html selectors etc
     * Only returns active records
     *
     * @param string $colName Value column
     * @param string $idName Id column
     * @param string|array $where Optional where filter selection clauses
     * @return array
     */
    public function getForSelect($colName, $idName = 'id', $where = null) {
    	$table = $this->getTableObject();
    	$select = $table->select()
    			  ->from($table,array($idName,$colName))
    			  ->where('rowSts = ?',ZF4_Defines::RSTAT_ACT );
    	if (null != $where) {
    		if (!is_array($where)) $where = array($where);
    		foreach ($where as $clause) {
    			$select->where($clause);
    		}
    	}
        //echo $select->__toString();
    	$records = $table->fetchAll($select);
    	$ret = array();
    	foreach ($records as $record) {
    		$ret[$record->$idName] = $record->$colName;
    	}
    	return $ret;
    }

    /**
     * Set referential integrity (chain processing) parameters
     *
     * $refParams is an array of parameters for determining references by this object
     * to other objects.  It is an array of arrays
     * i.e [0=>[refParameterArray], n =>[refParameterArray]]
     * Each refParameterArray[] consists of 4 values i.e.
     * 	[
     * 		'refType' => one of ZF4_Defines::OBJRECTBL_... constants
     * 		'objName' => class name of object referenced
     * 		'onUpdate' => one of ZF4_Defines::OBJRECACT_... constants.
     * 				for Parent refType only NONE and CASCADE are regarded
     * 				for Child refType, NONE, RESTRICT and CASCADE are regarded
     * 		'ondDelete' => one of ZF4_Defines::OBJRECACT_... constants.
     * 				for Parent refType only NONE and RESTRICT are regarded
     * 				for Child refType only NONE, RESTRICT and CASCADE are regarded
     * 		'frnFld' => string :
     * 				if refType = OBJRECTBL_PARENT then name of this object's
     * 						field that references the id field of objName
     * 				if refType = OBJRECTBL_CHILD then name of objName's field
     * 						that references ths object's id field
     * 	]
     *
     * @param array $refParams if null then this objects ref params are cleared
     * @return fluent interface
     */

    public function setRefParams($refParams) {
    	//store the raw info for onward transmission to table construction
    	$this->_refParams = $refParams;
    	//process it
    	$this->_parents = array();
    	$this->_children = array();
        if ($refParams != null && is_array($refParams)) {
        	foreach ($refParams as $value) {
        		$storeVal = array('onUpdate' => $value['onUpdate'],
        						  'onDelete' => $value['onDelete'],
        						  'frnFld' => $value['frnFld']);
        		switch ($value['refType']) {
        			case ZF4_Defines::OBJRECTBL_PARENT :
        				$this->_parents[$value['objName']] =  $storeVal;
        				break;
        			case ZF4_Defines::OBJRECTBL_CHILD :
        				$this->_children[$value['objName']] =  $storeVal;
        				break;
           			default:
        				break;
        		}
        	}
        }
        return $this;
    }

    /**
     * Check if this object has any impediments to trashing it
     *
     * @return boolean True if OK to trash else False
     */
    private function _checkRefTrash() {
    	$ret = true;
    	if (count($this->_parents) > 0) {
    		//process parents - $ret = false if any parent records exist for RESTRICT
    		foreach ($this->_parents as $objName => $parentDet) {
    			if ($parentDet['onDelete'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				if ($refObj->fetch(array('id'=>intval($this->get($parentDet['frnFld'])))) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}
    	}
    	if ($ret && count($this->_children) > 0) {
    		//process children
    		foreach ($this->_children as $objName => $childDet) {
    			if ($childDet['onDelete'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				if ($refObj->fetch(array($this->get($childDet['frnFld'])=>$this->id)) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}

    	}
    	return $ret;
    }

    /**
     * Check if this object has any impediments to Defuncting it
     *
     * @return boolean True if OK to Defunct else False
     */
    private function _checkRefDefunct() {
    	$ret = true;
    	if (count($this->_parents) > 0) {
    		//process parents - $ret = false if any ACTIVE parent records exist for RESTRICT
    		foreach ($this->_parents as $objName => $parentDet) {
    			if ($parentDet['onDelete'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				if ($refObj->fetch(array(
    						'id'=>intval($this->get($parentDet['frnFld'])),
    						ZF4_Defines::RSTAT_FLD => ZF4_Defines::RSTAT_ACT )) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}
    	}
    	if ($ret && count($this->_children) > 0) {
    		//process children
    		foreach ($this->_children as $objName => $childDet) {
    			if ($childDet['onDelete'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				//@todo check to see how this effects suspended records
    				if ($refObj->fetch(array(
    						$this->get($childDet['frnFld'])=>$this->id,
    						ZF4_Defines::RSTAT_FLD => ZF4_Defines::RSTAT_ACT )) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}

    	}
    	return $ret;
    }

    /**
     * Check if this object has any impediments to Updating it
     *
     * @return boolean True if OK to Update else False
     */
    private function _checkRefUpdate() {
    	$ret = true;
    	if (count($this->_parents) > 0) {
    		//process parents - $ret = false if any ACTIVE parent records exist for RESTRICT
    		foreach ($this->_parents as $objName => $parentDet) {
    			if ($parentDet['onUpdate'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				if ($refObj->fetch(array(
    						'id'=>intval($this->get($parentDet['frnFld'])),
    						ZF4_Defines::RSTAT_FLD => ZF4_Defines::RSTAT_ACT )) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}
    	}
    	if ($ret && count($this->_children) > 0) {
    		//process children
    		foreach ($this->_children as $objName => $childDet) {
    			if ($childDet['onUpdate'] == ZF4_Defines::OBJRECACT_RESTRICT ) {
    				$refObj = new $objName();
    				//@todo check to see how this effects suspended records
    				if ($refObj->fetch(array(
    						$this->get($childDet['frnFld'])=>$this->id,
    						ZF4_Defines::RSTAT_FLD => ZF4_Defines::RSTAT_ACT )) == 1) {
    					$ret = false;
    					break;
    				} else {
    					$ret = true;
    				}
    			}
    		}
    	}
    	return $ret;
    }
/**
 * Transaction processing
 *
 */
    /**
     * Begin a DB transaction
     *
     * @return Fluent_Interface
     */
    public function beginTransaction() {
        $this->_db->beginTransaction();
        return $this;
    }
    /**
     * Commit a DB transaction
     *
     * @return Fluent_Interface
     */
    public function commit() {
        $this->_db->commit();
        return $this;
    }
    /**
     * Rollback a DB transaction
     *
     * @return Fluent_Interface
     */
    public function rollback() {
        $this->_db->rollback();
        return $this;
    }
    /**
     * Set the transaction flag.
     * If true then object insert. updates and deletes
     * will be wrapped in beginTransaction / commit / rollback
     * statements.
     *
     * If False then it is up to you to begin and end the
     * transactions.  NB normal processing REQUIRES that
     * transactions are wrapped else they won't hit the database
     *
     * @param boolean $flag
     * @return Fluent_Interface
     */
    public function setTransactFlag($flag = true) {
        $this->_transact = $flag;
        return $this;
    }
    /**
     * Return the state of the object's transaction flag
     *
     * @return boolean
     */
    public function getTransactFlag() {
        return $this->_transact;
    }

/**
 * Helper functions
 */

    /**
     * get the count of records in the database for the object
     *
     * @param boolean $onlyActive get only Active records?
     * @return int Count or records
     */
    public function getCount($onlyActive = true) {
        $select = $this->getTableObject()->select()->from($this->getTableName(),'count(*) as count');
        if ($onlyActive) {
            $select->where(ZF4_Defines::RSTAT_FLD . " = ?", ZF4_Defines::RSTAT_ACT );
        }
        $ret = $this->getTableObject()->fetchRow($select);
        return intval($ret['count']);
    }
}//end class
