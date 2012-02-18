<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Import
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
 * Import controller
 *
 * Import data into system
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Import
 */
class ImportController extends Application_Model_Controller {

	/**
	 * registry key for process status
	 */
	const SESS_KEY_IMPORT = 'wlcImport';

	/**
	 * fields found in the import file
	 *
	 * @var array
	 */
	protected $_found = array();

	/**
	 * Stages in the import process
	 *
	 * @var array
	 */
	private $_stage = array('upload','identify','process','complete','error');

	/**
	 * Controller initialisation
	 *
	 */
	public function init() {
		$cxt = $this->_helper->getHelper('contextSwitch');
		if (!$cxt->hasContext('file')) {
			//set up context switching for the file downloads
			$cxt->addContext(
					'file',
					 array(
						'suffix' => 'file',
						'headers' => array(
							'Content-Type' => 'application/text',
							'Keep-Alive' => 'timeout=15, max=100',
							'Cache-Control' => 'public, must-revalidate, max-age=0',
							'Content-Disposition' => 'inline'
						)
					)
				)
				->addActionContext('download', 'file')  //download can be returned as file
				->addActionContext('identify','json')
				->initContext();
		}
	}

	/**
	 * handle file import process - really it's just a mini front controller
	 *
	 * forwards to other actions dependent on stage in the process
	 *
	 * params: stg = stage = [upload|identify|process|complete|error]
	 * 		   tbl = table to import [cat|staff|cust|relType|srvc]
	 *
	 * @throws Application_Model_Exception_InvalidParams if invalid stage is requested
	 */
	public function indexAction() {
		$stage = $this->getRequest()->getParam('stg');
		if (null !== $stage) {
			if (!in_array($stage,$this->_stage)) {
				throw new Application_Model_Exception_InvalidParams('Invalid stage request');
			}
			//go to appropriate action
			$this->_forward($stage, 'import');
		}
	}

	/**
	 * Present screen to get the file to be uploaded
	 *
	 * @throws Application_Model_Exception_InvalidHttpRequest
	 * @throws Application_Model_Exception_InvalidParams
	 */
	public function uploadAction() {
		$this->_helper->layout->setLayout('layout3');
		$request = $this->getRequest();
		$tbl = $this->getRequest()->getParam('tbl');
		if (null == $tbl
		   || !in_array($tbl,array('cat','staff','cust','reltype','srvc','opro'))
		   ) {
		   	throw new Application_Model_Exception_InvalidParams('Invalid Table specified');
		}
		
		$class = "Application_Model_Import_" . ucfirst(strtolower($tbl));
		$handler = new $class($this->view);
		$this->view->tbl = ucfirst($tbl);
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if ($request->isPost()) {
			//process the form post
			$form = $handler->requestForm();
			if ($form->isValid($request->getParams()) && $form->fName->receive()) {
				$sess->status = 'upload';
				$sess->form = $form->getValues();
				$sess->form['fLoc'] = $form->fName->getFileName();
				//go to identify stage
				$this->_forward('identify');
			} else {
				$sess->status = 'error';
				//$form->populate($request->getParams());
				$this->view->form = $form;
			}
		} elseif ($request->isGet()) {
			//display form page
			$sess->table = $tbl; //save the requested table name
			$this->view->form = $handler->requestForm();
		} else {
			throw new Application_Model_Exception_InvalidHttpRequest();
		}
	}

	/**
	 * Present the screen to identify columns in the data for importing
	 *
	 * @throws Application_Model_Form_Exception_InvalidImportRequest
	 */
	public function identifyAction() {
		if ($this->_helper->getHelper('contextSwitch')->getCurrentContext() == 'json') {
			$this->_identifyprofile();
		}
		$this->_helper->layout->setLayout('layout3');
		//make sure we are in correct processing cycle
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if (!in_array($sess->status,array('upload','identify'))) {
			throw new Application_Model_Exception_InvalidImportRequest();
		}
		$class = "Application_Model_Import_" . ucfirst(strtolower($sess->table));
		$handler = new $class($this->view);
		$this->view->tbl = ucfirst($sess->table);
		
		$request = $this->getRequest();
		if ($request->isPost() && $sess->status != 'upload') {
			$sess->status = 'identify';
			$data = $request->getParams();
			//get the field assigments from the input data
			$map = array();
			foreach ($data as $key => $value) {
				if (substr($key,0,4) == 'fld_') {
					$map[intval(str_replace('fld_','',$key))] = $value;
				}
			}
			$sess->form['map'] = $map;
			$sess->form['dtfmt'] = $data['dtfmt'];
			$sess->form['skip'] = (isset($data['skip']) ? true : false );
			$this->_forward('process');
		} elseif ($sess->status == 'upload' || $request->isGet()) {

			//display form page
			$this->_setFound();
			$this->view->form = $handler->colForm($sess->found);
			$this->view->proform = new Application_Model_Form_Impprofile(strtolower($sess->table)); 
			$sess->status = 'identify';
		} else {
			throw new Application_Model_Exception_InvalidHttpRequest();
		}
	}

