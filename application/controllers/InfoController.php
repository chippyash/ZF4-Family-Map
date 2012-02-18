<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Info
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
 * Information controller
 *
 * Serves up T&Cs, privacy and other site static pages
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Info
 */
class InfoController extends Application_Model_Controller {

	/**
	 * Privacy policy
	 *
	 */
	public function prvcyAction(){}
	/**
	 * Standard Site T&C
	 *
	 */
	public function tandcAction(){}
	/**
	 * site closed
	 *
	 */
	public function closedAction(){}

	/**
	 * Display the next membership/customer id for this organisation
	 *
	 */
	public function nextidAction() {
		try {
			$this->view->nextId = Application_Model_Customer::getNextMbrId();
		} catch (Exception $e) {
			throw new ZF4_Exception_Serious($e->getMessage());
		}
	}
	/**
	 * Site credits - about
	 */
	public function creditsAction() {
		//switch off footer links if it is a member
		$user = ZF4_User::getSessionIdentity();
		if (in_array('Member',$user['roles'])) {
			$this->view->seeFooterLinks = false;
		}
		
		$this->_helper->layout->setLayout('layout3');
		//get the hardcoded version of apps an libraries
		//WLC
		$this->view->verWLC = Application_Model_Version::VERSION;
		//ZF
		$this->view->verZF = Zend_Version::VERSION ;
		//IDS
		$this->view->verIDS = '0.6.4' ;
		//jQuery
		$this->view->verJQ = '1.4.2';
		$this->view->verJQUI = '1.8.5';
		//principle JS libs
		$this->view->verMarker = '1.1';
		$this->view->verKjells = '3.0';
		$this->view->verJQGrid = '3.8';
		$this->view->verGoogleMaps = '1.1';
		$this->view->verGoogleAPI = 'V.2';
		//get the repository revision string and date
		/*
		try {
			$rFile = file_get_contents(ZF4_BASE_PATH . '/application/config/revision.txt');
			$matches = array();
			$ret = preg_match('/Exported revision (?P<rev>\d*)\.\n/',$rFile,$matches);
			if ($ret != false) {
				$this->view->revision = $matches['rev'];
			} else {
				$this->view->revision = 'Unknown';
			}
			$matches = array();
			$fDate = new Zend_Date(filemtime(ZF4_BASE_PATH . '/application/config/revision.txt'));
			$this->view->lastChange = $fDate->get(Zend_Date::DATE_LONG );
		} catch (Exception $e) {
				$this->view->revision = 'Unknown';
				$this->view->lastChange = 'Unknown';
		}
		*/
	}
        
        /**
         * Display application license
         * GNU AFFERO GENERAL PUBLIC LICENSE V3
         */
        public function licenseAction() {
            
        }

}