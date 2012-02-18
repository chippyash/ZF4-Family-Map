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
 * STATIC Database domain object manager
 *
 * 1/ Ensures that only one copy of a database object is used to save on constant re-reading of the database
 * 2/ Manages the table metadata
 *
 * Use this manager class to instantiate your ZF4_Object_Db_Record based classes to benefit
 * from object caching
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 */
class ZF4_Object_Db_Manager_Cache {

	/**
	 * Internal object type usage
	 */
	const OBJTYPE_INT = 1;
	/**
	 * Internal object type usage
	 */
	const OBJTYPE_STR = 2;
	/**
	 * Internal object type usage
	 */
	const OBJTYPE_ARR = 3;
	/**
	 * Do not allow class to be instantiated
	 *
	 */
	protected function __construct() {}

	/**
	 * Array of objects er are already holding, indexed by their record id
	 *
	 * @var array
	 */
	private static $_objectsById = array();
	/**
	 * Pointer array to $_objectsById, indexed by name
	 *
	 * @var array
	 */#
	private static $_objectsByName = array();

	/**
	 * Pointer array to $_objectsById, indexed by name
	 *
	 * @var array
	 */
	private static $_objectsByArray = array();
	/**
	 * Message collector
	 *
	 * @var ZF4_Messenger
	 */
	private static $_messenger;
	/**
	 * Error flag
	 *
	 * @var boolean
	 */
	private static $_err = false;

	/**
	 * Get Db Record object from repository or get from database if necessary
	 *
	 * @param string $class Classname for object to get
	 * @param int|string|array|null $search id, name, search columns or null to identify the object
	 * @throws ZF4_Object_Exception if class doesn't exist
	 * @return ZF4_Object_Db_Record
	 */
	public static function getRecordObject($class, $search = null) {
		if (!class_exists($class)) {
			throw new ZF4_Object_Exception("Class '{$class}' does not exist",Zend_log::ERR );
		}
		$obj = self::_getRecordObject($class,$search);
		if ($obj !== false) {
			return $obj;
		} else {
			$obj = new $class($search);
			self::addRecordObject($obj);
			return $obj;
		}
	}

	/**
	 * Get a DB Record Object from manager repository
	 *
	 * @param string $class Classname for object to get
	 * @param int|string|array|null $search id, name, search columns or null to identify the object
	 * @return ZF4_Object_Db_Record or false if not found
	 */
	protected static function _getRecordObject($class, $search = null) {
            self::_loadCache();
		if (is_string($search)) {
			$index = "{$class}:{$search}";
			if (array_key_exists($index,self::$_objectsByName)) {
				return self::$_objectsByName[$index];
			}
		} elseif (is_int($search)) {
			$index = "{$class}:{$search}";
			if (array_key_exists($index,self::$_objectsById)) {
				return self::$_objectsById[$index];
			}
		} elseif (is_array($search)) {
			$keys = implode(":",array_keys($search));
			$values = implode(":",$search);
			$index = "{$keys}:{$values}";
			if (array_key_exists($index,self::$_objectsByArray)) {
				return self::$_objectsByArray[$index];
			}
		} elseif (is_null($search)) {
			$index = "{$class}:0";
			if (array_key_exists($index,self::$_objectsById)) {
				return self::$_objectsById[$index];
			}
		}

		//if we get here then it means we haven't returned an object yet
		// as none exists in our registry for the search key so return false to caller
		return false;
	}

	/**
	 * Add an object to indexes
	 *
	 * @param ZF4_Object_Db_Record $object Objct to add
	 */
	public static function addRecordObject(ZF4_Object_Db_Record $object) {
		$index = $object->id;
		if (is_null($index)) {
			$index = 0;
		}
		self::_addRecordObject($object,$index);
	}

