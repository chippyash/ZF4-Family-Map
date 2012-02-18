<?php

/**
 * ZF4 Library
 *
 * Common user functionality
 *
 * @category	ZF4
 * @package 	User
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
 * Common User functionality
 *
 * <p>Handles common interaction with user information and authentication</p>
 * <p>Works in conjunction with the application specific User model.  The user model
 * must be extended from ZF4_Db_Table_Model and have the following data fields in its
 * underlying table</p>
 * <ul>
 * <li>id - int(11) unsigned - the primary key</li>
 * <li>uName - varchar(30) unique index - user name</li>
 * <li>uPwd - char(32) - encrypted password</li>
 * <li>lastLogon - datetime - date and time of last logon</li>
 * <li>rowSts - enum('active','suspended','defunct') - row status</li>
 * </ul>
 *
 * <p>The user model may additionally have a method called getRoles()
 * which returns an array of role names that a user belongs to.  This functionality is
 * used by the ZF4_Action_Helper_Checkacl helper if employed.
 *
 * @category	ZF4
 * @package 	User
 */
class ZF4_User {
    /**
     * Session namespace for user details
     *
     * This is used as the key to use details held in the session for logged on users
     */

    const SESS_KEY_USER = 'zf4user';
    /**
     * Registry key for user model to use
     * @see self::setUserModel()
     */
    const REG_KEY_MODEL = 'zf4user_model';

    /**
     * Registry key for user model crpto seed
     * @see self::setCryptSeed()
     */
    const REG_KEY_CRYPT = 'zf4user_cryptseed';

    /**
     * Primary key field in user model
     */
    const FLD_PRIMARY = 'id';
    /**
     * Unique user name field in user model
     */
    const FLD_IDENTITY = 'uName';
    /**
     * Password field in user model
     */
    const FLD_CREDENTIAL = 'uPwd';
    /**
     * Row status field in user model
     */
    const FLD_STATUS = 'rowSts';
    /**
     * Field used to record last logon datetime
     */
    const FLD_LASTLOGON = 'lastLogon';
    /**
     * Field used to record last logo IP address
     */
    const FLD_LASTIP = 'lastIP';

    /**
     * The model connecting to the database user table
     *
     * The table should have at least the following fields;
     * id - unique internal id - primary key int(11) unsigned
     * uName - user name - usually unique varchar
     * uPwd - user password varchar
     * rowSts - row status enum('active','suspended','defunct')
     *
     * @var Zend_Db_Table_Abstract
     */
    private $_model;

    /**
     * Authenticator
     *
     * @var Zend_Auth
     */
    private $_auth;

    /**
     * The data for an object constructed with a user identifier
     *
     * This contains the db record data except for uPwd which is erased
     *
     * @var array
     */
    public $data = array();

    /**
     * credential hashing treatment
     *
     * @var string
     */
    private $_credTreatment = 'md5(concat("%s",?))';

    /**
     * Default crypt key for hashing user password if none supplied
     * @var string 
     */
    private $_defCryptKey = 'f438dcac-45f2-4b1c-9e3d-a301a0dad4fb';

