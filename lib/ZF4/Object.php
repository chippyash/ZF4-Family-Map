<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
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
 * Base class of the ZF4_Object Domain Object hierarchy
 *
 * <p>An object that behaves like an array
 * (rather than using ArrayObject class that tries to make
 * an array behave like an object and fails.)</p>
 *
 * <p>Object has following features:</p>
 * <ul>
 * <li> Can be made unmutatable so that new public properties cannot be added on the fly
 *   see setMutable(); getMutable();
 * <li> Public Properties are set using one of:
 * <ul>
 *   <li> $class<li>>propName = $value
 *   <li> $class['propName'] = $value
 *   <li> $class[nnn] = $value
 *   <li> $class<li>>setPropname($value) //returns Fluent_Interface
 *   <li> $class<li>>set('propName',$value) //returns Fluent_Interface
 * </ul>
 * <li> Get Public Properties using one of:
 * <ul>
 *   <li> $value = $class<li>>propName
 *   <li> $value = $class['propName']
 *   <li> $value = $class[nnn]
 *   <li> $value = $class<li>>getPropname()
 *   <li> $value = $class<li>>get('propName');
 * <ul>
 * <li> Object can be passed to a foreach loop
 * <li> Implements a toArray() method to only return public parameters
 * </ul>
 * <p>Use the array construct to take full advantage of this object.  Using -> construct will
 * miss out some stuff (a PHP failing). e.g. $myObj['myVar'] rather than $myObj->myVar.
 * Use the -> construct for protected and private parameters.</p>
 * <p>Object also supports messaging.  Use isMsg() after a method call to determine if an error message was set</p>
 * <p> Object supports language translation through _() and translateText()</p>
 *
 * @category 	ZF4
 * @package  	Object
 */
