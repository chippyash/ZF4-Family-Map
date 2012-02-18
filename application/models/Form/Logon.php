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
 * User handler
 */
include_once "ZF4/User.php";

/**
 * Form to allow user to logon
 *
 * @category	Family_Map
 * @package 	Forms
 */
class Application_Model_Form_Logon extends Application_Model_Form_Base {

	/**
	 * Form content description
	 *
	 */
	protected function _describe() {
		$this->setAction('/user/logon');
		$this->setAttrib('autocomplete','off');
		$tabindex = 100;

		$this->addElement('text',ZF4_User::FLD_IDENTITY ,array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Username',
			'required'		=> true,
			'class'			=> 'enableButtonOnData',
			'rel'			=> 'submit-button',
			'autocomplete'  => 'off'
		));

		$this->addElement('password',ZF4_User::FLD_CREDENTIAL, array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Password',
			'required'		=> true,
			'class'			=> 'enableButtonOnData',
			'rel'			=> 'submit-button',
			'autocomplete'  => 'off'
		));
		
		$this->addElement('button','submit-button',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Logon',
			'type'			=> 'submit',
			'required'		=> false,
			'class'			=> 'ok medium'
		));
	}
	/**
	 * Check the logon
	 *
	 */
	public function save() {
		$data = $this->getValues();
		if (ZF4_User::checkLogon($data['uName'],$data['uPwd'])) {
			return true;
		} else {
			return false;
		}
	}

}
