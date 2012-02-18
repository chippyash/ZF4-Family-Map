<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Organisation
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
 * @subpackage  Organisation
 */
class Application_Model_Org extends ZF4_Db_Table_Model {

    protected $_hasOrg = false; //it is the organisation!
    
    /**
     * Constructor
     *
     *
     * @param int|string $id	Organisation id or tag
     * @throws Application_Model_Exception_InvalidOrg if invalid organisation
     * identifier
     */
    public function __construct($id = null) {
        try {
            parent::__construct('org','tag', $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidOrg();
        }
    }

    /**
     * Inserts a new organisation
     * 
     * Extends ancestor to handle the organisation admin user
     * Adds a unique key for person encryption
     * 
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
    	//strip out admin user details
    	if (isset($data['uName'])) {
	    	$admin = array(
	    		'uName' => $data['uName'],
	    		'uEmail' => $data['uEmail'],
	    		'payrollId' => $data['payrollId']
	    	);
	    	unset($data['uName']);unset($data['uEmail']);
	    	unset($data['payrollId']);
    	} else {
    		$admin = false;
    	}
    	//add the organisation encryption key
    	$data['enckey'] = uniqid($data['tag'],true);
    	
    	$rKey = parent::insert($data);
    	if ($rKey != 0 && $admin !== false) {
    		$admin['orgId'] = $rKey;
    		$admin['role'] = Application_Model_User::ROLE_ID_ADMIN;
    		$user = new Application_Model_User();
    		$uid = $user->insert($admin);
    	}
    	return $rKey;
    }

    /**
     * Updates existing rows.
     * 
     * Extends ancestor to deal with adminuser for the organisation
     * removes reference to enckey as we never want this updating
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where) {
		if (isset($data['uName'])) {
	    	unset($data['uName']);unset($data['uEmail']);
	    	unset($data['payrollId']);
		} else {
			$admin = false;
		}
		//remove encryption key - it must never be updated
		if (isset($data['enckey'])) unset($data['enckey']);
		
    	$ret = parent::update($data, $where);	
    	return $ret;
    }

}
