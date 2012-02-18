<?php
/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  ViewHelper
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
 * @see ZF4_GMap
 */
require_once "ZF4/GMap.php";

/**
 * @see Zend_Registry
 */
require_once 'Zend/Registry.php';

/**
 * @see Zend_View_Helper_Abstract
 */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * @see ZF4_GMap_View_Helper_GMap_Container
 */
require_once "ZF4/GMap/View/Helper/GMap/Container.php";

/**
 * GMap View Helper. Functions as a stack for code and loads all GMap dependencies.
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  ViewHelper
 */
class ZF4_GMap_View_Helper_GMap extends Zend_View_Helper_Abstract
{
    /**
     * @var Zend_View_Interface
     */
    public $view;

   /**
     * Initialize helper
     *
     * Retrieve container from registry or create new container and store in
     * registry.
     *
     * @return void
     */
    public function __construct()
    {
        $registry = Zend_Registry::getInstance();
        $class = __CLASS__;
        if (!isset($registry[$class])) {
            require_once 'ZF4/GMap/View/Helper/GMap/Container.php';
            $container = new ZF4_GMap_View_Helper_GMap_Container();
            $registry[$class] = $container;
        }
        $this->_container = $registry[__CLASS__];
    }

	/**
	 * Return GMap View Helper class, to execute GMap library related functions.
	 *
	 * @return ZF4_GMap_View_Helper_GMap_Container
	 */
    public function GMap()
    {
        return $this->_container;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->_container->setView($view);
    }

    /**
     * Proxy to container methods
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     * @throws Zend_View_Exception For invalid method calls
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->_container, $method)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception(sprintf('Invalid method "%s" called on GMap view helper', $method));
        }

        return call_user_func_array(array($this->_container, $method), $args);
    }

}