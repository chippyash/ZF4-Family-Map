<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Enroll
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
 * Member enrollment controller
 * 
 * This controller allows ordinary members to enroll for specified services themselves
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Enroll
 */
class EnrollController extends Application_Model_Controller {

	/**
	 * Can we see the footer links
	 * Overide in ancestor if requried
	 *
	 * @var boolean
	 */
	protected $_seeFooterLinks = false;
	
	/**
	 * Set up context switching
	 *
	 */
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('sel', 'json')
					  ->addActionContext('act', 'json')
					  ->initContext();
	}

	/**
	 * Get a new logon timer
	 *
	 * @return Zend_Session_Namespace
	 */
	protected function _getTimer() {
		$timer = new Zend_Session_Namespace('ltimr');
		if (!isset($timer->seq)) {
			$timer->seq = -5;
		}
		return $timer;
	}
	
	/**
	 * Clear the timer
	 *
	 */
	protected function _unsetTimer() {
		if (Zend_Session::namespaceIsset('ltimr')) {
			Zend_Session::namespaceUnset('ltimr');
		}
	}

	/**
	 * Set timer increment and sleep
	 *
	 * @param Zend_Session_Namespace $timer
	 * @param int $inc increment seconds
	 */
	protected function _setTimer($inc) {
		$timer = $this->_getTimer();
		$timer->seq += $inc;  //add inc seconds each time
		if ($timer->seq > intval(ini_get('max_execution_time'))) {
			//limit pause time so we don't break execution limit
			$timer->seq = intval(ini_get('max_execution_time'));
		}
		sleep($timer->seq);  //wait an increasing amount of time
	}
	
	/**
	 * Allow member to logon to the system
	 *
	 */
	public function indexAction() {
		//$this->_helper->layout->setLayout('layout3');
	
		$request = $this->getRequest();
		//try to find the organisation details
		$orgTag = $request->getParam('org');
		
		
		if (!is_null($orgTag)) {
			try {
				$org = new Application_Model_Org($orgTag);
				$this->view->headTitle(' - ' . $org->name);
				$this->view->hasOrg = true;
			} catch (ZF4_Db_Table_Exception_InvalidId $e) {
				$org = null;
				$this->view->hasOrg = false;
			}
		} else {
			$org = null;
		}
		if (is_null($org)) {
			$this->view->form = null;
			$this->view->message = 'Sorry - no valid organisation specified';
			$this->_log("Attempt at entry with no organisation");
			$this->_setTimer(5);
			$this->_helper->viewRenderer->render('index','default'); //'content'
			$this->_helper->viewRenderer->render('advert','subcontent');
			return;
		} 
		
		$form = new Application_Model_Form_Logon();
		$form->setAction('/enroll?org=' . (null==$orgTag?'':$orgTag));
		$this->view->message = '';
		//if we are posting
		if ($request->isPost()) {
			if ($form->isValid($request->getParams())) {
				if ($form->save()) {
					$this->_log("Logged on");
					$this->_unsetTimer();
					$this->getHelper('Redirector')->gotoUrl('/enroll/enroll');
				} else {
					$uName = $request->getParam(ZF4_User::FLD_IDENTITY);
					$uPwd = $request->getParam(ZF4_User::FLD_CREDENTIAL);
					$this->view->message = 'That username and password combination does not match our records.';
					$this->_log("Failed logon (invalid combination {$uName}:{$uPwd})");
					$this->_setTimer(5);
				}
			}else{
				$uName = $request->getParam(ZF4_User::FLD_IDENTITY);
				$uPwd = $request->getParam(ZF4_User::FLD_CREDENTIAL);
				$this->view->message = 'Failed validation';
				$this->_log("Failed logon (invalid input {$uName}:{$uPwd})");
				$this->_setTimer(5);
			}
		} elseif (!$request->isGet()) {

			throw new Application_Model_Exception_InvalidHttpRequest();

		}
		$this->view->form = $form;
		$this->_helper->viewRenderer->render('index','default'); //'content'
		$this->_helper->viewRenderer->render('advert','subcontent');		
	}


	/**
	 * Allow member to see current enrollments and enroll for
	 * other services
	 * 
	 * Can only be reached once they have identified themselves
	 *
	 */
	public function enrollAction() {
		//get current username
		$user = ZF4_User::getSessionIdentity();
		$person = new Application_Model_Customer(intval($user['prsnId']));
		$this->view->userName = "{$person->style} {$person->fName} {$person->lName}";
		//get enrollments
		$this->view->enrollments = Application_Model_Enrolled::getEnrollments($person);
		//get other services
		$this->view->otherServices = Application_Model_Enrolled::getOtherServices($person);
		//remove 'other' services that are enrollable
    	$this->view->otherServices = array_diff_key($this->view->otherServices,$this->view->enrollments);
		//add organisation admin details
		$org = new Application_Model_Org(intval($user['orgId']));
		$this->view->adminName = $org->ctctName;
		$this->view->adminTel = $org->ctctTel;
		$this->view->orgName = $org->name;
		//render two content panes
		$this->_helper->viewRenderer->render('enrollleft','default'); //'content'
		$this->_helper->viewRenderer->render('enrollright','subcontent');
		//add tooltips support
		//$this->view->headScript()->appendFile('/js/tools.tooltip-1.1.3.min.js');
		//$this->view->inlineScript()->appendScript('$(document).ready(function(){$("td[title]").tooltip();})');
		
	}
	
	/**
	 * JSON - carry out enrollment action
	 *
	 * Param: srvcId = Service id - required
	 * 		  op = operation to carry out (del|add) - required
	 * 
	 * @throws Application_Model_Exception_InvalidParams
	 * @throws Application_Model_Exception_InvalidUser
	 */
	public function actAction() {
		$request = $this->getRequest();
		if (!$request->isPost()) {
			throw new Application_Model_Exception_InvalidHttpRequest();
		}
		$op = strtolower($request->getParam('op'));
		$srvcId = intval($request->getParam('srvcId'));
		if (empty($op) || !in_array($op,array('del','add')) || empty($srvcId)) {
			throw new Application_Model_Exception_InvalidParams('service id');
		}
		$response = new ZF4_Json_Message();
		$user = ZF4_User::getSessionIdentity();
		if (empty($user)) {
			throw new Application_Model_Exception_InvalidUser();
		}
		//$member = new Application_Model_Person(intval($user->prsnId));
		
		switch ($op) {
			case 'add':
				$enrollment = new Application_Model_Enrolled();
				$ret = $enrollment->insert(array(
						   		  	'prsnId'=>intval($user['prsnId']),
						   		  	'srvcId'=>$srvcId
						   		  ));
				if ($ret == 0) {
					$response->success = false;
					$response->msg = 'Unable to enroll';
				} else {
					$enrollment = new Application_Model_Enrolled(intval($ret));
					$response->data = array('status'=>$enrollment->status);
				}
				break;
			case 'del':
				$member = new Application_Model_Person(intval($user['prsnId']));
				$enrollment = $member->getEnrollment($srvcId);
				if (is_null($enrollment)) {
					$enrollment = $member->getEnrollment($srvcId,'waiting');
				}
				if (is_null($enrollment)) {
					$response->success = false;
					$response->msg = 'Unable to remove';
				} else {
					if ($enrollment->delete('id='.$enrollment->id) == 1) {
						$response->data = array('status'=>'past');
					} else {
						$response->success = false;
						$response->msg = 'Unable to remove';
					}
				}
				break;
			default :
				break;
		}

		$this->_helper->json->sendJSON($response);		
	}
	
	/**
	 * JSON - Return contents of a select box
	 *
	 * Param: sel = [enrolled]
	 * 		  srvcId = user id [required for sel==enrolled]
	 *
	 * @throws Application_Model_Exception_InvalidParams
	 */
	public function selAction() {
		$request = $this->getRequest();
		$type = $request->getParam('sel',null);
		$response = new ZF4_Json_Message();
		switch ($type) {
			case 'enrolled':
				$id = intval($request->getParam('srvcId',0));
				$opts = new Application_Model_Service($id);
				$options = $opts->getEnrolled();
				if ($options == null) $response->success = false;
				break;
			default :
				throw new Application_Model_Exception_InvalidParams();
				break;
		}
		$response->data = $options;
		$this->_helper->json->sendJSON($response);
	}

	
}