class ZF4_Object extends ZF4_Trait implements
        ArrayAccess, Countable, IteratorAggregate {

    /**
     * Holds list of public parameters for array operations
     *
     * @var array
     */
    private $_publicParams = array();
    /**
     * Can object mutate by having public parameters added or removed?
     *
     * @var boolean
     */
    private $_isMutable = true;
    /**
     * No Language flag
     *
     * @var boolean
     */
    private $_noLang = false;

    /**
     * Constructor
     *
     * @param boolean $noLang If True, do not load the translator for messaging trait
     */
    public function __construct($noLang = false) {
        parent::__construct();
        $this->setPublicArray();
        $this->setDebug();
        $this->_noLang = $noLang;
    }

    /**
     * Return public parameters as an array
     * Required as casting the object to an array will return non public parameters
     * as well. see http://uk2.php.net/manual/en/language.types.array.php#language.types.array.casting
     *
     * @return array
     */
    final public function toArray() {
        return array_intersect_key((array) $this, $this->_publicParams);
    }

    /** Object Mutation * */

    /**
     * Set mutate flag
     * Set true (the default object state) to allow public parameters to be added/removed on the fly
     * i.e as per ordinary PHP objects
     *
     * @param boolean $flag
     */
    final public function setMutable($flag = true) {
        $this->_isMutable = $flag;
    }

    /**
     * Get mutate flag state
     *
     * @return boolean
     */
    final public function getMutable() {
        return $this->_isMutable;
    }

    /** Generic getter and setter methods * */

    /**
     * Set a public parameter
     *
     * @param string $offset
     * @param mixed $value
     * @return Fluent_Interface
     */
    public function set($offset, $value) {
        $this->offsetSet($offset, $value);
        return $this;
    }

    /**
     * Get a public parameter value
     *
     * @param string $offset
     * @return mixed
     */
    public function get($offset) {
        return $this->offsetGet($offset);
    }

    /**
     * Exchange current public parameters for a new one
     *
     * @param array $arr
     * return Fluent_Interface
     */
    public function exchangeArray(array $arr) {
        //clear down existing parameters
        /*
          if (count($this)>0 ) {
          foreach ($this as $key=>$value) {
          $this->offsetUnset($key);
          }
          } */

        //set up public variable list
        $props = array();
        if (count($arr) > 0) {
            foreach ($arr as $key => $value) {
                $props[$key] = gettype($value);
            }
        } else {
            $props = null;
        }
        $this->setPublicArray($props);

        //set the new values
        if (count($arr) > 0) {
            foreach ($arr as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
        return $this;
    }

    /** methods to support descendent class implementation * */

    /**
     * Return the array of public parameters being maintained
     *
     * @return array
     */
    protected function getPublicArray() {
        $this->_checkParams();
        return $this->_publicParams;
    }

    /**
     * Set the array of public parameters being maintained
     *
     * Uses reflection to build a list of public parameters that object will maintain
     *
     * @param array $arr Additional public parameters to maintain
     */
    protected function setPublicArray($arr = null) {
        $cl = new ReflectionClass($this);
        foreach ($cl->getProperties() as $prop) {
            $rp = $cl->getProperty($prop->name);
            if ($rp->isPublic()) {
                $this->_publicParams[$prop->name] = $prop->class;
            }
        }
        if (is_array($arr)) {
            foreach ($arr as $name => $type) {
                if (!isset($this->_publicParams[$name])) {
                    $this->_publicParams[$name] = $type;
                }
            }
        }
    }

    /** OVERLOADING Functions * */

    /**
     * Set a public parameter
     * called if public parameter not known
     *
     * @param string $offset Parameter name
     * @param mixed $value
     */
    public function __set($offset, $value) {
        $this->offsetSet($offset, $value);
    }

    /**
     * Get a public parameter
     * called if public parameter not known
     *
     * @param string $offset Parameter name
     * @return mixed
     */
    public function __get($offset) {
        return $this->offsetGet($offset);
    }

    /**
     * Is public parameter set?
     * called if public parameter is not known
     *
     * @param string $offset
     * @return boolean
     */
    public function __isset($offset) {
        return $this->offsetExists($offset);
    }

    /**
     * Unset a public parameter
     * Called if public parameter is not known
     *
     * @param unknown_type $offset
     * @return unknown
     */
    public function __unset($offset) {
        $this->offsetUnset($offset);
    }

    /**
     * Call an object method if it doesn't appear to exist
     * Used to proved set and get functionality so that caller can use
     * getParamname() or setParamname. NB you must call exactly as the parameter
     * name is given, ie t->myVar = setmyVar($value)
     *
     * Will also try to call methods on any traits that have been registered
     *
     * @param string $method
     * @param mixed $params
     * @return mixed
     * @throws ZF4_Trait_Exception
     */
    public function __call($method, $params=array(null)) {
        //call trait broker first to see if we get satisfied
        try {
            $ret = $this->_callTrait($method, $this, $params);
            return $ret;
        } catch (ZF4_Trait_Exception $e) {
echo $e->getMessage(); exit;
        }

        //not a trait method so see if setter or getter
        $ret = null;
        $pVar = substr($method, 3, strlen($method) - 3);
        //$classMethods = get_class_methods($this);
        //if (!in_array($method,$classMethods)) {
        if (strpos($method, 'set') === 0) {
            $this->offsetSet($pVar, (isset($params[0]) ? $params[0] : null));
            return $this;
        }
        if (strpos($method, 'get') === 0) {
            return $this->offsetGet($pVar);
        }
        return $ret;
    }

    /** ArrayAccess Interface Implementation * */

    /**
     * Does a public parameter exist?
     *
     * @param string|int $offset Parameter name
     * @return boolean
     */
    public function offsetExists($offset) {
        $this->_checkParams();
        if (is_int($offset)) {
            return ($offset > -1 && $offset <= count($this));
        } else {
            return (array_key_exists($offset, $this->_publicParams));
        }
    }

    /**
     * Get value of a public parameter
     *
     * @param string $offset Parameter name
     * @return mixed Parameter value else null
     * @throws ZF4_Object_Exception if offset doesn't exist
     */
    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            if (is_int($offset)) {
                $tmp = array_keys($this->_publicParams);
                $oset = $tmp[$offset];
                $ret = $this->$oset;
            } else {
                $ret = $this->$offset;
            }
        } else {
            $tmp = (is_int($offset) ? $offset : "'{$offset}'");
            $msg = "Parameter {$tmp} does not exist in class: " . get_class($this);
            $msg .= '. Object data = ' . var_export($this->toArray(), true);
        /*
            ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();
        Zend_Debug::dump($trace);exit;
        */
            throw new ZF4_Object_Exception($msg, E_USER_ERROR);
        }
        return $ret;
    }

    /**
     * Set the value of a parameter
     * same as $class->param = $value for public parameters
     *
     * @param string $offset Parameter name
     * @param mixed $value Value for parameter
     * @throws ZF4_Object_Exception if parameter doesn't exist
     */
    public function offsetSet($offset, $value) {
        $exists = $this->offsetExists($offset);
        if (!$exists && !$this->_isMutable) {
            $tmp = (is_int($offset) ? $offset : "'{$offset}'");
            throw new ZF4_Object_Exception("Parameter {$tmp} does not exist in class: " . get_class($this),
                    Zend_Log::ERR);
        }
        //set the value
        $this->$offset = $value;
        if (!$exists) {
            //add to public params if it doesn't already exist
            if (is_object($value)) {
                $tp = get_class($value);
            } else {
                $tp = gettype($value);
            }
            $this->_publicParams[$offset] = $tp;
        }
    }

    /**
     * Unset a public parameter
     *
     * @param string $offset Public parameter name
     * @throws ZF4_Object_Exception if parameter doesn't exist
     */
    public function offsetUnset($offset) {
        if (!$this->offsetExists($offset)) {
            throw new ZF4_Object_Exception("Parameter '{$offset}' does not exist in class: " . get_class($this),
                    Zend_Log::ERR);
        } else {
            unset($this->$offset);
            unset($this->_publicParams[$offset]);
        }
    }

    /** Countable interface implementation * */

    /**
     * Return number of public parameter items
     *
     * @return int
     */
    public function count() {
        $this->_checkParams();
        return count($this->_publicParams);
    }

    /** IteratorAggregate interface implementation * */

    /**
     * return an iterator for the public parameters only
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Check public params in case user has called unset->paramName
     * which is not caught by anything in this class if paramName exists
     *
     */
    protected function _checkParams() {
        $this->_publicParams = array_intersect_key($this->_publicParams, (array) $this);
    }

    /**
     * Initialise any traits required for the object
     * Init Messenger trait
     * Provides:
     * 		$this->_($text)
     * 		$this->translateText($text)
     * 		$this->setMsg($msg)
     * 		$this->clearMsg()
     * 		$this->getMsg()
     * 		$this->isMsg()
     *
     * @return void
     * @access protected
     */
    function _initTraits() {
        $this->_registerTrait(new ZF4_Trait_Messenger(array('noLang' => $this->_noLang)));
    }

    /**
     * Debug functionality
     */
    private $_debug = false;

    protected function setDebug() {
        try {
            $this->_debug = (Zend_Registry::get(ZF4_Defines::REGK_OBJDEBUG) == true);
        } catch (Zend_Exception $e) {
            $this->_debug = false;
        }
    }

    final public function objDebug($value, $name = null) {
        if ($this->_debug) {
            Zend_Debug::dump($value, get_class($this) . '->' . $name);
        }
    }

}