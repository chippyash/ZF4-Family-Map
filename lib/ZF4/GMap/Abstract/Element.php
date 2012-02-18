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
 * Abstract Element
 * A Basic google maps element
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Element
 */
abstract class ZF4_GMap_Abstract_Element extends ZF4_Object_Virtual {

    protected $_name;
    
    protected $_value;
    
    /**
     * Constructor
     * 
     * @param string $name Name of element
     * @param mixed $value Value of element 
     * @param boolean $noLang No language support if true, default true
     */
    public function __construct($name, $value, $noLang = true) {
        $this->_name = $name;
        $this->_setValue($value);
        parent::__construct($noLang);
    }

    /**
     * Validate and set the value for the element 
     */
    abstract protected function _setValue($value);
    
    /**
     * Return google api declaration for element
     *
     * @return string
     */
    abstract public function toJScript();
    
}