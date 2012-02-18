<?php
/**
 * ZF4 Library
 *
 * @category	Family_Map
 * @package  	Validate
 * @subpackage  PersonType
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
 * Zend abstract validator
 *
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Person type Validation
 * Validates input as being in **set** of valid person types
 *
 * @category	Family_Map
 * @package  	Validate
 * @subpackage  PersonType
 */
class ZF4_Validate_PersonType extends Zend_Validate_Abstract {
	
  const NOT_MATCH = 'notMatch';

  /**
   * @var array
  */
  protected $_messageTemplates = array(
    self::NOT_MATCH =>
      'Not in set of valid Person Types'
  );

  /**
   * person model
   *
   * @var Application_Model_Person
   */
  protected static $_pHandler;
  
  /**
   * Defined by Zend_Validate_Interface
   *
   * Returns true if and only if value is in set of allowable person types
   * Multiple types (i.e. a set) can be passed in as comma delimited list
   *
   * @param  string $value
   *
   * @return boolean
  */
  public function isValid($value, $context = null) {
    $this->_setValue($value);
	if (null == self::$_pHandler) {
		self::$_pHandler = new Application_Model_Person();
	}
	$mask = self::$_pHandler->getValidTypes();
	$value = explode(',',$value);
	$error = false;
	foreach ($value as $type) {
		if (!in_array($type,$mask)) $error = true;
	}
	if ($error) {
		$this->_error(self::NOT_MATCH);
		return false;
	} else {
		return true;
	}
    
  }
}