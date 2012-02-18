<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Reltype
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
 * Relationship Type model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Reltype
 */
class Application_Model_Reltype extends ZF4_Db_Table_Model {

	/**
	 * Constructor
	 *
	 *
	 * @param int|string $id	Reltype id or name
	 * @throws Application_Model_Exception_InvalidReltype if invalid Reltype
	 */
	public function __construct($id = null) {
		$id = (is_int($id) && $id == 0 ? null : $id);  //trap zero index relationship type
		try {
			parent::__construct('relType','name', $id);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidReltype();
		}
	}
	
    
	/**
	 * Relationship types
	 * Get id->someNameColumn for use in form selectors
	 *
	 * If the object has a rowSts field then only active rows are returned
	 * 
	 * Extends ancestor to ensure only current organisation
	 *
	 * @param string $nameCol name column to use - default is the unique column for the table
	 * @param array $where additional where clauses
	 * @return array
	 */
    public function getForSelect($name = null,array $where = array()) {
    	$user = ZF4_User::getSessionIdentity();
    	array_push($where,'orgId=' . $user['orgId']);
    	return parent::getForSelect($name,$where);
    }    
    
    /**
     * Returns the set of allowable relationship types for a person
     * 
     * This will return an array [Fn|Bn => name] where F prefix depicts
     * a forward relationship type and B prefix is a backward relationship 
     * type
     *
     * @param int|Application_Model_Person $uid
     */
    public function getForSelectForPerson($uid) {
    	//get the person type mask to use in selection
    	if (!$uid instanceof Application_Model_Person ) {
    		$person = new Application_Model_Person(intval($uid));
    	} else {
    		$person = $uid;
    	}
    	$mask = $person->getTypeMask();
    	
    	//create the select - forward relationship types
    	$select = $this->select()
    			->from('relType',array('id'=> new Zend_Db_Expr("concat('F',id)"),'name'))
    			->where('orgId=?',intval($person->orgId))
    			->where(new Zend_Db_Expr("bin(headType+0 & {$mask})"));
  $sql = (string) $select;
    	$fRows = $this->fetchAll($select)->toArray();
    	//create the select - backward relationship types
    	$select = $this->select()
    			->from('relType',array('id'=> new Zend_Db_Expr("concat('B',id)"),'name'=>'revName'))
    			->where('orgId=?',intval($person->orgId))
    			->where(new Zend_Db_Expr("bin(tailType+0 & {$mask})"))
    			->where('direction=?','one-way');
    	$bRows = $this->fetchAll($select)->toArray();
    	
    	//combine and normalise
    	$rows = array_merge($fRows,$bRows);
    	$retArr = array();
    	foreach ($rows as $row) {
    		$retArr[$row['id']] = $row['name'];
    	}
    	return $retArr;
    }

    /**
     * get a select list of people that can fulfil one side of a relationship
     *
     * @param char $direction F|B Forward or backward
     * @return array [id=>concat(uid,style,fName,lName)]
     */
    public function getPeopleForType($direction) {
    	$flag = ($direction == 'F' ? false : true);
    	$mask = $this->getMask($flag);
    	$person = new Application_Model_Person();
    	$rows = $person->getForSelect(
    		"concat_ws(' ',uid,style,fName,lName)",
    		array("bin(pType+0 & {$mask})")
    	);
    	return $rows;
    }
    
    /**
     * return the person type mask for head or tail types for
     * this relationship type
     *
     * @param boolean $head If true then return headType mask else return tailType mask
     * @return int
     */
    public function getMask($head = true) {
    	if ($head) {
    		$pTypes = explode(',',$this->headType);
    	} else {
    		$pTypes = explode(',',$this->tailType);
    	}
    	$person = new Application_Model_Person();
    	$baseTypes = $person->getValidTypes(false);
		$valMask = '';
		foreach ($baseTypes as $nm) {
			$valMask = (in_array($nm, $pTypes) ? '1' : '0') . $valMask;
		}
		return bindec($valMask);    	
    }
}
