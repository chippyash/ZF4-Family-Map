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
 * Member profile edit form
 *
 * @category	Family_Map
 * @package 	Forms
 */
class Application_Model_Form_Member extends Application_Model_Form_Base {

	/**
	 * Form content description
	 *
	 */
	protected function _describe() 
	{		
		$this->setAction('/user/member');
		$tabindex = 200;

		$this->addElement('select','style',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Style',
			'required'		=> true,
			'multiOptions'  => array('Mr'=>'Mr','Mrs'=>'Mrs','Ms'=>'Ms','Miss'=>'Miss','Mst'=>'Mst','Dr'=>'Dr')
		));


		$this->addElement('text','fName',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'First Name',
			'required'		=> true,
			'size'			=> 20
		));
		$this->addElement('text','mName',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Middle Name',
			'size'			=> 20,
			'required'		=> false
		));
		$this->addElement('text','lName',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Last Name',
			'size'			=> 30,
			'required'		=> true
		));
		
		$this->addElement('date','dob',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Date of birth',
			'required'		=> true
		));
		$this->addElement('select','gender',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Gender',
			'required'		=> true,
			'multiOptions'	=> array('male'=>'Male','female'=>'Female')
		));

		$this->addElement('text','mTel',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Mobile Tel.',
			'required'		=> false
		));
		$this->addElement('text','oTel',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Other Tel.',
			'required'		=> false
		));

		$this->addElement('button','submitmem',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Submit',
			'required'		=> false,
			'type'			=> 'submit',
			'class'			=> 'button ok'
		));

		$this->addElement('hidden','id');
	}

	/**
	 * Save the member information
	 *
	 * @param Zend_View_Abstract $view
	 */
	function save(Zend_View_Abstract $view) {
		//save the new user details
		$data = $this->getValues();
		//retrieve the id
		$id = intval($data['id']);
		unset($data['id']);
		//remove hash field
		unset($data['hash']);
		//save the base data
		$member = new Application_Model_Customer($id);
		//add in some existing data so it gest saved correctly
		$data['pType'] = $member->pType;
		$data['geoId'] = $member->geoId;
		$data['ignoreAddress'] = true; //flag to bypass address resolution
		//$where = $member->quoteInto('id = ?', $id);
		$r = $member->update($data,'id='.$id);
		return ($r == 0 || $r == 1);
	}
}
