<?php
/**
 * Family Map Imports
 *
 * @category	Family_Map
 * @package 	Model
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
 * Service Import model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
 */
class Application_Model_Import_Srvc extends Application_Model_Import_Abstract {
	/**
	 * Table model
	 * 
	 * @var string
	 */
	protected $_model = 'Application_Model_Service';
	
	/**
	 * map input filters to field names
	 *
	 * @var array
	 */
	protected $_filters = array(
		'*' => array('filter'=>'Zend_Filter_StringTrim'),
		'staffId' => array('filter'=> 'ZF4_Filter_Staffid')
	);
	
	/**
	 * map input validations to field names
	 * 
	 * We fail a staff id that is not digits because it means that the filter
	 * has set it = 'undefined'
	 * 
	 * @var array|null
	 */
	protected $_validations = array(
		'staffId' => array('validator' => 'Zend_Validate_Digits'),
		'name' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'enrolType' => array('validator'=>'Zend_Validate_InArray','params'=>array('haystack'=>array('free','admin','staff','member','any'))),
		'eLimit'=>array('validator'=>'Zend_Validate_Int')
	);

	/**
	 * Return an array of fldName=>value that will be added to every
	 * record being inserted into the database
	 * 
	 * @return array
	 *
	 */
	protected function _getStaticData() {
		return array(
			'orgId'   => $this->_orgId
		);

	}	
	
}
