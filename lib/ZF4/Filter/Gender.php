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
 * Gender filter
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Gender implements Zend_Filter_Interface {

	/**
	 * Perform the filter
	 *
	 * @param mixed $value
	 * @return string male|female|undefined
	 */
	public function filter($value) {
		$filteredValue = 'undefined';
		if (is_string($value)) {
			$value = substr(strtoupper($value),0,1);
			$filteredValue = ($value == 'M' ? 'male' : ($value =='F' ? 'female' : 'undefined'));
		}
		return $filteredValue;
	}
}