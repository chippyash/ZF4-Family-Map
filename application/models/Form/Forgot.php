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
 * Form to allow user to retrieve a new password
 *
 * @category	Family_Map
 * @package 	Forms
 */
class Application_Model_Form_Forgot extends Application_Model_Form_Base {

	/**
	 * Form content description
	 *
	 */
	protected function _describe() {
		$this->setAction('/help/forgottenpassword');

		$tabindex = 100;

		$this->addElement('text','uName',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Your username',
			'size'			=> 40,
			'required'		=> true,
			'class'			=> 'textfield large'
		));
		$val1 = new Zend_Validate_Db_RecordExists(array('table'=>'systUser','field'=>'uName'));
		$val1->setMessage('Invalid User Name', Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND );
		$this->getElement('uName')->addValidator($val1);
		
		$this->addElement('text','payrollId',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Your payroll number',
			'size'			=> 40,
			'required'		=> true,
			'class'			=> 'textfield large'
		));
		$val2 = new Application_Model_Validate_PayrollExists($this);
		$this->getElement('payrollId')->addValidator($val2);
		
		$this->addElement('button','submit',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Submit',
			'required'		=> false,
			'class'			=> 'submit_btn3 button ok',
			'type'			=> 'submit'
		));

	}
	/**
	 * Email user with new password
	 *
	 */
	public function save(Zend_View_Abstract $view) {
		//create the new password
		try {
			$uName = $this->getValue('uName');
			//$user = new Application_Model_User($email);
			$user = new ZF4_User($uName);
		} catch (Exception $e) {
			//its an error
			throw new Application_Model_Exception_InvalidUser();
		}
		$pw = $user->genPassword();
		//update user with new password
		$user->updatePw($pw);

		//send the password
		$mailer = new ZF4_Mail();
		//Zend_Debug::dump($pw,'$pw');exit;
		$title = 'Family Map: New password';
		try {
			$mailer->renderMailTemplate(
				$view,
				'forgetpw.phtml',
				array('uName' => $uName,
					  'pw' => $pw
					 ),
				$user->data['uEmail'],
				$title
			);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

}
