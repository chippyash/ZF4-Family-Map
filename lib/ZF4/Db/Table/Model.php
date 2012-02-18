<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Database
 * @subpackage  Table
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
 * Adds table data as public parameters to the table object
 *
 * <p>Every underlying table to a model must have a primary key = 'id'.</p>
 * <p>Additionally it can have a a UNIQUE INDEX string field which identifies the record
 *
 * @category	ZF4
 * @package 	Database
 * @subpackage  Table
 */
class ZF4_Db_Table_Model extends Zend_Db_Table_Abstract {

	/**
	 * meta info column comment field
	 *
	 */
	const COMMENT = 'COMMENT';
	
	/**
	 * Unique column name on the table
	 *
	 * @var string
	 */
	protected $_uniqueCol = null;
	/**
	 * primary id col
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	/**
	 * column to order results by
	 *
	 * @var string
	 */
	public $orderCol = 'id';
	/**
	 * direction to order results in
	 *
	 * @var string 'asc','desc'
	 */
	public $orderDir = 'asc';

    /**
     * Flag to determine if this entity has an organisation id
     * id is always the orgId field
     * 
     * This flag determines insert and update functionality
     * 
     * @var boolean 
     */
    protected $_hasOrg = true;

    /**
     * Constructor
     *
     * If $id is null an empty table object will be returned
     * If $id is given and results in a valid record being read, the record values are added
     * as public parameters to the object and can be referenced as $object->parameter (read only)
     *
     * @param string $table the name of a table being used by the model
     * @param string $uniqueCol the name of the unique column in the table
     * @param int|string $id the record id or a unique name
     * @param string encryption salt
	 * @throws ZF4_Db_Table_Exception_InvalidId
	 * @throws ZF4_Db_Table_Exception_NoUniqueCol
     */
    public function __construct($table, $uniqueCol = null, $id = null) {
        parent::__construct(array('name'=>$table));
        $this->_uniqueCol = $uniqueCol;
        if (null !== $id) {
        	if (is_int($id)) {
        		$this->fetchRecordById($id);
        	} elseif (is_string($id)) {
        		$this->fetchRecordByName($id);
        	} else {
        		throw new ZF4_Db_Table_Exception_InvalidId();
        	}
        }
	}

	/**
	 * Set the table name
	 *
	 * @param string $tablename
	 * @return ZF4_Db_Table_Model Fluent Interface
	 */
	public function setTablename($tablename) {
		$this->_name = (string) $tablename;
		return $this;
	}
	/**
	 * Return the model's table name
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->_name;
	}
	
	/**
	 * Fetch a record from the database by its primary id
	 * and set the objects public parameters to its values
	 *
	 * @param int $id
	 * @return ZF4_Db_Table_Model Fluent Interface
	 * @throws ZF4_Db_Table_Exception_InvalidId
	 */
	public function fetchRecordById($id) {
		$row = $this->find($id)->toArray();
		if (!isset($row['id'])) {
			$row = $row[0];
		}
		if (null !== $row) {
			$this->_setRecordData($row);
		} else {
			throw new ZF4_Db_Table_Exception_InvalidId();
		}
		return $this;
	}

	/**
	 * Fetch a record from the database by its unique name field
	 * and set the objects public parameters to its values
	 *
	 * @param string $name
	 * @return ZF4_Db_Table_Model Fluent Interface
	 * @throws ZF4_Db_Table_Exception_InvalidId
	 * @throws ZF4_Db_Table_Exception_NoUniqueCol
	 */
	public function fetchRecordByName($name) {
		$row = $this->fetchRow($this->_getUniqueCol() . "='" . $name . "'");
		if (null !== $row) {
			$this->_setRecordData($row->toArray());
		} else {
			throw new ZF4_Db_Table_Exception_InvalidId();
		}
		return $this;
	}

	/**
	 * Return the unique index column name on this table
	 *
	 * @return string
	 * @throws ZF4_Db_Table_Exception_NoUniqueCol
	 */
	private function _getUniqueCol() {
		if ($this->_uniqueCol == null) {
			throw new ZF4_Db_Table_Exception_NoUniqueCol();
		}
		return $this->_uniqueCol;
	}

