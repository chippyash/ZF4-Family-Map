<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Usage
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
 * Organisation model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Usage
 */
class Application_Model_Usage extends ZF4_Db_Table_Model {

    /**
     * Constructor
     *
     *
     * @param int $id	Usage record id
     * @throws Application_Model_Exception_InvalidRecord if invalid record id
     * identifier
     */
    public function __construct($id = null) {
        try {
            parent::__construct('usage',null, $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidRecord();
        }
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
     * Batch save of usage record input
     *
     * @param int $batch  Number of records in the batch
     * @param Zend_Date $dt Date of batch
     * @param array $members Array of member ids
     * @param array $services Array of service ids
     * @return True|array of error messages indexed by location in member/service arrays
     */
    public static function saveRecords($batch, Zend_Date $dt, array $members, array $services) {
    	$user = ZF4_User::getSessionIdentity();
    	$orgId = $user['orgId'];
    	$dt = $dt->get(Zend_Date::ISO_8601 );
    	$usage = new Application_Model_Usage();
    	$msg = array();
    	for ($x=0;$x<$batch;$x++) {
    		try {
	    		$usage->insert(array(
	    			'prsnId' => $members[$x],
	    			'srvcId' => $services[$x],
	    			'uDate'	 => $dt,
	    			'orgId'	 => $orgId
	    		));
    		} catch (Exception $e) {
    			$msg[$x] = array(
    				'mId' => $members[$x],
    				'sId' => $services[$x],
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
