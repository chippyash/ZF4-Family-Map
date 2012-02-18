<?php
/**
 * Family Map Form
 *
 * @category	Family_Map
 * @package 	Forms
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
 * Import profile form
 *
 * @category	Family_Map
 * @package 	Forms
 */
class Application_Model_Form_Impprofile extends Application_Model_Form_Base {

	protected $_tble;
	
	/**
	 * Constructor
	 *
	 * @param string $tble Table name to get profiles for
	 * @param mixed $options as per Zend_Form constructor
	 */
	public function __construct($tble,$options = null) {
		$this->_tble = $tble;
		parent::__construct($options);
	}
	
	/**
	 * Form content description
	 *
	 */
	protected function _describe() {
		$this->setAction('/default/import/identify/format/json');

		$tabindex = 100;
		$model = new Application_Model_Importprofile();
		$user = ZF4_User::getSessionIdentity();
		$profiles = $model->getForSelect(
			'name',
			array(	'orgId=' . intval($user['orgId']),
					"tbl='{$this->_tble}'")
		);
		
		$this->addElement('select','prfid' ,array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Saved Profile Name',
			'multiOptions'   => $profiles
		));

		$this->addElement('button','fetch-button',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Fetch Profile',
			'type'			=> 'button',
			'required'		=> false,
			'onclick'		=> '_getProfile()'
		));
		$this->addElement('text','prfnm',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'New Profile name',
			'type'			=> 'submit',
			'required'		=> false
		));
		$this->addElement('button','set-button',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Save Profile',
			'type'			=> 'button',
			'required'		=> false,
			'onclick'		=> '_setProfile()'
		));
	}
}
