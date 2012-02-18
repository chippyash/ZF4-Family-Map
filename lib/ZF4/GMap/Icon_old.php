<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
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
 * Utility class for GMap icons
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
class ZF4_GMap_Icon {
	
	/**
	 * Factory to create an icon type
	 *
	 * @param int $iconType An icon type ZF4_GMap::MARKER_..
	 * @param mixed $params parameters for icon type
	 * @return ZF4_GMap_Icon_..
	 */
	public static function factory($iconType,$params) {
		$objName = "ZF4_GMap_Icon_";
		switch ($iconType) {
			case ZF4_GMap::MARKER_ALPHABET:
				$objName .= "Alphabet";
				break;
			case ZF4_GMap::MARKER_CUSTOM:
				$objName .= "Custom";
				break;
			case ZF4_GMap::MARKER_PIN_CYCLE :
				$objName .= "Pin_Cycle";
				break;
			case ZF4_GMap::MARKER_PIN_SINGLE :
				$objName .= "Pin_Single";
				break;
			case ZF4_GMap::MARKER_DEFAULT :
			default:
				$objName .= "Default";
				break;
		}
		$obj = new $objName($params);
		return $obj;
	}
	
	/**
	 * Helper method to clear any options from an array that
	 * belong to icons.  Used by Marker class
	 *
	 * @param array $options
	 * @return array
	 */#
	public static function cleanOptions($options) {
		//if not an array return
		if (!is_array($options)) return $options;
		//subtract icon option sfrom options array
		$iconOpts = array('colour','image','shadow','iconSize','shadowSize','iconAnchor','infoWindowAnchor');
		$options = array_diff($options,$iconOpts);
		return $options;
	}
}