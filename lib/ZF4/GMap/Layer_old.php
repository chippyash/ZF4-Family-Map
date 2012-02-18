<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
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
 * @subpackage  Layer
 */
class ZF4_GMap_Layer {
	
	/**
	 * counter for layer names
	 *
	 * @var int
	 */
	public static $layerNum = 1;
	
	/**
	 * Factory to create a layer type
	 *
	 * @param string $layerType A layer type
	 * @param mixed $params parameters for layer type (common param is 'hidden'=>bool)
	 * @return ZF4_GMap_Layer_..
	 */
	public static function factory($layerType,$params) {
		$objName = "ZF4_GMap_Layer_" . ucfirst($layerType);
		$obj = new $objName($params);
		return $obj;
	}
}