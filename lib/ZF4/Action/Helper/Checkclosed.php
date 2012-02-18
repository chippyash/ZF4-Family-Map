<?php
/**
 * ZF4 Library
 *
 * Action Helper Check to see if site is closed
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
 * Check closed site helper
 *
 * Simply checks state of application.ini->siteclosed.closed setting and redirects
 * to siteclosed.url if true
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Checkclosed extends ZF4_Action_Helper {

	/**
	 * Overide ancestor to massage options values
	 *
	 */
	public function init() {
		parent::init();
		$this->_options['closed'] = (isset($this->_options['closed']) ? (boolean) $this->_options['closed'] : false);
	}

	/**
	 * See if site is closed
	 */
	public function preDispatch() {
    	if ($this->_options['closed']) {
    		//redirect to closed message if not already going there
    		$ctrl = $this->getActionController();
    		$request = $ctrl->getRequest();
    		$url = '/' . $request->getModuleName()
    		     . '/' . $request->getControllerName()
    		     . '/' . $request->getActionName();
    		if ($url != $this->_options['url']) {
    			$ctrl->getHelper('redirector')->gotoUrl($this->_options['url']);
    		}
    	}

	}

}