	/**
	 * Do the work of adding an object to our indexes
	 *
	 * @param ZF4_Object_Db_Record $object Object to add
	 * @param int|string|array $index index to object
	 * @param int $type
	 */
	private static function _addRecordObject(ZF4_Object_Db_Record $object,$index,$type = self::OBJTYPE_INT) {
            self::_loadCache();
            $class = get_class($object);
		if ($type != self::OBJTYPE_INT ) {
			//if not identified by id then get the id from the object
			//create the int index first
			$id = $object->id;
			$intIdx = "{$class}:{$id}";
			self::$_objectsById[$intIdx] = $object;
		} else {
			//its an int type index
			$intIdx = "{$class}:{$index}";
			self::$_objectsById[$intIdx] = $object;
		}
		//process named objects
		if ($type == self::OBJTYPE_STR ) {
			self::$_objectsByName[$index] = self::$_objectsById[$intIdx];
		} else {
			//see if we can find the name for the object
			$name = $object->getNameCol();
			if (!is_null($name)) {
				if (isset($object->$name) && !is_null($object->$name)) {
					$nmIndex = "{$class}:{$object->$name}";
					self::$_objectsByName[$nmIndex] = self::$_objectsById[$intIdx];
				}
			}
		}
		//process array searched objects
		if ($type == self::OBJTYPE_ARR  ) {
			self::$_objectsByArray[$index] = self::$_objectsById[$intIdx];
		}
	}

	/**
	 * Create a new record object and add to cache
	 *
	 * @param string $class
	 * @param array $fldsArr [colname=>value,,]
	 * @param boolean $stripnulls
	 * @return Boolean|ZF4_Object_Db_Record False if could not create else the object
	 * @throws ZF4_Object_Exception if $class parameter is not a string
	 * @see ZF4_Object_Data::create()
	 */
	public static function createRecordObject($class, $fldsArr, $stripnulls = true) {
		if (!is_string($class)) {
			throw new ZF4_Object_Exception('$class parameter is not a string',E_USER_ERROR);
		}
		$obj = new $class();
		self::_clearMsg();
		$ret = $obj->create($fldsArr,$stripnulls);  //calls addRecordObject in its update()
		self::_setMsg($obj->getMsg());
		if ($ret != 1) {
			return false;
		} else {
			return $obj;
		}
	}

	/**
	 * Delete the record object from the cache if it exists in cache
	 *
	 * @param ZF4_Object_Db_Record $object
	 */
	public static function delRecordObject(ZF4_Object_Db_Record $object) {
            self::_loadCache();
		$class = get_class($object);
		$index = $object->id;
		if (is_null($index)) {
			$index = 0;
		}
		//remove name index if available
		$nmFld = $object->getNameCol();
		$nmIndex = "{$class}:{$nmFld}";
		if (!is_null($nmFld) && isset(self::$_objectsByName[$nmIndex])) {
			unset(self::$_objectsByName[$nmIndex]);
		}
		//remove object index
		$intIdx = "{$class}:{$index}";
		if (isset(self::$_objectsById[$intIdx])) {
			unset(self::$_objectsById[$intIdx]);
		}
	}

	/**
	 * Get index of stored record objects
	 *
	 * @return array
	 */
	public static function getRecordIndex() {
            self::_loadCache();
		return self::$_objectsById;
	}

	/**
	 * Flush the record objects index
	 * NB - this is a dangerous operation and will not destroy the objects
	 * that have been created.  Used only for debugging and testing
	 *
	 */
	public static function flushRecords() {
            self::_loadCache();
		self::$_objectsById = array();
		self::$_objectsByName = array();
		self::$_objectsByArray = array();
	}

	/**
	 * Does an object exist in the index?
	 *
	 * @param string $class class name
	 * @param int|string|array $search id, name or col search params for item
	 * @return boolean true if exists in index
	 */
	public static function is_record($class,$search) {
            self::_loadCache();
		if (is_null($search)) {
			return false;
		} elseif (is_string($search)) {
			$index = "{$class}:{$search}";
			return array_key_exists($index,self::$_objectsByName);
		} elseif (is_int($search)) {
			$index = "{$class}:{$search}";
			return array_key_exists($index,self::$_objectsById);
		} elseif (is_array($search)) {
			$keys = implode(":",array_keys($search));
			$values = implode(":",$search);
			$index = "{$keys}:{$values}";
			return array_key_exists($index,self::$_objectsByArray);
		}
	}

	/**
	 * Array of known table objects indexed by table name
	 *
	 * @var array
	 */
	private static $_tableIndex = array();

