<?php
/**
 * Trait-like Functionality for PHP
 * http://www.stevehollis.com/2011/04/trait-like-functionality-for-php/
 *
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  Abstract
 * @author 	Steve Hollis (steve@hlmenterprises.co.uk)
 * @license     Creative Commons Attribution 3.0 (http://creativecommons.org/licenses/by/3.0/)
 * @author      Ashley Kitson - adaption for ZF4
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
 * Abstract base class for classes that want to implement traits
 * Does the heavy lifting
 * 
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  Abstract
 */
abstract class ZF4_Trait_Abstract {

    /**
     * @var array Array of methods offering extended functionality
     */
    protected $_methods;
    /**
     * method name pattern to exclude from public method list
     *
     * @var string
     */
    protected $_excludeMethods = '/^(__construct|getMethods|_set.*)$/';

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct(array $options = array()) {
        $this->_setOptions($options);
        $this->_init();
    }

    /**
     * Extend this in your ancestor to do any trait initialisation
     * 
     * @return void
     */
    protected function _init() {

    }

    /**
     * Set options
     * For any options you send in you need to create a protected
     * _set<option> method in your ancestor class
     *
     * @param  array $options
     * @return ZF4_Trait_Abstract
     */
    protected function _setOptions(array $options) {
        foreach ($options as $key => $value) {
            $method = '_set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Return array of available methods
     * 
     * @return array
     */
    public function getMethods() {
        if (NULL === $this->_methods) {
            $this->_methods = array();
            $this->_retrieveMethods();
        }
        return $this->_methods;
    }

    /**
     * Create public method list
     * 
     * @return ZF4_Trait_Abstract
     */
    protected function _retrieveMethods() {
        $refObject = new ReflectionObject($this);
        foreach ($refObject->getMethods() as $method) {
            if ($method->isPublic()) {
                $name = $method->getName();
                if (preg_match($this->_excludeMethods, $name)==0) {
                    $this->_addMethod($name);
                }
            }
        }
        return $this;
    }

    /**
     * Add a method to public method list
     * 
     * @param string $method Name of method
     * @return ZF4_Trait_Abstract
     * @throws ZF4_Trait_Exception
     */
    protected function _addMethod($method) {
        if (!method_exists($this, $method)) {
            throw new ZF4_Trait_Exception("Method '$method' does not exist");
        }
        if (array_search($method, $this->_methods)) {
            throw new ZF4_Trait_Exception("Duplicate method '$method'");
        }
        $this->_methods[] = $method;

        return $this;
    }

}