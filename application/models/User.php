<?php

/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  User
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
 * User model
 *
 * Handles all interaction with user information
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  User
 */
class Application_Model_User extends ZF4_Db_Table_Model {
    /**
     * Role ids and names
     * 
     * These reflect those in the systRole Table
     */
    const ROLE_ID_SUPER = 1;
    const ROLE_ID_ADMIN = 2;
    const ROLE_ID_INPUT = 3;
    const ROLE_ID_USER = 4;
    const ROLE_NM_SUPER = 'Super Admin';
    const ROLE_NM_ADMIN = 'Admin';
    const ROLE_NM_INPUT = 'Inputter';
    const ROLE_NM_USER = 'User';

    /**
     * Pseudo roles - not found in systRole Table
     * but derived dynamically
     */
    const ROLE_ID_STAFF = 100;
    const ROLE_NM_STAFF = 'Staff';

    /**
     * User roles
     * @var array 
     */
    private static $_roles;

    /**
     * Constructor
     *
     *
     * @param int|string $user	User id or uName
     * @throws Application_Model_Exception_InvalidUser if invalid user identifier
     */
    public function __construct($user = null) {
        try {
            parent::__construct('systUser', 'uName', $user);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidUser();
        }
    }

    /**
     * Fetch the roles for the current user
     *
     * Add pseudo role 'staff' if user has associated staff id
     *
     * @see ZF4_Action_Helper_Checkacl
     * @return array Array of role names
     */
    public function getRoles() {
        if (empty(self::$_roles)) {
            $select = $this->getAdapter()->select()
                            ->from(array('r' => 'systRole'), array('rName'))
                            ->join(array('ur' => 'systUserRole'), 'ur.rId=r.id', array())
                            ->where('ur.uId = ?', $this->id);
            $rows = $this->getAdapter()->fetchCol($select);
            //are we staff?
            if (!empty($this->prsnId)) {
                $rows[] = self::ROLE_NM_STAFF;
            }
            self::$_roles = $rows;
        }
        return self::$_roles;
    }

    /**
     * Check if this user has a particular role
     * 
     * @param string $role
     * @return boolean
     */
    public function hasRole($role) {
        return in_array($role,  $this->getRoles());
    }

    /**
     * Inserts a new user.
     * 
     * Extends ancestor to create a new password and emails user with it
     * 
     * Manages roles for user
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
        //manage roles
        if (isset($data['role'])) {
            $roles = (is_array($data['role']) ? $data['role'] : array($data['role']));
            unset($data['role']);
        } else {
            $roles = array();
        }
        $rKey = parent::insert($data);
        if ($rKey != 0) {
            //add roles
            $this->_updateRoles($roles, $rKey);
            //create new password
            $wlcUser = new ZF4_User(intval($rKey));
            $pwd = $wlcUser->genPassword();
            $ret = $wlcUser->updatePw($pwd);
            //email the user with new password
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                            'ViewRenderer'
            );
            $view = $viewRenderer->getActionController()->view;
            $mailer = new ZF4_Mail();
            try {
                $mailer->renderMailTemplate(
                        $view,
                        'newuser.phtml',
                        array('uName' => $data['uName'],
                            'pwd' => $pwd
                        ),
                        $data['uEmail'],
                        'WLC Family Map account details'
                );
            } catch (Exception $e) {

            }
        }
        return $rKey;
    }

    /**
     * Updates existing rows.
     * 
     * Extends ancestor to deal with roles
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where) {
        //manage roles
        if (isset($data['role'])) {
            $roles = (is_array($data['role']) ? $data['role'] : array($data['role']));
            unset($data['role']);
        } else {
            $roles = false;
        }
        $ret = parent::update($data, $where);
        if ($roles !== false) {
            $isStaff = (in_array('Staff', $roles));
            if ($isStaff) array_pop ($roles); //relies on staff always being last role
            $this->_updateRoles($roles, str_replace('id=', '', $where));
            if ($isStaff) $roles[] = 'Staff';
        }
        return $ret;
    }

    /**
     * Delete user
     * OVERIDES Parent to set record as defunct instead
     *
     * @param string $where
     */
    public function delete($where) {
        $data = array('rowSts' => 'defunct');
        //use parent update so as not to worry about org id and roles
        return parent::update($data, $where);
    }

    /**
     * Add a role for this user
     *
     * @param string|int|Application_Model_Role $role  Role name, id or object
     * @return Application_Model_User	Fluent Interface
     */
    public function addRole($role) {
    	if (!$role instanceof Application_Model_Role) {
    		$role = new Application_Model_Role($role);
    	}
    	$model = new Zend_Db_Table('systUserRole');
    	$model->insert(array(
    		'uId' => $this->id,
    		'rId' => $role->id
    	));
    	return $this;
    }
    
    /**
     * Update roles for user
     *
     * @param array $roles role ids
     * @param string $uid user id
     */
    protected function _updateRoles(array $roles, $uid) {
        $uid = intval($uid);
        $model = new Zend_Db_Table('systUserRole');
        $model->delete('uId=' . $uid);
        if (empty($roles))
            return;
        //remove pseudo roles
        if (in_array('Staff', $roles))
                array_pop ($roles);
        foreach ($roles as $value) {
            if (in_array($value, array('', '&nbsp;', null)))
                continue;
            $model->insert(array(
                'uId' => $uid,
                'rId' => intval($value)
            ));
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
    public function getForSelect($name = null, array $where = array()) {
        $user = ZF4_User::getSessionIdentity();
        array_push($where, 'orgId=' . $user['orgId']);
        return parent::getForSelect($name, $where);
    }

    /**
     * Do post logon processing
     * Called by ZF4_User
     * 
     * Sets up organisation helpers
     * Sets up roles on user session details
     * 
     * @return void
     */
    public function postLogon() {
    	//set up organisation helper
        $org = new Application_Model_Org(intval($this->orgId));
        ZF4_View_Helper_Org::setOrganisation($org);
        //add roles to user session data
        $data = ZF4_User::getSessionIdentity();
        $data['roles'] = $this->getRoles();
        ZF4_User::setSessionIdentity($data);
    }
}
