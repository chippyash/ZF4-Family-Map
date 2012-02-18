<?php
/**
 * ZF4 Library
 *
 * Base Action helper - Extend ZF4 action helpers from this
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * ZF4 Base action helper
 *
 * Adds a getOptions() method to get the options for this helper from the application ini
 * file resources.actionhelper.<helpername> section
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * Options invoked from the application.ini->resources.actionhelper.<helpername> section
	 *
	 * @var mixed
	 */
	protected $_options;

	public function init() {
		$opts = $this->getActionController()
			  ->getInvokeArg('bootstrap')
			  ->getOption('resources');
		$tag = strtolower($this->getName());
		$this->_options = (isset($opts['actionhelper'][$tag]) ? $opts['actionhelper'][$tag] : array());
	}

	public function getOptions() {
		return $this->_options;
	}
}