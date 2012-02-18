<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Enrolled
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
 * Records members enrolled for a service
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Enrolled
 */
class Application_Model_Enrolled extends ZF4_Db_Table_Model {
    /**
     * Constructor
     *
     *
     * @param int $id	enrollment record id
     * @throws Application_Model_Exception_InvalidRecord if invalid record id
     * identifier
     */
    public function __construct($id = null) {
        try {
            parent::__construct('enrolled',null, $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidRecord();
        }
    }

    /**
     * Inserts a new row.
     * 
     * Extends ancestor to ensure enrollment date is set
     * 
     * Check to see if already enrolled or waiting (they can have a past record)
     * Check to see if member can be enrolled or goes on waiting list
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {

		if (!isset($data['orgId'])) {
			$user = ZF4_User::getSessionIdentity();
			$data['orgId'] = $user['orgId'];
		}
		if (!isset($data['eDate']) || empty($data['eDate'])) {
			$data['eDate'] = ZF4_Date::now()->get(Zend_Date::ISO_8601 );
		}
		if (isset($data['g'])) unset($data['g']);
		
		//check to see if already enrolled or waiting
		$select = $this->select()
					->from('enrolled',array('cnt'=>new Zend_Db_Expr('count(*)')))
					->where('orgId=?',intval($data['orgId']))
					->where('srvcId=?',intval($data['srvcId']))
					->where('prsnId=?',intval($data['prsnId']))
					->where('status!=?','past');
		$row = $this->fetchRow($select);
		$cnt = intval($row['cnt']);
		if ($cnt != 0) {
			return 0;
		}
		
		//if limit == -1 then enroll
		$service= new Application_Model_Service(intval($data['srvcId']));
		$limit = intval($service->eLimit);
		if ($limit == -1) {
			$data['status'] = 'enrolled';
		} else {
			//check waiting list
			$select = $this->select()
					->from('enrolled',array('cnt'=>new Zend_Db_Expr('count(*)')))
					->where('orgId=?',intval($data['orgId']))
					->where('srvcId=?',intval($data['srvcId']))
					->where('status=?','enrolled');
			$row = $this->fetchRow($select);
			$cnt = intval($row['cnt']);
			
			if ($cnt >= $limit) {
				$data['status'] = 'waiting';
			} else {
				$data['status'] = 'enrolled';
			}
		}
    	return parent::insert($data);	
    }	    
    
    /**
     * Overide the delete process to
     * a/ set status as 'past'
     * b/ see if there are any waiting members that can be enrolled
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows effected
     */
    public function delete($where) {
    	$parts = explode('=',$where);
    	$id = intval($parts[1]);
    	$enrollment = new Application_Model_Enrolled($id);
    	//don't delete - just set the enrollment as past and set leave date
    	$ret = parent::update(
    		array(
    			'status'=>'past',
    			'lDate' => ZF4_Date::now()->get(Zend_Date::ISO_8601 )
    			), 
    		$where
    	);	
    	
    	//if deleted user was enrolled - see if there is a waiting list
    	if ($enrollment->status == 'enrolled') {
    		//select first person waiting - by enrollment date
    		$select = $enrollment->select()
    				->from('enrolled',array('id'))
    				->where('orgId=?',intval($enrollment->orgId))
					->where('srvcId=?',intval($enrollment->srvcId))
					->where('status=?','waiting')
					->order('eDate','asc');
			$row = $enrollment->fetchRow($select);
			if ($row !== null) {
				$enrollment->update(array('status'=>'enrolled'),'id='.$row['id']);
			}
    	}
    	return $ret;
    }
    
	/**
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
     * Returns an options selector for services that have enrollments
     *
     * @return array
     */
    public function getEnrolledServices() {
    	$user = ZF4_User::getSessionIdentity();
    	$select = $this->select()
    			->from(array('e'=>'enrolled'),array())
    			->setIntegrityCheck(false)
    			->distinct()
    			->join(array('s'=>'service'),'e.srvcId = s.id',array('id','name'))
    			->where('e.orgId=?',intval($user['orgId']));
    	$rows = $this->fetchAll($select);
    	$ret = array();
    	foreach ($rows as $row) {
    		$ret[$row['id']] = $row['name'];
    	}
    	return $ret;
    }
    
    /**
     * get enrollment status for a particular member
     * This returns a list of all enrollable services (available to member)
     * With a status set to 'enrolled'|'waiting'|'past'|null
     *
     * @param Application_Model_Person $person
     * @return array [srvcId, srvcName, status]
     */
    public static function getEnrollments(Application_Model_Person $person) {
    	//first get all enrollable services
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$select = $db->select()
    		->from(array('s'=>'service'),
    			array('id',
    				'name' => 'desc',
    				'eLimit',
    				'status' => new Zend_Db_Expr('null'),
    				'extInfo'
    			))
    		->where("s.enrolType = 'member' or s.enrolType = 'any'")
    		->where('orgId=?',intval($person->orgId));
    	$rows = $db->fetchAll($select);
    	$ret = array();
    	foreach ($rows as $row) {
    		$ret[$row['id']] = $row;
    	}
    	//now get services that member is enrolled in
    	$select = $db->select()
    		->from(array('s'=>'service'),
    			array(
    				'id',
    				'name' => 'desc',
    				'eLimit',
    				'extInfo'
    			)
    		  )
    		->join(array('e'=>'enrolled'),'e.srvcId = s.id',array('status'))
    		->where('s.orgId=?',intval($person->orgId))
    		->where('e.prsnId=?',intval($person->id))
    		->where("s.enrolType = 'member' or s.enrolType = 'any'");
    	$rows = $db->fetchAll($select);
    	foreach ($rows as $row) { //this will overwrite previous null rows
    		$ret[$row['id']] = $row;
    	}
    	return $ret;
    }
    
    /**
     * Get all non enrollable services that member has been enrolled on
     * plus all 'free' services that member has used
     * With a status set to 'enrolled'|'waiting'|'past'|user|null
     *
     * @param Application_Model_Person $person
     * @return array [srvcId, srvcName, status]
     */
    public static function getOtherServices(Application_Model_Person $person) {

    	//first get all services of type 'admin' or 'staff'
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$select = $db->select()
    		->from(array('s'=>'service'),
    			array('id',
    				'name' => 'desc',
    				'eLimit',
    				'status' => new Zend_Db_Expr('null'),
    				'enrolType',
    				'extInfo'
    			))
    		->where("s.enrolType = 'admin' or s.enrolType = 'staff'")
    		->where('orgId=?',intval($person->orgId));
    	$rows = $db->fetchAll($select);
    	$ret = array();
    	foreach ($rows as $row) {
    		$ret[$row['id']] = $row;
    	}
    	//now get services that user is enrolled in
    	$select = $db->select()
    		->from(array('s'=>'service'),
    			array(
    				'id',
    				'name' => 'desc',
    				'eLimit',
    				'enrolType',
    				'extInfo'
    			)
    		  )
    		->join(array('e'=>'enrolled'),'e.srvcId = s.id',array('status'))
    		->where('s.orgId=?',intval($person->orgId))
    		->where('e.prsnId=?',intval($person->id))
    		->where("s.enrolType = 'admin' or s.enrolType = 'staff'");
    	$rows = $db->fetchAll($select);
    	foreach ($rows as $row) { //this will overwrite previous null rows
    		$ret[$row['id']] = $row;
    	}
    	//fetch a list of all services
    	$select = $db->select()
    		->from(array('s'=>'service'),
    			array(
    				'id',
    				'name' => 'desc',
    				'eLimit',
    				'status'=>new Zend_Db_Expr('null'),
    				'enrolType',
    				'extInfo'
    			)
    		  )
    		->where('s.orgId=?',intval($person->orgId));
    	$rows = $db->fetchAll($select);
    	$tRet = array();
    	foreach ($rows as $row) {
    		$tRet[$row['id']] = $row;
    	}
    	//remove services that we have already dealt with
    	$tRet = array_diff_key($tRet,$ret);
    	//now fetch a list of all 'free' services that a user has used
    	$select = $db->select()
    		->distinct()
    		->from(array('s'=>'service'),
    			array(
    				'id',
    				'name' => 'desc',
    				'eLimit',
    				'status'=>new Zend_Db_Expr('"user"'),
    				'enrolType',
    				'extInfo'
    			)
    		  )
    		 ->join(array('u'=>'usage'),'u.srvcId=s.id',array())
    		->where('s.orgId=?',intval($person->orgId))
    		->where('u.prsnId=?',intval($person->id))
    		->where("s.enrolType = 'free'");

    	$rows = $db->fetchAll($select);
    	foreach ($rows as $row) { //this will overide existing rows
    		$tRet[$row['id']] = $row;
    	}
    	$ret += $tRet; //add sets together
    	return $ret;    	
    }
    
    /**
     * Batch save of enrollment record input
     *
     * @param int $batch  Number of records in the batch
     * @param Zend_Date $dt Enrollment date
     * @param array $members Array of member ids
     * @param int $service Service Id being enrolled
     * @return True|array of error messages indexed by location in member/service arrays
     */
    public static function saveRecords($batch, Zend_Date $dt, array $members, $service) {
    	$user = ZF4_User::getSessionIdentity();
    	$orgId = $user['orgId'];
    	$dt = $dt->get(Zend_Date::ISO_8601 );
    	$usage = new Application_Model_Enrolled();
    	$msg = array();
    	for ($x=0;$x<$batch;$x++) {
    		try {
	    		$usage->insert(array(
	    			'prsnId' => $members[$x],
	    			'srvcId' => $service,
	    			'eDate'	 => $dt,
	    			'orgId'	 => $orgId
	    		));
    		} catch (Exception $e) {
    			$msg[$x] = array(
    				'mId' => $members[$x],
    				'sId' => $service,
    				'msg' => $e->getMessage()
    			);
    		}
    	}
    	if (count($msg) == 0) {
    		return true;
    	} else {
    		return $msg;
    	}
    }
}
