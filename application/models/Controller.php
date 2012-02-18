<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Base
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
 * Base application controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Base
 */
class Application_Model_Controller extends Zend_Controller_Action {

	/**
	 * Organisation Id
	 *
	 * @var int
	 */
	protected $_orgId;
	/**
	 * Can we see the footer links
	 * Overide in ancestor if requried
	 *
	 * @var boolean
	 */
	protected $_seeFooterLinks = true;
	
	/**
	 * Extends parent constructor
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Zend_Controller_Response_Abstract $response
	 * @param array $invokeArgs
	 */
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		//get the current user org id
		$user = ZF4_User::getSessionIdentity();
		$this->_orgId = (isset($user['orgId']) ? intval($user['orgId']) : 0);
		parent::__construct($request,$response,$invokeArgs);
		$this->view->headTitle('Family Map');
		//turn on links to support etc - default setting
		$this->view->seeFooterLinks = $this->_seeFooterLinks;
		
	}
	
	/**
	 * Create a log entry
	 *
	 * @param string $msg
	 */
	protected function _log($msg) {
		$uName = ZF4_User::getIdentity();
		$uName = ($uName == '' || $uName == null ? 'No Id' : $uName);
		$this->_helper->Logger(
			'message',
			$msg,
			Zend_Log::INFO ,
			array(
				'uName'=>$uName,
				'ip'=>ZF4_Visitor::getIp(),
				'orgId'=>$this->_orgId
			)
		);
	}
}