<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	Filter
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
 * Country filter
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Country implements Zend_Filter_Interface {

	/**
	 * country alias array
	 *
	 * @var array
	 */
	protected static $_aliases;
	protected static $_flipped;

	/**
	 * Perform the filter
	 *
	 * Will return the alias code if country or country code if found else null
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function filter($value) {
		if (empty(self::$_aliases)) {
			$c = new ZF4_Country();
			self::$_aliases = $c->getAliases(false,true);
			self::$_flipped = array_flip(self::$_aliases);
		}
		$value = strtoupper($value);
		$filteredValue = (isset(self::$_aliases[$value]) ? self::$_aliases[$value] : null);
		if (null === $filteredValue) {
			//try for the code
			$filteredValue = (isset(self::$_flipped[$value]) ? $value : null);
		}
		return $filteredValue;
	}
}