<?php
/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Element
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
 * Url string
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Element
 */
class ZF4_GMap_Element_Url extends ZF4_GMap_Abstract_Element {
    
    /**
     * Validate and set the value for the element 
     */
    protected function _setValue($value) {
        if (!is_string($value)) {
            throw new ZF4_Exception_InvalidParameter('$value is not a string');
        }
        $this->_value = $value;
    }
    
    /**
     * Return google api declaration for element
     *
     * @return string
     */
    public function toJScript() {
        $ret = '"' . Zend_Json::encode($this->_value) .'"';
        return $ret;
    }    
    
    /**
     * Return value as a json element
     * 
     * @return string 
     */
     public function toJson() {
         return $this->toJScript();
     }
}
