<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
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
 * Defines a ZF4 object that relies on a file for its data.  The file will be
 * separated into records by some mechanism (commonly line breaks.)
 *
 * <p>This object handles one record only.</p>
 * <p>The activate(), suspend() and defunct() methods have no real relevance for
 * this type of object as files will not typically contain these fields.</p>
 * <p>Use trash() to delete a record</p>
 * <p>if the record contains an 'id' field it will be used, otherwise the position in the data array is used as the id.</p>
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
 */
class ZF4_Object_File_Record extends ZF4_Object_Data {

    /**
     * The file that this record operates on
     *
     * @var string
     */
    protected $_file;
    /**
     * Name of UNIQUE column that will be used if a string
     * is passed into the constructor to instantiate the object
     * @see setNameCol()
     * @var string
     */
    protected $_nameCol;
    /**
     * File header description
     *
     * @var array
     */
    protected $_fileDesc;
    /**
     * File Handler object for this object
     *
     * @var ZF4_Object_File_Handler_Interface
     */
    private $_fObject;
    /**
     * Grid object datasource for this object
     *
     * @var ZF4_Datagrid_Grid_Datasource
     */
    private $_gObject;

    /**
     * Constructor
     *
     * The file must exist before accessing it via this object
     *
     * To use $search as an int, the data must contain a field called 'id'
     *
     * @param ZF4_Object_File_Handler_Interface $handler  File handler to use
     * @param array|string|int $search array of search values [colName=>value], nameCol value or id value
     * @param string $nameCol Name of column to use where $search is a string
     * @param boolean $noLang If True, do not load the translator for messaging service
     *
     * @see ZF4_File_Handler_Csv
     * @throws ZF4_Object_Exception if search is invalid
     * @throws ZF4_Object_Exception if handler is invalid
     */
    public function __construct(
        ZF4_Object_File_Handler_Interface $handler,
        $search = null,
        $nameCol = null,
        $noLang = false)
    {
        //construct the new object
        parent::__construct($noLang);
        $this->_fObject = $handler;

        //get the file header description
        $this->_fileDesc = $this->_fObject->getHeader();
        $this->_nameCol = $nameCol;
        $this->setMetaData($search);   //set meta data and load object
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
    private function setMetaData($search = null) {
        //set up empty data array in event that we are constructed without search values
        $data = array();
        $checkId = false;
        if (isset($search)) {
            //modify the search as necessary
            if (is_int($search)) {
                $search = array('id' => $search);
                $checkId = true;
            } elseif (is_string($search) && !is_null($this->_nameCol)) {
                $search = array($this->_nameCol => $search);
            } elseif (is_string($search) && is_null($this->_nameCol)) {
                //throw an error
                $msg = sprintf($this->_("No name column in class %s."), get_class($this));
                throw new ZF4_Object_Exception($msg, E_USER_ERROR);
            } elseif (!is_array($search)) {
                //throw an error
                $msg = sprintf($this->_("Invalid object search parameters in class %s. Type= %s, Value= %s"), get_class($this), gettype($search), (string) $search);
                throw new ZF4_Object_Exception($msg, E_USER_ERROR);
            }
        }
        if (isset($search)) {
            //get the object data from store
            $this->setKey($search);
            $this->read($checkId);
        } else {
            //setup an empty object
            $this->exchangeArray($data);
            $this->setDirty(false);  //need to clear flag as setData will have set it
        }
    }

    /** CRUD FUNCTIONALITY * */

    /**
     * 1/ remove any fields in the data that are not in the file
     * 2/ convert date fields into DB compatible format
     *
     * @param array $data (colname=>value)
     * @return array the cleaned up data
     */
    private function cleanData($data) {
        $cleanData = array();
        $cols = $this->_fObject->getHeader();
        foreach ($data as $key => $value) {
            if (in_array($key, $cols)) {
                $cleanData[$key] = $value;
            }
        }
        return $cleanData;
    }

    /**
     * Strip data array of null values
     *
     * @param array $data product of this->getData()
     * @return array Data
     */
    private function stripNulls($data) {
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
     * @param boolean $stripNulls Strip out null value fields before updating
     * @return int Number or records created (0 or 1)
     */
    protected function make($stripNulls) {
        if (!$this->getDirty()) {
            return 0;
        } else {
            $data = $this->toArrayAll();
            if ($stripNulls) {
                $data = $this->stripNulls($data);
            }
            //clean up fields that don't belong to the record
            $data = $this->cleanData($data);

            $ret = $this->_fObject->insert($data);

            //if one record created
            if ($ret == 1) {
                //if data doesn't have an id column then create it
                $this->id = $this->_fObject->lastInsertId();
            }
            return $ret;
        }
    }

    /**
     * Update the object's record
     *
     * @param boolean $stripNulls If true then strip out any fields that have null values
     * @return int Number of rows effected (0 or 1)
     */
    protected function doUpdate($stripNulls) {
        if (!$this->getDirty()) {
            return 0;
        } else {
            $data = $this->toArrayAll();
            if ($stripNulls) {
                $data = $this->stripNulls($data);
            }
            //clean up fields that don't belong to the record
            $data = $this->cleanData($data);

            $ret = $this->_fObject->update($this->id, $data);

            return $ret;
        }
    }

    /**
     * Update the object to file
     *
     * @param boolean $stripNulls Strip out null fields before update
     * @return int Number of records effected (0 or 1)
     */
    public function update($stripNulls = false) {
        if ($this->getNew()) {
            $ret = $this->make($stripNulls);
        } else {
            $ret = $this->doUpdate($stripNulls);
        }
        return $ret;
    }

    /**
     * Read data from store into object
     *
     * @param boolean $checkId Check to see if record has an id field
     * @return number of records read - should be == 1
     * @throws ZF4_Object_Exception if key not set
     */
    public function read($checkId = false) {
        $keys = $this->getKey();
        if (!isset($keys)) {
            throw new ZF4_Object_Exception($this->_('Keys not set for read'), Zend_Log::ERR);
        }
        $ret = $this->search($keys, $checkId);
        return $ret;
    }

    /**
     * Read data from store into object
     * based on some arbitrary set of columns
     *
     * @param array $where [colname1=>value, ... ]
     * @param boolean $checkId Check to see if record has an id field
     * @return number of records read - should be == 1
     */
    protected function search($where, $checkId = false) {
        $data = $this->_fObject->searchData($where, $checkId);
        if ($data['result']) {
            $this->exchangeArray($data['data']);
            $this->set('id', $data['id'])
                    ->setNew(false);
            return 1;
        } else {
            $this->initData();
            $this->set('id', 0);
            return 0;
        }
    }

    /**
     * Populate the object from store using specific columns in where clause
     *
     * @param array|string|int $colVals array [col=>value ..], nameCol value or primary key (id) value
     * @return int Number of rows retrieved NB only first row retrieved will be used
     * @throws ZF4_Object_Exception if $colVals contains invalid parameters
     */
    public function fetch($colVals) {
        $ret = $this->search($colVals);
        return $ret;
    }

    /**
     * Delete the record
     *
     * @return Fluent_Interface
     */
    public function trash() {
        $this->_fObject->trash($this->id);
        $this->initData();
        return $this;
    }

    public function exec($statement, $read = false) {

    }

    /**
     * Initialise the data
     *
     * Extends parent to initialise the keys to null and remove any public fields
     *
     * @return Fluent_Interface
     */
    public function initData() {
        $this->setKey(null);
        parent::initData();
        if (!empty($this->_fObject)) {
            $headers = $this->_fObject->getHeader();
            foreach ($headers as $col) {
                if (isset($this->$col)) {
                    unset($this->$col);
                }
            }
        }
        return $this;
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
            $select = $this->prepSelect($obj);
        } elseif (is_int($obj)) {
            $keys = $this->getKey();
            $keyFld = (isset($keys[0]) ? $keys[0] : 'id');
            $cols = array($keyFld => $obj);
            $select = $this->prepSelect($cols);
        } elseif (is_string($obj) && !is_null($this->_nameCol)) {
            $cols = array($this->_nameCol => $obj);
            $select = $this->prepSelect($cols);
        } else {
            $msg = sprintf(self::$this->_("Invalid parameter type to %s"), "ZF4_Db_Record::is_a()");
            throw new ZF4_Object_Exception($msg, Zend_Log::ERR);
        }
        $ret = ($this->exec($select) == 1);
        return $ret;
    }

    /**
     * Get the name of the file supporting this object
     * Psuedonym for getFileName
     *
     * @return string
     */
    public function getTableName() {
        return $this->getFileName();
    }

    /**
     * Get the name of the file supporting this object
     *
     * @return string
     */
    public function getFileName() {
        return $this->_file;
    }

    /**
     * Return the ZF4_Object_File_Handler_Interface object for this object's underlying
     * file.  Psuedonym for getFileObject()
     *
     * @return ZF4_Object_File_Handler_Interface
     */
    public function getTableObject() {
        return $this->getFileObject();
    }

    /**
     * Return the ZF4_Object_File_Handler object for this object's underlying
     * file
     *
     * @return ZF4_Object_File_Handler
     */
    public function getFileObject() {
        return $this->_fObject;
    }

    /**
     * Create and return a ZF4_Datagrid_Grid_Datasource object based on
     * data for all records of this object type.
     *
     * Adds a rowstatus field for grid display requirements
     *
     * @return ZF4_Datagrid_Grid_Datasource
     */
    public function getGridObject() {
        if (!isset($this->gObject)) {
            $data = $this->_fObject->getData();
            foreach ($data as &$row) {
                $row[ZF4_Defines::RSTAT_FLD] = ZF4_Defines::RSTAT_ACT;
            }
            //$dataObject = new ZF4_Datagrid_Grid_DataSource_Array($data);
            //$this->gObject = new ZF4_Datagrid_Grid($dataObject);
            $this->gObject = new ZF4_Datagrid_Grid_DataSource_Array($data);
        }
        return $this->gObject;
    }

    /**
     * Return array of column values indexed by record id (or other column)
     * Usually used as input for html selectors etc
     *
     * @param string $colName Value column
     * @param string $idName Id column
     * @return array
     */
    public function getForSelect($colName, $idName = 'id') {
        $records = $this->_fObject->getData();
        $ret = array();
        foreach ($records as $record) {
            $ret[$record[$idName]] = $record[$colName];
        }
        return $ret;
    }

    /**
     * Helper functions
     */

    /**
     * get the count of records in the database for the object
     *
     * @param boolean $onlyActive get only Active records? (IGNORED)
     * @return int Count or records
     */
    public function getCount($onlyActive = true) {
        $ret = count($this->_fObject->getData());
        return $ret;
    }

}

//end class
