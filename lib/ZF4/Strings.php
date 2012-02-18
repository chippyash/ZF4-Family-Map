<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Strings
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
 * Generic static string functions
 *
 * @category	ZF4
 * @package 	Strings
 */
class ZF4_Strings {
	
	/**
	 * Proper case a strings
	 *
	 * @param string $str
	 * @return string
	 */
	public static function properCase($str) {
		$parts = explode(' ',$str);
		$lim = count($parts);
		$result = '';
		for ($i=0; $i<$lim; $i++) {
			$result .=  ucfirst($parts[$i]) . ' ';
		}
		return trim($result);
	}
}