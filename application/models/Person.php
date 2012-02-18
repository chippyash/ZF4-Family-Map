<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Person
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * Person model
 *
 * Handles all interaction with person information
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Person
 */
class Application_Model_Person extends ZF4_Db_Table_Model {
	
	/**
	 * Base people types against which valid and invalid types are masked
	 *
	 * @var array
	 */
	private $_baseTypes = array('member','pupil','staff','doctor','health visitor','carer');
	/**
	 * Valid types for a person
	 * These MUST be in the same order as the pType SET declaration in the person table
	 * 
	 * Overide in ancestor
	 *
	 * @var array
	 */
	protected $_validTypes = array('member','pupil','staff','doctor','health visitor','carer');
	/**
	 * Bitmask for valid types;
	 *
	 * @var binary (int)
	 */
	private $_validmask = 0;
	
	/**
	 * Invalid types for a person
	 *
	 * Overide in ancestor
	 * 
	 * @var array
	 */
	protected $_invalidTypes = array();
	/**
	 * Bitmask for invalid types
	 *
	 * @var binary (int)
	 */
	private $_invalidMask = 0;
	
	/**
	 * Encrypted fields
	 *
	 * @var array
	 */
	//protected $_encFlds = array('fName','lName','hNum');
		
	/**
	 * Age range delimiters
	 * 
	 * If you change this you also need to change the DB stored procedure
	 * setAgeRange()
	 *
	 * @var array
	 */
	protected static $_ageRange = array(
		'A' => array(
			'start' => 0,
			'end'   => 5,
			'label' => '0 to 5'
		),
		'B' => array(
			'start' => 6,
			'end'   => 11,
			'label' => '6 to 11'
		),
		'C' => array(
			'start' => 12,
			'end'   => 19,
			'label' => '12 to 19'
		),
		'D' => array(
			'start' => 20,
			'end'   => 25,
			'label' => '20 to 25'
		),
		'E' => array(
			'start' => 26,
			'end'   => 200,
			'label' => 'over 25'
		)
	);
	/**
	 * Constructor
	 *
	 *
	 * @param int|string $user	User id or uName
	 * @throws Application_Model_Exception_InvalidUser if invalid user identifier
	 * @throws ZF4_Db_Table_Exception_NoSalt if encryption key not found
	 */
	public function __construct($user = null) {
		try {
			$sess = new Zend_Session_Namespace(ZF4_User::SESS_KEY_USER );
			$this->_encSalt = $sess->enckey;
		} catch (Exception $e) {
			throw new ZF4_Db_Table_Exception_NoSalt();
		}
		try {
			parent::__construct('person','uid', $user,false);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidPerson();
		}
		$this->_setMasks();
	}

	/**
	 * Set the valid and invalid bitmasks
	 *
	 */
	protected function _setMasks() {
		//set the valid and invalid masks
		$valMask = '';
		$invalMask = '';
		foreach ($this->_baseTypes as $nm) {
			$valMask = (in_array($nm, $this->_validTypes) ? '1' : '0') . $valMask;
			$invalMask = (in_array($nm, $this->_invalidTypes) ? '1' : '0') . $invalMask;
		}
		$this->_validMask = bindec($valMask);
		$this->_invalidMask = bindec($invalMask);
	}
	
	/**
	 * Return the valid set member mask
	 *
	 * @return int
	 */
	public function getValidMask() {
		return $this->_validMask;
	}
	
	/**
	 * Return the invalid set member mask
	 *
	 * @return int
	 */
	public function getInvalidMask() {
		return $this->_invalidMask;
	}
	
	/**
	 * return the person type mask for this person
	 *
	 */
	public function getTypeMask() {
		$valMask = '';
		$pTypes = explode(',',$this->pType);
		foreach ($this->_baseTypes as $nm) {
			$valMask = (in_array($nm, $pTypes) ? '1' : '0') . $valMask;
		}
		return bindec($valMask);
	}
	
	/**
	 * Return the categories for this person
	 *
	 * @return array
	 */
	public function getCategories() {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$select = $db->select()
					 ->from(array('pc'=>'person_cat'),array())
					 ->where('prsnId=?',$this->id)
					 ->join(array('c'=>'cat'),'pc.catId=c.id',array('id'=>'c.id','name'=>'c.name'));
		$rows = $db->fetchAll($select);
		return $rows;
	}

