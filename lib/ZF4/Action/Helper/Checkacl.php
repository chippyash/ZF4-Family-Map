<?php
/**
 * ZF4 Library
 *
 * Action Helper Check to see if user has access to the requested controller
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
 * Check ACL helper for access to requested controller->action
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Checkacl extends ZF4_Action_Helper {

	/**
	 * Overide ancestor to massage options values
	 *
	 */
	public function init() {
		parent::init();
		$this->_options['skip'] = (isset($this->_options['skip']) ? explode(',',$this->_options['skip']) : array());
	}

	/**
	 * 
	 */
	public function preDispatch() {
		//check to see if we need to skip the check
		$request = $this->getActionController()->getRequest();
		$url = '/' . $request->getModuleName()
		     . '/' . $request->getControllerName()
		     . '/' . $request->getActionName();
		if (in_array($url,$this->_options['skip'])) { return;} //nothing to do

		//get the ACL
		$acl = new $this->_options['aclmodel']();

		//get user info
		$wlcUser = new ZF4_User();
		$user = $wlcUser->getModel();

		//check to see if user model has a getRoles() method
		if (method_exists($user,'getRoles')) {
			//expects an array or role names that this user has
			$roles = $user->getRoles();
		} else {
			//no roles
			$roles = array();
		}

		//check to see if we are super administrator
                //@todo - move string role names to constant definitions
		if (in_array('Super Admin',$roles)) {
			//if we are heading for the default page then reset the 
			// action request
			$ctrl = $this->getActionController();
    		$request = $ctrl->getRequest();
    		$url = '/' . $request->getModuleName()
    		     . '/' . $request->getControllerName();
    		if ($url == '/default/map') {
    			$ctrl->getHelper('redirector')->gotoUrl('/default/input/org');
    		}
		}
		
		//check to see if we are an inputter
		if (in_array('Inputter',$roles)) {
			//if we are heading for the default page then reset the 
			// action request
			$ctrl = $this->getActionController();
    		$request = $ctrl->getRequest();
    		$url = '/' . $request->getModuleName()
    		     . '/' . $request->getControllerName();
    		if ($url == '/default/map') {
    			$ctrl->getHelper('redirector')->gotoUrl('/default/input/usage');
    		}
		}
		
		//check to see if we are a Member
		if (in_array('Member',$roles)) {
			//if we are heading for the default page then reset the 
			// action request
			$ctrl = $this->getActionController();
    		$request = $ctrl->getRequest();
    		$url = '/' . $request->getModuleName()
    		     . '/' . $request->getControllerName();
    		if ($url == '/default/map') {
    			$ctrl->getHelper('redirector')->gotoUrl('/default/enroll/enroll');
    		}
		}
		
   		//construct the resource name
   		$request = $this->getActionController()->getRequest();
   		$resource = $request->getModuleName()
   		     . '_' . $request->getControllerName()
   		     . '_' . $request->getActionName();

   		//check authority to access
   		$allowed = false; 
   		foreach ($roles as $role) {
   			if ($acl->isAllowed($role,$resource)) {
				$allowed = true;
				break;
			}
   		}

   		if (!$allowed) {
   			throw new ZF4_Action_Helper_Checkacl_Exception();
   		}

	}

}