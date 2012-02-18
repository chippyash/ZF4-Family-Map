<?php

/**
 * Trait-like Functionality for PHP
 * http://www.stevehollis.com/2011/04/trait-like-functionality-for-php/
 *
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  Broker
 * @author 	Steve Hollis (steve@hlmenterprises.co.uk)
 * @license     Creative Commons Attribution 3.0 (http://creativecommons.org/licenses/by/3.0/)
 * @author 	Ashley Kitson - conversion to ZF4 standard
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
 * Trait broker - shared broker for all classes implementing traits
 * 
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  Broker
 */
class ZF4_Trait_Broker implements ZF4_Singleton_Interface {

    /**
     * Array of instance traits in use and their public methods
     * [traitName=>[methodName,methodName,...]
     *
     * @var array
     */
    protected $_traits = array();
    /**
     * Array of extended functionality methods provided by traits, indexed by trait
     * [methodName=>traitName,...]
     *
     * @var array
     */
    protected $_methods = array();
    /**
     * Array of object classes and their traits
     * [className=>[traitName=>trait,traitName=>trait,...]]
     *
     * @var array
     */
    protected $_classes = array();

    ////////////
    // Traits //
    ////////////

    /**
     * Register a trait.
     *
     * If the trait is passed in as a string, a generic trait will be
     * instantiated.  If the trait is an object, that trait will be used
     * for the class.
     *
     * @param string|ZF4_Trait_Abstract $trait trait to register
     * @param ZF4_Trait_Interface $object Object that trait is assigned to
     * @param array $options Options to pass to trait if $trait is a string
     * @return ZF4_Trait_Broker
     * @throws ZF4_Trait_Exception
     */
    public function registerTrait($trait, ZF4_Trait_Interface $object, array $options = array()) {
        $traitClass = $this->_checkTrait($trait);
        $objectClass = $this->_checkClass($object);
        if (is_string($trait)) {
            $trait = new $traitClass($options);
        }
        if (!array_key_exists($traitClass, $this->_traits)) {
            // Register trait methods
            $this->_registerMethods($trait, $objectClass);
        }

        if (!isset($this->_classes[$objectClass][$traitClass])) {
            // Add the trait to the class stack
            $this->_classes[$objectClass][$traitClass] = $trait;
        }

        return $this;
    }

    /**
     * Unregister a trait.
     *
     * @param string|ZF4_Trait_Abstract $traitClass Trait object or class name
     * @param string|ZF4_Trait_Interace $objectClass Object class name
     * @return ZF4_Trait_Broker
     * @throws ZF4_Trait_Exception
     */
    public function unregisterTrait($traitClass, $objectClass) {
        $traitClass = $this->_checkTrait($traitClass);
        $objectClass = $this->_checkClass($objectClass);

        // No traits registered for class
        if (!isset($this->_classes[$objectClass][$traitClass])) {
            throw new ZF4_Trait_Exception("Trait ' . $traitClass . ' not registered for class '$objectClass'");
        }
        //unregister the trait for the object
        unset($this->_classes[$objectClass][$traitClass]);

        return $this;
    }