	protected static $_geodata;
	protected function getGeoModel() {
		if (!isset(self::$_geodata)) {
			self::$_geodata = new Application_Model_Geodata();
		}
		return self::$_geodata;
	}
    /**
     * Inserts a new row.
     * 
     * Extends ancestor to ensure various fields are set
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
		//set an age if date of birth is available
		if (isset($data['dob'])) {
			$data['age'] = self::age($data['dob']);
			$data['ageRange'] = self::getAgeRange($data['age']);
		}
		//add geoData id
		if (isset($data['hNum']) && isset($data['pCode'])) {
			try {
				$location = $this->getGeoModel()->fetchByAddress($data['hNum'],$data['pCode']);
				$data['geoId'] = $location->id;
			} catch (ZF4_Db_Table_Exception_InvalidId $e) {
				//record does not exist
				try {
					$locId = $this->getGeoModel()->insert(array('hNum'=>$data['hNum'],'pCode'=>$data['pCode']));
					if ($locId !== 0) {
						$data['geoId'] = $locId;
						//as it's new - set the google location if available
						$geo = new Application_Model_Geodata(intval($locId));
						if (!$geo->setLocation()) {
							//blank the id as it is not found
							$data['geoId'] = null;
						}
					}
				} catch (Exception $e) {
					//do nothing - There is an error so ignore
				}
			}
		}
    	return parent::insert($data);	
    }	
    
    /**
     * Update record
     * Extends ancestor to handle age, ageRange amd geoId fields
     *
     * @param array $data
     * @param string|array $where
     * @return int
     */
    public function update(array $data,$where) {
		//set an age if date of birth is available
		if (isset($data['dob'])) {
			$data['age'] = self::age($data['dob']);
			$data['ageRange'] = self::getAgeRange($data['age']);
		}
    	$ret = parent::update($data,$where);
    	if ($ret != 0) {
    		// the geoId is never going to be part of an update so we must fetch the updated record
    		$rec = new Application_Model_Person(intval(str_replace('id=','',$where)));
    		if (!isset($rec->geoId) && isset($rec->hNum) && isset($rec->pCode)) {
    			//update the geo data
				try {
					$location = $this->getGeoModel()->fetchByAddress($rec->hNum,$rec->pCode);
					parent::update(array('geoId',$location->id),$where);
				} catch (ZF4_Db_Table_Exception_InvalidId $e) {
					//record does not exist
					try {
						$locId = $this->getGeoModel()->insert(array('hNum'=>$data['hNum'],'pCode'=>$data['pCode']));
						if ($locId !== 0) {
							parent::update(array('geoId',$locId),$where);
							//as it's new - set the google location if available
							$this->getGeoModel()->find(intval($locId))->setLocation();
						}
					} catch (Exception $e) {
						//do nothing - There is an error so ignore
					}
				}
    		}
    	}
    	
    	return $ret;
    }
	/**
	 * Get id->someNameColumn for use in form selectors
	 *
	 * Extends ancestor to ensure only current organisation and valid person types
	 *
	 * @param string $nameCol name column to use - default is the unique column for the table
	 * @param array $where additional where clauses
	 * @return array
	 */
    public function getForSelect($name = null,array $where = array()) {
    	$user = ZF4_User::getSessionIdentity();
    	array_push($where,'orgId=' . $user['orgId']);
    	$mask = $this->getValidMask();
    	array_push($where,"bin(pType+0 & {$mask})");
    	$ret = parent::getForSelect($name,$where);
    	asort($ret);
    	return $ret;
    }
    
    /**
     * return an age selector array
     *
     * @return array
     */
    public static function getAgeSelector() {
    	$ret = array();
    	foreach (self::$_ageRange as $key=>$def) {
    		$ret[$key] = $def['label'];
    	}
    	return $ret;
    }
    
