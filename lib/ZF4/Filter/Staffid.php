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
 * Staff Id filter
 * 
 * Takes a string staff id and converts into numeric internal id
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Staffid implements Zend_Filter_Interface {

	/**
	 * Perform the filter
	 *
	 * @param mixed $value
	 * @return int|string Person id or 'undefined'
	 */
	public function filter($value) {
		$filteredValue = 'undefined';
		$flag = true;
		try {
			if (is_numeric($value)) {
				$model = new Application_Model_Staff(intval($value));
			} else {
				$model = new Application_Model_Staff($value);
			}
		} catch (ZF4_Db_Table_Exception_InvalidId $e) {
			//we are not a valid staff member
			$flag = false;
		}
		if ($flag) {
			$filteredValue = $model->id;
		}
		return $filteredValue;
	}
}