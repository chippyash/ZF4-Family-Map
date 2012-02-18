<?php
/**
 * ZF4 Library
 *
 * Action helper ensure user is logged on
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
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
 * ZF4 Required Logon helper
 *
 * Checks state of application.ini->logon.required and if true and user is not
 * logged on then redirects to application.ini->logon.redirect.
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Logonrequired extends ZF4_Action_Helper {

	/**
	 * Overide ancestor to massage options values
	 *
	 */
	public function init() {
		parent::init();
		$this->_options['required'] = (isset($this->_options['required']) ? (boolean) $this->_options['required'] : false);
		$this->_options['skip'] = (isset($this->_options['skip']) ? explode(',',$this->_options['skip']) : array());
	}

	/**
	 * See if user is required to be logged on
	 *
	 * If user is not logged on then redirect to the application.ini->logon.redirect page
	 * If user is logged on then set the view->user parameter to current user details
	 *
	 */
	public function preDispatch() {
		if (!$this->_options['required']) return; //nothing to do

		//see if we are already on the logon page or any page in the logon.skip list
		$ctrl = $this->getActionController();
		$request = $ctrl->getRequest();
		$url = '/' . $request->getModuleName()
		     . '/' . $request->getControllerName()
		     . '/' . $request->getActionName();
		$skip = $this->_options['skip'];
		array_push($skip,$this->_options['redirect']);
		if (in_array($url,$skip)) { return;} //nothing to do

		//We need to check if user is logged on
//		if (ZF4_User::checkLogon(null,null,$this->_options['usermodel'])) {
		if (ZF4_User::checkLogon()) {
			//if user is logged on, save user details to view template
			$sess = new Zend_Session_Namespace(ZF4_User::SESS_KEY_USER);
			$this->getActionController()->view->user = $sess->user;
			if (!isset($sess->enckey)) {
				//get the user's organisation encryption key
				try {
					$user = ZF4_User::getSessionIdentity();
					$org = new Application_Model_Org(intval($user['orgId']));
					$sess->enckey = $org->enckey;
				} catch (Exception $e) {
					throw new ZF4_Exception_Serious('No organisation key found');
				}
			}			
		} else {
			//redirect to logon page
   			$ctrl->getHelper('redirector')->gotoUrl($this->_options['redirect']);
		}
	}

}