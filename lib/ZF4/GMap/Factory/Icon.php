<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Factory
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
 * @subpackage  Factory
 */
class ZF4_GMap_Factory_Icon {
	
	/**
	 * Class base name to generate
	 *
	 * @var string
	 */
	static protected $_classBase = 'ZF4_GMap_Icon_';
	/**
	 * Allowed class subtypes to generate
	 * Enter in lower case
	 *
	 * @var array
	 */
	static protected $_allowedTypes = array(
		ZF4_GMap::ICON_ALPHABET  	=> 'alphabet',
		ZF4_GMap::ICON_CUSTOM    	=> 'custom',
		ZF4_GMap::ICON_DEFAULT   	=> 'default',
		ZF4_GMap::ICON_PIN_CYCLE 	=> 'pin_Cycle',
		ZF4_GMap::ICON_PIN_SINGLE	=> 'pin_Single'
	);

	/**
	 * Create a marker icon
	 *
	 * @param int|string $type one of ZF4_GMap::ICON.. or the icon name
	 * @param array $params Any parameters required for the icon type
	 * @return ZF4_GMap_Abstract_Icon
	 * @throws ZF4_GMap_Exception if invalid icon type given
	 */
	public static function factory($type, $params = null) {
		if (is_string($type)) {
			if (!in_array(strtolower($type),self::$_allowedTypes)) {
				throw new ZF4_GMap_Exception('Invalid Icon type requested');
			}
		} elseif (is_int($type)) {
			if (!isset(self::$_allowedTypes[$type])) {
				throw new ZF4_GMap_Exception('Invalid Icon type requested');
			}
			$type = self::$_allowedTypes[$type];
		}
		$class = self::$_classBase . ucfirst($type);
		return new $class($params);
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
		//subtract icon options from options array
		$iconOpts = array('colour','image','shadow','iconSize','shadowSize','iconAnchor','infoWindowAnchor');
		$options = array_diff($options,$iconOpts);
		return $options;
	}
}