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
 * Relationship Type Import model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
 */
class Application_Model_Import_Reltype extends Application_Model_Import_Abstract {
	/**
	 * Table model
	 * 
	 * @var string
	 */
	protected $_model = 'Application_Model_Reltype';
	
	/**
	 * map input validations to field names
	 * 
	 * @var array|null
	 */
	protected $_validations = array(
		'name' => array('validator' => 'Zend_Validate_NotEmpty'),
		'revName' => array('validator' => 'Zend_Validate_NotEmpty'),
		'direction' => array('validator'=>'Zend_Validate_InArray','params'=>array('haystack'=>array('one-way','two-way'))),
		'relColour' => array('validator'=>'Zend_Validate_Digits'),
		'relValue' => array('validator'=>'Zend_Validate_InArray','params'=>array('haystack'=>array('1','3','5','7'))),
		'headType' => array('validator'=>'ZF4_Validate_PersonType'),
		'tailType' => array('validator'=>'ZF4_Validate_PersonType')
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