	/**
	 * Return the record parameters
	 *
	 * Will strip out any parameters beginning with _
	 *
	 * @return array
	 */
	public function getRecordData() {
		$vars =  get_object_vars($this);
		foreach ($vars as $key=>$value) {
			if (strpos($key,'_') === 0) {
				unset($vars[$key]);
			}
		}
		return $vars;
	}

	/**
	 * Set public parameters on this object
	 *
	 * @param array $data
	 * @return ZF4_Db_Table_Model Fluent Interface
	 */
	protected function _setRecordData(array $data) {
		foreach ($data as $key=>$value) {
			$this->$key = $value;
		}
		return $this;
	}

	/**
	 * Get id->someNameColumn for use in form selectors
	 *
	 * If the object has a rowSts field then only active rows are returned
	 * 
	 * If you use an array for nameCol, the first column name will be returned as a the field name
	 * and the values from all the fields will be concatenated
	 *
	 * @param string|array $nameCol name column or array of columns to use - default is the unique column for the table
	 * @param array $where additional where clauses to add to selection
	 * @return array 
	 */
	public function getForSelect($nameCol = null, array $where = array()) {
		
		if ($nameCol == null) {
			$nameCol = $this->_getUniqueCol();
			$fldSel = array('id',$nameCol);
		} elseif (is_array($nameCol)) {
			$t ="concat_ws(' '," . implode(',',$nameCol) . ')';
			$fldSel = array('id',$nameCol[0] =>new Zend_Db_Expr($t));
			$nameCol = $nameCol[0];
		} else {
			$fldSel = array('id',$nameCol);
		}
		$select = $this->select()
				       ->from($this,$fldSel)
				       ->order($this->orderCol.' '.$this->orderDir);
					   
		$cols = $this->getAdapter()->describeTable($this->_name);
		if (isset($cols['rowSts'])) {
			//only include active rows for selectors
			//if we have a row status field
			$select->where('rowSts = ','active');
		}
		if (count($where) > 0) {
			foreach ($where as $w) {
				$select->where($w);
			}
		}

		$rows = $this->getAdapter()->fetchAll($select);
		$retArr = array();
		if (count($rows)>0) {
			foreach ($rows as $row) {
				$retArr[intval($row['id'])] = $row[$nameCol];
			}
		}
		return $retArr;
	}
	
    /**
     * Returns an instance of a ZF4_Db_Table_Select object.
     * 
     * OVERIDES ancestor method
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART) {
        $select = new ZF4_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }    
	
    /**
     * Zend doesn't return the column comments. so use this to 
     * get the table column metadata including comments
     * 
     * NB This is only tested for MySQL
     * 
     * If you don't need comments use ->info(Zend_Db_Table_Abstract::METADATA) instead
     * as this is a bit slow
     *
     */
    public function getColInfo() {
    	$meta = $this->info(Zend_Db_Table_Abstract::METADATA);
    	$sql = 'SHOW FULL COLUMNS FROM `' . $this->getTableName() .'`';
    	$stmt = $this->getAdapter()->query($sql);
    	$result = $stmt->fetchAll(Zend_Db::FETCH_NUM);
    	foreach ($result as $col) {
    		$meta[$col[0]][self::COMMENT] = $col[8];
    	}
    	return $meta;
    }
    
    /**
     * Get the values out of an enum field definition
     *
     * @param string $fld
     * @return string
     */
    protected function _getEnumValues($fld) {
    	$meta = $this->getColInfo();
    	$pattern = "/enum\((?P<values>.*)\)/";
    	$matches = array();
    	if (preg_match($pattern,$meta[$fld]['DATA_TYPE'],$matches) == 1) {
    		return str_replace("'",'',$matches['values']);
    	} else {
    		return '';
    	}
    }

    /**
     * Inserts a new row.
     *
     * Extends ancestor to ensure organisation id is set
     * from current user if hasOrg flag is set
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
	if ($this->_hasOrg && !isset($data['orgId'])) {
		$user = ZF4_User::getSessionIdentity();
		$data['orgId'] = $user['orgId'];
	}
    	return parent::insert($data);
    }

}
















