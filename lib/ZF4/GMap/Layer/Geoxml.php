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
 * Defines a Map Layer (google GGeoXml) for the GMap interface 
 *
 * Corresponds to ZF4_GMap::LAYER_GEOXML
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
class ZF4_GMap_Layer_Geoxml 
	extends ZF4_GMap_Abstract_Layer {

	/**
	 * Hide the layer when it is added to map
	 *
	 * @var boolean
	 */
	private $_hideOnStart = false;
	
	/**
	 * Public variables are as for the GLayer properties
	 * See: http://code.google.com/apis/maps/documentation/overlays.html
	 * Owing to a bug in ArrayObject::getArrayCopy()
	 * we need to set them in the constructor
	 */
	public function __construct($options) {
		parent::__construct();
		//set up attributes
		$this->layerName = $options['id'];
		$this->_hideOnStart = (isset($options['hidden']) ? $options['hidden'] : false);
	}
	
	
	/**
	 * render the javascript required to add overlay to a map
	 * 
	 * @param ZF4_GMap_Map $map Handle to the map that the layer is being attached to
	 * @param ZF4_Gmap_Marker $marker Handle to marker to use for layer.  Not required for Geoxml
	 */
	public function toJScript(ZF4_GMap_Map $map, ZF4_Gmap_Marker $marker = null) {
		$layName = ZF4_GMap::MAP_LAYER_PREFIX . ZF4_GMap_Layer::$layerNum;
		ZF4_GMap_Layer::$layerNum ++;
		$script = "_GMAP.{$layName} = new GGeoXml(\"{$this->layerName}\"); ";
		if ($this->_hideOnStart) {
			$script .= "_GMAP.{$layName}.hide();";
		}		

		$script .= "_GMAP." . ZF4_GMap::MAP_VAR_PREFIX . $map->id 
		        . ".addOverlay(_GMAP.{$layName});";
		return $script;
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