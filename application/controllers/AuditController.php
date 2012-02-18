<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Audit
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
 * Audit trails
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Audit
 */
class AuditController extends Application_Model_Controller {


	/**
	 * Set up context switching
	 *
	 */
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('messages', 'json')
					  ->addActionContext('logons', 'json')
					  ->addActionContext('ids', 'json')
					  ->initContext();
	}

	/**
	 * Display action messages
	 *
	 */
	public function messagesAction() {

		$this->view->headLink()->prependStylesheet('/css/jqgrid_custom.css','screen,print');
		$this->view->headScript()->appendFile('/js/jqgrid_shared.js');

		//switch output on context
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		if ($contextSwitch->getCurrentContext() == 'json') {
			//get the request information
			$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
			$select->from('actionMessage',
				array('id',
					  'logDt',
					  'lvl' => new Zend_Db_Expr("CASE `lvl` WHEN 0 THEN 'Emergency' WHEN 1 THEN 'Alert' WHEN 2 THEN 'Critical' WHEN 3 THEN 'Error' WHEN 4 THEN 'Warning' WHEN 5 THEN 'Notice' WHEN 6 THEN 'Information' ELSE 'Debug' END"),
					  'msg',
					  'uName',
					  'ip')
			);

			$grid = new ZF4_JQuery_Grid($this,$select);
			$grid->handle();
		} else {
			//output the grid model support
			$grid = new ZF4_JQuery_Grid($this);
			$grid->display();
		}

	}

	/**
	 * Display logon trail
	 *
	 */
	public function logonsAction() {

		$this->view->headLink()->prependStylesheet('/css/jqgrid_custom.css','screen,print');
		$this->view->headScript()->appendFile('/js/jqgrid_shared.js');
		//switch output on context
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		if ($contextSwitch->getCurrentContext() == 'json') {
			//get the request information
			$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
			$select->from('logons',array('id','logDt',
			'lvl' => new Zend_Db_Expr("CASE `lvl` WHEN 0 THEN 'Emergency' WHEN 1 THEN 'Alert' WHEN 2 THEN 'Critical' WHEN 3 THEN 'Error' WHEN 4 THEN 'Warning' WHEN 5 THEN 'Notice' WHEN 6 THEN 'Information' ELSE 'Debug' END"),
			'msg','uName','ip'));
			$grid = new ZF4_JQuery_Grid($this,$select);
			$grid->handle();
		} else {
			//output the grid model support
			$grid = new ZF4_JQuery_Grid($this);
			$grid->display();
		}
	}

	/**
	 * Display IDS log
	 *
	 */
	public function idsAction() {

		$this->view->headLink()->prependStylesheet('/css/jqgrid_custom.css','screen,print');
		$this->view->headScript()->appendFile('/js/jqgrid_shared.js');
		//switch output on context
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		if ($contextSwitch->getCurrentContext() == 'json') {
			//get the request information
			$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
			$select->from('ids_intrusions',array('id','name','value','page','tags','ip','impact','origin','created'));
			$grid = new ZF4_JQuery_Grid($this,$select);
			$grid->handle();
		} else {
			//see if an 'empty log' request was made
			if (intval($this->getRequest()->getParam('rem',0)) == 1) {
				Zend_Db_Table_Abstract::getDefaultAdapter()->query('truncate table ids_intrusions');
			}

			//output the grid model support
			$grid = new ZF4_JQuery_Grid($this);
			$grid->display();
		}
	}
}