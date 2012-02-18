<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Trait
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
 * Abstract base class for implementing trait behaviour
 * Extend any class that needs to support Traits from this class
 *
 * @category 	ZF4
 * @package  	Trait
 */
abstract class ZF4_Trait implements ZF4_Trait_Interface {

    /**
     * Trait broker
     * @var ZF4_Trait_Broker
     */
    private $_traitBroker;

    /**
     * Initialize any traits
     */
    public function __construct() {
       // $this->_initTraits();
    }

    /**
     * Call a trait method if it exists
     *
     * @param string $method
     * @param object $pObj Parent owning class of this trait
     * @param array $args
     * @return boolean True on success else false
     * @throws ZF4_Trait_Exception
     * @access protected
     */
    public function _callTrait($method, $pObj, $args) {
        // Check trait broker for extended functionality
        $class = get_class($this);
        $traitBroker = $this->getTraitBroker();
        //if ($method == 'getMsg') Zend_Debug::dump($class,'class');Zend_Debug::dump($traitBroker);
        // Check whether class traits have been initialised
        if (!$traitBroker->isClassRegistered($class)) {
            $this->_initTraits();
        }

        if ($traitBroker->hasMethod($method, $class)) {
            return $traitBroker->callMethod($method, $this, $pObj, $args);
        }
        throw new ZF4_Trait_Exception(
                "Invalid method " . get_class($this) . "::" . $method . "(" . print_r($args, 1) . ")"
        );
    }

    /**
     * @return ZF4_Trait_Broker
     */
   final  public function getTraitBroker() {
        if (!isset($this->_traitBroker)) {
            $this->_traitBroker = ZF4_Trait_Broker::getInstance(null);
        }

        return $this->_traitBroker;
    }

    /**
     * Initialise any traits required for the object
     *
     * @return void
     * @access protected
     */
    public function _initTraits() {
        //extend in ancestor
    }

    /**
     * Register a trait for the object
     *
     * @param ZF4_Trait_Abstract
     * @return ZF4_Trait_Interface Fluent Interface
     * @access protected
     */
    final public function _registerTrait(ZF4_Trait_Abstract $trait) {
        $this->getTraitBroker()->registerTrait($trait, $this);
        return $this;
    }

    /**
     * Does the object have a particular trait
     *
     * @param string $traitName Name of trait
     * @returns boolean
     */
    public function hasTrait($traitName){
        $traitBroker = $this->getTraitBroker();
        $class = get_class($this);
        if (!$traitBroker->isClassRegistered($class)) {
            $this->_initTraits();
        }
        return $traitBroker->hasTrait($traitName,$class);
    }

}