    /**
     * Get age range code given an age
     *
     * @param int $age
     * @return char
     */
    public static function getAgeRange($age) {
    	$age = intval($age);
    	$ret = null;
    	foreach (self::$_ageRange as $key=>$def) {
    		if ($age >= $def['start'] && $age <= $def['end']) {
    			$ret = $key;
    			break;
    		}
    	}
    	return $ret;
    }
    /**
     * Given a date, compute the age of the customer from today
     * Returns the whole number year age
     *
     * @param Zend_Date|string $dob Date of birth
     * @return int
     */
    public static function age($dob) {
    	if (is_string($dob)) {
    		$dob = new Zend_Date($dob);
    	} elseif (!$dob instanceof Zend_Date) {
    		throw new ZF4_Exception('Invalid Date parameter');
    	}
    	$today = new Zend_Date();
    	$age = $today->get(Zend_Date::YEAR ) - $dob->get(Zend_Date::YEAR );
    	return intval($age);
    }
    
	/**
	 * returns an array of language codes => language names
	 *
	 * @return array [langCode=>langName]
	 */
	public static function getLanguages() {
		$list = Zend_Registry::get('Zend_Locale')->getTranslationList('language');
		return $list;
	}
	    
	/**
	 * Check for valid types
	 *
	 * @param array|string $type
	 * @return string
	 */
	protected function _checkType($type = null) {
    	if (isset($type)) {
    		if (!is_array($type)) {
    			$type = explode(',',$type);
    		}
    		$diff = array_diff($this->_validTypes,$type);
    		if (in_array($diff,$this->_invalidTypes)) {
    			throw new Application_Model_Exception_InvalidAncillary();
    		}
    	} else {
    		throw new Application_Model_Exception_InvalidAncillary();
    	}
		return implode(',',$type);
	}
	
	/**
	 * Return valid types
	 *
	 * @param boolean $asString
	 * @return string|array
	 */
	public function getValidTypes($asString = true) {
		if ($asString) {
			$vt = $this->_validTypes;
			foreach ($vt as &$value) {
				$value = "'{$value}'";
			}
			return implode(',',$vt);
		} else {
			return $this->_validTypes;
		}
	}
	
	/**
	 * Retrieve the relationships for this person
	 *
	 * @return array
	 */
	public function getRelationships() {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		//use straight sql 'cus ZF is crap at doing unions
		$sql = "SELECT DISTINCT `r`.`id`, `r`.`prsnIdA`, `r`.`prsnIdB`, `r`.`relTypeId`, 'forward' AS `direction`, `t`.`direction` AS `relDir`, `t`.`name`, `t`.`relColour`, `t`.`relValue` "
		. "FROM `relation` AS `r`"
		. "INNER JOIN `relType` AS `t` ON t.id=r.relTypeId "
		. "WHERE (r.prsnIdA='{$this->id}') "
		. "UNION SELECT `r`.`id`, `r`.`prsnIdA`, `r`.`prsnIdB`, `r`.`relTypeId`, 'backward' AS `direction`, `t`.`direction` AS `relDir`, `t`.`revName` AS `name`, `t`.`relColour`, `t`.`relValue` "
		. "FROM `relation` AS `r`"
		. "INNER JOIN `relType` AS `t` ON t.id=r.relTypeId "
		. "WHERE (r.prsnIdB='{$this->id}')"
		. "AND (t.direction != 'two-way')";
 				
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	/**
	 * Return array of service data that this person is enrolled into
	 *
	 * @return array Array of enrollments [enrollId=>[enrollment and service data]]
	 */
	public function getEnrollments() {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$select = $db->select()
				->from(array('e'=>'enrolled'))
				->join(array('s'=>'service'),'s.id=e.srvcId',array('staffId','name','desc','enrolType','eLimit'))
				->where('e.prsnId=>', intval($this->id))
				->where('e.orgId=?',intval($this->orgId))
				->where('s.orgId=?',intval($this->orgId));
		$rows = $db->fetchAll($select);
		$ret = array();
    	foreach ($rows as $row) {
    		$ret[$row['id']] = $row;
    	}
		return $ret;
	}
	
	/**
	 * Get an enrollment for a service
	 *
	 * @param int $srvcId Service Id
	 * @param string $status enrollment status
	 * @return Application_Model_Enrolled|null
	 */
	public function getEnrollment($srvcId,$status='enrolled') {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$select = $db->select()
				->from('enrolled')
				->where('prsnId=?', intval($this->id))
				->where('orgId=?',intval($this->orgId))
				->where('srvcId=?',intval($srvcId))
				->where('status=?',$status);
		$row = $db->fetchRow($select);
		if (null != $row) {
			return new Application_Model_Enrolled(intval($row['id']));
		} else {
			return null;
		}
	}
}