    /**
     * Constructor
     *
     * If a null user is passed in, the object will construct trying to use the
     * current logged on user
     *
     * @param int|string $user	User id or uName
     * @param string $model The user data model name. If null use the default model
     * 						set by self::setuserModel()
     * @param string $cryptSeed     Crypto seed string.  If null use the default
     *                              string set by self::setCryptSeed()
     * @throws ZF4_User_Exception if no model is found
     */
    public function __construct($user = null, $model = null, $cryptSeed = null) {
        //set the authenticator
        $this->_auth = Zend_Auth::getInstance();
        //set credential treatment
        if (empty($cryptSeed)) {
            if (Zend_Registry::isRegistered(self::REG_KEY_CRYPT)) {
                $cryptSeed = Zend_Registry::get(self::REG_KEY_CRYPT);
            } else {
                $cryptSeed = $this->_defCryptKey;
            }
        }
        $this->_credTreatment = sprintf($this->_credTreatment, $cryptSeed);
        //check user
        if (null == $user) {
            if ($this->_auth->hasIdentity()) {
                $user = $this->_auth->getIdentity();
            }
        }
        //create the user model
        if (!is_null($model)) {
            try {
                $this->_model = new $model($user);
            } catch (Exception $e) {
                throw new ZF4_User_Exception('Invalid model for user class');
            }
        } else {
            if (Zend_Registry::isRegistered(self::REG_KEY_MODEL)) {
                $model = Zend_Registry::get(self::REG_KEY_MODEL);
                $this->_model = new $model($user);
            } else {
                throw new ZF4_User_Exception('Invalid model for user class');
            }
        }

        //transfer the user data if user is found
        if (!empty($this->_model->id)) {
            $data = $this->_model->getRecordData();
            unset($data[self::FLD_CREDENTIAL]); //don't need to carry password hash around
        } else {
            //empty data
            $data = array();
        }
        //transfer data to public parameter
        $this->data = $data;
    }

    /**
     * Set the default user model to use with this class
     *
     * @param string $modelName
     */
    public static function setUserModel($modelName) {
        Zend_Registry::set(self::REG_KEY_MODEL, $modelName);
    }

    /**
     * Set the crypt key to use for generating passwords
     * 
     * @param string $seed 
     */
    public static function setCryptSeed($seed) {
        Zend_Registry::set(self::REG_KEY_CRYPT, $seed);
    }

    /**
     * Is the user valid?
     *
     * Tests to see if there is any user data
     *
     * @return boolean
     */
    public function isValid() {
        return !empty($this->data);
    }

    /**
     * Get the user data model
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getModel() {
        return $this->_model;
    }

    /**
     * Return the authenticator
     *
     * @return Zend_Auth
     */
    public function getAuthenticator() {
        return $this->_auth;
    }

    /** PASSWORD MANIPULATION * */

    /**
     * Generate a password
     *
     * @param int $length	password length
     * @return string		The plain text password
     */
    public function genPassword($length = 8) {
        // start with a blank password
        $password = "";

        // define possible characters
        $possible = '0123456789abcdfghjkmnpqrstvwxyzABCDEFGHJKLMNOPQRSTVWXYZ';

        // set up a counter
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {

            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);

            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        // done!
        return $password;
    }

    /**
     * Update the user's password
     *
     * @param string $newpassword	Plain text password
     * @return ZF4_User Fluent Interface
     * @throws ZF4_User_Exception If invalid user model
     */
    public function updatePw($newpassword) {
        //check that we have a valid current user
        if (empty($this->_model->id)) {
            throw new ZF4_User_Exception('Invalid user model');
        }
        $cred = str_replace('?', "'{$newpassword}'", $this->_credTreatment);
        $data = array(
            self::FLD_CREDENTIAL => new Zend_Db_Expr($cred)
        );

        $where = $this->_model->getAdapter()->quoteInto(self::FLD_PRIMARY . ' = ?', intval($this->data[self::FLD_PRIMARY]));
        $this->_model->update($data, $where);
        return $this;
    }

    /**
     * Return the credential treatment
     * Required for static functions
     * 
     * @access private
     * @return type 
     */
    public function getCredTreatment() {
        return $this->_credTreatment;
    }
    
    /** USER AUTHENTICATION _ STATIC FUNCTIONS * */

