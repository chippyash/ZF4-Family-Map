<?php
/**
 * ZF4 Library
 *
 * Resource to load Action helpers
 *
 * @category	ZF4
 * @package 	Application
 * @subpackage  Resource
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
 * Load action helpers
 *
 * @category	ZF4
 * @package 	Application
 * @subpackage  Resource
 */
class ZF4_Application_Resource_Actionhelper extends  Zend_Application_Resource_ResourceAbstract {

	public function init() {
		$options = $this->getOptions();

		//add any action helper plugin paths
		$prefixes = (is_array($options['prefix']) ? $options['prefix'] : (array) $options['prefix']);
		foreach ($prefixes as $prefix) {
			Zend_Controller_Action_HelperBroker::addPrefix($prefix);
		}

		//initialise any action helpers
		$helpers = (is_array($options['helper']) ? $options['helper'] : (array) $options['helper']);
		foreach ($helpers as $helper) {
			Zend_Controller_Action_HelperBroker::getStaticHelper($helper);
		}
	}
}