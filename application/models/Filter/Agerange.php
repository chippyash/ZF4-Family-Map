<?php
/**
 * Familymap Library
 *
 * @category	Family_Map
 * @package  	Filter
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
 * Agerange filter
 *
 * Extends Zend_Filter_Digits to produce a 1 letter agerange code
 *
 */
class Application_Model_Filter_Agerange extends Zend_Filter_Digits {

	/**
	 * Perform the filter
	 *
	 * Will return the age range code for a given age
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function filter($value) {
		//ensure we have digits
		$age = intval(parent::filter($value));
		//convert to an age range
		$ret = 'Z';
		if ($age == 0) {
			$ret = 'Z';
		} elseif ($age < 17) {
			$ret = 'A';
		} elseif ($age == 17 || $age == 18 ) {
			$ret = 'B';
		} elseif ($age > 17 && $age < 26) {
			$ret = 'C';
		} elseif ($age > 25 && $age < 31) {
			$ret = 'D';
		} elseif ($age > 30 && $age < 41) {
			$ret = 'E';
		} elseif ($age > 40 && $age < 51) {
			$ret = 'F';
		} elseif ($age > 50 && $age < 61) {
			$ret = 'G';
		} elseif ($age > 60 && $age < 71) {
			$ret = 'H';
		} else {
			$ret = 'I';
		}

		return $ret;
	}
}