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
 * 'Searchable' Record table object manages insertion, deletion and update
 * of record into the search service.
 * 
 * Implements the Searchable trait and adds CRUD support for it
 * 
 * You must set the module name in your ancestor record object by setting
 * protected $_modName = 'mymodulename'
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 * @see ZF4_Trait_Db_Searchable
 */
class ZF4_Object_Db_Record_Searcheable extends ZF4_Object_Db_Record {

	/**
	 * You must set the module name in your ancestor record object
	 * 
	 * @var string
	 */
	protected $_modName = 'Unknown';
	
	/**
	* Index the entire table into the search service
	* Indexes only Active records
	*
	*/
	public function indexAllRecords() {
		$tObj = $this->getTableObject();
		$select = $tObj->select()->where(ZF4_Defines::rstatWhere());
		$records = $tObj->fetchObjects($select);
		foreach ($records as $record) {
			$record->setSearchIndex($this->_modName);
		}
	}
	
	/**
	* Re-index the entire table into the search service
	* Indexes only active records
	*
	*/
	public function reindexAllRecords() {
		$tObj = $this->getTableObject();
		$select = $tObj->select()->where(ZF4_Defines::rstatWhere());
		$records = $tObj->fetchObjects($select);
		foreach ($records as $record) {
			$record->updateSearchIndex($this->_modName);
		}
	}
	
/**
 * Database support for searchable trait
 */
	
	/**
	 * Create new object - extend ancestor
	 *
	 * @param boolean $_stripNulls
	 * @return int
	 */
	protected function _make($_stripNulls) {
		$ret = parent::_make($_stripNulls);
		if ($ret != 0) {
			$this->setSearchIndex($this->_modName);
		}
		return $ret;
	}
	
	/**
	 * Update object - extends ancestor
	 *
	 * @param boolean $_stripNulls
	 * @return int
	 */
	protected function _doUpdate($_stripNulls) {
		$ret = parent::_doUpdate($_stripNulls);
		if ($ret != 0) {
			$this->updateSearchIndex($this->_modName);
		}
		return $ret;
	}
	
	/**
	 * Activate object - extends ancestor
	 *
	 * @return boolean
	 */
	public function activate() {
		$ret = parent::activate();
		if ($ret) {
			$this->setSearchIndex($this->_modName);
		}
		return $ret;
	}
	
	/**
	 * Suspend object - extends ancestor
	 *
	 * @return boolean
	 */
	public function suspend() {
		$ret = parent::suspend();
		if ($ret) {
			$this->delSearchIndex($this->_modName);
		}
		return $ret;
	}
	
	/**
	 * Defunct object - extends ancestor
	 *
	 * @return boolean
	 */
	public function defunct() {
		$ret = parent::defunct();
		if ($ret) {
			$this->delSearchIndex($this->_modName);
		}
		return $ret;
	}
	
	/**
	 * Trash object - extends ancestor
	 *
	 * @return ZF4_Object_Db_Record Fluent interface
	 */	
	public function trash() {
		$this->delSearchIndex($this->_modName);
		return parent::trash();
	}
	
    /**
     * Add Searcheable trait
     * Provides:
     * 		$this->setSearchIndex()
     * 		$this->delSearchIndex()
     * 		$this->updateSearchIndex()
     */
    function _initTraits() {
        parent::_initTraits();
        $this->_registerTrait( new ZF4_Trait_Db_Searchable() );
    }  
}
