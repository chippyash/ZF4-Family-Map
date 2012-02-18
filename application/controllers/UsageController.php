<?php
/**
 * @category	Family_Map
 * @package 	Controller
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
 * Batch Input controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Usage
 */
class UsageController extends Application_Model_Controller {

	/**
	 * Organisation Id
	 *
	 * @var int
	 */
	protected $_orgId;
	
	/**
	 * Set up context switching
	 *
	 */
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('sel', 'json')
					  ->initContext();
	}

/**
 * MAIN DATA MAINTENANCE SCREEN
 */
	/**
	 * Present maintenance table selection to user
	 * 
	 * Functionality is in the view script, css and js files
	 */
	public function indexAction() {
		
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

	
/**
 * Batch Usage input
 */

	/**
	 * This presents a data entry grid - 20 rows at a time
	 * that the user can input usage data into
	 *
	 */
	public function batchAction() {
		$request = $this->getRequest();
		if ($request->isPost() && $request->getParam('submit') !== null) {
			//process the data
			$batch = intval($request->getParam('nrec'));
			$dt = new Zend_Date($request->getParam('dt'),null,'en_GB');
			$members = $request->getParam('mbr');
			$services = $request->getParam('srvc');
			$ret = Application_Model_Usage::saveRecords($batch,$dt,$members,$services);
			if ($ret !== true) {
				//send errors back to user
				//shouldn't be any as input is by selection box
			} else {
				$this->_log('Usage Input: '.$batch.' records entered');
			}
		}
		//add date entry for form
		$this->view->headScript()->appendFile('js/jquery.dateentry.js','screen');
		
		$this->_helper->layout->setLayout('layout3');
		//set up member select options
		$members = new Application_Model_Customer();
		$this->view->memOpts = $members->getForSelect('uid');
		//set up service select options
		$services = new Application_Model_Service();
		$this->view->srvcOpts = $services->getForSelect('name');
	}
	
	/**
	 * Screen for recording usage of enrolled services
	 *
	 */
	public function registerAction() {
		$request = $this->getRequest();
		if ($request->isPost() && $request->getParam('submit') !== null) {
			//process the data
			$dt = new Zend_Date($request->getParam('dt'),null,'en_GB');
			$members = $request->getParam('mbr');
			$batch = count($members);
			$service = $request->getParam('srvcId');
			$srvcs = array_fill(0,$batch,$service);
			$ret = Application_Model_Usage::saveRecords($batch,$dt,array_keys($members),$srvcs);
			if ($ret !== true) {
				//send errors back to user
				//shouldn't be any as input is by selection box
			} else {
				$this->_log('Registration: '.$batch.' records entered');
			}
		}
		//add date entry for form
		$this->view->headScript()->appendFile('js/jquery.dateentry.js','screen');
		
		$this->_helper->layout->setLayout('layout3');
		//set up service select options
		$user = new Application_Model_User(ZF4_User::getIdentity());
		$services = new Application_Model_Service();
		if (in_array('User',$user->getRoles())) {
			$this->view->srvcOpts 
				= $services->getForSelect('name',array("staffId = {$user->id}"));
		} else {
			$this->view->srvcOpts = $services->getForSelect('name');
		}
		
	}

/** PROTECTED METHODS **/
	/**
	 * Set up a data maintenance page
	 *
	 */
	protected function _dmSetup() {
		$request = $this->getRequest();
		$op = $request->getParam('oper');
		$ctxt = $this->_helper->getHelper('contextSwitch')->getCurrentContext();
		if (null == $op && $ctxt !== 'json') { //render the data maintenance page
			$this->_helper->layout->setLayout('layout3');
			$this->render('dmaint');
			$this->view->headScript()
				->appendFile('/js/jquery.ui.datepicker.js');
				//->appendFile('/js/ui.multiselect.js');
		}		
	}

}