<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Trait
 * @subpackage 	Interface
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
 * Interface for a class implementing trait support
 * 
 * @category 	ZF4
 * @package  	Trait
 * @subpackage 	Interface
 */
interface ZF4_Trait_Interface {

    /**
     * Does the object have a particular trait
     * 
     * @param string $traitName Name of trait
     * @returns boolean
     */
    public function hasTrait($traitName);

    /**
     * Call a trait method if it exists
     * Simply put a call to _callTrait($method, $args) in your __call method
     *
     * @param string $method
     * @param object $pObj Parent owning class of this trait
     * @param array $args
     * @return boolean True on success else false
     * @access protected
     */
    function _callTrait($method, $pObj, $args);

    /**
     * Returns singleton instance of the trait broker
     * via ZF4-Trait_Broker::getInstance()
     *
     * @return ZF4_Trait_Broker
     */
    public function getTraitBroker();

    /**
     * Initialise any traits required for the object
     * Call this from your class constructor
     *
     * @return void
     * @access protected
     */
    function _initTraits();

    /**
     * Register a trait for the object
     * Typically you will call $this->getTraitBroker()->registerTrait(...)
     *
     * @param MZF4Trait_Abstract
     * @return ZF4_Trait_Interface Fluent Interface
     * @access protected
     */
    function _registerTrait(ZF4_Trait_Abstract $trait);
}