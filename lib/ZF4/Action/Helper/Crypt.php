<?php
/**
 * ZF4 Library
 *
 * Action Helper Check to read in Crypt seed value
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
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
 * ZF4_Crypt resource config loader helper
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Crypt extends ZF4_Action_Helper {

	/**
	 * The cryptography seed read from the application.ini->resources.actionhelper.crypt.seed=
	 * line
	 *
	 * usage: $seed = ZF4_Action_Helper_Crypt::seed;
	 *
	 * @var string
	 */
	public static $seed;

	/**
	 * Overide ancestor to set static seed on this object
	 *
	 * @return string The seed - object broker will place it in the registry
	 *
	 */
	public function init() {
		parent::init();
		self::$seed = $this->_options['seed'];
		return $this->_options['seed'];
	}

}