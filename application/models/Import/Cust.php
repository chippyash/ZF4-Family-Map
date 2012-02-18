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
 * Customer Import model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
 */
class Application_Model_Import_Cust extends Application_Model_Import_Abstract {
	
	/**
	 * Table model
	 * 
	 * @var string
	 */
	protected $_model = 'Application_Model_Customer';
	/**
	 * map input filters to field names
	 *
	 * @var array
	 */
	protected $_filters = array(
		'*' => array('filter'=>'Zend_Filter_StringTrim'),
		'gender' => array('filter'=>'ZF4_Filter_Gender'),
		'style' => array('filter'=>'ZF4_Filter_Style'),
		'lang' => array('filter'=>'ZF4_Filter_Language'),
		'pType' => array('filter'=>'Zend_Filter_Alpha'),
		'ethnicity' =>array('filter'=>'ZF4_Filter_Padstr','params'=>array('padString'=>'0','padLength'=>2,'padType'=>STR_PAD_LEFT))
	);
	
	/**
	 * map input validations to field names
	 * 
	 * @var array|null
	 */

	protected $_validations = array(
		'ethnicity' => array('validator'=>'Zend_Validate_Regex','params'=>array('pattern'=>'/^\d{2}$/')),
		'pCode' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'hNum' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'fName' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'lName' => array('validator'=>'Zend_Validate_NotEmpty','params'=>array('type'=>72 )),
		'pType' => array('validator'=>'Zend_Validate_InArray','params'=>array('haystack'=>array('member','pupil'))),
		'email' => array('validator'=>'Zend_Validate_EmailAddress','params'=>array('allow'=> 5))
	);
		
	/**
	 * Category fields
	 *
	 * @var array [catId=>catname]
	 */
	protected $_catflds = array();
	
	/**
	 * address fields, taken from geoData
	 *
	 * @var array [catId=>catname]
	 */
	protected $_addrflds = array();
	
	/**
	 * Category records store
	 *
	 * @var array
	 */
	protected $_catData = array();
	/**
	 * Address data store
	 *
	 * @var array
	 */
	protected $_addrData = array();
	
	/**
	 * get category fields for adding to customer import mapping
	 *
	 */
	protected function _getCats() {
		if (empty($this->_catflds)) {
			$model = new Application_Model_Category();
			$tmp = $model->getForSelect('name');
			//code up the fields so they can be integrated in the customer data
			foreach ($tmp as $key=>$name) {
				$this->_catflds["cat.{$key}"] 
					= array(
						ZF4_Db_Table_Model::COMMENT =>"cat > {$name}|",
						'DATA_TYPE' => "enum('yes','no')"
					);
			}
		}
		return $this->_catflds;
	}
	
	/**
	 * get address fields for adding to customer import mapping
	 *
	 */
	protected function _getAddr() {
		if (empty($this->_addrflds)) {
			$model = new Application_Model_Geodata();
			$info = $model->getColInfo();
			$this->_addrflds = array('hNum'=>$info['hNum'],'pCode'=>$info['pCode']);
		}
		return $this->_addrflds;
	}
	
	/**
	 * get the column full info for the model
	 * Overide ancestor to add category and address information fields
	 *
	 * @return array
	 */
	public function getMeta() {
		if (empty($this->_meta)) {
			$this->_meta = $this->getModel()->getColInfo();
			//add in the category fields
			$catFlds = $this->_getCats();
			$this->_meta = array_merge($this->_meta,$catFlds);
			//add in the address fields
			$addrFlds = $this->_getAddr();
			$this->_meta = array_merge($this->_meta,$addrFlds);
		}
		return $this->_meta;
	}
		
	/**
	 * Strip off unwanted fields from the meta info
	 * 
	 * @param array $meta meta column information
	 * @return array
	 */
	protected function _stripCols(array $meta) {
		//strip off the system managed fields
		unset($meta['age']);
		unset($meta['ageRange']);
		unset($meta['geoId']);
		return parent::_stripCols($meta);
	}
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
	 * - if gender is undefined try and determine it from the style attribute
	 * - strip out any category information for later processing in _postImport()
	 * - strip out address information  for later processing in _postImport()
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
		//strip out category information and store for post processing
		$this->_catData = array();
		foreach ($cleanData as $fld=>$data) {
			if (substr($fld,0,4) == 'cat.') {
				$this->_catData[substr($fld,4)] = $data;
				unset($cleanData[$fld]);
			}
		}
		//strip out address information and store for post processing
		/*
		if (isset($cleanData['hNum'])) {
			$this->_addrData['hNum'] = $cleanData['hNum'];
			unset($cleanData['hNum']);
		} else {
			$this->_addrData['hNum'] = '';
		}
		if (isset($cleanData['pCode'])) {
			$this->_addrData['pCode'] = $cleanData['pCode'];
			unset($cleanData['pCode']);
		} else {
			$this->_addrData['pCode'] = '';
		}
		*/
		return $cleanData;
	}	
	
	/**
	 * Process categories
	 *
	 * @param int $masterRcdId The id of the master record that has just been inserted
	 */
	protected function _postImport($masterRcdId, $cleanData) {
		if (empty($this->_catData)) return;
		$model = new Application_Model_Customer(intval($masterRcdId));
		foreach ($this->_catData as $key=>$value) {
			if($value == 'yes') $model->addCategory(intval($key));
		}
	}
	
	/**
	 * Extend ancestor to add in filters for category fields
	 *
	 * @param int $dtFormat Date format code
	 * @return Zend_Filter_Input
	 */
	protected function _getInputValidator($dtFormat) {
		$cats = $this->_getCats();
		foreach ($cats as $key=>$value) {		
			//add filter for each category field
			$this->_filters["cat.{$key}"] = array(
			'filter'=>'Zend_Filter_Boolean',
			'params'=> array(
				'type' => array('all')
			));
		}
		return parent::_getInputValidator($dtFormat);
	}
}