    /**
     * Check if current user is logged (if no details given then uses current user)
     *
     * <p>If details are given, will attempt to authenticate the user and if successful will set up
     * the session data structure to hold user based information</p>
     * <p>The session data is held in namespace self::SESS_KEY_USER</p>
     * <p>The session data contains prevLogon (previous logon) and lastLogon (current logon)
     * in Zend_Date objects</p>
     *
     * @param string $uName 	user name
     * @param string $pw	password
     * @param string $model Application specific user data model. if null try and use the default model
     * 			    set by self::setUserModel()
     * @return boolean	    true if user logged on else false
     * @throws ZF4_User_Exception if no model is found
     */
    public static function checkLogon($uName = null, $pw = null, $model = null) {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return true;
        }
        //set the model
        //create the user model
        if (!is_null($model)) {
            try {
                $tClass = new $model();
            } catch (Exception $e) {
                throw new ZF4_User_Exception('Invalid model for user class');
            }
        } else {
            if (Zend_Registry::isRegistered(self::REG_KEY_MODEL)) {
                $model = Zend_Registry::get(self::REG_KEY_MODEL);
                $tClass = new $model();
            } else {
                throw new ZF4_User_Exception('Invalid model for user class');
            }
        }

        //check identity
        if (!empty($uName) && !empty($pw)) {
            $authAdapter = new Zend_Auth_Adapter_DbTable(
                            Zend_Db_Table::getDefaultAdapter()
            );
            $tmp = new ZF4_User();
            $credTreatment = $tmp->getCredTreatment();
            $authAdapter->setTableName($tClass->info(Zend_Db_Table_Abstract::NAME))
                    ->setIdentityColumn(self::FLD_IDENTITY)
                    ->setCredentialColumn(self::FLD_CREDENTIAL)
                    ->setCredentialTreatment($credTreatment . ' AND ' . self::FLD_STATUS . ' = "active"')
                    ->setIdentity($uName)
                    ->setCredential($pw);
            $result = $auth->authenticate($authAdapter);
            if ($result->isValid()) {
                $user = new ZF4_User($uName, $model);
                $data = $user->data;
                //set the previous logon datetime
                $data['prevLogon'] = new Zend_Date($data['lastLogon'], Zend_Date::ISO_8601);
                //set the current logon datetime
                $dt = ZF4_Date::now();
                $data['lastLogon'] = $dt;
                //set the logon IP addresses
                $data['prevIP'] = $data['lastIP'];
                $data['lastIP'] = ZF4_Visitor::getIp();
                $user->getModel()->update(
                        array(self::FLD_LASTLOGON => $dt->get(Zend_Date::ISO_8601),
                    self::FLD_LASTIP => $data['lastIP']
                        ), 'id=' . $data['id']
                );
                self::setSessionIdentity($data);
                //call any postlogon functionality in the user model
                try {
                    $user->getModel()->postLogon();
                } catch (Exception $e) {
                    //do nothing
                }
                return true;
            } else {
                self::setSessionIdentity(false);
                return false;
            }
        } else {
            self::setSessionIdentity(false);
            return false;
        }
    }

    /**
     * Clears the current user identity
     *
     * Called by the UserController logoff facility to log user off
     *
     * @param string $model Application specific user data model
     * @return void
     */
    public static function clearIdentity($model = null) {
        if (self::checkLogon(null, null, $model)) {
            Zend_Auth::getInstance()->clearIdentity();
            //remove the session details
            Zend_Session::destroy();
        }
    }

    /**
     * Return the current logged on user's identity
     *
     * @param string $model Application specific user data model
     * @return string  Return null if no logged on user
     */
    public static function getIdentity($model = null) {
        if (self::checkLogon(null, null, $model)) {
            return Zend_Auth::getInstance()->getIdentity();
        } else {
            return null;
        }
    }

    /**
     * Return the user identity details stored in current session
     *
     * @param string $model Application specific user data model
     * @return array|null  array of details else null if no identity
     */
    public static function getSessionIdentity($model = null) {
        if (self::getIdentity($model) == null) {
            return null;
        } else {
            $sess = new Zend_Session_Namespace(self::SESS_KEY_USER);
            return (array) $sess->user;
        }
    }

    /**
     * Set user session data
     *
     * @param boolean|array $data data array or False
     */
    public static function setSessionIdentity($data) {
        $sess = new Zend_Session_Namespace(self::SESS_KEY_USER);
        $sess->user = $data;
    }

}
