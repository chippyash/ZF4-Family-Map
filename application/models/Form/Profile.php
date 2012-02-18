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
 * User profile edit form
 *
 * @category	Family_Map
 * @package 	Forms
 */
class Application_Model_Form_Profile extends Application_Model_Form_Base {

	/**
	 * Form content description
	 *
	 */
	protected function _describe() 
	{		
		$this->setAction('/user/index');
		$tabindex = 100;

		$this->addElement('text','uName',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'User id',
			'readonly'		=> true,
			'required'		=> false
		));

		$emailVal = new Zend_Validate_EmailAddress();
		$emailVal->setMessage('Invalid email address', Zend_Validate_EmailAddress::INVALID_FORMAT );
		$this->addElement('text','uEmail',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Email Address',
			'required'		=> true
		));
		$this->getElement('uEmail')->addValidator($emailVal);
		$this->addElement('text','uEmail2',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Confirm Email Address',
			'required'		=> true
		));
		$this->getElement('uEmail2')->addValidator(new ZF4_Validate_MatchField('uEmail','Email Address'));


		$this->addElement('password','uPwd',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Enter new password?',
			'required'		=> false
		));

		$this->addElement('password','uPwcheck',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Confirm new password',
			'required'		=> false
		));
		$this->getElement('uPwcheck')->addValidator(new ZF4_Validate_MatchField('uPwd','New password'));

		$this->addElement('button','submitprof',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Submit',
			'required'		=> false,
			'type'			=> 'submit',
			'class'			=> 'button ok'
		));

		$this->addElement('hidden','id');
	}

	/**
	 * Save the user information
	 *
	 * @param Zend_View_Abstract $view
	 */
	function save(Zend_View_Abstract $view) {
		//save the new user details
		$data = $this->getValues();
		//remove the check password
		unset($data['uPwcheck']);
		if (!empty($data['uPwd'])) {
			$pw = $data['uPwd']; //save for later
		} else {
			$pw = false;
		}
		unset($data['uPwd']);
		//remove the check email
		unset($data['uEmail2']);
		//retrieve the id
		$id = intval($data['id']);
		unset($data['id']);
		//remove hash field
		unset($data['hash']);
		//save the base data
		$user = new ZF4_User($id);
		$where = $user->getModel()->getAdapter()->quoteInto('id = ?', $id);
		$r = $user->getModel()->update($data,$where);
		//process the password if required
		if ($pw !== false) {
			//update user with new password
			$user->updatePw($pw);
		}
		//process the email address for linked person if required
		if (!empty($user->data['prsnId'])) {
			$member = new Application_Model_Customer(intval($user->data['prsnId']));
			$newData = array(
				'pType' => $member->pType,
				'geoId' => $member->geoId,
				'ignoreAddress' => true, //flag to bypass address resolution
				'email' => $data['uEmail']
			);
			$member->update($newData,'id='.$member->id);
		}
		return ($r == 0 || $r == 1);
	}
}
