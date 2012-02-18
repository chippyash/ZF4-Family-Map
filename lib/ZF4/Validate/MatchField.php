<?php
/**
 * ZWare Business Function Library (ZBF)
 *
 * Validation Library
 *
 * @category	ZF4
 * @package  	Validate
 * @subpackage  MatchField
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
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
 * MatchField Validation
 * Checks to see if the value is equal to another
 *
 * @category	ZF4
 * @package  	Validate
 * @subpackage  MatchField
 */
class ZF4_Validate_MatchField extends Zend_Validate_Abstract {
  const NOT_MATCH = 'notMatch';
  const MISSING_FIELD_NAME = 'missingFieldName';
  const INVALID_FIELD_NAME = 'invalidFieldName';

  /**
   * @var array
  */
  protected $_messageTemplates = array(
    self::MISSING_FIELD_NAME  =>
      'Field name to match against was not provided.',
    self::INVALID_FIELD_NAME  =>
      'The field "%fieldName%" was not provided to match against.',
    self::NOT_MATCH =>
      'Does not match %fieldTitle%.'
  );

  /**
   * @var array
  */
  protected $_messageVariables = array(
    'fieldName' => '_fieldName',
    'fieldTitle' => '_fieldTitle'
  );

  /**
   * Name of the field as it appear in the $context array.
   *
   * @var string
   */
  protected $_fieldName;

  /**
   * Title of the field to display in an error message.
   *
   * If evaluates to false then will be set to $this->_fieldName.
   *
   * @var string
  */
  protected $_fieldTitle;

  /**
   * Sets validator options
   *
   * @param  string $fieldName
   * @param  string $fieldTitle
   * @return void
  */
  public function __construct($fieldName, $fieldTitle = null) {
    $this->setFieldName($fieldName);
    $this->setFieldTitle($fieldTitle);
  }

  /**
   * Returns the field name.
   *
   * @return string
  */
  public function getFieldName() {
    return $this->_fieldName;
  }

  /**
   * Sets the field name.
   *
   * @param  string $fieldName
   * @return Fluent_Interface
  */
  public function setFieldName($fieldName) {
    $this->_fieldName = $fieldName;
    return $this;
  }

  /**
   * Returns the field title.
   *
   * @return integer
  */
  public function getFieldTitle() {
    return $this->_fieldTitle;
  }

  /**
   * Sets the field title.
   *
   * @param  string:null $fieldTitle
   * @return Fluent_Interface
  */
  public function setFieldTitle($fieldTitle = null) {
    $this->_fieldTitle = $fieldTitle ? $fieldTitle : $this->_fieldName;
    return $this;
  }

  /**
   * Defined by Zend_Validate_Interface
   *
   * Returns true if and only if a field name has been set, the field name is available in the
   * context, and the value of that field name matches the provided value.
   *
   * @param  string $value
   *
   * @return boolean
  */
  public function isValid($value, $context = null) {
    $this->_setValue($value);
    $field = $this->getFieldName();

    if (empty($field)) {
      $this->_error(self::MISSING_FIELD_NAME);
      return false;
    } elseif (!isset($context[$field])) {
      $this->_error(self::INVALID_FIELD_NAME);
      return false;
    } elseif (is_array($context)) {
      if ($value == $context[$field]) {
        return true;
      }
    } elseif (is_string($context) && ($value == $context)) {
      return true;
    }
    $this->_error(self::NOT_MATCH);
    return false;
  }
}