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
 * Other Professionals Import model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
 */
class Application_Model_Import_Opro extends Application_Model_Import_Abstract {
	
	/**
	 * Table model
	 * 
	 * @var string
	 */
	protected $_model = 'Application_Model_Opro';
	
	/**
	 * Strip off unwanted fields from the meta info
	 *
	 * @param array $meta meta column information
	 * @return array
	 */
	protected function _stripCols(array $meta) {
		//strip off the system managed fields
		unset($meta['pCode']);
		unset($meta['hNum']);
		unset($meta['dob']);
		unset($meta['age']);
		unset($meta['ageRange']);
		unset($meta['ethnicity']);
		unset($meta['lang']);
		unset($meta['geoId']);
		return parent::_stripCols($meta);
	}
	
	/**
	 * map input filters to field names
	 *
	 * @var array
	 */
	protected $_filters = array(
		'*' => array('filter'=>'Zend_Filter_StringTrim'),
		'gender' => array('filter'=>'ZF4_Filter_Gender'),
		'style' => array('filter'=>'ZF4_Filter_Style'),
		'pType' => array('filter'=>'Zend_Filter_Alpha','params'=>array('allowwhitespace'=>true))
	);
	/**
	 * map input validations to field names
	 * 
	 * @var array|null
	 */	
	protected $_validations = array(
		'uid' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'fName' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'lName' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'pType' => array('validator'=>'Zend_Validate_InArray','params'=>array('haystack'=>array('doctor','health visitor','carer'))),
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
	
	/**
	 * Run a final check on the data just prior to 
	 * inserting into the database.
	 * 
	 * if gender is undefined try and determine it from the style attribute
	 *
	 * @param array $cleanData field array of filtered and validated data
	 * @return array
	 */
	protected function _preImport($cleanData) {
		if ($cleanData['gender'] == 'undefined') {
			switch ($cleanData['style']) {
				case 'Mr':
				case 'Mst':
					$cleanData['gender'] = 'male';
					break;
			
				case 'Mrs':
				case 'Ms':
				case 'Miss':
					$cleanData['gender'] = 'female';
					break;
					
				default:
					//this will take care of Dr - cannot determine gender
					break;
			}
		}
		return $cleanData;
	}	
}
