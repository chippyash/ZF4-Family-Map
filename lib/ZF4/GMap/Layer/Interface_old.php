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
 * GMap Layer interface
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
interface ZF4_GMap_Layer_Interface {
	
	/**
	 * render the javascript required to add overlay to a map
	 * 
	 * @param ZF4_GMap_Map $map Handle to the map that the layer is being attached to
	 * @param ZF4_Gmap_Marker $marker Handle to marker to use for layer.  Not all layer types
	 * are expected to require this.  Db layer does.
	 */
	public function toJScript(ZF4_GMap_Map $map, ZF4_Gmap_Marker $marker = null);
	
	/**
	 * Render the snippet of javerscript to create a info window divs
	 *
	 * @param int $startNode starting node for this layer
	 * @param ZF4_GMap_Info_Template $template template object to use
	 */
	public function renderInfoDom(&$startNode, ZF4_GMap_Info_Template $template);
}