	/**
	 * Handler for saving and retrieving import profiles
	 * JSON
	 *
	 * parameters:
	 * 	prfop: string get|set
	 *  nm: string name of profile to save [required for set]
	 *  id: int id of profile to retrieve [required for get]
	 *  tbl: table to save profile for [required for set]
	 *  map: field mapping [required for set]
	 */
	protected function _identifyProfile() {
		$response = new ZF4_Json_Message();
		$request = $this->getRequest();
		$op = $request->getParam('prfop');
		switch ($op) {
			case 'set':
				$model = new Application_Model_Importprofile();
				$id = $model->insert(array(
					'tbl' => $request->getParam('tbl'),
					'name' => $request->getParam('nm'),
					'profile' => serialize($request->getParam('map'))
				));
				$response->data = array('id'=>$id,'value'=>$request->getParam('nm'));
				break;
			case 'get':
				$model = new Application_Model_Importprofile(intval($request->getParam('id')));
				$response->data = unserialize($model->profile);
				break;
			default:
				$response->success = false;
				$response->msg = 'Invalid profile operation';
				break;
		}
		$this->_helper->json->sendJSON($response);
	}
	
	/**
	 * Set up the found fields
	 *
	 */
	protected function _setFound() {
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		$delim = Application_Model_Import_Abstract::getFieldDelimiter($sess->form['fType']);
		$quoteStyle = Application_Model_Import_Abstract::getQuoteType($sess->form['qType']);
		//get first line of file
		$t = $sess->form['fLoc'];
		$fh = fopen($sess->form['fLoc'],'r');
		$line = fgetcsv($fh,0,$delim,$quoteStyle);
		fclose($fh);
		$sess->found = $this->_found = $line;
	}

	/**
	 * Import the data
	 *
	 */
	public function processAction()	{
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if ($sess->status !== 'identify') {
			throw new Application_Model_Exception_InvalidImportRequest();
		}
		$sess->status = 'process';
		$class = "Application_Model_Import_" . ucfirst(strtolower($sess->table));
		$handler = new $class($this->view);
		$error = $handler->import(
			$sess->form['map'], 
			$sess->found, 
			$sess->form['fLoc'], 
			$this->_helper->Logger->getLogger('message'),
			$sess->form['fType'],
			$sess->form['qType'],
			$sess->form['dtfmt'],
			$sess->form['skip']
		);
		
		//Log the import message and send user to correct page
		$sess->importResult = $importResults = $handler->getResults();
		if ($error) {
			$this->_log('Imported file: ' . $sess->form['fLoc'] . ': With ' . $importResults['numErrors'] .' errors in ' . $importResults['numLines'] . ' lines');
			$this->_forward('error');
		} else {
			$this->_log('Imported file: ' . $sess->form['fLoc'] . ': No errors');
			$this->_forward('complete');
		}
	}

	/**
	 * Display Complete! page
	 *
	 */
	public function completeAction() {
		$this->_helper->layout->setLayout('layout3');
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if ($sess->status !== 'process') {
			throw new Application_Model_Exception_InvalidImportRequest();
		}
		$this->view->file = basename($sess->form['fLoc']);
	}

	/**
	 * Display import errors
	 *
	 * Log incomplete import
	 */
	public function errorAction() {
		$this->_helper->layout->setLayout('layout3');
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if ($sess->status !== 'process') {
			throw new Application_Model_Exception_InvalidImportRequest();
		}
		$this->view->numLines = $sess->importResult['numLines'];
		$this->view->errors = $sess->importResult['numErrors'];
		$this->view->file = basename($sess->form['fLoc']);
		$this->view->log = file($sess->importResult['logFile']);
	}

	/**
	 * Allow user to download the error log or import error lines file
	 *
	 * Param: type = [file]
	 *
	 * @throws Application_Model_Exception_InvalidImportRequest
	 * @throws Application_Model_Exception_InvalidParams
	 */
	public function downloadAction() {
		$sess = new Zend_Session_Namespace(self::SESS_KEY_IMPORT );
		if ($sess->status !== 'process') {
			throw new Application_Model_Exception_InvalidImportRequest();
		}
		$request = $this->getRequest();
		$type = $request->getParam('type','file');

		if ($type == 'log') {
			//$file = $this->_getLogFileName();
		} elseif ($type == 'file') {
			$file = ZF4_ROOT_PATH .
				'/uploads/import_errors_' .
				ZF4_User::getIdentity() .
				'_' . str_replace(' ','_',basename($sess->form['fLoc']));
		} else {
			throw new Application_Model_Exception_InvalidParams();
		}

		Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
		$fContent = file_get_contents($file);
		$this->_helper->contextSwitch()->addHeader('file','Content-Length',strlen($fContent));
		echo $fContent;

	}

	/**
	 * Returns XML snippet for a selector list
	 *
	 * params
	 */
	public function getselectorAction() {
		$request = $this->getRequest();
		$type = $request->getParam('itype',null);
		$bId = intval($request->getParam('bId'));
		$pId = intval($request->getParam('pId'));
		if ($type == 'property') {
			$opts = new Application_Model_Property();
			$opts->orderCol = 'pName';
			$opts->orderDir = 'asc';
			$selOpts = $opts->getForSelect($bId);
		} elseif ($type == 'campaign') {
			$opts = new Application_Model_Campaign();
			$opts->orderCol = 'cName';
			$opts->orderDir = 'asc';
			$selOpts = $opts->getForSelect(
				intval($bId),
				intval($pId)
			);
		} else {
			throw new Application_Model_Exception_InvalidParams();
		}
		$this->view->opts = $selOpts;
		$this->_helper->layout->disableLayout();
	}

	/**
	 * XHTML - get all available attributes for campaigns
	 * return as a set of checkboxes
	 *
	 */
	public function getattribsAction() {
		$campaign = new Application_Model_Campaign();
		$this->view->opts = $campaign->getAllAttribs();
		$this->_helper->layout->disableLayout();
	}
}