	/**
	 * Retrieve a table object given the table name or false if we are not holding it
	 * Not intended to be called directly.  Called from ZF4_Object_Db_Table constructor
	 *
	 * @param string $tableName Name of table
	 * @param string $recObjClass Name of record object class using the table
	 * @return false|ZF4_Object_Db_Table
	 */
	public static function getTableObject($tableName,$recObjClass) {
            self::_loadCache();
		if (self::is_table($tableName,$recObjClass)) {
			return self::$_tableIndex["{$tableName}:{$recObjClass}"];
		} else {
			return false;
		}
	}

	/**
	 * Add a tableobject to registry
	 * Not intended to be called directly.  Called from ZF4_Object_Db_Table constructor
	 *
	 * @param ZF4_Object_Db_Table $obj
	 */
	public static function addTableObject(ZF4_Object_Db_Table $obj) {
            self::_loadCache();
		$recObjClass = $obj->getRecordObject();
		$tableName = $obj->info(Zend_Db_Table_Abstract::NAME );
		$index = "{$tableName}:{$recObjClass}";
		self::$_tableIndex[$index] = $obj;
	}

	/**
	 * return the table object index array
	 *
	 * @return array
	 */
	public static function getTableIndex() {
            self::_loadCache();
		return self::$_tableIndex;
	}

/**
	 * Flush the table objects index
	 * NB - this is a dangerous operation and will not destroy the objects
	 * that have been created.  Used only for debugging and testing
	 *
	 */
	public static function flushTables() {
            self::_loadCache();
		self::$_tableIndex = array();
	}

	/**
	 * Does a table object exist for a table?
	 *
	 * @param string $tableName
	 * @return boolean
	 */
	public static function is_table($tableName,$recObjClass) {
            self::_loadCache();
		$index = "{$tableName}:{$recObjClass}";
		return array_key_exists($index,self::$_tableIndex);
	}

	/**
	 * Set a message on the messenger
	 *
	 * @param string|array $msg Messages to set
	 */
	protected static function _setMsg($msg = null) {
		if (!empty($msg)) {
			if (is_null(self::$_messenger)) {
				self::$_messenger = new ZF4_Messenger();
			}
			self::$_messenger->setMsg($msg);
			self::$_err = true;
		}
	}

	/**
	 * Clear message stack
	 *
	 */
	protected static function _clearMsg() {
		if (is_null(self::$_messenger)) {
			self::$_messenger = new ZF4_Messenger();
		}
		self::$_messenger->clearMsg();
		self::$_err = false;
	}

	/**
	 * Get messages from stack
	 *
	 * Clears message stack and error flag
	 *
	 * @return array
	 */
	public static function getMsg() {
		if (is_null(self::$_messenger)) {
			self::$_messenger = new ZF4_Messenger();
		}
		self::$_err = false;
		return self::$_messenger->getMsg();
	}

	/**
	 * Check if there are errors
	 *
	 * @return boolean True = there are errors - use getMsg()
	 */
	public static function isError() {
		return self::$_err;
	}

/**
 * Caching
 */
        /**
         * Is the cache loaded?
         *
         * @var boolean
         */
        protected static $_isCacheLoaded = false;

        /**
         *
         * @var boolean
         */
        protected static $_doSaveCache = false;

        /**
         * Load the cache
         */
        protected static function _loadCache() {
            if (!self::$_isCacheLoaded) {
                $cacheFile = ZF4_Defines::dirCache('dbObject') . 'dbObject.cache';
                self::$_isCacheLoaded = true;
                if (!file_exists($cacheFile)) {
                    return;
                }
                $cacheObj = unserialize(file_get_contents($cacheFile));
                self::$_objectsByArray = $cacheObj[0];
                self::$_objectsById = $cacheObj[1];
                self::$_objectsByName = $cacheObj[2];
                self::$_tableIndex = $cacheObj[3];
                //register the saveCache function for shutdown processing
                register_shutdown_function(array('ZF4_Object_Db_Manager','_saveCache'));
            }
        }

        /**
         * Save the cache
         *
         * @access private
         */
        public static function _saveCache() {
            if (self::$_isCacheLoaded) {
                $cacheFile = ZF4_Defines::dirCache('dbObject') . 'dbObject.cache';
                $cacheObj = array(
                    self::$_objectsByArray,
                    self::$_objectsById,
                    self::$_objectsByName,
                    self::$_tableIndex
                );
                file_put_contents($cacheFile, serialize($cacheObj));
                self::$_isCacheLoaded = false;
            }
        }
}