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
 * 'Filtered' Record table object that allows only certain fields to be picked up
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 */
class ZF4_Object_Db_Record_Filter extends ZF4_Object_Db_Record {

	/**
	 * The filtered columns - ie the ones we want from the underlying table
	 *
	 * @var array [colname...]
	 */
	protected $_filterCols;
	
   /**
     * Constructor
     *
     * <p>Set the record object, required columns and table name for the table.</p>
     * <p>Overides ancestor</p>
     *
     * @param ZF4_Object_Db_Record|string $recObj ZF4_Object_Db_Record descendent or name of table
     * @param array $filterCols Array of columns to filter data on
     * @params int|string|array $search record search parameters
     */
    public function __construct($recObj, $filterCols, $search = null) {
    	if ($recObj instanceof ZF4_Object_Db_Record) {
    		//retrieve the table name from the object
    		$recObj = $recObj->getTableName();
    	}
    	$this->setFilteredColumns($filterCols);
    	parent::__construct($recObj, $search);
    }

    /**
     * Set the filter columns 
     * 
     * Will add standard columns if not present
     *
     * @param array $colArr Array of column names
     * @return Fluent_Interface
     * @throws ZF4_Object_Exception if parameter is not an array
     */
    public function setFilteredColumns($colArr) {
    	if (!is_array($colArr)) {
    		throw new ZF4_Object_Exception('Parameter to ' . __FUNCTION__ . ' is not an array', E_USER_ERROR);
    	}
    	//make sure we have standard columns in the array
    	$this->_filterCols = array_merge($colArr,
    			array(	ZF4_Defines::RID_FLD, 
    					ZF4_Defines::RSTAT_FLD, 
    					ZF4_Defines::RUID_FLD, 
    					ZF4_Defines::RDT_FLD  ));
    	return $this;
    }
    
    /**
     * Return the current set of filtered columns
     *
     * @return array
     */
    public function getFilteredColumns() {
    	return $this->_filterCols;
    }

    /**
     * Read data from store into object
     *
     * Overides ancestor
     * 
     * @return number of records read - should be == 1
     * @throws ZF4_Object_Exception if key not set
     */
    public function read() {
    	$keys = $this->getKey();
        if (!isset($keys)) {
            throw new ZF4_Object_Exception($this->_('Keys not set for read'),Zend_Log::ERR );
        }
        $select = $this->_db->select()->from("{$this->_table}",$this->_filterCols);

        foreach ($keys as $k=>$v) {
        	$select->where($this->_db->quoteIdentifier($k)." = ".$this->_db->quote($v));
        }

        return $this->exec($select,true);
    }
    
	/**
	 * Read data from store into object
	 * based on some arbitrary where clause
	 * 
	 * Overides ancestor
	 *
	 * @param string $where the 'where' part of a sql select
     * @return number of records read - should be == 1
	 */
    public function search($where) {
        $select = $this->_db->select()
               ->from("{$this->_table}",$this->_filterCols)
    		   ->where($where);
    	return $this->exec($select,true);
    }
    
    /**
     * prepare a select statement for fetching object from Db
     * @todo allow other operators other than '='
     *
     * Overides ancestor
     * 
     * @param array $colVars col=>value ..
     * @return ZF4_Db_Select
     */
    protected function prepSelect($colVars) {
    	$select = $this->_db->select()
                    ->from("$this->_table",$this->_filterCols);
        foreach ($colVars as $col => $value) {
        	$select->where($this->_db->quoteIdentifier($col)
        	               ." = "
        	               .$this->_db->quote($value,$this->_tableDesc[$col]['DATA_TYPE']));
        }
        return $select;
    }
    
}