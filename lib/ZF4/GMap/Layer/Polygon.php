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
 * Defines a Polygon layer for the GMap interface 
 *
 * Corresponds to ZF4_GMap::LAYER_POLYGON
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
class ZF4_GMap_Layer_Polygon extends ZF4_GMap_Abstract_Layer {

	protected $_coords = array();
	protected $_color = '#000000';
	protected $_opacity = 0.5;
	
	/**
	 * Constructor
	 * 
	 * Options:
	 *  coords	Coordinate array for polygon
	 *  colour  Colour of polygon
	 *  opacity Opacity of polygon
	 * 
	 * @param string $name  Layer name
	 * @param ZF4_GMap_Map $map The map that this layer is attached to
	 * @param ZF4_GMap_Icon_Abstract $icon Default icon pin to use - if null will use the one set on the map
	 * @param array $options
	 * @param boolean $noLang if true do not use language support - default = true
	 */
	public function __construct($name, ZF4_Gmap_Map $map, ZF4_GMap_Abstract_Icon $icon = null, array $options = array(), $noLang = true) {
		parent::__construct($name, $map, $icon, $options, $noLang);
		//set up additional attributes
		$this->_coords = (isset($options['coords']) ? $options['coords'] : $this->_coords);
		$this->_colour = (isset($options['colour']) ? $options['colour'] : $this->_colour);
		$this->_opacity = (isset($options['opacity']) ? $options['opacity'] : $this->_opacity);
	}
	
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
			'type' => 'polygon',
			'map' => $mapName,
			'coords' => $this->_coords,
			'colour' => $this->_colour,
			'opacity' => $this->_opacity
		);
		
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