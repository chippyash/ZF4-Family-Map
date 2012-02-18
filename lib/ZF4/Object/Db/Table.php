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
 * Database table object
 *
 * The database adapter for all Zend_Db_Table_Abstract descendent
 * objects is set during the initialisation of the database service
 * You can overide this by setting the Zend_Db_Table_Abstract::ADAPTER
 * parameter when constructing this object
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 */

class ZF4_Object_Db_Table extends Zend_Db_Table_Abstract {

	const UNIQUE = 'unique';

	/**
	 * Unique key column names
	 *
	 * @var array
	 */
	protected $_unique;

    /**
     * The ZF4_Object_Db_Record descendent object for this
     * table object
     *
     * @var string
     */
    private $_recObj;

   /**
     * Constructor
     *
     * <p>Set the record object and table name for the table.</p>
     * <p>Overides ancestor</p>
     *
     * @param ZF4_Object_Db_Record|string $recObj descendent or name of ZF4_Object_Db_Record
     * @param string $table Name of database table supporting this object.  If null it is pulled from the record object
     * @params array $params array of parameters specifying child and parent tables
     */
    public function __construct($recObj, $table = null, $params = null) {
    	if (is_null($table)) {
    		//get table name from record object
    		if (is_string($recObj)) $recObj = new $recObj();
    		$table = $recObj->getTableName();
    	}
    	$this->setRecordObject($recObj);
        parent::__construct(array(Zend_Db_Table_Abstract::NAME => $table));

        //if we have any parameters then process
        //@todo Complete the referential stuff for tables
        if ($params != null && is_array($params)) {
        	foreach ($params as $value) {
        		switch ($value['refType']) {
        			case ZF4_Defines::OBJRECTBL_PARENT :

        				break;

        			case ZF4_Defines::OBJRECTBL_CHILD :

        				break;
           			default:
        				break;
       			}
      		}
    	}
    }

    /**
     * Set the ZF4_Object_Db_Record descendent object for this table object
     *
     * @param  ZF4_Object_Db_Record|string $obj descendent of ZF4_Object_Db_Record or class name
     * @throws ZF4_Object_Exception if invalid $obj type given
     */
    public function setRecordObject($obj) {
    	if (is_string($obj)) {
    		if (class_exists($obj)) {
		        if (!$obj instanceof ZF4_Object_Db_Record) {
					$translator = Zend_Registry::get(ZF4_Application_Resource_Language::REGKEY_TRANSLATE );
					$msg = sprintf($translator->_("Invalid object type given in %s"),"ZF4_Object_Db_Table::setRecordObject()");
		            throw new ZF4_Object_Exception($msg,Zend_Log::CRIT );
		        }
    			$this->_recObj = $obj;
    		}
    	} else {
	        if (!$obj instanceof ZF4_Object_Db_Record) {
				$translator = Zend_Registry::get(ZF4_Application_Resource_Language::REGKEY_TRANSLATE );
				$msg = sprintf($translator->_("Invalid object type given in %s"),"ZF4_Object_Db_Table::setRecordObject()");
	            throw new ZF4_Object_Exception($msg,Zend_Log::CRIT );
	        }
	        $this->_recObj = get_class($obj);
    	}
        return $this;
    }

    /**
     * return the record object classname being used by this table object
     *
     * @return String
     */
    public function getRecordObject() {
    	return $this->_recObj;
    }

    /**
     * Fetch rows of data as ZF4_Object_Db_Record based objects
     * If the select parameter is null, will fetch all rows
     *
     * @todo See if there is a more efficient way of fetching objects from cache manager
     * @param Zend_Db_Table_Select $select Default null
     * @return array of ZF4_Object_Db_Record(s)
     * @throws ZF4_Object_Exception if the record object type has not been set
     */
    public function fetchObjects($select = null) {
        if (!isset($this->_recObj)) {
            $translator = Zend_Registry::get(ZF4_Application_Resource_Language::REGKEY_TRANSLATE );
            throw new ZF4_Object_Exception($translator->_("Record object type not set"), Zend_Log::ERR );
        }
        $rows = $this->fetchAll($select);
        //Zend_Debug::dump($rows,  get_class($this));
        $ret = array();
        foreach ($rows as $row) {
        	$obj = new $this->_recObj();
        	$obj->exchangeArray($row->toArray());
        	$ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Wrap a delete transaction
     *
     * @param string|array $where where condition
     */
    public function delete($where) {
    	$db = parent::getAdapter();
    	$db->beginTransaction();
    	parent::delete($where);
    	$db->commit();
    }

    /**
     * Wrap an insert transaction
     *
     * @param array $data
     */
    public function insert(array $data) {
    	$db = parent::getAdapter();
    	$db->beginTransaction();
    	parent::insert($data);
    	$db->commit();
    }

    /**
     * Wrap an update transaction
     *
     * @param array $data
     * @param array|string $where
     */
    public function update(array $data, $where) {
    	$db = parent::getAdapter();
    	$db->beginTransaction();
    	parent::update($data, $where);
    	$db->commit();
    }

    /**
     * Returns table information.
     *
     * <p>You can elect to return only a part of this information by supplying its key name,
     * otherwise all information is returned as an array.</p>
     * <p>Overides ancestor method to add unique columns</p>
     *
     * @param  $key The specific info part to return OPTIONAL
     * @return mixed
     *
     */
    public function info($key = null)
    {
        $this->_setupPrimaryKey();
        $this->_setupUniqueKey();

        $info = array(
            self::SCHEMA           => $this->_schema,
            self::NAME             => $this->_name,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array) $this->_primary,
            self::METADATA         => $this->_metadata,
            self::ROW_CLASS        => $this->_rowClass,
            self::ROWSET_CLASS     => $this->_rowsetClass,
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
            self::SEQUENCE         => $this->_sequence,
            self::UNIQUE		   => (array) $this->_unique
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('There is no table information for the key "' . $key . '"');
        }

        return $info[$key];
    }

    /**
     * Sets up unique keys associated with table
     *
     */
    protected function _setupUniqueKey() {
    	if (empty($this->_unique)) {
	        foreach ($this->_metadata as $col) {
	            if (isset($col['UNIQUE']) && $col['UNIQUE']) {
	                $this->_unique[ $col['UNIQUE_POSITION'] ] = $col['COLUMN_NAME'];
	            }
	        }
    	}
    }

    /**
     * Workaround for ZF BUG ZF-6628 (prev ZF-5695)
     * @todo - remove/replace when issue BUG ZF-6628 resolved
     *
     * @return null|array indexed positional array column names that participate in a table's UNIQUE indexes
     */
    public function getUniqueCols() {
    	$this->_setupUniqueKey();
    	return $this->_unique;
    }

    /**
     * Returns an instance of a ZF4_Db_Table_Select object.
     *
     * OVERIDES ancestor method
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return ZF4_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new ZF4_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }

}