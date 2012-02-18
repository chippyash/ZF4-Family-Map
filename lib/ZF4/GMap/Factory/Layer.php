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
 * Utility class for GMap layers
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Factory
 */
class ZF4_GMap_Factory_Layer {
	
	/**
	 * Class base name to generate
	 *
	 * @var string
	 */
	static protected $_classBase = 'ZF4_GMap_Layer_';
	/**
	 * Allowed class subtypes to generate
	 * Enter in lower case
	 *
	 * @var array
	 */
	static protected $_allowedTypes = array(
		ZF4_GMap::LAYER_CUSTOM 	=> 'custom',
		ZF4_GMap::LAYER_DB  		=> 'db',
		ZF4_GMap::LAYER_GEOXML  	=> 'geoxml',
		ZF4_GMap::LAYER_TRAFFIC  	=> 'traffic',
		ZF4_GMap::LAYER_POLYGON   => 'polygon'
	);
	
	/**
	 * Create a layer
	 *
	 * @param int|string $type one of ZF4_GMap::LAYER.. or the layer name
	 * @param array $params Any parameters required for the layer type
	 * 				id => string: layer name
	 * 				map => ZF4_Gmap_Map: map to attach the layer to
	 * 				icon => string|int|AWare_Gmap_Abstract_Icon [optional] Icon to use for layer
	 * 				hidden => boolean [optional] Set true to hide layer on map startup
	 * @param boolean $noLang Set true to not use language support - default = true
	 * @return ZF4_GMap_Abstract_Layer
	 * @throws ZF4_GMap_Exception if invalid layer type given
	 * @throws ZF4_GMap_Exception if no layer id given in params
	 * 
	 */	
	public static function factory($type, $params, $noLang = true) {
		if (is_string($type)) {
			if (!in_array(strtolower($type),self::$_allowedTypes)) {
				throw new ZF4_GMap_Exception('Invalid Layer type requested');
			}
		} elseif (is_int($type)) {
			if (!isset(self::$_allowedTypes[$type])) {
				throw new ZF4_GMap_Exception('Invalid Layer type requested');
			}
			$type = self::$_allowedTypes[$type];
		}
		if (!isset($params['id'])) throw new ZF4_GMap_Exception('Invalid layer id');
		if (!isset($params['map'])) throw new ZF4_GMap_Exception('Invalid map for layer');
		$icon = (isset($params['icon']) ? $params['icon'] : null);
		$class = self::$_classBase . ucfirst($type);
		return new $class($params['id'],$params['map'],$icon, $params, $noLang);

	}	
	
}