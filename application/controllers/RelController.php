<?php
/**
 * Mapping subsystem
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Relationships
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
 * Relationship viewing controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Relationships
 */
class RelController extends Application_Model_Controller {

	/**
	 * 1/ Set up json context for required actions
	 */
	public function init() {
		$cxt = $this->_helper->getHelper('contextSwitch');
		$cxt->addActionContext('index', 'xml')
			->initContext();
	}

	/**
	 * Display the constellation roamer
	 * 
	 */
	public function indexAction() {
		$cxt = $this->_helper->getHelper('contextSwitch');
		if ($cxt->getCurrentContext() == 'xml') {
			$this->_helper->layout->disableLayout();
			$this->_getrelData();
			return;
		}
		//set up roamer control
		$this->_helper->layout->setLayout('layout3');
		//we create a grouped selector for choosing people
		//the grouping is done in the view script
		$opts = new Application_Model_Person();
		$this->view->people = $opts->getForSelect(array('pType','`uid`','`fName`','`lName`'));
		//get the relationship key colours
		$opts = new Application_Model_Reltype();
		$this->view->relKey = $opts->getForSelect(array('relColour','name'));
		$this->view->headScript()->appendFile('/constellation_roamer/swfobject.js');
		
	}
	
	/**
	 * Return relationship data
	 * XML
	 */
	public function _getrelData() {
		$request = $this->getRequest();
		$p = $request->getParams();
		$nodeId = $request->getParam('id',0);
		$depth = $request->getParam('depth',3);
		if ($nodeId == 0) {
			//just return the school
			$currUser = ZF4_User::getSessionIdentity();
			$org = new Application_Model_Org(intval($currUser['orgId']));
			//$this->view->treeNodes = '<node id="0" tooltip="'.$org->name.'" graphic_type="image" graphic_image_url="/constellation_roamer/images/school.png" />';
			//$this->view->treeEdges = '';
			$this->view->data = '<nodes><node id="0" tooltip="'.$org->name.'" graphic_type="image" graphic_image_url="/constellation_roamer/images/school.png" /></nodes>';
		} else {
			//$tree = new Application_Model_Reltree(intval($nodeId),intval($depth));
			$graph = new Application_Model_Relgraph();
			//$this->view->treeNodes = $tree->renderNodes();
			//$this->view->treeEdges = $tree->renderEdges();
			$this->view->data = $graph->render($nodeId,$depth);
		}
	}
}