    /**
     * Is a trait of a particular class registered?
     *
     * @param string|ZF4_Trait_Abstract $traitClass Trait class
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return bool
     * @throws ZF4_Trait_Exception
     */
    public function hasTrait($traitClass, $objectClass) {
        $traitClass = $this->_checkTrait($traitClass);
        $objectClass = $this->_checkClass($objectClass);
        if (isset($this->_classes[$objectClass][$traitClass])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve a trait for a class
     *
     * @param string|ZF4_Trait_Abstract $traitClass Trait class
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return false|ZF4_Trait_Abstract Returns false if none found or trait if found
     * @throws ZF4_Trait_Exception
     */
    public function getTrait($traitClass, $objectClass) {
        $traitClass = $this->_checkTrait($traitClass);
        $objectClass = $this->_checkClass($objectClass);

        if ($this->hasTrait($traitClass, $objectClass)) {
            return $this->_classes[$objectClass][$traitClass];
        } else {
            return false;
        }
    }

    /**
     * Retrieve all traits for an object
     *
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return array can be empty array if no traits
     * @throws ZF4_Trait_Exception
     */
    public function getTraits($objectClass) {
        $objectClass = $this->_checkClass($objectClass);
        if (isset($this->_classes[$objectClass])) {
            return $this->_classes[$objectClass];
        } else {
            return array();
        }
    }

    /**
     * Check object class|object and return class name
     * 
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return string
     * @throws ZF4_Trait_Exception
     */
    private function _checkClass($objectClass) {
        if ($objectClass instanceof ZF4_Trait_Interface) {
            $objectClass = get_class($objectClass);
        } elseif (!is_string($objectClass)) {
            throw new ZF4_Trait_Exception('Invalid object specified');
        }
        return $objectClass;
    }

    /**
     * Check trait class name|object and return trait name
     *
     * @param string|ZF4_Trait_Abstract $traitClass Trait class
     * @return string
     * @throws ZF4_Trait_Exception
     */
    private function _checkTrait($traitClass) {
        if ($traitClass instanceof ZF4_Trait_Abstract) {
            $traitClass = get_class($traitClass);
        } elseif (!is_string($traitClass)) {
            throw new ZF4_Trait_Exception('Invalid trait specified');
        }
        return $traitClass;
    }

    /////////////
    // Methods //
    /////////////

    /**
     * Register trait methods with the broker
     *
     * @param ZF4_Trait_Abstract $trait
     * @param string $objectClass
     * @return ZF4_Trait_Broker
     */
    protected function _registerMethods(ZF4_Trait_Abstract $trait, $objectClass) {
        $traitClass = $this->_checkTrait($trait);
        $this->_traits[$traitClass] = array();
        foreach ($trait->getMethods() as $method) {
            $this->_registerMethod($traitClass, $method, $objectClass);
        }

        return $this;
    }

    /**
     * Register trait method with the broker
     *
     * @param string $traitClass
     * @param string $method
     * @param string $objectClass
     * @return ZF4_Trait_Broker
     * @throws ZF4_Trait_Exception
     */
    protected function _registerMethod($traitClass, $method, $objectClass) {
        if (!isset($this->_methods[$objectClass])) {
            $this->_methods[$objectClass] = array();
        } else {
            //we can't have two methods with same name - limitation of trait architecture
            if ($this->hasMethod($method, $objectClass)) {
                throw new ZF4_Trait_Exception("Method '$method' already registered.");
            }
        }
        $this->_methods[$objectClass][$method] = $traitClass;
        $this->_traits[$traitClass][] = $method;
        return $this;
    }

    /**
     * @param string $method
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return boolean
     */
    public function hasMethod($method, $objectClass) {
        $objectClass = $this->_checkClass($objectClass);
        if (!isset($this->_methods[$objectClass])) {
            return false;
        }

        return array_key_exists($method, $this->_methods[$objectClass]);
    }

    /**
     * Call the trait method
     * $pObj is always passed in as the first parameter to the method
     *
     * @param string $method
     * @param ZF4_Trait_Interface $object
     * @param Object $pObj The parent object that has the trait
     * @param array $args
     * @return mixed
     * @throws ZF4_Trait_Exception
     */
    public function callMethod(
            $method,
            ZF4_Trait_Interface $object,
            $pObj,
            array $args = array())
   {
        $objectClass = get_class($object);
        if (!$this->hasMethod($method, $objectClass)) {
            throw new ZF4_Trait_Exception("Cannot call method '$method' - not registered.");
        }
        $trait = $this->_classes[$objectClass][$this->_methods[$objectClass][$method]];
        //add the owning object as first parameter
        array_unshift($args, $pObj);
        //call the trait method and return result
        $result = call_user_func_array(
                        array($trait, $method),
                        $args
        );
        return $result;
    }

    /////////////
    // Classes //
    /////////////

    /**
     * Is class registered in broker
     *
     * @param string|ZF4_Trait_Interface $objectClass Object class
     * @return boolean
     */
    public function isClassRegistered($objectClass) {
        $objectClass = $this->_checkClass($objectClass);
        $ret = array_key_exists($objectClass, $this->_classes);
        return $ret;
    }


    /**
     * ZF4_Singleton_Interface
     *
     * @var ZF4_Trait_Broker
     */
    protected static $_instance;

    /**
     * Prevents new ZF4_Trait_Broker() being called from anywhere except
     * getInstance()
     */
    protected function __construct() {}

    /**
     * Get an instance of the singleton class
     *
     * @param mixed $config configuration parameters, usually an array
     * @return object descended from ZF4_Singleton
     */
    public static function getInstance($config) {
        if (empty(self::$_instance)) {
            self::$_instance = new ZF4_Trait_Broker();
        }
        return self::$_instance;
    }

    /**
     * ignored - not required in this case
     *
     * @param mixed $config
     * @access private
     */
    public function init($config) {}

}