<?php
/**
 * @category	Family_Map
 * @package 	Validate
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
 * Check that a payroll number exists against a given username
 *
 * @category	Family_Map
 * @package 	Validate
 */
class Application_Model_Validate_PayrollExists extends Zend_Validate_Db_RecordExists
{

	protected $_form;
	/**
	 * Constructor
	 *
	 */
	public function __construct(Application_Model_Form_Base $form) {
		$this->_form = $form;
		parent::__construct(array(
						'table'	=> 'systUser',
						'field' => 'payrollId'
					)
		);
		$this->setMessage('Invalid Payroll Number', Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND );
	}
	
    /**
     * Gets the select object to be used by the validator.
     * If no select object was supplied to the constructor,
     * then it will auto-generate one from the given table,
     * schema, field, and adapter options.
     * 
     * Extends Ancestor
     *
     * @return Zend_Db_Select The Select object which will be used
     */
    public function getSelect() {
    	$select = parent::getSelect();
    	//add the user name value
        $uName = $this->_form->getValue('uName');
        $select->where('uName = ?',$uName);
        $this->_select = $select;
        return $this->_select;
    }
}
