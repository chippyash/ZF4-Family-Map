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
 * File handler for CSV files
 * 
 * <p>A csv file is a comma separated variable file.  The first line contains
 * the field names</p>
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
 */
class ZF4_Object_File_Handler_Csv extends ZF4_Object_File_Handler_Abstract {

    /**
     * The file header
     *
     * @var array
     */
    protected $_header = array();
    /**
     * The file data rows
     *
     * @var array
     */
    protected $_rows = array();
    /**
     * field delimiter
     *
     * @var string
     */
    protected $_delimiter = ',';
    /**
     * Id of last record inserted
     *
     * @var int
     */
    private $_lastInsertId = 0;
    /**
     * Does the file have a header line?
     *
     * @var boolean
     */
    private $_fileHasHeader = true;

    /**
     * Constructor
     *
     * Will create the file if it doesn't exist.
     * To do this the $options['header'] must be an array of column headings
     *
     * @param string $fileName Full path to file
     * @param boolean $load Load the file into internal storage on construction
     * @param ZF4_Crypt $encrypter Encrypter to use if required
     * @param array $options array of additional options required by concrete
     *                       implementation
     *              ['header'] - array of column headings. Optional - required
     *                           to create a new file or if data file does not have
     *                           a header line
     *              ['delimiter'] - string|char field delimiter. Optional
     *                          default == ','
     *              ['hasHeader'] - boolean. Set == false if file does not have
     *                              a header line . default = true.  If false
     *                              you need to give the 'header' option.
     * @throws ZF4_Object_Exception If file is unreadable
     */
    public function __construct(
            $fileName,
            $load = false,
            ZF4_Crypt $encrypter = null,
            array $options = array())
    {
        if (isset ($options['delimiter'])) {
            $this->setDelimiter($options['delimiter']);
        }
        if (isset($options['hasHeader'])) {
            $this->_fileHasHeader = (boolean) $options['hasHeader'];
            if (!$this->_fileHasHeader  && !isset($options['header'])) {
                throw new ZF4_Object_Exception_PropertyNotFound("options['header']");
            }
        }
        if(!file_exists($filename) && isset ($options['header'])) {
            $this->_file = $fileName;
            $this->setEncrypter($encrypter);
            $this->setHeader($options['header']);
            $this->create();
        }
        
        parent::__construct($fileName,$load,$encrypter);
    }

    /**
     * Construct rows of data from file
     *
     */
    protected function _postFetch() {
        //NB this will reduce the _data parameter to nil
        //get the header row
        $this->setHeader(explode($this->_delimiter, array_shift($this->_data)));
        //get the data
        $c = count($this->_data);
        $this->_rows = array();
        for ($x = 0; $x < $c; $x++) {
            $row = explode($this->_delimiter, array_shift($this->_data));
            $v = 0;
            $newArr = array();
            foreach ($this->getHeader() as $colName) {
                $newArr[$colName] = (isset($row[$v]) ? $row[$v] : null);
                $v++;
            }
            $this->_rows[] = $newArr; //add a row of formatted data
        }
        $this->_data = null; //make sure data is clear
    }

    /**
     * Get data from internal data store
     *
     * @return array The data in [[colName=>value, ...], ...] format
     */
    public function getData() {
        return $this->_rows;
    }

    /**
     * Set internal store to some new data
     *
     * @param array $data The data in [[colName=>value, ...], ...] format
     * @return Fluent_Interface
     */
    public function setData($data) {
        $this->_rows = $data;
        return $this;
    }

    /**
     * Get header information
     *
     * @return array [colName1, ... , colNameN]
     */
    public function getHeader() {
        return $this->_header;
    }

    /**
     * Set the header column info
     *
     * @param array $header [colName1, ... , colNameN]
     * @return Fluent_Interface
     */
    public function setHeader(array $header) {
        $this->_header = $header;
        return $this;
    }

    /**
     * Get field delimiter
     *
     * @return string
     */
    public function getDelimiter() {
        return $this->_delimiter;
    }

    /**
     * Set field delimiter
     *
     * @param string $delim delimiter
     * @return Fluent_Interface
     */
    public function setDelimiter($delim) {
        $this->_delimiter = $delim;
        return $this;
    }

    /**
     * Create ouput data from data rows
     * 
     * Makes sure data is output in same order as the header 
     */
    protected function _preWrite() {
        $this->_data = array();
        $header = $this->getHeader();
        //write out the header
        $this->_data[] = implode($this->_delimiter, $header);
        //write out the data
        foreach ($this->_rows as $row) {
            $tmpArr = array();
            foreach ($header as $col) {
                $tmpArr[] = $row[$col];
            }
            $this->_data[] = implode($this->_delimiter, $tmpArr);
        }
    }

    /**
     * Add a new row to the csv file
     *
     * @param array $data
     * @return int number of records inserted. should be 1 or 0
     */
    public function insert($data) {
        array_push($this->_rows, $data);
        $keys = array_keys($this->_rows);
        $this->_lastInsertId = array_pop($keys);
        if (isset($data['id'])) {
            $this->_rows[$this->_lastInsertId]['id'] = $this->_lastInsertId;
        }
        $this->write();
        return 1;
    }

    /**
     * Get the id of the last inserted record
     *
     * @return int
     */
    public function lastInsertId() {
        return $this->_lastInsertId;
    }

    /**
     * Search the data for specific row
     *
     * @param array $search [colName=>value, ... ]
     * @param boolean $checkId Check to see if record has an id field
     * @return array Array of information [result:boolean, id:int, data:array]
     */
    public function searchData(array $search, $checkId = false) {
        if ($checkId) {
            //see if record has no id field
            if (array_search('id', $this->getHeader()) === false) {
                $foundKey = true;
                $key = intval($search['id']);
            } else {
                $foundKey = false;
            }
        } else {
            $foundKey = false;
        }
        if (!$foundKey) {
            if (is_array($this->_rows)) {
                foreach ($this->_rows as $key => $record) {
                    if ($this->_searchRecord($search, $record)) {
                        $foundKey = true;
                        break;
                    }
                }
            }
        }
        //check that record exists
        if ($foundKey && !isset($this->_rows[$key])) {
            $foundKey = false;
        }
        $retArr = array(
            'result' => $foundKey,
            'id' => ($foundKey ? $key : null),
            'data' => ($foundKey ? $this->_rows[$key] : null),
        );
        return $retArr;
    }

    /**
     * Search a record for matching values
     *
     * @param array $search columns to search for
     * @param array $record record to search in
     * @return boolean
     */
    protected function _searchRecord(array $search, array $record) {
        $result = array_intersect_key($record, $search);
        $ret = ($result == $search);
        return $ret;
    }

    /**
     * Update an existing row in the csv file
     *
     * @param int $id	The record id to update (index in rows array)
     * @param array $data The data to write out
     * @return int number of records inserted. should be 1 or 0
     */
    public function update($id, $data) {
        $id = intval($id);
        if (!isset($this->_rows[$id])) {
            //no record
            return 0;
        }
        $this->_rows[$id] = $data;
        $this->write();
        return 1;
    }

    public function trash($id) {
        $id = intval($id);
        if (isset($this->_rows[$id])) {
            unset($this->_rows[$id]);
            $this->write();
        }
    }

}
