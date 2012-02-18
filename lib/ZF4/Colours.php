<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Colours
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
 * Generic static colour related functions
 *
 * @category	ZF4
 * @package 	Colours
 */
class ZF4_Colours {
	
	/**
	 * generate an array of websafe colours
	 *
	 * @return array
	 */
	public static function webSafe() {
		$cs = array('00', '33', '66', '99', 'CC', 'FF');
		$retArr = array();
		for($i=0; $i<6; $i++) {
	        for($j=0; $j<6; $j++) {
	            for($k=0; $k<6; $k++) {
	            	$col = $cs[$i] .$cs[$j] .$cs[$k];
	                $retArr[$col] = $col;
	            }
	        }
	    }
	    return $retArr;
	}
	
	/**
	 * Generate a select option list of websafe colours
	 *
	 * @return string html option list
	 */
	public static function webSafeOptions() {
		$webSafe = self::webSafe();
		$ret = '';
		foreach ($webSafe as $value) {
			$ret .= "<option value='{$value}'>#{$value}</option>";
		}
		return $ret;
	}
}