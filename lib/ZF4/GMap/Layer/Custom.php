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
 * Defines a generic Map Layer (google GLayer) for the GMap interface 
 *
 * Corresponds to ZF4_GMap::LAYER_CUSTOM
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
class ZF4_GMap_Layer_Custom extends ZF4_GMap_Abstract_Layer {


	/**
	 * Render the javascript to add this layer to the output
	 *
	 * @param string $mapName name of map that layers are being added on
	 * @return string javascript
	 */
	protected function _toJscriptThis($mapName) {
		$layer = array(
			'id' => $this->id,
			'hidden' => $this->_hideOnStart,
			'type' => 'custom',
			'map' => $mapName,
			'defIcon' => $this->_defIcon->toArray(),
			'locations' => array()
		);
		
		$locations = $this->getLocations();
		if (count($locations) > 0) {
			foreach ($locations as $location) {
				$layer['locations'][] = $location->toArray();
			}
		}
		return Zend_Json::encode($layer);
	}
	
	/**
	 * Render the snippet of javerscript to create a info window divs
	 *
	 * @param ZF4_GMap_Info_Template $template template object to use
	 */
	public function renderInfoDom(&$startNode, ZF4_GMap_Info_Template $template) {
		return "";
	}
	
}